<?php
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url('public/assets/css/style.css') ?>" rel="stylesheet">
    <link href="<?= base_url('public/assets/css/style-custom.css') ?>" rel="stylesheet">
</head>
<body>
<!-- Mobile Navbar -->
<nav class="navbar navbar-dark bg-dark d-md-none fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= base_url('dashboard') ?>"><?= APP_NAME ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<div class="container-fluid" style="padding-top:56px;">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse text-white" style="min-height:100vh;">
            <div class="position-sticky pt-3 px-3">
                <div class="mb-4">
                    <div class="fw-bold"><?= APP_NAME ?></div>
                    <small><?= $user['full_name'] ?? 'Pengguna' ?> (<?= $role ?? '-' ?>)</small>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                    <?php if (hasRole(['admin','staff'])): ?>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('anggota') ?>"><i class="bi bi-people me-2"></i>Anggota</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('simpanan') ?>"><i class="bi bi-piggy-bank me-2"></i>Simpanan</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('pinjaman') ?>"><i class="bi bi-cash-stack me-2"></i>Pinjaman</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('produk') ?>"><i class="bi bi-box me-2"></i>Produk</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('penjualan') ?>"><i class="bi bi-cart3 me-2"></i>Penjualan</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('laporan') ?>"><i class="bi bi-file-earmark-text me-2"></i>Laporan</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('settings') ?>"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                        <?php if (hasRole(['admin'])): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('logs') ?>"><i class="bi bi-journal-text me-2"></i>Logs</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (hasRole(['member'])): ?>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('profile') ?>"><i class="bi bi-person me-2"></i>Profil</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('simpanan') ?>"><i class="bi bi-piggy-bank me-2"></i>Simpanan Saya</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('pinjaman') ?>"><i class="bi bi-cash-stack me-2"></i>Pinjaman Saya</a></li>
                    <?php endif; ?>
                    <?php if (hasRole(['member','staff','admin'])): ?>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('penjualan') ?>"><i class="bi bi-bag me-2"></i>Transaksi Saya</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link text-danger" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
            <?= $content ?? '' ?>
        </main>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('public/assets/js/ksp-ajax.js') ?>"></script>
<?php include __DIR__ . '/../templates/modals.php'; ?>
</body>
</html>
