<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Purchase Orders</h2>
    <div class="btn-group">
        <a href="<?= base_url('pemasok/createPO') ?>" class="btn btn-primary btn-sm">Buat PO Baru</a>
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
                        <th>No PO</th>
                        <th>Supplier</th>
                        <th>Tanggal PO</th>
                        <th>Total Nilai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pos as $po): ?>
                        <tr>
                            <td>
                                <strong>PO-<?= htmlspecialchars($po['id']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($po['supplier_name']) ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($po['tanggal_po'], 'd M Y')) ?><br>
                                <small class="text-muted">Pengiriman: <?= htmlspecialchars(formatDate($po['tanggal_pengiriman'], 'd M Y')) ?></small>
                            </td>
                            <td>
                                <strong class="text-success">Rp <?= formatCurrency($po['total_nilai']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-<?= $po['status'] === 'completed' ? 'success' : ($po['status'] === 'pending' ? 'warning' : 'info') ?>">
                                    <?= htmlspecialchars($po['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewPO(<?= $po['id'] ?>)">
                                        Detail
                                    </button>
                                    <?php if ($po['status'] === 'draft'): ?>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editPO(<?= $po['id'] ?>)">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="approvePO(<?= $po['id'] ?>)">
                                            Approve
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="printPO(<?= $po['id'] ?>)">
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

<!-- Modal for PO details -->
<div class="modal fade" id="poModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="poContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewPO(id) {
    // Placeholder for AJAX detail loading
    alert('Detail PO akan dimuat dengan AJAX');
}

function editPO(id) {
    // Placeholder for edit form
    alert('Form edit PO akan dibuka');
}

function approvePO(id) {
    if (confirm('Approve PO ini?')) {
        // Placeholder for approval
        alert('PO diapprove');
    }
}

function printPO(id) {
    // Placeholder for print functionality
    alert('Fitur print PO akan diimplementasikan');
}
</script>
