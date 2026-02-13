@extends('layouts.app')

@section('title', 'Monitoring System')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Monitoring System</h1>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <iframe id="monitoring-frame" style="width: 100%; height: 80vh; border: none;" title="Monitoring System"></iframe>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        var hostname = window.location.hostname;
        // Assuming Flask is running on HTTP port 8000
        $('#monitoring-frame').attr('src', 'http://' + hostname + ':5000');
    });
</script>
@endsection
