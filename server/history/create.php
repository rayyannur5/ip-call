<?php

require_once('../init.php');

$category = $_GET['category'];
$duration = isset($_GET['duration']) ? $_GET['duration'] : '0 detik';
$timestamp = date('Y-m-d H:i:s');
$bed_id = $_GET['bed_id'];

queryBoolean("INSERT INTO `history` VALUES(NULL, '$bed_id', $category, '$duration', NULL,'$timestamp')");

echo json_encode([
    'success' => true,
]);
