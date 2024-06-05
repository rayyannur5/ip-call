<?php

require_once('../dompdf/autoload.inc.php');
// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();
try {
    //code...
    $month = $_GET['month'];
    $html = file_get_contents("http://localhost/ip-call-server/history/pdf_html.php?month=$month");
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
$filename = "PDF_RIWAYAT_TELEPON_" . date("Y-m-d H:i:s") . '.pdf';
$dompdf->stream($filename);
