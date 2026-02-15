<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="dashboard">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-info" onclick="toggleView()">
                <i class="bi bi-eye"></i> <span id="viewToggleText">Detail</span>
            </button>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-warning" onclick="showSettings()">
                <i class="bi bi-gear"></i> Settings
            </button>
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

<?php if (true): ?>
<!-- Development Mode Notice -->
<div class="alert alert-info">
    <i class="bi bi-speedometer2 me-2"></i>
    <strong>Enhanced Dashboard:</strong> Dashboard informatif dengan real-time data, analytics, dan actionable insights.
</div>
<?php endif; ?>

<!-- Key Performance Indicators -->
<div class="row mb-4" id="kpi-section">
    <div class="col-12">
        <div class="card border-primary" id="kpi-main-card">
            <div class="card-header bg-primary text-white" id="kpi-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>
                    Key Performance Indicators - Real-time Data
                </h5>
            </div>
            <div class="card-body" id="kpi-content">
                <div class="row">
                    <!-- Financial KPIs -->
                    <div class="col-md-6" id="financial-kpi">
                        <div class="card border-success" id="financial-card">
                            <div class="card-header bg-success text-white">
                                
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-success" id="total-simpanan"><?= formatUang($stats['total_simpanan'] ?? 0) ?></h4>
                                        <small>Total Simpanan</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-danger" id="total-pinjaman"><?= formatUang($stats['total_pinjaman'] ?? 0) ?></h4>
                                        <small>Total Pinjaman</small>
                                    </div>
                                </div>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h5 class="text-info" id="simpanan-bulan-ini"><?= formatUang($stats['simpanan_bulan_ini'] ?? 0) ?></h5>
                                        <small>Simpanan Bulan Ini</small>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="text-warning" id="shu-tahun-ini"><?= formatUang($stats['total_shu'] ?? 0) ?></h5>
                                        <small>SHU Tahun Ini</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Member KPIs -->
                    <div class="col-md-6" id="member-kpi">
                        <div class="card border-info" id="member-card">
                            <div class="card-header bg-info text-white">
                                
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-info" id="total-anggota"><?= $stats['total_anggota'] ?? 0 ?></h4>
                                        <small>Total Anggota</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-primary" id="anggota-aktif"><?= $stats['anggota_aktif'] ?? 0 ?></h4>
                                        <small>Anggota Aktif</small>
                                    </div>
                                </div>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h5 class="text-warning" id="pinjaman-aktif"><?= $stats['pinjaman_aktif'] ?? 0 ?></h5>
                                        <small>Pinjaman Aktif</small>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="text-success" id="total-transaksi"><?= $stats['total_transaksi'] ?? 0 ?></h5>
                                        <small>Total Transaksi</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Risk Indicators -->
                <div class="row mt-3" id="risk-section">
                    <div class="col-12">
                        <div class="card border-danger" id="risk-card">
                            <div class="card-header bg-danger text-white">
                                
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        
                                        <small>Pinjaman Macet</small>
                                    </div>
                                    <div class="col-md-3">
                                        
                                        <small>Angsuran Terlambat</small>
                                    </div>
                                    <div class="col-md-3">
                                        
                                        <small>Rasio Aktif</small>
                                    </div>
                                    <div class="col-md-3">
                                        
                                        <small>Rasio Simpanan/Pinjaman</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities Section -->
<div class="row mb-4" id="activities-section">
    <div class="col-md-8" id="activities-main">
        <div class="card border-info" id="activities-card">
            <div class="card-header bg-info text-white" id="activities-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Aktivitas Terkini
                </h5>
            </div>
            <div class="card-body" id="activities-content">
                <?php if (!empty($recent_activities)): ?>
                    <div class="timeline" id="timeline-container">
                        <?php foreach ($recent_activities as $index => $activity): ?>
                            <div class="d-flex align-items-start mb-3 activity-item" id="activity-<?= $index ?>">
                                <div class="me-3 activity-icon">
                                    <?php if ($activity['type'] === 'anggota'): ?>
                                        <i class="bi bi-person-plus text-primary"></i>
                                    <?php elseif ($activity['type'] === 'simpanan'): ?>
                                        <i class="bi bi-piggy-bank text-success"></i>
                                    <?php elseif ($activity['type'] === 'pinjaman'): ?>
                                        <i class="bi bi-cash-stack text-warning"></i>
                                    <?php else: ?>
                                        <i class="bi bi-activity text-info"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1 activity-content">
                                    
                                    <p class="mb-1 activity-description"><?= htmlspecialchars($activity['description']) ?></p>
                                    <small class="text-muted activity-time"><?= formatTanggal($activity['created_at'], 'd M Y H:i') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4" id="no-activities">
                        <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                        <p class="mb-0">Belum ada aktivitas terkini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4" id="quick-actions-section">
        <div class="card border-warning" id="quick-actions-card">
            <div class="card-header bg-warning text-dark" id="quick-actions-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body" id="quick-actions-content">
                <div class="d-grid gap-2" id="quick-actions-grid">
                    <a href="<?= base_url('anggota/create') ?>" class="btn btn-outline-primary btn-sm quick-action" id="action-add-anggota">
                        <i class="bi bi-person-plus me-2"></i>Tambah Anggota
                    </a>
                    <a href="<?= base_url('simpanan/create') ?>" class="btn btn-outline-success btn-sm quick-action" id="action-add-simpanan">
                        <i class="bi bi-piggy-bank me-2"></i>Setor Simpanan
                    </a>
                    <a href="<?= base_url('pinjaman/create') ?>" class="btn btn-outline-warning btn-sm quick-action" id="action-add-pinjaman">
                        <i class="bi bi-cash-stack me-2"></i>Ajukan Pinjaman
                    </a>
                    <a href="<?= base_url('laporan') ?>" class="btn btn-outline-info btn-sm quick-action" id="action-view-laporan">
                        <i class="bi bi-file-earmark-text me-2"></i>Lihat Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Chart Section -->
<div class="row mb-4" id="chart-section">
    <div class="col-12">
        <div class="card border-secondary" id="chart-card">
            <div class="card-header bg-secondary text-white" id="chart-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up-arrow me-2"></i>
                    Data Bulanan - Grafik Performa
                </h5>
            </div>
            <div class="card-body" id="chart-content">
                <?php if (!empty($monthly_data)): ?>
                    <div class="row">
                        <div class="col-md-8" id="chart-container">
                            <canvas id="monthlyChart" width="400" height="200"></canvas>
                        </div>
                        <div class="col-md-4" id="chart-summary">
                            
                            <div class="mt-3" id="monthly-summary-data">
                                <?php foreach ($monthly_data as $month => $data): ?>
                                    <div class="mb-2 monthly-item" id="month-<?= $month ?>">
                                        <strong><?= $month ?></strong>
                                        <div class="small text-muted">
                                            Simpanan: <?= formatUang($data['simpanan'] ?? 0) ?><br>
                                            Pinjaman: <?= formatUang($data['pinjaman'] ?? 0) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4" id="no-chart-data">
                        <i class="bi bi-graph-up" style="font-size: 2rem;"></i>
                        <p class="mb-0">Data bulanan tidak tersedia</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>

<!-- JavaScript for Chart -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($monthly_data)): ?>
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        // Fallback: show simple data table
        const chartContainer = document.getElementById('monthlyChart');
        if (chartContainer) {
            chartContainer.innerHTML = '<div class="alert alert-warning">Chart library not available. Please check your internet connection.</div>';
        }
        return;
    }
    
    // Prepare chart data
    const monthlyData = <?= json_encode($monthly_data) ?>;
    const labels = Object.keys(monthlyData);
    const simpananData = labels.map(month => monthlyData[month]['simpanan'] || 0);
    const pinjamanData = labels.map(month => monthlyData[month]['pinjaman'] || 0);
    
    // Create chart
    const ctx = document.getElementById('monthlyChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Simpanan',
                    data: simpananData,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Pinjaman',
                    data: pinjamanData,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Grafik Simpanan & Pinjaman Bulanan'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                }
            }
        });
    } else {
        console.error('Chart canvas element not found!');
    }
    <?php endif; ?>
    
    // Auto-refresh dashboard every 30 seconds
    setInterval(function() {
        console.log('Refreshing dashboard data...');
        // Uncomment below to enable auto-refresh
        // location.reload();
    }, 30000);
});
</script>


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page dashboard - enhanced initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Dashboard', 'dashboard');
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