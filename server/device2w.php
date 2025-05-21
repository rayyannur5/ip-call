<?php

require_once ('init.php');

$rooms = queryArray("
    SELECT 
        room.* 
    FROM room
    JOIN bed ON bed.room_id = room.id
    where bed.tw = 1
    group by room.id
");

$one_room_one_device = queryArray("SELECT value FROM utils WHERE type = 'one_room_one_device'")[0];

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
    
    if($one_room_one_device['value'] == 1){
        array_push($devices, [
            'id' => $room_id . '_room',
            'username' => "Ruang $room_name"
        ]);
    }

    $rooms[$key]['device'] = $devices;
}

echo json_encode([
    'success'=> true,
    'data'=> $rooms
]);