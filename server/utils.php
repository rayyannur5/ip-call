<?php

require_once ('init.php');

$utils = queryArray("SELECT * FROM utils");

echo json_encode([
    'success'=> true,
    'data'=> $utils
]);