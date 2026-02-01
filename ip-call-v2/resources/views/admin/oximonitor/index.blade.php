@extends('layouts.app')

@section('title', 'OxiMonitor Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pt-3">
    <h1 class="h3 mb-0 text-gray-800">OxiMonitor Logs</h1>
</div>
<div class="card">
    <div class="card-body">
         <table class="table table-striped">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Bed ID</th>
                    <th>Volume</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log->timestamp }}</td>
                    <td>{{ $log->bed_id }}</td>
                    <td>{{ $log->vol }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $logs->links() }}
    </div>
</div>
@endsection
