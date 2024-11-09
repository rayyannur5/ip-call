<?php
global $conn;
require_once('../config.php');
session_start();

$id = $_POST['id'];

try {
    mysqli_begin_transaction($conn);

    $fullname = implode(" ", $_POST['name']);
    $res = queryBoolean("INSERT INTO room VALUES ($id, '$fullname')");

    foreach($_POST['name'] as $key => $name) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["audio"]["name"][$key]);
        move_uploaded_file($_FILES["audio"]["tmp_name"][$key], '../' . $target_file);
        queryBoolean("INSERT INTO mastersound VALUES (NULL, '$name', '$target_file')");
    }
    mysqli_commit($conn);
    $_SESSION['flash-message'] = [
        'success' => true,
        'message' => 'Tambah Ruang Berhasil'
    ];

    header('location: ../setting.php');


} catch (Throwable $th){
    var_dump($th);
    die();
}