<?php

require_once('../../init.php');

$category = 4;
$value = $_GET['value'];
$timestamp = date('Y-m-d H:i:s');

queryBoolean("INSERT INTO `log` VALUES(NULL, $category, '$value', '$timestamp')");

echo json_encode([
    'success' => true,
]);
