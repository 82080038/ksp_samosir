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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="penjualan-agent">Penjualan Agen</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="penjualan" class="btn btn-sm btn-outline-secondary">
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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="penjualan-agent">Penjualan Agen</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="penjualan" class="btn btn-sm btn-outline-secondary">
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

<div class="d-flex justify-content-between align-items-center mb-3">
    
    <div class="btn-group">
        <a href="<?= base_url('penjualan/createAgentSale') ?>" class="btn btn-primary btn-sm">Tambah Penjualan Agen</a>
        <a href="<?= base_url('penjualan') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Penjualan</a>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No Transaksi</th>
                        <th>Agen</th>
                        <th>Pelanggan</th>
                        <th>Total Nilai</th>
                        <th>Komisi</th>
                        <th>Status Approval</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agent_sales as $sale): ?>
                        <tr>
                            <td>
                                <strong>AS-<?= htmlspecialchars($sale['id']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($sale['agent_name']) ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($sale['pelanggan_nama']) ?></strong><br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($sale['pelanggan_alamat']) ?><br>
                                        <?= htmlspecialchars($sale['pelanggan_telp']) ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <strong class="text-success">Rp <?= formatCurrency($sale['total_nilai']) ?></strong>
                            </td>
                            <td>
                                <strong class="text-info">Rp <?= formatCurrency($sale['komisi']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-<?= $sale['status_approval'] === 'approved' ? 'success' : ($sale['status_approval'] === 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= htmlspecialchars($sale['status_approval']) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($sale['created_at'], 'd M Y')) ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewAgentSale(<?= $sale['id'] ?>)">
                                        Detail
                                    </button>
                                    <?php if ($sale['status_approval'] === 'pending'): ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="approveAgentSale(<?= $sale['id'] ?>)">
                                            Approve
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="rejectAgentSale(<?= $sale['id'] ?>)">
                                            Reject
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Pagination" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Summary Card -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                
                <h4 class="text-primary">
                    <?php
                    $total_sales = count($agent_sales);
                    echo $total_sales;
                    ?>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                
                <h4 class="text-success">
                    Rp <?php
                    $total_value = array_sum(array_column($agent_sales, 'total_nilai'));
                    echo formatCurrency($total_value);
                    ?>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                
                <h4 class="text-info">
                    Rp <?php
                    $total_commission = array_sum(array_column($agent_sales, 'komisi'));
                    echo formatCurrency($total_commission);
                    ?>
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Modal for agent sale details -->
<div class="modal fade" id="agentSaleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="agentSaleContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewAgentSale(id) {
    // Placeholder for AJAX detail loading
    alert('Detail penjualan agen akan dimuat dengan AJAX');
}

function approveAgentSale(id) {
    if (confirm('Approve penjualan agen ini?')) {
        // Placeholder for approval
        alert('Penjualan agen diapprove');
    }
}

function rejectAgentSale(id) {
    if (confirm('Reject penjualan agen ini?')) {
        // Placeholder for rejection
        alert('Penjualan agen direject');
    }
}
</script>


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page penjualan - agent_sales initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Agent_sales', 'penjualan-agent_sales');
    }
});

// Global functions
function savePenjualan() {
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
    console.log('Page penjualan - agent_sales initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Penjualan Agen', 'penjualan-agent_sales');
    }
});

// Global functions
function savePenjualan() {
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