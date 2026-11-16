<?php
/** Merge uploaded PDFs with database history without changing admin records. */
function publishedNewsletterArchive(array $history, string $root): array {
    $archive = [];
    foreach ($history as $entry) {
        if (!empty($entry['file_path']) && is_file($root . '/' . $entry['file_path'])) {
            $archive[(int)$entry['year']][(int)$entry['month']] = $entry;
        }
    }
    foreach (glob($root . '/newsletters/[12][0-9][0-9][0-9]/tvsnews*.pdf') ?: [] as $file) {
        if (!preg_match('~/([12][0-9]{3})/tvsnews(0[1-9]|1[0-2])([0-9]{2})\.pdf$~', str_replace('\\', '/', $file), $m)) continue;
        if (substr($m[1], -2) !== $m[3] || filesize($file) === 0) continue;
        $year = (int)$m[1]; $month = (int)$m[2];
        $archive[$year][$month] = ['year' => $year, 'month' => $month,
            'file_path' => 'newsletters/' . $m[1] . '/' . basename($file), 'filename' => basename($file), 'file_type' => 'pdf'];
    }
    krsort($archive, SORT_NUMERIC);
    foreach ($archive as &$issues) krsort($issues, SORT_NUMERIC);
    unset($issues);
    return $archive;
}
