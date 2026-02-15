<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>


        <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-dashboard">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
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

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        
        
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#refreshModal">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

<?php if (true): ?>
<!-- Welcome Message -->
<div class="alert alert-info">
    <i class="bi bi-gear me-2"></i>
    <strong>Selamat datang!</strong> Anda sedang dalam mode development. Semua fitur dapat diakses tanpa autentikasi.
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
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-6 col-xl-3 mb-4">
                        <div class="card ksp-stats-card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        
                                        <span class="h2 font-weight-bold mb-0 text-primary"><?= formatAngka($stats['total_anggota'] ?? 0) ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-white bg-opacity-25 rounded p-3">
                                            <i class="bi bi-people"></i>
                                        </div>
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
                                        
                                        <span class="h2 font-weight-bold mb-0 text-success">Rp <?= formatUang($stats['total_simpanan'] ?? 0) ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-white bg-opacity-25 rounded p-3">
                                            <i class="bi bi-cash-coin"></i>
                                        </div>
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
                                        
                                        <span class="h2 font-weight-bold mb-0 text-warning">Rp <?= formatUang($stats['total_pinjaman'] ?? 0) ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-white bg-opacity-25 rounded p-3">
                                            <i class="bi bi-cash-stack"></i>
                                        </div>
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
                                        
                                        <span class="h2 font-weight-bold mb-0 text-info">Rp <?= formatUang($stats['penjualan_bulan'] ?? 0) ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-white bg-opacity-25 rounded p-3">
                                            <i class="bi bi-cart3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts and Tables -->
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                
                            </div>
                            <div class="card-body">
                                <canvas id="monthlyChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php if (!empty($recent_activities)): ?>
                                        <?php foreach ($recent_activities as $activity): ?>
                                            <div class="list-group-item px-0">
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">
                                                        <?php if (isset($activity['action'])): ?>
                                                            <?= htmlspecialchars($activity['action']) ?>
                                                        <?php elseif (isset($activity['title'])): ?>
                                                            <?= htmlspecialchars($activity['title']) ?>
                                                        <?php else: ?>
                                                            Activity
                                                        <?php endif; ?>
                                                    </small>
                                                    <small class="text-muted">
                                                        <?php if (isset($activity['created_at'])): ?>
                                                            <?= formatDate($activity['created_at'], 'd M H:i') ?>
                                                        <?php elseif (isset($activity['time'])): ?>
                                                            <?= htmlspecialchars($activity['time']) ?>
                                                        <?php else: ?>
                                                            Now
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                <div class="text-truncate small">
                                                    <?php if (isset($activity['full_name'])): ?>
                                                        <?= htmlspecialchars($activity['full_name']) ?>
                                                    <?php elseif (isset($activity['description'])): ?>
                                                        <?= htmlspecialchars($activity['description']) ?>
                                                    <?php else: ?>
                                                        System
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="list-group-item px-0">
                                            <div class="text-center text-muted py-3">
                                                <i class="bi bi-clock-history fa-2x mb-2"></i>
                                                <div>Tidak ada aktivitas terbaru</div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Alert Cards -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card border-left border-warning border-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle text-warning fa-2x me-3"></i>
                                    <div>
                                        
                                        <p class="card-text mb-0"><?= $stats['angsuran_terlambat'] ?? 0 ?> angsuran terlambat</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card border-left border-danger border-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-box text-danger fa-2x me-3"></i>
                                    <div>
                                        
                                        <p class="card-text mb-0"><?= $stats['stok_rendah'] ?? 0 ?> produk perlu restock</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
if (typeof Chart === 'undefined') {
  const fallback = document.createElement('script');
  fallback.src = 'https://unpkg.com/chart.js@4.4.1/dist/chart.umd.js';
  document.head.appendChild(fallback);
}
</script>
<script>
    // Monthly Chart
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($monthly_data, 'month')) ?>,
            datasets: [{
                label: 'Simpanan',
                data: <?= json_encode(array_column($monthly_data, 'simpanan')) ?>,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Penjualan',
                data: <?= json_encode(array_column($monthly_data, 'penjualan')) ?>,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
</script>


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page dashboard - index initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Dashboard', 'dashboard-index');
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