<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';
require_once __DIR__ . '/../../../app/helpers/FormatHelper.php';

$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;

// Safe defaults
$modul = $modul ?? [];
$jenis = $jenis ?? [];
$siblings = $siblings ?? [];
$page_title = $page_title ?? 'Modul Koperasi';

$modulNama = htmlspecialchars($modul['nama_modul'] ?? $page_title);
$modulDesc = htmlspecialchars($modul['deskripsi'] ?? '');
$modulKode = $modul['kode_modul'] ?? '';
$modulKategori = $modul['kategori'] ?? '';
$jenisNama = htmlspecialchars($jenis['nama_jenis'] ?? ucfirst($modulKategori));
$jenisWarna = $jenis['warna_tema'] ?? '#0d6efd';

// Map FA icons to Bootstrap Icons
$biIconMap = [
    'fas fa-piggy-bank' => 'bi-piggy-bank', 'fas fa-seedling' => 'bi-tree-fill',
    'fas fa-cow' => 'bi-heart-pulse', 'fas fa-industry' => 'bi-building-gear',
    'fas fa-store' => 'bi-shop', 'fas fa-shopping-cart' => 'bi-cart',
    'fas fa-fish' => 'bi-water', 'fas fa-plane' => 'bi-airplane',
    'fas fa-bolt' => 'bi-lightning', 'fas fa-leaf' => 'bi-tree',
    'fas fa-lock' => 'bi-safe', 'fas fa-wallet' => 'bi-wallet2',
    'fas fa-qrcode' => 'bi-qr-code', 'fas fa-map' => 'bi-map',
    'fas fa-calendar-alt' => 'bi-calendar-range', 'fas fa-spray-can' => 'bi-droplet',
    'fas fa-wheat' => 'bi-graph-up-arrow', 'fas fa-tint' => 'bi-moisture',
    'fas fa-drumstick-bite' => 'bi-basket', 'fas fa-stethoscope' => 'bi-clipboard2-pulse',
    'fas fa-heart' => 'bi-heart', 'fas fa-cogs' => 'bi-gear-wide-connected',
    'fas fa-boxes' => 'bi-boxes', 'fas fa-check-circle' => 'bi-check-circle',
    'fas fa-truck' => 'bi-truck', 'fas fa-truck-loading' => 'bi-truck',
    'fas fa-tags' => 'bi-tags', 'fas fa-warehouse' => 'bi-house-gear',
    'fas fa-shipping-fast' => 'bi-send', 'fas fa-calendar-check' => 'bi-calendar-check',
    'fas fa-shopping-basket' => 'bi-basket2', 'fas fa-store-alt' => 'bi-shop-window',
    'fas fa-ship' => 'bi-water', 'fas fa-gavel' => 'bi-hammer',
    'fas fa-suitcase' => 'bi-suitcase', 'fas fa-bus' => 'bi-bus-front',
    'fas fa-bed' => 'bi-house-door', 'fas fa-user-tie' => 'bi-person-badge',
    'fas fa-solar-panel' => 'bi-sun', 'fas fa-car-battery' => 'bi-battery-charging',
    'fas fa-plug' => 'bi-plugin', 'fas fa-chart-area' => 'bi-graph-up',
    'fas fa-tree' => 'bi-tree', 'fas fa-gem' => 'bi-gem',
    'fas fa-users' => 'bi-people', 'fas fa-hand-holding-usd' => 'bi-cash-stack',
    'fas fa-chart-line' => 'bi-graph-up', 'fas fa-book' => 'bi-journal-text',
];
$modulIcon = $biIconMap[$modul['icon'] ?? ''] ?? 'bi-grid';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:0.85rem">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('koperasi_modul') ?>"><?= $jenisNama ?></a></li>
                <li class="breadcrumb-item active"><?= $modulNama ?></li>
            </ol>
        </nav>
        <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="koperasi-modul">
            <i class="bi <?= $modulIcon ?> me-2" style="color:<?= $jenisWarna ?>"></i><?= $modulNama ?>
        </h1>
        <p class="text-muted mb-0" style="font-size:0.9rem"><?= $modulDesc ?></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-primary" onclick="showAddForm()">
                <i class="bi bi-plus-circle me-1"></i> Tambah Data
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportData()">
                <i class="bi bi-download me-1"></i> Export
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                             style="width:48px;height:48px;background:<?= $jenisWarna ?>15">
                            <i class="bi <?= $modulIcon ?>" style="font-size:1.3rem;color:<?= $jenisWarna ?>"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted" style="font-size:0.8rem">Total Data</div>
                        <div class="h4 mb-0 fw-bold" id="stat-total">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                             style="width:48px;height:48px;background:#10b98115">
                            <i class="bi bi-check-circle" style="font-size:1.3rem;color:#10b981"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted" style="font-size:0.8rem">Aktif</div>
                        <div class="h4 mb-0 fw-bold text-success" id="stat-active">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                             style="width:48px;height:48px;background:#f59e0b15">
                            <i class="bi bi-clock-history" style="font-size:1.3rem;color:#f59e0b"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted" style="font-size:0.8rem">Bulan Ini</div>
                        <div class="h4 mb-0 fw-bold text-warning" id="stat-month">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                             style="width:48px;height:48px;background:#6366f115">
                            <i class="bi bi-arrow-up-right" style="font-size:1.3rem;color:#6366f1"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted" style="font-size:0.8rem">Pertumbuhan</div>
                        <div class="h4 mb-0 fw-bold" style="color:#6366f1" id="stat-growth">0%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Main Content -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi <?= $modulIcon ?> me-2" style="color:<?= $jenisWarna ?>"></i>Data <?= $modulNama ?>
                </h5>
                <div class="input-group" style="max-width:250px">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0" placeholder="Cari..." id="searchInput">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="dataTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Nama / Kode</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th style="width:100px" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi <?= $modulIcon ?> d-block mb-2" style="font-size:2.5rem;color:<?= $jenisWarna ?>40"></i>
                                    Modul <strong><?= $modulNama ?></strong> siap digunakan.<br>
                                    <small>Klik "Tambah Data" untuk mulai menambahkan data.</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- Module Info Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Info Modul</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" style="width:40%">Kode</td><td><code><?= htmlspecialchars($modulKode) ?></code></td></tr>
                    <tr><td class="text-muted">Kategori</td><td><span class="badge" style="background:<?= $jenisWarna ?>"><?= $jenisNama ?></span></td></tr>
                    <tr><td class="text-muted">Tipe</td><td><?= ($modul['is_core'] ?? 0) ? 'Core' : 'Spesifik' ?></td></tr>
                    <tr><td class="text-muted">Status</td><td><span class="badge bg-success">Aktif</span></td></tr>
                </table>
            </div>
        </div>

        <!-- Related Modules -->
        <?php if (!empty($siblings)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0"><i class="bi bi-grid me-2"></i>Modul Terkait</h6>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($siblings as $sib): 
                    $sibIcon = $biIconMap[$sib['icon'] ?? ''] ?? 'bi-grid';
                    $sibKode = strtolower($sib['kode_modul']);
                ?>
                <a href="<?= base_url('koperasi_modul/' . $sibKode) ?>" 
                   class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="bi <?= $sibIcon ?> me-2" style="color:<?= $jenisWarna ?>"></i>
                    <span><?= htmlspecialchars($sib['nama_modul']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0"><i class="bi bi-lightning me-2"></i>Aksi Cepat</h6>
            </div>
            <div class="card-body d-grid gap-2">
                <button class="btn btn-outline-primary btn-sm" onclick="showAddForm()">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Data Baru
                </button>
                <button class="btn btn-outline-success btn-sm" onclick="exportData()">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export ke Excel
                </button>
                <button class="btn btn-outline-info btn-sm" onclick="printReport()">
                    <i class="bi bi-printer me-1"></i> Cetak Laporan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showAddForm() {
    if (typeof KSP !== 'undefined' && KSP.toast) {
        KSP.toast.info('Form tambah data <?= addslashes($modulNama) ?> akan segera tersedia.');
    } else {
        alert('Form tambah data <?= addslashes($modulNama) ?> akan segera tersedia.');
    }
}
function exportData() {
    if (typeof KSP !== 'undefined' && KSP.toast) {
        KSP.toast.info('Export data <?= addslashes($modulNama) ?> akan segera tersedia.');
    } else {
        alert('Export data akan segera tersedia.');
    }
}
function printReport() {
    window.print();
}

// Search filter
document.getElementById('searchInput')?.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#dataTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>
