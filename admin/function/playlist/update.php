<?php

require_once('../../config.php');

session_start();

$id = $_POST['id'];
$name = $_POST['name'];
$volume = $_POST['volume'];
$start = $_POST['start'];
$end = $_POST['end'];

$res = queryBoolean("
    UPDATE playlist SET 
        name = '$name',
        volume = '$volume',
        start_time = '$start',
        end_time = '$end'
    where id = '$id'
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