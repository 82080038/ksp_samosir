<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Dashboard Pengawas</h2>
        <p class="text-muted">Pantau aktivitas dan kelola pengawasan koperasi</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['total_violations'] ?></h5>
                        <p class="card-text">Total Pelanggaran</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $stats['pending_violations'] ?></h5>
                        <p class="card-text">Pelanggaran Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $stats['logs_this_month'] ?></h5>
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
