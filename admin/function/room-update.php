<?php
require_once('../config.php');
session_start();

$name = $_POST['name'];
$id = $_POST['id'];


if ($_FILES['audio']['name'] != "") {
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

        $room = queryArray("SELECT * FROM room WHERE id = $id")[0];
        unlink('../' . $room['audio']);

        $beds = queryArray("SELECT * FROM bed WHERE room_id = $id");

        foreach ($beds as $index => $bed) {
            $bed_id = $bed['id'];
            $username = 'Ruang ' . $name . ' ' . $index + 1;
            queryBoolean("UPDATE bed SET username = '$username' WHERE id = $bed_id");
        }

        $toilets = queryArray("SELECT * FROM toilet WHERE room_id = $id");

        foreach ($toilets as $index => $toilet) {
            $toilet_id = $toilet['id'];
            $username = 'Toilet ' . $name . ' ' . $index + 1;
            queryBoolean("UPDATE toilet SET username = '$username' WHERE id = $toilet_id");
        }

        $res = queryBoolean("UPDATE room SET name = '$name', audio = '$target_file' WHERE id = $id");

        if ($res) {
            $_SESSION['flash-message'] = [
                'success' => true,
                'message' => 'Ubah Ruang Berhasil'
            ];
        } else {
            $_SESSION['flash-message'] = [
                'success' => false,
                'message' => 'Ubah Ruang Gagal'
            ];
        }
    } else {
        $_SESSION['flash-message'] = [
            'success' => false,
            'message' => $message
        ];
    }
} else {
    $res = queryBoolean("UPDATE room SET name = '$name' WHERE id = $id");

    $beds = queryArray("SELECT * FROM bed WHERE room_id = $id");

    foreach ($beds as $index => $bed) {
        $bed_id = $bed['id'];
        $username = 'Ruang ' . $name . ' ' . $index + 1;
        queryBoolean("UPDATE bed SET username = '$username' WHERE id = $bed_id");
    }

    $toilets = queryArray("SELECT * FROM toilet WHERE room_id = $id");

    foreach ($toilets as $index => $toilet) {
        $toilet_id = $toilet['id'];
        $username = 'Toilet ' . $name . ' ' . $index + 1;
        queryBoolean("UPDATE toilet SET username = '$username' WHERE id = $toilet_id");
    }

    if ($res) {
        $_SESSION['flash-message'] = [
            'success' => true,
            'message' => 'Ubah Ruang Berhasil'
        ];
    } else {
        $_SESSION['flash-message'] = [
            'success' => false,
            'message' => 'Ubah Ruang Gagal'
        ];
    }
}
header('location: ../setting_umum.php');
