<?php
require_once('../config.php');
session_start();
$res = queryBoolean("UPDATE bed SET mode = 0");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Bed berhasil diubah ke Emergency'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'Bed gagal diubah'
    ];
}
header('location: ../setting.php');