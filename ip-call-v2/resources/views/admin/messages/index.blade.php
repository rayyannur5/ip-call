@extends('layouts.app')

@section('title', 'Log Pesan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pt-3">
    <h1 class="h3 mb-0 text-gray-800">Log Pesan</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('messages.export', ['type' => 'pdf', 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category' => request('category')]) }}" class="btn btn-danger shadow-sm">
            <i class="fas fa-file-pdf me-2"></i> Export PDF
        </a>
        <a href="{{ route('messages.export', ['type' => 'excel', 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category' => request('category')]) }}" class="btn btn-success shadow-sm">
            <i class="fas fa-file-excel me-2"></i> Export Excel
        </a>
    </div>
</div>
<div class="card">
<div class="card-body">
        <form action="{{ route('messages.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Start Date">
                </div>
                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="End Date">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('messages.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>



        <table class="table table-bordered table-striped">
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
                    {{-- Ideally fetch Category Name via relationship --}}
                    <td>{{ $log->value }}</td>
                    <td>{{ $log->device_id }}</td>
                    <td>{{ $log->time }}</td>
                    <td>{{ $log->nurse_presence ? 'Yes' : 'No' }}</td>
                    <td>{{ $log->timestamp }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        {{ $logs->links() }}
    </div>
</div>
@endsection
