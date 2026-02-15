<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>



<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="admin-crud">Admin CRUD Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="admin" class="btn btn-sm btn-outline-secondary">
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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="admin-crud">Admin CRUD Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="admin" class="btn btn-sm btn-outline-secondary">
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






<!-- Flash Messages -->
<?php if ($error = getFlashMessage("error")): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success = getFlashMessage("success")): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;

// Get comprehensive admin statistics
$stats = [
    'total_anggota' => (fetchRow("SELECT COUNT(*) as total FROM anggota WHERE status != 'deleted'") ?? [])['total'] ?? 0,
    'active_anggota' => (fetchRow("SELECT COUNT(*) as total FROM anggota WHERE status = 'active'") ?? [])['total'] ?? 0,
    'total_simpanan' => (fetchRow("SELECT COUNT(*) as total FROM simpanan WHERE status = 'active'") ?? [])['total'] ?? 0,
    'total_saldo_simpanan' => (fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM simpanan WHERE status = 'active'") ?? [])['total'] ?? 0,
    'total_pinjaman' => (fetchRow("SELECT COUNT(*) as total FROM pinjaman WHERE status IN ('aktif', 'approved')") ?? [])['total'] ?? 0,
    'total_outstanding_pinjaman' => (fetchRow("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total FROM pinjaman WHERE status = 'aktif'") ?? [])['total'] ?? 0,
    'pending_applications' => (fetchRow("SELECT COUNT(*) as total FROM pinjaman WHERE status = 'pending'") ?? [])['total'] ?? 0,
    'total_transactions_today' => (fetchRow("SELECT COUNT(*) as total FROM transaksi_simpanan WHERE DATE(created_at) = CURDATE()") ?? [])['total'] ?? 0 + 
                                 (fetchRow("SELECT COUNT(*) as total FROM transaksi_pinjaman WHERE DATE(tanggal) = CURDATE()") ?? [])['total'] ?? 0
];

// Get recent activities across all modules
$recent_activities = [
    [
        'type' => 'info',
        'icon' => 'bi-person-plus',
        'message' => 'New member registration',
        'time' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'module' => 'Anggota'
    ],
    [
        'type' => 'success',
        'icon' => 'bi-cash-stack',
        'message' => 'Loan application approved',
        'time' => date('Y-m-d H:i:s', strtotime('-3 hours')),
        'module' => 'Pinjaman'
    ],
    [
        'type' => 'warning',
        'icon' => 'bi-exclamation-triangle',
        'message' => 'Overdue payment detected',
        'time' => date('Y-m-d H:i:s', strtotime('-5 hours')),
        'module' => 'Monitoring'
    ],
    [
        'type' => 'info',
        'icon' => 'bi-piggy-bank',
        'message' => 'New savings account created',
        'time' => date('Y-m-d H:i:s', strtotime('-6 hours')),
        'module' => 'Simpanan'
    ]
];

// Get CRUD navigation items
$crud_modules = [
    [
        'name' => 'Manajemen Anggota',
        'icon' => 'bi-people',
        'color' => 'primary',
        'description' => 'Kelola data anggota koperasi',
        'actions' => [
            ['url' => base_url('anggota'), 'label' => 'Daftar Anggota', 'icon' => 'bi-list'],
            ['url' => base_url('anggota/create'), 'label' => 'Tambah Anggota', 'icon' => 'bi-plus-circle'],
            ['url' => base_url('anggota/reports'), 'label' => 'Laporan', 'icon' => 'bi-file-earmark-text']
        ],
        'stats' => [
            'total' => $stats['total_anggota'],
            'active' => $stats['active_anggota'],
            'new_this_month' => (fetchRow("SELECT COUNT(*) as count FROM anggota WHERE MONTH(created_at) = MONTH(CURRENT_DATE)") ?? [])['count'] ?? 0
        ]
    ],
    [
        'name' => 'Manajemen Simpanan',
        'icon' => 'bi-piggy-bank',
        'color' => 'success',
        'description' => 'Kelola rekening simpanan anggota',
        'actions' => [
            ['url' => base_url('simpanan'), 'label' => 'Daftar Simpanan', 'icon' => 'bi-list'],
            ['url' => base_url('simpanan/create'), 'label' => 'Buka Rekening', 'icon' => 'bi-plus-circle'],
            ['url' => base_url('simpanan/transactions'), 'label' => 'Transaksi', 'icon' => 'bi-arrow-left-right']
        ],
        'stats' => [
            'total_accounts' => $stats['total_simpanan'],
            'total_balance' => $stats['total_saldo_simpanan'],
            'transactions_today' => (fetchRow("SELECT COUNT(*) as count FROM transaksi_simpanan WHERE DATE(created_at) = CURDATE()") ?? [])['count'] ?? 0
        ]
    ],
    [
        'name' => 'Manajemen Pinjaman',
        'icon' => 'bi-cash-stack',
        'color' => 'warning',
        'description' => 'Kelola pengajuan dan pencairan pinjaman',
        'actions' => [
            ['url' => base_url('pinjaman'), 'label' => 'Daftar Pinjaman', 'icon' => 'bi-list'],
            ['url' => base_url('pinjaman/create'), 'label' => 'Ajukan Pinjaman', 'icon' => 'bi-plus-circle'],
            ['url' => base_url('pinjaman/approvals'), 'label' => 'Persetujuan', 'icon' => 'bi-check-circle']
        ],
        'stats' => [
            'total_loans' => $stats['total_pinjaman'],
            'outstanding' => $stats['total_outstanding_pinjaman'],
            'pending' => $stats['pending_applications']
        ]
    ],
    [
        'name' => 'Manajemen Produk',
        'icon' => 'bi-box',
        'color' => 'info',
        'description' => 'Kelola produk dan inventaris',
        'actions' => [
            ['url' => base_url('produk'), 'label' => 'Daftar Produk', 'icon' => 'bi-list'],
            ['url' => base_url('produk/create'), 'label' => 'Tambah Produk', 'icon' => 'bi-plus-circle'],
            ['url' => base_url('inventory'), 'label' => 'Inventaris', 'icon' => 'bi-archive']
        ],
        'stats' => [
            'total_products' => (fetchRow("SELECT COUNT(*) as count FROM produk WHERE is_active = 1") ?? [])['count'] ?? 0,
            'low_stock' => (fetchRow("SELECT COUNT(*) as count FROM produk WHERE stok <= stok_minimal AND is_active = 1") ?? [])['count'] ?? 0,
            'categories' => (fetchRow("SELECT COUNT(*) as count FROM kategori_produk") ?? [])['count'] ?? 0
        ]
    ],
    [
        'name' => 'Manajemen Penjualan',
        'icon' => 'bi-cart3',
        'color' => 'success',
        'description' => 'Kelola transaksi penjualan',
        'actions' => [
            ['url' => base_url('penjualan'), 'label' => 'Daftar Penjualan', 'icon' => 'bi-list'],
            ['url' => base_url('penjualan/create'), 'label' => 'Transaksi Baru', 'icon' => 'bi-plus-circle'],
            ['url' => base_url('penjualan/reports'), 'label' => 'Laporan Penjualan', 'icon' => 'bi-file-earmark-bar-graph']
        ],
        'stats' => [
            'total_sales' => (fetchRow("SELECT COUNT(*) as count FROM penjualan") ?? [])['count'] ?? 0,
            'revenue_today' => (fetchRow("SELECT COALESCE(SUM(total_harga), 0) as total FROM penjualan WHERE DATE(tanggal_penjualan) = CURDATE()") ?? [])['total'] ?? 0,
            'revenue_month' => (fetchRow("SELECT COALESCE(SUM(total_harga), 0) as total FROM penjualan WHERE MONTH(tanggal_penjualan) = MONTH(CURRENT_DATE)") ?? [])['total'] ?? 0
        ]
    ],
    [
        'name' => 'Manajemen Pelanggan',
        'icon' => 'bi-person-badge',
        'color' => 'primary',
        'description' => 'Kelola data pelanggan',
        'actions' => [
            ['url' => base_url('pelanggan'), 'label' => 'Daftar Pelanggan', 'icon' => 'bi-list'],
            ['url' => base_url('pelanggan/create'), 'label' => 'Tambah Pelanggan', 'icon' => 'bi-plus-circle'],
            ['url' => base_url('pelanggan/analysis'), 'label' => 'Analisis', 'icon' => 'bi-graph-up']
        ],
        'stats' => [
            'total_customers' => (fetchRow("SELECT COUNT(*) as count FROM pelanggan") ?? [])['count'] ?? 0,
            'new_this_month' => (fetchRow("SELECT COUNT(*) as count FROM pelanggan WHERE MONTH(created_at) = MONTH(CURRENT_DATE)") ?? [])['count'] ?? 0,
            'total_spending' => (fetchRow("SELECT COALESCE(SUM(total_harga), 0) as total FROM penjualan") ?? [])['total'] ?? 0
        ]
    ]
];

// Calculate financial stats
$crud_modules[3]['stats']['profit'] = $crud_modules[3]['stats']['total_revenue'] - $crud_modules[3]['stats']['total_expenses'];
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    
    
        <button type="button" class="btn btn-sm btn-primary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<?php if (true): ?>
<!-- Development Mode Notice -->
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-shield-check me-2"></i>
    <strong>Admin Dashboard CRUD System:</strong> Comprehensive CRUD operations ready with role-based impact analysis.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Quick Stats Overview -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        
                        <span class="h2 font-weight-bold mb-0 text-primary"><?= formatAngka($stats['total_anggota']) ?></span>
                        <small class="text-muted"><?= formatAngka($stats['active_anggota']) ?> aktif</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        
                        <span class="h2 font-weight-bold mb-0 text-success">Rp <?= formatUang($stats['total_saldo_simpanan']) ?></span>
                        <small class="text-muted"><?= formatAngka($stats['total_simpanan']) ?> rekening</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-piggy-bank fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        
                        <span class="h2 font-weight-bold mb-0 text-warning">Rp <?= formatUang($stats['total_outstanding_pinjaman']) ?></span>
                        <small class="text-muted"><?= formatAngka($stats['total_pinjaman']) ?> aktif</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cash-stack fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        
                        <span class="h2 font-weight-bold mb-0 text-info"><?= formatAngka($stats['total_transactions_today']) ?></span>
                        <small class="text-muted"><?= formatAngka($stats['pending_applications']) ?> pending</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-arrow-left-right fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CRUD Module Navigation -->
<div class="row mb-4">
    <?php foreach ($crud_modules as $module): ?>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">
                        <i class="<?= $module['icon'] ?> me-2"></i>
                        <?= $module['name'] ?>
                    </h5>
                    <small class="text-muted"><?= $module['description'] ?></small>
                </div>
                <span class="badge bg-<?= $module['color'] ?>">CRUD</span>
            </div>
            <div class="card-body">
                <!-- Module Stats -->
                <div class="row mb-3">
                    <?php foreach ($module['stats'] as $key => $value): ?>
                    <div class="col-4 text-center">
                        <div class="text-<?= $module['color'] ?>">
                            <i class="bi bi-<?= $key === 'total' || $key === 'total_accounts' || $key === 'total_loans' ? 'collection' : ($key === 'active' || $key === 'outstanding' || $key === 'total_balance' || $key === 'pending' ? 'check-circle' : 'graph-up') ?>"></i>
                        </div>
                        <div class="fw-bold">
                            <?php 
                            if (strpos($key, 'total') !== false && $key !== 'total_loans') {
                                echo is_numeric($value) && $value > 1000000 ? formatUang($value) : formatAngka($value);
                            } else {
                                echo formatAngka($value);
                            }
                            ?>
                        </div>
                        <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $key)) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <?php foreach ($module['actions'] as $action): ?>
                    <a href="<?= $action['url'] ?>" class="btn btn-outline-<?= $module['color'] ?> btn-sm">
                        <i class="<?= $action['icon'] ?> me-1"></i>
                        <?= $action['label'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Recent Activities & Quick Actions -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Aktivitas Terbaru (Cross-Module Impact)
                </h5>
                <a href="<?= base_url('monitoring') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-activity"></i> View All
                </a>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($recent_activities as $activity): ?>
                    <div class="timeline-item d-flex mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-<?= $activity['type'] ?> rounded-circle p-2">
                                <i class="<?= $activity['icon'] ?> text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between">
                                <div class="fw-medium"><?= $activity['message'] ?></div>
                                <small class="text-muted"><?= formatDate($activity['time']) ?></small>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-folder me-1"></i><?= $activity['module'] ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
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
                        <i class="bi bi-person-plus me-2"></i>Tambah Anggota Baru
                    </a>
                    <a href="<?= base_url('simpanan/create') ?>" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>Buka Rekening Baru
                    </a>
                    <a href="<?= base_url('pinjaman/create') ?>" class="btn btn-warning">
                        <i class="bi bi-cash me-2"></i>Ajukan Pinjaman
                    </a>
                    <a href="<?= base_url('pinjaman/approvals') ?>" class="btn btn-info">
                        <i class="bi bi-check-circle me-2"></i>Review Persetujuan
                        <?php if ($stats['pending_applications'] > 0): ?>
                        <span class="badge bg-danger ms-1"><?= $stats['pending_applications'] ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= base_url('settings') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-gear me-2"></i>Pengaturan Sistem
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Impact Analysis Section -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-diagram-3 me-2"></i>
                    CRUD Impact Analysis - Cross-Role Effects
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="text-primary">
                                <i class="bi bi-people fa-2x"></i>
                            </div>
                            <div class="fw-medium">Anggota CRUD</div>
                            <small class="text-muted">Affects: Member Portal, Dashboard Stats, Loan Eligibility</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="text-success">
                                <i class="bi bi-piggy-bank fa-2x"></i>
                            </div>
                            <div class="fw-medium">Simpanan CRUD</div>
                            <small class="text-muted">Affects: Member Portal, Financial Reports, Dashboard Stats</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="text-warning">
                                <i class="bi bi-cash-stack fa-2x"></i>
                            </div>
                            <div class="fw-medium">Pinjaman CRUD</div>
                            <small class="text-muted">Affects: Member Portal, Payment Schedule, Risk Assessment</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});

// Auto-refresh dashboard stats every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);
</script>
?>


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page admin - dashboard_crud initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Dashboard_crud', 'admin-dashboard_crud');
    }
});

// Global functions
function saveAdmin() {
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


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page admin - dashboard_crud initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Dashboard crud', 'admin-dashboard_crud');
    }
});

// Global functions
function saveAdmin() {
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