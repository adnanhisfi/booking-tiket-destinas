<?php

/**
 * Pembuat dokumen .docx minimal berbasis OOXML + ZipArchive.
 * Dirancang khusus untuk dokumentasi Sprint 3 JelajahKu.
 */
class DocxBuilder
{
    private string $body = '';

    /** @var array<int, array{path:string, rel:string, name:string}> */
    private array $images = [];

    private int $docPr = 100;

    private int $imageCounter = 0;

    public function __construct(
        private string $code,
        private string $title,
        private string $subtitle,
        private array $meta = [],
        private string $footerLabel = '',
    ) {}

    public function cover(): void
    {
        $this->spacer(1200);
        $this->p('DOKUMENTASI SPRINT 3', [
            'size' => 20, 'bold' => true, 'color' => '2E7D32', 'spacing' => 60, 'after' => 160,
        ]);
        $this->p($this->title, [
            'size' => 56, 'bold' => true, 'color' => '14532D', 'after' => 140, 'line' => 240,
        ]);
        $this->p($this->subtitle, [
            'size' => 26, 'color' => '4B5563', 'after' => 300, 'line' => 300,
        ]);
        $this->rule();
        $this->spacer(160);

        $rows = [];
        foreach ($this->meta as $label => $value) {
            $rows[] = [$label, $value];
        }
        $this->table([], $rows, [2700, 6938], ['fontSize' => 19, 'firstColBold' => true, 'firstColFill' => 'E8F5E9']);

        $this->spacer(260);
        $this->callout(
            'Status pekerjaan',
            'SELESAI — implementasi, pengujian otomatis, dan verifikasi langsung telah dilakukan. Seluruh artefak bukti terlampir pada dokumen ini.',
            'E8F5E9',
            '2E7D32'
        );
        $this->spacer(900);
        $this->p('JelajahKu — Tiket Wisata, Cerita Baru', [
            'size' => 20, 'bold' => true, 'color' => '2E7D32', 'after' => 40, 'align' => 'center',
        ]);
        $this->p('Disusun sebagai bukti artefak Sprint 3', [
            'size' => 17, 'color' => '6B7280', 'align' => 'center',
        ]);
        $this->pageBreak();
    }

    public function h1(string $text): void
    {
        $this->body .= '<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr>'.$this->inline($text, ['bold' => true]).'</w:p>';
    }

    public function h2(string $text): void
    {
        $this->body .= '<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr>'.$this->inline($text, ['bold' => true]).'</w:p>';
    }

    public function h3(string $text): void
    {
        $this->body .= '<w:p><w:pPr><w:pStyle w:val="Heading3"/></w:pPr>'.$this->inline($text, ['bold' => true]).'</w:p>';
    }

    public function p(string $text, array $opts = []): void
    {
        $this->body .= $this->paragraphXml($text, $opts);
    }

    public function bullets(array $items, int $level = 0): void
    {
        foreach ($items as $item) {
            $indent = 360 + ($level * 300);
            $this->body .= $this->paragraphXml('•   '.$item, [
                'indent' => $indent,
                'hanging' => 220,
                'after' => 50,
            ]);
        }
    }

    public function numbered(array $items, int $start = 1): void
    {
        $n = $start;
        foreach ($items as $item) {
            $this->body .= $this->paragraphXml($n.'.  '.$item, [
                'indent' => 380,
                'hanging' => 240,
                'after' => 50,
            ]);
            $n++;
        }
    }

    public function code(string $code, string $caption = ''): void
    {
        $code = str_replace(["\r\n", "\r", "\t"], ["\n", "\n", '    '], $code);
        $code = trim($code, "\n");
        $lines = explode("\n", $code);

        $runProps = '<w:rFonts w:ascii="Consolas" w:hAnsi="Consolas" w:cs="Consolas"/>'
            .'<w:color w:val="1F2937"/><w:sz w:val="17"/><w:szCs w:val="17"/>';

        $runs = '';
        foreach ($lines as $i => $line) {
            if ($i > 0) {
                $runs .= '<w:r><w:br/></w:r>';
            }
            $runs .= '<w:r><w:rPr>'.$runProps.'</w:rPr><w:t xml:space="preserve">'.$this->e($line).'</w:t></w:r>';
        }

        $this->body .= '<w:p><w:pPr>'
            .'<w:pBdr><w:left w:val="single" w:sz="18" w:space="8" w:color="2E7D32"/></w:pBdr>'
            .'<w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/>'
            .'<w:spacing w:before="40" w:after="140" w:line="235" w:lineRule="auto"/>'
            .'<w:ind w:left="170" w:right="170"/>'
            .'</w:pPr>'.$runs.'</w:p>';

        if ($caption !== '') {
            $this->caption($caption);
        }
    }

    public function caption(string $text): void
    {
        $this->body .= '<w:p><w:pPr><w:pStyle w:val="Caption"/></w:pPr>'
            .'<w:r><w:rPr><w:i/><w:color w:val="6B7280"/><w:sz w:val="17"/></w:rPr><w:t xml:space="preserve">'.$this->e($text).'</w:t></w:r></w:p>';
    }

    public function image(string $path, int $widthTwips = 8800, string $caption = ''): void
    {
        if (! is_file($path)) {
            throw new RuntimeException('Gambar tidak ditemukan: '.$path);
        }

        $info = getimagesize($path);
        $ratio = $info ? ($info[1] / $info[0]) : 0.62;
        $heightTwips = (int) round($widthTwips * $ratio);
        $cx = $widthTwips * 635;
        $cy = $heightTwips * 635;

        $this->imageCounter++;
        $name = 'image'.$this->imageCounter.'.png';
        $rel = 'rIdImg'.$this->imageCounter;
        $this->images[] = ['path' => $path, 'rel' => $rel, 'name' => $name];
        $id = $this->docPr++;

        $drawing = '<w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0">'
            .'<wp:extent cx="'.$cx.'" cy="'.$cy.'"/>'
            .'<wp:effectExtent l="0" t="0" r="0" b="0"/>'
            .'<wp:docPr id="'.$id.'" name="Picture '.$id.'"/>'
            .'<wp:cNvGraphicFramePr><a:graphicFrameLocks noChangeAspect="1"/></wp:cNvGraphicFramePr>'
            .'<a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            .'<pic:pic>'
            .'<pic:nvPicPr><pic:cNvPr id="'.$id.'" name="'.$name.'"/><pic:cNvPicPr/></pic:nvPicPr>'
            .'<pic:blipFill><a:blip r:embed="'.$rel.'"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
            .'<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="'.$cx.'" cy="'.$cy.'"/></a:xfrm>'
            .'<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'
            .'</pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing>';

        $this->body .= '<w:p><w:pPr><w:spacing w:before="120" w:after="60"/><w:jc w:val="center"/></w:pPr>'
            .'<w:r>'.$drawing.'</w:r></w:p>';

        if ($caption !== '') {
            $this->caption($caption);
        }
    }

    public function table(array $headers, array $rows, ?array $widths = null, array $opts = []): void
    {
        $cols = $headers !== [] ? count($headers) : (count($rows[0] ?? []) ?: 1);
        if ($widths === null) {
            $widths = array_fill(0, $cols, (int) floor(9638 / max(1, $cols)));
        }
        $total = array_sum($widths);
        if ($total !== 9638) {
            $widths[count($widths) - 1] += 9638 - $total;
        }

        $fontSize = $opts['fontSize'] ?? 18;
        $headerFill = $opts['headerFill'] ?? '1B5E20';
        $firstColBold = $opts['firstColBold'] ?? false;
        $firstColFill = $opts['firstColFill'] ?? null;
        $align = $opts['align'] ?? 'left';

        $xml = '<w:tbl><w:tblPr>'
            .'<w:tblW w:w="9638" w:type="dxa"/>'
            .'<w:tblBorders>'
            .'<w:top w:val="single" w:sz="4" w:space="0" w:color="D1D5DB"/>'
            .'<w:left w:val="single" w:sz="4" w:space="0" w:color="D1D5DB"/>'
            .'<w:bottom w:val="single" w:sz="4" w:space="0" w:color="D1D5DB"/>'
            .'<w:right w:val="single" w:sz="4" w:space="0" w:color="D1D5DB"/>'
            .'<w:insideH w:val="single" w:sz="4" w:space="0" w:color="D1D5DB"/>'
            .'<w:insideV w:val="single" w:sz="4" w:space="0" w:color="D1D5DB"/>'
            .'</w:tblBorders>'
            .'<w:tblLayout w:type="fixed"/>'
            .'<w:tblCellMar><w:top w:w="70" w:type="dxa"/><w:left w:w="90" w:type="dxa"/>'
            .'<w:bottom w:w="70" w:type="dxa"/><w:right w:w="90" w:type="dxa"/></w:tblCellMar>'
            .'</w:tblPr><w:tblGrid>';

        foreach ($widths as $w) {
            $xml .= '<w:gridCol w:w="'.$w.'"/>';
        }
        $xml .= '</w:tblGrid>';

        if ($headers !== []) {
            $xml .= '<w:tr><w:trPr><w:cantSplit/><w:tblHeader/></w:trPr>';
            foreach ($headers as $i => $header) {
                $xml .= $this->cellXml((string) $header, $widths[$i], $fontSize, true, $headerFill, 'FFFFFF', $align);
            }
            $xml .= '</w:tr>';
        }

        foreach ($rows as $rowIndex => $row) {
            $xml .= '<w:tr><w:trPr><w:cantSplit/></w:trPr>';
            foreach (array_values($row) as $i => $cell) {
                $fill = null;
                $bold = false;
                if ($firstColBold && $i === 0) {
                    $bold = true;
                    $fill = $firstColFill;
                }
                if ($rowIndex % 2 === 1 && $fill === null) {
                    $fill = 'F9FAFB';
                }
                $color = '1F2937';
                $text = (string) $cell;
                if (preg_match('/^(Selesai|Lulus|Berhasil|Terpenuhi|Aman|OK)\b/u', $text)) {
                    $color = '1B5E20';
                    $bold = true;
                } elseif (preg_match('/^(Belum|Gagal|Tidak)\b/u', $text)) {
                    $color = 'B91C1C';
                    $bold = true;
                }
                $xml .= $this->cellXml($text, $widths[$i], $fontSize, $bold, $fill, $color, $align, $i === 0 && $firstColBold);
            }
            $xml .= '</w:tr>';
        }

        $xml .= '</w:tbl>';
        // Beri jarak setelah tabel agar tidak menempel ke paragraf berikutnya.
        $this->body .= $xml.'<w:p><w:pPr><w:spacing w:after="0" w:line="120" w:lineRule="exact"/></w:pPr></w:p>';
    }

    public function callout(string $title, string $text, string $fill = 'E8F5E9', string $border = '2E7D32'): void
    {
        $xml = '<w:tbl><w:tblPr><w:tblW w:w="9638" w:type="dxa"/>'
            .'<w:tblBorders>'
            .'<w:top w:val="single" w:sz="4" w:space="0" w:color="'.$border.'"/>'
            .'<w:left w:val="single" w:sz="18" w:space="0" w:color="'.$border.'"/>'
            .'<w:bottom w:val="single" w:sz="4" w:space="0" w:color="'.$border.'"/>'
            .'<w:right w:val="single" w:sz="4" w:space="0" w:color="'.$border.'"/>'
            .'</w:tblBorders>'
            .'<w:tblLayout w:type="fixed"/>'
            .'<w:tblCellMar><w:top w:w="160" w:type="dxa"/><w:left w:w="200" w:type="dxa"/>'
            .'<w:bottom w:w="160" w:type="dxa"/><w:right w:w="200" w:type="dxa"/></w:tblCellMar>'
            .'</w:tblPr><w:tblGrid><w:gridCol w:w="9638"/></w:tblGrid>'
            .'<w:tr><w:tc><w:tcPr><w:tcW w:w="9638" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="'.$fill.'"/></w:tcPr>'
            .'<w:p><w:pPr><w:spacing w:after="60"/></w:pPr>'
            .$this->inline($title, ['bold' => true, 'color' => $border, 'size' => 20]).'</w:p>'
            .'<w:p><w:pPr><w:spacing w:after="0"/></w:pPr>'.$this->inline($text, ['color' => '374151', 'size' => 19]).'</w:p>'
            .'</w:tc></w:tr></w:tbl>';
        $this->body .= $xml.'<w:p><w:pPr><w:spacing w:after="0" w:line="120" w:lineRule="exact"/></w:pPr></w:p>';
    }

    public function pageBreak(): void
    {
        $this->body .= '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
    }

    public function spacer(int $twips = 200): void
    {
        $this->body .= '<w:p><w:pPr><w:spacing w:before="0" w:after="0" w:line="'.$twips.'" w:lineRule="exact"/></w:pPr></w:p>';
    }

    public function save(string $path): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (is_file($path)) {
            unlink($path);
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat '.$path);
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('docProps/core.xml', $this->coreProps());
        $zip->addFromString('docProps/app.xml', $this->appProps());
        $zip->addFromString('word/document.xml', $this->documentXml());
        $zip->addFromString('word/styles.xml', $this->stylesXml());
        $zip->addFromString('word/header1.xml', $this->headerXml());
        $zip->addFromString('word/footer1.xml', $this->footerXml());
        $zip->addFromString('word/_rels/document.xml.rels', $this->documentRels());

        foreach ($this->images as $image) {
            $zip->addFile($image['path'], 'word/media/'.$image['name']);
        }

        $zip->close();
    }

    private function paragraphXml(string $text, array $opts = []): string
    {
        $style = $opts['style'] ?? null;
        $align = $opts['align'] ?? null;
        $after = $opts['after'] ?? null;
        $before = $opts['before'] ?? null;
        $indent = $opts['indent'] ?? null;
        $hanging = $opts['hanging'] ?? null;
        $keepNext = $opts['keepNext'] ?? false;
        $pageBreakBefore = $opts['pageBreakBefore'] ?? false;
        $shading = $opts['shading'] ?? null;
        $borderLeft = $opts['borderLeft'] ?? null;

        $pPr = '<w:pPr>';
        if ($style) {
            $pPr .= '<w:pStyle w:val="'.$style.'"/>';
        }
        if ($keepNext) {
            $pPr .= '<w:keepNext/>';
        }
        if ($pageBreakBefore) {
            $pPr .= '<w:pageBreakBefore/>';
        }
        if ($borderLeft) {
            $pPr .= '<w:pBdr><w:left w:val="single" w:sz="18" w:space="8" w:color="'.$borderLeft.'"/></w:pBdr>';
        }
        if ($shading) {
            $pPr .= '<w:shd w:val="clear" w:color="auto" w:fill="'.$shading.'"/>';
        }
        if ($before !== null || $after !== null) {
            $pPr .= '<w:spacing';
            if ($before !== null) {
                $pPr .= ' w:before="'.$before.'"';
            }
            if ($after !== null) {
                $pPr .= ' w:after="'.$after.'"';
            }
            $pPr .= '/>';
        }
        if ($indent !== null) {
            $pPr .= '<w:ind w:left="'.$indent.'"'.($hanging !== null ? ' w:hanging="'.$hanging.'"' : '').'/>';
        }
        if ($align) {
            $pPr .= '<w:jc w:val="'.$align.'"/>';
        }
        $pPr .= '</w:pPr>';

        return '<w:p>'.$pPr.$this->inline($text, $opts).'</w:p>';
    }

    private function inline(string $text, array $opts = []): string
    {
        $size = $opts['size'] ?? null;
        $color = $opts['color'] ?? null;
        $boldAll = $opts['bold'] ?? false;
        $italic = $opts['italic'] ?? false;

        $base = '';
        if ($boldAll) {
            $base .= '<w:b/>';
        }
        if ($italic) {
            $base .= '<w:i/>';
        }
        if ($color) {
            $base .= '<w:color w:val="'.$color.'"/>';
        }
        if ($size) {
            $base .= '<w:sz w:val="'.$size.'"/><w:szCs w:val="'.$size.'"/>';
        }

        $parts = preg_split('/(\*\*[^*]+\*\*|`[^`]+`)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $xml = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (str_starts_with($part, '**') && str_ends_with($part, '**')) {
                $inner = substr($part, 2, -2);
                $boldBase = $boldAll ? $base : '<w:b/>'.$base;
                $xml .= '<w:r><w:rPr>'.$boldBase.($color ? '' : '<w:color w:val="14532D"/>').'</w:rPr>'
                    .'<w:t xml:space="preserve">'.$this->e($inner).'</w:t></w:r>';
            } elseif (str_starts_with($part, '`') && str_ends_with($part, '`')) {
                $inner = substr($part, 1, -1);
                $xml .= '<w:r><w:rPr><w:rFonts w:ascii="Consolas" w:hAnsi="Consolas"/>'
                    .'<w:color w:val="0F766E"/>'
                    .($size ? '<w:sz w:val="'.max(14, $size - 2).'"/><w:szCs w:val="'.max(14, $size - 2).'"/>' : '')
                    .'<w:shd w:val="clear" w:color="auto" w:fill="F1F5F9"/>'
                    .'</w:rPr><w:t xml:space="preserve">'.$this->e($inner).'</w:t></w:r>';
            } else {
                $lines = preg_split("/\n/", $part);
                foreach ($lines as $index => $line) {
                    if ($index > 0) {
                        $xml .= '<w:r><w:br/></w:r>';
                    }
                    $xml .= '<w:r><w:rPr>'.$base.'</w:rPr><w:t xml:space="preserve">'.$this->e($line).'</w:t></w:r>';
                }
            }
        }

        return $xml;
    }

    private function cellXml(
        string $text,
        int $width,
        int $fontSize,
        bool $bold = false,
        ?string $fill = null,
        string $color = '1F2937',
        string $align = 'left',
        bool $label = false,
    ): string {
        $tcPr = '<w:tcPr><w:tcW w:w="'.$width.'" w:type="dxa"/>';
        if ($fill) {
            $tcPr .= '<w:shd w:val="clear" w:color="auto" w:fill="'.$fill.'"/>';
        }
        $tcPr .= '<w:vAlign w:val="center"/></w:tcPr>';

        $paragraphs = '';
        foreach (explode("\n", $text) as $line) {
            $paragraphs .= '<w:p><w:pPr><w:spacing w:before="0" w:after="0" w:line="240" w:lineRule="auto"/>'
                .'<w:jc w:val="'.$align.'"/></w:pPr>'
                .$this->inline($line, ['bold' => $bold, 'color' => $color, 'size' => $fontSize])
                .'</w:p>';
        }

        return '<w:tc>'.$tcPr.$paragraphs.'</w:tc>';
    }

    private function rule(): void
    {
        $this->body .= '<w:p><w:pPr><w:pBdr><w:bottom w:val="single" w:sz="12" w:space="1" w:color="2E7D32"/></w:pBdr>'
            .'<w:spacing w:before="0" w:after="240"/></w:pPr></w:p>';
    }

    private function documentXml(): string
    {
        $sectPr = '<w:sectPr>'
            .'<w:headerReference w:type="default" r:id="rIdHeader1"/>'
            .'<w:footerReference w:type="default" r:id="rIdFooter1"/>'
            .'<w:pgSz w:w="11906" w:h="16838"/>'
            .'<w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134" w:header="680" w:footer="680" w:gutter="0"/>'
            .'<w:cols w:space="708"/>'
            .'<w:titlePg/>'
            .'<w:docGrid w:linePitch="360"/></w:sectPr>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
            .'xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" '
            .'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" '
            .'xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            .'<w:body>'.$this->body.$sectPr.'</w:body></w:document>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:eastAsia="Calibri" w:cs="Calibri"/>'
            .'<w:sz w:val="22"/><w:szCs w:val="22"/><w:color w:val="1F2937"/><w:lang w:val="id-ID"/></w:rPr></w:rPrDefault>'
            .'<w:pPrDefault><w:pPr><w:spacing w:after="120" w:line="276" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults>'
            .'<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:qFormat/></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/>'
            .'<w:next w:val="Normal"/><w:qFormat/><w:pPr><w:keepNext/>'
            .'<w:pBdr><w:bottom w:val="single" w:sz="8" w:space="4" w:color="A7D3A8"/></w:pBdr>'
            .'<w:spacing w:before="360" w:after="160"/><w:outlineLvl w:val="0"/></w:pPr>'
            .'<w:rPr><w:b/><w:color w:val="14532D"/><w:sz w:val="32"/><w:szCs w:val="32"/></w:rPr></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/>'
            .'<w:next w:val="Normal"/><w:qFormat/><w:pPr><w:keepNext/><w:spacing w:before="280" w:after="100"/><w:outlineLvl w:val="1"/></w:pPr>'
            .'<w:rPr><w:b/><w:color w:val="2E7D32"/><w:sz w:val="26"/><w:szCs w:val="26"/></w:rPr></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Heading3"><w:name w:val="heading 3"/><w:basedOn w:val="Normal"/>'
            .'<w:next w:val="Normal"/><w:qFormat/><w:pPr><w:keepNext/><w:spacing w:before="200" w:after="80"/><w:outlineLvl w:val="2"/></w:pPr>'
            .'<w:rPr><w:b/><w:color w:val="166534"/><w:sz w:val="23"/><w:szCs w:val="23"/></w:rPr></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Caption"><w:name w:val="caption"/><w:basedOn w:val="Normal"/>'
            .'<w:pPr><w:spacing w:before="40" w:after="120"/><w:jc w:val="center"/></w:pPr>'
            .'<w:rPr><w:i/><w:color w:val="6B7280"/><w:sz w:val="17"/></w:rPr></w:style>'
            .'</w:styles>';
    }

    private function headerXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:hdr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:p><w:pPr>'
            .'<w:pBdr><w:bottom w:val="single" w:sz="4" w:space="4" w:color="D1D5DB"/></w:pBdr>'
            .'<w:tabs><w:tab w:val="right" w:pos="9638"/></w:tabs>'
            .'<w:spacing w:after="0"/></w:pPr>'
            .'<w:r><w:rPr><w:color w:val="2E7D32"/><w:b/><w:sz w:val="16"/></w:rPr><w:t>JelajahKu — Dokumentasi Sprint 3</w:t></w:r>'
            .'<w:r><w:tab/></w:r>'
            .'<w:r><w:rPr><w:color w:val="6B7280"/><w:sz w:val="16"/></w:rPr><w:t>'.$this->e($this->code).'</w:t></w:r>'
            .'</w:p></w:hdr>';
    }

    private function footerXml(): string
    {
        $label = $this->footerLabel !== '' ? $this->footerLabel : $this->code;
        $props = '<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:color w:val="6B7280"/><w:sz w:val="16"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:ftr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:p><w:pPr><w:spacing w:before="0" w:after="0"/><w:jc w:val="center"/></w:pPr>'
            .'<w:r><w:rPr>'.$props.'</w:rPr><w:t xml:space="preserve">'.$this->e($label).'  ·  Halaman </w:t></w:r>'
            .'<w:r><w:fldChar w:fldCharType="begin"/></w:r>'
            .'<w:r><w:instrText xml:space="preserve"> PAGE </w:instrText></w:r>'
            .'<w:r><w:fldChar w:fldCharType="separate"/></w:r><w:r><w:t>1</w:t></w:r>'
            .'<w:r><w:fldChar w:fldCharType="end"/></w:r>'
            .'<w:r><w:rPr>'.$props.'</w:rPr><w:t xml:space="preserve"> dari </w:t></w:r>'
            .'<w:r><w:fldChar w:fldCharType="begin"/></w:r>'
            .'<w:r><w:instrText xml:space="preserve"> NUMPAGES </w:instrText></w:r>'
            .'<w:r><w:fldChar w:fldCharType="separate"/></w:r><w:r><w:t>1</w:t></w:r>'
            .'<w:r><w:fldChar w:fldCharType="end"/></w:r>'
            .'</w:p></w:ftr>';
    }

    private function documentRels(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'<Relationship Id="rIdHeader1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header1.xml"/>'
            .'<Relationship Id="rIdFooter1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/>';

        foreach ($this->images as $image) {
            $xml .= '<Relationship Id="'.$image['rel'].'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/'.$image['name'].'"/>';
        }

        return $xml.'</Relationships>';
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Default Extension="png" ContentType="image/png"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            .'<Override PartName="/word/header1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"/>'
            .'<Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/>'
            .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            .'<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            .'</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            .'</Relationships>';
    }

    private function coreProps(): string
    {
        $now = gmdate('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            .'xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" '
            .'xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            .'<dc:title>'.$this->e($this->title).'</dc:title>'
            .'<dc:subject>'.$this->e($this->subtitle).'</dc:subject>'
            .'<dc:creator>Tim JelajahKu</dc:creator>'
            .'<cp:keywords>Sprint 3; JelajahKu; Bukti Artefak</cp:keywords>'
            .'<dc:description>Dokumentasi bukti artefak Sprint 3 aplikasi booking tiket destinasi JelajahKu.</dc:description>'
            .'<cp:lastModifiedBy>Tim JelajahKu</cp:lastModifiedBy>'
            .'<dcterms:created xsi:type="dcterms:W3CDTF">'.$now.'</dcterms:created>'
            .'<dcterms:modified xsi:type="dcterms:W3CDTF">'.$now.'</dcterms:modified>'
            .'</cp:coreProperties>';
    }

    private function appProps(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
            .'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            .'<Application>JelajahKu Docx Builder</Application>'
            .'<Company>JelajahKu</Company>'
            .'<AppVersion>1.0</AppVersion>'
            .'</Properties>';
    }

    private function e(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
