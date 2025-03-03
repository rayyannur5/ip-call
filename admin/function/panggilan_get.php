<?php

require_once "../config.php";

$limit = $_GET["length"];
$start = $_GET["start"];
$category = isset($_GET["category"]) ? $_GET["category"] : "";
$date = isset($_GET["date"]) ? $_GET["date"] : date('Y-m-d');

if (!empty($category)) {
    $data = queryArray(
        "SELECT bed.username as name_bed, category_history.name as name_category, history.duration, history.record, history.timestamp FROM history
        JOIN category_history ON category_history.id = history.category_history_id
        JOIN bed ON bed.id = history.bed_id
        WHERE history.category_history_id = $category
        AND DATE(history.timestamp) = '$date'
        ORDER BY history.id DESC
        LIMIT $start, $limit"
    );

    $total_result = $conn->query("SELECT COUNT(*) AS total FROM history");
    $total = $total_result->fetch_assoc()["total"];

    $total_result_filtered = $conn->query("SELECT COUNT(*) AS total FROM history WHERE history.category_history_id = $category AND DATE(history.timestamp) = '$date'");
    $total_filtered = $total_result_filtered->fetch_assoc()["total"];

    $response = [
        "draw" => intval($_GET["draw"]),
        "recordsTotal" => $total,
        "recordsFiltered" => $total_filtered,
        "data" => $data,
    ];
} else {
    $data = queryArray(
        "SELECT bed.username as name_bed, category_history.name as name_category, history.duration, history.record, history.timestamp FROM history
        JOIN category_history ON category_history.id = history.category_history_id
        JOIN bed ON bed.id = history.bed_id
        WHERE DATE(history.timestamp) = '$date'
        ORDER BY history.id DESC
        LIMIT $start, $limit"
    );

    $total_result = $conn->query("SELECT COUNT(*) AS total FROM history");
    $total = $total_result->fetch_assoc()["total"];
    
    $total_result_filtered = $conn->query("SELECT COUNT(*) AS total FROM history WHERE DATE(history.timestamp) = '$date'");
    $total_filtered = $total_result_filtered->fetch_assoc()["total"];

    $response = [
        "draw" => intval($_GET["draw"]),
        "recordsTotal" => $total,
        "recordsFiltered" => $total_filtered,
        "data" => $data,
    ];
}

header("Content-Type: application/json");
echo json_encode($response);
