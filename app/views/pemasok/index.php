<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>


        <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-pemasok">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="pemasok">Manajemen Pemasok</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="pemasok" class="btn btn-sm btn-outline-secondary">
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

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        
        <p class="text-muted">Kelola pemasok, purchase order, dan faktur supplier</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        
                        <p class="card-text">Total Pemasok Aktif</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-truck fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        
                        <p class="card-text">PO Bulan Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        
                        <p class="card-text">Invoice Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        
                        <p class="card-text">PO Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="<?= base_url('pemasok/suppliers') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-users"></i> Kelola Pemasok
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('pemasok/createPO') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-plus"></i> Buat Purchase Order
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('pemasok/purchaseOrders') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-list"></i> Lihat PO
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('pemasok/invoices') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-file-invoice-dollar"></i> Kelola Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Purchase Orders -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                
            </div>
            <div class="card-body">
                <?php if (empty($recent_pos)): ?>
                    <p class="text-muted">Belum ada purchase order.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_pos as $po): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    
                                    <small class="text-muted">
                                        <span class="badge bg-<?= $po['status'] === 'completed' ? 'success' : ($po['status'] === 'pending' ? 'warning' : 'info') ?>">
                                            <?= htmlspecialchars($po['status']) ?>
                                        </span>
                                    </small>
                                </div>
                                <p class="mb-1">Supplier: <?= htmlspecialchars($po['supplier_name']) ?></p>
                                <small class="text-muted">
                                    Total: Rp <?= formatCurrency($po['total_nilai']) ?> |
                                    Dibuat: <?= htmlspecialchars(formatDate($po['created_at'], 'd M Y')) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                
            </div>
            <div class="card-body">
                <?php if (empty($pending_invoices)): ?>
                    <p class="text-muted">Tidak ada invoice pending.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pending_invoices as $invoice): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    
                                    <small class="text-muted">
                                        <span class="badge bg-warning">Belum Lunas</span>
                                    </small>
                                </div>
                                <p class="mb-1">Supplier: <?= htmlspecialchars($invoice['supplier_name']) ?></p>
                                <small class="text-muted">
                                    Nilai: Rp <?= formatCurrency($invoice['total_nilai']) ?> |
                                    Jatuh Tempo: <?= htmlspecialchars(formatDate($invoice['tanggal_jatuh_tempo'], 'd M Y')) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page pemasok - index initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Pemasok', 'pemasok-index');
    }
});

// Global functions
function savePemasok() {
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