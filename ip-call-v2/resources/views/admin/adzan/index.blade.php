@extends('layouts.app')

@section('title', 'Informasi Adzan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pt-3">
    <h1 class="h3 mb-0 text-gray-800">Informasi Adzan</h1>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ url('admin/adzan/update') }}" method="POST">
            @csrf
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center" style="gap: 30px">
                    <!-- Toggle Active -->
                    <div class="d-flex align-items-center" style="gap: 10px">
                        <label class="switch">
                            <input type="checkbox" name="adzan_active" id="adzan-aktif" {{ $active == 1 ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                        <label for="adzan-aktif" class="mb-0 fw-bold" style="cursor: pointer;">Aktif</label>
                    </div>
                    
                    <!-- Toggle Auto -->
                    <div class="d-flex align-items-center" style="gap: 10px">
                        <label class="switch">
                            <input type="checkbox" name="adzan_auto" id="switch-otomatis" {{ $auto == 1 ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                        <label for="switch-otomatis" class="mb-0 fw-bold" style="cursor: pointer;">Otomatis</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Sholat</th>
                        <th>Waktu</th>
                        <th>Audio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($adzans as $adzan)
                    <tr>
                        <td>{{ $adzan->name }}</td>
                        <td>
                            <input type="text" name="{{ $adzan->key }}" class="form-control timepicker" value="{{ $adzan->value }}" {{ $auto == 1 ? 'readonly' : '' }}>
                        </td>
                         <td>-</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<style>
/* Apple-style Toggle Switch */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 28px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(22px);
  -ms-transform: translateX(22px);
  transform: translateX(22px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>

<link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/flatpickr.min.css') }}">
<script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
        });
    });
</script>
@endsection
