<?php

require_once ('init.php');

$res = queryArray("SELECT * FROM playlist");

echo json_encode($res);