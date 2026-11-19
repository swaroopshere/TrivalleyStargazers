<?php
require_once __DIR__.'/newsletter_archive.php';
/** Controlled beta content import. No user accounts or membership data are read. */
function contentSyncSnapshot(PDO $pdo): array {
    $snapshot = [];
    foreach (['newsletters', 'official_documents', 'contacts'] as $table) {
        $snapshot[$table] = $pdo->query("SELECT * FROM $table ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    }
    return $snapshot;
}

function validatePublishedContent(array $manifest, string $root): void {
    foreach (array_merge($manifest['documents'], $manifest['newsletters']) as $entry) {
        $path = $entry['file_path'];
        if (!preg_match('~^(?:pdfs/[^/]+\.pdf|newsletters/[0-9]{4}/tvsnews[0-9]{4}\.pdf|privacy\.pdf)$~', $path)
            || strpos($path, '..') !== false || stripos($path, 'delvallenotify') !== false) {
            throw new RuntimeException('Invalid public document path');
        }
        if (!is_file($root . '/' . $path) || !hash_equals($entry['sha256'], hash_file('sha256', $root . '/' . $path))) {
            throw new RuntimeException('Published file verification failed: ' . $path);
        }
    }
}

function syncPublishedContent(PDO $pdo, array $manifest, string $root, bool $apply = false, string $expected = ''): array {
    validatePublishedContent($manifest, $root);
    $before = contentSyncSnapshot($pdo);
    $fingerprint = hash('sha256', json_encode($before));
    if (!$apply) {
        return ['operation'=>'plan', 'fingerprint'=>$fingerprint, 'backup'=>$before,
            'planned_newsletters'=>count($manifest['newsletters']), 'planned_documents'=>count($manifest['documents'])];
    }
    if (!hash_equals($fingerprint, $expected)) throw new RuntimeException('Database content changed after backup; plan again');
    $counts = ['newsletters_inserted'=>0, 'documents_updated'=>0, 'contacts_inserted'=>0,
        'contacts_updated'=>0, 'contacts_preserved_newer'=>0, 'calendar_inserted'=>0, 'meetings_inserted'=>0,
        'legacy_archive_inserted'=>0, 'legacy_archive_missing'=>0];
    $inserted = [];
    $select = function (string $sql, array $params) use ($pdo) {
        $stmt=$pdo->prepare($sql); $stmt->execute($params); return $stmt->fetch(PDO::FETCH_ASSOC);
    };
    $insert = function (string $table, array $values) use ($pdo, &$inserted): void {
        $keys=array_keys($values);
        $stmt=$pdo->prepare('INSERT INTO '.$table.' ('.implode(',',$keys).') VALUES ('.implode(',',array_fill(0,count($keys),'?')).')');
        $stmt->execute(array_values($values));
        $inserted[$table][]=(int)$pdo->lastInsertId();
    };
    $pdo->beginTransaction();
    try {
        foreach ($manifest['newsletters'] as $row) {
            if (!$select('SELECT id FROM newsletters WHERE year=? AND month=?',[$row['year'],$row['month']])) {
                unset($row['sha256']); $row['is_current']=0;
                $insert('newsletters',$row); $counts['newsletters_inserted']++;
            }
        }
        foreach ($manifest['legacy_archive'] ?? [] as $row) {
            if ($select('SELECT id FROM newsletters WHERE year=? AND month=?',[$row['year'],$row['month']])) continue;
            $file=publishedNewsletterFile($root,$row['file_path']);
            if (!$file) { $counts['legacy_archive_missing']++; continue; }
            $row['file_size']=filesize($file); $row['is_current']=0;
            $insert('newsletters',$row); $counts['legacy_archive_inserted']++;
        }
        $latest=$manifest['newsletters'];
        usort($latest,function ($a,$b) { return ($b['year']*100+$b['month']) <=> ($a['year']*100+$a['month']); });
        $target=$latest[0];
        $current=$pdo->query('SELECT year,month FROM newsletters WHERE is_current=1 ORDER BY year DESC,month DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if (!$current || $current['year']*100+$current['month'] <= $target['year']*100+$target['month']) {
            $pdo->exec('UPDATE newsletters SET is_current=0 WHERE is_current=1');
            $stmt=$pdo->prepare('UPDATE newsletters SET is_current=1 WHERE year=? AND month=?');
            $stmt->execute([$target['year'],$target['month']]);
        }
        foreach ($manifest['documents'] as $order=>$row) {
            $found=$select('SELECT * FROM official_documents WHERE file_path=?',[$row['file_path']]);
            if (!$found) $found=$select('SELECT * FROM official_documents WHERE doc_type=?',[$row['doc_type']]);
            if ($found) {
                $stmt=$pdo->prepare('UPDATE official_documents SET title=?,filename=?,file_path=?,file_size=? WHERE id=?');
                $stmt->execute([$row['title'],$row['filename'],$row['file_path'],$row['file_size'],$found['id']]);
            } else {
                unset($row['sha256']); $row['sort_order']=$order+1; $row['is_active']=1;
                $insert('official_documents',$row);
            }
            $counts['documents_updated']++;
        }
        $counts['private_document_records_hidden']=$pdo->exec("UPDATE official_documents SET is_active=0 WHERE file_path LIKE '%delvallenotify%' AND is_active=1");
        foreach ($manifest['contacts'] as $row) {
            $found=$row['email_user']
                ? $select('SELECT * FROM contacts WHERE category=? AND email_user=?',[$row['category'],$row['email_user']])
                : $select('SELECT * FROM contacts WHERE category=? AND position=?',[$row['category'],$row['position']]);
            if (!$found) {
                $row['is_active']=1; $insert('contacts',$row); $counts['contacts_inserted']++;
            } elseif ($found['name'] !== $row['name'] || $found['position'] !== $row['position']) {
                if (($found['updated_at'] ?? '') > $manifest['contacts_source_date'].' 23:59:59') {
                    $counts['contacts_preserved_newer']++; continue;
                }
                $stmt=$pdo->prepare('UPDATE contacts SET name=?,position=?,email_domain=? WHERE id=?');
                $stmt->execute([$row['name'],$row['position'],$row['email_domain'],$found['id']]);
                $counts['contacts_updated']++;
            }
        }
        foreach ($manifest['calendar'] as $row) {
            if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$row['date'] ?? '')) continue;
            if (!$select('SELECT id FROM calendar_cache WHERE event_date=? AND title=?',[$row['date'],$row['title']])) {
                $insert('calendar_cache',['event_id'=>'published-'.hash('sha256',$row['date'].$row['title']),
                    'event_date'=>$row['date'],'event_time'=>$row['time'] ?? '', 'title'=>$row['title'],
                    'description'=>$row['description'] ?? '', 'location'=>$row['location'] ?? '']);
                $counts['calendar_inserted']++;
            }
        }
        $meeting=$manifest['meeting'];
        if (!$select('SELECT id FROM meetings WHERE meeting_type=? AND meeting_date=?',[$meeting['meeting_type'],$meeting['meeting_date']])) {
            $meeting['is_active']=1; $insert('meetings',$meeting); $counts['meetings_inserted']++;
        }
        $audit=$pdo->prepare('INSERT INTO audit_log (action,table_name,new_value) VALUES (?,?,?)');
        $audit->execute(['sync_published_content','beta_content',json_encode($counts)]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
    return ['operation'=>'applied','counts'=>$counts,'inserted_ids'=>$inserted,
        'current_newsletter'=>$pdo->query('SELECT year,month,file_path FROM newsletters WHERE is_current=1')->fetchAll(PDO::FETCH_ASSOC),
        'notification_public_records'=>(int)$pdo->query("SELECT COUNT(*) FROM official_documents WHERE file_path LIKE '%delvallenotify%' AND is_active=1")->fetchColumn()];
}
