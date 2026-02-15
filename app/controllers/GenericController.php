<?php
/**
 * Generic Controller - CONTROLLER UMUM UNTUK SEMUA HALAMAN
 * 
 * Controller yang menangani halaman-halaman yang tidak memiliki
 * controller khusus. Menggunakan view files yang ada.
 * 
 * @author KSP Samosir Development Team
 * @version 2.0.0
 */

require_once __DIR__ . '/BaseController.php';

class GenericController extends BaseController {

    private function getBasePath() {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir === '/' || $scriptDir === '.') {
            return '';
        }
        return rtrim($scriptDir, '/');
    }
    
    /**
     * Index action - render view berdasarkan current page
     */
    public function index() {
        $currentPage = $this->getCurrentPage();
        $viewPath = $this->getViewPath($currentPage);
        
        // Load data yang mungkin dibutuhkan
        $data = $this->loadViewData($currentPage);
        
        // Render view
        return $this->renderPage($viewPath, $data);
    }
    
    /**
     * Get current page dari URL
     */
    private function getCurrentPage() {
        $requestUri = $_SERVER['REQUEST_URI'];
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Remove base path dynamically
        $basePath = $this->getBasePath();
        if ($basePath !== '' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        // Clean path
        $path = trim($path, '/');
        $segments = explode('/', $path);
        
        // Return first segment sebagai page
        return $segments[0] ?? 'dashboard';
    }
    
    /**
     * Get view path berdasarkan page
     */
    private function getViewPath($page) {
        // Mapping page ke view
        $viewMapping = [
            'dashboard' => 'dashboard/index',
            'anggota' => 'anggota/index',
            'simpanan' => 'simpanan/index',
            'pinjaman' => 'pinjaman/index',
            'accounting' => 'accounting/index',
            'shu' => 'shu/index',
            'penjualan' => 'penjualan/index',
            'customer_service' => 'customer_service/index',
            'invoice' => 'invoice/index',
            'settings' => 'settings/index',
            'profile' => 'profile/index',
            'laporan' => 'laporan/index'
        ];
        
        return $viewMapping[$page] ?? $page . '/index';
    }
    
    /**
     * Load data yang dibutuhkan untuk view
     */
    private function loadViewData($page) {
        $data = [
            'user' => $this->user,
            'currentPage' => $page
        ];
        
        // Load data spesifik per page
        switch ($page) {
            case 'dashboard':
                $data = array_merge($data, $this->loadDashboardData());
                break;
            case 'anggota':
                $data = array_merge($data, $this->loadAnggotaData());
                break;
            case 'simpanan':
                $data = array_merge($data, $this->loadSimpananData());
                break;
            case 'pinjaman':
                $data = array_merge($data, $this->loadPinjamanData());
                break;
            case 'accounting':
                $data = array_merge($data, $this->loadAccountingData());
                break;
            case 'shu':
                $data = array_merge($data, $this->loadShuData());
                break;
            case 'penjualan':
                $data = array_merge($data, $this->loadPenjualanData());
                break;
            case 'customer_service':
                $data = array_merge($data, $this->loadCustomerServiceData());
                break;
            case 'invoice':
                $data = array_merge($data, $this->loadInvoiceData());
                break;
            case 'settings':
                $data = array_merge($data, $this->loadSettingsData());
                break;
            case 'profile':
                $data = array_merge($data, $this->loadProfileData());
                break;
            case 'laporan':
                $data = array_merge($data, $this->loadLaporanData());
                break;
        }
        
        return $data;
    }
    
    /**
     * Load dashboard data
     */
    private function loadDashboardData() {
        if (!$this->database) return [];
        
        try {
            // Get summary statistics
            $stats = [];
            
            // Total anggota
            $stmt = $this->database->query("SELECT COUNT(*) as total FROM anggota WHERE is_active = 1");
            $stats['total_anggota'] = $stmt->fetch()['total'];
            
            // Total simpanan
            $stmt = $this->database->query("SELECT SUM(amount) as total FROM simpanan WHERE status = 'active'");
            $stats['total_simpanan'] = $stmt->fetch()['total'] ?? 0;
            
            // Total pinjaman
            $stmt = $this->database->query("SELECT SUM(amount) as total FROM pinjaman WHERE status = 'active'");
            $stats['total_pinjaman'] = $stmt->fetch()['total'] ?? 0;
            
            // Total SHU tahun ini
            $stmt = $this->database->prepare("SELECT SUM(amount) as total FROM shu WHERE YEAR(created_at) = YEAR(CURRENT_DATE)");
            $stmt->execute();
            $stats['total_shu'] = $stmt->fetch()['total'] ?? 0;
            
            return [
                'stats' => $stats,
                'recent_activities' => $this->getRecentActivities(),
                'chart_data' => $this->getChartData()
            ];
        } catch (Exception $e) {
            error_log('Failed to load dashboard data: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Load anggota data
     */
    private function loadAnggotaData() {
        if (!$this->database) return [];
        
        try {
            $page = $this->get('page', 1);
            $search = $this->get('search', '');
            $pagination = $this->getPagination(0, $page, 20);
            
            $whereClause = "WHERE is_active = 1";
            $params = [];
            
            if ($search) {
                $whereClause .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $searchParam = "%{$search}%";
                $params = [$searchParam, $searchParam, $searchParam];
            }
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM anggota {$whereClause}";
            $stmt = $this->database->prepare($countQuery);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];
            $pagination = $this->getPagination($total, $page, 20);
            
            // Get data
            $query = "SELECT * FROM anggota {$whereClause} ORDER BY created_at DESC LIMIT {$pagination['offset']}, {$pagination['items_per_page']}";
            $stmt = $this->database->prepare($query);
            $stmt->execute($params);
            $anggota = $stmt->fetchAll();
            
            return [
                'anggota' => $anggota,
                'pagination' => $pagination,
                'search' => $search
            ];
        } catch (Exception $e) {
            error_log('Failed to load anggota data: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Load simpanan data
     */
    private function loadSimpananData() {
        if (!$this->database) return [];
        
        try {
            $page = $this->get('page', 1);
            $pagination = $this->getPagination(0, $page, 20);
            
            // Get total count
            $stmt = $this->database->query("SELECT COUNT(*) as total FROM simpanan WHERE status = 'active'");
            $total = $stmt->fetch()['total'];
            $pagination = $this->getPagination($total, $page, 20);
            
            // Get data
            $query = "
                SELECT s.*, a.name as anggota_name 
                FROM simpanan s 
                LEFT JOIN anggota a ON s.anggota_id = a.id 
                WHERE s.status = 'active' 
                ORDER BY s.created_at DESC 
                LIMIT {$pagination['offset']}, {$pagination['items_per_page']}
            ";
            $stmt = $this->database->query($query);
            $simpanan = $stmt->fetchAll();
            
            return [
                'simpanan' => $simpanan,
                'pagination' => $pagination
            ];
        } catch (Exception $e) {
            error_log('Failed to load simpanan data: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Load pinjaman data
     */
    private function loadPinjamanData() {
        if (!$this->database) return [];
        
        try {
            $page = $this->get('page', 1);
            $pagination = $this->getPagination(0, $page, 20);
            
            // Get total count
            $stmt = $this->database->query("SELECT COUNT(*) as total FROM pinjaman WHERE status = 'active'");
            $total = $stmt->fetch()['total'];
            $pagination = $this->getPagination($total, $page, 20);
            
            // Get data
            $query = "
                SELECT p.*, a.name as anggota_name 
                FROM pinjaman p 
                LEFT JOIN anggota a ON p.anggota_id = a.id 
                WHERE p.status = 'active' 
                ORDER BY p.created_at DESC 
                LIMIT {$pagination['offset']}, {$pagination['items_per_page']}
            ";
            $stmt = $this->database->query($query);
            $pinjaman = $stmt->fetchAll();
            
            return [
                'pinjaman' => $pinjaman,
                'pagination' => $pagination
            ];
        } catch (Exception $e) {
            error_log('Failed to load pinjaman data: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Load accounting data
     */
    private function loadAccountingData() {
        if (!$this->database) return [];
        
        try {
            // Get recent transactions
            $stmt = $this->database->query("
                SELECT * FROM jurnal 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $transactions = $stmt->fetchAll();
            
            // Get summary
            $stmt = $this->database->query("
                SELECT 
                    SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_debit,
                    SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_credit
                FROM jurnal 
                WHERE DATE(created_at) = CURDATE()
            ");
            $summary = $stmt->fetch();
            
            return [
                'transactions' => $transactions,
                'summary' => $summary
            ];
        } catch (Exception $e) {
            error_log('Failed to load accounting data: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Load SHU data
     */
    private function loadShuData() {
        if (!$this->database) return [];
        
        try {
            // Get SHU calculations
            $stmt = $this->database->query("
                SELECT * FROM shu 
                ORDER BY year DESC, month DESC 
                LIMIT 12
            ");
            $shuData = $stmt->fetchAll();
            
            return [
                'shu_data' => $shuData
            ];
        } catch (Exception $e) {
            error_log('Failed to load SHU data: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Load penjualan data
     */
    private function loadPenjualanData() {
        if (!$this->database) return [];
        
        try {
            // Get recent sales
            $stmt = $this->database->query("
                SELECT * FROM penjualan 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $sales = $stmt->fetchAll();
            
            return [
                'sales' => $sales
            ];
        } catch (Exception $e) {
            error_log('Failed to load penjualan data: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Load customer service data
     */
    private function loadCustomerServiceData() {
        if (!$this->database) return [];
        
        try {
            // Get tickets
            $stmt = $this->database->query("
                SELECT * FROM customer_service_tickets 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $tickets = $stmt->fetchAll();
            
            return [
                'tickets' => $tickets
            ];
        } catch (Exception $e) {
            error_log('Failed to load customer service data: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Load invoice data
     */
    private function loadInvoiceData() {
        if (!$this->database) return [];
        
        try {
            // Get invoices
            $stmt = $this->database->query("
                SELECT * FROM invoices 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $invoices = $stmt->fetchAll();
            
            return [
                'invoices' => $invoices
            ];
        } catch (Exception $e) {
            error_log('Failed to load invoice data: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Load settings data
     */
    private function loadSettingsData() {
        if (!$this->database) return [];
        
        try {
            // Get all settings
            $stmt = $this->database->query("SELECT * FROM settings ORDER BY setting_key");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            return [
                'settings' => $settings
            ];
        } catch (Exception $e) {
            error_log('Failed to load settings data: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Load profile data
     */
    private function loadProfileData() {
        return [
            'user' => $this->user
        ];
    }
    
    /**
     * Load laporan data
     */
    private function loadLaporanData() {
        if (!$this->database) return [];
        
        try {
            // Get available reports
            $reports = [
                'anggota' => 'Laporan Anggota',
                'simpanan' => 'Laporan Simpanan',
                'pinjaman' => 'Laporan Pinjaman',
                'keuangan' => 'Laporan Keuangan',
                'shu' => 'Laporan SHU'
            ];
            
            return [
                'reports' => $reports
            ];
        } catch (Exception $e) {
            error_log('Failed to load laporan data: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities() {
        if (!$this->database) return [];
        
        try {
            $stmt = $this->database->query("
                SELECT * FROM activity_logs 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get chart data untuk dashboard
     */
    private function getChartData() {
        if (!$this->database) return [];
        
        try {
            // Monthly data for last 6 months
            $stmt = $this->database->query("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM activity_logs 
                WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Handle dynamic actions
     */
    public function __call($method, $args) {
        $currentPage = $this->getCurrentPage();
        
        // Check jika method adalah action spesifik
        if (strpos($method, 'Action') !== false) {
            $action = str_replace('Action', '', $method);
            $viewPath = $currentPage . '/' . strtolower($action);
            
            if (file_exists(__DIR__ . '/../views/' . $viewPath . '.php')) {
                $data = $this->loadViewData($currentPage);
                return $this->renderPage($viewPath, $data);
            }
        }
        
        // Fallback ke index
        return $this->index();
    }
}
?>
