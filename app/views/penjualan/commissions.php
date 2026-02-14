<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Komisi Agen</h2>
    <div class="btn-group">
        <a href="<?= base_url('penjualan/agentSales') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Penjualan Agen</a>
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
                        <th>Agen</th>
                        <th>Periode</th>
                        <th>Total Penjualan</th>
                        <th>Total Komisi</th>
                        <th>Status Pembayaran</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commissions as $commission): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($commission['agent_name']) ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars($commission['periode']) ?>
                            </td>
                            <td>
                                <strong class="text-success">Rp <?= formatCurrency($commission['total_penjualan']) ?></strong>
                            </td>
                            <td>
                                <strong class="text-info">Rp <?= formatCurrency($commission['total_komisi']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-<?= $commission['status_pembayaran'] === 'lunas' ? 'success' : 'warning' ?>">
                                    <?= htmlspecialchars($commission['status_pembayaran']) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($commission['created_at'], 'd M Y')) ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewCommission(<?= $commission['id'] ?>)">
                                        Detail
                                    </button>
                                    <?php if ($commission['status_pembayaran'] === 'belum_bayar'): ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="payCommission(<?= $commission['id'] ?>)">
                                            Bayar
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
                <h6>Total Komisi</h6>
                <h4 class="text-primary">
                    <?php
                    $total_commissions = count($commissions);
                    echo $total_commissions;
                    ?>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6>Total Nilai Komisi</h6>
                <h4 class="text-info">
                    Rp <?php
                    $total_commission_value = array_sum(array_column($commissions, 'total_komisi'));
                    echo formatCurrency($total_commission_value);
                    ?>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6>Komisi Belum Dibayar</h6>
                <h4 class="text-warning">
                    <?php
                    $unpaid = count(array_filter($commissions, function($comm) { return $comm['status_pembayaran'] === 'belum_bayar'; }));
                    echo $unpaid;
                    ?>
                </h4>
                <small>Rp <?php
                    $unpaid_value = array_sum(array_map(function($comm) {
                        return $comm['status_pembayaran'] === 'belum_bayar' ? $comm['total_komisi'] : 0;
                    }, $commissions));
                    echo formatCurrency($unpaid_value);
                ?></small>
            </div>
        </div>
    </div>
</div>

<!-- Modal for commission details -->
<div class="modal fade" id="commissionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Komisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="commissionContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewCommission(id) {
    // Placeholder for AJAX detail loading
    alert('Detail komisi akan dimuat dengan AJAX');
}

function payCommission(id) {
    if (confirm('Tandai komisi ini sebagai lunas?')) {
        // Placeholder for payment processing
        alert('Komisi ditandai sebagai lunas');
    }
}
</script>
