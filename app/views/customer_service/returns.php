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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="cs-returns">Kelola Return</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('customer_service/createReturn') ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Buat Return Baru
            </a>
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
                        <th>ID Return</th>
                        <th>Order</th>
                        <th>Pelanggan</th>
                        <th>Alasan Return</th>
                        <th>Status</th>
                        <th>Jumlah Refund</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($returns as $return): ?>
                        <tr>
                            <td>
                                <strong>#<?= htmlspecialchars($return['id']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($return['no_faktur']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($return['customer_name']) ?></strong>
                            </td>
                            <td>
                                <div style="max-width: 200px;" title="<?= htmlspecialchars($return['alasan_return']) ?>">
                                    <?= htmlspecialchars(substr($return['alasan_return'], 0, 30)) ?>...
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $return['status'] === 'processed' ? 'success' : ($return['status'] === 'pending' ? 'warning' : 'info') ?>">
                                    <?= htmlspecialchars($return['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($return['jumlah_refund'] > 0): ?>
                                    <strong class="text-success">Rp <?= formatCurrency($return['jumlah_refund']) ?></strong>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($return['created_at'], 'd M Y')) ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewReturn(<?= $return['id'] ?>)">
                                        Detail
                                    </button>
                                    <?php if ($return['status'] === 'pending'): ?>
                                        <a href="<?= base_url('customer_service/processReturn/' . $return['id']) ?>" class="btn btn-sm btn-outline-warning">
                                            Proses
                                        </a>
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
                <h6>Total Return</h6>
                <h4 class="text-primary">
                    <?php
                    $total_returns = count($returns);
                    echo $total_returns;
                    ?>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6>Return Pending</h6>
                <h4 class="text-warning">
                    <?php
                    $pending = count(array_filter($returns, function($r) { return $r['status'] === 'pending'; }));
                    echo $pending;
                    ?>
                </h4>
                <small>Perlu diproses</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6>Total Refund</h6>
                <h4 class="text-success">
                    Rp <?php
                    $total_refund = array_sum(array_column($returns, 'jumlah_refund'));
                    echo formatCurrency($total_refund);
                    ?>
                </h4>
                <small>Sudah diproses</small>
            </div>
        </div>
    </div>
</div>

<!-- Modal for return details -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="returnContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewReturn(id) {
    // Placeholder for AJAX detail loading
    alert('Detail return akan dimuat dengan AJAX');
}
</script>
