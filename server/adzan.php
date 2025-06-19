<?php

require_once ('init.php');

$res = queryArray("SELECT * FROM adzan");

echo json_encode($res);