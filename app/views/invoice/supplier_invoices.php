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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="invoice-supplier">Invoice Supplier</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('invoice/recordSupplierInvoice') ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Catat Invoice Baru
            </a>
            <a href="<?= base_url('invoice') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
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
                        <th>PO</th>
                        <th>Supplier</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Jatuh Tempo</th>
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
                                <strong><?= htmlspecialchars($invoice['nomor_po']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($invoice['supplier_name']) ?></strong>
                            </td>
                            <td>
                                <strong class="text-danger">Rp <?= formatCurrency($invoice['total_nilai']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-<?= $invoice['status_pembayaran'] === 'lunas' ? 'success' : ($invoice['status_pembayaran'] === 'overdue' ? 'danger' : 'warning') ?>">
                                    <?= htmlspecialchars($invoice['status_pembayaran']) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($invoice['tanggal_jatuh_tempo'], 'd M Y')) ?>
                                <?php if ($invoice['tanggal_jatuh_tempo'] < date('Y-m-d') && $invoice['status_pembayaran'] !== 'lunas'): ?>
                                    <br><small class="text-danger">Overdue</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewInvoice(<?= $invoice['id'] ?>, 'supplier')">
                                        Detail
                                    </button>
                                    <a href="<?= base_url('invoice/downloadInvoice/' . $invoice['id'] . '/supplier') ?>" class="btn btn-sm btn-outline-primary">
                                        Download
                                    </a>
                                    <?php if ($invoice['status_pembayaran'] === 'belum_lunas'): ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="markAsPaid(<?= $invoice['id'] ?>, 'supplier')">
                                            Tandai Lunas
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
                <h6>Total Invoice Supplier</h6>
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
                <h6>Total Nilai Invoice</h6>
                <h4 class="text-danger">
                    Rp <?php
                    $total_value = array_sum(array_column($invoices, 'total_nilai'));
                    echo formatCurrency($total_value);
                    ?>
                </h4>
                <small>Belum termasuk yang lunas</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6>Invoice Overdue</h6>
                <h4 class="text-warning">
                    <?php
                    $overdue = count(array_filter($invoices, function($inv) {
                        return $inv['tanggal_jatuh_tempo'] < date('Y-m-d') && $inv['status_pembayaran'] !== 'lunas';
                    }));
                    echo $overdue;
                    ?>
                </h4>
                <small>Perlu segera dibayar</small>
            </div>
        </div>
    </div>
</div>

<!-- Modal for invoice details -->
<div class="modal fade" id="invoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Invoice Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="invoiceContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewInvoice(id, type) {
    // Placeholder for AJAX detail loading
    alert('Detail invoice supplier akan dimuat dengan AJAX');
}

function markAsPaid(id, type) {
    if (confirm('Tandai invoice supplier ini sebagai lunas?')) {
        window.location.href = '<?= base_url('invoice/markAsPaid/') ?>' + id + '/' + type;
    }
}
</script>
