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
<div class="card shadow-sm border-0">
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
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Nama Ruang</th>
                    <th>Time</th>
                    <th>Nurse Presence</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($log->timestamp)->timezone('Asia/Jakarta')->format('d M Y H:i:s') }}</td>
                    <td>
                        <span style="height: 10px; width: 10px; background-color: {{ 
                            match(strtoupper($log->category->name ?? '')) {
                                'INFUS' => '#28a745',
                                'TELEPON' => '#ffc107',
                                'PERAWAT' => '#fd7e14',
                                'DARURAT' => '#dc3545',
                                'CODE BLUE' => '#007bff',
                                default => '#6c757d',
                            }
                        }}; border-radius: 50%; display: inline-block; margin-right: 5px;"></span>
                        {{ $log->category->name ?? '-' }}
                    </td>
                    <td>{{ $log->bed->username ?? '-' }}</td>
                    <td>
                        @if($log->nurse_presence)
                            {{ \Carbon\CarbonInterval::seconds($log->time)->cascade()->locale('id')->forHumans() }}
                        @else
                            0 detik
                        @endif
                    </td>
                    <td>{{ $log->nurse_presence ? 'Yes' : 'No' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        {{ $logs->links() }}
    </div>
</div>
@endsection
