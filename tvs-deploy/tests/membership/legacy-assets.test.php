<?php
require __DIR__.'/../../includes/legacy_asset_migration.php';
function checkAsset($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function rejectsAsset(callable $fn) { try { $fn(); } catch (RuntimeException $e) { return; } throw new RuntimeException('Expected rejection'); }
$root=sys_get_temp_dir().'/tvs-archive-'.bin2hex(random_bytes(6));
foreach (['pdfs','images','newsletters/1996/0396','newsletters/2026'] as $dir) mkdir($root.'/'.$dir,0700,true);
$paths=['pdfs/test.pdf','images/old picture.jpg','newsletters/1996/0396/index.html','newsletters/2026/tvsnews1026.pdf'];
$assets=[];
foreach($paths as $path) {
    file_put_contents($root.'/'.$path,'verified archive '.$path);
    $assets[]=['file_path'=>$path,'asset_kind'=>strpos($path,'newsletters/')===0?'newsletter_asset':(strpos($path,'images/')===0?'picture':'document'),
        'file_size'=>filesize($root.'/'.$path),'sha256'=>hash_file('sha256',$root.'/'.$path),'modified_utc'=>'2026-11-15T00:00:00+00:00'];
}
$manifest=['version'=>1,'migration_id'=>'test-archive','imported_at_utc'=>'2026-11-16 00:00:00','assets'=>$assets,'newsletters'=>[
    ['year'=>1996,'month'=>3,'file_path'=>$paths[2],'file_type'=>'html','file_size'=>$assets[2]['file_size']],
    ['year'=>2026,'month'=>10,'file_path'=>$paths[3],'file_type'=>'pdf','file_size'=>$assets[3]['file_size']]
]];
try {
    validateLegacyAssets($manifest,$root);
    foreach(['../outside.pdf','pdfs/../test.pdf','/pdfs/test.pdf','pdfs/.hidden.pdf','pdfs/delvallenotify.pdf','Maildir/mail.pdf','images/script.php','images\\test.jpg'] as $bad) rejectsAsset(fn()=>legacyAssetFile($root,$bad));
    $bad=$manifest; $bad['assets'][0]['sha256']=str_repeat('0',64); rejectsAsset(fn()=>validateLegacyAssets($bad,$root));
    $bad=$manifest; $bad['assets'][]=$bad['assets'][0]; rejectsAsset(fn()=>validateLegacyAssets($bad,$root));
    $bad=$manifest; $bad['newsletters'][0]['month']=4; rejectsAsset(fn()=>validateLegacyAssets($bad,$root));
    $pdo=new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('CREATE TABLE newsletters(id INTEGER PRIMARY KEY, year INTEGER, month INTEGER, filename TEXT, file_path TEXT, file_type TEXT, file_size INTEGER, uploaded_at TEXT, is_current INTEGER, UNIQUE(year,month))');
    $pdo->exec("INSERT INTO newsletters VALUES(1,2026,10,'tvsnews1026.pdf','newsletters/2026/tvsnews1026.pdf','pdf',10,'2026-11-15 00:00:00',1)");
    $snapshot=legacyAssetsSnapshot($pdo);
    checkAsset($snapshot['legacy_assets']===null && !legacyAssetsTableExists($pdo),'Snapshot must not create a table');
    createLegacyAssetsTable($pdo);
    rejectsAsset(fn()=>importLegacyAssets($pdo,$manifest));
    $pdo->beginTransaction(); $first=importLegacyAssets($pdo,$manifest); $pdo->commit();
    checkAsset($first['assets_inserted']===4 && $first['newsletters_inserted']===1,'Import totals');
    checkAsset((int)$pdo->query('SELECT SUM(is_current) FROM newsletters')->fetchColumn()===1,'Current newsletter preserved');
    checkAsset((int)$pdo->query('SELECT SUM(is_listed) FROM legacy_assets')->fetchColumn()===0,'Import must not publish listings');
    $before=legacyAssetsFingerprint(legacyAssetsSnapshot($pdo));
    $pdo->beginTransaction(); $again=importLegacyAssets($pdo,$manifest); $pdo->commit();
    checkAsset($again['assets_inserted']===0 && $again['newsletters_inserted']===0,'Replay must be idempotent');
    checkAsset($before===legacyAssetsFingerprint(legacyAssetsSnapshot($pdo)),'Replay must not modify data');
    $pdo->exec("UPDATE legacy_assets SET is_listed=1 WHERE asset_kind='picture'");
    $pdo->beginTransaction(); importLegacyAssets($pdo,$manifest); $pdo->commit();
    checkAsset((int)$pdo->query('SELECT SUM(is_listed) FROM legacy_assets')->fetchColumn()===1,'Preserve editorial visibility');
    $bad=$manifest; array_unshift($bad['assets'],array_merge($assets[0],['file_path'=>'pdfs/new.pdf'])); $bad['assets'][2]['sha256']=str_repeat('f',64);
    $pdo->beginTransaction(); rejectsAsset(fn()=>importLegacyAssets($pdo,$bad)); $pdo->rollBack();
    checkAsset((int)$pdo->query('SELECT COUNT(*) FROM legacy_assets')->fetchColumn()===4,'Conflict rollback must remove partial inserts');
    echo "Legacy asset validation, import, replay, visibility and rollback tests passed.\n";
} finally {
    foreach($paths as $path) unlink($root.'/'.$path);
    foreach(['newsletters/1996/0396','newsletters/1996','newsletters/2026','newsletters','pdfs','images',''] as $dir) rmdir($root.($dir?'/'.$dir:''));
}
