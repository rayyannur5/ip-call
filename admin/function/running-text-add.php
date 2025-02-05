<?php

require_once('../config.php');

$running_texts = queryArray("SELECT * FROM running_text");
$name = "running_text_" . (count($running_texts) + 1);

queryBoolean("INSERT running_text VALUES('$name')");

header('location: ../setting_running_text.php');