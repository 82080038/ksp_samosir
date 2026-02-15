<?php
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
$recent_distributions = $recent_distributions ?? [];
if (file_exists(__DIR__ . '/../../../app/helpers/FormatHelper.php')) {
    require_once __DIR__ . '/../../../app/helpers/FormatHelper.php';
}
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="shu">SHU (Sisa Hasil Usaha)</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('shu/calculate') ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-calculator"></i> Hitung SHU
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<p class="text-muted mb-3">Kelola Sisa Hasil Usaha (SHU) dan pembagian dividen</p>

<!-- Flash Messages -->
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="<?= base_url('shu/calculate') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-calculator"></i> Hitung SHU
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('shu/createDistribution') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-plus"></i> Buat Distribusi
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('shu/distribute') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-list"></i> Lihat Distribusi
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('shu/reports') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-file-alt"></i> Laporan SHU
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Distributions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                
            </div>
            <div class="card-body">
                <?php if (empty($recent_distributions)): ?>
                    <p class="text-muted">Belum ada distribusi keuntungan.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>Tanggal Distribusi</th>
                                    <th>Total Keuntungan</th>
                                    <th>SHU Anggota</th>
                                    <th>Dividen Investor</th>
                                    <th>Cadangan</th>
                                    <th>Status</th>
                                    <th>Disetujui Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_distributions as $dist): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($dist['periode']) ?></strong></td>
                                        <td><?= htmlspecialchars(formatDate($dist['tanggal_distribusi'], 'd M Y')) ?></td>
                                        <td>Rp <?= formatCurrency($dist['total_keuntungan']) ?></td>
                                        <td>Rp <?= formatCurrency($dist['shu_anggota']) ?></td>
                                        <td>Rp <?= formatCurrency($dist['dividen_investor']) ?></td>
                                        <td>Rp <?= formatCurrency($dist['cadangan_koperasi']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $dist['status'] === 'approved' ? 'success' : 'warning' ?>">
                                                <?= htmlspecialchars($dist['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($dist['approved_by_name'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>