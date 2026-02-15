<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';
require_once __DIR__ . '/../../../app/helpers/SidebarHelper.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <?php setPageTitle($pageInfo['page']); ?>
    <meta name="description" content="<?= $pageInfo['seo_title'] ?? $pageInfo['title'] ?>">
    <meta name="keywords" content="koperasi, simpan pinjam, <?= strtolower($pageInfo['title']) ?>">
    <meta name="author" content="KSP Samosir">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/ksp-ui.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/ksp-enhanced.css') ?>" rel="stylesheet">
    <style>
        /* ================================
           GLOBAL LAYOUT SPACING RULES
           ================================
           - Navbar Height: 56px (fixed)
           - Sidebar Width: 250px (expanded), 70px (collapsed)
           - Main Content Top Padding: 76px (56px navbar + 20px breathing room)
           - Main Content Left Margin: 250px (expanded), 70px (collapsed), 0px (mobile)
        ================================ */
        
        /* Fixed Navbar - Base Reference */
        .navbar {
            height: 56px !important;
            min-height: 56px !important;
        }
        
        /* Sidebar Configuration */
        .sidebar { 
            position: fixed; 
            top: 56px; /* Exactly at navbar bottom */
            bottom: 0; 
            left: 0; 
            z-index: 100; 
            padding: 0; 
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); 
            width: 250px; /* Standard sidebar width */
            transition: all 0.3s ease;
            background: #212529;
        }
        
        .sidebar-sticky { 
            position: relative; 
            top: 0; 
            height: calc(100vh - 56px); /* Full height minus navbar */
            padding-top: 0.5rem; 
            padding-bottom: 0.5rem;
            padding-right: 0;
            overflow-x: hidden; 
            overflow-y: auto;
            scroll-behavior: smooth;
        }
        /* Hide scrollbar but keep scrolling */
        .sidebar-sticky::-webkit-scrollbar { width: 3px; }
        .sidebar-sticky::-webkit-scrollbar-track { background: transparent; }
        .sidebar-sticky::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 3px; }
        .sidebar-sticky::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }
        
        /* Main Content - Desktop Default */
        .main-content { 
            margin-left: 250px; /* Align with expanded sidebar */
            padding: 76px 20px 20px 20px; /* 56px navbar + 20px breathing room */
            transition: all 0.3s ease;
            min-height: calc(100vh - 56px); /* Full viewport minus navbar */
            background: #f8f9fa;
            overflow-x: hidden; /* Never show horizontal scrollbar */
            max-width: calc(100vw - 250px); /* Constrain to available width */
        }
        
        /* Tablet Breakpoint (768px - 991px) */
        @media (max-width: 991.98px) and (min-width: 768px) {
            .sidebar { 
                margin-left: -250px;
                z-index: 1040;
            }
            
            .sidebar.show { 
                margin-left: 0;
            }
            
            .main-content { 
                margin-left: 0; /* Full width on tablet */
                padding: 76px 15px 20px 15px; /* Reduced side padding for tablet */
                min-height: calc(100vh - 56px);
                max-width: 100vw;
            }
        }
        
        /* Mobile Breakpoint (< 768px) */
        @media (max-width: 767.98px) {
            .sidebar { 
                margin-left: -250px;
                z-index: 1040;
            }
            
            .sidebar.show { 
                margin-left: 0;
            }
            
            .main-content { 
                margin-left: 0; /* Full width on mobile */
                padding: 76px 10px 20px 10px; /* Minimal side padding for mobile */
                min-height: calc(100vh - 56px);
                max-width: 100vw;
            }
        }
        
        /* Collapsed Sidebar State - All Screen Sizes */
        .sidebar-collapsed .sidebar {
            width: 70px !important; /* Collapsed sidebar width */
        }
        
        .sidebar-collapsed .main-content {
            margin-left: 70px; /* Align with collapsed sidebar */
            padding: 76px 20px 20px 20px; /* Maintain consistent top padding */
            min-height: calc(100vh - 56px);
        }
        
        /* Collapsed Sidebar on Mobile/Tablet - Force Full Width */
        @media (max-width: 991.98px) {
            .sidebar-collapsed .sidebar {
                width: 0 !important;
                margin-left: -250px;
            }
            
            .sidebar-collapsed .main-content {
                margin-left: 0 !important;
                padding: 76px 15px 20px 15px; /* Use tablet padding */
            }
        }
        
        @media (max-width: 767.98px) {
            .sidebar-collapsed .main-content {
                padding: 76px 10px 20px 10px; /* Use mobile padding */
            }
        }
        
        /* Sidebar Content Adjustments for Collapsed State */
        .sidebar-collapsed .sidebar-text,
        .sidebar-collapsed .sidebar-brand-text span:not(:first-child) {
            display: none;
        }
        
        /* Additional Navbar Styles */
        .navbar { 
            z-index: 1030;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand { 
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        /* Sidebar Navigation */
        .sidebar .nav-link { 
            font-weight: 500; 
            color: rgba(255, 255, 255, 0.8); 
            padding: 0.5rem 0.75rem;
            margin: 0.125rem 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }
        
        .sidebar .nav-link:hover { 
            color: #fff; 
            background-color: rgba(255, 255, 255, 0.1); 
            transform: translateX(2px);
        }
        
        .sidebar .nav-link.active { 
            color: #fff; 
            background-color: rgba(13, 110, 253, 0.2); 
            border-left: 3px solid #0d6efd;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 0.5rem;
        }
        
        /* Remove gap between sidebar and content */
        .sidebar {
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .main-content {
            border-left: none; /* Remove any border that might create gap */
        }
        
        /* Mobile Navigation */
        .mobile-nav .offcanvas {
            width: 280px;
        }
        
        /* Toast Container */
        .toast-container {
            z-index: 1060;
        }
        
        /* Animations */
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Cards */
        .ksp-stats-card { 
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; 
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .ksp-stats-card:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); 
        }
        
        /* Responsive Tables */
        .table-responsive {
            border-radius: 0.375rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        /* Form Enhancements */
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: #0d6efd;
        }
        
        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1050;
        }
        
        /* Print Styles */
        @media print {
            .sidebar, .navbar {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                padding-top: 0 !important;
            }
        }
    </style>
</head>
<body>
<!-- Mobile Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler d-lg-none" type="button" data-nav-toggle="mobile" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Desktop Toggle Button -->
        <button class="btn btn-outline-light btn-sm d-none d-lg-inline-block me-3" 
                data-nav-toggle="desktop" type="button" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        
        <!-- Brand -->
        <a class="navbar-brand sidebar-brand-text" href="<?= base_url('dashboard') ?>">
            <i class="bi bi-building me-2"></i>
            <span class="sidebar-text">KSP Samosir</span>
        </a>
        
        <!-- User Menu (Right side) -->
        <div class="navbar-nav ms-auto">
            <!-- Notifications -->
            <div class="nav-item dropdown">
                <a class="nav-link text-white position-relative" href="#" id="notificationDropdown" 
                   role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Notifikasi</h6></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="bi bi-person-plus text-success me-2"></i>
                        Anggota baru terdaftar
                        <small class="text-muted d-block">5 menit yang lalu</small>
                    </a></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="bi bi-cash-stack text-info me-2"></i>
                        Simpanan baru ditambahkan
                        <small class="text-muted d-block">1 jam yang lalu</small>
                    </a></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        Pinjaman perlu persetujuan
                        <small class="text-muted d-block">2 jam yang lalu</small>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="<?= base_url('notifications') ?>">
                        Lihat semua notifikasi
                    </a></li>
                </ul>
            </div>
            
            <!-- User Dropdown -->
            <div class="nav-item dropdown">
                <a class="nav-link text-white dropdown-toggle d-flex align-items-center" 
                   href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" 
                   aria-expanded="false">
                    <i class="bi bi-person-circle me-2"></i>
                    <span class="d-none d-md-inline-block">
                        <?= $user['full_name'] ?? 'Pengguna' ?>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">
                        <i class="bi bi-shield-check me-2"></i>
                        <?= ucfirst($role ?? '-') ?>
                    </h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= base_url('profile') ?>">
                        <i class="bi bi-person me-2"></i>Profil Saya
                    </a></li>
                    <li><a class="dropdown-item" href="<?= base_url('settings') ?>">
                        <i class="bi bi-gear me-2"></i>Pengaturan
                    </a></li>
                    <li><a class="dropdown-item" href="<?= base_url('help') ?>">
                        <i class="bi bi-question-circle me-2"></i>Bantuan
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">
                        <i class="bi bi-box-arrow-right me-2"></i>Keluar
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse text-white">
            <div class="position-sticky px-2 sidebar-sticky">
                <div class="mb-2 mt-1 px-2 py-1">
                    <div class="fw-bold sidebar-brand-text" style="font-size:0.85rem">KSP Samosir</div>
                    <small class="sidebar-text text-white-50" style="font-size:0.7rem"><?= $user['full_name'] ?? 'Pengguna' ?> (<?= $role ?? '-' ?>)</small>
                </div>
                <ul class="nav flex-column">
                    <!-- Dynamic sidebar from database -->
                    <?= renderSidebarMenus() ?>

                    <!-- Logout (All Roles) -->
                    <li class="nav-item mt-3">
                        <a class="nav-link text-danger" href="<?= base_url('logout') ?>">
                            <i class="bi bi-box-arrow-right me-2"></i>Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="main-content" id="main-content">
            <!-- Loading Screen -->
            <div id="loading-screen" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading KSP Samosir...</div>
                </div>
            </div>

            <?= $content ?? '<div class="alert alert-warning">Content not loaded</div>' ?>
        </main>
    </div>
</div>

<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous"></script>

<!-- Bootstrap 5.3 Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js" crossorigin="anonymous" defer></script>

<!-- KSP Unified UI Library (modals, toasts, alerts, tables, forms, AJAX) -->
<script src="<?= base_url('assets/js/ksp-ui.js') ?>"></script>

<?php 
// Include JavaScript helpers
if (function_exists('generateJsHelpers')) echo generateJsHelpers(); 
?>

<?php if (file_exists(APP_PATH . '/templates/modals.php')) include APP_PATH . '/templates/modals.php'; ?>

<!-- Sidebar & App Init -->
<script>
document.getElementById('loading-screen').style.display = 'none';
$(function() {
    var sb = document.getElementById('sidebarMenu');
    if (!sb) return;

    // Close mobile sidebar on nav click
    $(sb).find('a.nav-link').on('click', function() {
        if (window.innerWidth < 768 && sb.classList.contains('show')) {
            bootstrap.Collapse.getOrCreateInstance(sb).hide();
        }
    });

    // Escape key closes sidebar
    $(sb).on('keydown', function(e) {
        if (e.key === 'Escape') bootstrap.Collapse.getOrCreateInstance(sb).hide();
    });

    // Ensure desktop sidebar visible on resize
    $(window).on('resize', function() {
        if (window.innerWidth >= 768) sb.classList.add('show');
    });

    // Touch swipe for mobile sidebar
    var sx = 0;
    document.addEventListener('touchstart', function(e) { sx = e.changedTouches[0].screenX; }, {passive: true});
    document.addEventListener('touchend', function(e) {
        var d = e.changedTouches[0].screenX - sx;
        if (Math.abs(d) > 50 && window.innerWidth < 768) {
            bootstrap.Collapse.getOrCreateInstance(sb)[d > 0 ? 'show' : 'hide']();
        }
    }, {passive: true});

    // === Scroll active nav-link to center of sidebar ===
    var sticky = sb.querySelector('.sidebar-sticky');
    var active = sb.querySelector('.nav-link.active');
    if (sticky && active) {
        var stickyH = sticky.clientHeight;
        var activeTop = active.offsetTop;
        var activeH = active.offsetHeight;
        // Scroll so that active item is vertically centered
        var scrollTo = activeTop - (stickyH / 2) + (activeH / 2);
        // Clamp: don't scroll past top or bottom
        var maxScroll = sticky.scrollHeight - stickyH;
        scrollTo = Math.max(0, Math.min(scrollTo, maxScroll));
        sticky.scrollTop = scrollTo;
    }

    // Clear stale service workers
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(r) { r.forEach(function(sw) { sw.unregister(); }); });
    }
});
</script>

</body>
</html>