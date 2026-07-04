@extends('layouts.app')

@section('title', 'Setting Ruang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pt-3">
    <h1 class="h3 mb-0 text-gray-800">Setting Ruang</h1>
    <div class="d-flex gap-2">
        {{-- Bulk Actions (Mockups) --}}
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Quick Actions
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('rooms.bulk_mode', ['mode' => 2]) }}">Jadikan CodeBlue Semua</a></li>
                <li><a class="dropdown-item" href="{{ route('rooms.bulk_mode', ['mode' => 0]) }}">Jadikan Emergency Semua</a></li>
                <li><a class="dropdown-item" href="{{ route('rooms.bulk_tw', ['tw' => 0]) }}">Jadikan 1W Semua</a></li>
                <li><a class="dropdown-item" href="{{ route('rooms.bulk_tw', ['tw' => 1]) }}">Jadikan 2W Semua</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('rooms.bulk_cable', ['cable' => 1]) }}">Jadikan Cable Semua</a></li>
                <li><a class="dropdown-item" href="{{ route('rooms.bulk_cable', ['cable' => 0]) }}">Jadikan Non-Cable Semua</a></li>
            </ul>
        </div>
        
        <button class="btn btn-primary d-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-tambah-ruang">
            <i class="fas fa-plus me-2"></i> Tambah Ruang
        </button>
        <button class="btn btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-ask-reboot">
            <i class="fas fa-power-off me-2"></i> Update & Reboot
        </button>
    </div>
</div>


<div class="row">
    @foreach ($rooms as $room)
        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden" style="zoom: 0.85;">
                {{-- Room Header --}}
                <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title fw-bold text-dark mb-0">{{ $room->name }}</h5>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('rooms.bypass', ['id' => $room->id, 'type' => 'room']) }}" 
                           class="btn btn-sm {{ $room->bypass ? 'btn-danger' : 'btn-outline-secondary' }}" 
                           title="Toggle Bypass">
                            {{ $room->bypass ? 'ON' : 'OFF' }}
                        </a>
                        <button class="btn btn-sm btn-light text-primary" data-bs-toggle="modal" data-bs-target="#modal-ubah-ruang-{{ $room->id }}">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body bg-light">
                    {{-- Beds Section --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Beds</span>
                            <form action="{{ route('beds.store') }}" method="post">
                                @csrf
                                <input type="text" name="room_id" value="{{ $room->id }}" hidden />
                                <button class="btn btn-xs btn-primary rounded-circle shadow-none" type="submit" style="width: 24px; height: 24px; padding: 0;">
                                    <i class="fas fa-plus" style="font-size: 10px;"></i>
                                </button>
                            </form>
                        </div>
                        
                        <div class="d-flex flex-column gap-2">
                            @forelse ($room->beds as $bed)
                                <div class="bg-white p-2 rounded shadow-sm border-start border-4 border-info">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="d-flex align-items-center overflow-hidden">
                                            <div class="badge bg-secondary me-2">{{ $bed->id }}</div>
                                            <div class="text-truncate fw-medium" title="{{ $bed->username }}">{{ $bed->username }}</div>
                                        </div>
                                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="modal" data-bs-target="#modal-ubah-bed-{{ $bed->id }}">
                                            <i class="fas fa-sliders-h fa-xs"></i>
                                        </button>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        @if($bed->cable)
                                            <span class="badge rounded-pill text-bg-success" style="font-size: 0.65rem;" title="Cable: Phone = {{ $bed->phone }}">
                                                <i class="fas fa-link"></i> Cable
                                            </span>
                                        @endif
                                        <span class="badge rounded-pill {{ $bed->mode == 2 ? 'text-bg-primary' : 'text-bg-danger' }}" 
                                              style="font-size: 0.65rem;">
                                            {{ $bed->mode == 2 ? 'CodeBlue' : 'Emergency' }}
                                        </span>
                                        <span class="badge rounded-pill {{ $bed->tw ? 'text-bg-primary' : 'text-bg-warning' }}" 
                                              style="font-size: 0.65rem;">
                                            {{ $bed->tw ? '2W' : '1W' }}
                                        </span>
                                        <a href="{{ route('rooms.bypass', ['id' => $bed->id, 'type' => 'bed']) }}" 
                                           class="btn btn-sm {{ $bed->bypass ? 'btn-danger' : 'btn-outline-secondary' }} py-0 px-1"
                                           style="font-size: 0.65rem; line-height: 1.4;"
                                           title="Toggle Bypass">
                                            {{ $bed->bypass ? 'BYP' : 'ACT' }}
                                        </a>
                                    </div>
                                </div>
                                @include('admin.rooms.partials.modal_edit_bed', ['bed' => $bed])
                            @empty
                                <div class="text-center text-muted fst-italic py-2 small">No beds added</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Toilets Section --}}
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Toilets</span>
                            <form action="{{ route('toilets.store') }}" method="post">
                                @csrf
                                <input type="text" name="room_id" value="{{ $room->id }}" hidden />
                                <button class="btn btn-xs btn-primary rounded-circle shadow-none" type="submit" style="width: 24px; height: 24px; padding: 0;">
                                    <i class="fas fa-plus" style="font-size: 10px;"></i>
                                </button>
                            </form>
                        </div>
                        
                        <div class="d-flex flex-column gap-2">
                            @forelse ($room->toilets as $toilet)
                                <div class="bg-white p-2 rounded shadow-sm d-flex justify-content-between align-items-center border-start border-4 border-warning">
                                    <div class="d-flex align-items-center overflow-hidden">
                                        <div class="badge bg-secondary me-2">{{ $toilet->id }}</div>
                                        <div class="text-truncate fw-medium small">{{ $toilet->username }}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="{{ route('rooms.bypass', ['id' => $toilet->id, 'type' => 'toilet']) }}" 
                                           class="badge rounded-pill {{ $toilet->bypass ? 'text-bg-danger' : 'text-bg-secondary' }} text-decoration-none"
                                           style="font-size: 0.65rem; cursor: pointer;">
                                            {{ $toilet->bypass ? 'BYP' : 'ACT' }}
                                        </a>
                                        <a href="{{ route('toilets.destroy', ['id' => $toilet->id]) }}" class="text-danger ms-2">
                                            <i class="fas fa-times fa-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted fst-italic py-2 small">No toilets added</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modals for Room --}}
        @include('admin.rooms.partials.modal_edit_room', ['room' => $room])
    @endforeach
</div>

{{-- Global Modals --}}
@include('admin.rooms.partials.modal_ask_reboot')
@include('admin.rooms.partials.modal_add_room')

@endsection

@section('scripts')
<script>
    // Include script for dynamic inputs here or in the partial
    var counterNama = 1;
    function tambahKombinasi() {
        counterNama++;
        var html = `
            <div class="mb-3" id="kombinasi-${counterNama}">
                <label class="form-label small text-muted">Nama Ruang ${counterNama}</label>
                <input type="text" class="form-control" placeholder="Ketik disini" name="name[]" required>
                <input type="file" class="form-control mt-2" name="audio[]">
            </div>
        `;
        document.getElementById('section-tambah-nama-ruang').insertAdjacentHTML('beforeend', html);
    }

    function kurangKombinasi() {
        if(counterNama == 1) return;
        document.getElementById(`kombinasi-${counterNama}`).remove();
        counterNama--;
    }
</script>
@endsection
