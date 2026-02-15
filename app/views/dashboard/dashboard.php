<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>


</div>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="dashboard">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="dashboard" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
    </div>
</div>


<!-- Flash Messages -->
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
// Standardized view template with dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;

// Get dashboard statistics
$stats = [
    'total_anggota' => fetchRow("SELECT COUNT(*) as total FROM anggota")['total'] ?? 0,
    'total_simpanan' => fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM simpanan")['total'] ?? 0,
    'total_pinjaman' => fetchRow("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total FROM pinjaman WHERE status = 'aktif'")['total'] ?? 0,
    'total_shu' => 5000000
];

$recent_activities = [
    ['type' => 'info', 'message' => 'Dashboard loaded with standardized dependencies', 'time' => date('Y-m-d H:i:s')]
];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    
    
        <button type="button" class="btn btn-sm btn-primary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<?php if (true): ?>
<!-- Development Mode Notice -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-gear me-2"></i>
    <strong>Mode Development:</strong> Standardized dependencies and consistent file structure.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        
                        <span class="h2 font-weight-bold mb-0 text-primary"><?= formatAngka($stats['total_anggota']) ?></span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        
                        <span class="h2 font-weight-bold mb-0 text-success">Rp <?= formatUang($stats['total_simpanan']) ?></span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-piggy-bank fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        
                        <span class="h2 font-weight-bold mb-0 text-warning">Rp <?= formatUang($stats['total_pinjaman']) ?></span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cash-stack fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        
                        <span class="h2 font-weight-bold mb-0 text-info">Rp <?= formatUang($stats['total_shu']) ?></span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-up fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Access & Recent Activities -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Aktivitas Terbaru
                </h5>
                <a href="<?= base_url('monitoring') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-activity me-1"></i>View Logs
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_activities)): ?>
                    <div class="timeline">
                        <?php foreach (array_slice($recent_activities, 0, 5) as $activity): ?>
                            <div class="timeline-item d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-<?= getActivityColor($activity['type'] ?? 'info') ?> rounded-circle p-2">
                                        <i class="bi bi-<?= getActivityIcon($activity['type'] ?? 'info') ?> text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-medium"><?= $activity['message'] ?? 'No message' ?></div>
                                    <small class="text-muted"><?= formatDate($activity['time'] ?? date('Y-m-d H:i:s')) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Belum ada aktivitas terbaru.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= base_url('anggota/create') ?>" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Tambah Anggota
                    </a>
                    <a href="<?= base_url('simpanan') ?>" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>Simpanan Baru
                    </a>
                    <a href="<?= base_url('pinjaman/create') ?>" class="btn btn-warning">
                        <i class="bi bi-cash me-2"></i>Ajukan Pinjaman
                    </a>
                    <a href="<?= base_url('settings') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-gear me-2"></i>Pengaturan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Status -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    System Status
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="text-success">
                                <i class="bi bi-check-circle fa-2x"></i>
                            </div>
                            <div class="fw-medium">Dependencies</div>
                            <small class="text-success">Standardized</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="text-success">
                                <i class="bi bi-check-circle fa-2x"></i>
                            </div>
                            <div class="fw-medium">File Structure</div>
                            <small class="text-success">Consistent</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="text-success">
                                <i class="bi bi-check-circle fa-2x"></i>
                            </div>
                            <div class="fw-medium">Naming</div>
                            <small class="text-success">Standardized</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="text-success">
                                <i class="bi bi-check-circle fa-2x"></i>
                            </div>
                            <div class="fw-medium">Page Titles</div>
                            <small class="text-success">Dynamic</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper functions for activities
function getActivityColor($type) {
    $colors = [
        'success' => 'success',
        'error' => 'danger',
        'warning' => 'warning',
        'info' => 'info'
    ];
    return $colors[$type] ?? 'info';
}

function getActivityIcon($type) {
    $icons = [
        'success' => 'check-circle',
        'error' => 'x-circle',
        'warning' => 'exclamation-triangle',
        'info' => 'info-circle'
    ];
    return $icons[$type] ?? 'info-circle';
}
?>


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page dashboard - dashboard initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Dashboard', 'dashboard-dashboard');
    }
});

// Global functions
function saveDashboard() {
    const form = document.querySelector('form');
    if (form) {
        form.dispatchEvent(new Event('submit'));
    }
}
</script>

<style>
/* Page-specific styles */
.page-title {
    font-weight: 700;
}

.main-content {
    min-height: 400px;
}
</style>