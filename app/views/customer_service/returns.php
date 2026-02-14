<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Kelola Return & Refund</h2>
    <div class="btn-group">
        <a href="<?= base_url('customer_service/createReturn') ?>" class="btn btn-primary btn-sm">Buat Return Baru</a>
        <a href="<?= base_url('customer_service') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Dashboard</a>
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
