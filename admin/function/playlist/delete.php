<?php

require_once('../../config.php');

session_start();

$id = $_GET['id'];

$res = queryBoolean("DELETE FROM playlist WHERE id = '$id'");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Playlist berhasil dihapus'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'Playlist gagal dihapus'
    ];
}
header('location: ../../setting_music.php');