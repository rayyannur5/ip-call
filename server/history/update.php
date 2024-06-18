<?php

require_once('../init.php');

$name = $_GET['name'];

$history = queryArray("SELECT * FROM history ORDER BY id DESC LIMIT 1");

$url = "records/$name.wav";

queryBoolean("UPDATE history SET record = '$url' WHERE id = {$history[0]['id']}");

echo json_encode([
    'success' => true
]);