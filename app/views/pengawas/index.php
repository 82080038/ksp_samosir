<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;

// Get pengawas statistics
$stats = [
    'total_violations' => fetchRow("SELECT COUNT(*) as total FROM logs WHERE type = 'violation'")['total'] ?? 0,
    'pending_violations' => fetchRow("SELECT COUNT(*) as total FROM logs WHERE type = 'violation' AND status = 'pending'")['total'] ?? 0,
    'resolved_violations' => fetchRow("SELECT COUNT(*) as total FROM logs WHERE type = 'violation' AND status = 'resolved'")['total'] ?? 0,
    'total_audits' => fetchRow("SELECT COUNT(*) as total FROM logs WHERE type = 'audit'")['total'] ?? 0,
    'logs_this_month' => fetchRow("SELECT COUNT(*) as total FROM logs WHERE MONTH(tanggal_pelanggaran) = MONTH(CURRENT_DATE) AND YEAR(tanggal_pelanggaran) = YEAR(CURRENT_DATE)")['total'] ?? 0,
    'pending_reports' => fetchRow("SELECT COUNT(*) as total FROM reports WHERE status = 'draft'")['total'] ?? 0
];
?>
<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="pengawas">Dashboard Pengawas</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('pengawas/reports') ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-file-earmark-text"></i> Laporan
            </a>
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<?php if (true): ?>
<!-- Development Mode Notice -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-gear me-2"></i>
    <strong>Dashboard Pengawas:</strong> Sistem monitoring dan pengawasan dengan dependency management terstandarisasi.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Total Pelanggaran</h5>
                        <span class="h2 font-weight-bold mb-0 text-danger"><?= formatAngka($stats['total_violations']) ?></span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Pending</h5>
                        <span class="h2 font-weight-bold mb-0 text-warning"><?= formatAngka($stats['pending_violations']) ?></span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Resolved</h5>
                        <span class="h2 font-weight-bold mb-0 text-success"><?= formatAngka($stats['resolved_violations']) ?></span>
                        <p class="card-text">Log Bulan Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-list fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $stats['pending_reports'] ?></h5>
                        <p class="card-text">Laporan Draft</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-file-alt fa-2x opacity-75"></i>
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
                        <a href="<?= base_url('pengawas/logs') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-list"></i> Lihat Log Aktivitas
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('pengawas/violations') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-exclamation-triangle"></i> Kelola Pelanggaran
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('pengawas/sanctions') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-gavel"></i> Referensi Sanksi
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('pengawas/reports') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-file-alt"></i> Laporan Pengawas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Violations -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Pelanggaran Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_violations)): ?>
                    <p class="text-muted">Belum ada pelanggaran tercatat.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_violations as $violation): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($violation['user_name']) ?></h6>
                                    <small class="text-muted">
                                        <span class="badge bg-<?= $violation['status'] === 'investigasi' ? 'warning' : ($violation['status'] === 'diputuskan' ? 'info' : 'success') ?>">
                                            <?= htmlspecialchars($violation['status']) ?>
                                        </span>
                                    </small>
                                </div>
                                <p class="mb-1"><?= htmlspecialchars(substr($violation['jenis_pelanggaran'], 0, 50)) ?>...</p>
                                <small class="text-muted">Tanggal: <?= htmlspecialchars(formatDate($violation['tanggal_pelanggaran'], 'd M Y')) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Laporan Pending</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pending_reports)): ?>
                    <p class="text-muted">Tidak ada laporan pending.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pending_reports as $report): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($report['judul']) ?></h6>
                                    <small class="text-muted">Draft</small>
                                </div>
                                <p class="mb-1">Oleh: <?= htmlspecialchars($report['created_by_name']) ?></p>
                                <small class="text-muted">Dibuat: <?= htmlspecialchars(formatDate($report['created_at'], 'd M Y')) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
