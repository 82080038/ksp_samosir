<?php
/**
 * Navigation API Endpoint
 * 
 * Provides navigation data for dynamic page title synchronization
 * Supports both static HTML navigation and database-driven navigation
 * 
 * @author KSP Samosir Development Team
 * @version 1.0.0
 */

// Include required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/FormatHelper.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Helper function
function get_base_path() {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($scriptDir === '/' || $scriptDir === '.') {
        return '';
    }
    return rtrim($scriptDir, '/');
}

function base_url($path = '') {
    $basePath = get_base_path();
    $path = ltrim($path, '/');
    if ($path === '') {
        return $basePath === '' ? '/' : $basePath . '/';
    }
    return ($basePath === '' ? '' : $basePath) . '/' . $path;
}

// Get navigation data from database
function getNavigationFromDatabase() {
    try {
        $pdo = getPDO();
        
        // Query untuk navigation items
        $stmt = $pdo->prepare("
            SELECT 
                n.id,
                n.page_key as page,
                n.title,
                n.url,
                n.icon_class,
                n.parent_id,
                n.sort_order,
                n.is_active,
                n.role_required,
                n.description
            FROM navigation n 
            WHERE n.is_active = 1 
            ORDER BY n.sort_order ASC
        ");
        
        $stmt->execute();
        $navigation = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format navigation data
        $formattedNavigation = [];
        foreach ($navigation as $item) {
            $formattedNavigation[] = [
                'page' => $item['page'],
                'title' => $item['title'],
                'url' => $item['url'],
                'icon' => $item['icon_class'],
                'parent_id' => $item['parent_id'],
                'sort_order' => $item['sort_order'],
                'role_required' => $item['role_required'],
                'description' => $item['description']
            ];
        }
        
        return $formattedNavigation;
        
    } catch (Exception $e) {
        error_log("Navigation API Error: " . $e->getMessage());
        return null;
    }
}

// Get static navigation configuration (fallback)
function getStaticNavigation() {
    return [
        [
            'page' => 'dashboard',
            'title' => 'Dashboard',
            'url' => base_url('dashboard'),
            'icon' => 'bi-speedometer2',
            'role_required' => null
        ],
        [
            'page' => 'accounting',
            'title' => 'Akuntansi',
            'url' => base_url('accounting'),
            'icon' => 'bi-calculator',
            'role_required' => ['admin', 'accounting']
        ],
        [
            'page' => 'anggota',
            'title' => 'Anggota',
            'url' => base_url('anggota'),
            'icon' => 'bi-people',
            'role_required' => ['admin', 'staff']
        ],
        [
            'page' => 'simpanan',
            'title' => 'Simpanan',
            'url' => base_url('simpanan'),
            'icon' => 'bi-piggy-bank',
            'role_required' => ['admin', 'staff']
        ],
        [
            'page' => 'pinjaman',
            'title' => 'Pinjaman',
            'url' => base_url('pinjaman'),
            'icon' => 'bi-cash-stack',
            'role_required' => ['admin', 'staff']
        ],
        [
            'page' => 'penjualan',
            'title' => 'Penjualan',
            'url' => base_url('penjualan'),
            'icon' => 'bi-cart3',
            'role_required' => ['admin', 'staff']
        ],
        [
            'page' => 'settings',
            'title' => 'Pengaturan',
            'url' => base_url('settings'),
            'icon' => 'bi-gear',
            'role_required' => ['admin']
        ],
        [
            'page' => 'laporan',
            'title' => 'Laporan',
            'url' => base_url('laporan'),
            'icon' => 'bi-file-earmark-text',
            'role_required' => ['admin', 'staff']
        ],
        [
            'page' => 'customer_service',
            'title' => 'Customer Service',
            'url' => base_url('customer_service'),
            'icon' => 'bi-headset',
            'role_required' => ['admin', 'customer_service']
        ],
        [
            'page' => 'invoice',
            'title' => 'Invoice',
            'url' => base_url('invoice'),
            'icon' => 'bi-receipt',
            'role_required' => ['admin', 'invoice']
        ],
        [
            'page' => 'shu',
            'title' => 'SHU',
            'url' => base_url('shu'),
            'icon' => 'bi-graph-up',
            'role_required' => ['admin', 'accounting']
        ]
    ];
}

// Main API logic
try {
    // Get current user role (if available)
    $currentUser = null;
    $userRole = null;
    
    // Check if user is logged in (simplified check)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['user'])) {
        $currentUser = $_SESSION['user'];
        $userRole = $currentUser['role'] ?? null;
    }
    
    // Try to get navigation from database first
    $navigation = getNavigationFromDatabase();
    
    // Fallback to static navigation if database fails
    if ($navigation === null) {
        $navigation = getStaticNavigation();
    }
    
    // Filter navigation based on user role
    if ($userRole) {
        $filteredNavigation = [];
        foreach ($navigation as $item) {
            $roleRequired = $item['role_required'] ?? null;
            
            // If no role required or user has required role
            if ($roleRequired === null || 
                (is_array($roleRequired) && in_array($userRole, $roleRequired)) ||
                (!is_array($roleRequired) && $roleRequired === $userRole)) {
                $filteredNavigation[] = $item;
            }
        }
        $navigation = $filteredNavigation;
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => [
            'navigation' => $navigation,
            'user_role' => $userRole,
            'timestamp' => date('Y-m-d H:i:s'),
            'source' => getNavigationFromDatabase() !== null ? 'database' : 'static'
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Failed to load navigation data'
    ], JSON_PRETTY_PRINT);
}
?>
