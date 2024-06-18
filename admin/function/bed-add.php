<?php

require_once('../config.php');

session_start();


$room_id = $_POST['room_id'];
$room = queryArray("SELECT * FROM room WHERE id = $room_id")[0];
$room_name = $room['name'];

$bed_before = queryArray("SELECT * FROM bed WHERE room_id = $room_id");

// create id
$id = "01" . ($room_id < 10 ? "0" . $room_id : $room_id) . (count($bed_before) < 10 ? "0" . count($bed_before) + 1 : count($bed_before) + 1);

// create name
$name = "Ruang " . $room_name . " " . count($bed_before) + 1;

// insert into db
$res = queryBoolean("INSERT INTO bed VALUES ('$id', $room_id, '$name', 100, 100, 1, 0, NULL)");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Bed berhasil ditambahkan'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'Bed gagal ditambahkan'
    ];
}
header('location: ../setting_umum.php');
