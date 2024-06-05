<?php

require_once('../init.php');


$data = queryArray("SELECT * FROM toilet");

echo json_encode([
    'success' => true,
    'data' => $data,
]);
