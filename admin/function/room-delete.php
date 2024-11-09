<?php

global $conn;
require_once('../config.php');
session_start();

$id = $_GET['id'];

try {
    mysqli_begin_transaction($conn);
    $room = queryArray("SELECT * FROM room WHERE id = $id")[0];

    $names = explode(" ", $room['name']);
    foreach($names as $name) {
        $audio = queryArray("SELECT * FROM mastersound WHERE name = '$name'")[0];
        unlink('../' . $audio['source']);
        queryBoolean("DELETE FROM mastersound WHERE name = '$name'");
    }

    queryBoolean("DELETE FROM bed WHERE room_id = '$id'");
    queryBoolean("DELETE FROM toilet WHERE room_id = '$id'");
    $res = queryBoolean("DELETE FROM room WHERE id = '$id'");

    mysqli_commit($conn);

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
} catch (Throwable $th) {
    mysqli_rollback($conn);
    var_dump($th);
    die();
}

