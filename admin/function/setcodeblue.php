<?php
require_once('../config.php');
session_start();
$res = queryBoolean("UPDATE bed SET mode = 2");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Bed berhasil diubah ke CodeBlue'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'Bed gagal diubah'
    ];
}
header('location: ../setting.php');