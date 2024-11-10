<?php
require_once('init.php');

$data = queryArray("SELECT * FROM mastersound");

echo json_encode([
    'success'=> true,
    'data'=> $data
]);