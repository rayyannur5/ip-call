<?php

require_once '../config.php';
session_start();

$type = $_GET['type'];
$value = $_GET['value'];

$res = queryBoolean("UPDATE utils SET value = '$value' WHERE type = '$type'");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => "$type berhasil di ubah"
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => "$type gagal di ubah"
    ];
}
header('location: ../setting_umum.php');