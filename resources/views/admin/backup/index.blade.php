@extends('layouts.app')

@section('title', 'Backup & Restore')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pt-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-database me-2"></i>Backup & Restore Data</h1>
</div>

<div class="row">
    <!-- Card Backup -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-primary text-white border-0 py-3 d-flex align-items-center">
                <i class="fas fa-download me-2 fs-5"></i>
                <h5 class="card-title mb-0">Cadangkan Data (Backup)</h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <p class="text-muted">Fitur ini akan mengompresi dan mengunduh berkas arsip ZIP berisi data sistem Anda:</p>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex align-items-center px-0">
                            <i class="fas fa-database text-primary me-3 fs-5"></i>
                            <div>
                                <strong>Basis Data (MySQL)</strong>
                                <div class="small text-muted">Seluruh tabel sistem Nurse Call</div>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center px-0">
                            <i class="fas fa-folder-open text-warning me-3 fs-5"></i>
                            <div>
                                <strong>Folder Uploads</strong>
                                <div class="small text-muted">Audio, aset, dan berkas yang diunggah</div>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center px-0">
                            <i class="fas fa-microphone text-danger me-3 fs-5"></i>
                            <div>
                                <strong>Folder Records</strong>
                                <div class="small text-muted">Rekaman suara panggilan (.wav)</div>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <a id="btnBackup" href="{{ route('backup_restore.run') }}" class="btn btn-primary w-100 py-2 fs-6 shadow-sm btn-action">
                    <i class="fas fa-file-archive me-2"></i>Unduh Berkas Backup (.zip)
                </a>
            </div>
        </div>
    </div>

    <!-- Card Restore -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-danger text-white border-0 py-3 d-flex align-items-center">
                <i class="fas fa-upload me-2 fs-5"></i>
                <h5 class="card-title mb-0">Pulihkan Data (Restore)</h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <p class="text-muted">Kembalikan data basis data dan file media dari berkas cadangan ZIP yang sebelumnya telah diunduh.</p>
                    
                    <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-dark p-3 mb-4 rounded-3 d-flex">
                        <i class="fas fa-exclamation-triangle text-warning me-3 fs-4 mt-1"></i>
                        <div>
                            <strong class="text-danger">Peringatan Kritis!</strong>
                            <p class="mb-0 small text-muted mt-1">Proses pemulihan ini akan <strong>menimpa dan mengganti</strong> semua data database saat ini, file unggahan, dan rekaman audio. Data yang ada sekarang yang tidak terdapat di dalam file backup akan hilang permanen.</p>
                        </div>
                    </div>
                </div>

                <form id="restoreForm" action="{{ route('backup_restore.restore') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="backup_file" class="form-label font-weight-bold">Pilih Berkas Backup (.zip)</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".zip" required>
                        </div>
                    </div>
                    <button type="button" id="btnRestore" class="btn btn-danger w-100 py-2 fs-6 shadow-sm btn-action">
                        <i class="fas fa-sync-alt me-2"></i>Mulai Proses Pemulihan (Restore)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Handle loading state for Backup
        $('#btnBackup').on('click', function() {
            Toast.fire({
                icon: 'success',
                title: 'Backup berhasil diunduh!'
            });
        });

        // Confirmation and loading state for Restore
        $('#btnRestore').on('click', function(e) {
            e.preventDefault();
            
            const fileInput = $('#backup_file');
            if (fileInput.val() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Berkas',
                    text: 'Silakan pilih berkas ZIP cadangan terlebih dahulu.'
                });
                return;
            }

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Proses ini akan menimpa database aktif dan seluruh file media. Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Pulihkan Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show processing modal
                    Swal.fire({
                        title: 'Memproses Pemulihan...',
                        text: 'Harap tunggu, sistem sedang memulihkan basis data dan berkas media.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    $('#restoreForm').submit();
                }
            });
        });
    });
</script>

<style>
    .btn-action {
        transition: all 0.2s ease-in-out;
    }
    .btn-action:hover {
        transform: translateY(-2px);
    }
    .card {
        transition: box-shadow 0.3s ease-in-out;
    }
    .card:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>
@endsection
