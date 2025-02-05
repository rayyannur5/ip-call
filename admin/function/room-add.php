<?php
global $conn;
require_once('../config.php');
session_start();

$id = $_POST['id'];
$type = $_POST['jenis'];
$type_bed = $_POST['type_bed'];
$separator_bed = $_POST['separator_bed'];
$running_text = $_POST['running_text'];

try {
    mysqli_begin_transaction($conn);

    $fullname = ltrim(implode(" ", $_POST['name']));
    $res = queryBoolean("
        INSERT INTO room (id, type, name, running_text, type_bed, bed_separator) 
        VALUES ($id, '$type', '$fullname', '$running_text', '$type_bed', '$separator_bed')
    ");

    foreach($_POST['name'] as $key => $name) {
        $check = queryArray("SELECT * FROM mastersound WHERE name = '$name'");
        if(count($check) == 0) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["audio"]["name"][$key]);
            move_uploaded_file($_FILES["audio"]["tmp_name"][$key], '../' . $target_file);
            queryBoolean("INSERT INTO mastersound VALUES (NULL, '$name', '$target_file')");
        }
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