@extends('layouts.app')

@section('title', 'Setting Audio')



@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pt-3">
    <h1 class="h3 mb-0 text-gray-800">Setting Audio</h1>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal"
            data-bs-target="#modal-tambah-list">
            <i class="fas fa-plus me-2"></i> Tambah Audio
        </button>
    </div>
</div>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="ps-4">Waktu</th>
                                    <th>Volume</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($list as $item)
                                    <tr>
                                        <td class="ps-4 fw-bold">{{ $item->time }}</td>
                                        <td>
                                            <span class="badge bg-info text-dark">{{ $item->vol }}%</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ url('admin/audio/destroy/' . $item->id) }}"
                                                class="btn btn-outline-danger btn-sm rounded-circle shadow-sm"
                                                onclick="return confirm('Apakah anda yakin ingin menghapus data ini?')"
                                                title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                                            Belum ada data audio tersimpan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modal-tambah-list" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg curved-modal">
                <form action="{{ url('admin/audio/store') }}" method="post">
                    @csrf
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Tambah Audio Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Waktu Putar</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="far fa-clock text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0 date-time-picker" name="time" placeholder="Pilih waktu..." required>
                            </div>
                            <div class="form-text">Format 24 jam (HH:MM)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Volume (0-100)</label>
                            <div class="range-wrap">
                                <input type="range" class="form-range" name="vol" min="0" max="100" id="volRange" oninput="this.nextElementSibling.value = this.value">
                                <output class="fw-bold text-primary">50</output>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-link text-secondary text-decoration-none"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <!-- Flatpickr -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/airbnb.css') }}">
    <script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $(".date-time-picker").flatpickr({
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });
        });
    </script>
@endsection
