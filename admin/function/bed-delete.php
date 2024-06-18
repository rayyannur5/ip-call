<?php
require_once('../config.php');
session_start();

$id = $_GET['id'];

$res = queryBoolean("DELETE FROM bed WHERE id = '$id'");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Ruang berhasil dihapus'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'Ruang gagal dihapus'
    ];
}
header('location: ../setting.php');
