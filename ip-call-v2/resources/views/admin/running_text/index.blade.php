@extends('layouts.app')

@section('title', 'Setting Running Text')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pt-3">
    <h1 class="h3 mb-0 text-gray-800">Setting Running Text</h1>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus me-2"></i> Add New
        </button>
    </div>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">


        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width: 40%">Topic</th>
                    <th style="width: 25%">Speed</th>
                    <th style="width: 25%">Brightness</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($texts as $text)
                <tr>
                    <td>{{ $text->topic }}</td>
                    <td>
                        <input type="number" class="form-control" id="speed_{{ $text->topic }}" value="{{ $text->speed }}">
                    </td>
                    <td>
                        <input type="number" class="form-control" id="brightness_{{ $text->topic }}" value="{{ $text->brightness }}">
                    </td>
                    <td class="d-flex gap-2">
                        <button type="button" class="btn btn-warning btn-sm" onclick="updateRow('{{ $text->topic }}')">
                            Update
                        </button>
                        <a href="{{ url('/admin/running-text/destroy/' . $text->topic) }}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createModalLabel">
                    <i class="fas fa-plus-circle me-2"></i> Add Running Text
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ url('/admin/running-text/store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="topic" class="form-label fw-bold">Topic</label>
                            <input type="text" class="form-control" id="topic" name="topic" placeholder="e.g., welcome_message" required>
                            <div class="form-text">Unique identifier / Text Content for this running text.</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="speed" class="form-label fw-bold">Speed</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                <input type="number" class="form-control" id="speed" name="speed" value="50" required>
                            </div>
                            <div class="form-text">Lower value = Faster speed.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="brightness" class="form-label fw-bold">Brightness</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-sun"></i></span>
                                <input type="number" class="form-control" id="brightness" name="brightness" value="10" required>
                            </div>
                            <div class="form-text">Range: 0-15 (usually).</div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Running Text</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Form for Update -->
<form id="update-form" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="speed" id="update_speed">
    <input type="hidden" name="brightness" id="update_brightness">
</form>

@section('scripts')
<script>
    function updateRow(topic) {
        var speed = document.getElementById('speed_' + topic).value;
        var brightness = document.getElementById('brightness_' + topic).value;

        // Populate hidden form
        document.getElementById('update_speed').value = speed;
        document.getElementById('update_brightness').value = brightness;

        // Set action
        var form = document.getElementById('update-form');
        form.action = "{{ url('/admin/running-text/update') }}/" + topic;

        // Submit
        form.submit();
    }
</script>
@endsection

@endsection
