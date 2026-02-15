<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;

// Safe defaults for all risk_stats keys used in the template
$risk_stats = $risk_stats ?? [];
$risk_stats += ['total_alerts' => 0, 'high_severity_alerts' => 0, 'medium_risk' => 0, 'low_risk' => 0, 'resolved_today' => 0, 'overdue_invoices' => 0, 'high_risk_members' => 0];
?>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="risk">Manajemen Risiko</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-primary" onclick="refreshRiskData()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportRiskReport()">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
    </div>
</div>

<!-- Description -->
<p class="text-muted mb-4">Monitoring risiko dan compliance koperasi</p>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- Risk Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $risk_stats['total_alerts'] ?></h5>
                        <p class="card-text">Total Alert Risiko</p>
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
                        <h5 class="card-title mb-0"><?= $risk_stats['high_severity_alerts'] ?></h5>
                        <p class="card-text">Alert Tinggi</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-circle fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $risk_stats['overdue_invoices'] ?></h5>
                        <p class="card-text">Invoice Overdue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $risk_stats['high_risk_members'] ?></h5>
                        <p class="card-text">Anggota High Risk</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-times fa-2x opacity-75"></i>
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
                        <a href="<?= base_url('risk/monitorTransactions') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-search"></i> Monitor Transaksi
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('risk/compliance') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-check-circle"></i> Status Compliance
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('risk/fraudDetection') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-shield-alt"></i> Deteksi Fraud
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('risk/memberRiskAssessment') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-users"></i> Assessment Anggota
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Risk Alerts -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Alert Risiko Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_alerts)): ?>
                    <p class="text-muted">Tidak ada alert risiko aktif.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_alerts as $alert): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <span class="badge bg-<?= $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info') ?>">
                                            <?= htmlspecialchars($alert['severity']) ?>
                                        </span>
                                        <?= htmlspecialchars($alert['risk_type']) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($alert['type']) ?></span>
                                    </small>
                                </div>
                                <p class="mb-1"><?= htmlspecialchars($alert['description']) ?></p>
                                <?php if ($alert['entity_name']): ?>
                                    <p class="mb-1"><strong>Entitas:</strong> <?= htmlspecialchars($alert['entity_name']) ?></p>
                                <?php endif; ?>
                                <small class="text-muted">
                                    Dibuat: <?= htmlspecialchars(formatDate($alert['created_at'], 'd M Y H:i')) ?> |
                                    Oleh: <?= htmlspecialchars($alert['created_by_name']) ?>
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
                <h5>Status Compliance</h5>
            </div>
            <div class="card-body">
                <?php foreach ($compliance_status as $key => $status): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>
                            <?php
                            $labels = [
                                'member_data_completeness' => 'Kelengkapan Data Anggota',
                                'financial_records_accurate' => 'Akurasi Catatan Keuangan',
                                'regulatory_compliance' => 'Kepatuhan Regulasi',
                                'audit_trail_integrity' => 'Integritas Audit Trail',
                                'backup_regularity' => 'Reguleritas Backup'
                            ];
                            echo $labels[$key] ?? $key;
                            ?>
                        </span>
                        <span class="badge bg-<?= $status['status'] === 'compliant' ? 'success' : ($status['status'] === 'warning' ? 'warning' : 'danger') ?>">
                            <?= htmlspecialchars($status['status']) ?>
                        </span>
                    </div>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar bg-<?= $status['status'] === 'compliant' ? 'success' : ($status['status'] === 'warning' ? 'warning' : 'danger') ?>" 
                             style="width: <?= isset($status['percentage']) ? $status['percentage'] : 100 ?>%"></div>
                    </div>
                    <small class="text-muted d-block mb-3"><?= htmlspecialchars($status['message']) ?></small>
                <?php endforeach; ?>

                <div class="mt-3">
                    <a href="<?= base_url('risk/generateComplianceReport') ?>" class="btn btn-outline-primary btn-sm">
                        Generate Laporan Compliance
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Risk Mitigation Tips -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Tips Mitigasi Risiko</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>🔒 Keamanan Data</h6>
                        <ul class="small">
                            <li>Lakukan backup rutin</li>
                            <li>Enkripsi data sensitif</li>
                            <li>Monitor akses pengguna</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>💰 Keuangan</h6>
                        <ul class="small">
                            <li>Diversifikasi investasi</li>
                            <li>Monitor overdue payments</li>
                            <li>Audit laporan keuangan</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>👥 Operasional</h6>
                        <ul class="small">
                            <li>Verifikasi identitas anggota</li>
                            <li>Monitor aktivitas mencurigakan</li>
                            <li>Latih SDM secara berkala</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
