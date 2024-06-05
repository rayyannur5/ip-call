
<?php

require_once('../../init.php');

$batas = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$first_page = ($page > 1) ? ($page * $batas) - $batas : 0;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$logs = queryArray("SELECT log.*, category_log.name FROM log JOIN category_log ON log.category_log_id = category_log.id WHERE date(timestamp) = '$date' ORDER BY timestamp DESC LIMIT $first_page, $batas");

echo json_encode([
	'success' => true,
	'data' => $logs
]);
