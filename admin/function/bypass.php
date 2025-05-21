<?php
require_once('../config.php');
session_start();

$id = $_GET["id"];
$type = $_GET["type"];

if($type == "bed") {
    $bed = queryArray("SELECT * FROM bed WHERE id = $id")[0];
    if($bed["bypass"] == "1") {
        $res = queryBoolean("UPDATE bed SET bypass = 0 where id = $id");
    } else {
        $res = queryBoolean("UPDATE bed SET bypass = 1 where id = $id");
    }
} else if ($type == "room") {
    $room = queryArray("SELECT * FROM room WHERE id = $id")[0];
    if($room["bypass"] == "1") {
        $res = queryBoolean("UPDATE room SET bypass = 0 where id = $id");
    } else {
        $res = queryBoolean("UPDATE room SET bypass = 1 where id = $id");
    }
} else if ($type == "toilet") {
    $toilet = queryArray("SELECT * FROM toilet WHERE id = $id")[0];
    if($toilet["bypass"] == "1") {
        $res = queryBoolean("UPDATE toilet SET bypass = 0 where id = $id");
    } else {
        $res = queryBoolean("UPDATE toilet SET bypass = 1 where id = $id");
    }
}


if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Bypass berhasil'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'Bypass gagal'
    ];
}
header('location: ../setting.php');