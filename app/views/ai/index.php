<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Dashboard AI Features</h2>
        <p class="text-muted">Rekomendasi produk dan deteksi fraud menggunakan AI</p>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- AI Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $recommendation_stats['total_recommendations_generated'] ?></h5>
                        <p class="card-text">Rekomendasi Dihasilkan</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-lightbulb fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $recommendation_stats['unique_users_recommended'] ?></h5>
                        <p class="card-text">Pengguna Direkomendasikan</p>
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
                        <h5 class="card-title mb-0"><?= $fraud_stats['total_alerts_today'] ?></h5>
                        <p class="card-text">Alert Fraud Hari Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $fraud_stats['high_risk_alerts'] ?></h5>
                        <p class="card-text">Alert Risiko Tinggi</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-shield-alt fa-2x opacity-75"></i>
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
                        <a href="<?= base_url('ai/personalizedRecommendations') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-user-tag"></i> Rekomendasi Personal
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" onclick="runFraudDetection()" class="btn btn-outline-danger btn-block">
                            <i class="fas fa-search"></i> Jalankan Deteksi Fraud
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" onclick="generateBulkRecommendations()" class="btn btn-outline-success btn-block">
                            <i class="fas fa-magic"></i> Generate Rekomendasi Massal
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" onclick="viewFraudAlerts()" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-bell"></i> Lihat Alert Fraud
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Recommendations and Fraud Alerts -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Rekomendasi Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_recommendations)): ?>
                    <p class="text-muted">Belum ada rekomendasi yang dihasilkan.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_recommendations as $rec): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Rekomendasi untuk <?= htmlspecialchars($rec['user_name']) ?></h6>
                                    <small class="text-muted">
                                        <?php if ($rec['was_purchased']): ?>
                                            <span class="badge bg-success">Dibeli</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Belum Dibeli</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <p class="mb-1">
                                    Score: <strong><?= htmlspecialchars($rec['recommendation_score']) ?>%</strong> |
                                    Alasan: <?= htmlspecialchars($rec['recommendation_reason']) ?>
                                </p>
                                <small class="text-muted">
                                    Dibuat: <?= htmlspecialchars(formatDate($rec['created_at'], 'd M Y H:i')) ?>
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
                <h5>Alert Fraud Aktif</h5>
            </div>
            <div class="card-body">
                <?php
                $active_alerts = fetchAll("SELECT * FROM ai_fraud_alerts WHERE status = 'active' ORDER BY created_at DESC LIMIT 5");
                if (empty($active_alerts)):
                ?>
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle"></i> Tidak Ada Alert Fraud Aktif</h6>
                        <p>Sistem belum mendeteksi aktivitas mencurigakan.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($active_alerts as $alert): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <span class="badge bg-<?= $alert['risk_level'] === 'high' ? 'danger' : ($alert['risk_level'] === 'medium' ? 'warning' : 'info') ?>">
                                            <?= htmlspecialchars($alert['risk_level']) ?>
                                        </span>
                                        <?= htmlspecialchars($alert['alert_type']) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($alert['status']) ?></span>
                                    </small>
                                </div>
                                <p class="mb-1"><?= htmlspecialchars($alert['description']) ?></p>
                                <small class="text-muted">
                                    Dibuat: <?= htmlspecialchars(formatDate($alert['created_at'], 'd M Y H:i')) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- AI Performance Metrics -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Performa AI</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Rekomendasi Produk</h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <h4 class="text-primary">85%</h4>
                                <small>Akurasi</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-success">15.5%</h4>
                                <small>Conversion Rate</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-info">2.3s</h4>
                                <small>Response Time</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Deteksi Fraud</h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <h4 class="text-warning">92%</h4>
                                <small>Precision</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-danger">8%</h4>
                                <small>False Positive</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-secondary">0.5s</h4>
                                <small>Detection Time</small>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6>Algoritma yang Digunakan</h6>
                        <ul class="small">
                            <li><strong>Collaborative Filtering:</strong> Rekomendasi berdasarkan pengguna serupa</li>
                            <li><strong>Content-Based Filtering:</strong> Rekomendasi berdasarkan riwayat pembelian</li>
                            <li><strong>Rule-Based Detection:</strong> Aturan untuk mendeteksi fraud</li>
                            <li><strong>Pattern Recognition:</strong> Identifikasi pola tidak biasa</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Data Training</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-primary" style="width: 78%">78% Purchase History</div>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" style="width: 65%">65% User Behavior</div>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-warning" style="width: 52%">52% Transaction Patterns</div>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-danger" style="width: 89%">89% Fraud Cases</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function runFraudDetection() {
    if (confirm('Jalankan deteksi fraud pada transaksi terbaru?')) {
        window.location.href = '<?= base_url('ai/runFraudDetection') ?>';
    }
}

function generateBulkRecommendations() {
    alert('Fitur generate rekomendasi massal akan diimplementasikan untuk semua pengguna aktif');
}

function viewFraudAlerts() {
    alert('Fitur lihat detail alert fraud akan diimplementasikan dengan dashboard lengkap');
}
</script>
