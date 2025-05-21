<?php

require_once('../config.php');

session_start();


$room_id = $_POST['room_id'];
$room = queryArray("SELECT * FROM room WHERE id = $room_id")[0];
$room_name = $room['name'];
$jenis = $room['type'];
$jenis_bed = $room['type_bed'];
$separator = $room['bed_separator'] != "" ? $room['bed_separator'] . " " : '';

$bed_before = queryArray("SELECT * FROM bed WHERE room_id = $room_id");

$nomor = str_pad( count($bed_before) + 1,2,"0", STR_PAD_LEFT);

// create id
$id = "01" . ($room_id < 10 ? "0" . $room_id : $room_id) . $nomor;

// create name

function convertToLetter($number) {
    // Konversi angka ke huruf dengan basis ASCII (A = 65, B = 66, ..., Z = 90)
    if ($number >= 1 && $number <= 26) {
        return chr(64 + $number);
    }
}

if($jenis_bed == 'numeric'){
    $name = ($jenis != "" ? "$jenis " : "") . $room_name . " " . (count($bed_before) == 0 ? "" : $separator . count($bed_before) + 1);
} else if($jenis_bed == 'abjad'){
    $name = ($jenis != "" ? "$jenis " : "") . $room_name . " " . (count($bed_before) == 0 ? "" : $separator . convertToLetter(count($bed_before) + 1));
}


if(count($bed_before) == 1 ) {
    if($jenis_bed == 'numeric'){
        $name_bed_1 = $bed_before[0]['username'] . $separator . "1";
    } else if ($jenis_bed == 'abjad'){
        $name_bed_1 = $bed_before[0]['username'] . $separator . "A";
    }
    $id_bed_1 = $bed_before[0]['id'];
    queryBoolean("UPDATE bed SET username = '$name_bed_1' WHERE id = $id_bed_1");
}

// insert into db
$res = queryBoolean("INSERT INTO bed VALUES ('$id', $room_id, '$name', 100, 100, 1, 0, NULL, '', 0)");

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
header('location: ../setting.php');
