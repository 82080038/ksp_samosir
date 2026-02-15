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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="cs-refunds">Kelola Refund</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('customer_service') ?>" class="btn btn-sm btn-outline-secondary">
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
                        <th>ID Refund</th>
                        <th>Return</th>
                        <th>Customer</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Diproses Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($refunds as $refund): ?>
                        <tr>
                            <td>
                                <strong>#<?= htmlspecialchars($refund['id']) ?></strong>
                            </td>
                            <td>
                                <strong>Return #<?= htmlspecialchars($refund['return_id']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars(substr($refund['alasan_return'], 0, 30)) ?>...</small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($refund['customer_name']) ?></strong>
                            </td>
                            <td>
                                <strong class="text-success">Rp <?= formatCurrency($refund['amount']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= htmlspecialchars($refund['method']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $refund['status'] === 'completed' ? 'success' : ($refund['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                    <?= htmlspecialchars($refund['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= $refund['processed_by_name'] ?: '-' ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewRefund(<?= $refund['id'] ?>)">
                                        Detail
                                    </button>
                                    <?php if ($refund['status'] === 'pending'): ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="processRefund(<?= $refund['id'] ?>)">
                                            Proses
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
                <h6>Total Refund</h6>
                <h4 class="text-primary">
                    <?php
                    $total_refunds = count($refunds);
                    echo $total_refunds;
                    ?>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6>Total Nilai Refund</h6>
                <h4 class="text-success">
                    Rp <?php
                    $total_amount = array_sum(array_column($refunds, 'amount'));
                    echo formatCurrency($total_amount);
                    ?>
                </h4>
                <small>Semua status</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6>Refund Pending</h6>
                <h4 class="text-warning">
                    <?php
                    $pending = count(array_filter($refunds, function($r) { return $r['status'] === 'pending'; }));
                    echo $pending;
                    ?>
                </h4>
                <small>Menunggu proses</small>
            </div>
        </div>
    </div>
</div>

<!-- Modal for refund details -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Refund</h5>
                <button type="button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="refundContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewRefund(id) {
    // Placeholder for AJAX detail loading
    alert('Detail refund akan dimuat dengan AJAX');
}

function processRefund(id) {
    if (confirm('Proses refund ini?')) {
        window.location.href = '<?= base_url('customer_service/processRefund/') ?>' + id;
    }
}
</script>
