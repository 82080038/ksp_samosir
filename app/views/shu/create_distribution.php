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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="shu-create-distribution">Buat Distribusi SHU</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="shu" class="btn btn-sm btn-outline-secondary">
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

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('shu') ?>">SHU</a></li>
                <li class="breadcrumb-item active">Buat Distribusi</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('shu/createDistribution') ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="periode" class="form-label">Periode *</label>
                        <input type="text" class="form-control" id="periode" name="periode" placeholder="Contoh: 2024" required>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_distribusi" class="form-label">Tanggal Distribusi *</label>
                        <input type="date" class="form-control" id="tanggal_distribusi" name="tanggal_distribusi" required>
                    </div>

                    <div class="mb-3">
                        <label for="total_keuntungan" class="form-label">Total Keuntungan (Rp) *</label>
                        <input type="number" step="0.01" class="form-control" id="total_keuntungan" name="total_keuntungan" placeholder="0.00" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="shu_anggota" class="form-label">SHU untuk Anggota (Rp)</label>
                        <input type="number" step="0.01" class="form-control" id="shu_anggota" name="shu_anggota" placeholder="0.00">
                    </div>

                    <div class="mb-3">
                        <label for="dividen_investor" class="form-label">Dividen untuk Investor (Rp)</label>
                        <input type="number" step="0.01" class="form-control" id="dividen_investor" name="dividen_investor" placeholder="0.00">
                    </div>

                    <div class="mb-3">
                        <label for="cadangan_koperasi" class="form-label">Cadangan Koperasi (Rp)</label>
                        <input type="number" step="0.01" class="form-control" id="cadangan_koperasi" name="cadangan_koperasi" placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                
                <div id="distribution_summary">
                    <p>Total: Rp <span id="total_display">0.00</span></p>
                    <p>Selisih: Rp <span id="difference_display">0.00</span></p>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Buat Distribusi</button>
                <a href="<?= base_url('shu') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function updateSummary() {
    const total = parseFloat(document.getElementById('total_keuntungan').value) || 0;
    const shu = parseFloat(document.getElementById('shu_anggota').value) || 0;
    const dividen = parseFloat(document.getElementById('dividen_investor').value) || 0;
    const cadangan = parseFloat(document.getElementById('cadangan_koperasi').value) || 0;

    const allocated = shu + dividen + cadangan;
    const difference = total - allocated;

    document.getElementById('total_display').textContent = total.toLocaleString('id-ID');
    document.getElementById('difference_display').textContent = difference.toLocaleString('id-ID');

    // Change color based on difference
    const diffElement = document.getElementById('difference_display');
    if (difference === 0) {
        diffElement.style.color = 'green';
    } else if (difference > 0) {
        diffElement.style.color = 'orange';
    } else {
        diffElement.style.color = 'red';
    }
}

// Auto-calculate suggested distribution
document.getElementById('total_keuntungan').addEventListener('input', function() {
    const total = parseFloat(this.value) || 0;
    if (total > 0) {
        // Suggested distribution: 70% SHU, 10% Dividen, 20% Cadangan
        const shu = total * 0.7;
        const dividen = total * 0.1;
        const cadangan = total * 0.2;

        document.getElementById('shu_anggota').value = shu.toFixed(2);
        document.getElementById('dividen_investor').value = dividen.toFixed(2);
        document.getElementById('cadangan_koperasi').value = cadangan.toFixed(2);
    }
    updateSummary();
});

// Update summary on any input change
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', updateSummary);
});

// Initial update
updateSummary();
</script>


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Halaman shu - create_distribution diinisialisasi');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Buat Distribusi', 'shu-create_distribution');
    }
});

// Global functions
function saveShu() {
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