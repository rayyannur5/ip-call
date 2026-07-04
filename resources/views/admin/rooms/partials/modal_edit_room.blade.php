<div class="modal fade" id="modal-ubah-ruang-{{ $room->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('rooms.update') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Edit Configurasi Ruang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID Ruang</label>
                                <input type="text" name="last_id" value="{{ $room->id }}" hidden/>
                                <input type="text" name="id" value="{{ $room->id }}" class="form-control" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Running Text</label>
                                <select name="running_text" class="form-select">
                                    <option value="">Tidak Ada</option>
                                    @foreach ($running_texts as $text)
                                        <option value="{{ $text->topic }}" {{ $text->topic == $room->running_text ? 'selected' : '' }}>{{ $text->topic }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Jenis Ruang</label>
                                <select name="jenis" class="form-select">
                                    <option value="Ruang" {{ $room->type == "Ruang" ? 'selected' : '' }} >Ruang</option>
                                    <option value="Kamar" {{ $room->type == "Kamar" ? 'selected' : '' }} >Kamar</option>
                                    <option value="" {{ $room->type  == "" ? 'selected' : '' }} >(Kosong)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Urutan Ruang</label>
                                <select name="type_bed" class="form-select">
                                    <option value="numeric" {{ $room->type_bed == 'numeric' ? 'selected' : '' }} >Numeric (1,2,3,..)</option>
                                    <option value="abjad" {{ $room->type_bed == 'abjad' ? 'selected' : '' }} >Abjad (A,B,C,..)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pemisah Ruang</label>
                                <select name="separator_bed" class="form-select">
                                    <option value="" {{ $room->bed_separator == "" ? 'selected' : '' }} >(Tanpa Pemisah)</option>
                                    <option value="Bed" {{ $room->bed_separator == "Bed" ? 'selected' : '' }} >Bed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Mengubah konfigurasi di bawah ini mungkin akan mengubah nama Bed dan Toilet secara otomatis.
                    </div>

                    <div class="bg-light p-3 rounded border">
                        @foreach ($room->names as $key2 => $name)
                            <div class="mb-3 border-bottom pb-3">
                                <label class="form-label small text-muted">Variasi Nama {{ $key2 + 1 }}</label>
                                <input type="text" name="last_name[]" value="{{ $name }}" hidden>
                                <input type="text" class="form-control mb-2" name="name[]" value="{{ $name }}">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <input type="file" class="form-control form-control-sm" name="audio[]">
                                    </div>
                                    <div class="col-md-5">
                                        @if($room->audio[$key2])
                                            <audio src="{{ $room->audio[$key2] }}" controls class="w-100" style="height: 30px;"></audio>
                                        @else
                                            <span class="badge bg-secondary">No Audio</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer justify-content-between bg-light">
                    <a href="{{ route('rooms.destroy', ['id' => $room->id]) }}" class="btn btn-outline-danger" onclick="return confirm('Apakah anda yakin ingin menghapus ruang ini beserta seluruh bed didalamnya?')">
                        <i class="fas fa-trash-alt me-1"></i> Hapus Ruang
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
