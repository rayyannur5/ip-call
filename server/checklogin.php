<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$login = false;

session_start();
$_SESSION['user'] = 'user';

if (isset($_SESSION["user"])) {
    if ($_SESSION["user"] == "user") {
        $login = true;
    }
}

echo json_encode([
    "state" => $login,
]);
