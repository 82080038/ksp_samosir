<?php
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;

$all_jenis = $all_jenis ?? [];
$all_moduls = $all_moduls ?? [];
$page_title = $page_title ?? 'Modul Koperasi';

// Group modules by kategori
$grouped = [];
foreach ($all_moduls as $m) {
    $kat = $m['kategori'] ?? 'other';
    $grouped[$kat][] = $m;
}

// Map jenis by kode
$jenisMap = [];
foreach ($all_jenis as $j) {
    $jenisMap[strtolower($j['kode_jenis'])] = $j;
}

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
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="koperasi-modul">
        <i class="bi bi-grid-3x3-gap me-2"></i><?= htmlspecialchars($page_title) ?>
    </h1>
</div>

<p class="text-muted mb-4">Pilih jenis koperasi untuk melihat modul spesifik yang tersedia.</p>

<?php foreach ($all_jenis as $j):
    $kode = strtolower($j['kode_jenis']);
    $moduls = $grouped[$kode] ?? [];
    if (empty($moduls)) continue;
    $warna = $j['warna_tema'] ?? '#0d6efd';
    $jenisIcon = $biIconMap[$j['icon'] ?? ''] ?? 'bi-grid';
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex align-items-center">
        <i class="bi <?= $jenisIcon ?> me-2" style="color:<?= $warna ?>;font-size:1.2rem"></i>
        <h5 class="mb-0"><?= htmlspecialchars($j['nama_jenis']) ?></h5>
        <small class="text-muted ms-2">(<?= htmlspecialchars($j['kode_jenis']) ?>)</small>
        <span class="badge ms-auto" style="background:<?= $warna ?>"><?= count($moduls) ?> modul</span>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3" style="font-size:0.9rem"><?= htmlspecialchars($j['deskripsi'] ?? '') ?></p>
        <div class="row g-2">
            <?php foreach ($moduls as $m):
                $mIcon = $biIconMap[$m['icon'] ?? ''] ?? 'bi-grid';
                $mKode = strtolower($m['kode_modul']);
            ?>
            <div class="col-md-3 col-sm-6">
                <a href="<?= base_url('koperasi_modul/' . $mKode) ?>" class="card border h-100 text-decoration-none" style="transition:all 0.2s">
                    <div class="card-body text-center py-3">
                        <i class="bi <?= $mIcon ?> d-block mb-2" style="font-size:1.5rem;color:<?= $warna ?>"></i>
                        <div class="fw-semibold" style="font-size:0.85rem;color:#333"><?= htmlspecialchars($m['nama_modul']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($m['deskripsi'] ?? '') ?></small>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<style>
.card a.card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important; }
</style>
