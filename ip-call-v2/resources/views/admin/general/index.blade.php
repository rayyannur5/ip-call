@extends('layouts.app')

@section('title', 'Setting Umum')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pt-3">
    <h1 class="h3 mb-0 text-gray-800">Setting Umum</h1>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Keterangan</th>
                    <th>Value</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($utils as $util)
                <tr>
                    <td class="align-middle">{{ $util->type }}</td>
                    <td>
                        <textarea name="description" class="form-control" form="form-{{ $loop->iteration }}" placeholder="Keterangan" rows="3">{{ $util->description }}</textarea>
                    </td>
                    <td>
                        <input type="number" name="value" class="form-control" value="{{ $util->value }}" form="form-{{ $loop->iteration }}">
                    </td>
                    <td>
                        <form action="{{ url('/admin/general/update') }}" method="POST" id="form-{{ $loop->iteration }}">
                            @csrf
                            <input type="hidden" name="type" value="{{ $util->type }}">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
