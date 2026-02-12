<!-- Modal Templates -->
<!-- Member Details Modal -->
<div class="modal fade" id="memberDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Anggota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="memberDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loan Details Modal -->
<div class="modal fade" id="loanDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pinjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="loanDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Address Search Modal -->
<div class="modal fade" id="addressSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cari Alamat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="addressSearch" placeholder="Ketik nama alamat...">
                    <select class="form-select" id="addressType" style="max-width: 150px;">
                        <option value="all">Semua</option>
                        <option value="province">Provinsi</option>
                        <option value="regency">Kabupaten</option>
                        <option value="district">Kecamatan</option>
                        <option value="village">Desa</option>
                    </select>
                    <button class="btn btn-outline-secondary" id="searchAddressBtn">Cari</button>
                </div>
                <div id="addressResults" class="list-group">
                    <div class="text-center text-muted">Ketik untuk mencari alamat</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Summary Modal -->
<div class="modal fade" id="activitySummaryModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ringkasan Aktivitas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="summaryStartDate" value="<?= date('Y-m-01') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="summaryEndDate" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary d-block" id="loadSummaryBtn">Muat Ringkasan</button>
                    </div>
                </form>
                <div id="activitySummaryContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmContent">
                <!-- Content will be dynamically inserted -->
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center" style="z-index: 9999;">
    <div class="text-center text-white">
        <div class="spinner-border mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div id="loadingText">Loading...</div>
    </div>
</div>

<script>
// Initialize KSP AJAX system
$(document).ready(function() {
    if (typeof window.KSP !== 'undefined') {
        window.KSP.init();
    }
});
</script>
