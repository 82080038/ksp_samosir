<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>


<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="anggota">Data Anggota</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-primary" data-action="crud-add">
                <i class="bi bi-plus-circle"></i> Tambah
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-action="crud-refresh">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-info" data-action="crud-export" data-format="excel">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Search and Filter Section -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="crud-search" class="form-label">Cari Anggota</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="crud-search" placeholder="Nama, No. Anggota, atau Email...">
                </div>
            </div>
            <div class="col-md-3">
                <label for="filter-status" class="form-label">Status</label>
                <select class="form-select" id="filter-status">
                    <option value="">Semua Status</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                    <option value="Calon">Calon</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-date" class="form-label">Tanggal Bergabung</label>
                <input type="date" class="form-control" id="filter-date">
            </div>
        </div>
        
        <!-- Bulk Actions (Hidden by default) -->
        <div class="crud-bulk-actions mt-3" style="display: none;">
            <div class="d-flex justify-content-between align-items-center">
                <span class="crud-bulk-count text-muted">0 item(s) selected</span>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-warning" data-action="crud-bulk-edit">
                        <i class="bi bi-pencil"></i> Edit Selected
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-action="crud-bulk-delete">
                        <i class="bi bi-trash"></i> Delete Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Table Section -->
<div class="card">
    <div class="card-body">
        <!-- Responsive Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="crud-table">
                <thead class="table-dark">
                    <tr>
                        <th width="50">
                            <input type="checkbox" class="form-check-input crud-select-all">
                        </th>
                        <th>No. Anggota</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th>Tanggal Bergabung</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded via AJAX -->
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div id="crud-pagination" class="mt-3">
            <!-- Pagination will be rendered via AJAX -->
        </div>
    </div>
</div>

</div>

<!-- CRUD Modal (Hidden by default) -->
<div class="modal fade" id="crud-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Data Anggota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="crud-form" data-validation>
                    <div class="row g-3">
                        <!-- Identitas Anggota -->
                        <div class="col-12">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-person-badge me-2"></i>Identitas Anggota
                            </h6>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">No. Anggota *</label>
                            <input type="text" name="no_anggota" class="form-control" 
                                   data-validate="required|min:5" readonly>
                            <small class="form-text text-muted">Nomor unik anggota</small>
                        </div>
                        
                        <div class="col-md-8">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="nama_lengkap" class="form-control" 
                                   data-validate="required|min:3" placeholder="Nama sesuai KTP">
                            <small class="form-text text-muted">Nama sesuai KTP</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   data-validate="email" placeholder="email@example.com">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Telepon</label>
                            <input type="tel" name="telepon" class="form-control" 
                                   data-validate="phone" placeholder="08123456789">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <!-- Status Anggota -->
                        <div class="col-12 mt-4">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-shield-check me-2"></i>Status Anggota
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Status Anggota</label>
                            <select name="status_anggota" class="form-control">
                                <option value="Calon">Calon</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Bergabung</label>
                            <input type="date" name="tanggal_bergabung" class="form-control" 
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="crud-form" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for CRUD Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Enhanced CRUD Anggota page initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Data Anggota', 'anggota-index');
    }
    
    // Initialize CRUD framework
    if (typeof KSP !== 'undefined' && KSP.CRUD) {
        KSP.CRUD.init('anggota', {
            apiEndpoint: '<?= base_url('api/anggota') ?>',
            itemsPerPage: 10,
            enableInlineEdit: true,
            enableBulkActions: true,
            enableInstantSearch: true
        });
    }
});

// Global functions
function saveAnggota() {
    const form = document.querySelector('#crud-form');
    if (form) {
        form.dispatchEvent(new Event('submit'));
    }
}
</script>

<style>
/* Page-specific styles */
.page-title {
    font-weight: 700;
}

.main-content {
    min-height: 400px;
}

/* Table enhancements */
.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

/* Loading state */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Bulk actions */
.crud-bulk-actions {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #dee2e6;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
