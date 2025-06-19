<?php

require_once('../init.php');

$vol = $_GET['vol'];

queryBoolean("UPDATE bed SET vol = $vol");