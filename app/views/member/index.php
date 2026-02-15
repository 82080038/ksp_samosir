<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="member">Data Member</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2" style="color: black;"><?= $pageInfo['title'] ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-gear"></i> Akun
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= base_url('member/profile') ?>"><i class="bi bi-person"></i> Profil</a></li>
                <li><a class="dropdown-item" href="<?= base_url('member/changePassword') ?>"><i class="bi bi-key"></i> Ubah Kata Sandi</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
            </ul>
        </div>
    </div>
</div>

<?php if (true): ?>
<!-- Development Mode Notice -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-gear me-2"></i>
    <strong>Portal Anggota:</strong> Sistem layanan mandiri untuk anggota koperasi dengan dependency management terstandarisasi.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="card-title">Selamat Datang, <?= htmlspecialchars($member['nama_lengkap'] ?? 'Anggota') ?>!</h3>
                        <p class="card-text mb-2">No. Anggota: <strong><?= htmlspecialchars($member['no_anggota'] ?? '-') ?></strong></p>
                        <p class="card-text mb-0">Status: <span class="badge bg-light text-primary">
                            <?= htmlspecialchars($member['status'] ?? 'Aktif') ?>
                        </span></p>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="bi bi-person-circle fa-4x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Total Simpanan</h5>
                        <span class="h2 font-weight-bold mb-0 text-success">
                            Rp <?= formatUang($stats['total_savings'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-wallet2 fa-2x text-success"></i>
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
                        <h5 class="card-title text-uppercase mb-0">Pinjaman Aktif</h5>
                        <span class="h2 font-weight-bold mb-0 text-info">
                            <?= formatAngka($stats['active_loans'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cash fa-2x text-info"></i>
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
                        <h5 class="card-title text-uppercase mb-0">Pengajuan Pending</h5>
                        <span class="h2 font-weight-bold mb-0 text-warning">
                            <?= formatAngka($stats['pending_applications'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock-history fa-2x text-warning"></i>
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
                        <h5 class="card-title text-uppercase mb-0">Member Sejak</h5>
                        <span class="h6 font-weight-bold mb-0">
                            <?= formatDate($stats['member_since'] ?? '-', 'M Y') ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar-check fa-2x text-primary"></i>
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
                <h5 class="card-title mb-0">Layanan Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('member/loanApplication') ?>" class="btn btn-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-plus-circle fa-2x mb-2"></i>
                            <span>Ajukan Pinjaman</span>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('member/savingsStatement') ?>" class="btn btn-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-file-earmark-text fa-2x mb-2"></i>
                            <span>Riwayat Simpanan</span>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('member/loanHistory') ?>" class="btn btn-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-list-check fa-2x mb-2"></i>
                            <span>Riwayat Pinjaman</span>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('member/profile') ?>" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-person-gear fa-2x mb-2"></i>
                            <span>Perbarui Profil</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_activities)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-activity fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada aktivitas</h5>
                    <p class="text-muted">Aktivitas Anda akan muncul di sini setelah melakukan transaksi.</p>
                </div>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($recent_activities as $activity): ?>
                    <div class="timeline-item mb-3">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">
                                <span class="badge bg-secondary me-2">
                                    <?= htmlspecialchars($activity['type'] ?? 'Activity') ?>
                                </span>
                                <?= htmlspecialchars($activity['description'] ?? '') ?>
                            </h6>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                <?= formatDate($activity['date'] ?? '', 'd M Y H:i') ?>
                            </small>
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
                <h5 class="card-title mb-0">Informasi Penting</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-primary mb-3">
                    <h6 class="alert-heading mb-2">
                        <i class="bi bi-info-circle me-2"></i>Rapat Anggota
                    </h6>
                    <p class="mb-1 small">Rapat tahunan akan dilaksanakan pada tanggal 15 Februari 2026.</p>
                    <small class="text-muted">Tempat: Kantor Koperasi</small>
                </div>
                
                <div class="alert alert-success mb-3">
                    <h6 class="alert-heading mb-2">
                        <i class="bi bi-trophy me-2"></i>SHU 2025
                    </h6>
                    <p class="mb-1 small">Pembagian SHU tahun 2025 telah selesai. Cek rekening Anda.</p>
                    <small class="text-muted">Total: Rp 250.000,-</small>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading mb-2">
                        <i class="bi bi-bell me-2"></i>Pengingat
                    </h6>
                    <p class="mb-1 small">Jangan lupa bayar angsuran pinjaman bulan ini.</p>
                    <small class="text-muted">Batas: 25 Feb 2026</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}
</style>
