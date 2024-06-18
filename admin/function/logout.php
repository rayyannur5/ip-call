<?php
session_start();
session_unset();
header("location: http://localhost/ip-call/auth/login.php");
