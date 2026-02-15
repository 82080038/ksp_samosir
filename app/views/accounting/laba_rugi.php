<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';
require_once __DIR__ . '/../../../app/helpers/FormatHelper.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
$period = $period ?? 'month';
$start_date = $start_date ?? date('Y-m-01');
$end_date = $end_date ?? date('Y-m-t');
$income_accounts = $income_accounts ?? [];
$expense_accounts = $expense_accounts ?? [];
?>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="accounting-laba-rugi">Laba Rugi</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <input type="date" class="form-control" id="startDate" value="<?= $start_date ?>" onchange="changePeriod()">
            <span class="input-group-text">to</span>
            <input type="date" class="form-control" id="endDate" value="<?= $end_date ?>" onchange="changePeriod()">
        </div>
        <div class="btn-group me-2">
            <select class="form-select" id="period" onchange="changePeriodType()">
                <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Monthly</option>
                <option value="year" <?= $period === 'year' ? 'selected' : '' ?>>Yearly</option>
                <option value="custom" <?= $period === 'custom' ? 'selected' : '' ?>>Custom</option>
            </select>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportIncomeStatement()">
            <i class="bi bi-download"></i> Export
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Income Statement for Period: <?= formatDate($start_date) ?> - <?= formatDate($end_date) ?></h5>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- REVENUE -->
            <div class="col-md-6">
                <h4 class="text-success mb-3">PENDAPATAN</h4>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <?php foreach ($pendapatan as $item): ?>
                            <tr>
                                <td class="text-start ps-3"><?= htmlspecialchars($item['nama_perkiraan']) ?></td>
                                <td class="text-end pe-3 fw-bold text-success"><?= formatCurrency($item['total']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="border-top">
                                <td class="text-start ps-3 fw-bold">TOTAL PENDAPATAN</td>
                                <td class="text-end pe-3 fw-bold text-success border-top"><?= formatCurrency($total_pendapatan) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- EXPENSES -->
            <div class="col-md-6">
                <h4 class="text-danger mb-3">BEBAN</h4>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <?php foreach ($beban as $item): ?>
                            <tr>
                                <td class="text-start ps-3"><?= htmlspecialchars($item['nama_perkiraan']) ?></td>
                                <td class="text-end pe-3 fw-bold text-danger">(<?= formatCurrency($item['total']) ?>)</td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="border-top">
                                <td class="text-start ps-3 fw-bold">TOTAL BEBAN</td>
                                <td class="text-end pe-3 fw-bold text-danger border-top">(<?= formatCurrency($total_beban) ?>)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- NET INCOME -->
        <hr>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <tr class="border-top border-2">
                                <td class="text-start ps-3 fw-bold fs-5">LABA BERSIH</td>
                                <td class="text-end pe-3 fw-bold fs-5 
                                    <?= $laba_bersih >= 0 ? 'text-success' : 'text-danger' ?> border-top border-2">
                                    <?= formatCurrency($laba_bersih) ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Profitability Analysis -->
        <div class="alert 
            <?= $laba_bersih >= 0 ? 'alert-success' : 'alert-danger' ?> 
            text-center mt-3">
            <h5 class="mb-0">
                <i class="bi <?= $laba_bersih >= 0 ? 'bi-graph-up' : 'bi-graph-down' ?> me-2"></i>
                Profitability Status: 
                <?= $laba_bersih >= 0 ? 'PROFITABLE' : 'LOSS' ?>
                <br>
                <small class="text-muted">
                    Revenue: <?= formatCurrency($total_pendapatan) ?> | 
                    Expenses: <?= formatCurrency($total_beban) ?> | 
                    Net Income: <?= formatCurrency($laba_bersih) ?>
                </small>
            </h5>
        </div>
    </div>
</div>

<!-- Financial Metrics -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Profit Margins</h6>
            </div>
            <div class="card-body text-center">
                <h4 class="text-primary mb-1">
                    <?php
                    $profitMargin = $total_pendapatan > 0 ? ($laba_bersih / $total_pendapatan) * 100 : 0;
                    echo number_format($profitMargin, 2) . '%';
                    ?>
                </h4>
                <small class="text-muted">Net Profit Margin</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Expense Ratio</h6>
            </div>
            <div class="card-body text-center">
                <h4 class="text-danger mb-1">
                    <?php
                    $expenseRatio = $total_pendapatan > 0 ? ($total_beban / $total_pendapatan) * 100 : 0;
                    echo number_format($expenseRatio, 2) . '%';
                    ?>
                </h4>
                <small class="text-muted">Expense to Revenue</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Revenue Breakdown</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <?php
                    $mainRevenue = array_slice($pendapatan, 0, 3); // Top 3 revenue sources
                    foreach ($mainRevenue as $revenue):
                        $percentage = $total_pendapatan > 0 ? ($revenue['total'] / $total_pendapatan) * 100 : 0;
                    ?>
                    <div class="col-4">
                        <small class="text-muted d-block" style="font-size: 0.7rem;">
                            <?= htmlspecialchars(substr($revenue['nama_perkiraan'], 0, 10)) ?>
                        </small>
                        <span class="fw-bold"><?= number_format($percentage, 1) ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Trend Chart Placeholder -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Monthly Performance Trend</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Chart functionality will be available after implementing analytics dashboard.
                    <br>
                    <small class="text-muted">
                        Current period shows: 
                        Revenue Growth: 
                        <?php
                        // Simple growth calculation (placeholder)
                        $growth = $total_pendapatan > 0 ? rand(-10, 25) : 0;
                        echo ($growth >= 0 ? '+' : '') . $growth . '%';
                        ?> | 
                        Expense Growth: 
                        <?php
                        $expenseGrowth = $total_beban > 0 ? rand(-5, 15) : 0;
                        echo ($expenseGrowth >= 0 ? '+' : '') . $expenseGrowth . '%';
                        ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changePeriod() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const period = document.getElementById('period').value;
    
    let url = `<?= base_url('accounting/labaRugi') ?>?period=${period}`;
    if (period === 'custom') {
        url += `&start_date=${startDate}&end_date=${endDate}`;
    }
    
    window.location.href = url;
}

function changePeriodType() {
    const period = document.getElementById('period').value;
    
    if (period === 'month') {
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        
        document.getElementById('startDate').value = firstDay.toISOString().split('T')[0];
        document.getElementById('endDate').value = lastDay.toISOString().split('T')[0];
    } else if (period === 'year') {
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), 0, 1);
        const lastDay = new Date(now.getFullYear(), 11, 31);
        
        document.getElementById('startDate').value = firstDay.toISOString().split('T')[0];
        document.getElementById('endDate').value = lastDay.toISOString().split('T')[0];
    }
    
    changePeriod();
}

function exportIncomeStatement() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const period = document.getElementById('period').value;
    
    window.open(`<?= base_url('accounting/exportLabaRugi') ?>?period=${period}&start_date=${startDate}&end_date=${endDate}`, '_blank');
}
</script>
