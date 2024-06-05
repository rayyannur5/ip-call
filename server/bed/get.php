<?php

require_once('../init.php');

$id = $_GET['id'];

$data = queryArray("SELECT * FROM bed WHERE room_id = '$id'");

echo json_encode([
    'success' => true,
    'data' => $data,
]);