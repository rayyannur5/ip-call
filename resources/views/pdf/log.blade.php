<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ date('Y-m-d H:i:s') }}</title>
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
            @foreach ($logs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $log->name }}</td>
                    <td>{{ $log->username }}</td>
                    <td>{{ $log->time }}</td>
                    <td>{{ $log->presence }}</td>
                    <td>{{ $log->timestamp }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
