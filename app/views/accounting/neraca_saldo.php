<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="accounting-neraca-saldo">Neraca Saldo</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <input type="date" class="form-control" id="asOfDate" value="<?= $as_of_date ?>" onchange="changeDate()">
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportTrialBalance()">
            <i class="bi bi-download"></i> Export
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Trial Balance as of <?= formatDate($as_of_date) ?></h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Tipe Akun</th>
                        <th class="text-end">Total Debet</th>
                        <th class="text-end">Total Kredit</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($neracaSaldo)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data neraca saldo</td>
                    </tr>
                    <?php else: ?>
                        <?php
                        $totalDebet = 0;
                        $totalKredit = 0;
                        $totalSaldo = 0;
                        foreach ($neracaSaldo as $row):
                            $totalDebet += $row['total_debet'];
                            $totalKredit += $row['total_kredit'];
                            $totalSaldo += $row['saldo'];
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['kode_perkiraan']) ?></strong></td>
                            <td><?= htmlspecialchars($row['nama_perkiraan']) ?></td>
                            <td>
                                <span class="badge bg-<?= $row['tipe_akun'] === 'Aktiva' ? 'primary' : ($row['tipe_akun'] === 'Kewajiban' ? 'warning' : 'success') ?>">
                                    <?= htmlspecialchars($row['tipe_akun']) ?>
                                </span>
                            </td>
                            <td class="text-end"><?= formatCurrency($row['total_debet']) ?></td>
                            <td class="text-end"><?= formatCurrency($row['total_kredit']) ?></td>
                            <td class="text-end fw-bold <?= $row['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= formatCurrency($row['saldo']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr class="fw-bold">
                        <td colspan="3">TOTAL</td>
                        <td class="text-end"><?= formatCurrency($totalDebet ?? 0) ?></td>
                        <td class="text-end"><?= formatCurrency($totalKredit ?? 0) ?></td>
                        <td class="text-end <?= ($totalSaldo ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= formatCurrency($totalSaldo ?? 0) ?>
                        </td>
                    </tr>
                    <tr class="table-warning">
                        <td colspan="5" class="text-end fw-bold">BALANCE CHECK</td>
                        <td class="text-end fw-bold <?= abs(($totalDebet ?? 0) - ($totalKredit ?? 0)) < 0.01 ? 'text-success' : 'text-danger' ?>">
                            <?= abs(($totalDebet ?? 0) - ($totalKredit ?? 0)) < 0.01 ? 'BALANCED' : 'NOT BALANCED' ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Summary by Account Type</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Account Type</th>
                                <th class="text-end">Count</th>
                                <th class="text-end">Total Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $summary = [];
                            foreach ($neracaSaldo as $row) {
                                $type = $row['tipe_akun'];
                                if (!isset($summary[$type])) {
                                    $summary[$type] = ['count' => 0, 'balance' => 0];
                                }
                                $summary[$type]['count']++;
                                $summary[$type]['balance'] += $row['saldo'];
                            }
                            foreach ($summary as $type => $data):
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($type) ?></strong></td>
                                <td class="text-end"><?= $data['count'] ?></td>
                                <td class="text-end fw-bold <?= $data['balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= formatCurrency($data['balance']) ?>
                                </td>
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
                <h6 class="card-title mb-0">Balance Sheet Position</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <h4 class="text-primary mb-1">Assets</h4>
                            <h5 class="mb-0">
                                <?= formatCurrency(($summary['Aktiva']['balance'] ?? 0) + ($summary['Aktiva Tetap']['balance'] ?? 0)) ?>
                            </h5>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <h4 class="text-success mb-1">Equity</h4>
                            <h5 class="mb-0">
                                <?= formatCurrency(($summary['Ekuitas']['balance'] ?? 0) + ($summary['Kewajiban']['balance'] ?? 0)) ?>
                            </h5>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <span class="badge fs-6 p-2 
                        <?= abs(($summary['Aktiva']['balance'] ?? 0) + ($summary['Aktiva Tetap']['balance'] ?? 0) - 
                               (($summary['Ekuitas']['balance'] ?? 0) + ($summary['Kewajiban']['balance'] ?? 0))) < 1 ? 
                           'bg-success' : 'bg-danger' ?>">
                        Balance Sheet: 
                        <?= abs(($summary['Aktiva']['balance'] ?? 0) + ($summary['Aktiva Tetap']['balance'] ?? 0) - 
                               (($summary['Ekuitas']['balance'] ?? 0) + ($summary['Kewajiban']['balance'] ?? 0))) < 1 ? 
                           'BALANCED' : 'OUT OF BALANCE' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changeDate() {
    const newDate = document.getElementById('asOfDate').value;
    window.location.href = `<?= base_url('accounting/neracaSaldo') ?>?as_of_date=${newDate}`;
}

function exportTrialBalance() {
    const asOfDate = document.getElementById('asOfDate').value;
    window.open(`<?= base_url('accounting/exportNeracaSaldo') ?>?as_of_date=${asOfDate}`, '_blank');
}
</script>
