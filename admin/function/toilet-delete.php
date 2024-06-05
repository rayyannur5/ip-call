<?php

require_once('../config.php');
session_start();

$id = $_GET['id'];

$res = queryBoolean("DELETE FROM toilet WHERE id = '$id'");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Toilet berhasil dihapus'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'Toilet gagal dihapus'
    ];
}
header('location: ../index.php');
