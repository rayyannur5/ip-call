<?php

require_once('../init.php');

$id = $_GET['id'];
$ip = $_GET['ip'];

$data = queryBoolean("UPDATE bed SET ip = '$ip' WHERE id = '$id'");
