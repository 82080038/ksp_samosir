<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;

// Get logs data (safe - table/columns may vary)
try {
    $logs = fetchAll("SELECT * FROM logs ORDER BY created_at DESC LIMIT 100") ?? [];
} catch (Exception $e) {
    $logs = [];
}
?>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="logs">Audit Log</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-primary" onclick="refreshLogs()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportLogs()">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
    </div>
</div>

<!-- Description -->
<p class="text-muted mb-4">Monitor aktivitas sistem dan audit trail</p>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- Logs Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Module</th>
                        <th>Status</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada data logs</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= function_exists('formatDate') ? formatDate($log['created_at'] ?? '', 'd M Y H:i') : ($log['created_at'] ?? '-') ?></td>
                                <td><?= $log['user_name'] ?? $log['username'] ?? 'System' ?></td>
                                <td><?= $log['action'] ?? '-' ?></td>
                                <td><?= $log['module'] ?? $log['category'] ?? '-' ?></td>
                                <td>
                                    <?php $logStatus = $log['status'] ?? $log['level'] ?? 'info'; ?>
                                    <?php if ($logStatus === 'success'): ?>
                                        <span class="badge bg-success">Success</span>
                                    <?php elseif ($logStatus === 'error'): ?>
                                        <span class="badge bg-danger">Error</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= ucfirst($logStatus) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewLogDetail(<?= $log['id'] ?>)">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
