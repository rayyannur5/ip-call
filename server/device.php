<?php

require_once ('init.php');

$rooms = queryArray("SELECT * FROM room");

foreach ($rooms as $key => $room) {
    $room_id = $room['id'];
    $room_name = $room['name'];
    $beds = queryArray("SELECT * FROM bed WHERE room_id = $room_id");
    $toilets = queryArray("SELECT * FROM toilet WHERE room_id = $room_id");
    $devices = array_merge($beds, $toilets);
    array_push($devices, [
        'id' => $room_id,
        'username' => "Lampu $room_name"
    ]);
    $rooms[$key]['device'] = $devices;
}

echo json_encode([
    'success'=> true,
    'data'=> $rooms
]);