<?php
/**
 * KSP Front Controller - SATU CONTROLLER UNTUK SEMUA HALAMAN
 * 
 * Front controller tunggal yang menangani SEMUA request:
 * - Routing otomatis
 * - Permission checking
 * - Page rendering dengan unified layout
 * - Error handling
 * 
 * @author KSP Samosir Development Team
 * @version 2.0.0
 */

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Error reporting
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Force production mode untuk stability
define('APP_ENV', 'development');

// Define constants
define('KSP_ROOT', __DIR__ . '/../');
define('KSP_APP', true);
define('KSP_VERSION', '2.0.0');

// Include core files
require_once KSP_ROOT . 'app/core/KSPPageRenderer.php';
require_once KSP_ROOT . 'config/database.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize session user jika belum ada
if (!isset($_SESSION['user'])) {
    // Set default guest user untuk development
    $_SESSION['user'] = [
        'id' => 1,
        'name' => 'Admin User',
        'email' => 'admin@ksp-samosir.com',
        'role' => 'admin',
        'avatar' => base_url('assets/images/default-avatar.png')
    ];
}

// Helper functions
function get_base_path() {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($scriptDir === '/' || $scriptDir === '.') {
        return '';
    }
    return rtrim($scriptDir, '/');
}

function base_url($path = '') {
    $baseUrl = get_base_path();
    $path = ltrim($path, '/');
    if ($path === '') {
        return $baseUrl === '' ? '/' : $baseUrl . '/';
    }
    return ($baseUrl === '' ? '' : $baseUrl) . '/' . $path;
}

function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

function abort($code = 404, $message = 'Page not found') {
    http_response_code($code);
    echo renderErrorPage($code, $message);
    exit;
}

function renderErrorPage($code, $message) {
    $renderer = KSPPageRenderer::getInstance();
    $content = '
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="error-code display-1 text-primary">' . $code . '</div>
                    <h2 class="mb-4">' . htmlspecialchars($message) . '</h2>
                    <p class="text-muted mb-4">
                        Maaf, halaman yang Anda cari tidak ditemukan atau Anda tidak memiliki akses.
                    </p>
                    <a href="' . base_url('dashboard') . '" class="btn btn-primary">
                        <i class="bi bi-house"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    ';
    
    return $renderer->render($content, [
        'show_sidebar' => false,
        'show_breadcrumb' => false
    ]);
}

// Route definitions
$routes = [
    // Dashboard
    'dashboard' => [
        'controller' => 'DashboardController',
        'action' => 'index',
        'title' => 'Dashboard',
        'roles' => ['admin', 'staff', 'member', 'customer_service', 'invoice', 'accounting']
    ],
    
    // Authentication
    'login' => [
        'controller' => 'AuthController',
        'action' => 'login',
        'title' => 'Login',
        'roles' => ['guest']
    ],
    'logout' => [
        'controller' => 'AuthController',
        'action' => 'logout',
        'title' => 'Logout',
        'roles' => ['admin', 'staff', 'member', 'customer_service', 'invoice', 'accounting']
    ],
    
    // Anggota
    'anggota' => [
        'controller' => 'AnggotaController',
        'action' => 'index',
        'title' => 'Anggota',
        'roles' => ['admin', 'staff']
    ],
    'anggota/create' => [
        'controller' => 'AnggotaController',
        'action' => 'create',
        'title' => 'Tambah Anggota',
        'roles' => ['admin', 'staff']
    ],
    'anggota/edit' => [
        'controller' => 'AnggotaController',
        'action' => 'edit',
        'title' => 'Edit Anggota',
        'roles' => ['admin', 'staff']
    ],
    
    // Simpanan
    'simpanan' => [
        'controller' => 'SimpananController',
        'action' => 'index',
        'title' => 'Simpanan',
        'roles' => ['admin', 'staff', 'member']
    ],
    'simpanan/create' => [
        'controller' => 'SimpananController',
        'action' => 'create',
        'title' => 'Tambah Simpanan',
        'roles' => ['admin', 'staff']
    ],
    
    // Pinjaman
    'pinjaman' => [
        'controller' => 'PinjamanController',
        'action' => 'index',
        'title' => 'Pinjaman',
        'roles' => ['admin', 'staff', 'member']
    ],
    'pinjaman/create' => [
        'controller' => 'PinjamanController',
        'action' => 'create',
        'title' => 'Ajukan Pinjaman',
        'roles' => ['admin', 'staff', 'member']
    ],
    
    // Accounting
    'accounting' => [
        'controller' => 'AccountingController',
        'action' => 'index',
        'title' => 'Akuntansi',
        'roles' => ['admin', 'accounting']
    ],
    'accounting/jurnal' => [
        'controller' => 'AccountingController',
        'action' => 'jurnal',
        'title' => 'Jurnal',
        'roles' => ['admin', 'accounting']
    ],
    'accounting/neraca' => [
        'controller' => 'AccountingController',
        'action' => 'neraca',
        'title' => 'Neraca',
        'roles' => ['admin', 'accounting']
    ],
    'accounting/laba_rugi' => [
        'controller' => 'AccountingController',
        'action' => 'labaRugi',
        'title' => 'Laba Rugi',
        'roles' => ['admin', 'accounting']
    ],
    
    // SHU
    'shu' => [
        'controller' => 'ShuController',
        'action' => 'index',
        'title' => 'SHU',
        'roles' => ['admin', 'accounting']
    ],
    'shu/calculate' => [
        'controller' => 'ShuController',
        'action' => 'calculate',
        'title' => 'Hitung SHU',
        'roles' => ['admin', 'accounting']
    ],
    'shu/distribute' => [
        'controller' => 'ShuController',
        'action' => 'distribute',
        'title' => 'Distribusi SHU',
        'roles' => ['admin', 'accounting']
    ],
    'shu/reports' => [
        'controller' => 'ShuController',
        'action' => 'reports',
        'title' => 'Laporan SHU',
        'roles' => ['admin', 'accounting']
    ],
    
    // Penjualan
    'penjualan' => [
        'controller' => 'PenjualanController',
        'action' => 'index',
        'title' => 'Penjualan',
        'roles' => ['admin', 'staff']
    ],
    'penjualan/promos' => [
        'controller' => 'PenjualanController',
        'action' => 'promos',
        'title' => 'Promo',
        'roles' => ['admin', 'staff']
    ],
    'penjualan/commissions' => [
        'controller' => 'PenjualanController',
        'action' => 'commissions',
        'title' => 'Komisi',
        'roles' => ['admin', 'staff']
    ],
    
    // Customer Service
    'customer_service' => [
        'controller' => 'CustomerServiceController',
        'action' => 'index',
        'title' => 'Customer Service',
        'roles' => ['admin', 'customer_service']
    ],
    'customer_service/tickets' => [
        'controller' => 'CustomerServiceController',
        'action' => 'tickets',
        'title' => 'Tickets',
        'roles' => ['admin', 'customer_service']
    ],
    
    // Invoice
    'invoice' => [
        'controller' => 'InvoiceController',
        'action' => 'index',
        'title' => 'Invoice',
        'roles' => ['admin', 'invoice']
    ],
    'invoice/customer' => [
        'controller' => 'InvoiceController',
        'action' => 'customerInvoices',
        'title' => 'Invoice Customer',
        'roles' => ['admin', 'invoice']
    ],
    'invoice/supplier' => [
        'controller' => 'InvoiceController',
        'action' => 'supplierInvoices',
        'title' => 'Invoice Supplier',
        'roles' => ['admin', 'invoice']
    ],
    
    // Settings
    'settings' => [
        'controller' => 'SettingsController',
        'action' => 'index',
        'title' => 'Pengaturan',
        'roles' => ['admin']
    ],
    
    // Profile
    'profile' => [
        'controller' => 'ProfileController',
        'action' => 'index',
        'title' => 'Profil Saya',
        'roles' => ['admin', 'staff', 'member', 'customer_service', 'invoice', 'accounting']
    ],
    
    // Reports
    'laporan' => [
        'controller' => 'LaporanController',
        'action' => 'index',
        'title' => 'Laporan',
        'roles' => ['admin', 'staff']
    ],
    
    // API Routes
    'api/navigation' => [
        'controller' => 'ApiController',
        'action' => 'navigation',
        'title' => 'Navigation API',
        'roles' => ['admin', 'staff', 'member', 'customer_service', 'invoice', 'accounting'],
        'api' => true
    ]
];

// Get current route
$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'];

// Remove base path
$basePath = get_base_path();
if ($basePath !== '' && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Clean path
$path = trim($path, '/');
$path = $path === '' ? 'dashboard' : $path;

// Find matching route
$route = null;
$routeKey = null;

// Exact match first
if (isset($routes[$path])) {
    $route = $routes[$path];
    $routeKey = $path;
} else {
    // Try to find pattern match
    foreach ($routes as $pattern => $routeData) {
        // Convert pattern to regex
        $regex = '#^' . str_replace(['/', '{id}'], ['\/', '(\d+)'], $pattern) . '$#';
        if (preg_match($regex, $path, $matches)) {
            $route = $routeData;
            $routeKey = $pattern;
            // Extract parameters
            if (isset($matches[1])) {
                $_GET['id'] = $matches[1];
            }
            break;
        }
    }
}

// If no route found, try to find controller/action pattern
if (!$route) {
    $segments = explode('/', $path);
    if (count($segments) >= 2) {
        $controllerName = ucfirst($segments[0]) . 'Controller';
        $actionName = $segments[1];
        
        $route = [
            'controller' => $controllerName,
            'action' => $actionName,
            'title' => ucfirst($segments[0]),
            'roles' => ['admin', 'staff'] // Default roles
        ];
        $routeKey = $path;
    }
}

// If still no route, show 404
if (!$route) {
    abort(404, 'Page not found');
}

// Check user authentication and permissions
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

function hasPermission($requiredRoles) {
    $user = getCurrentUser();
    if (!$user) {
        return in_array('guest', $requiredRoles);
    }
    
    $userRole = $user['role'] ?? 'guest';
    return in_array($userRole, $requiredRoles) || in_array('*', $requiredRoles);
}

// Permission check
if (!hasPermission($route['roles'])) {
    $user = getCurrentUser();
    if (!$user) {
        // Redirect to login
        redirect(base_url('login'));
    } else {
        // Show forbidden page
        abort(403, 'Access denied');
    }
}

// Load and execute controller
function loadController($controllerName) {
    $controllerFile = KSP_ROOT . 'app/controllers/' . $controllerName . '.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return new $controllerName();
    }
    
    // Fallback to generic controller
    require_once KSP_ROOT . 'app/controllers/GenericController.php';
    return new GenericController();
}

// Execute route
try {
    $controller = loadController($route['controller']);
    $action = $route['action'];
    
    // Check if method exists
    if (!method_exists($controller, $action)) {
        abort(404, 'Action not found');
    }
    
    // Execute controller action
    if (isset($route['api']) && $route['api']) {
        // API route - return JSON
        $result = $controller->$action();
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        // Web route - render page
        $content = $controller->$action();
        
        // Get page renderer
        $renderer = KSPPageRenderer::getInstance();
        
        // Override page data from route
        $pageData = $renderer->getPageData();
        $pageData['title'] = $route['title'];
        $pageData['breadcrumb_title'] = $route['title'];
        
        // Render with unified layout
        echo $renderer->render($content, [
            'show_sidebar' => true,
            'show_header' => true,
            'show_breadcrumb' => true
        ]);
    }
    
} catch (Exception $e) {
    // Graceful error handling
    http_response_code(500);
    
    // Log error
    error_log('FrontController Error: ' . $e->getMessage());
    
    // Show error page
    if (defined('APP_ENV') && APP_ENV === 'development') {
        echo '<h1>Application Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        // Production - show simple error
        echo '<!DOCTYPE html>
<html>
<head>
    <title>KSP Samosir - System Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">System Maintenance</h4>
                        <p class="text-muted">The system is currently under maintenance. Please try again later.</p>
                        <a href="' . base_url('') . '" class="btn btn-primary">Try Again</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
    }
}
