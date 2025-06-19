<?php

require_once('../config.php');

$topic = queryArray("SELECT topic FROM running_text ORDER BY topic DESC LIMIT 1")[0]['topic'];

queryBoolean("DELETE FROM running_text WHERE topic = '$topic'");

header('location: ../setting_running_text.php');