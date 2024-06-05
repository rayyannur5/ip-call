<?php

require_once('../config.php');

session_start();

$room_id = $_POST['room_id'];
$room = queryArray("SELECT * FROM room WHERE id = $room_id")[0];
$room_name = $room['name'];

$toilet_before = queryArray("SELECT * FROM toilet WHERE room_id = $room_id");

// create id
$id = "02" . ($room_id < 10 ? "0" . $room_id : $room_id) . (count($toilet_before) < 10 ? "0" . count($toilet_before) + 1 : count($toilet_before) + 1);

// create name
$name = "Toilet " . $room_name . " " . count($toilet_before) + 1;

// insert into db
$res = queryBoolean("INSERT INTO toilet VALUES ('$id', $room_id, '$name')");

if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Toilet berhasil ditambahkan'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'Toilet gagal ditambahkan'
    ];
}
header('location: ../index.php');
