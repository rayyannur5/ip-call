<?php

require_once('../init.php');

$data = queryArray("SELECT * FROM bed");

echo json_encode([
    'success' => true,
    'data' => $data,
]);
