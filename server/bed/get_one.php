<?php

require_once('../init.php');

$id = $_GET['id'];

$util = queryArray("SELECT * from utils where type = 'timeout_time_activity'")[0];
$data = queryArray("SELECT * FROM bed WHERE id = '$id'");
$data[0]['timeout'] = $util['value'];
echo json_encode([
    'success' => true,
    'data' => $data,
]);
