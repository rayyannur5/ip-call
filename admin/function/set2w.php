<?php
require_once('../config.php');
session_start();
$res = queryBoolean("UPDATE bed SET tw = 1");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Bed berhasil diubah ke 1W semua'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'Bed gagal diubah'
    ];
}
header('location: ../setting.php');