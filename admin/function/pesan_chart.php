<?php

require_once '../config.php';

$filter = $_GET['filter'];

if($filter == 'month'){
    $start_date = date('Y-m-d', strtotime('-30 days'));
    $end_date = date('Y-m-d');
} else if ($filter == 'week') {
    $start_date = date('Y-m-d', strtotime('-7 days'));
    $end_date = date('Y-m-d');
} else {
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d');
}


$start_date = $start_date . ' 00:00:00';

$end_date = $end_date . ' 23:59:59';


$categories = queryArray("SELECT category_log.name, COUNT(log.id) as count FROM log 
JOIN category_log ON category_log.id = log.category_log_id 
WHERE log.timestamp BETWEEN '$start_date' AND '$end_date'
GROUP BY category_log_id");


header("Content-Type: application/json");
echo json_encode($categories);