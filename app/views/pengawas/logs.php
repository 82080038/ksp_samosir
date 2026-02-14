<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Log Aktivitas</h2>
    <div class="btn-group">
        <a href="<?= base_url('pengawas') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Dashboard</a>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5>Riwayat Aktivitas Sistem</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Tabel</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars(formatDate($log['created_at'], 'd M Y')) ?><br>
                                    <?= htmlspecialchars(formatDate($log['created_at'], 'H:i:s')) ?>
                                </small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($log['user_name'] ?? 'System') ?></strong><br>
                                <small class="text-muted">IP: <?= htmlspecialchars($log['ip_address']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $this->getActionBadgeColor($log['action']) ?>">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <code><?= htmlspecialchars($log['table_name']) ?></code>
                                <?php if ($log['record_id']): ?>
                                    <br><small>ID: <?= htmlspecialchars($log['record_id']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($log['old_values'] || $log['new_values']): ?>
                                    <button class="btn btn-sm btn-outline-info" onclick="showLogDetail(<?= $log['id'] ?>)">
                                        Lihat Detail
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
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

<!-- Modal for log details -->
<div class="modal fade" id="logDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Log Aktivitas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logDetailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function showLogDetail(logId) {
    // Placeholder for AJAX detail loading
    alert('Detail log akan dimuat dengan AJAX');
}

function getActionBadgeColor(action) {
    const colors = {
        'INSERT': 'success',
        'UPDATE': 'primary',
        'DELETE': 'danger',
        'LOGIN': 'info',
        'LOGOUT': 'secondary'
    };
    return colors[action] || 'secondary';
}
</script>
