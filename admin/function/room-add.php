<?php
require_once('../config.php');
session_start();

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["audio"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

$message = "";

// Check if file already exists
if (file_exists($target_file)) {
    $message = "Sorry, file already exists.";
    $uploadOk = 0;
}

// Check file size
if ($_FILES["audio"]["size"] > 500000) {
    $message = "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow certain file formats
if ($imageFileType != "ogg") {
    $message = "Sorry, only OGG files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    $message = "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["audio"]["tmp_name"], '../' . $target_file)) {
        $message = "The file " . htmlspecialchars(basename($_FILES["audio"]["name"])) . " has been uploaded.";
        $uploadOk = 1;
    } else {
        $uploadOk = 0;
        $message = "Sorry, there was an error uploading your file.";
    }
}

if ($uploadOk == 1) {
    $name = $_POST['name'];

    $res = queryBoolean("INSERT INTO room VALUES (NULL, '$name', '$target_file')");

    if ($res) {
        $_SESSION['flash-message'] = [
            'success' => true,
            'message' => 'Tambah Ruang Berhasil'
        ];
    } else {
        $_SESSION['flash-message'] = [
            'success' => false,
            'message' => 'Tambah Ruang Gagal'
        ];
    }
} else {
    $_SESSION['flash-message'] = [
        'success' => false,
        'message' => $message
    ];
}

header('location: ../index.php');
