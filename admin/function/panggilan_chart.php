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


$categories = queryArray("SELECT category_history.name, COUNT(history.id) as count FROM history 
JOIN category_history ON category_history.id = history.category_history_id 
WHERE history.timestamp BETWEEN '$start_date' AND '$end_date'
GROUP BY category_history_id");


header("Content-Type: application/json");
echo json_encode($categories);