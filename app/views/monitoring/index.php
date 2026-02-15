<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="monitoring">System Monitoring</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h2" style="color: black;"><?= $pageInfo['title'] ?></h1>
        <p class="text-muted">Monitoring performa, kesehatan sistem, dan alert real-time</p>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- System Status Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-<?= $system_status['database_status'] === 'healthy' ? 'success' : 'danger' ?>">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-database fa-3x text-<?= $system_status['database_status'] === 'healthy' ? 'success' : 'danger' ?>"></i>
                </div>
                <h5>Database</h5>
                <span class="badge bg-<?= $system_status['database_status'] === 'healthy' ? 'success' : 'danger' ?>">
                    <?= ucfirst($system_status['database_status']) ?>
                </span>
                <p class="small text-muted mt-2">Last check: <?= $system_status['last_check'] ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-<?= $system_status['application_status'] === 'healthy' ? 'success' : 'danger' ?>">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-server fa-3x text-<?= $system_status['application_status'] === 'healthy' ? 'success' : 'danger' ?>"></i>
                </div>
                <h5>Application</h5>
                <span class="badge bg-<?= $system_status['application_status'] === 'healthy' ? 'success' : 'danger' ?>">
                    <?= ucfirst($system_status['application_status']) ?>
                </span>
                <p class="small text-muted mt-2">Last check: <?= $system_status['last_check'] ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-clock fa-3x text-info"></i>
                </div>
                <h5>Uptime</h5>
                <span class="badge bg-info">Operational</span>
                <p class="small text-muted mt-2"><?= $system_status['uptime'] ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                </div>
                <h5>Active Alerts</h5>
                <span class="badge bg-warning"><?= count($recent_alerts) ?></span>
                <p class="small text-muted mt-2">Require attention</p>
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Performance Metrics (<?= $performance_metrics['period'] ?>)</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h4 class="text-primary"><?= $performance_metrics['avg_query_time'] ?>ms</h4>
                            <small>Average Query Time</small>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: <?= min($performance_metrics['avg_query_time'] / 100 * 100, 100) ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h4 class="text-success"><?= $performance_metrics['avg_page_load'] ?>s</h4>
                            <small>Average Page Load</small>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: <?= min($performance_metrics['avg_page_load'] / 3 * 100, 100) ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h4 class="text-info"><?= $performance_metrics['avg_memory_usage'] ?>MB</h4>
                            <small>Memory Usage</small>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: <?= min($performance_metrics['avg_memory_usage'] / 512 * 100, 100) ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <button class="btn btn-outline-primary btn-block" onclick="runHealthChecks()">
                            <i class="fas fa-stethoscope"></i> Run Health Checks
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('monitoring/performanceReport') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-chart-line"></i> Performance Report
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('monitoring/logs') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-list"></i> View Logs
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('monitoring/alerts') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-bell"></i> View Alerts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Alerts -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Recent Alerts</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_alerts)): ?>
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle"></i> No Active Alerts</h6>
                        <p>All systems are operating normally.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_alerts as $alert): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <span class="badge bg-<?= $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info') ?>">
                                            <?= htmlspecialchars($alert['severity']) ?>
                                        </span>
                                        <?= htmlspecialchars($alert['title']) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($alert['status']) ?></span>
                                    </small>
                                </div>
                                <p class="mb-1 small"><?= htmlspecialchars($alert['message']) ?></p>
                                <small class="text-muted">
                                    <?= htmlspecialchars(formatDate($alert['created_at'], 'd M Y H:i')) ?>
                                    <?php if ($alert['status'] === 'active'): ?>
                                        <a href="<?= base_url('monitoring/acknowledgeAlert/' . $alert['id']) ?>" class="btn btn-sm btn-outline-primary ms-2">Acknowledge</a>
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Health Check Status</h5>
            </div>
            <div class="card-body">
                <?php if (empty($health_checks)): ?>
                    <p class="text-muted">No health checks have been run yet.</p>
                    <p class="small text-muted">Click "Run Health Checks" to perform system diagnostics.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($health_checks as $check): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <span class="badge bg-<?= $check['status'] === 'passing' ? 'success' : ($check['status'] === 'warning' ? 'warning' : 'danger') ?>">
                                            <?= htmlspecialchars($check['status']) ?>
                                        </span>
                                        <?= htmlspecialchars(ucfirst($check['check_name'])) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?= htmlspecialchars(formatDate($check['last_check'], 'd M Y H:i')) ?>
                                    </small>
                                </div>
                                <?php if ($check['response_time']): ?>
                                    <p class="mb-1 small">Response time: <?= htmlspecialchars($check['response_time']) ?>ms</p>
                                <?php endif; ?>
                                <?php
                                $details = json_decode($check['details'], true);
                                if ($details && isset($details['message'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($details['message']) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- System Resources Chart (Placeholder) -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>System Resources Overview</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Real-time Monitoring</h6>
                    <p>Sistem monitoring ini menyediakan:</p>
                    <ul class="mb-0">
                        <li><strong>Database Health:</strong> Connection status, query performance, data integrity</li>
                        <li><strong>Application Health:</strong> File system checks, session handling, error monitoring</li>
                        <li><strong>Performance Metrics:</strong> Query times, page loads, memory usage tracking</li>
                        <li><strong>Alert System:</strong> Real-time notifications untuk issues critical</li>
                        <li><strong>Health Checks:</strong> Automated system diagnostics</li>
                        <li><strong>Logging System:</strong> Comprehensive audit trails</li>
                    </ul>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="text-center">
                            <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                            <h1 class="h2" style="color: black;"><?= $pageInfo['title'] ?></h1>
                            <p class="small">Login attempts, unauthorized access, suspicious activities</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <i class="fas fa-tachometer-alt fa-2x text-primary mb-2"></i>
                            <h6>Performance Tracking</h6>
                            <p class="small">Response times, throughput, resource utilization</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                            <h6>Alert Management</h6>
                            <p class="small">Automated alerts, escalation procedures, resolution tracking</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function runHealthChecks() {
    if (confirm('Jalankan health checks untuk semua komponen sistem?')) {
        window.location.href = '<?= base_url('monitoring/runHealthChecks') ?>';
    }
}
</script>
