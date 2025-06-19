<?php

require_once('../../../config.php');

session_start();

$id = $_GET['id'];
$ord = $_GET['ord'];

queryBoolean("delete from playlist_item where id = $id and ord = $ord");

$_SESSION['flash-message'] = [
    'success' => true,
    'message' => 'Hapus Item Berhasil'
];

header('location: ../../../setting_music.php');