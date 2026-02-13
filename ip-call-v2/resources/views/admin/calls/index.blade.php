@extends('layouts.app')

@section('title', 'Log Panggilan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
        <h1 class="h3 mb-0 text-gray-800">Log Panggilan</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('calls.export', ['type' => 'pdf', 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category' => request('category')]) }}"
                class="btn btn-danger shadow-sm">
                <i class="fas fa-file-pdf me-2"></i> Export PDF
            </a>
            <a href="{{ route('calls.export', ['type' => 'excel', 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'category' => request('category')]) }}"
                class="btn btn-success shadow-sm">
                <i class="fas fa-file-excel me-2"></i> Export Excel
            </a>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('calls.index') }}" method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}"
                            placeholder="Start Date">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}"
                            placeholder="End Date">
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
                        <th>Timestamp</th>
                        <th>Nama Ruang</th>
                        <th>Kategori</th>
                        <th>Duration</th>
                        <th>Record</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($calls as $call)
                        <tr>
                            <td class="align-middle">
                                {{ \Carbon\Carbon::parse($call->timestamp)->timezone('Asia/Jakarta')->format('d M Y H:i:s') }}
                            </td>
                            <td class="align-middle">{{ $call->bed->username ?? '-' }}</td>
                            <td class="align-middle">{{ $call->category->name ?? '-' }}</td>
                            <td class="align-middle">{{ $call->duration }}</td>
                            <td class="align-middle">
                                <div style="height: 35px; display: flex; align-items: center;">
                                    @if($call->record)
                                        <audio controls style="height: 35px;">
                                            <source src="/{{ $call->record }}" type="audio/mpeg">
                                            Your browser does not support the audio element.
                                        </audio>
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $calls->links() }}
        </div>
    </div>
@endsection