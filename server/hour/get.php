<?php

require_once('../init.php');

$data = queryArray("SELECT * FROM list_hour_audio");
echo json_encode([
    'success' => true,
    'data' => $data,
]);
