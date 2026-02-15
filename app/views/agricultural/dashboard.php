<?php
// Dependency management
if (!function_exists('initView')) {
    require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';
}
if (!function_exists('getCurrentUser')) {
    require_once __DIR__ . '/../../../config/config.php';
}
$pageInfo = $pageInfo ?? (function_exists('initView') ? initView() : []);
$user = $user ?? (function_exists('getCurrentUser') ? getCurrentUser() : []);
$role = $role ?? ($user['role'] ?? 'admin');
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="agricultural">Agricultural Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 bg-dark text-white p-0" style="min-height: 100vh;">
                <div class="p-3">
                    <h5><i class="fas fa-seedling me-2"></i>KSP Pertanian</h5>
                    <hr>
                    <nav class="nav flex-column">
                        <a href="#" class="nav-link text-white active">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="#" class="nav-link text-white">
                            <i class="fas fa-map me-2"></i>Lahan
                        </a>
                        <a href="#" class="nav-link text-white">
                            <i class="fas fa-calendar-alt me-2"></i>Planning Tanam
                        </a>
                        <a href="#" class="nav-link text-white">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </a>
                        <a href="#" class="nav-link text-white">
                            <i class="fas fa-chart-line me-2"></i>Laporan
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-seedling me-2"></i>Dashboard Koperasi Pertanian</h2>
                        <p class="text-muted">Monitor dan kelola kegiatan pertanian anggota</p>
                    </div>
                    <div>
                        <button class="btn btn-success" onclick="showPlanningModal()">
                            <i class="fas fa-plus me-2"></i>Planning Baru
                        </button>
                        <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                            <i class="fas fa-sync me-2"></i>Refresh
                        </button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Lahan</h6>
                                        <h3 id="totalLahan">0</h3>
                                        <small class="text-success"><i class="fas fa-arrow-up me-1"></i>Hektar</small>
                                    </div>
                                    <div class="stat-icon green">
                                        <i class="fas fa-map"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Sedang Ditanam</h6>
                                        <h3 id="sedangDitanam">0</h3>
                                        <small class="text-info"><i class="fas fa-seedling me-1"></i>Aktif</small>
                                    </div>
                                    <div class="stat-icon blue">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Siap Panen</h6>
                                        <h3 id="siapPanen">0</h3>
                                        <small class="text-warning"><i class="fas fa-clock me-1"></i>Proses</small>
                                    </div>
                                    <div class="stat-icon orange">
                                        <i class="fas fa-wheat"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Produksi</h6>
                                        <h3 id="totalProduksi">0</h3>
                                        <small class="text-success"><i class="fas fa-arrow-up me-1"></i>Ton</small>
                                    </div>
                                    <div class="stat-icon purple">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Planning Status Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-chart-pie me-2"></i>Status Planning Tanam</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="planningChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tanaman Distribution -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-chart-bar me-2"></i>Distribusi Tanaman</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="tanamanChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Activities -->
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-history me-2"></i>Aktivitas Terkini</h6>
                            </div>
                            <div class="card-body">
                                <div id="recentActivities">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-bolt me-2"></i>Aksi Cepat</h6>
                            </div>
                            <div class="card-body">
                                <div class="quick-action mb-3" onclick="showPlanningModal()">
                                    <i class="fas fa-plus fa-2x text-success mb-2"></i>
                                    <h6>Planning Baru</h6>
                                    <small class="text-muted">Buat planning tanam baru</small>
                                </div>
                                <div class="quick-action mb-3" onclick="showInventoryModal()">
                                    <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                                    <h6>Tambah Inventory</h6>
                                    <small class="text-muted">Input pupuk/pestisida/benih</small>
                                </div>
                                <div class="quick-action" onclick="showLahanModal()">
                                    <i class="fas fa-map fa-2x text-warning mb-2"></i>
                                    <h6>Tambah Lahan</h6>
                                    <small class="text-muted">Registrasi lahan baru</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Planning Modal -->
    <div class="modal fade" id="planningModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-alt me-2"></i>Planning Tanam Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="planningForm">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Anggota</label>
                                <select class="form-select" id="anggotaSelect" required>
                                    <option value="">-- Pilih Anggota --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lahan</label>
                                <select class="form-select" id="lahanSelect" required>
                                    <option value="">-- Pilih Lahan --</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanaman</label>
                                <select class="form-select" id="tanamanSelect" required>
                                    <option value="">-- Pilih Tanaman --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Periode Tanam</label>
                                <input type="month" class="form-control" id="periodeTanam" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Luas Tanam (Ha)</label>
                                <input type="number" class="form-control" id="luasTanam" step="0.1" min="0.1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" id="catatan" rows="1"></textarea>
                            </div>
                        </div>
                        <div class="mt-3" id="estimasiPreview" style="display: none;">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-calculator me-2"></i>Estimasi:</h6>
                                <div id="estimasiDetails"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="savePlanning()">
                        <i class="fas fa-save me-2"></i>Simpan Planning
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    
    
    <script>
        let dashboardData = {};
        let planningChart = null;
        let tanamanChart = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboard();
            loadAnggota();
            loadTanaman();
        });

        // Load Dashboard Data
        async function loadDashboard() {
            try {
                const response = await fetch('/api/agricultural/dashboard');
                const data = await response.json();
                
                if (data.success) {
                    dashboardData = data.data;
                    updateStatistics();
                    updateRecentActivities();
                    loadCharts();
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        // Update Statistics
        function updateStatistics() {
            const stats = dashboardData.stats;
            document.getElementById('totalLahan').textContent = stats.total_lahan || 0;
            document.getElementById('sedangDitanam').textContent = stats.sedang_ditanam || 0;
            document.getElementById('siapPanen').textContent = stats.siap_panen || 0;
            document.getElementById('totalProduksi').textContent = (stats.total_produksi || 0).toFixed(1);
        }

        // Update Recent Activities
        function updateRecentActivities() {
            const activities = dashboardData.recent_activities || [];
            const container = document.getElementById('recentActivities');
            
            if (activities.length === 0) {
                container.innerHTML = '<p class="text-muted">Belum ada aktivitas</p>';
                return;
            }
            
            container.innerHTML = activities.map(activity => {
                const statusClass = activity.status === 'proses_tanam' ? 'active' : 
                                  activity.status === 'siap_panen' ? 'pending' : 'completed';
                const statusIcon = activity.status === 'proses_tanam' ? 'fa-seedling' :
                                  activity.status === 'siap_panen' ? 'fa-clock' : 'fa-check';
                
                return `
                    <div class="activity-item ${statusClass}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">${activity.nama_tanaman}</h6>
                                <p class="mb-1">${activity.nama_lengkap} - ${activity.nama_lahan}</p>
                                <small class="text-muted">
                                    <i class="fas fa-${statusIcon} me-1"></i>
                                    ${activity.status} • ${activity.luas_tanam} Ha
                                </small>
                            </div>
                            <small class="text-muted">${formatDate(activity.updated_at)}</small>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Load Charts
        async function loadCharts() {
            try {
                const response = await fetch('/api/agricultural/statistics');
                const data = await response.json();
                
                if (data.success) {
                    createPlanningChart(data.data.planning_stats);
                    createTanamanChart(data.data.tanaman_stats);
                }
            } catch (error) {
                console.error('Error loading charts:', error);
            }
        }

        // Create Planning Chart
        function createPlanningChart(stats) {
            const ctx = document.getElementById('planningChart').getContext('2d');
            
            if (planningChart) {
                planningChart.destroy();
            }
            
            planningChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: stats.map(s => s.status),
                    datasets: [{
                        data: stats.map(s => s.count),
                        backgroundColor: [
                            '#10b981',
                            '#f59e0b',
                            '#3b82f6',
                            '#ef4444',
                            '#8b5cf6',
                            '#6b7280'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Create Tanaman Chart
        function createTanamanChart(stats) {
            const ctx = document.getElementById('tanamanChart').getContext('2d');
            
            if (tanamanChart) {
                tanamanChart.destroy();
            }
            
            tanamanChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: stats.map(s => s.nama_tanaman),
                    datasets: [{
                        label: 'Luas (Ha)',
                        data: stats.map(s => s.total_luas),
                        backgroundColor: '#3b82f6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Load Anggota
        async function loadAnggota() {
            try {
                const response = await fetch('/api/anggota');
                const data = await response.json();
                
                if (data.success) {
                    const select = document.getElementById('anggotaSelect');
                    data.data.forEach(anggota => {
                        const option = document.createElement('option');
                        option.value = anggota.id;
                        option.textContent = `${anggota.nama_lengkap} (${anggota.no_anggota})`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading anggota:', error);
            }
        }

        // Load Tanaman
        async function loadTanaman() {
            try {
                const response = await fetch('/api/agricultural/tanaman');
                const data = await response.json();
                
                if (data.success) {
                    const select = document.getElementById('tanamanSelect');
                    data.data.forEach(tanaman => {
                        const option = document.createElement('option');
                        option.value = tanaman.id;
                        option.textContent = `${tanaman.nama_tanaman} (${tanaman.kategori})`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading tanaman:', error);
            }
        }

        // Load Lahan based on Anggota
        document.getElementById('anggotaSelect').addEventListener('change', function() {
            const anggotaId = this.value;
            if (anggotaId) {
                loadLahan(anggotaId);
            }
        });

        async function loadLahan(anggotaId) {
            try {
                const response = await fetch(`/api/agricultural/lahan/${anggotaId}`);
                const data = await response.json();
                
                if (data.success) {
                    const select = document.getElementById('lahanSelect');
                    select.innerHTML = '<option value="">-- Pilih Lahan --</option>';
                    data.data.forEach(lahan => {
                        const option = document.createElement('option');
                        option.value = lahan.id;
                        option.textContent = `${lahan.nama_lahan} (${lahan.luas_lahan} Ha)`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading lahan:', error);
            }
        }

        // Show Planning Modal
        function showPlanningModal() {
            const modal = new bootstrap.Modal(document.getElementById('planningModal'));
            modal.show();
        }

        // Calculate Estimasi
        document.getElementById('tanamanSelect').addEventListener('change', function() {
            const tanamanId = this.value;
            const luasTanam = document.getElementById('luasTanam').value;
            
            if (tanamanId && luasTanam) {
                calculateEstimasi(tanamanId, luasTanam);
            }
        });

        document.getElementById('luasTanam').addEventListener('input', function() {
            const tanamanId = document.getElementById('tanamanSelect').value;
            const luasTanam = this.value;
            
            if (tanamanId && luasTanam) {
                calculateEstimasi(tanamanId, luasTanam);
            }
        });

        async function calculateEstimasi(tanamanId, luasTanam) {
            try {
                const response = await fetch('/api/agricultural/tanaman');
                const data = await response.json();
                
                if (data.success) {
                    const tanaman = data.data.find(t => t.id == tanamanId);
                    if (tanaman) {
                        const avgProductivity = (parseFloat(tanaman.produktivitas_min) + parseFloat(tanaman.produktivitas_max)) / 2;
                        const avgPrice = (parseFloat(tanaman.harga_jual_min) + parseFloat(tanaman.harga_jual_max)) / 2;
                        
                        const estimasiProduksi = luasTanam * avgProductivity;
                        const estimasiPendapatan = estimasiProduksi * avgPrice;
                        const kebutuhanModal = estimasiPendapatan * 0.3;
                        
                        document.getElementById('estimasiDetails').innerHTML = `
                            <div class="row">
                                <div class="col-6">
                                    <small>Estimasi Produksi:</small><br>
                                    <strong>${estimasiProduksi.toFixed(2)} Ton</strong>
                                </div>
                                <div class="col-6">
                                    <small>Estimasi Pendapatan:</small><br>
                                    <strong>Rp ${formatNumber(estimasiPendapatan)}</strong>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <small>Kebutuhan Modal (30%):</small><br>
                                    <strong class="text-warning">Rp ${formatNumber(kebutuhanModal)}</strong>
                                </div>
                            </div>
                        `;
                        document.getElementById('estimasiPreview').style.display = 'block';
                    }
                }
            } catch (error) {
                console.error('Error calculating estimasi:', error);
            }
        }

        // Save Planning
        async function savePlanning() {
            const formData = {
                anggota_id: document.getElementById('anggotaSelect').value,
                lahan_id: document.getElementById('lahanSelect').value,
                tanaman_id: document.getElementById('tanamanSelect').value,
                periode_tanam: document.getElementById('periodeTanam').value,
                luas_tanam: document.getElementById('luasTanam').value,
                catatan: document.getElementById('catatan').value
            };
            
            try {
                const response = await fetch('/api/agricultural/planning', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Planning berhasil dibuat!');
                    bootstrap.Modal.getInstance(document.getElementById('planningModal')).hide();
                    document.getElementById('planningForm').reset();
                    loadDashboard();
                } else {
                    alert('Gagal membuat planning: ' + data.message);
                }
            } catch (error) {
                console.error('Error saving planning:', error);
                alert('Terjadi kesalahan saat menyimpan planning');
            }
        }

        // Refresh Dashboard
        function refreshDashboard() {
            loadDashboard();
        }

        // Utility Functions
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { 
                day: 'numeric', 
                month: 'short', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatNumber(num) {
            return parseFloat(num).toLocaleDateString('id-ID');
        }

        // Placeholder functions for other modals
        function showInventoryModal() {
            alert('Fitur inventory akan segera tersedia');
        }

        function showLahanModal() {
            alert('Fitur manajemen lahan akan segera tersedia');
        }
    </script>
