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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="blockchain">Transparansi Blockchain</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-primary" onclick="refreshBlockchain()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-info" onclick="viewTransactions()">
                <i class="bi bi-eye"></i> View Transactions
            </button>
        </div>
    </div>
</div>

<!-- Description -->
<p class="text-muted mb-4">Sistem transparansi berbasis blockchain untuk catatan immutable</p>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- Transparency Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['total_blocks'] ?></h5>
                        <p class="card-text">Total Block</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-cubes fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $stats['blocks_this_month'] ?></h5>
                        <p class="card-text">Block Bulan Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $stats['governance_decisions'] ?></h5>
                        <p class="card-text">Keputusan Tata Kelola</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-gavel fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-<?= $stats['chain_integrity'] === 'Valid' ? 'success' : 'danger' ?> text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['chain_integrity'] ?></h5>
                        <p class="card-text">Integritas Chain</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-link fa-2x opacity-75"></i>
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
                        <a href="<?= base_url('blockchain/transactionHistory') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-history"></i> Riwayat Transaksi
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('blockchain/recordGovernanceDecision') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-plus"></i> Catat Keputusan
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('blockchain/verifyIntegrity') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-shield-alt"></i> Verifikasi Integritas
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('blockchain/transparencyReport') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-file-alt"></i> Laporan Transparansi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Blocks and Verification Status -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Block Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_blocks)): ?>
                    <p class="text-muted">Belum ada block yang dicatat.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_blocks as $block): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($block['block_type']) ?></span>
                                        Block #<?= htmlspecialchars($block['id']) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <span class="badge bg-success">Verified</span>
                                    </small>
                                </div>
                                <p class="mb-1"><?= htmlspecialchars($block['summary']) ?></p>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            Hash: <code class="text-truncate d-inline-block" style="max-width: 200px;"><?= htmlspecialchars(substr($block['current_hash'], 0, 16)) ?>...</code>
                                        </small>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted">
                                            Dibuat: <?= htmlspecialchars(formatDate($block['created_at'], 'd M Y H:i')) ?> |
                                            Oleh: <?= htmlspecialchars($block['recorded_by_name']) ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="<?= base_url('blockchain/blockDetail/' . $block['id']) ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Status Verifikasi</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary"><?= $verification_status['total_blocks'] ?></h4>
                        <small>Total Block</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success"><?= $verification_status['verified_blocks'] ?></h4>
                        <small>Terverifikasi</small>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="progress">
                        <div class="progress-bar bg-<?= $verification_status['verification_percentage'] >= 90 ? 'success' : 'warning' ?>" 
                             style="width: <?= $verification_status['verification_percentage'] ?>%">
                            <?= $verification_status['verification_percentage'] ?>%
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block">Persentase Verifikasi Chain</small>
                </div>

                <?php if ($verification_status['verification_percentage'] < 100): ?>
                    <div class="alert alert-warning mt-3">
                        <h6><i class="fas fa-exclamation-triangle"></i> Peringatan</h6>
                        <p class="mb-0">Beberapa block belum terverifikasi. Jalankan verifikasi integritas untuk detail lebih lanjut.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success mt-3">
                        <h6><i class="fas fa-check-circle"></i> Chain Valid</h6>
                        <p class="mb-0">Semua block dalam blockchain telah terverifikasi dan integritas data terjamin.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Blockchain Technology</h5>
            </div>
            <div class="card-body">
                <h6>Fitur Keamanan:</h6>
                <ul class="small mb-3">
                    <li><strong>Hash Chaining:</strong> Setiap block terhubung dengan block sebelumnya</li>
                    <li><strong>Immutable Records:</strong> Data tidak dapat diubah setelah dicatat</li>
                    <li><strong>Cryptographic Security:</strong> SHA-256 hashing untuk integritas</li>
                    <li><strong>Transparent Ledger:</strong> Semua transaksi dapat dilacak</li>
                </ul>

                <h6>Manfaat untuk Koperasi:</h6>
                <ul class="small">
                    <li>Transparansi penuh untuk anggota</li>
                    <li>Audit trail yang tidak dapat diubah</li>
                    <li>Verifikasi independen transaksi</li>
                    <li>Kepercayaan stakeholder meningkat</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Blockchain Visualization -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Visualisasi Blockchain</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="d-inline-block">
                        <?php for ($i = 0; $i < min(10, $stats['total_blocks']); $i++): ?>
                            <div class="d-inline-block mx-1 mb-2">
                                <div class="card" style="width: 80px;">
                                    <div class="card-body p-2 text-center">
                                        <div class="bg-primary text-white rounded-circle mx-auto mb-1" style="width: 30px; height: 30px; line-height: 30px;">
                                            <i class="fas fa-cube fa-xs"></i>
                                        </div>
                                        <small class="text-muted d-block">#<?= $i + 1 ?></small>
                                    </div>
                                </div>
                                <?php if ($i < min(9, $stats['total_blocks'] - 1)): ?>
                                    <div class="text-center">
                                        <i class="fas fa-arrow-down text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <?php if ($stats['total_blocks'] > 10): ?>
                    <div class="text-center mt-3">
                        <small class="text-muted">... dan <?= $stats['total_blocks'] - 10 ?> block lainnya</small>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info mt-4">
                    <h6><i class="fas fa-info-circle"></i> Cara Kerja Blockchain</h6>
                    <p class="mb-2">Setiap block berisi data transaksi yang di-hash secara kriptografis dan dihubungkan dengan block sebelumnya melalui hash chain. Sistem ini memastikan:</p>
                    <ul class="mb-0">
                        <li><strong>Immutability:</strong> Data tidak dapat diubah tanpa merusak seluruh chain</li>
                        <li><strong>Transparency:</strong> Semua transaksi dapat diverifikasi oleh pihak independen</li>
                        <li><strong>Accountability:</strong> Setiap perubahan tercatat dengan jejak audit lengkap</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
