<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#refreshModal">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

<?php if (hasPermission('admin_access')): ?>
<!-- Super Admin Content -->
<div class="alert alert-info">
    <i class="bi bi-gear me-2"></i>
    <strong>Selamat datang, <?= getCurrentUser()['full_name'] ?>!</strong> Anda login sebagai <strong><?= ucfirst(getCurrentUser()['role']) ?></strong> dengan akses manajemen.
</div>
<?php elseif (hasPermission('manage_users')): ?>
<!-- Admin Content -->
<div class="alert alert-info">
    <i class="bi bi-gear me-2"></i>
    <strong>Selamat datang, <?= getCurrentUser()['full_name'] ?>!</strong> Anda login sebagai <strong>Administrator</strong> dengan akses manajemen.
</div>
<?php elseif (hasPermission('approve_pinjaman')): ?>
<!-- Supervisor Content -->
<div class="alert alert-warning">
    <i class="bi bi-eye me-2"></i>
    <strong>Selamat datang, <?= getCurrentUser()['full_name'] ?>!</strong> Anda login sebagai <strong>Supervisor</strong> dengan akses approval.
</div>
<?php elseif (hasPermission('transaksi_simpanan')): ?>
<!-- Staff Content -->
<div class="alert alert-primary">
    <i class="bi bi-person-workspace me-2"></i>
    <strong>Selamat datang, <?= getCurrentUser()['full_name'] ?>!</strong> Anda login sebagai <strong>Staff</strong> dengan akses operasional.
</div>
<?php else: ?>
<!-- Member Content -->
<div class="alert alert-secondary">
    <i class="bi bi-person me-2"></i>
    <strong>Selamat datang, <?= getCurrentUser()['full_name'] ?>!</strong> Anda login sebagai <strong>Member</strong>.
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
                                        <h5 class="card-title text-uppercase mb-0">Total Anggota</h5>
                                        <span class="h2 font-weight-bold mb-0"><?= number_format($stats['total_anggota'] ?? 0) ?></span>
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
                                        <h5 class="card-title text-uppercase mb-0">Total Simpanan</h5>
                                        <span class="h2 font-weight-bold mb-0">Rp <?= formatCurrency($stats['total_simpanan']) ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-white bg-opacity-25 rounded p-3">
                                            <i class="bi bi-piggy-bank"></i>
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
                                        <h5 class="card-title text-uppercase mb-0">Total Pinjaman</h5>
                                        <span class="h2 font-weight-bold mb-0">Rp <?= formatCurrency($stats['total_pinjaman']) ?></span>
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
                                        <h5 class="card-title text-uppercase mb-0">Penjualan Bulan Ini</h5>
                                        <span class="h2 font-weight-bold mb-0">Rp <?= formatCurrency($stats['penjualan_bulan']) ?></span>
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
                                <h5 class="card-title mb-0">Grafik Bulanan</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="monthlyChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted"><?= $activity['action'] ?></small>
                                                <small class="text-muted"><?= formatDate($activity['created_at'], 'd M H:i') ?></small>
                                            </div>
                                            <div class="text-truncate small"><?= $activity['full_name'] ?? 'System' ?></div>
                                        </div>
                                    <?php endforeach; ?>
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
                                        <h6 class="card-title mb-1">Angsuran Terlambat</h6>
                                        <p class="card-text mb-0"><?= $stats['angsuran_terlambat'] ?> angsuran terlambat</p>
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
                                        <h6 class="card-title mb-1">Stok Rendah</h6>
                                        <p class="card-text mb-0"><?= $stats['stok_rendah'] ?> produk perlu restock</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

</main>
</div>
