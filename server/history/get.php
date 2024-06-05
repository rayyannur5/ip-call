<?php

require_once('../init.php');


$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');


$data = queryArray("SELECT history.*, bed.username, category_history.name FROM history JOIN category_history ON history.category_history_id = category_history.id JOIN bed ON bed.id = history.bed_id WHERE date(history.timestamp) = '$date' ORDER BY history.timestamp DESC");

echo json_encode([
    'success' => true,
    'data' => $data,
]);
