<?php

require_once('../dompdf/autoload.inc.php');

date_default_timezone_set('Asia/Jakarta');
// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();
try {
    //code...
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $html = file_get_contents("http://localhost/ip-call/server/history/pdf_html.php?start_date=$start_date&end_date=$end_date");
    $dompdf->load_html($html);
} catch (\Throwable $th) {
    //throw $th;
    var_dump($th);
}

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$filename = "PDF_RIWAYAT_TELEPON_" . date("Y-m-d_H.i.s") . '.pdf';
$dompdf->stream($filename);
