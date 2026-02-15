<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>


</div>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="shu-member-report">Laporan SHU Anggota</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="shu" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
    </div>
</div>


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

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('shu') ?>">SHU</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('shu/reports') ?>">Laporan</a></li>
                <li class="breadcrumb-item active">Detail Anggota</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="<?= base_url('shu/reports') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Laporan</a>
    </div>
</div>

<!-- Member Info -->
<div class="card mb-4">
    <div class="card-header">
        
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>No Anggota:</strong> <code><?= htmlspecialchars($member['no_anggota']) ?></code></p>
                <p><strong>Nama:</strong> <?= htmlspecialchars($member['nama_lengkap']) ?></p>
                <p><strong>NIK:</strong> <?= htmlspecialchars($member['nik']) ?></p>
                <p><strong>Status:</strong>
                    <span class="badge bg-<?= $member['status'] === 'aktif' ? 'success' : 'secondary' ?>">
                        <?= htmlspecialchars($member['status']) ?>
                    </span>
                </p>
            </div>
            <div class="col-md-6">
                <p><strong>Tanggal Gabung:</strong> <?= htmlspecialchars(formatDate($member['tanggal_gabung'], 'd M Y')) ?></p>
                <p><strong>Alamat:</strong> <?= htmlspecialchars($member['alamat']) ?></p>
                <p><strong>No HP:</strong> <?= htmlspecialchars($member['no_hp']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($member['email']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- SHU History -->
<div class="card">
    <div class="card-header">
        
    </div>
    <div class="card-body">
        <?php if (empty($shu_history)): ?>
            <p class="text-muted">Belum ada riwayat SHU untuk anggota ini.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Tanggal Distribusi</th>
                            <th>SHU dari Transaksi</th>
                            <th>SHU dari Partisipasi</th>
                            <th>Total SHU</th>
                            <th>Status Pembayaran</th>
                            <th>Tanggal Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shu_history as $shu): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($shu['periode']) ?></strong></td>
                                <td><?= htmlspecialchars(formatDate($shu['tanggal_distribusi'], 'd M Y')) ?></td>
                                <td>Rp <?= formatCurrency($shu['shu_dari_transaksi']) ?></td>
                                <td>Rp <?= formatCurrency($shu['shu_dari_partisipasi']) ?></td>
                                <td><strong class="text-success">Rp <?= formatCurrency($shu['total_shu']) ?></strong></td>
                                <td>
                                    <span class="badge bg-<?= $shu['status_pembayaran'] === 'lunas' ? 'success' : ($shu['status_pembayaran'] === 'belum_bayar' ? 'warning' : 'info') ?>">
                                        <?= htmlspecialchars($shu['status_pembayaran']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $shu['tanggal_bayar'] ? htmlspecialchars(formatDate($shu['tanggal_bayar'], 'd M Y')) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            
                            <h4 class="text-success">
                                Rp <?= formatCurrency(array_sum(array_column($shu_history, 'total_shu'))) ?>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            
                            <h4 class="text-success">
                                Rp <?= formatCurrency(array_sum(array_map(function($shu) {
                                    return $shu['status_pembayaran'] === 'lunas' ? $shu['total_shu'] : 0;
                                }, $shu_history))) ?>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            
                            <h4 class="text-warning">
                                Rp <?= formatCurrency(array_sum(array_map(function($shu) {
                                    return $shu['status_pembayaran'] !== 'lunas' ? $shu['total_shu'] : 0;
                                }, $shu_history))) ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Savings & Loan Info (for SHU calculation context) -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                
            </div>
            <div class="card-body">
                <?php
                $simpanan = fetchAll("SELECT js.nama_simpanan, s.saldo FROM simpanan s JOIN jenis_simpanan js ON s.jenis_simpanan_id = js.id WHERE s.anggota_id = ? AND s.status = 'aktif'", [$member['id']], 'i');
                if (empty($simpanan)): ?>
                    <p class="text-muted">Tidak ada simpanan aktif.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($simpanan as $sim): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?= htmlspecialchars($sim['nama_simpanan']) ?></span>
                                <strong>Rp <?= formatCurrency($sim['saldo']) ?></strong>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between bg-light">
                            <strong>Total Simpanan:</strong>
                            <strong class="text-primary">Rp <?= formatCurrency(array_sum(array_column($simpanan, 'saldo'))) ?></strong>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                
            </div>
            <div class="card-body">
                <?php
                $pinjaman = fetchAll("SELECT p.jumlah_pinjaman, p.status FROM pinjaman p WHERE p.anggota_id = ? AND p.status IN ('disetujui', 'dicairkan')", [$member['id']], 'i');
                if (empty($pinjaman)): ?>
                    <p class="text-muted">Tidak ada pinjaman aktif.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($pinjaman as $pin): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Pinjaman <?= htmlspecialchars($pin['status']) ?></span>
                                <strong>Rp <?= formatCurrency($pin['jumlah_pinjaman']) ?></strong>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between bg-light">
                            <strong>Total Pinjaman:</strong>
                            <strong class="text-danger">Rp <?= formatCurrency(array_sum(array_column($pinjaman, 'jumlah_pinjaman'))) ?></strong>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page shu - member_report initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Laporan Anggota', 'shu-member_report');
    }
});

// Global functions
function saveShu() {
    const form = document.querySelector('form');
    if (form) {
        form.dispatchEvent(new Event('submit'));
    }
}
</script>

<style>
/* Page-specific styles */
.page-title {
    font-weight: 700;
}

.main-content {
    min-height: 400px;
}
</style>