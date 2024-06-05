<?php

$hostname   = 'localhost';
$user       = 'root';
$password   = '';
$db         = 'ip-call';

date_default_timezone_set('Asia/Jakarta');

try {
    $conn = mysqli_connect($hostname, $user, $password, $db);
} catch (\Throwable $th) {
    echo json_encode([
        "success" => false,
        "error" => $th->getMessage()
    ]);
    exit(0);
}

function queryArray($query) {
    global $conn;
    try {
        $res = mysqli_query($conn,$query);
        $data = [];
        while($i = mysqli_fetch_assoc($res)){
            array_push($data, $i);
        }
        return $data;
    } catch (\Throwable $th) {
        echo json_encode([
            "success" => false,
            "error" => $th->getMessage()
        ]);
        exit(0);
    }
}

function queryBoolean($query) {
    global $conn;
    try {
        $res = mysqli_query($conn,$query);
        return mysqli_affected_rows($conn);
    } catch (\Throwable $th) {
        echo json_encode([
            "success" => false,
            "error" => $th->getMessage()
        ]);
        exit(0);
    }
}

header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
