<?php
/** Metadata-only migration from verified live FTP files. Never publishes a listing. */
function legacyAssetFile(string $root, string $path): string {
    if ($path === '' || preg_match('~[\\\\\x00-\x1f\x7f]~', $path)
        || preg_match('~(?:^|/)\.[^/]*~', $path) || strpos($path, ':') !== false
        || stripos($path, 'delvallenotify') !== false || $path[0] === '/') {
        throw new RuntimeException('Invalid archive path');
    }
    $parts = explode('/', $path);
    if (count($parts) > 1 && !in_array($parts[0], ['newsletters','pdfs','images','talks','ron','don_dossa','ken','barcroft','gert'], true)) {
        throw new RuntimeException('Archive folder is not allowed');
    }
    if (!preg_match('~\.(?:pdf|docx?|xlsx?|pptx?|od[ts]|jpe?g|png|gif|webp|tiff?|bmp|svg|html?|shtml)$~i', $path)) {
        throw new RuntimeException('Archive file type is not allowed');
    }
    $base = realpath($root);
    $file = realpath($root.'/'.$path);
    if (!$base || !$file || !is_file($file) || strncmp($file, $base.DIRECTORY_SEPARATOR, strlen($base)+1) !== 0) {
        throw new RuntimeException('Archive file missing or outside source root: '.$path);
    }
    return $file;
}

function validateLegacyAssets(array $manifest, string $root, bool $verifyHashes = true): void {
    if (($manifest['version'] ?? null) !== 1 || empty($manifest['assets']) || !isset($manifest['newsletters'])
        || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $manifest['imported_at_utc'] ?? '')) {
        throw new RuntimeException('Invalid archive manifest');
    }
    $seen = [];
    foreach ($manifest['assets'] as $row) {
        $path = $row['file_path'];
        if (isset($seen[$path])) throw new RuntimeException('Duplicate archive path');
        $seen[$path] = true;
        $file = legacyAssetFile($root, $path);
        if (!in_array($row['asset_kind'], ['document','picture','newsletter_asset'], true)
            || !preg_match('/^[a-f0-9]{64}$/', $row['sha256'])
            || filesize($file) !== $row['file_size']
            || ($verifyHashes && !hash_equals($row['sha256'], hash_file('sha256', $file)))
            || (!$verifyHashes && gmdate('c', filemtime($file)) !== $row['modified_utc'])) {
            throw new RuntimeException('Archive content changed: '.$path);
        }
    }
    $issues = [];
    foreach ($manifest['newsletters'] as $row) {
        $key = $row['year'].'-'.$row['month'];
        if (isset($issues[$key]) || !isset($seen[$row['file_path']])
            || $row['year'] < 1900 || $row['year'] > 2100 || $row['month'] < 1 || $row['month'] > 12
            || !preg_match('~^newsletters/([0-9]{4})/(?:tvsnews([0-9]{2})([0-9]{2})\.pdf|([0-9]{2})([0-9]{2})/index\.html)$~', $row['file_path'], $m)
            || (int)$m[1] !== $row['year']) throw new RuntimeException('Invalid newsletter issue');
        $month = (int)($m[2] ?: $m[4]);
        $year = (int)($m[3] ?: $m[5]);
        if ($month !== $row['month'] || $year !== $row['year'] % 100) throw new RuntimeException('Newsletter date mismatch');
        $issues[$key] = true;
    }
}

function legacyAssetsTableExists(PDO $pdo): bool {
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
        return (bool)$pdo->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='legacy_assets'")->fetchColumn();
    }
    return (bool)$pdo->query("SHOW TABLES LIKE 'legacy_assets'")->fetchColumn();
}

function legacyAssetsSnapshot(PDO $pdo): array {
    return [
        'legacy_assets' => legacyAssetsTableExists($pdo) ? $pdo->query('SELECT * FROM legacy_assets ORDER BY path_hash')->fetchAll(PDO::FETCH_ASSOC) : null,
        'newsletters' => $pdo->query('SELECT * FROM newsletters ORDER BY year,month')->fetchAll(PDO::FETCH_ASSOC)
    ];
}

function legacyAssetsFingerprint(array $snapshot): string {
    return hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR));
}

function createLegacyAssetsTable(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS legacy_assets (
        path_hash CHAR(64) PRIMARY KEY,
        file_path TEXT NOT NULL,
        asset_kind VARCHAR(32) NOT NULL,
        file_size BIGINT NOT NULL,
        sha256 CHAR(64) NOT NULL,
        modified_utc VARCHAR(32) NOT NULL,
        migration_id VARCHAR(100) NOT NULL,
        is_listed SMALLINT NOT NULL DEFAULT 0
    )".($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin' : ''));
}

/** Caller must validate files once, back up this snapshot, and own the transaction. */
function importLegacyAssets(PDO $pdo, array $manifest): array {
    if (!$pdo->inTransaction()) throw new RuntimeException('Archive import requires a transaction');
    $counts = ['assets_inserted'=>0,'assets_unchanged'=>0,'newsletters_inserted'=>0,'newsletters_preserved'=>0];
    $find = $pdo->prepare('SELECT * FROM legacy_assets WHERE path_hash=?');
    $insert = $pdo->prepare('INSERT INTO legacy_assets (path_hash,file_path,asset_kind,file_size,sha256,modified_utc,migration_id,is_listed) VALUES (?,?,?,?,?,?,?,0)');
    foreach ($manifest['assets'] as $row) {
        $hash = hash('sha256', $row['file_path']);
        $find->execute([$hash]); $existing = $find->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            foreach (['file_path','asset_kind','file_size','sha256','modified_utc'] as $field) {
                if ((string)$existing[$field] !== (string)$row[$field]) throw new RuntimeException('Existing asset differs; explicit reconciliation needed: '.$row['file_path']);
            }
            $counts['assets_unchanged']++;
        } else {
            $insert->execute([$hash,$row['file_path'],$row['asset_kind'],$row['file_size'],$row['sha256'],$row['modified_utc'],$manifest['migration_id']]);
            $counts['assets_inserted']++;
        }
    }
    $findIssue = $pdo->prepare('SELECT file_path FROM newsletters WHERE year=? AND month=?');
    $insertIssue = $pdo->prepare('INSERT INTO newsletters (year,month,filename,file_path,file_type,file_size,uploaded_at,is_current) VALUES (?,?,?,?,?,?,?,0)');
    foreach ($manifest['newsletters'] as $row) {
        $findIssue->execute([$row['year'],$row['month']]); $existing = $findIssue->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            if ($existing['file_path'] !== $row['file_path']) throw new RuntimeException('Conflicting newsletter issue');
            $counts['newsletters_preserved']++;
        } else {
            $insertIssue->execute([$row['year'],$row['month'],basename($row['file_path']),$row['file_path'],$row['file_type'],$row['file_size'],$manifest['imported_at_utc']]);
            $counts['newsletters_inserted']++;
        }
    }
    return $counts;
}
