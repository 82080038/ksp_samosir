<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="pengawas-violations">Pelanggaran</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
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
                        <th>Pelanggar</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Sanksi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($violations as $violation): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($violation['user_name']) ?></strong>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($violation['jenis_pelanggaran']) ?>">
                                    <?= htmlspecialchars($violation['jenis_pelanggaran']) ?>
                                </div>
                                <?php if (!empty($violation['deskripsi'])): ?>
                                    <small class="text-muted d-block">
                                        <?= htmlspecialchars(substr($violation['deskripsi'], 0, 50)) ?>...
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($violation['tanggal_pelanggaran'], 'd M Y')) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $this->getStatusBadgeColor($violation['status']) ?>">
                                    <?= htmlspecialchars($violation['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars($violation['jenis_sanksi'] ?: '-') ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewViolation(<?= $violation['id'] ?>)">
                                        Lihat
                                    </button>
                                    <?php if ($violation['status'] === 'investigasi'): ?>
                                        <button class="btn btn-sm btn-outline-warning" onclick="decideViolation(<?= $violation['id'] ?>)">
                                            Putuskan
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

<!-- Modal for violation details -->
<div class="modal fade" id="violationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pelanggaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="violationContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function addViolation() {
    alert('Fitur tambah pelanggaran akan diimplementasikan dengan form modal');
}

function viewViolation(id) {
    // Placeholder for AJAX detail loading
    alert('Detail pelanggaran akan dimuat dengan AJAX');
}

function decideViolation(id) {
    // Placeholder for decision form
    alert('Form putusan akan dibuka');
}

function getStatusBadgeColor(status) {
    const colors = {
        'investigasi': 'warning',
        'diputuskan': 'info',
        'dieksekusi': 'success',
        'ditutup': 'secondary'
    };
    return colors[status] || 'secondary';
}
</script>
