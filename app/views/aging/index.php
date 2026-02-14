<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Dashboard Aging Reports</h2>
        <p class="text-muted">Analisis umur piutang dan hutang</p>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- Aging Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">Rp <?= formatCurrency($receivables_aging['aging_totals']['total']) ?></h5>
                        <p class="card-text">Total Piutang</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-arrow-up fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0">Rp <?= formatCurrency($receivables_aging['total_overdue']) ?></h5>
                        <p class="card-text">Piutang Overdue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0">Rp <?= formatCurrency($payables_aging['aging_totals']['total']) ?></h5>
                        <p class="card-text">Total Hutang</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-arrow-down fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0">Rp <?= formatCurrency($payables_aging['total_overdue']) ?></h5>
                        <p class="card-text">Hutang Overdue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
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
                        <a href="<?= base_url('aging/receivablesAging') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-chart-line"></i> Piutang Aging
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('aging/payablesAging') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-chart-bar"></i> Hutang Aging
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('aging/exportAging?type=receivables') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-download"></i> Export Piutang
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('aging/exportAging?type=payables') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-download"></i> Export Hutang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receivables Aging Summary -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Ringkasan Piutang Aging</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Current</th>
                                <th>1-30 Days</th>
                                <th>31-60 Days</th>
                                <th>61-90 Days</th>
                                <th>>90 Days</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $receivables_limited = array_slice($receivables_aging['aging_summary'], 0, 5);
                            foreach ($receivables_limited as $customer):
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($customer['name']) ?></strong></td>
                                    <td>Rp <?= formatCurrency($customer['current']) ?></td>
                                    <td>Rp <?= formatCurrency($customer['1_30_days']) ?></td>
                                    <td>Rp <?= formatCurrency($customer['31_60_days']) ?></td>
                                    <td>Rp <?= formatCurrency($customer['61_90_days']) ?></td>
                                    <td>Rp <?= formatCurrency($customer['over_90_days']) ?></td>
                                    <td><strong>Rp <?= formatCurrency($customer['total']) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-primary">
                                <td><strong>Total</strong></td>
                                <td><strong>Rp <?= formatCurrency($receivables_aging['aging_totals']['current']) ?></strong></td>
                                <td><strong>Rp <?= formatCurrency($receivables_aging['aging_totals']['1_30_days']) ?></strong></td>
                                <td><strong>Rp <?= formatCurrency($receivables_aging['aging_totals']['31_60_days']) ?></strong></td>
                                <td><strong>Rp <?= formatCurrency($receivables_aging['aging_totals']['61_90_days']) ?></strong></td>
                                <td><strong>Rp <?= formatCurrency($receivables_aging['aging_totals']['over_90_days']) ?></strong></td>
                                <td><strong>Rp <?= formatCurrency($receivables_aging['aging_totals']['total']) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <a href="<?= base_url('aging/receivablesAging') ?>" class="btn btn-outline-primary btn-sm">Lihat Detail Lengkap</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Ringkasan Hutang Aging</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Current</th>
                                <th>1-30 Days</th>
                                <th>31-60 Days</th>
                                <th>61-90 Days</th>
                                <th>>90 Days</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $payables_limited = array_slice($payables_aging['aging_summary'], 0, 5);
                            foreach ($payables_limited as $supplier):
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($supplier['name']) ?></strong></td>
                                    <td>Rp <?= formatCurrency($supplier['current']) ?></td>
                                    <td>Rp <?= formatCurrency($supplier['1_30_days']) ?></td>
                                    <td>Rp <?= formatCurrency($supplier['31_60_days']) ?></td>
                                    <td>Rp <?= formatCurrency($supplier['61_90_days']) ?></td>
                                    <td>Rp <?= formatCurrency($supplier['over_90_days']) ?></td>
                                    <td><strong>Rp <?= formatCurrency($supplier['total']) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-success">
                                <td><strong>Total</strong></td>
                                <td><strong>Rp <?= formatCurrency($payables_aging['aging_totals']['current']) ?></strong></td>
                                <td><strong>Rp <?= formatCurrency($payables_aging['aging_totals']['1_30_days']) ?></strong></td>
                                <td><strong>Rp <?= formatCurrency($payables_aging['aging_totals']['31_60_days']) ?></strong></td>
                                <td><strong>Rp <?= formatCurrency($payables_aging['aging_totals']['61_90_days']) ?></strong></td>
                                <td><strong>Rp <?= formatCurrency($payables_aging['aging_totals']['over_90_days']) ?></strong></td>
                                <td><strong>Rp <?= formatCurrency($payables_aging['aging_totals']['total']) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <a href="<?= base_url('aging/payablesAging') ?>" class="btn btn-outline-success btn-sm">Lihat Detail Lengkap</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Aging Analysis -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Analisis Aging</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Piutang (Accounts Receivable)</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" style="width: <?= $receivables_aging['aging_totals']['total'] > 0 ? ($receivables_aging['aging_totals']['current'] / $receivables_aging['aging_totals']['total'] * 100) : 0 ?>%"></div>
                            <div class="progress-bar bg-warning" style="width: <?= $receivables_aging['aging_totals']['total'] > 0 ? ($receivables_aging['aging_totals']['1_30_days'] / $receivables_aging['aging_totals']['total'] * 100) : 0 ?>%"></div>
                            <div class="progress-bar bg-danger" style="width: <?= $receivables_aging['aging_totals']['total'] > 0 ? (($receivables_aging['aging_totals']['31_60_days'] + $receivables_aging['aging_totals']['61_90_days'] + $receivables_aging['aging_totals']['over_90_days']) / $receivables_aging['aging_totals']['total'] * 100) : 0 ?>%"></div>
                        </div>
                        <small class="text-muted">
                            Hijau: Current | Kuning: 1-30 hari | Merah: >30 hari overdue
                        </small>
                    </div>
                    <div class="col-md-6">
                        <h6>Hutang (Accounts Payable)</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" style="width: <?= $payables_aging['aging_totals']['total'] > 0 ? ($payables_aging['aging_totals']['current'] / $payables_aging['aging_totals']['total'] * 100) : 0 ?>%"></div>
                            <div class="progress-bar bg-warning" style="width: <?= $payables_aging['aging_totals']['total'] > 0 ? ($payables_aging['aging_totals']['1_30_days'] / $payables_aging['aging_totals']['total'] * 100) : 0 ?>%"></div>
                            <div class="progress-bar bg-danger" style="width: <?= $payables_aging['aging_totals']['total'] > 0 ? (($payables_aging['aging_totals']['31_60_days'] + $payables_aging['aging_totals']['61_90_days'] + $payables_aging['aging_totals']['over_90_days']) / $payables_aging['aging_totals']['total'] * 100) : 0 ?>%"></div>
                        </div>
                        <small class="text-muted">
                            Hijau: Current | Kuning: 1-30 hari | Merah: >30 hari overdue
                        </small>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6>Kolektibilitas Piutang</h6>
                        <?php
                        $receivables_total = $receivables_aging['aging_totals']['total'];
                        $current_ratio = $receivables_total > 0 ? ($receivables_aging['aging_totals']['current'] / $receivables_total * 100) : 0;
                        $overdue_ratio = $receivables_total > 0 ? ($receivables_aging['total_overdue'] / $receivables_total * 100) : 0;
                        ?>
                        <p>Current: <strong><?= number_format($current_ratio, 1) ?>%</strong></p>
                        <p>Overdue: <strong><?= number_format($overdue_ratio, 1) ?>%</strong></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Likuiditas Hutang</h6>
                        <?php
                        $payables_total = $payables_aging['aging_totals']['total'];
                        $current_ratio = $payables_total > 0 ? ($payables_aging['aging_totals']['current'] / $payables_total * 100) : 0;
                        $overdue_ratio = $payables_total > 0 ? ($payables_aging['total_overdue'] / $payables_total * 100) : 0;
                        ?>
                        <p>Current: <strong><?= number_format($current_ratio, 1) ?>%</strong></p>
                        <p>Overdue: <strong><?= number_format($overdue_ratio, 1) ?>%</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
