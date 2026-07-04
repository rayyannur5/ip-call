<div class="modal fade" id="modal-tambah-ruang" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('rooms.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Tambah Ruang Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID Ruang</label>
                                <input type="text" name="id" class="form-control" placeholder="Contoh: 101" required />
                                <div class="form-text">ID unik untuk identifikasi ruang.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Running Text Topic</label>
                                <select name="running_text" class="form-select">
                                    <option value="">-- Pilih Topic --</option>
                                    @foreach ($running_texts as $text)
                                        <option value="{{ $text->topic }}">{{ $text->topic }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Jenis Ruang</label>
                                <select name="jenis" class="form-select">
                                    <option value="Ruang">Ruang</option>
                                    <option value="Kamar">Kamar</option>
                                    <option value="">(Kosong)</option>
                                </select>
                            </div>
                             <div class="mb-3">
                                <label class="form-label fw-bold">Format Urutan Bed</label>
                                <select name="type_bed" class="form-select">
                                    <option value="numeric">Angka (1, 2, 3...)</option>
                                    <option value="abjad">Huruf (A, B, C...)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pemisah Nama</label>
                                <select name="separator_bed" class="form-select">
                                    <option value="">(Tanpa Pemisah)</option>
                                    <option value="Bed">Kata "Bed"</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="fw-bold text-primary mb-3">Konfigurasi Audio & Nama</h6>
                    
                    <div id="section-tambah-nama-ruang" class="bg-light p-3 rounded border">
                         <div class="mb-3" id="kombinasi-1">
                            <label class="form-label small text-muted">Nama Ruang 1</label>
                            <input type="text" class="form-control mb-2" placeholder="Nama Ruang (Display)" name="name[]" required>
                            <input type="file" class="form-control form-control-sm" name="audio[]">
                        </div>
                    </div>
                    
                    <div class="mt-3 d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="tambahKombinasi()">
                            <i class="fas fa-plus me-1"></i> Tambah Variasi
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="kurangKombinasi()">
                            <i class="fas fa-minus me-1"></i> Hapus
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan Ruang</button>
                </div>
            </form>
        </div>
    </div>
</div>
