<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Penjualan Agen</h2>
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
                <h6>Total Penjualan Agen</h6>
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
                <h6>Total Nilai Penjualan</h6>
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
                <h6>Total Komisi</h6>
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
                <h5 class="modal-title">Detail Penjualan Agen</h5>
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
