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

    <!-- Bootstrap CSS - Optimized Loading -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
          crossorigin="anonymous"
          onload="this.onload=null;this.rel='stylesheet'"
          as="style">

    <!-- Bootstrap Icons - Optimized Loading -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
          rel="stylesheet"
          onload="this.onload=null;this.rel='stylesheet'"
          as="style">

    <!-- Custom CSS - Optimized -->
    <link href="<?= base_url('public/assets/css/style-blue.min.css?v=' . filemtime(__DIR__ . '/../../public/assets/css/style-blue.css')) ?>"
          rel="stylesheet"
          as="style">

    <!-- Preload Critical Resources -->
    <link rel="preload" href="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" as="script">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" as="script">

    <!-- DNS Prefetch for External Resources -->
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//ajax.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.googleapis.com">

    <!-- Critical CSS Inline -->
    <style>
        /* Critical above-the-fold styles */
        .layout-body { padding-top: 0; }
        .ksp-sidebar { overflow-y: auto; min-height: 100vh; }
        .ksp-sidebar .nav-link {
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 6px;
            transition: all 0.15s ease;
        }
        .ksp-sidebar .nav-link:hover { transform: translateX(2px); }

        /* Loading states */
        .loading { opacity: 0.6; pointer-events: none; }
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #007bff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive optimizations */
        @media (min-width: 768px) {
            .ksp-sidebar-desktop {
                position: fixed;
                top: 56px;
                bottom: 0;
                width: 220px;
                min-width: 220px;
                z-index: 1000;
            }
            .ksp-content-wrap {
                margin-left: 220px;
                padding: 16px;
                min-height: calc(100vh - 56px);
            }
        }

        @media (max-width: 767.98px) {
            .ksp-sidebar { position: relative; }
            .navbar-toggler { order: -1; }
        }

        /* Performance optimizations */
        * { box-sizing: border-box; }
        img { max-width: 100%; height: auto; }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    </style>
</head>
<body>
<!-- Loading Screen -->
<div id="loading-screen" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #fff; z-index: 9999; display: flex; align-items: center; justify-content: center;">
    <div class="text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-2">Loading <?= APP_NAME ?>...</div>
    </div>
</div>

<header class="container-fluid sticky-top bg-dark">
    <div class="row">
        <nav class="navbar navbar-dark bg-dark d-flex justify-content-between">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">
                <i class="bi bi-bank me-2"></i><?= APP_NAME ?>
            </a>
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
                    <div class="fw-bold">
                        <i class="bi bi-bank me-2"></i><?= APP_NAME ?>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-person-circle me-1"></i><?= $user['full_name'] ?? 'Pengguna' ?>
                        <span class="badge bg-primary ms-1"><?= $role ?? '-' ?></span>
                    </small>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('dashboard') ?>">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('anggota') ?>">
                            <i class="bi bi-people me-2"></i>Anggota
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('simpanan') ?>">
                            <i class="bi bi-piggy-bank me-2"></i>Simpanan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('pinjaman') ?>">
                            <i class="bi bi-cash-stack me-2"></i>Pinjaman
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('produk') ?>">
                            <i class="bi bi-box me-2"></i>Produk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('penjualan') ?>">
                            <i class="bi bi-cart3 me-2"></i>Penjualan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('laporan') ?>">
                            <i class="bi bi-file-earmark-text me-2"></i>Laporan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('settings') ?>">
                            <i class="bi bi-gear me-2"></i>Pengaturan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('logs') ?>">
                            <i class="bi bi-journal-text me-2"></i>Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="<?= base_url('logout') ?>">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
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
    <div class="container">
        <div class="row">
            <div class="col-md-6 text-start">
                © <?= date('Y') ?> <?= APP_NAME ?> - Optimized for Performance
            </div>
            <div class="col-md-6 text-end">
                <small>v<?= APP_VERSION ?? '1.0' ?> - Bootstrap 5.3.2</small>
            </div>
        </div>
    </div>
</footer>

<!-- Optimized JavaScript Loading -->
<script>
// Immediately hide loading screen
document.getElementById('loading-screen').style.display = 'none';

// Performance monitoring
window.addEventListener('load', function() {
    // Log page load time
    if (window.performance) {
        const loadTime = performance.now();
        console.log('Page loaded in', loadTime.toFixed(2), 'ms');
    }
});

// Service Worker Registration (for caching)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/ksp_samosir/sw.js')
            .then(function(registration) {
                console.log('SW registered: ', registration);
            })
            .catch(function(registrationError) {
                console.log('SW registration failed: ', registrationError);
            });
    });
}
</script>

<!-- jQuery - Optimized Loading -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpAhJTS7x/GBWmLiZJ58/aE4EFq5Dcry1"
        crossorigin="anonymous"
        defer></script>

<!-- Bootstrap JS Bundle - Optimized Loading -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"
        defer></script>

<!-- Custom AJAX Handler - Optimized -->
<script src="<?= base_url('public/assets/js/ksp-ajax.min.js?v=' . filemtime(__DIR__ . '/../../public/assets/js/ksp-ajax.js')) ?>" defer></script>

<!-- Main Application Script -->
<script defer>
// Enhanced Bootstrap sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarMenu = document.getElementById('sidebarMenu');
    const toggler = document.querySelector('[data-bs-target="#sidebarMenu"]');

    // Enhanced sidebar toggle with smooth animations
    if (sidebarMenu) {
        sidebarMenu.querySelectorAll('a.nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                // Close mobile menu after navigation
                if (window.innerWidth < 768 && sidebarMenu.classList.contains('show')) {
                    const collapse = bootstrap.Collapse.getOrCreateInstance(sidebarMenu);
                    collapse.hide();
                }
            });
        });

        // Keyboard navigation support
        sidebarMenu.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const collapse = bootstrap.Collapse.getOrCreateInstance(sidebarMenu);
                collapse.hide();
            }
        });
    }

    // Enhanced responsive behavior
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768 && sidebarMenu) {
            // Ensure desktop sidebar is visible
            sidebarMenu.classList.add('show');
        }
    });

    // Touch gesture support for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });

    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        const swipeThreshold = 50;
        if (touchEndX - touchStartX > swipeThreshold && window.innerWidth < 768) {
            // Swipe right - show menu
            if (sidebarMenu) {
                const collapse = bootstrap.Collapse.getOrCreateInstance(sidebarMenu);
                collapse.show();
            }
        } else if (touchStartX - touchEndX > swipeThreshold && window.innerWidth < 768) {
            // Swipe left - hide menu
            if (sidebarMenu) {
                const collapse = bootstrap.Collapse.getOrCreateInstance(sidebarMenu);
                collapse.hide();
            }
        }
    }

    // Performance monitoring
    window.KSP = window.KSP || {};
    window.KSP.performance = {
        mark: function(name) {
            if (window.performance && window.performance.mark) {
                window.performance.mark(name);
            }
        },
        measure: function(name, start, end) {
            if (window.performance && window.performance.measure) {
                window.performance.measure(name, start, end);
                const measure = window.performance.getEntriesByName(name)[0];
                console.log(`${name}: ${measure.duration.toFixed(2)}ms`);
            }
        }
    };

    // Mark page ready
    window.KSP.performance.mark('page-ready');
});
</script>

<?php include __DIR__ . '/../templates/modals.php'; ?>
</body>
</html>
