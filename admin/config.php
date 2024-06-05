<?php
date_default_timezone_set('Asia/Jakarta');

$conn = mysqli_connect("localhost", "root", "", "ip-call");

function queryArray($query)
{
    global $conn;
    try {
        $res = mysqli_query($conn, $query);
        $data = [];
        while ($i = mysqli_fetch_assoc($res)) {
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

function queryBoolean($query)
{
    global $conn;
    try {
        $res = mysqli_query($conn, $query);
        return mysqli_affected_rows($conn);
    } catch (\Throwable $th) {
        echo json_encode([
            "success" => false,
            "error" => $th->getMessage()
        ]);
        exit(0);
    }
}
