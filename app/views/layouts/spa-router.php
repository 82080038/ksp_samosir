<?php
/**
 * KSP Samosir SPA Router
 * Single Page Application Router for Dynamic Content Loading
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../../app/helpers/FormatHelper.php';
require_once __DIR__ . '/../../../config/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Handle AJAX requests for content loading
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'load_content') {
    $page = $_POST['page'] ?? 'dashboard';
    
    try {
        $content = loadSPAContent($page);
        $title = getSPATitle($page);
        
        echo json_encode([
            'success' => true,
            'content' => $content,
            'title' => $title,
            'page' => $page
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'page' => $page
        ]);
    }
    exit;
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
    exit;
}

/**
 * Load SPA content based on page
 */
function loadSPAContent($page) {
    $contentMap = [
        'dashboard' => '/app/views/dashboard/index.php',
        'settings' => '/app/views/settings/index.php',
        'anggota' => '/app/views/anggota/index.php',
        'simpanan' => '/app/views/simpanan/index.php',
        'pinjaman' => '/app/views/pinjaman/index.php',
        'accounting' => '/app/views/accounting/index.php',
        'inventory' => '/app/views/inventory/index.php',
        'digital_documents' => '/app/views/digital_documents/index.php',
        'analytics/dashboard' => '/app/views/analytics/dashboard.php',
        'ai_credit' => '/app/views/ai_credit/index.php',
        'member' => '/app/views/member/index.php',
        'accounting/jurnal' => '/app/views/accounting/jurnal.php',
        'accounting/laba_rugi' => '/app/views/accounting/laba_rugi.php',
        'accounting/neraca_saldo' => '/app/views/accounting/neraca_saldo.php',
    ];
    
    $viewPath = $contentMap[$page] ?? '/app/views/errors/404.php';
    $fullPath = __DIR__ . '/../../..' . $viewPath;
    
    if (!file_exists($fullPath)) {
        $fullPath = __DIR__ . '/../../views/errors/404.php';
    }
    
    // Start output buffering
    ob_start();
    
    // Extract variables needed for the view
    extract(getPageData($page));
    
    // Include the view file
    include $fullPath;
    
    // Get the content and clean buffer
    $content = ob_get_clean();
    
    return $content;
}

/**
 * Get page-specific data
 */
function getPageData($page) {
    $data = [];
    
    switch ($page) {
        case 'dashboard':
            $data['stats'] = getDashboardStats();
            $data['recent_activities'] = getRecentActivities();
            break;
        case 'anggota':
            $data['members'] = getMembersList();
            break;
        case 'simpanan':
            $data['simpanan'] = getSimpananList();
            break;
        case 'pinjaman':
            $data['pinjaman'] = getPinjamanList();
            break;
        case 'accounting':
            $data['stats'] = getAccountingStats();
            break;
        case 'inventory':
            $data['items'] = getInventoryItems();
            break;
        case 'digital_documents':
            $data['stats'] = getDigitalDocumentsStats();
            break;
        case 'analytics/dashboard':
            $data['kpi'] = getAnalyticsKPI();
            break;
        case 'ai_credit':
            $data['scores'] = getAICreditScores();
            break;
        // Add more cases as needed
    }
    
    return $data;
}

/**
 * Get page title for SPA
 */
function getSPATitle($page) {
    $titles = [
        'dashboard' => 'Dashboard',
        'settings' => 'Pengaturan',
        'anggota' => 'Manajemen Anggota',
        'simpanan' => 'Simpanan',
        'pinjaman' => 'Pinjaman',
        'accounting' => 'Accounting System',
        'inventory' => 'Inventory Management',
        'digital_documents' => 'Digital Documents',
        'analytics/dashboard' => 'Analytics Dashboard',
        'ai_credit' => 'AI Credit Scoring',
        'member' => 'Portal Anggota',
        'accounting/jurnal' => 'Jurnal Umum',
        'accounting/laba_rugi' => 'Laporan Laba Rugi',
        'accounting/neraca_saldo' => 'Neraca Saldo',
    ];
    
    return $titles[$page] ?? 'KSP Samosir';
}

// Dummy data functions (replace with actual database queries)
function getDashboardStats() {
    try {
        return [
            'total_anggota' => fetchRow("SELECT COUNT(*) as total FROM anggota")['total'] ?? 0,
            'total_simpanan' => fetchRow("SELECT SUM(saldo) as total FROM simpanan")['total'] ?? 0,
            'total_pinjaman' => fetchRow("SELECT SUM(jumlah_pinjaman) as total FROM pinjaman WHERE status = 'aktif'")['total'] ?? 0,
            'total_shu' => 5000000
        ];
    } catch (Exception $e) {
        return [
            'total_anggota' => 0,
            'total_simpanan' => 0,
            'total_pinjaman' => 0,
            'total_shu' => 0
        ];
    }
}

function getRecentActivities() {
    try {
        $activities = fetchAll("SELECT * FROM activities ORDER BY created_at DESC LIMIT 10");
        return $activities ?: [];
    } catch (Exception $e) {
        return [];
    }
}

function getMembersList() {
    try {
        return fetchAll("SELECT * FROM anggota ORDER BY nama_lengkap") ?: [];
    } catch (Exception $e) {
        return [];
    }
}

function getSimpananList() {
    try {
        return fetchAll("SELECT s.*, a.nama_lengkap FROM simpanan s LEFT JOIN anggota a ON s.anggota_id = a.id") ?: [];
    } catch (Exception $e) {
        return [];
    }
}

function getPinjamanList() {
    try {
        return fetchAll("SELECT p.*, a.nama_lengkap FROM pinjaman p LEFT JOIN anggota a ON p.anggota_id = a.id") ?: [];
    } catch (Exception $e) {
        return [];
    }
}

function getAccountingStats() {
    return ['total_jurnal' => 100];
}

function getInventoryItems() {
    return [];
}

function getDigitalDocumentsStats() {
    return [
        'total_documents' => 50,
        'signed_documents' => 30,
        'pending_signatures' => 10,
        'completed_signatures' => 20
    ];
}

function getAnalyticsKPI() {
    return [];
}

function getAICreditScores() {
    return [];
}
?>
