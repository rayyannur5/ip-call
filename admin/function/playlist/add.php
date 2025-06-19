<?php

require_once('../../config.php');

session_start();

$name = $_POST['name'];
$volume = $_POST['volume'];
$start = $_POST['start'];
$end = $_POST['end'];

$res = queryBoolean("
    INSERT INTO playlist (name, volume, start_time, end_time) 
    VALUES ('$name', $volume, '$start', '$end')
");


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
header('location: ../../setting_music.php');