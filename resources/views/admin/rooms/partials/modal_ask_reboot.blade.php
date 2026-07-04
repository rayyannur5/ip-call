<div class="modal fade" id="modal-ask-reboot" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Pastikan Perubahan Data Benar!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="lead mb-0">Apakah anda yakin ingin melakukan reboot sistem?</p>
                <small class="text-muted">Tindakan ini akan me-restart layanan Nurse Call.</small>
            </div>
            <div class="modal-footer justify-content-between bg-light">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                 <form action="{{ route('rooms.reboot') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-danger shadow-sm">Ya, Reboot Sekarang</button>
                 </form>
            </div>
        </div>
    </div>
</div>
