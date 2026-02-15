<?php
// Simple Front Controller untuk testing
class SimpleFrontController {
    
    public function __construct() {
        // Start session only if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function getBasePath() {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir === '/' || $scriptDir === '.') {
            return '';
        }
        return rtrim($scriptDir, '/');
    }

    private function appUrl($path = '') {
        $base = $this->getBasePath();
        $path = ltrim($path, '/');
        if ($path === '') {
            return $base === '' ? '/' : $base . '/';
        }
        return ($base === '' ? '' : $base) . '/' . $path;
    }

    private function redirect($path) {
        header('Location: ' . $this->appUrl($path));
        exit;
    }
    
    public function dispatch() {
        $requestUri = $_SERVER['REQUEST_URI'];
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Remove base path dynamically
        $basePath = $this->getBasePath();
        if ($basePath !== '' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        $cleanPath = trim($path, '/');
        
        // Route handling
        if ($cleanPath === 'login') {
            $this->showLogin();
        } elseif ($cleanPath === 'logout') {
            $this->handleLogout();
        } elseif ($cleanPath === 'dashboard' || $cleanPath === '') {
            // Check if logged in
            if (!$this->isLoggedIn()) {
                $this->redirect('login');
            }
            // Redirect to main router for consistent layout
            $targetUrl = $this->appUrl('dashboard');
            header("Location: $targetUrl");
            exit;
        } else {
            // Check if logged in for other pages
            if (!$this->isLoggedIn()) {
                $this->redirect('login');
            }
            // Forward to main router (index.php) for actual module handling
            // Preserve current session and redirect to main app
            $targetUrl = $this->appUrl($cleanPath);
            header("Location: $targetUrl");
            exit;
        }
    }
    
    private function showLogin() {
        // Handle login POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Development credentials from auth/login.php
            if ($username === 'admin' && $password === 'admin123') {
                $_SESSION['user'] = [
                    'id' => 1,
                    'name' => 'Administrator',
                    'username' => 'admin',
                    'role' => 'admin',
                    'avatar' => $this->appUrl('assets/images/default-avatar.png')
                ];
                $this->redirect('dashboard');
            } elseif ($username === 'manager' && $password === 'manager123') {
                $_SESSION['user'] = [
                    'id' => 2,
                    'name' => 'Manager',
                    'username' => 'manager',
                    'role' => 'manager',
                    'avatar' => $this->appUrl('assets/images/default-avatar.png')
                ];
                $this->redirect('dashboard');
            } elseif ($username === 'staff' && $password === 'staff123') {
                $_SESSION['user'] = [
                    'id' => 3,
                    'name' => 'Staff User',
                    'username' => 'staff',
                    'role' => 'staff',
                    'avatar' => $this->appUrl('assets/images/default-avatar.png')
                ];
                $this->redirect('dashboard');
            } elseif ($username === 'anggota' && $password === 'anggota123') {
                $_SESSION['user'] = [
                    'id' => 4,
                    'name' => 'Anggota',
                    'username' => 'anggota',
                    'role' => 'member',
                    'avatar' => $this->appUrl('assets/images/default-avatar.png')
                ];
                $this->redirect('dashboard');
            } else {
                $error = 'Username atau password salah!';
            }
        }
        
        // If already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }
        
        $error = $error ?? '';
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>KSP Samosir - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: #2c3e50;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-bank" style="font-size: 3rem;"></i>
                <h3 class="mt-3">KSP Samosir</h3>
                <p class="mb-0">Sistem Informasi Koperasi</p>
            </div>
            <div class="login-body">';
        
        if ($error) {
            $html .= '<div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> ' . htmlspecialchars($error) . '
                      </div>';
        }
        
        $html .= '<form method="post" action="' . htmlspecialchars($this->appUrl('login')) . '">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person"></i> Username
                        </label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </form>
                <div class="text-center mt-3">
                    <small class="text-muted">
                        Demo Development Credentials:
                    </small>
                </div>
                <div class="text-center">
                    <small class="badge bg-danger">Admin: admin/admin123</small>
                </div>
                <div class="text-center mt-1">
                    <small class="badge bg-primary">Manager: manager/manager123</small>
                </div>
                <div class="text-center mt-1">
                    <small class="badge bg-success">Staff: staff/staff123</small>
                </div>
                <div class="text-center mt-1">
                    <small class="badge bg-warning">Anggota: anggota/anggota123</small>
                </div>
                
                <!-- Quick Fill Buttons -->
                <div class="mt-3">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="fillCreds(\'admin\', \'admin123\')">
                            <i class="bi bi-person-fill"></i> Admin
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillCreds(\'manager\', \'manager123\')">
                            <i class="bi bi-person-badge"></i> Manager
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="fillCreds(\'staff\', \'staff123\')">
                            <i class="bi bi-person"></i> Staff
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="fillCreds(\'anggota\', \'anggota123\')">
                            <i class="bi bi-people"></i> Anggota
                        </button>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <small class="text-muted">
                        © 2026 KSP Samosir. All rights reserved.
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-fill credentials function
        function fillCreds(username, password) {
            document.getElementById(\'username\').value = username;
            document.getElementById(\'password\').value = password;
            
            // Visual feedback
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = \'<i class="bi bi-check-circle"></i> Filled!\';
            btn.classList.add(\'btn-success\');
            btn.classList.remove(\'btn-outline-danger\', \'btn-outline-primary\', \'btn-outline-success\', \'btn-outline-warning\');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove(\'btn-success\');
                btn.classList.add(\'btn-outline-danger\', \'btn-outline-primary\', \'btn-outline-success\', \'btn-outline-warning\');
            }, 1000);
            
            // Auto submit after fill
            setTimeout(() => {
                document.querySelector(\'form\').submit();
            }, 1500);
        }
        
        // Auto-focus on page load
        document.addEventListener(\'DOMContentLoaded\', function() {
            document.getElementById(\'username\').focus();
        });
    </script>
</body>
</html>';
        
        echo $html;
    }
    
    private function handleLogout() {
        session_destroy();
        // Clear session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        $this->redirect('login');
    }
    
    private function isLoggedIn() {
        return isset($_SESSION['user']) && !empty($_SESSION['user']);
    }
    
    
    private function showPage($slug, $title, $description) {
        $user = $_SESSION['user'];
        $role = $user['role'] ?? 'member';

        ob_start();
        ?>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
            <h1 class="h2 page-title" id="page-title"><?= htmlspecialchars($title) ?></h1>
            <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
                <span class="badge bg-secondary"><?= htmlspecialchars($role) ?></span>
            </div>
        </div>
        <p class="text-muted"><?= htmlspecialchars($description) ?></p>
        <div class="alert alert-info"><i class="bi bi-info-circle"></i> Halaman <?= htmlspecialchars($title) ?> sedang dalam pengembangan.</div>
        <?php
        $content = ob_get_clean();

        echo $this->renderAppLayout($title, $content, $role, strtolower($slug), $user);
    }

    private function buildQuickActions($role) {
        $buttons = [];

        if (in_array($role, ['admin', 'staff'], true)) {
            $buttons[] = '<a href="' . htmlspecialchars($this->appUrl('anggota')) . '" class="btn btn-outline-primary"><i class="bi bi-person-plus"></i> Tambah Anggota</a>';
            $buttons[] = '<a href="' . htmlspecialchars($this->appUrl('simpanan')) . '" class="btn btn-outline-success"><i class="bi bi-plus-circle"></i> Tambah Simpanan</a>';
        }

        if ($role === 'member') {
            $buttons[] = '<a href="' . htmlspecialchars($this->appUrl('permohonan')) . '" class="btn btn-outline-warning"><i class="bi bi-plus-circle"></i> Ajukan Pinjaman</a>';
        }

        $buttons[] = '<a href="' . htmlspecialchars($this->appUrl('settings')) . '" class="btn btn-outline-secondary"><i class="bi bi-gear"></i> Pengaturan</a>';

        return implode('', $buttons);
    }

    private function renderSidebar($role, $activePage) {
        $items = [
            ['slug' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'section' => null],
        ];

        if (in_array($role, ['admin', 'staff', 'manager'], true)) {
            // Manajemen
            $items[] = ['slug' => 'anggota', 'icon' => 'bi-people', 'label' => 'Anggota', 'section' => 'Manajemen'];
            $items[] = ['slug' => 'simpanan', 'icon' => 'bi-piggy-bank', 'label' => 'Simpanan', 'section' => 'Manajemen'];
            $items[] = ['slug' => 'pinjaman', 'icon' => 'bi-cash-stack', 'label' => 'Pinjaman', 'section' => 'Manajemen'];
        }

        if ($role === 'admin') {
            // Akuntansi & Keuangan
            $items[] = ['slug' => 'accounting', 'icon' => 'bi-calculator', 'label' => 'Akuntansi', 'section' => 'Akuntansi & Keuangan'];
            $items[] = ['slug' => 'invoice', 'icon' => 'bi-receipt', 'label' => 'Invoice', 'section' => 'Akuntansi & Keuangan'];
            $items[] = ['slug' => 'shu', 'icon' => 'bi-graph-up', 'label' => 'SHU', 'section' => 'Akuntansi & Keuangan'];
            // Layanan & Operasional
            $items[] = ['slug' => 'customer_service', 'icon' => 'bi-headset', 'label' => 'Customer Service', 'section' => 'Layanan & Operasional'];
            $items[] = ['slug' => 'produk', 'icon' => 'bi-box', 'label' => 'Produk', 'section' => 'Layanan & Operasional'];
            $items[] = ['slug' => 'penjualan', 'icon' => 'bi-cart', 'label' => 'Penjualan', 'section' => 'Layanan & Operasional'];
            $items[] = ['slug' => 'pelanggan', 'icon' => 'bi-person-badge', 'label' => 'Pelanggan', 'section' => 'Layanan & Operasional'];
            $items[] = ['slug' => 'inventory', 'icon' => 'bi-archive', 'label' => 'Inventory', 'section' => 'Layanan & Operasional'];
            $items[] = ['slug' => 'asset', 'icon' => 'bi-building', 'label' => 'Asset', 'section' => 'Layanan & Operasional'];
            $items[] = ['slug' => 'payment', 'icon' => 'bi-credit-card', 'label' => 'Pembayaran', 'section' => 'Layanan & Operasional'];
            $items[] = ['slug' => 'shipping', 'icon' => 'bi-truck', 'label' => 'Pengiriman', 'section' => 'Layanan & Operasional'];
            // Teknis & Pengawasan
            $items[] = ['slug' => 'admin', 'icon' => 'bi-gear-fill', 'label' => 'Admin', 'section' => 'Teknis & Pengawasan'];
            $items[] = ['slug' => 'pengawas', 'icon' => 'bi-shield-check', 'label' => 'Pengawas', 'section' => 'Teknis & Pengawasan'];
            $items[] = ['slug' => 'logs', 'icon' => 'bi-file-text', 'label' => 'Logs', 'section' => 'Teknis & Pengawasan'];
            $items[] = ['slug' => 'backup', 'icon' => 'bi-cloud-download', 'label' => 'Backup', 'section' => 'Teknis & Pengawasan'];
            $items[] = ['slug' => 'monitoring', 'icon' => 'bi-activity', 'label' => 'Monitoring', 'section' => 'Teknis & Pengawasan'];
            $items[] = ['slug' => 'risk', 'icon' => 'bi-exclamation-triangle', 'label' => 'Manajemen Risiko', 'section' => 'Teknis & Pengawasan'];
            $items[] = ['slug' => 'blockchain', 'icon' => 'bi-link-45deg', 'label' => 'Blockchain', 'section' => 'Teknis & Pengawasan'];
            // AI & Digital
            $items[] = ['slug' => 'ai', 'icon' => 'bi-cpu', 'label' => 'AI Intelligence', 'section' => 'AI & Digital'];
            $items[] = ['slug' => 'ai_credit', 'icon' => 'bi-robot', 'label' => 'AI Credit Scoring', 'section' => 'AI & Digital'];
            $items[] = ['slug' => 'digital_documents', 'icon' => 'bi-file-earmark-pdf', 'label' => 'Dokumen Digital', 'section' => 'AI & Digital'];
            // Lainnya
            $items[] = ['slug' => 'rapat', 'icon' => 'bi-people-fill', 'label' => 'Rapat', 'section' => 'Lainnya'];
            $items[] = ['slug' => 'learning', 'icon' => 'bi-book', 'label' => 'Learning', 'section' => 'Lainnya'];
            $items[] = ['slug' => 'tax', 'icon' => 'bi-receipt-cutoff', 'label' => 'Pajak', 'section' => 'Lainnya'];
            $items[] = ['slug' => 'payroll', 'icon' => 'bi-cash-coin', 'label' => 'Payroll', 'section' => 'Lainnya'];
        }

        if ($role === 'member') {
            $items[] = ['slug' => 'simpanan', 'icon' => 'bi-piggy-bank', 'label' => 'Simpanan Saya', 'section' => 'Layanan Saya'];
            $items[] = ['slug' => 'pinjaman', 'icon' => 'bi-cash-stack', 'label' => 'Pinjaman Saya', 'section' => 'Layanan Saya'];
            $items[] = ['slug' => 'transaksi', 'icon' => 'bi-receipt', 'label' => 'Riwayat Transaksi', 'section' => 'Layanan Saya'];
        }

        $items[] = ['slug' => 'laporan', 'icon' => 'bi-file-earmark-text', 'label' => 'Laporan', 'section' => 'Lainnya'];
        $items[] = ['slug' => 'settings', 'icon' => 'bi-gear', 'label' => 'Pengaturan', 'section' => 'Lainnya'];

        ob_start();
        ?>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-brand"><i class="bi bi-bank me-2"></i> KSP Samosir</div>
            <nav class="nav flex-column">
                <?php $currentSection = null; ?>
                <?php foreach ($items as $item): ?>
                    <?php if ($item['section'] !== $currentSection && $item['section'] !== null): ?>
                        <?php $currentSection = $item['section']; ?>
                        <div class="sidebar-section"><?= htmlspecialchars($currentSection) ?></div>
                    <?php endif; ?>
                    <a class="nav-link text-white <?= $activePage === $item['slug'] ? 'active' : '' ?>" href="<?= htmlspecialchars($this->appUrl($item['slug'])) ?>">
                        <i class="bi <?= htmlspecialchars($item['icon']) ?>"></i> <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderAppLayout($pageTitle, $content, $role, $activePage, $user) {
        $sidebar = $this->renderSidebar($role, $activePage);

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>KSP Samosir - <?= htmlspecialchars($pageTitle) ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f8f9fa; }
                .navbar { z-index: 1030; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .sidebar { position: fixed; top: 56px; left: 0; height: calc(100vh - 56px); width: 250px; background-color: #2c3e50; overflow-y: auto; z-index: 1020; }
                .sidebar .nav-link { font-weight: 500; color: rgba(255,255,255,0.8); padding: 0.75rem 1rem; margin: 0.25rem 0.5rem; border-radius: 0.375rem; transition: all 0.3s ease; display: flex; align-items: center; }
                .sidebar .nav-link:hover { color: #fff; background-color: rgba(255,255,255,0.1); transform: translateX(2px); }
                .sidebar .nav-link.active { color: #fff; background-color: rgba(13,110,253,0.2); border-left: 3px solid #0d6efd; }
                .sidebar .nav-link i { width: 20px; text-align: center; margin-right: 0.5rem; }
                .main-content { margin-left: 250px; padding: 76px 20px 20px 20px; min-height: calc(100vh - 56px); }
                .sidebar-brand { padding: 1rem; background-color: #34495e; color: #fff; text-align: center; font-weight: 600; }
                .sidebar-section { padding: 0.5rem 1rem; color: rgba(255,255,255,0.6); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 1rem; }
                @media (max-width: 991.98px) {
                    .sidebar { margin-left: -250px; transition: margin-left 0.3s ease; }
                    .sidebar.show { margin-left: 0; }
                    .main-content { margin-left: 0; padding: 76px 15px 20px 15px; }
                }
            </style>
        </head>
        <body>
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
                <div class="container-fluid">
                    <button class="navbar-toggler d-lg-none" type="button" id="sidebarToggler"><span class="navbar-toggler-icon"></span></button>
                    <a class="navbar-brand" href="<?= htmlspecialchars($this->appUrl('dashboard')) ?>"><i class="bi bi-bank"></i> KSP Samosir</a>
                    <div class="navbar-nav ms-auto">
                        <div class="nav-item dropdown">
                            <a class="nav-link text-white dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i><span class="d-none d-md-inline-block"><?= htmlspecialchars($user['name']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header"><i class="bi bi-shield-check me-2"></i><?= htmlspecialchars(ucfirst($role)) ?></h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= htmlspecialchars($this->appUrl('profile')) ?>"><i class="bi bi-person me-2"></i> Profil</a></li>
                                <li><a class="dropdown-item" href="<?= htmlspecialchars($this->appUrl('settings')) ?>"><i class="bi bi-gear me-2"></i> Pengaturan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= htmlspecialchars($this->appUrl('logout')) ?>"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <?= $sidebar ?>

            <div class="main-content">
                <?= $content ?>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const sidebar = document.getElementById('sidebar');
                    const toggler = document.getElementById('sidebarToggler');
                    if (sidebar && toggler) {
                        toggler.addEventListener('click', function () {
                            sidebar.classList.toggle('show');
                        });
                    }
                });
            </script>
        </body>
        </html>
        <?php

        return ob_get_clean();
    }
}

// Initialize and dispatch
$controller = new SimpleFrontController();
$controller->dispatch();
?>
