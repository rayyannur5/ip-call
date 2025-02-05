<?php
    require_once('../init_html.php');
    
    $_start_date = $_GET['start_date'];
    $_end_date = $_GET['end_date'];
    $start_date = date("$_start_date 00:00:00");
    $end_date = date("$_end_date 23:59:59");

    $res = queryArray("
        SELECT 
            log.id, 
            category_log.name,
            coalesce(bed.username, toilet.username) as username,
            sec_to_time(log.time) as time,
            case when log.nurse_presence = 1 then 'Ya' else 'Tidak' end as presence,
            log.timestamp
        FROM log 
        JOIN category_log ON category_log.id = log.category_log_id
        LEFT JOIN bed ON bed.id = log.device_id
        LEFT JOIN toilet on toilet.id = log.device_id
        WHERE log.timestamp BETWEEN '$start_date' AND '$end_date'
    ");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= date('Y-m-d H:i:s') ?></title>
</head>

<style>
#log_table {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#log_table td, #log_table th {
  border: 1px solid #ddd;
  padding: 8px;
}

#log_table tr:nth-child(even){background-color: #f2f2f2;}

#log_table tr:hover {background-color: #ddd;}

#log_table th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}
</style>

<body>
    <table id="log_table">
        <thead>
            <tr>
                <th>id</th>
                <th>Kategori</th>
                <th>Ruang</th>
                <th>Waktu</th>
                <th>Kehadiran Perawat</th>
                <th>timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($res as $key => $value) { ?>
                <tr>
                    <td><?= $value['id'] ?></td>
                    <td><?= $value['name'] ?></td>
                    <td><?= $value['username'] ?></td>
                    <td><?= $value['time'] ?></td>
                    <td><?= $value['presence'] ?></td>
                    <td><?= $value['timestamp'] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</body>
</html>
