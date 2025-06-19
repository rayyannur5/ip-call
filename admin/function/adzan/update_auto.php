<?php

require_once('../../config.php');

session_start();

$auto = queryArray("SELECT * FROM utils WHERE type = 'adzan_auto'")[0]['value'];

if($auto == "1"){
    queryBoolean("UPDATE utils SET value = '0' WHERE type = 'adzan_auto'");
} else {
    queryBoolean("UPDATE utils SET value = '1' WHERE type = 'adzan_auto'");
}

$_SESSION['flash-message'] = [
    'success' => true,
    'message' => 'Otomatis tersimpan'
];

header('location: ../../setting_adzan.php');