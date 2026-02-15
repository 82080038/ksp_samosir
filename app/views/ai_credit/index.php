<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';
require_once __DIR__ . '/../../../app/helpers/FormatHelper.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="ai-credit">AI Credit Scoring</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('ai_credit/bulkScoring') ?>" class="btn btn-sm btn-success">
                <i class="bi bi-robot"></i> Bulk Scoring
            </a>
            <a href="<?= base_url('ai_credit/modelTraining') ?>" class="btn btn-sm btn-info">
                <i class="bi bi-gear"></i> Model Training
            </a>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#refreshModal">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<?php if (true): ?>
<!-- Development Mode Notice -->
<div class="alert alert-info">
    <i class="bi bi-robot me-2"></i>
    <strong>AI Credit Scoring:</strong> Machine learning powered credit risk assessment system. Based on fintech trends 2024 - AI adoption in cooperative lending.
</div>
<?php endif; ?>

<!-- Flash Messages -->
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Total Scored</h5>
                        <span class="h2 font-weight-bold mb-0 text-primary">
                            <?= formatAngka($stats['total_scored'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-robot fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Avg Score</h5>
                        <span class="h2 font-weight-bold mb-0 text-success">
                            <?= formatAngka($stats['avg_score'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-up fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">High Risk</h5>
                        <span class="h2 font-weight-bold mb-0 <?= ($stats['high_risk_count'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                            <?= formatAngka($stats['high_risk_count'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Approval Rate</h5>
                        <span class="h2 font-weight-bold mb-0 text-info">
                            <?= formatPersentase($stats['approved_ratio'] ?? 0, 1) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Model Performance -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">AI Model Performance</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-primary"><?= formatPersentase($model_performance['accuracy'] ?? 0, 1) ?></h4>
                            <small class="text-muted">Accuracy</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-success"><?= formatPersentase($model_performance['precision'] ?? 0, 1) ?></h4>
                            <small class="text-muted">Precision</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-info"><?= formatPersentase($model_performance['recall'] ?? 0, 1) ?></h4>
                            <small class="text-muted">Recall</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-warning mb-1">Last Trained</h6>
                            <small class="text-muted">
                                <?= formatDate($model_performance['last_trained'] ?? 'now', 'd M Y H:i') ?>
                            </small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-primary mb-1">Training Samples</h6>
                            <small class="text-muted">
                                <?= formatAngka($model_performance['training_samples'] ?? 0) ?> loans
                            </small>
                        </div>
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
                <h5 class="card-title mb-0">AI Credit Scoring Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body text-center">
                                <i class="bi bi-search fa-2x text-primary mb-2"></i>
                                <h6 class="card-title">Loan Scoring</h6>
                                <p class="card-text small">Score individual loan applications</p>
                                <a href="<?= base_url('pinjaman') ?>" class="btn btn-primary btn-sm">View Loans</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <i class="bi bi-robot fa-2x text-success mb-2"></i>
                                <h6 class="card-title">Bulk Scoring</h6>
                                <p class="card-text small">Process multiple applications</p>
                                <a href="<?= base_url('ai_credit/bulkScoring') ?>" class="btn btn-success btn-sm">Start</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-info">
                            <div class="card-body text-center">
                                <i class="bi bi-graph-up fa-2x text-info mb-2"></i>
                                <h6 class="card-title">Risk Analytics</h6>
                                <p class="card-text small">View risk distribution</p>
                                <a href="<?= base_url('ai_credit/analytics') ?>" class="btn btn-info btn-sm">View</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <i class="bi bi-gear fa-2x text-warning mb-2"></i>
                                <h6 class="card-title">Model Training</h6>
                                <p class="card-text small">Retrain AI model</p>
                                <a href="<?= base_url('ai_credit/modelTraining') ?>" class="btn btn-warning btn-sm">Train</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Credit Scores -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Credit Scores</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Member</th>
                                <th>Loan Number</th>
                                <th>Amount</th>
                                <th>Credit Score</th>
                                <th>Grade</th>
                                <th>Risk Level</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_scores)): ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="py-4">
                                        <i class="bi bi-robot fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No credit scores yet</h5>
                                        <p class="text-muted mb-3">
                                            Start by scoring loan applications to see AI-powered credit assessments.
                                        </p>
                                        <a href="<?= base_url('ai_credit/bulkScoring') ?>" class="btn btn-primary">
                                            <i class="bi bi-play-circle me-2"></i>Start Scoring
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($recent_scores as $score): ?>
                                <tr>
                                    <td><?= formatDate($score['created_at']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($score['nama_lengkap'] ?? 'Unknown') ?></strong>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($score['no_pinjaman'] ?? '-') ?></code>
                                    </td>
                                    <td class="text-end">
                                        Rp <?= formatUang($score['jumlah_pinjaman'] ?? 0) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge fs-6
                                            <?php
                                            $score_val = $score['total_score'] ?? 0;
                                            if ($score_val >= 750) echo 'bg-success';
                                            elseif ($score_val >= 650) echo 'bg-warning';
                                            elseif ($score_val >= 550) echo 'bg-danger';
                                            else echo 'bg-dark';
                                            ?>">
                                            <?= formatAngka($score_val, 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge
                                            <?php
                                            $grade = $score['grade'] ?? 'C';
                                            if ($grade === 'A') echo 'bg-success';
                                            elseif ($grade === 'B') echo 'bg-info';
                                            elseif ($grade === 'C') echo 'bg-warning';
                                            elseif ($grade === 'D') echo 'bg-danger';
                                            else echo 'bg-dark';
                                            ?>">
                                            <?= htmlspecialchars($grade) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge
                                            <?php
                                            $risk = $score['risk_level'] ?? 'Medium Risk';
                                            if (strpos($risk, 'Low') !== false) echo 'bg-success';
                                            elseif (strpos($risk, 'Medium') !== false) echo 'bg-warning';
                                            elseif (strpos($risk, 'High') !== false) echo 'bg-danger';
                                            else echo 'bg-dark';
                                            ?>">
                                            <?= htmlspecialchars($risk) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('ai_credit/viewScore/' . $score['loan_id']) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
