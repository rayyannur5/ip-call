<?php
require_once "../config.php";

// Fetch Current Flow Rate
$status = queryArray("SELECT flow_rate FROM oximonitor_status LIMIT 1");
$current_flow = isset($status[0]) ? floatval($status[0]['flow_rate']) : 0;

// Helper function
function getVolumeAtDate($date) {
    global $conn;
    $query = "SELECT volume FROM oximonitor_log WHERE DATE(created_at) <= '$date' ORDER BY created_at DESC LIMIT 1";
    $result = queryArray($query);
    return isset($result[0]) ? floatval($result[0]['volume']) : 0;
}

// Latest Volume
$latest = queryArray("SELECT volume FROM oximonitor_log ORDER BY created_at DESC LIMIT 1");
$latest_volume = isset($latest[0]) ? floatval($latest[0]['volume']) : 0;

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 days'));
$three_days_ago = date('Y-m-d', strtotime('-3 days'));
$seven_days_ago = date('Y-m-d', strtotime('-7 days'));
$fourteen_days_ago = date('Y-m-d', strtotime('-14 days'));
$thirty_days_ago = date('Y-m-d', strtotime('-30 days'));

$vol_yesterday_end = getVolumeAtDate($yesterday); 
$usage_today = $latest_volume - $vol_yesterday_end;

$vol_3_days_ago = getVolumeAtDate($three_days_ago);
$usage_3_days = $latest_volume - $vol_3_days_ago;

$vol_7_days_ago = getVolumeAtDate($seven_days_ago);
$usage_7_days = $latest_volume - $vol_7_days_ago;

$vol_14_days_ago = getVolumeAtDate($fourteen_days_ago);
$usage_14_days = $latest_volume - $vol_14_days_ago;

$vol_30_days_ago = getVolumeAtDate($thirty_days_ago);
$usage_30_days = $latest_volume - $vol_30_days_ago;

$avg_3_days = $usage_3_days / 3;
$avg_7_days = $usage_7_days / 7;

// Format numbers
function fmt($num) {
    return number_format($num, 2, ',', '.');
}

$response = [
    "current_flow" => fmt($current_flow),
    "usage_today" => fmt($usage_today),
    "usage_3_days" => fmt($usage_3_days),
    "usage_7_days" => fmt($usage_7_days),
    "avg_3_days" => fmt($avg_3_days),
    "avg_7_days" => fmt($avg_7_days),
    "usage_14_days" => fmt($usage_14_days),
    "usage_30_days" => fmt($usage_30_days)
];

header("Content-Type: application/json");
echo json_encode($response);
?>
