<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Dashboard Payroll</h2>
        <p class="text-muted">Kelola karyawan dan penggajian</p>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['total_employees'] ?></h5>
                        <p class="card-text">Total Karyawan Aktif</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0">Rp <?= formatCurrency($stats['total_salary_paid_month']) ?></h5>
                        <p class="card-text">Total Gaji Dibayar Bulan Ini</p>
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
                        <h5 class="card-title mb-0"><?= $stats['pending_payrolls'] ?></h5>
                        <p class="card-text">Karyawan Pending Payroll</p>
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
                        <h5 class="card-title mb-0"><?= $stats['total_payrolls_this_month'] ?></h5>
                        <p class="card-text">Payroll Diproses Bulan Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-file-invoice-dollar fa-2x opacity-75"></i>
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
                        <a href="<?= base_url('payroll/employees') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-users"></i> Kelola Karyawan
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('payroll/createEmployee') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-user-plus"></i> Tambah Karyawan
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('payroll/processPayroll') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-calculator"></i> Proses Payroll
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('payroll/payrollHistory') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-history"></i> Riwayat Payroll
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Payrolls -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Payroll Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_payrolls)): ?>
                    <p class="text-muted">Belum ada data payroll.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_payrolls as $payroll): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($payroll['employee_id']) ?> - <?= htmlspecialchars($payroll['full_name']) ?></h6>
                                    <small class="text-muted">
                                        <span class="badge bg-success">Diproses</span>
                                    </small>
                                </div>
                                <p class="mb-1">
                                    Periode: <?= htmlspecialchars($payroll['period']) ?> |
                                    Gaji Bersih: Rp <?= formatCurrency($payroll['net_salary']) ?>
                                </p>
                                <small class="text-muted">
                                    Diproses: <?= htmlspecialchars(formatDate($payroll['processed_at'], 'd M Y H:i')) ?>
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
                <h5>Ringkasan Gaji Bulan Ini</h5>
            </div>
            <div class="card-body">
                <?php
                $monthly_summary = fetchRow("SELECT COUNT(*) as total_payrolls, SUM(net_salary) as total_salary, AVG(net_salary) as avg_salary FROM payrolls WHERE MONTH(processed_at) = MONTH(CURRENT_DATE) AND YEAR(processed_at) = YEAR(CURRENT_DATE)");
                ?>
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-primary"><?= $monthly_summary['total_payrolls'] ?? 0 ?></h4>
                        <small>Karyawan</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-success">Rp <?= formatCurrency($monthly_summary['total_salary'] ?? 0) ?></h4>
                        <small>Total Gaji</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info">Rp <?= formatCurrency($monthly_summary['avg_salary'] ?? 0) ?></h4>
                        <small>Rata-rata</small>
                    </div>
                </div>

                <hr>

                <div class="mt-3">
                    <a href="<?= base_url('payroll/payrollReport?period=' . date('Y-m')) ?>" class="btn btn-outline-primary btn-sm">
                        Lihat Laporan Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Department Summary -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Ringkasan per Departemen</h5>
            </div>
            <div class="card-body">
                <?php
                $dept_summary = fetchAll("SELECT department, COUNT(*) as employee_count, SUM(basic_salary) as total_salary FROM employees WHERE status = 'active' GROUP BY department ORDER BY total_salary DESC");
                ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Departemen</th>
                                <th>Jumlah Karyawan</th>
                                <th>Total Gaji Pokok</th>
                                <th>Rata-rata Gaji</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dept_summary as $dept): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($dept['department']) ?></strong></td>
                                    <td><?= $dept['employee_count'] ?></td>
                                    <td>Rp <?= formatCurrency($dept['total_salary']) ?></td>
                                    <td>Rp <?= formatCurrency($dept['total_salary'] / $dept['employee_count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
