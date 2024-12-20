<?php

require_once('../config.php');

session_start();


$room_id = $_POST['room_id'];
$room = queryArray("SELECT * FROM room WHERE id = $room_id")[0];
$room_name = $room['name'];
$jenis = $room['type'];

$bed_before = queryArray("SELECT * FROM bed WHERE room_id = $room_id");

$nomor = str_pad( count($bed_before) + 1,2,"0", STR_PAD_LEFT);

// create id
$id = "01" . ($room_id < 10 ? "0" . $room_id : $room_id) . $nomor;

// create name
$name = ($jenis != "" ? "$jenis " : "") . $room_name . " " . (count($bed_before) == 0 ? "" : count($bed_before) + 1);


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
header('location: ../setting.php');
