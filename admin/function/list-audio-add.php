<?php

require_once('../config.php');

session_start();

$time = $_POST['time'] . ':00';
$vol = $_POST['vol'];


$list = queryArray("SELECT * FROM list_hour_audio");


foreach ($list as $key => $val) {
    if($val['time'] == $time) {
        $_SESSION['flash-message'] = [
            'success' => false,
            'message' => 'Tanggal sudah ada'
        ];
    
        header('location: ../audio.php');
    }
}

$res = queryBoolean("INSERT INTO list_hour_audio VALUES ('$time', $vol)");



if ($res) {
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'List berhasil ditambahkan'
    ];
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => 'List gagal ditambahkan'
    ];
}
header('location: ../audio.php');
