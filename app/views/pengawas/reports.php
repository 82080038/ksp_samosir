<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="pengawas-reports">Laporan Pengawas</h1>
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
                        <th>Judul Laporan</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($report['judul']) ?></strong>
                                <?php if (!empty($report['periode_mulai']) && !empty($report['periode_akhir'])): ?>
                                    <br><small class="text-muted">
                                        <?= htmlspecialchars(formatDate($report['periode_mulai'], 'd M Y')) ?> - <?= htmlspecialchars(formatDate($report['periode_akhir'], 'd M Y')) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($report['periode_mulai']) && !empty($report['periode_akhir'])): ?>
                                    <?= htmlspecialchars(formatDate($report['periode_mulai'], 'M Y')) ?> - <?= htmlspecialchars(formatDate($report['periode_akhir'], 'M Y')) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $this->getReportStatusBadgeColor($report['status']) ?>">
                                    <?= htmlspecialchars($report['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars($report['created_by_name']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($report['created_at'], 'd M Y')) ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewReport(<?= $report['id'] ?>)">
                                        Lihat
                                    </button>
                                    <?php if ($report['status'] === 'draft'): ?>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editReport(<?= $report['id'] ?>)">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="submitReport(<?= $report['id'] ?>)">
                                            Ajukan
                                        </button>
                                    <?php elseif ($report['status'] === 'final'): ?>
                                        <button class="btn btn-sm btn-outline-warning" onclick="approveReport(<?= $report['id'] ?>)">
                                            Setujui
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

<!-- Modal for report details -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Laporan Pengawas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reportContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function createReport() {
    alert('Fitur buat laporan baru akan diimplementasikan dengan form modal');
}

function viewReport(id) {
    // Placeholder for AJAX detail loading
    alert('Detail laporan akan dimuat dengan AJAX');
}

function editReport(id) {
    // Placeholder for edit form
    alert('Form edit laporan akan dibuka');
}

function submitReport(id) {
    if (confirm('Ajukan laporan ini untuk approval?')) {
        // Placeholder for submission
        alert('Laporan diajukan untuk approval');
    }
}

function approveReport(id) {
    if (confirm('Setujui laporan ini?')) {
        // Placeholder for approval
        alert('Laporan disetujui');
    }
}

function getReportStatusBadgeColor(status) {
    const colors = {
        'draft': 'secondary',
        'final': 'warning',
        'disetujui': 'success'
    };
    return colors[status] || 'secondary';
}
</script>
