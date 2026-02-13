<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= APP_NAME ?></title>
    <link rel="icon" href="data:,">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=dev" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css?v=dev" rel="stylesheet">
    <link href="<?= base_url('public/assets/css/style-blue.css?v=dev') ?>" rel="stylesheet">
    <style>
        .layout-body { padding-top: 0; }
        .ksp-sidebar { overflow-y: auto; min-height: 100vh; }
        .ksp-sidebar .nav-link { padding: 10px 14px; border-radius: 10px; margin-bottom: 6px; }
        @media (min-width: 768px) {
            .ksp-sidebar-desktop { position: fixed; top: 56px; bottom: 0; width: 220px; min-width: 220px; }
            .ksp-content-wrap { margin-left: 220px; padding: 16px; }
        }
    </style>
</head>
<body>
<header class="container-fluid sticky-top bg-dark">
    <div class="row">
        <nav class="navbar navbar-dark bg-dark d-flex justify-content-between">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>"><?= APP_NAME ?></a>
            <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </nav>
    </div>
</header>

<main class="container-fluid layout-body">
    <div class="row flex-nowrap align-items-start">
        <!-- Sidebar (mobile collapse, desktop sticky) -->
        <nav id="sidebarMenu" class="col-12 col-md-3 col-lg-2 bg-dark text-white collapse d-md-block ksp-sidebar ksp-sidebar-desktop px-3 py-3">
            <div>
                <div class="mb-4">
                    <div class="fw-bold"><?= APP_NAME ?></div>
                    <small><?= $user['full_name'] ?? 'Pengguna' ?> (<?= $role ?? '-' ?>)</small>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('anggota') ?>"><i class="bi bi-people me-2"></i>Anggota</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('simpanan') ?>"><i class="bi bi-piggy-bank me-2"></i>Simpanan</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('pinjaman') ?>"><i class="bi bi-cash-stack me-2"></i>Pinjaman</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('produk') ?>"><i class="bi bi-box me-2"></i>Produk</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('penjualan') ?>"><i class="bi bi-cart3 me-2"></i>Penjualan</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('laporan') ?>"><i class="bi bi-file-earmark-text me-2"></i>Laporan</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('settings') ?>"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('logs') ?>"><i class="bi bi-journal-text me-2"></i>Logs</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </nav>

        <!-- Content -->
        <section class="col py-3 px-3 ksp-content ksp-content-wrap">
            <?= $content ?? '' ?>
        </section>
    </div>
</main>

<footer class="container-fluid ksp-footer text-center text-muted small py-3">
    © <?= date('Y') ?> <?= APP_NAME ?>
</footer>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('public/assets/js/ksp-ajax.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarMenu = document.getElementById('sidebarMenu');
    const toggler = document.querySelector('[data-bs-target="#sidebarMenu"]');

    if (sidebarMenu) {
        sidebarMenu.querySelectorAll('a.nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                const collapse = bootstrap.Collapse.getOrCreateInstance(sidebarMenu);
                collapse.hide();
            });
        });
    }

    // Fallback manual jika bootstrap collapse tidak tersedia
    if (toggler && sidebarMenu && typeof bootstrap === 'undefined') {
        toggler.addEventListener('click', function (e) {
            e.preventDefault();
            sidebarMenu.classList.toggle('show');
        });
    }

    // Saat layar melebar ke desktop, pastikan menu mobile tertutup
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768 && sidebarMenu) {
            const collapse = bootstrap.Collapse.getOrCreateInstance(sidebarMenu);
            collapse.hide();
        }
    });
});
</script>
<?php include __DIR__ . '/../templates/modals.php'; ?>
</body>
</html>
