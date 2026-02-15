<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
$anggota = $anggota ?? [];
$jenis = $jenis ?? [];
?>


</div>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="pinjaman-create">Ajukan Pinjaman</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="pinjaman" class="btn btn-sm btn-outline-secondary">
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

</div>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="pinjaman-create">Ajukan Pinjaman</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="pinjaman" class="btn btn-sm btn-outline-secondary">
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
<?php if ($error = getFlashMessage("error")): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success = getFlashMessage("success")): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>


<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="post" action="<?= base_url('pinjaman/store') ?>">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Anggota *</label>
            <select name="anggota_id" class="form-select" required>
                <option value="">- Pilih Anggota -</option>
                <?php foreach ($anggota as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama_lengkap']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Jenis Pinjaman *</label>
            <select name="jenis_pinjaman_id" class="form-select" required>
                <option value="">- Pilih Jenis -</option>
                <?php foreach ($jenis as $j): ?>
                    <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nama_pinjaman']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">No Pinjaman *</label>
            <input type="text" name="no_pinjaman" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Jumlah Pinjaman *</label>
            <input type="number" step="0.01" name="jumlah_pinjaman" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Bunga (% per tahun)</label>
            <input type="number" step="0.01" name="bunga_persen" class="form-control" value="0">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tenor (bulan)</label>
            <input type="number" name="tenor_bulan" class="form-control" value="12">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Pengajuan</label>
            <input type="date" name="tanggal_pengajuan" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-8">
            <label class="form-label">Tujuan Pinjaman</label>
            <textarea name="tujuan_pinjaman" class="form-control" rows="2"></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="pengajuan">Pengajuan</option>
                <option value="ditolak">Ditolak</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?= base_url('pinjaman') ?>" class="btn btn-secondary">Batal</a>
    </div>
</form>


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page pinjaman - create initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Tambah Pinjaman', 'pinjaman-create');
    }
});

// Global functions
function savePinjaman() {
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


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page pinjaman - create initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Tambah Pinjaman', 'pinjaman-create');
    }
});

// Global functions
function savePinjaman() {
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