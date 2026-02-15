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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="invoice-customer">Invoice Pelanggan</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
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
                        <th>Order</th>
                        <th>Customer</th>
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
                                <strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($invoice['no_faktur']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($invoice['customer_name']) ?></strong>
                            </td>
                            <td>
                                <strong class="text-success">Rp <?= formatCurrency($invoice['total_amount']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-<?= $invoice['status'] === 'paid' ? 'success' : ($invoice['status'] === 'overdue' ? 'danger' : 'warning') ?>">
                                    <?= htmlspecialchars($invoice['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($invoice['due_date'], 'd M Y')) ?>
                                <?php if ($invoice['due_date'] < date('Y-m-d') && $invoice['status'] !== 'paid'): ?>
                                    <br><small class="text-danger">Overdue</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewInvoice(<?= $invoice['id'] ?>, 'customer')">
                                        Detail
                                    </button>
                                    <a href="<?= base_url('invoice/downloadInvoice/' . $invoice['id'] . '/customer') ?>" class="btn btn-sm btn-outline-primary">
                                        Download
                                    </a>
                                    <?php if ($invoice['status'] === 'unpaid'): ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="markAsPaid(<?= $invoice['id'] ?>, 'customer')">
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

<!-- Modal for invoice details -->
<div class="modal fade" id="invoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Invoice</h5>
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
    alert('Detail invoice akan dimuat dengan AJAX');
}

function markAsPaid(id, type) {
    if (confirm('Tandai invoice ini sebagai lunas?')) {
        window.location.href = '<?= base_url('invoice/markAsPaid/') ?>' + id + '/' + type;
    }
}
</script>
