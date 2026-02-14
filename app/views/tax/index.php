<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Dashboard Pajak</h2>
        <p class="text-muted">Kelola perhitungan dan pelaporan pajak koperasi</p>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- Tax Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">Rp <?= formatCurrency($stats['total_tax_paid_year']) ?></h5>
                        <p class="card-text">Total Pajak Dibayar Tahun Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $stats['employees_with_tax'] ?></h5>
                        <p class="card-text">Karyawan dengan Pajak</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $stats['pending_tax_filings'] ?></h5>
                        <p class="card-text">Pelaporan Pending</p>
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
                        <h5 class="card-title mb-0"><?= number_format($stats['tax_compliance_rate'], 1) ?>%</h5>
                        <p class="card-text">Tingkat Kepatuhan Pajak</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-percentage fa-2x opacity-75"></i>
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
                        <a href="<?= base_url('tax/calculatePPh21') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-calculator"></i> Hitung PPh 21
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('tax/calculatePPh23') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-calculator"></i> Hitung PPh 23
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('tax/calculatePPh25') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-calculator"></i> Hitung PPh 25
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('tax/compliance') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-check-circle"></i> Status Kepatuhan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Tax Deadlines -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Deadline Pajak Mendatang</h5>
            </div>
            <div class="card-body">
                <?php if (empty($upcoming_deadlines)): ?>
                    <p class="text-muted">Tidak ada deadline mendatang.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcoming_deadlines as $deadline): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($deadline['type']) ?></h6>
                                    <small class="text-muted">
                                        <span class="badge bg-warning">
                                            <?= htmlspecialchars(formatDate($deadline['deadline'], 'd M Y')) ?>
                                        </span>
                                    </small>
                                </div>
                                <p class="mb-1"><?= htmlspecialchars($deadline['description']) ?></p>
                                <small class="text-muted">
                                    <?php
                                    $days_left = ceil((strtotime($deadline['deadline']) - time()) / (60 * 60 * 24));
                                    echo $days_left . ' hari lagi';
                                    ?>
                                </small>
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
                <h5>Ringkasan Pajak Tahun Ini</h5>
            </div>
            <div class="card-body">
                <?php
                $current_year = date('Y');
                $yearly_tax = fetchRow("SELECT COALESCE(SUM(tax_amount), 0) as total FROM tax_filings WHERE YEAR(filing_date) = ?", [$current_year], 'i')['total'];
                $monthly_tax = fetchRow("SELECT COALESCE(SUM(tax), 0) as total FROM payrolls WHERE YEAR(processed_at) = ?", [$current_year], 'i')['total'];
                $withholding_tax = fetchRow("SELECT COALESCE(SUM(tax_amount), 0) as total FROM withholding_tax WHERE YEAR(transaction_date) = ?", [$current_year], 'i')['total'];
                ?>
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-primary">Rp <?= formatCurrency($yearly_tax) ?></h4>
                        <small>PPh Badan</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-success">Rp <?= formatCurrency($monthly_tax) ?></h4>
                        <small>PPh 21</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info">Rp <?= formatCurrency($withholding_tax) ?></h4>
                        <small>PPh Potong</small>
                    </div>
                </div>

                <hr>

                <div class="mt-3">
                    <a href="<?= base_url('tax/taxReports?type=annual&period=' . $current_year) ?>" class="btn btn-outline-primary btn-sm">
                        Lihat Laporan Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tax Compliance Status -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Status Kepatuhan Pajak</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <i class="fas fa-file-invoice fa-2x text-primary mb-2"></i>
                            <h6>PPh 21 Bulanan</h6>
                            <span class="badge bg-success">Compliant</span>
                            <br><small class="text-muted">Dilaporkan bulan ini</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <i class="fas fa-hand-holding-usd fa-2x text-success mb-2"></i>
                            <h6>PPh 23 Potong</h6>
                            <span class="badge bg-success">Compliant</span>
                            <br><small class="text-muted">Dipotong otomatis</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <i class="fas fa-building fa-2x text-warning mb-2"></i>
                            <h6>PPh 25 Angsuran</h6>
                            <span class="badge bg-warning">Due Soon</span>
                            <br><small class="text-muted">Jatuh tempo bulan depan</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <i class="fas fa-calendar-check fa-2x text-info mb-2"></i>
                            <h6>SPT Tahunan</h6>
                            <span class="badge bg-info">On Track</span>
                            <br><small class="text-muted">Deadline April 2025</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
