<?php

require_once('../init.php');

$data = queryArray("SELECT * FROM room");

echo json_encode([
    'success' => true,
    'data' => $data,
]);