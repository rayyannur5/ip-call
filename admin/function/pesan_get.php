<?php

require_once "../config.php";

$limit = $_GET["length"];
$start = $_GET["start"];
$category = isset($_GET["category"]) ? $_GET["category"] : "";

if (!empty($category)) {
    $sql .= " AND category = '$category'";
    $data = queryArray(
        "SELECT category_log.name as name_category, log.value, log.timestamp FROM log
        JOIN category_log ON category_log.id = log.category_log_id
        WHERE log.category_log_id = $category
        ORDER BY log.id DESC
        LIMIT $start, $limit"
    );

    $total_result = $conn->query("SELECT COUNT(*) AS total FROM log");
    $total = $total_result->fetch_assoc()["total"];

    $total_result_filtered = $conn->query("SELECT COUNT(*) AS total FROM log WHERE log.category_log_id = $category");
    $total_filtered = $total_result_filtered->fetch_assoc()["total"];

    $response = [
        "draw" => intval($_GET["draw"]),
        "recordsTotal" => $total,
        "recordsFiltered" => $total_filtered,
        "data" => $data,
    ];
} else {
    $data = queryArray(
        "SELECT category_log.name as name_category, log.value, log.timestamp FROM log
        JOIN category_log ON category_log.id = log.category_log_id
        ORDER BY log.id DESC
        LIMIT $start, $limit"
    );

    $total_result = $conn->query("SELECT COUNT(*) AS total FROM log");
    $total = $total_result->fetch_assoc()["total"];

    $response = [
        "draw" => intval($_GET["draw"]),
        "recordsTotal" => $total,
        "recordsFiltered" => $total,
        "data" => $data,
    ];
}

header("Content-Type: application/json");
echo json_encode($response);
