<?php

require_once('../vendor/autoload.php');
require_once('../init_html.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;

try {
    //code...
    $start_date = date("{$_GET['start_date']} 00:00:00");
    $end_date = date("{$_GET['end_date']} 23:59:59");
    
    $res = queryArray("
        SELECT 
            log.id, 
            category_log.name,
            coalesce(bed.username, toilet.username) as username,
            sec_to_time(log.time) as time,
            case when log.nurse_presence = 1 then 'Ya' else 'Tidak' end as presence,
            log.timestamp
        FROM log 
        JOIN category_log ON category_log.id = log.category_log_id
        LEFT JOIN bed ON bed.id = log.device_id
        LEFT JOIN toilet on toilet.id = log.device_id
        WHERE log.timestamp BETWEEN '$start_date' AND '$end_date'
    ");

    
    $spreadsheet = new Spreadsheet();
    // $activeWorksheet = $spreadsheet->getActiveSheet();
    // $activeWorksheet->setCellValue('A1', 'Hello World !');
    $spreadsheet->setActiveSheetIndex(0);
    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
    $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $spreadsheet->getActiveSheet()->setCellValue('A1', 'id')
        ->setCellValue('B1', 'Kategori')
        ->setCellValue('C1', 'Ruang')
        ->setCellValue('D1', 'Waktu')
        ->setCellValue('E1', 'Kehadiran')
        ->setCellValue('F1', 'timestamp');


    
    $spreadsheet->getActiveSheet()->fromArray($res, null, 'A2');


    $filename = "EXCEL_LOG_" . date("Y-m-d H:i:s");
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=$filename.xlsx");
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    
    // $writer = new Xlsx($spreadsheet);
    // $writer->save('tes.xlsx');
} catch (\Throwable $th) {
    //throw $th;
    print_r($th);
}
