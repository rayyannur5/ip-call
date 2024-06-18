<?php
require_once('../config.php');
session_start();

if (isset($_POST['tw'])) {
    $tw = 1;
} else {
    $tw = 0;
}
$vol = $_POST['vol'];
$mic = $_POST['mic'];
$id = $_POST['id'];
$mode = $_POST['mode'];

$res = queryBoolean("UPDATE bed SET tw = $tw, vol = $vol, mic = $mic, mode = $mode WHERE id = $id");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Bed berhasil diupdate'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'Bed gagal diupdate'
    ];
}
header('location: ../setting_umum.php');
