<?php
require_once "../config.php";

$limit = isset($_REQUEST["length"]) ? $_REQUEST["length"] : 10;
$start = isset($_REQUEST["start"]) ? $_REQUEST["start"] : 0;
$draw = isset($_REQUEST["draw"]) ? intval($_REQUEST["draw"]) : 1;

$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : null;
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : null;

// Base query conditions
$whereClause = "";
if ($startDate && $endDate) {
    $whereClause = "WHERE DATE(created_at) BETWEEN '$startDate' AND '$endDate'";
}

// 1. Get total number of distinct dates (Total records in DB)
$total_query = "SELECT COUNT(DISTINCT DATE(created_at)) as total FROM oximonitor_log";
$total_res = queryArray($total_query);
$recordsTotal = isset($total_res[0]) ? $total_res[0]['total'] : 0;

// 2. Get total filtered records
$filtered_query = "SELECT COUNT(DISTINCT DATE(created_at)) as total FROM oximonitor_log $whereClause";
$filtered_res = queryArray($filtered_query);
$recordsFiltered = isset($filtered_res[0]) ? $filtered_res[0]['total'] : 0;

// 3. Get the specific page of dates
$date_query = "SELECT DISTINCT DATE(created_at) as log_date FROM oximonitor_log $whereClause ORDER BY log_date DESC LIMIT $start, $limit";
$dates = queryArray($date_query);

$data = [];
$no = $start + 1;

foreach ($dates as $row) {
    $date = $row['log_date'];
    $prev_date = date('Y-m-d', strtotime($date . ' -1 day'));

    // Get max volume at end of this date
    $q1 = "SELECT volume FROM oximonitor_log WHERE DATE(created_at) <= '$date' ORDER BY created_at DESC LIMIT 1";
    $r1 = queryArray($q1);
    $vol_today = isset($r1[0]) ? $r1[0]['volume'] : 0;

    // Get max volume at end of previous date
    $q2 = "SELECT volume FROM oximonitor_log WHERE DATE(created_at) <= '$prev_date' ORDER BY created_at DESC LIMIT 1";
    $r2 = queryArray($q2);
    $vol_yesterday = isset($r2[0]) ? $r2[0]['volume'] : 0;

    $usage = $vol_today - $vol_yesterday;
    // Format usage
    $usage_fmt = number_format($usage, 2, ',', '.'); // 1.054,32

    // Format date
    $months = [
        'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 
        'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 
        'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
    ];
    $date_obj = DateTime::createFromFormat('Y-m-d', $date);
    $month_name = $date_obj->format('F');
    $date_fmt = $date_obj->format('d') . ' ' . (isset($months[$month_name]) ? $months[$month_name] : $month_name) . ' ' . $date_obj->format('Y');

    $data[] = [
        $no++,
        $date_fmt,
        $usage_fmt
    ];
}

$response = [
    "draw" => $draw,
    "recordsTotal" => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data" => $data
];

header("Content-Type: application/json");
echo json_encode($response);
?>
