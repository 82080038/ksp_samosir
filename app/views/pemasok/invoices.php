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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="pemasok-invoices">Invoice Pemasok</h1>
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
    
    <div class="btn-group">
        <a href="<?= base_url('pemasok') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Dashboard</a>
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
                        <th>No Invoice</th>
                        <th>Supplier</th>
                        <th>PO</th>
                        <th>Tanggal Invoice</th>
                        <th>Total Nilai</th>
                        <th>Jatuh Tempo</th>
                        <th>Status Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($invoice['nomor_invoice']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($invoice['supplier_name']) ?></strong>
                            </td>
                            <td>
                                PO-<?= htmlspecialchars($invoice['po_id']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($invoice['nomor_po']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($invoice['tanggal_invoice'], 'd M Y')) ?>
                            </td>
                            <td>
                                <strong class="text-danger">Rp <?= formatCurrency($invoice['total_nilai']) ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($invoice['tanggal_jatuh_tempo'], 'd M Y')) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $invoice['status_pembayaran'] === 'lunas' ? 'success' : 'warning' ?>">
                                    <?= htmlspecialchars($invoice['status_pembayaran']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewInvoice(<?= $invoice['id'] ?>)">
                                        Detail
                                    </button>
                                    <?php if ($invoice['status_pembayaran'] === 'belum_lunas'): ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="payInvoice(<?= $invoice['id'] ?>)">
                                            Bayar
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary" onclick="printInvoice(<?= $invoice['id'] ?>)">
                                        Print
                                    </button>
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
                    $total_invoices = count($invoices);
                    echo $total_invoices;
                    ?>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                
                <h4 class="text-danger">
                    Rp <?php
                    $total_value = array_sum(array_column($invoices, 'total_nilai'));
                    echo formatCurrency($total_value);
                    ?>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                
                <h4 class="text-warning">
                    <?php
                    $unpaid = count(array_filter($invoices, function($inv) { return $inv['status_pembayaran'] === 'belum_lunas'; }));
                    echo $unpaid;
                    ?>
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Modal for invoice details -->
<div class="modal fade" id="invoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="invoiceContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewInvoice(id) {
    // Placeholder for AJAX detail loading
    alert('Detail invoice akan dimuat dengan AJAX');
}

function payInvoice(id) {
    if (confirm('Tandai invoice ini sebagai lunas?')) {
        // Placeholder for payment processing
        alert('Invoice ditandai sebagai lunas');
    }
}

function printInvoice(id) {
    // Placeholder for print functionality
    alert('Fitur print invoice akan diimplementasikan');
}
</script>


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page pemasok - invoices initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Invoices', 'pemasok-invoices');
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