<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>


</div>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="anggota-create">Tambah Anggota</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="anggota" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
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

<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>


</div>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="anggota-create">Tambah Anggota</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="anggota" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
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

<!-- Form Container -->
<div class="card" id="form-container">
    <div class="card-header" id="form-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-person-plus me-2"></i>
            Form Data Anggota Baru
        </h5>
    </div>
    <div class="card-body" id="form-content">
        <form method="post" action="<?= base_url('anggota/store') ?>" id="anggota-form" data-validation>
            <div class="row g-3" id="form-fields">
                <!-- Identitas Anggota -->
                <div class="col-12">
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-person-badge me-2"></i>Identitas Anggota
                    </h6>
                </div>
                
                <div class="col-md-4" id="field-no-anggota">
                    <label class="form-label">No Anggota *</label>
                    <input type="text" name="no_anggota" class="form-control" required id="input-no-anggota" 
                           data-validate="required|min:5" data-validate-realtime="true" readonly>
                    <small class="form-text text-muted">Nomor unik anggota</small>
                </div>
                
                <div class="col-md-8" id="field-nama-lengkap">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" class="form-control" required id="input-nama-lengkap" 
                           data-validate="required|min:3" data-validate-realtime="true" placeholder="Nama sesuai KTP">
                    <small class="form-text text-muted">Nama sesuai KTP</small>
                </div>
                
                <div class="col-md-6" id="field-tempat-lahir">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control" id="input-tempat-lahir">
                </div>
                
                <div class="col-md-6" id="field-tanggal-lahir">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" id="input-tanggal-lahir">
                </div>
                
                <div class="col-md-4" id="field-jenis-kelamin">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control" id="input-jenis-kelamin">
                        <option value="">Pilih</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                
                <div class="col-md-4" id="field-status-perkawinan">
                    <label class="form-label">Status Perkawinan</label>
                    <select name="status_perkawinan" class="form-control" id="input-status-perkawinan">
                        <option value="">Pilih</option>
                        <option value="Belum Kawin">Belum Kawin</option>
                        <option value="Kawin">Kawin</option>
                        <option value="Cerai Hidup">Cerai Hidup</option>
                        <option value="Cerai Mati">Cerai Mati</option>
                    </select>
                </div>
                
                <div class="col-md-4" id="field-agama">
                    <label class="form-label">Agama</label>
                    <select name="agama" class="form-control" id="input-agama">
                        <option value="">Pilih</option>
                        <option value="Islam">Islam</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Katolik">Katolik</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Buddha">Buddha</option>
                        <option value="Konghucu">Konghucu</option>
                    </select>
                </div>
                
                <!-- Kontak Informasi -->
                <div class="col-12 mt-4">
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-telephone me-2"></i>Informasi Kontak
                    </h6>
                </div>
                
                <div class="col-md-6" id="field-no-telepon">
                    <label class="form-label">No. Telepon</label>
                    <input type="tel" name="no_telepon" class="form-control" id="input-no-telepon" 
                           data-validate="phone">
                </div>
                
                <div class="col-md-6" id="field-email">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" id="input-email" 
                           data-validate="email" placeholder="email@example.com">
                </div>
                
                <div class="col-12" id="field-alamat">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="3" id="input-alamat"></textarea>
                </div>
                
                <!-- Informasi Pekerjaan -->
                <div class="col-12 mt-4">
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-briefcase me-2"></i>Informasi Pekerjaan
                    </h6>
                </div>
                
                <div class="col-md-6" id="field-pekerjaan">
                    <label class="form-label">Pekerjaan</label>
                    <input type="text" name="pekerjaan" class="form-control" id="input-pekerjaan">
                </div>
                
                <div class="col-md-6" id="field-penghasilan">
                    <label class="form-label">Penghasilan per Bulan</label>
                    <input type="number" name="penghasilan" class="form-control" id="input-penghasilan">
                </div>
                
                <!-- Status Anggota -->
                <div class="col-12 mt-4">
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-shield-check me-2"></i>Status Anggota
                    </h6>
                </div>
                
                <div class="col-md-6" id="field-status-anggota">
                    <label class="form-label">Status Anggota</label>
                    <select name="status_anggota" class="form-control" id="input-status-anggota">
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                        <option value="Calon">Calon</option>
                    </select>
                </div>
                
                <div class="col-md-6" id="field-tanggal-bergabung">
                    <label class="form-label">Tanggal Bergabung</label>
                    <input type="date" name="tanggal_bergabung" class="form-control" id="input-tanggal-bergabung" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between" id="form-actions">
                        <a href="<?= base_url('anggota') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Batal
                        </a>
                        <div class="btn-group">
                            <button type="reset" class="btn btn-outline-warning" id="btn-reset">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary" id="btn-save">
                                <i class="bi bi-check-circle me-2"></i>Simpan Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

</div>

<!-- JavaScript for Form Handling -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('anggota-form');
    const noAnggotaInput = document.getElementById('input-no-anggota');
    const namaLengkapInput = document.getElementById('input-nama-lengkap');
    
    // Auto-generate no anggota if empty
    if (noAnggotaInput && !noAnggotaInput.value) {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        noAnggotaInput.value = `ANG${year}${month}${random}`;
    }
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            if (!noAnggotaInput.value || !namaLengkapInput.value) {
                if (typeof showNotification !== 'undefined') {
                    showNotification('No Anggota dan Nama Lengkap wajib diisi!', 'danger');
                }
                return;
            }
            
            // Show loading
            const saveBtn = document.getElementById('btn-save');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Menyimpan...';
            saveBtn.disabled = true;
            
            // Submit form
            this.submit();
        });
    }
    
    // Reset form handler
    const resetBtn = document.getElementById('btn-reset');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (confirm('Apakah Anda yakin ingin mereset form?')) {
                form.reset();
                // Re-generate no anggota
                if (noAnggotaInput) {
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                    noAnggotaInput.value = `ANG${year}${month}${random}`;
                }
            }
        });
    }
    
    console.log('Anggota create form initialized');
});

// Global function for save button
function saveAnggota() {
    document.getElementById('anggota-form').dispatchEvent(new Event('submit'));
}
</script>

<style>
/* Form-specific styles */
.form-label {
    font-weight: 600;
    color: #495057;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page anggota - create initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Tambah Anggota', 'anggota-create');
    }
});

// Global functions
function saveAnggota() {
    const form = document.querySelector('form');
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
</style>