<?php

require_once ('init.php');

$id = $_GET['id'];

$running_text = queryArray("SELECT * FROM running_text where topic = '$id'")[0];

echo json_encode($running_text);