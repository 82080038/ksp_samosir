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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="accounting-neraca">Neraca</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <input type="date" class="form-control" id="asOfDate" value="<?= $as_of_date ?>" onchange="changeDate()">
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportBalanceSheet()">
            <i class="bi bi-download"></i> Export
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Balance Sheet as of <?= formatDate($as_of_date) ?></h5>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- ASSETS -->
            <div class="col-md-6">
                <h4 class="text-primary mb-3">AKTIVA</h4>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <?php
                            $aktivaTotal = 0;
                            foreach ($aktiva as $item):
                                $aktivaTotal += $item['saldo'];
                            ?>
                            <tr>
                                <td class="text-start ps-3"><?= htmlspecialchars($item['nama']) ?></td>
                                <td class="text-end pe-3 fw-bold"><?= formatCurrency($item['saldo']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="border-top border-2">
                                <td class="text-start ps-3 fw-bold">TOTAL AKTIVA</td>
                                <td class="text-end pe-3 fw-bold text-primary border-top border-2"><?= formatCurrency($aktivaTotal) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- LIABILITIES & EQUITY -->
            <div class="col-md-6">
                <h4 class="text-success mb-3">KEWAJIBAN & EKUITAS</h4>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <?php
                            $passivaTotal = 0;
                            foreach ($passiva as $item):
                                $passivaTotal += $item['saldo'];
                            ?>
                            <tr>
                                <td class="text-start ps-3"><?= htmlspecialchars($item['nama']) ?></td>
                                <td class="text-end pe-3 fw-bold"><?= formatCurrency($item['saldo']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Ekuitas -->
                            <?php foreach ($ekuitas as $item): ?>
                            <tr>
                                <td class="text-start ps-3"><?= htmlspecialchars($item['nama']) ?></td>
                                <td class="text-end pe-3 fw-bold"><?= formatCurrency($item['saldo']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <tr class="border-top border-2">
                                <td class="text-start ps-3 fw-bold">TOTAL KEWAJIBAN & EKUITAS</td>
                                <td class="text-end pe-3 fw-bold text-success border-top border-2">
                                    <?= formatCurrency($total_passiva + $total_ekuitas) ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Balance Check -->
        <hr>
        <div class="row">
            <div class="col-12">
                <div class="alert 
                    <?= abs($total_aktiva - ($total_passiva + $total_ekuitas)) < 1 ? 'alert-success' : 'alert-danger' ?> 
                    text-center">
                    <h5 class="mb-0">
                        <i class="bi <?= abs($total_aktiva - ($total_passiva + $total_ekuitas)) < 1 ? 'bi-check-circle' : 'bi-exclamation-triangle' ?> me-2"></i>
                        Balance Check: 
                        <?= abs($total_aktiva - ($total_passiva + $total_ekuitas)) < 1 ? 'BALANCED' : 'OUT OF BALANCE' ?>
                        <br>
                        <small class="text-muted">
                            Assets: <?= formatCurrency($total_aktiva) ?> | 
                            Liabilities & Equity: <?= formatCurrency($total_passiva + $total_ekuitas) ?> | 
                            Difference: <?= formatCurrency(abs($total_aktiva - ($total_passiva + $total_ekuitas))) ?>
                        </small>
                    </h5>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Ratios -->

<?php if (true): ?>
<!-- Development Mode Notice -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-gear me-2"></i>
    <strong>Mode Development:</strong> Semua fitur dapat diakses tanpa autentikasi.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Financial Ratios</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="p-2 text-center">
                            <small class="text-muted d-block">Debt to Equity</small>
                            <span class="h5 text-primary">
                                <?php
                                $debtToEquity = $total_ekuitas > 0 ? ($total_passiva / $total_ekuitas) : 0;
                                echo number_format($debtToEquity, 2);
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 text-center">
                            <small class="text-muted d-block">Equity Ratio</small>
                            <span class="h5 text-success">
                                <?php
                                $equityRatio = $total_aktiva > 0 ? ($total_ekuitas / $total_aktiva) * 100 : 0;
                                echo number_format($equityRatio, 1) . '%';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Asset Composition</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $totalAssets = $total_aktiva;
                    $currentAssets = array_sum(array_column($aktiva, 'saldo'));
                    $currentAssetRatio = $totalAssets > 0 ? ($currentAssets / $totalAssets) * 100 : 0;
                    ?>
                    <div class="col-6">
                        <div class="p-2 text-center">
                            <small class="text-muted d-block">Current Assets</small>
                            <span class="h5 text-info">
                                <?= number_format($currentAssetRatio, 1) ?>%
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 text-center">
                            <small class="text-muted d-block">Fixed Assets</small>
                            <span class="h5 text-warning">
                                <?= number_format(100 - $currentAssetRatio, 1) ?>%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changeDate() {
    const newDate = document.getElementById('asOfDate').value;
    window.location.href = `<?= base_url('accounting/neraca') ?>?as_of_date=${newDate}`;
}

function exportBalanceSheet() {
    const asOfDate = document.getElementById('asOfDate').value;
    window.open(`<?= base_url('accounting/exportNeraca') ?>?as_of_date=${asOfDate}`, '_blank');
}
</script>
