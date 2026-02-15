<?php
require_once __DIR__ . '/../../../app/helpers/FormatHelper.php';
require_once __DIR__ . '/spa-router.php';
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
    <title>KSP Samosir - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 48px 0 0; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); }
        .sidebar-sticky { position: relative; top: 0; height: calc(100vh - 48px); padding-top: .5rem; overflow-x: hidden; overflow-y: auto; }
        main { padding-top: 48px; }
        @media (max-width: 767.98px) { .sidebar { top: 5rem; } main { padding-top: 0; } }
        .navbar-brand { padding-top: .75rem; padding-bottom: .75rem; font-size: 1rem; background-color: rgba(0, 0, 0, .25); box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25); }
        .sidebar .nav-link { font-weight: 500; color: #333; transition: all 0.3s ease; cursor: pointer; }
        .sidebar .nav-link:hover { color: #fff; background-color: rgba(255, 255, 255, 0.1); border-radius: 4px; }
        .sidebar .nav-link.active { color: #007bff; background-color: rgba(0, 123, 255, 0.15); border-left: 3px solid #007bff; }
        .ksp-stats-card { transition: transform 0.2s ease-in-out; }
        .ksp-stats-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .spa-loading { 
            position: absolute; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
        .content-fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<!-- Mobile Navbar -->
<nav class="navbar navbar-dark bg-dark d-md-none fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#" data-page="dashboard">KSP Samosir</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse text-white">
            <div class="position-sticky pt-3 px-3 sidebar-sticky">
                <div class="mb-4">
                    <div class="fw-bold">KSP Samosir</div>
                    <small><?= $user['full_name'] ?? 'Pengguna' ?> (<?= $role ?? '-' ?>)</small>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white spa-link active" data-page="dashboard">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    </li>

                    <!-- Admin & Staff Menu Items -->
                    <?php if (hasRole(['admin','staff'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="accounting">
                                <i class="bi bi-calculator me-2"></i>Akuntansi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="analytics/dashboard">
                                <i class="bi bi-graph-up me-2"></i>Analitik
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="ai_credit">
                                <i class="bi bi-robot me-2"></i>AI Kredit Skor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="digital_documents">
                                <i class="bi bi-file-earmark-text me-2"></i>Dokumen Digital
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="notifications">
                                <i class="bi bi-bell me-2"></i>Notifikasi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="anggota">
                                <i class="bi bi-people me-2"></i>Anggota
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="simpanan">
                                <i class="bi bi-piggy-bank me-2"></i>Simpanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="pinjaman">
                                <i class="bi bi-cash-stack me-2"></i>Pinjaman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="inventory">
                                <i class="bi bi-box me-2"></i>Inventaris
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="settings">
                                <i class="bi bi-gear me-2"></i>Pengaturan
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Member Menu Items -->
                    <?php if (hasRole(['member'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="member">
                                <i class="bi bi-person-circle me-2"></i>Portal Anggota
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="simpanan">
                                <i class="bi bi-piggy-bank me-2"></i>Simpanan Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white spa-link" data-page="pinjaman">
                                <i class="bi bi-cash-stack me-2"></i>Pinjaman Saya
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Logout -->
                    <li class="nav-item mt-3">
                        <a class="nav-link text-white spa-link" data-page="logout">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- SPA Content Container -->
            <div id="spa-content" class="content-fade-in">
                <!-- Initial content will be loaded here -->
                <div class="spa-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading...</div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous"></script>
<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<!-- SPA JavaScript -->
<script>
/**
 * KSP Samosir SPA Application
 * Single Page Application with Dynamic Content Loading
 */
class KSPSPA {
    constructor() {
        this.currentPage = 'dashboard';
        this.contentContainer = document.getElementById('spa-content');
        this.loadingIndicator = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadInitialContent();
        this.updateActiveNavigation();
    }

    bindEvents() {
        // Bind navigation clicks
        document.querySelectorAll('.spa-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.getAttribute('data-page');
                if (page === 'logout') {
                    window.location.href = '<?= base_url('auth/logout') ?>';
                    return;
                }
                this.loadPage(page);
            });
        });

        // Bind browser back/forward
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.page) {
                this.loadPage(e.state.page, false);
            }
        });
    }

    async loadPage(page, addToHistory = true) {
        if (page === this.currentPage) return;

        try {
            this.showLoading();
            this.currentPage = page;

            const response = await fetch('<?= base_url('app/views/layouts/spa-router.php') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=load_content&page=${encodeURIComponent(page)}`
            });

            const data = await response.json();

            if (data.success) {
                this.contentContainer.innerHTML = data.content;
                this.contentContainer.classList.add('content-fade-in');
                this.updateActiveNavigation();
                this.updatePageTitle(data.title);
                
                if (addToHistory) {
                    history.pushState({page: page}, '', `#${page}`);
                }

                // Re-initialize any JavaScript components in the new content
                this.initializePageComponents();
            } else {
                throw new Error('Failed to load content');
            }
        } catch (error) {
            console.error('SPA Error:', error);
            this.contentContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading page. Please try again.
                </div>
            `;
        } finally {
            this.hideLoading();
        }
    }

    loadInitialContent() {
        // Load initial page based on hash or default to dashboard
        const hash = window.location.hash.substring(1);
        const initialPage = hash || 'dashboard';
        this.loadPage(initialPage, false);
    }

    showLoading() {
        if (!this.loadingIndicator) {
            this.loadingIndicator = document.createElement('div');
            this.loadingIndicator.className = 'spa-loading';
            this.loadingIndicator.innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading...</div>
            `;
        }
        this.contentContainer.appendChild(this.loadingIndicator);
    }

    hideLoading() {
        if (this.loadingIndicator && this.loadingIndicator.parentNode) {
            this.loadingIndicator.parentNode.removeChild(this.loadingIndicator);
        }
    }

    updateActiveNavigation() {
        document.querySelectorAll('.spa-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-page') === this.currentPage) {
                link.classList.add('active');
            }
        });
    }

    updatePageTitle(title) {
        document.title = `${title} - KSP Samosir`;
    }

    initializePageComponents() {
        // Re-initialize Bootstrap components
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });

        // Re-initialize any custom scripts
        if (typeof window.initializePageScripts === 'function') {
            window.initializePageScripts();
        }
    }
}

// Initialize SPA when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.KSPSPA = new KSPSPA();
    
    // Global function for manual page loading
    window.loadSPAPage = function(page) {
        if (window.KSPSPA) {
            window.KSPSPA.loadPage(page);
        }
    };
});

// Handle mobile sidebar close after navigation
document.addEventListener('DOMContentLoaded', function() {
    const sidebarMenu = document.getElementById('sidebarMenu');
    
    document.querySelectorAll('.spa-link').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768 && sidebarMenu.classList.contains('show')) {
                const collapse = bootstrap.Collapse.getOrCreateInstance(sidebarMenu);
                collapse.hide();
            }
        });
    });
});
</script>
</body>
</html>
