@extends('layouts.app')

@section('title', 'Setting Musik (Murotal)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pt-3">
    <h1 class="h3 mb-0 text-gray-800">Setting Musik (Murotal)</h1>
    <div class="d-flex gap-2">
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-tambah-playlist"><i class="fa fa-plus me-2"></i> Tambah</button>
        <a href="{{ url('admin/playlist/write-config') }}" class="btn btn-danger shadow-sm"><i class="fa fa-check me-2"></i> Update Config</a>
    </div>
</div>


        <div class="row">
            @foreach($playlists as $playlist)
                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">{{ $playlist->name }} ({{ \Carbon\Carbon::parse($playlist->start_time)->format('H:i') }} => {{ \Carbon\Carbon::parse($playlist->end_time)->format('H:i') }})</h3>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modal-tambah-playlist-item-{{ $playlist->id }}" title="Tambah Item"><i class="fas fa-plus"></i></button>
                                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modal-ubah-playlist-{{ $playlist->id }}" title="Edit Playlist">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column mb-3">
                                @foreach($playlist->items as $item)
                                <div class="bg-info rounded p-2 mb-2 d-flex align-items-center justify-content-between" style="gap: 10px">
                                    <div class="text-white text-truncate" style="max-width: 150px;" title="{{ $item->path }}">{{ $item->path }}</div>
                                    <!-- Assume files are served from /playlist/music relative to public or some accessible URL. 
                                         Legacy served from /ip-call/playlist/music/. 
                                         Ideally we serve from storage or public. 
                                         If we saved to base_path('../playlist/music'), we need a symlink or route to serve it.
                                         For now, let's keep the legacy URL pattern if the server configuration allows it.
                                    -->
                                    <audio src="/ip-call/playlist/music/{{ $item->path }}" controls class="w-100 mb-0" style="height: 30px"></audio>
                                    <a href="{{ url('admin/playlist/item/destroy/' . $item->id . '/' . $item->ord) }}" class="btn m-0 p-0 text-white">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Ubah Playlist -->
                <div class="modal fade" id="modal-ubah-playlist-{{ $playlist->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ url('admin/playlist/update') }}" method="post">
                                @csrf
                                <div class="modal-header">
                                    <h4 class="modal-title">Ubah Playlist</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="{{ $playlist->id }}">
                                    <div class="form-group mb-2">
                                        <label>Nama Playlist</label>
                                        <input type="text" name="name" value="{{ $playlist->name }}" class="form-control">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Volume</label>
                                        <input type="number" name="volume" value="{{ $playlist->volume }}" min="0" max="100" class="form-control">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Waktu Mulai</label>
                                        <input type="text" name="start" value="{{ \Carbon\Carbon::parse($playlist->start_time)->format('H:i') }}" class="form-control timepicker">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Waktu Selesai</label>
                                        <input type="text" name="end" value="{{ \Carbon\Carbon::parse($playlist->end_time)->format('H:i') }}" class="form-control timepicker">
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    <div>
                                        <a href="{{ url('admin/playlist/destroy/' . $playlist->id) }}" class="btn btn-danger">Hapus</a>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Tambah Item -->
                <div class="modal fade" id="modal-tambah-playlist-item-{{ $playlist->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ url('admin/playlist/item/store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header">
                                    <h4 class="modal-title">Tambah Playlist Item</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group mb-2">
                                        <label>File</label>
                                        <input type="file" name="file" class="form-control" required>
                                        <input type="hidden" name="playlist_id" value="{{ $playlist->id }}">
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>


<!-- Modal Tambah Playlist -->
<div class="modal fade" id="modal-tambah-playlist" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ url('admin/playlist/store') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Playlist</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label>Nama Playlist</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group mb-2">
                        <label>Volume</label>
                        <input type="number" name="volume" min="0" max="100" class="form-control" value="100">
                    </div>
                    <div class="form-group mb-2">
                        <label>Waktu Mulai</label>
                        <input type="text" name="start" class="form-control timepicker">
                    </div>
                    <div class="form-group mb-2">
                        <label>Waktu Selesai</label>
                        <input type="text" name="end" class="form-control timepicker">
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
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
