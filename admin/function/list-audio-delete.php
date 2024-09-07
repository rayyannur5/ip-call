<?php

require_once('../config.php');

session_start();

$time = $_GET['time'];

$res = queryBoolean("DELETE FROM list_hour_audio WHERE `time` = '$time'");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'List berhasil dihapus'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'List gagal dihapus'
    ];
}
header('location: ../audio.php');