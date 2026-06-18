<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once(__DIR__ . '/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

require_login();
require_sesskey();

$courseid = optional_param('courseid', 0, PARAM_INT);
$html = required_param('html', PARAM_RAW);

if ($courseid) {
    $course = get_course($courseid);
    require_course_login($course);
}

function th_exam_monitor_normalize_url(string $src): string {
    global $CFG;

    $src = trim($src);
    if ($src === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $src)) {
        return $src;
    }

    if (strpos($src, '//') === 0) {
        return (is_https() ? 'https:' : 'http:') . $src;
    }

    if ($src[0] === '/') {
        return rtrim($CFG->wwwroot, '/') . $src;
    }

    return rtrim($CFG->wwwroot, '/') . '/' . ltrim($src, '/');
}

function th_exam_monitor_node_text(DOMNode $node): string {
    $text = trim($node->textContent ?? '');
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim((string)$text);
}

function th_exam_monitor_cell_value(DOMElement $cell): array {
    $imgs = $cell->getElementsByTagName('img');
    $text = th_exam_monitor_node_text($cell);

    if ($imgs->length > 0) {
        $img = $imgs->item(0);
        $src = th_exam_monitor_normalize_url($img->getAttribute('src'));

        if ($src !== '') {
            return [
                'text' => ($text !== '' ? $text : 'Xem ảnh'),
                'url'  => $src,
            ];
        }

        return [
            'text' => ($text !== '' ? $text : '[Image]'),
            'url'  => '',
        ];
    }

    return [
        'text' => $text,
        'url'  => '',
    ];
}

try {
    if (!is_dir($CFG->tempdir)) {
        throw new Exception('Temp dir khong ton tai: ' . $CFG->tempdir);
    }

    $phptempdir = $CFG->tempdir . '/phpspreadsheet';
    if (!is_dir($phptempdir)) {
        mkdir($phptempdir, $CFG->directorypermissions, true);
    }

    libxml_use_internal_errors(true);

    $doc = new DOMDocument('1.0', 'UTF-8');
    $wrappedhtml = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>';
    $loaded = $doc->loadHTML('<?xml encoding="utf-8" ?>' . $wrappedhtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    if (!$loaded) {
        throw new Exception('Khong doc duoc HTML export');
    }

    $tables = $doc->getElementsByTagName('table');
    if ($tables->length === 0) {
        throw new Exception('Khong tim thay bang HTML de xuat Excel');
    }

    $table = $tables->item(0);

    $spreadsheet = new Spreadsheet();
    $spreadsheet->getProperties()
        ->setCreator('Moodle')
        ->setTitle('Exam Monitor Report');

    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Report');
    $sheet->getDefaultRowDimension()->setRowHeight(-1);
    $spreadsheet->getDefaultStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);

    $occupied = [];
    $rownum = 1;
    $maxcols = 1;

    foreach ($table->getElementsByTagName('tr') as $tr) {
        $colnum = 1;
        $cells = [];

        foreach ($tr->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if ($tag === 'th' || $tag === 'td') {
                    $cells[] = $child;
                }
            }
        }

        foreach ($cells as $cell) {
            while (isset($occupied[$rownum][$colnum])) {
                $colnum++;
            }

            $isheader = strtolower($cell->tagName) === 'th';
            $colspan = max(1, (int)$cell->getAttribute('colspan'));
            $rowspan = max(1, (int)$cell->getAttribute('rowspan'));

            $value = th_exam_monitor_cell_value($cell);
            $text = $value['text'];
            $url = $value['url'];

            $startcol = $colnum;
            $endcol = $colnum + $colspan - 1;
            $endrow = $rownum + $rowspan - 1;

            $coordinate = Coordinate::stringFromColumnIndex($startcol) . $rownum;
            $sheet->setCellValueExplicit($coordinate, $text, DataType::TYPE_STRING);

            // if ($url !== '') {
            //     $sheet->getCell($coordinate)->getHyperlink()->setUrl($url);
            //     $sheet->getStyle($coordinate)->getFont()->getColor()->setARGB(Color::COLOR_BLUE);
            //     $sheet->getStyle($coordinate)->getFont()->setUnderline(true);
            // }

            if ($url !== '') {
                try {
                    // tải ảnh về file tạm
                    $curl = new curl();
                    $options = [
                        'CURLOPT_RETURNTRANSFER' => true,
                        'CURLOPT_FOLLOWLOCATION' => true,
                        'CURLOPT_SSL_VERIFYPEER' => false,
                        'CURLOPT_SSL_VERIFYHOST' => 0,
                    ];

                    $imgdata = $curl->get($url, [], $options);

                    if ($imgdata !== false) {
                        $tmpimg = $CFG->tempdir . '/img_' . md5($url) . '.jpg';
                        file_put_contents($tmpimg, $imgdata);

                        if (file_exists($tmpimg)) {
                            $drawing = new Drawing();
                            $drawing->setPath($tmpimg);
                            $drawing->setCoordinates($coordinate);

                            // chỉnh size ảnh
                            $drawing->setHeight(80);

                            $drawing->setWorksheet($sheet);

                            // tăng chiều cao row cho đủ ảnh
                            $sheet->getRowDimension($rownum)->setRowHeight(60);

                            // optional: bỏ text
                            $sheet->setCellValueExplicit($coordinate, $text, DataType::TYPE_STRING);
                            $sheet->getStyle($coordinate)->getAlignment()->setWrapText(true);
                            $sheet->getStyle($coordinate)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                            $sheet->getRowDimension($rownum)->setRowHeight(90);
                        }
                    } else {
                        // fallback nếu lỗi ảnh
                        $sheet->setCellValue($coordinate, 'Không tải được ảnh');
                    }

                } catch (Throwable $e) {
                    $sheet->setCellValue($coordinate, 'Lỗi ảnh');
                }
            }

            if ($isheader) {
                $sheet->getStyle($coordinate)->getFont()->setBold(true);
                $sheet->getStyle($coordinate)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            if ($colspan > 1 || $rowspan > 1) {
                $mergeRange = Coordinate::stringFromColumnIndex($startcol) . $rownum . ':' .
                    Coordinate::stringFromColumnIndex($endcol) . $endrow;
                $sheet->mergeCells($mergeRange);
            }

            for ($r = $rownum; $r <= $endrow; $r++) {
                for ($c = $startcol; $c <= $endcol; $c++) {
                    $occupied[$r][$c] = true;
                }
            }

            $colnum = $endcol + 1;
            $maxcols = max($maxcols, $endcol);
        }

        $rownum++;
    }

    $lastcol = Coordinate::stringFromColumnIndex($maxcols);
    $lastrow = $rownum - 1;
    $range = 'A1:' . $lastcol . $lastrow;

    $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    for ($i = 1; $i <= $maxcols; $i++) {
        $col = Coordinate::stringFromColumnIndex($i);
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Giới hạn autosize quá bé/quá to.
    for ($i = 1; $i <= $maxcols; $i++) {
        $col = Coordinate::stringFromColumnIndex($i);
        $width = $sheet->getColumnDimension($col)->getWidth();

        if ($width < 12) {
            $sheet->getColumnDimension($col)->setWidth(12);
        } else if ($width > 50) {
            $sheet->getColumnDimension($col)->setWidth(50);
        }
    }

    $filename = 'exam_monitor_' . date('Ymd_His') . '.xlsx';

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Expires: 0');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->setPreCalculateFormulas(false);
    $writer->save('php://output');

    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    exit;

} catch (Throwable $e) {
    error_log('th_exam_monitor export_excel error: ' . $e->getMessage());
    error_log($e->getTraceAsString());

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Export Excel loi: ' . $e->getMessage();
    exit;
}