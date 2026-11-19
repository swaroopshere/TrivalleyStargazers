<?php
// Run from a private shell. The HTTP route is deliberately unavailable.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/legacy_asset_migration.php';
$options = getopt('', ['manifest:','root:','backup:','expected:','apply']);
try {
    if (empty($options['manifest']) || empty($options['root'])) throw new RuntimeException('Supply --manifest and --root (the existing live FTP document root)');
    $manifest = json_decode(file_get_contents($options['manifest']), true, 512, JSON_THROW_ON_ERROR);
    validateLegacyAssets($manifest, $options['root']);
    $pdo = db()->getConnection();
    $snapshot = legacyAssetsSnapshot($pdo); $fingerprint = legacyAssetsFingerprint($snapshot);
    if (!isset($options['apply'])) {
        echo json_encode(['operation'=>'plan','database'=>$pdo->query('SELECT DATABASE()')->fetchColumn(),'fingerprint'=>$fingerprint,'assets'=>count($manifest['assets']),'newsletters'=>count($manifest['newsletters'])], JSON_PRETTY_PRINT)."\n"; exit;
    }
    if (empty($options['backup']) || empty($options['expected']) || !hash_equals($fingerprint,$options['expected'])) throw new RuntimeException('Apply requires a fresh --expected fingerprint and new --backup path');
    $oldMask = umask(0077);
    $backup = fopen($options['backup'], 'x');
    umask($oldMask);
    if (!$backup) throw new RuntimeException('Cannot create private backup');
    $encoded = json_encode($snapshot, JSON_THROW_ON_ERROR);
    if (fwrite($backup, $encoded) !== strlen($encoded)) throw new RuntimeException('Backup incomplete');
    fclose($backup);
    createLegacyAssetsTable($pdo); // MySQL DDL is outside the data transaction.
    $pdo->beginTransaction();
    $pdo->query('SELECT id FROM newsletters ORDER BY id FOR UPDATE')->fetchAll();
    $recheck = legacyAssetsSnapshot($pdo);
    if ($snapshot['legacy_assets'] === null && $recheck['legacy_assets'] === []) $recheck['legacy_assets'] = null;
    if (!hash_equals($fingerprint, legacyAssetsFingerprint($recheck))) throw new RuntimeException('Database changed after backup');
    $result = importLegacyAssets($pdo, $manifest);
    $pdo->commit();
    echo json_encode($result, JSON_PRETTY_PRINT)."\n";
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, $e->getMessage()."\n"); exit(1);
}
