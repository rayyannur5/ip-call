<div class="modal fade" id="modal-ubah-bed-{{ $bed->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('beds.update') }}" method="post">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-bed me-2"></i>Edit {{ $bed->username }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <a href="http://{{ $bed->ip }}:5000" target="_blank" class="badge bg-light text-dark text-decoration-none border px-3 py-2 fs-6">
                            <i class="fas fa-network-wired me-2 text-primary"></i>IP: {{ $bed->ip }}
                        </a>
                    </div>
                    
                    <input type="text" value="{{ $bed->id }}" name="id" hidden />
                    
                    <div class="mb-4 form-check form-switch p-3 bg-light rounded border d-flex align-items-center justify-content-between">
                        <label class="form-check-label fw-bold mb-0" for="switch-tw-{{ $bed->id }}">Twoway Communication</label>
                        <input class="form-check-input ms-0" type="checkbox" role="switch" name="tw" id="switch-tw-{{ $bed->id }}" {{ $bed->tw ? 'checked' : '' }} style="margin-left: 0 !important; float: none;">
                    </div>
                    
                    <div class="row g-3">
                         <div class="col-md-4">
                            <label class="form-label small text-muted">Mode</label>
                            <input type="number" class="form-control text-center" name="mode" value="{{ $bed->mode }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Speaker Volume</label>
                            <input type="number" class="form-control text-center" name="vol" value="{{ $bed->vol }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Mic Sensitivity</label>
                            <input type="number" class="form-control text-center" name="mic" value="{{ $bed->mic }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between bg-light">
                    @if ($loop->last)
                        <a href="{{ route('beds.destroy', ['id' => $bed->id]) }}" class="btn btn-outline-danger btn-sm" onclick="return confirm('Hapus bed ini?')">
                            <i class="fas fa-trash me-1"></i> Hapus
                        </a>
                    @else
                        <div></div>
                    @endif
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info text-white px-4 shadow-sm">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
