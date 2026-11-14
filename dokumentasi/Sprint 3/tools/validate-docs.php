<?php

/**
 * Validasi struktural berkas .docx Sprint 3.
 *
 * Memastikan setiap dokumen adalah ZIP valid, memuat bagian OOXML wajib,
 * dan seluruh XML/RELS dapat diparse. Hasil ditulis ke evidence/docx-validation.txt.
 */
$baseDir = dirname(__DIR__);
$docxFiles = glob($baseDir.'/*.docx') ?: [];
sort($docxFiles);

$lines = [
    'Validasi berkas OOXML — Sprint 3',
    'Kriteria: ZIP valid, [Content_Types].xml + word/document.xml + _rels/.rels ada, seluruh XML/RELS dapat diparse.',
    '',
];

$required = ['[Content_Types].xml', 'word/document.xml', '_rels/.rels'];
$failed = 0;

foreach ($docxFiles as $file) {
    $name = basename($file);
    $zip = new ZipArchive;

    if ($zip->open($file) !== true) {
        $lines[] = "{$name} | GAGAL | ZIP tidak dapat dibuka";
        $failed++;

        continue;
    }

    $names = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $names[] = $zip->getNameIndex($i);
    }

    $missing = array_values(array_diff($required, $names));
    $xmlErrors = [];
    $media = 0;

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);

        if (str_starts_with($entry, 'word/media/')) {
            $media++;
        }

        if (! preg_match('/\.(xml|rels)$/', $entry)) {
            continue;
        }

        $content = $zip->getFromIndex($i);
        $dom = new DOMDocument;

        if ($content === false || @$dom->loadXML($content) === false) {
            $xmlErrors[] = $entry;
        }
    }

    $zip->close();

    $status = $missing === [] && $xmlErrors === [] ? 'LULUS' : 'GAGAL';
    if ($status === 'GAGAL') {
        $failed++;
    }

    $lines[] = sprintf(
        '%s | %s | media=%d | %.1f KB | missing=%s | xml_error=%s',
        $name,
        $status,
        $media,
        filesize($file) / 1024,
        implode(',', $missing),
        implode(',', $xmlErrors)
    );
}

$lines[] = '';
$lines[] = $failed === 0
    ? 'Seluruh '.count($docxFiles).' dokumen dinyatakan valid dan dapat dibuka sebagai OOXML.'
    : "{$failed} dokumen gagal validasi.";

$output = $baseDir.'/evidence/docx-validation.txt';
file_put_contents($output, implode(PHP_EOL, $lines).PHP_EOL);

echo implode(PHP_EOL, $lines).PHP_EOL;

exit($failed === 0 ? 0 : 1);
