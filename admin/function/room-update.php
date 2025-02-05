<?php
global $conn;
require_once('../config.php');
session_start();

$name = $_POST['name'];
$id = $_POST['id'];
$last_id = $_POST['last_id'];
$jenis = $_POST['jenis'];
$jenis_bed = $_POST['type_bed'];
$separator_bed = $_POST['separator_bed'];
$running_text = $_POST['running_text'];

function convertToLetter($number) {
    // Konversi angka ke huruf dengan basis ASCII (A = 65, B = 66, ..., Z = 90)
    if ($number >= 1 && $number <= 26) {
        return chr(64 + $number);
    }
}


try {
    mysqli_begin_transaction($conn);

    $fullname = ltrim(implode(" ", $_POST['name']));
    $res = queryBoolean("
        UPDATE room 
        SET id = $id, 
            name = '$fullname', 
            type = '$jenis',
            running_text = '$running_text',
            type_bed = '$jenis_bed',
            bed_separator = '$separator_bed'
        WHERE id = $last_id
    ");
    $room = queryArray("SELECT * FROM room WHERE id = $id");
    $jenis = $room[0]['type'];
    $jenis_bed = $room[0]['type_bed'];
    $separator = $room[0]['bed_separator'] != "" ? $room[0]['bed_separator'] . " " : '';

    foreach($_POST['name'] as $key => $name) {

        $last_name = $_POST['last_name'][$key];

        $beds = queryArray("SELECT * FROM bed WHERE room_id = $last_id");
        $is_only_one = count($beds) == 1;
        foreach ($beds as $index => $bed) {
            $bed_id = $bed['id'];
            $new_bed_id = "01" . ($id < 10 ? "0" . $id : $id) . (($index + 1) < 10 ? "0" . ($index + 1) : ($index + 1));

            if($jenis == "") {

                if($is_only_one) {
                    $username = $fullname;
                } else {
                    if ($jenis_bed == 'numeric') {
                        $username = $fullname . ' ' . $separator . $index + 1;
                    } else {
                        $username = $fullname . ' ' . $separator . convertToLetter($index + 1);
                    }
                }

            } else {

                if($is_only_one) {
                    $username = $jenis.' ' . $fullname;
                } else {
                    if($jenis_bed == 'numeric') {
                        $username = $jenis.' ' . $fullname . ' ' . $separator . $index + 1;
                    } else {
                        $username = $jenis.' ' . $fullname . ' ' . $separator . convertToLetter($index + 1);
                    }
                }

            }


            $username = ltrim($username);
            queryBoolean("UPDATE bed SET id = '$new_bed_id', room_id = $id, username = '$username' WHERE id = $bed_id");
        }

        $toilets = queryArray("SELECT * FROM toilet WHERE room_id = $last_id");
        $is_only_one_toilet = count($toilets) == 1;
        foreach ($toilets as $index => $toilet) {
            $toilet_id = $toilet['id'];
            $new_toilet_id = "02" . ($id < 10 ? "0" . $id : $id) . (($index + 1) < 10 ? "0" . ($index + 1) : ($index + 1));
            if($is_only_one_toilet) {
                $username = 'Toilet ' . $fullname;
            } else {
                $username = 'Toilet ' . $fullname . ' ' . $index + 1;
            }

            queryBoolean("UPDATE toilet SET id = '$new_toilet_id', room_id = $id, username = '$username' WHERE id = $toilet_id");
        }

        if($_FILES['audio']['name'][$key] != "") {

            $sound = queryArray("SELECT * FROM mastersound WHERE name = '$last_name'")[0];
            unlink('../' . $sound['source']);

            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["audio"]["name"][$key]);
            move_uploaded_file($_FILES["audio"]["tmp_name"][$key], '../' . $target_file);
            queryBoolean("UPDATE mastersound SET source = '$target_file', name = '$name' WHERE name = '$last_name'");
        }

            $_SESSION['flash-message'] = [
                'success' => true,
                'message' => 'Ubah Ruang Berhasil'
            ];

    }

    mysqli_commit($conn);
    header('location: ../setting.php');
} catch (Exception $e) {
    mysqli_rollback($conn);
    var_dump($e);
    die();
}

