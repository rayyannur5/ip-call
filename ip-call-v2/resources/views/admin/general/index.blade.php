@extends('layouts.app')

@section('title', 'Setting Umum')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pt-3">
    <h1 class="h3 mb-0 text-gray-800">Setting Umum</h1>
</div>
<div class="card">
    <div class="card-body">

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($utils as $util)
                <tr>
                    <td>{{ $util->type }}</td>
                    <td>
                        <form action="{{ url('/admin/general/update') }}" method="POST" class="d-flex">
                            @csrf
                            <input type="hidden" name="type" value="{{ $util->type }}">
                            <input type="number" name="value" class="form-control me-2" value="{{ $util->value }}">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
