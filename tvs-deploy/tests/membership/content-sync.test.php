<?php
require __DIR__.'/../../includes/content_sync.php';
function verifySync(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$root=sys_get_temp_dir().'/tvs-content-test-'.bin2hex(random_bytes(6));
mkdir($root.'/newsletters/2026',0700,true); mkdir($root.'/pdfs');
mkdir($root.'/newsletters/1996/0396',0700,true);
file_put_contents($root.'/newsletters/1996/0396/index.html','Published historical newsletter');
foreach(['newsletters/2026/tvsnews1026.pdf','pdfs/bylaws.pdf'] as $path) file_put_contents($root.'/'.$path,'%PDF-fixture');
$pdo=new PDO('sqlite::memory:'); $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE newsletters(id INTEGER PRIMARY KEY,year INTEGER,month INTEGER,filename TEXT,file_path TEXT,file_type TEXT,file_size INTEGER,is_current INTEGER)');
$pdo->exec('CREATE TABLE official_documents(id INTEGER PRIMARY KEY,doc_type TEXT,title TEXT,filename TEXT,file_path TEXT,file_size INTEGER,sort_order INTEGER,is_active INTEGER)');
$pdo->exec('CREATE TABLE contacts(id INTEGER PRIMARY KEY,category TEXT,position TEXT,name TEXT,email_user TEXT,email_domain TEXT,sort_order INTEGER,is_active INTEGER,updated_at TEXT)');
$pdo->exec('CREATE TABLE calendar_cache(id INTEGER PRIMARY KEY,event_id TEXT,event_date TEXT,event_time TEXT,title TEXT,description TEXT,location TEXT)');
$pdo->exec('CREATE TABLE meetings(id INTEGER PRIMARY KEY,meeting_type TEXT,meeting_date TEXT,is_active INTEGER)');
$pdo->exec('CREATE TABLE audit_log(id INTEGER PRIMARY KEY,action TEXT,table_name TEXT,new_value TEXT)');
$pdo->exec("INSERT INTO newsletters(year,month,is_current) VALUES(2026,1,1)");
$pdo->exec("INSERT INTO contacts(category,position,name,email_user,updated_at) VALUES('officer','President','New admin edit','president','2026-11-14')");
$sha=hash('sha256','%PDF-fixture');
$manifest=['newsletters'=>[['year'=>2026,'month'=>10,'filename'=>'tvsnews1026.pdf','file_path'=>'newsletters/2026/tvsnews1026.pdf','file_type'=>'pdf','file_size'=>12,'sha256'=>$sha]],
    'documents'=>[['doc_type'=>'bylaws','title'=>'Bylaws','filename'=>'bylaws.pdf','file_path'=>'pdfs/bylaws.pdf','file_size'=>12,'sha256'=>$sha]],
    'contacts'=>[['category'=>'officer','position'=>'President','name'=>'Older public name','email_user'=>'president','email_domain'=>'trivalleystargazers.org','sort_order'=>1]],
    'contacts_source_date'=>'2026-06-21','calendar'=>[['date'=>'2026-08-15','title'=>'Public star party','time'=>'20:00:00']],
    'meeting'=>['meeting_type'=>'public','meeting_date'=>'2026-10-16'],
    'legacy_archive'=>[['year'=>1996,'month'=>3,'file_path'=>'newsletters/1996/0396/index.html','filename'=>'index.html','file_type'=>'html']]];
try {
    $plan=syncPublishedContent($pdo,$manifest,$root);
    verifySync((int)$pdo->query('SELECT COUNT(*) FROM newsletters')->fetchColumn()===1,'Plan changed database');
    $invalid=$manifest; $invalid['documents'][0]['file_path']='pdfs/delvallenotify.pdf';
    try { validatePublishedContent($invalid,$root); throw new LogicException('Private document accepted'); } catch(RuntimeException $e) {}
    $bad=$manifest; $bad['meeting']['invalid_column']='must fail';
    try { syncPublishedContent($pdo,$bad,$root,true,$plan['fingerprint']); throw new LogicException('Invalid transaction accepted'); } catch(PDOException $e) {}
    verifySync((int)$pdo->query('SELECT COUNT(*) FROM newsletters')->fetchColumn()===1,'Failed transaction was not rolled back');
    $result=syncPublishedContent($pdo,$manifest,$root,true,$plan['fingerprint']);
    verifySync($result['current_newsletter'][0]['month']==10,'October not current in database');
    verifySync($result['counts']['newsletters_inserted']===1,'Newsletter import failed');
    verifySync($result['counts']['calendar_inserted']===1,'Calendar import failed');
    verifySync($result['counts']['legacy_archive_inserted']===1,'Historical newsletter was not imported');
    verifySync($pdo->query('SELECT name FROM contacts')->fetchColumn()==='New admin edit','Newer admin contact was overwritten');
    verifySync($result['notification_public_records']===0,'Notification exposed');
    $again=syncPublishedContent($pdo,$manifest,$root);
    $replay=syncPublishedContent($pdo,$manifest,$root,true,$again['fingerprint']);
    verifySync($replay['counts']['newsletters_inserted']===0 && $replay['counts']['calendar_inserted']===0 && $replay['counts']['meetings_inserted']===0,'Replay inserted duplicates');
    try { syncPublishedContent($pdo,$manifest,$root,true,$plan['fingerprint']); throw new LogicException('Stale backup accepted'); } catch(RuntimeException $e) {}
    echo "PASS content sync: dry run, hash validation, rollback, current issue, document privacy, admin-edit preservation, idempotency and stale-backup protection\n";
} finally {
    foreach(['newsletters/2026/tvsnews1026.pdf','pdfs/bylaws.pdf'] as $path) unlink($root.'/'.$path);
    unlink($root.'/newsletters/1996/0396/index.html'); rmdir($root.'/newsletters/1996/0396'); rmdir($root.'/newsletters/1996');
    rmdir($root.'/newsletters/2026'); rmdir($root.'/newsletters'); rmdir($root.'/pdfs'); rmdir($root);
}
