<?php

require_once "../config.php";

$limit = $_GET["length"];
$start = $_GET["start"];
$category = isset($_GET["category"]) ? $_GET["category"] : "";
$presence = $_GET["presence"];
$date_start = isset($_GET["date_start"]) ? $_GET["date_start"] : date('Y-m-d');
$date_end = isset($_GET["date_end"]) ? $_GET["date_end"] : date('Y-m-d');


$filter = "WHERE DATE(log.timestamp) BETWEEN '$date_start' AND '$date_end'";

if (!empty($category)) {

    $filter .= "AND log.category_log_id = '$category'";
}

if ($presence != "") {
    $filter .= "AND log.nurse_presence = '$presence'";
}

$data = queryArray("
    SELECT 
        category_log.name as name_category, 
        log.value, 
        sec_to_time(log.time) as time,
        coalesce(bed.username, toilet.username) as username,
        case when log.nurse_presence = 1 then 'Ya' else 'Tidak' end as kehadiran_perawat,
        log.timestamp 
    FROM log
    JOIN category_log ON category_log.id = log.category_log_id
    LEFT JOIN bed on bed.id = log.device_id
    LEFT JOIN toilet on toilet.id = log.device_id
    $filter
    ORDER BY log.id DESC
    LIMIT $start, $limit
");

$total_result = $conn->query("SELECT COUNT(*) AS total FROM log");
$total = $total_result->fetch_assoc()["total"];

$total_result_filtered = $conn->query("
        SELECT COUNT(*) AS total 
        FROM log 
        $filter
    ");
$total_filtered = $total_result_filtered->fetch_assoc()["total"];

$response = [
    "draw" => intval($_GET["draw"]),
    "recordsTotal" => $total,
    "recordsFiltered" => $total_filtered,
    "data" => $data,
];


header("Content-Type: application/json");
echo json_encode($response);
