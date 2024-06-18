<?php

require_once('../config.php');
session_start();

$id = $_GET['id'];

$room = queryArray("SELECT * FROM room WHERE id = $id")[0];
unlink('../' . $room['audio']);

queryBoolean("DELETE FROM bed WHERE room_id = '$id'");
queryBoolean("DELETE FROM toilet WHERE room_id = '$id'");
$res = queryBoolean("DELETE FROM room WHERE id = '$id'");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Bed berhasil dihapus'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'Bed gagal dihapus'
    ];
}
header('location: ../setting.php');
