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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="anggota-edit">Edit Anggota</h1>
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

// Get anggota data from URL parameter or session
$anggota = $_GET['data'] ?? null;
if ($anggota && is_string($anggota)) {
    $anggota = json_decode(urldecode($anggota), true);
}
?>


</div>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="anggota-edit">Edit Anggota</h1>
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

<!-- Anggota Info Card -->
<?php if ($anggota): ?>
<div class="card mb-4" id="info-card">
    <div class="card-header bg-info text-white" id="info-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-person-badge me-2"></i>
            Informasi Anggota: <?= htmlspecialchars($anggota['nama_lengkap'] ?? '') ?>
        </h5>
    </div>
    <div class="card-body" id="info-content">
        <div class="row">
            <div class="col-md-3">
                <strong>No. Anggota:</strong><br>
                <span class="text-primary"><?= htmlspecialchars($anggota['no_anggota'] ?? '') ?></span>
            </div>
            <div class="col-md-3">
                <strong>Status:</strong><br>
                <span class="badge bg-<?= $anggota['status_anggota'] === 'Aktif' ? 'success' : 'warning' ?>">
                    <?= htmlspecialchars($anggota['status_anggota'] ?? '') ?>
                </span>
            </div>
            <div class="col-md-3">
                <strong>Tanggal Bergabung:</strong><br>
                <?= formatTanggal($anggota['tanggal_bergabung'] ?? '', 'd M Y') ?>
            </div>
            <div class="col-md-3">
                <strong>Terakhir Update:</strong><br>
                <?= formatTanggal($anggota['updated_at'] ?? '', 'd M Y H:i') ?>
            </div>
        </div>
    </div>
</div>

<!-- Form Container -->
<div class="card" id="form-container">
    <div class="card-header" id="form-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-pencil-square me-2"></i>
            Form Edit Data Anggota
        </h5>
    </div>
    <div class="card-body" id="form-content">
        <form method="post" action="<?= base_url('anggota/update') ?>" id="anggota-form">
            <input type="hidden" name="id" value="<?= $anggota['id'] ?? '' ?>" id="input-id">
            
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
                           value="<?= htmlspecialchars($anggota['no_anggota'] ?? '') ?>" readonly>
                    <small class="form-text text-muted">Nomor unik anggota (tidak dapat diubah)</small>
                </div>
                
                <div class="col-md-8" id="field-nama-lengkap">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" class="form-control" required id="input-nama-lengkap"
                           value="<?= htmlspecialchars($anggota['nama_lengkap'] ?? '') ?>">
                    <small class="form-text text-muted">Nama sesuai KTP</small>
                </div>
                
                <div class="col-md-6" id="field-tempat-lahir">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control" id="input-tempat-lahir"
                           value="<?= htmlspecialchars($anggota['tempat_lahir'] ?? '') ?>">
                </div>
                
                <div class="col-md-6" id="field-tanggal-lahir">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" id="input-tanggal-lahir"
                           value="<?= $anggota['tanggal_lahir'] ?? '' ?>">
                </div>
                
                <div class="col-md-4" id="field-jenis-kelamin">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control" id="input-jenis-kelamin">
                        <option value="">Pilih</option>
                        <option value="L" <?= ($anggota['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= ($anggota['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                
                <div class="col-md-4" id="field-status-perkawinan">
                    <label class="form-label">Status Perkawinan</label>
                    <select name="status_perkawinan" class="form-control" id="input-status-perkawinan">
                        <option value="">Pilih</option>
                        <option value="Belum Kawin" <?= ($anggota['status_perkawinan'] ?? '') === 'Belum Kawin' ? 'selected' : '' ?>>Belum Kawin</option>
                        <option value="Kawin" <?= ($anggota['status_perkawinan'] ?? '') === 'Kawin' ? 'selected' : '' ?>>Kawin</option>
                        <option value="Cerai Hidup" <?= ($anggota['status_perkawinan'] ?? '') === 'Cerai Hidup' ? 'selected' : '' ?>>Cerai Hidup</option>
                        <option value="Cerai Mati" <?= ($anggota['status_perkawinan'] ?? '') === 'Cerai Mati' ? 'selected' : '' ?>>Cerai Mati</option>
                    </select>
                </div>
                
                <div class="col-md-4" id="field-agama">
                    <label class="form-label">Agama</label>
                    <select name="agama" class="form-control" id="input-agama">
                        <option value="">Pilih</option>
                        <option value="Islam" <?= ($anggota['agama'] ?? '') === 'Islam' ? 'selected' : '' ?>>Islam</option>
                        <option value="Kristen" <?= ($anggota['agama'] ?? '') === 'Kristen' ? 'selected' : '' ?>>Kristen</option>
                        <option value="Katolik" <?= ($anggota['agama'] ?? '') === 'Katolik' ? 'selected' : '' ?>>Katolik</option>
                        <option value="Hindu" <?= ($anggota['agama'] ?? '') === 'Hindu' ? 'selected' : '' ?>>Hindu</option>
                        <option value="Buddha" <?= ($anggota['agama'] ?? '') === 'Buddha' ? 'selected' : '' ?>>Buddha</option>
                        <option value="Konghucu" <?= ($anggota['agama'] ?? '') === 'Konghucu' ? 'selected' : '' ?>>Konghucu</option>
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
                           value="<?= htmlspecialchars($anggota['no_telepon'] ?? '') ?>">
                </div>
                
                <div class="col-md-6" id="field-email">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" id="input-email"
                           value="<?= htmlspecialchars($anggota['email'] ?? '') ?>">
                </div>
                
                <div class="col-12" id="field-alamat">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="3" id="input-alamat"><?= htmlspecialchars($anggota['alamat'] ?? '') ?></textarea>
                </div>
                
                <!-- Informasi Pekerjaan -->
                <div class="col-12 mt-4">
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-briefcase me-2"></i>Informasi Pekerjaan
                    </h6>
                </div>
                
                <div class="col-md-6" id="field-pekerjaan">
                    <label class="form-label">Pekerjaan</label>
                    <input type="text" name="pekerjaan" class="form-control" id="input-pekerjaan"
                           value="<?= htmlspecialchars($anggota['pekerjaan'] ?? '') ?>">
                </div>
                
                <div class="col-md-6" id="field-penghasilan">
                    <label class="form-label">Penghasilan per Bulan</label>
                    <input type="number" name="penghasilan" class="form-control" id="input-penghasilan"
                           value="<?= htmlspecialchars($anggota['penghasilan'] ?? '') ?>">
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
                        <option value="Aktif" <?= ($anggota['status_anggota'] ?? '') === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="Tidak Aktif" <?= ($anggota['status_anggota'] ?? '') === 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                        <option value="Calon" <?= ($anggota['status_anggota'] ?? '') === 'Calon' ? 'selected' : '' ?>>Calon</option>
                    </select>
                </div>
                
                <div class="col-md-6" id="field-tanggal-bergabung">
                    <label class="form-label">Tanggal Bergabung</label>
                    <input type="date" name="tanggal_bergabung" class="form-control" id="input-tanggal-bergabung"
                           value="<?= $anggota['tanggal_bergabung'] ?? '' ?>">
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between" id="form-actions">
                        <div class="btn-group">
                            <a href="<?= base_url('anggota') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                            <button type="button" class="btn btn-outline-danger" onclick="deleteAnggota()">
                                <i class="bi bi-trash me-2"></i>Hapus
                            </button>
                        </div>
                        <div class="btn-group">
                            <button type="reset" class="btn btn-outline-warning" id="btn-reset">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary" id="btn-save">
                                <i class="bi bi-check-circle me-2"></i>Update Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Data anggota tidak ditemukan. Silakan kembali ke halaman daftar anggota.
    <div class="mt-3">
        <a href="<?= base_url('anggota') ?>" class="btn btn-primary">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Anggota
        </a>
    </div>
</div>
<?php endif; ?>

</div>

<!-- JavaScript for Form Handling -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('anggota-form');
    const noAnggotaInput = document.getElementById('input-no-anggota');
    const namaLengkapInput = document.getElementById('input-nama-lengkap');
    
    // Store original values for reset
    const originalValues = {};
    if (form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            originalValues[input.name] = input.value;
        });
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
            saveBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Mengupdate...';
            saveBtn.disabled = true;
            
            // Submit form
            this.submit();
        });
    }
    
    // Reset form handler
    const resetBtn = document.getElementById('btn-reset');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (confirm('Apakah Anda yakin ingin mereset perubahan?')) {
                // Restore original values
                Object.keys(originalValues).forEach(name => {
                    const input = form.querySelector(`[name="${name}"]`);
                    if (input) {
                        input.value = originalValues[name];
                    }
                });
            }
        });
    }
    
    console.log('Anggota edit form initialized');
});

// Global function for save button
function saveAnggota() {
    document.getElementById('anggota-form').dispatchEvent(new Event('submit'));
}

// Global function for delete button
function deleteAnggota() {
    if (confirm('Apakah Anda yakin ingin menghapus data anggota ini? Tindakan ini tidak dapat dibatalkan!')) {
        const form = document.getElementById('anggota-form');
        const id = document.getElementById('input-id').value;
        
        if (id) {
            // Create delete form
            const deleteForm = document.createElement('form');
            deleteForm.method = 'POST';
            deleteForm.action = '<?= base_url('anggota/delete') ?>';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            
            deleteForm.appendChild(idInput);
            document.body.appendChild(deleteForm);
            deleteForm.submit();
        }
    }
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
    console.log('Page anggota - edit initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Edit Anggota', 'anggota-edit');
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