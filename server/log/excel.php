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
    
    $res = queryArray("SELECT log.*, category_log.name FROM log JOIN category_log ON category_log.id = log.category_log_id
     WHERE log.timestamp BETWEEN '$start_date' AND '$end_date'");
    $array = [];

    foreach ($res as $key => $value) {
        array_push($array,[$value['id'], $value['name'], $value['value'], $value['timestamp']]);
    }
    
    $spreadsheet = new Spreadsheet();
    // $activeWorksheet = $spreadsheet->getActiveSheet();
    // $activeWorksheet->setCellValue('A1', 'Hello World !');
    $spreadsheet->setActiveSheetIndex(0);
    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
    $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $spreadsheet->getActiveSheet()->setCellValue('A1', 'id')
        ->setCellValue('B1', 'Kategori')
        ->setCellValue('C1', 'Keterangan')
        ->setCellValue('D1', 'Waktu');


    
    $spreadsheet->getActiveSheet()->fromArray($array, null, 'A2');


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
