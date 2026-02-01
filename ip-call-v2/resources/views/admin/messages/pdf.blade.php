<!DOCTYPE html>
<html>
<head>
    <title>Message Logs Export</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Message Logs</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Category</th>
                <th>Value</th>
                <th>Device ID</th>
                <th>Time</th>
                <th>Nurse Presence</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->category_log_id }}</td>
                <td>{{ $log->value }}</td>
                <td>{{ $log->device_id }}</td>
                <td>{{ $log->time }}</td>
                <td>{{ $log->nurse_presence ? 'Yes' : 'No' }}</td>
                <td>{{ $log->timestamp }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
