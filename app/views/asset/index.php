<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Dashboard Manajemen Asset</h2>
        <p class="text-muted">Kelola asset tetap, depresiasi, dan maintenance</p>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- Asset Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['total_assets'] ?></h5>
                        <p class="card-text">Total Asset</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-cubes fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">Rp <?= formatCurrency($stats['total_asset_value']) ?></h5>
                        <p class="card-text">Total Nilai Asset</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">Rp <?= formatCurrency($stats['total_depreciation']) ?></h5>
                        <p class="card-text">Total Depresiasi</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">Rp <?= formatCurrency($stats['net_book_value']) ?></h5>
                        <p class="card-text">Nilai Buku Bersih</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calculator fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="<?= base_url('asset/assets') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-list"></i> Lihat Asset
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('asset/addAsset') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-plus"></i> Tambah Asset
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('asset/depreciationReport') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-file-alt"></i> Laporan Depresiasi
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-info btn-block" onclick="showMaintenanceAlert()">
                            <i class="fas fa-tools"></i> Maintenance Alert
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Asset Categories and Maintenance Alerts -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Asset per Kategori</h5>
            </div>
            <div class="card-body">
                <?php
                $category_stats = fetchAll("SELECT category, COUNT(*) as count, SUM(acquisition_cost) as total_value FROM fixed_assets GROUP BY category ORDER BY total_value DESC");
                ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($category_stats as $stat): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars(ucfirst($stat['category'])) ?></strong></td>
                                    <td><?= $stat['count'] ?></td>
                                    <td>Rp <?= formatCurrency($stat['total_value']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Alert Maintenance</h5>
            </div>
            <div class="card-body">
                <?php if ($stats['assets_needing_maintenance'] > 0): ?>
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Perlu Perhatian</h6>
                        <p>Ada <strong><?= $stats['assets_needing_maintenance'] ?></strong> asset yang memerlukan maintenance atau dalam kondisi buruk.</p>
                        <a href="<?= base_url('asset/assets?filter=maintenance') ?>" class="btn btn-sm btn-warning">Lihat Asset</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle"></i> Semua Asset Dalam Kondisi Baik</h6>
                        <p>Semua asset dalam kondisi baik dan terjadwal maintenance.</p>
                    </div>
                <?php endif; ?>

                <hr>

                <h6>Asset Terbaru</h6>
                <?php if (empty($recent_assets)): ?>
                    <p class="text-muted small">Belum ada asset tercatat.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_assets as $asset): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($asset['asset_name']) ?></h6>
                                    <small>Rp <?= formatCurrency($asset['acquisition_cost']) ?></small>
                                </div>
                                <p class="mb-1">Kode: <code><?= htmlspecialchars($asset['asset_code']) ?></code></p>
                                <small class="text-muted">
                                    Ditambahkan: <?= htmlspecialchars(formatDate($asset['created_at'], 'd M Y')) ?> |
                                    Oleh: <?= htmlspecialchars($asset['created_by_name']) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Depreciation Overview -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Ringkasan Depresiasi Bulan Ini</h5>
            </div>
            <div class="card-body">
                <?php
                $current_month = date('Y-m');
                $monthly_depreciation = fetchRow("SELECT COALESCE(SUM(depreciation_amount), 0) as total FROM asset_depreciation WHERE DATE_FORMAT(depreciation_date, '%Y-%m') = ?", [$current_month], 's')['total'];
                $monthly_assets = fetchRow("SELECT COUNT(DISTINCT asset_id) as total FROM asset_depreciation WHERE DATE_FORMAT(depreciation_date, '%Y-%m') = ?", [$current_month], 's')['total'];
                ?>
                <div class="row text-center">
                    <div class="col-md-4">
                        <h4 class="text-primary">Rp <?= formatCurrency($monthly_depreciation) ?></h4>
                        <small>Total Depresiasi Bulan Ini</small>
                    </div>
                    <div class="col-md-4">
                        <h4 class="text-success"><?= $monthly_assets ?></h4>
                        <small>Asset yang Didepresiasi</small>
                    </div>
                    <div class="col-md-4">
                        <h4 class="text-info">Rp <?= formatCurrency($monthly_depreciation / max($monthly_assets, 1)) ?></h4>
                        <small>Rata-rata per Asset</small>
                    </div>
                </div>

                <hr>

                <div class="mt-3">
                    <a href="<?= base_url('asset/depreciationReport?period=' . $current_month) ?>" class="btn btn-outline-primary btn-sm">
                        Lihat Laporan Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showMaintenanceAlert() {
    // Show assets needing maintenance
    window.location.href = '<?= base_url('asset/assets?filter=maintenance') ?>';
}
</script>
