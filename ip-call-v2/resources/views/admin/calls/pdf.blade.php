<!DOCTYPE html>
<html>
<head>
    <title>Call Logs Export</title>
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
    <h1>Call Logs</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Bed ID</th>
                <th>Category</th>
                <th>Duration</th>
                <th>Record</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($calls as $call)
            <tr>
                <td>{{ $call->id }}</td>
                <td>{{ $call->bed_id }}</td>
                <td>{{ $call->category_history_id }}</td>
                <td>{{ $call->duration }}</td>
                <td>{{ $call->record }}</td>
                <td>{{ $call->timestamp }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
