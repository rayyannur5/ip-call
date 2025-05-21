<?php

require_once('../../init.php');

$category = 1;
$value = $_GET['value'];
$device_id = $_GET['device_id'];
$time = $_GET['time'];
$nurse_presence = $_GET['nurse_presence'];
$timestamp = date('Y-m-d H:i:s');

queryBoolean("INSERT INTO `log` VALUES(NULL, $category, '$value', '$device_id', $time, $nurse_presence, '$timestamp')");

echo json_encode([
    'success' => true,
]);
