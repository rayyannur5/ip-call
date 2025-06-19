<?php

require_once '../config.php';
session_start();

$topic = $_GET['topic'];
$speed = $_GET['speed'];
$brightness = $_GET['brightness'];

$res = queryBoolean("UPDATE running_text SET speed = '$speed', brightness = '$brightness' WHERE topic = '$topic'");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => "$topic berhasil di ubah"
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => "$topic gagal di ubah"
    ];
}
header('location: ../setting_running_text.php');