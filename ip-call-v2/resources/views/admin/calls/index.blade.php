@extends('layouts.app')

@section('title', 'Log Panggilan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pt-3">
    <h1 class="h3 mb-0 text-gray-800">Log Panggilan</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('calls.export', ['type' => 'pdf', 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category' => request('category')]) }}" class="btn btn-danger shadow-sm">
            <i class="fas fa-file-pdf me-2"></i> Export PDF
        </a>
        <a href="{{ route('calls.export', ['type' => 'excel', 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category' => request('category')]) }}" class="btn btn-success shadow-sm">
            <i class="fas fa-file-excel me-2"></i> Export Excel
        </a>
    </div>
</div>
<div class="card">
<div class="card-body">
        <form action="{{ route('calls.index') }}" method="GET" class="mb-3">
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
                    <a href="{{ route('calls.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>



        <table class="table table-bordered table-striped">
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
        
        {{ $calls->links() }}
    </div>
</div>
@endsection
