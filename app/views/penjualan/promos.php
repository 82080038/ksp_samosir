<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Kelola Promo</h2>
    <div class="btn-group">
        <a href="<?= base_url('penjualan/createPromo') ?>" class="btn btn-primary btn-sm">Buat Promo Baru</a>
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
                        <th>Kode Promo</th>
                        <th>Jenis Diskon</th>
                        <th>Nilai Diskon</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Digunakan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promos as $promo): ?>
                        <tr>
                            <td>
                                <strong class="text-primary"><?= htmlspecialchars($promo['kode_promo']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($promo['jenis_diskon'] === 'persen' ? 'Persen' : 'Nominal') ?>
                                </span>
                            </td>
                            <td>
                                <strong>
                                    <?php if ($promo['jenis_diskon'] === 'persen'): ?>
                                        <?= htmlspecialchars($promo['nilai_diskon']) ?>%
                                    <?php else: ?>
                                        Rp <?= formatCurrency($promo['nilai_diskon']) ?>
                                    <?php endif; ?>
                                </strong>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($promo['tanggal_mulai'], 'd M')) ?> -
                                <?= htmlspecialchars(formatDate($promo['tanggal_akhir'], 'd M Y')) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $promo['status'] === 'aktif' ? 'success' : ($promo['status'] === 'kadaluarsa' ? 'warning' : 'secondary') ?>">
                                    <?= htmlspecialchars($promo['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                // Count usage - this would need actual usage tracking
                                $usage_count = 0; // Placeholder
                                echo $usage_count;
                                ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewPromo(<?= $promo['id'] ?>)">
                                        Detail
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editPromo(<?= $promo['id'] ?>)">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deletePromo(<?= $promo['id'] ?>)">
                                        Hapus
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

<!-- Modal for promo details -->
<div class="modal fade" id="promoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Promo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="promoContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewPromo(id) {
    // Placeholder for AJAX detail loading
    alert('Detail promo akan dimuat dengan AJAX');
}

function editPromo(id) {
    // Placeholder for edit form
    alert('Form edit promo akan dibuka');
}

function deletePromo(id) {
    if (confirm('Hapus promo ini?')) {
        // Placeholder for delete action
        alert('Promo akan dihapus');
    }
}
</script>
