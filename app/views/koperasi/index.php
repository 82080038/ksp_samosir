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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="koperasi">Manajemen Koperasi</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0">
                <i class="fas fa-university me-2"></i>
                KSP Samosir
            </h5>
            <small>Koperasi Management System</small>
        </div>
        
        <div class="sidebar-menu">
            <a href="#dashboard" class="menu-item active" data-page="dashboard">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </a>
            <a href="#anggota" class="menu-item" data-page="anggota">
                <i class="fas fa-users me-2"></i>
                Anggota
            </a>
            <a href="#simpanan" class="menu-item" data-page="simpanan">
                <i class="fas fa-piggy-bank me-2"></i>
                Simpanan
            </a>
            <a href="#pinjaman" class="menu-item" data-page="pinjaman">
                <i class="fas fa-hand-holding-usd me-2"></i>
                Pinjaman
            </a>
            <a href="#angsuran" class="menu-item" data-page="angsuran">
                <i class="fas fa-calendar-check me-2"></i>
                Angsuran
            </a>
            <a href="#laporan" class="menu-item" data-page="laporan">
                <i class="fas fa-chart-line me-2"></i>
                Laporan
            </a>
            <a href="#settings" class="menu-item" data-page="settings">
                <i class="fas fa-cogs me-2"></i>
                Settings
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    
        <div id="pageContent">
            <!-- Dashboard Page -->
            <div id="dashboard-page" class="page-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">Dashboard Koperasi</h2>
                        <p class="text-muted">Overview koperasi KSP Samosir</p>
                    </div>
                    <div>
                        <button class="action-btn" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt"></i>
                            Refresh
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4" id="statsContainer">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="mb-1" id="totalAnggota">-</h3>
                            <p class="text-muted mb-0">Total Anggota</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-piggy-bank"></i>
                            </div>
                            <h3 class="mb-1" id="totalSimpanan">-</h3>
                            <p class="text-muted mb-0">Total Simpanan</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <h3 class="mb-1" id="totalPinjaman">-</h3>
                            <p class="text-muted mb-0">Total Pinjaman</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-info bg-opacity-10 text-info">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h3 class="mb-1" id="angsuranBulanIni">-</h3>
                            <p class="text-muted mb-0">Angsuran Bulan Ini</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="table-card">
                            <h5 class="mb-3">Quick Actions</h5>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <button class="action-btn w-100" onclick="showModal('anggotaModal')">
                                        <i class="fas fa-user-plus"></i>
                                        Tambah Anggota
                                    </button>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <button class="action-btn w-100" onclick="showModal('simpananModal')">
                                        <i class="fas fa-plus-circle"></i>
                                        Tambah Simpanan
                                    </button>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <button class="action-btn w-100" onclick="showModal('pinjamanModal')">
                                        <i class="fas fa-hand-holding-usd"></i>
                                        Ajukan Pinjaman
                                    </button>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <button class="action-btn w-100" onclick="showModal('angsuranModal')">
                                        <i class="fas fa-calendar-plus"></i>
                                        Bayar Angsuran
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="table-card">
                            <h5 class="mb-3">Pinjaman Pending</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No. Anggota</th>
                                            <th>Nama</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pendingPinjamanTable">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="table-card">
                            <h5 class="mb-3">Angsuran Jatuh Tempo</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No. Anggota</th>
                                            <th>Nama</th>
                                            <th>Jatuh Tempo</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="jatuhTempoTable">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other pages will be loaded dynamically -->
            <div id="anggota-page" class="page-content" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">Data Anggota</h2>
                        <p class="text-muted">Manajemen data anggota koperasi</p>
                    </div>
                    <div>
                        <button class="action-btn" onclick="showModal('anggotaModal')">
                            <i class="fas fa-user-plus"></i>
                            Tambah Anggota
                        </button>
                    </div>
                </div>

                <div class="table-card">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchAnggota" placeholder="Cari anggota...">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterStatus">
                                <option value="">Semua Status</option>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                                <option value="keluar">Keluar</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterUnit">
                                <option value="">Semua Unit</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-primary" onclick="filterAnggota()">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No. Anggota</th>
                                    <th>Nama Lengkap</th>
                                    <th>Unit</th>
                                    <th>Total Simpanan</th>
                                    <th>Total Pinjaman</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="anggotaTable">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    

    <!-- Modals -->
    <!-- Anggota Modal -->
    <div class="modal fade" id="anggotaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Anggota Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="anggotaForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NIK</label>
                                <input type="text" class="form-control" name="nik" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" name="tempat_lahir" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" name="tanggal_lahir" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jenis Kelamin</label>
                                <select class="form-select" name="jenis_kelamin" required>
                                    <option value="">Pilih</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" rows="2" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">No. HP</label>
                                <input type="tel" class="form-control" name="no_hp" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pekerjaan</label>
                                <input type="text" class="form-control" name="pekerjaan" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pendapatan Bulanan</label>
                                <input type="number" class="form-control" name="pendapatan_bulanan" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit</label>
                                <select class="form-select" name="unit_id" required>
                                    <option value="">Pilih Unit</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Non-Aktif</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveAnggota()">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    
    <script src="koperasi-management.js"></script>
