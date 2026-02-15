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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="analytics">Dashboard Analitik</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('analytics/reports') ?>" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-bar-graph"></i> Laporan
            </a>
            <a href="<?= base_url('analytics/predictive') ?>" class="btn btn-outline-success">
                <i class="bi bi-graph-up-arrow"></i> Prediktif
            </a>
        </div>
        <div class="btn-group me-2">
            <select class="form-select" id="timeRange" onchange="changeTimeRange()">
                <option value="7d">7 Hari Terakhir</option>
                <option value="30d" selected>30 Hari Terakhir</option>
                <option value="90d">90 Hari Terakhir</option>
                <option value="1y">Tahun Terakhir</option>
            </select>
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="refreshDashboard()">
            <i class="bi bi-arrow-clockwise"></i> Segarkan
        </button>
    </div>
</div>

<?php if (true): ?>
<!-- Development Mode Notice -->
<div class="alert alert-info">
    <i class="bi bi-graph-up me-2"></i>
    <strong>Analitik Lanjutan:</strong> Dashboard business intelligence real-time dengan analitik prediktif. Berdasarkan tren fintech 2024 - BI dan pengambilan keputusan berbasis data.
</div>
<?php endif; ?>

<!-- Key Performance Indicators -->
<div class="row mb-4">
    <?php foreach ($kpis as $key => $kpi): ?>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="card-title text-uppercase text-muted mb-1">
                            <?= htmlspecialchars($kpi['label']) ?>
                        </h6>
                        <h2 class="mb-1">
                            <?php if ($key === 'total_assets' || $key === 'net_income' || $key === 'loan_portfolio'): ?>
                                Rp <?= formatUang($kpi['value']) ?>
                            <?php elseif ($key === 'member_growth' || $key === 'transaction_volume'): ?>
                                <?= formatAngka($kpi['value']) ?>
                            <?php else: ?>
                                <?= formatPersentase($kpi['value'], 1) ?>
                            <?php endif; ?>
                        </h2>
                        <small class="text-<?= $kpi['trend'] === 'up' ? 'success' : ($kpi['trend'] === 'down' ? 'danger' : 'muted') ?>">
                            <i class="bi bi-arrow-<?= $kpi['trend'] === 'up' ? 'up' : ($kpi['trend'] === 'down' ? 'down' : 'right') ?>"></i>
                            <?= formatPersentase($kpi['change'], 1) ?>
                        </small>
                    </div>
                    <div class="col-auto">
                        <i class="bi
                            <?php
                            if ($key === 'total_assets') echo ' bi-cash-stack';
                            elseif ($key === 'net_income') echo ' bi-graph-up';
                            elseif ($key === 'loan_portfolio') echo ' bi-bank';
                            elseif ($key === 'member_growth') echo ' bi-people';
                            elseif ($key === 'transaction_volume') echo ' bi-activity';
                            elseif ($key === 'average_loan_size') echo ' bi-calculator';
                            elseif ($key === 'savings_rate') echo ' bi-piggy-bank';
                            elseif ($key === 'collection_rate') echo ' bi-check-circle';
                            ?>
                            fa-2x text-<?= $key === 'high_risk_count' ? 'danger' : 'primary' ?>">
                        </i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Trend Charts Row -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Revenue & Savings Trends</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Loan Performance</h5>
            </div>
            <div class="card-body">
                <canvas id="loanChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Risk Analytics & Segmentation -->
<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Risk Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="riskChart" height="250"></canvas>
                <div class="mt-3">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="p-2">
                                <h5 class="text-success mb-0">75%</h5>
                                <small class="text-muted">Low Risk</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2">
                                <h5 class="text-warning mb-0">20%</h5>
                                <small class="text-muted">Medium</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2">
                                <h5 class="text-danger mb-0">4%</h5>
                                <small class="text-muted">High Risk</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2">
                                <h5 class="text-dark mb-0">1%</h5>
                                <small class="text-muted">Very High</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Demografi Anggota</h5>
            </div>
            <div class="card-body">
                <canvas id="demographicsChart" height="250"></canvas>
                <div class="mt-3">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="p-2">
                                <h5 class="text-primary mb-0">35%</h5>
                                <small class="text-muted">25-34 years</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2">
                                <h5 class="text-success mb-0">28%</h5>
                                <small class="text-muted">35-44 years</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2">
                                <h5 class="text-info mb-0">37%</h5>
                                <small class="text-muted">45+ years</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Predictive Insights & Forecasts -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Predictive Insights</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card border-success h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-graph-up-arrow fa-2x text-success mb-2"></i>
                                <h6 class="card-title">Revenue Forecast</h6>
                                <h4 class="text-success mb-1">+23.5%</h4>
                                <p class="card-text small text-muted">Next 3 months</p>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: 85%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-info h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-people fa-2x text-info mb-2"></i>
                                <h6 class="card-title">Pertumbuhan Anggota</h6>
                                <h4 class="text-info mb-1">+18.2%</h4>
                                <p class="card-text small text-muted">6 Bulan Lagi</p>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: 78%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-warning h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-exclamation-triangle fa-2x text-warning mb-2"></i>
                                <h6 class="card-title">Risk Alert</h6>
                                <h4 class="text-warning mb-1">Medium</h4>
                                <p class="card-text small text-muted">Portfolio risk level</p>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: 65%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-primary h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-cash fa-2x text-primary mb-2"></i>
                                <h6 class="card-title">Cash Flow</h6>
                                <h4 class="text-primary mb-1">Positive</h4>
                                <p class="card-text small text-muted">Strong liquidity</p>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: 92%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities & Alerts -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Aktivitas Terkini</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <div class="timeline-item mb-3">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">
                                <?php
                                $activities = [
                                    'Pengajuan pinjaman baru disetujui',
                                    'Simpanan diproses',
                                    'Profil anggota diperbarui',
                                    'Pembayaran diterima',
                                    'Penilaian risiko selesai'
                                ];
                                echo $activities[$i];
                                ?>
                            </h6>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                <?= formatDate(date('Y-m-d H:i:s', strtotime("-{$i} hours")), 'H:i') ?> hari ini
                            </small>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Peringatan & Rekomendasi Sistem</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Permintaan Pinjaman Tinggi:</strong> Pertimbangkan untuk meningkatkan batas persetujuan pinjaman untuk sektor produktif.
                </div>
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Pertumbuhan Simpanan:</strong> Implementasikan kampanye simpanan yang ditargetkan untuk segmen dengan partisipasi rendah.
                </div>
                <div class="alert alert-success mb-3">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Tingkat Penagihan:</strong> Sangat baik, tingkat pembayaran tepat waktu 94% bulan ini.
                </div>
                <div class="alert alert-primary">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Wawasan AI:</strong> Anggota berusia 25-34 menunjukkan tingkat pengembalian pinjaman 40% lebih tinggi.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Pendapatan dan Tren Simpanan Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Pendapatan',
            data: [12000000, 15000000, 18000000, 16000000, 20000000, 22000000, 25000000, 23000000, 28000000, 26000000, 30000000, 32000000],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.4
        }, {
            label: 'Simpanan',
            data: [8000000, 9000000, 11000000, 10000000, 13000000, 14000000, 16000000, 15000000, 18000000, 17000000, 20000000, 21000000],
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                    }
                }
            }
        }
    }
});

// Kinerja Pinjaman Chart
const loanCtx = document.getElementById('loanChart').getContext('2d');
new Chart(loanCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pinjaman Produktif', 'Pinjaman Konsumtif', 'Pinjaman Usaha', 'Pinjaman Darurat'],
        datasets: [{
            data: [45, 25, 20, 10],
            backgroundColor: [
                'rgb(75, 192, 192)',
                'rgb(255, 99, 132)',
                'rgb(255, 205, 86)',
                'rgb(153, 102, 255)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// Distribusi Risiko Chart
const riskCtx = document.getElementById('riskChart').getContext('2d');
new Chart(riskCtx, {
    type: 'pie',
    data: {
        labels: ['Risiko Rendah', 'Risiko Sedang', 'Risiko Tinggi', 'Risiko Sangat Tinggi'],
        datasets: [{
            data: [75, 20, 4, 1],
            backgroundColor: [
                'rgb(40, 167, 69)',
                'rgb(255, 193, 7)',
                'rgb(220, 53, 69)',
                'rgb(52, 58, 64)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// Demografi Chart
const demoCtx = document.getElementById('demographicsChart').getContext('2d');
new Chart(demoCtx, {
    type: 'bar',
    data: {
        labels: ['18-24', '25-34', '35-44', '45-54', '55+'],
        datasets: [{
            label: 'Anggota',
            data: [8, 35, 28, 18, 11],
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgb(54, 162, 235)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        }
    }
});

function changeTimeRange() {
    const timeRange = document.getElementById('timeRange').value;
    // Implement time range filtering
    console.log('Changing time range to:', timeRange);
}

function refreshDashboard() {
    location.reload();
}
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

.ksp-stats-card {
    transition: transform 0.2s;
}

.ksp-stats-card:hover {
    transform: translateY(-2px);
}
</style>
