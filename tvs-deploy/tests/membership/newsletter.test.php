<?php
require __DIR__ . '/../../includes/newsletter_archive.php';
$root = sys_get_temp_dir() . '/tvs-archive-test-' . bin2hex(random_bytes(6));
mkdir($root . '/newsletters/2026', 0700, true);
foreach (['01', '08', '09', '10'] as $month) file_put_contents($root . '/newsletters/2026/tvsnews' . $month . '26.pdf', '%PDF-test');
file_put_contents($root . '/newsletters/2026/tvsnews1125.pdf', '%PDF-wrong-year');
try {
    $archive = publishedNewsletterArchive([['year'=>2026,'month'=>1,'file_path'=>'newsletters/2026/tvsnews0126.pdf']], $root);
    if (array_keys($archive[2026]) !== [10,9,8,1]) throw new RuntimeException('FTP-published October or archive entries missing');
    if (count($archive[2026]) !== 4) throw new RuntimeException('Wrong-year PDF included or duplicate January');
    echo "PASS uploaded October, September, August and DB history merge correctly\n";
} finally {
    foreach (glob($root . '/newsletters/2026/*') as $file) unlink($file);
    rmdir($root . '/newsletters/2026'); rmdir($root . '/newsletters'); rmdir($root);
}
