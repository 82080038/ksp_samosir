<?php
/**
 * KSP Samosir Page Title Helper
 * Dynamic page title system for consistent browser tab titles
 */

/**
 * Get page title based on current page/route
 */
function titleHelperBasePath() {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($scriptDir === '/' || $scriptDir === '.') {
        return '';
    }
    return rtrim($scriptDir, '/');
}

function getPageTitle($page = null) {
    if (!$page) {
        // Get current page from URL or fallback
        $request = $_SERVER['REQUEST_URI'] ?? '';
        $basePath = titleHelperBasePath();
        if ($basePath !== '' && strpos($request, $basePath) === 0) {
            $request = substr($request, strlen($basePath));
        }
        $request = rtrim($request, '/');
        $segments = explode('/', $request);
        $page = empty($segments[0]) ? ($segments[1] ?? 'dashboard') : $segments[0];
    }
    
    // Sanitize page name
    $page = preg_replace('/[^a-zA-Z0-9_\-]/', '', $page);
    
    // Define page titles
    $titles = [
        // Main modules
        'dashboard' => 'Dashboard',
        'anggota' => 'Manajemen Anggota',
        'simpanan' => 'Simpanan',
        'pinjaman' => 'Pinjaman',
        'settings' => 'Pengaturan',
        'notifications' => 'Notifikasi',
        
        // Role-specific dashboards
        'member' => 'Portal Anggota',
        'pengawas' => 'Dashboard Pengawas',
        'admin' => 'Admin Dashboard',
        
        // Admin modules
        'accounting' => 'Accounting System',
        'inventory' => 'Inventory Management',
        'digital_documents' => 'Dokumen Digital',
        'ai_credit' => 'AI Credit Scoring',
        'analytics' => 'Analytics Dashboard',
        'monitoring' => 'System Monitoring',
        'pengawas' => 'Pengawasan',
        
        // Authentication
        'login' => 'Login',
        'logout' => 'Logout',
        'register' => 'Registrasi',
        'profile' => 'Profil Pengguna',
        
        // Reports
        'reports' => 'Laporan',
        'laporan' => 'Laporan',
        
        // Member portal
        'members' => 'Daftar Anggota',
        
        // Other modules
        'backup' => 'Backup System',
        'blockchain' => 'Blockchain Integration',
        'customer_service' => 'Customer Service',
        'invoice' => 'Invoice Management',
        'learning' => 'Learning Center',
        'pemasok' => 'Manajemen Pemasok',
        'payroll' => 'Payroll System',
        'rapat' => 'Manajemen Rapat',
        'risk' => 'Risk Management',
        'shu' => 'SHU & Dividen',
        'tax' => 'Tax Management',
        
        // Sub-pages (with action handling)
        'create' => 'Tambah Data',
        'edit' => 'Edit Data',
        'view' => 'Detail Data',
        'delete' => 'Hapus Data',
        'list' => 'Daftar Data',
        'reports' => 'Laporan',
        'settings' => 'Pengaturan',
    ];
    
    // Handle sub-pages (module/action pattern)
    if (strpos($page, '/') !== false) {
        $parts = explode('/', $page);
        $module = $parts[0] ?? '';
        $action = $parts[1] ?? '';
        
        $moduleTitle = $titles[$module] ?? ucfirst($module);
        $actionTitle = $titles[$action] ?? ucfirst($action);
        
        switch ($action) {
            case 'create':
                return "Tambah $moduleTitle";
            case 'edit':
                return "Edit $moduleTitle";
            case 'view':
                return "Detail $moduleTitle";
            case 'delete':
                return "Hapus $moduleTitle";
            case 'list':
                return "Daftar $moduleTitle";
            case 'reports':
                return "Laporan $moduleTitle";
            case 'settings':
                return "Pengaturan $moduleTitle";
            default:
                return "$moduleTitle - $actionTitle";
        }
    }
    
    return $titles[$page] ?? ucfirst($page);
}

/**
 * Get full page title with app name
 */
function getFullPageTitle($page = null) {
    $pageTitle = getPageTitle($page);
    return "$pageTitle - KSP Samosir";
}

/**
 * Set page title dynamically
 */
function setPageTitle($page = null) {
    $title = getFullPageTitle($page);
    echo "<title>$title</title>";
}

/**
 * Get page title for SPA navigation
 */
function getSPATitle($page) {
    return getPageTitle($page);
}

/**
 * Update browser title for SPA
 */
function updateBrowserTitle($page) {
    $title = getFullPageTitle($page);
    echo "<script>document.title = " . json_encode($title) . ";</script>";
}

/**
 * Generate SEO-friendly title
 */
function getSEOTitle($page = null, $additionalInfo = '') {
    $pageTitle = getPageTitle($page);
    $baseTitle = "KSP Samosir - Koperasi Simpan Pinjam";
    
    if (!empty($additionalInfo)) {
        return "$pageTitle $additionalInfo | $baseTitle";
    }
    
    return "$pageTitle | $baseTitle";
}

/**
 * Get breadcrumb title
 */
function getBreadcrumbTitle($page = null) {
    return getPageTitle($page);
}

/**
 * Initialize page title system
 */
function initPageTitle() {
    // Get current page from URL
    $request = $_SERVER['REQUEST_URI'] ?? '';
    $basePath = titleHelperBasePath();
    if ($basePath !== '' && strpos($request, $basePath) === 0) {
        $request = substr($request, strlen($basePath));
    }
    $request = rtrim($request, '/');
    $segments = explode('/', $request);
    
    $page = empty($segments[0]) ? ($segments[1] ?? 'dashboard') : $segments[0];
    
    // Set page title
    setPageTitle($page);
    
    // Return page info for further use
    return [
        'page' => $page,
        'title' => getPageTitle($page),
        'full_title' => getFullPageTitle($page),
        'seo_title' => getSEOTitle($page)
    ];
}
?>
