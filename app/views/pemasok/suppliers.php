<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Kelola Pemasok</h2>
    <div class="btn-group">
        <a href="<?= base_url('pemasok/createSupplier') ?>" class="btn btn-primary btn-sm">Tambah Pemasok</a>
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
                        <th>Nama Perusahaan</th>
                        <th>Kategori</th>
                        <th>Kontak</th>
                        <th>Status</th>
                        <th>Rating</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($supplier['nama_perusahaan']) ?></strong>
                                <?php if (!empty($supplier['npwp'])): ?>
                                    <br><small class="text-muted">NPWP: <?= htmlspecialchars($supplier['npwp']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($supplier['kategori']) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($supplier['telepon']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($supplier['email']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $supplier['status'] === 'aktif' ? 'success' : 'secondary' ?>">
                                    <?= htmlspecialchars($supplier['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= ($supplier['rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>"></i>
                                <?php endfor; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($supplier['created_at'], 'd M Y')) ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewSupplier(<?= $supplier['id'] ?>)">
                                        Lihat
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editSupplier(<?= $supplier['id'] ?>)">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteSupplier(<?= $supplier['id'] ?>)">
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

<!-- Modal for supplier details -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pemasok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="supplierContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewSupplier(id) {
    // Placeholder for AJAX detail loading
    alert('Detail pemasok akan dimuat dengan AJAX');
}

function editSupplier(id) {
    // Placeholder for edit form
    alert('Form edit pemasok akan dibuka');
}

function deleteSupplier(id) {
    if (confirm('Hapus pemasok ini?')) {
        // Placeholder for delete action
        alert('Pemasok akan dihapus');
    }
}
</script>
