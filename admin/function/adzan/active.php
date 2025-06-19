<?php

require_once('../../config.php');

session_start();

$auto = queryArray("SELECT * FROM utils WHERE type = 'adzan_active'")[0]['value'];

if($auto == "1"){
    queryBoolean("UPDATE utils SET value = '0' WHERE type = 'adzan_active'");
} else {
    queryBoolean("UPDATE utils SET value = '1' WHERE type = 'adzan_active'");
}

$_SESSION['flash-message'] = [
    'success' => true,
    'message' => 'Aktif tersimpan'
];

header('location: ../../setting_adzan.php');