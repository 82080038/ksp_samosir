<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Dashboard SHU & Dividen</h2>
        <p class="text-muted">Kelola Sisa Hasil Usaha (SHU) dan pembagian dividen</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">Rp <?= formatCurrency($stats['total_shu_distributed']) ?></h5>
                        <p class="card-text">Total SHU Dibagikan</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-coins fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0">Rp <?= formatCurrency($stats['total_dividends_distributed']) ?></h5>
                        <p class="card-text">Total Dividen Dibagikan</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $stats['pending_member_payments'] ?></h5>
                        <p class="card-text">Pembayaran SHU Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['pending_investor_payments'] ?></h5>
                        <p class="card-text">Pembayaran Dividen Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                <h5>Distribusi Terbaru</h5>
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
