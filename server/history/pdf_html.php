<?php
    require_once('../init_html.php');
    $_start_date = $_GET['start_date'];
    $_end_date = $_GET['end_date'];
    $start_date = date("$_start_date 00:00:00");
    $end_date = date("$_end_date 23:59:59");

    $res = queryArray("SELECT history.*, category_history.name, bed.username FROM history 
    JOIN category_history ON category_history.id = history.category_history_id
    JOIN bed ON bed.id = history.bed_id
    WHERE history.timestamp BETWEEN '$start_date' AND '$end_date'");
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
                <th>Durasi</th>
                <th>Tangal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($res as $key => $value) { ?>
                <tr>
                    <td><?= $value['id'] ?></td>
                    <td><?= $value['name'] ?></td>
                    <td><?= $value['username'] ?></td>
                    <td><?= $value['duration'] ?></td>
                    <td><?= $value['timestamp'] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</body>
</html>
