<?php

require_once('../../../config.php');

session_start();

try {
    mysqli_begin_transaction($conn);

    $playlist_id = $_POST['playlist_id'];
    $file = $_FILES['file'];

    $target_dir = "../../../../playlist/music/";
    $target_file = $target_dir . basename($file["name"]);

    move_uploaded_file($file["tmp_name"], $target_file);

    $ord = queryArray("SELECT ifnull(max(ord), 0) + 1 ord from playlist_item where id = $playlist_id")[0]['ord'];

    $filename = basename($file["name"]);

    queryBoolean("
        INSERT INTO playlist_item (id, ord, path)
        VALUES ($playlist_id, $ord, '$filename')
    ");

    mysqli_commit($conn);

    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Tambah Item Berhasil'
    ];

    header('location: ../../../setting_music.php');
} catch(Exception $e) {
    var_dump($e);
    die();
}