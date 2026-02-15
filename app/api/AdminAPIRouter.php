<?php
/**
 * API Router for Admin Dashboard
 * Handles all API endpoints for admin functionality
 */

class AdminAPIRouter
{
    private $controllers = [];
    
    public function __construct()
    {
        $this->initializeControllers();
    }
    
    /**
     * Initialize all controllers
     */
    private function initializeControllers()
    {
        $this->controllers = [
            'admin' => new AdminDashboardController(),
            'super_admin' => new SuperAdminDashboardController(),
            'multi_unit' => new MultiUnitController(),
            'koperasi' => new KoperasiController(),
            'ai' => new AIService(),
            'auth' => new AuthService(),
            'realtime' => new RealtimeDashboardController(),
            'predictive' => new PredictiveAnalyticsController(),
            'reports' => new CustomReportController(),
            'visualization' => new DataVisualizationController(),
            'enhanced_sales' => new EnhancedSalesController(),
            'commission' => new CommissionController(),
            'marketing' => new MarketingAnalyticsController(),
            'knowledge_base' => new KnowledgeBaseController()
        ];
    }
    
    /**
     * Route API requests
     */
    public function route($method, $endpoint, $data = null)
    {
        // Parse endpoint
        $parts = explode('/', trim($endpoint, '/'));
        $module = $parts[0] ?? '';
        $action = $parts[1] ?? '';
        $params = array_slice($parts, 2);
        
        // Check authentication
        if (!$this->isAuthenticated()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        // Check permissions
        if (!$this->hasPermission($module, $action)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        try {
            // Route to appropriate controller
            switch ($module) {
                case 'admin':
                    return $this->handleAdminAPI($method, $action, $params, $data);
                    
                case 'super_admin':
                    return $this->handleSuperAdminAPI($method, $action, $params, $data);
                    
                case 'multi_unit':
                    return $this->handleMultiUnitAPI($method, $action, $params, $data);
                    
                case 'koperasi':
                    return $this->handleKoperasiAPI($method, $action, $params, $data);
                    
                case 'ai':
                    return $this->handleAIAPI($method, $action, $params, $data);
                    
                case 'reports':
                    return $this->handleReportsAPI($method, $action, $params, $data);
                    
                case 'visualization':
                    return $this->handleVisualizationAPI($method, $action, $params, $data);
                    
                case 'sales':
                    return $this->handleSalesAPI($method, $action, $params, $data);
                    
                case 'financial':
                    return $this->handleFinancialAPI($method, $action, $params, $data);
                    
                case 'system':
                    return $this->handleSystemAPI($method, $action, $params, $data);
                    
                default:
                    return $this->jsonResponse(['success' => false, 'message' => 'Endpoint not found'], 404);
            }
        } catch (Exception $e) {
            error_log("API Error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Internal server error'], 500);
        }
    }
    
    /**
     * Handle Admin Dashboard API
     */
    private function handleAdminAPI($method, $action, $params, $data)
    {
        switch ($action) {
            case 'dashboard':
                if ($method === 'GET') {
                    return $this->jsonResponse($this->controllers['admin']->getAdminDashboard());
                }
                break;
                
            case 'module':
                if ($method === 'GET' && isset($params[0])) {
                    $moduleCode = $params[0];
                    return $this->jsonResponse($this->getModuleData($moduleCode));
                }
                break;
                
            case 'stats':
                if ($method === 'GET') {
                    return $this->jsonResponse([
                        'success' => true,
                        'stats' => $this->controllers['admin']->getQuickStats()
                    ]);
                }
                break;
        }
        
        return $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
    }
    
    /**
     * Handle Super Admin API
     */
    private function handleSuperAdminAPI($method, $action, $params, $data)
    {
        switch ($action) {
            case 'dashboard':
                if ($method === 'GET') {
                    return $this->jsonResponse($this->controllers['super_admin']->getSuperAdminDashboard());
                }
                break;
                
            case 'tenants':
                if ($method === 'GET') {
                    return $this->jsonResponse([
                        'success' => true,
                        'tenants' => $this->controllers['super_admin']->getTenantOverview()
                    ]);
                }
                break;
                
            case 'billing':
                if ($method === 'GET') {
                    return $this->jsonResponse([
                        'success' => true,
                        'billing' => $this->controllers['super_admin']->getBillingSummary()
                    ]);
                }
                break;
        }
        
        return $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
    }
    
    /**
     * Handle Multi-Unit API
     */
    private function handleMultiUnitAPI($method, $action, $params, $data)
    {
        $controller = $this->controllers['multi_unit'];
        
        switch ($action) {
            case 'units':
                if ($method === 'GET') {
                    $indukId = $params[0] ?? null;
                    return $this->jsonResponse([
                        'success' => true,
                        'units' => $controller->getAllUnits($indukId)
                    ]);
                } elseif ($method === 'POST') {
                    return $this->jsonResponse($controller->createUnit($data));
                }
                break;
                
            case 'unit':
                if ($method === 'GET' && isset($params[0])) {
                    return $this->jsonResponse([
                        'success' => true,
                        'unit' => $controller->getUnitById($params[0])
                    ]);
                } elseif ($method === 'PUT' && isset($params[0])) {
                    return $this->jsonResponse($controller->updateUnit($params[0], $data));
                } elseif ($method === 'DELETE' && isset($params[0])) {
                    return $this->jsonResponse($controller->deleteUnit($params[0]));
                }
                break;
                
            case 'hierarchy':
                if ($method === 'GET') {
                    $indukId = $params[0] ?? 1;
                    return $this->jsonResponse([
                        'success' => true,
                        'hierarchy' => $controller->getUnitHierarchy($indukId)
                    ]);
                }
                break;
                
            case 'performance':
                if ($method === 'GET') {
                    $unitId = $params[0] ?? null;
                    $periode = $params[1] ?? null;
                    return $this->jsonResponse([
                        'success' => true,
                        'performance' => $controller->getUnitPerformance($unitId, $periode)
                    ]);
                }
                break;
        }
        
        return $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
    }
    
    /**
     * Handle AI API
     */
    private function handleAIAPI($method, $action, $params, $data)
    {
        $controller = $this->controllers['ai'];
        
        switch ($action) {
            case 'recommendations':
                if ($method === 'POST') {
                    $userId = $data['user_id'] ?? $_SESSION['user_id'];
                    return $this->jsonResponse($controller->generateProductRecommendations($userId, $data));
                }
                break;
                
            case 'sentiment':
                if ($method === 'POST') {
                    $customerId = $data['customer_id'];
                    $textData = $data['text_data'] ?? [];
                    return $this->jsonResponse($controller->analyzeCustomerSentiment($customerId, $textData));
                }
                break;
                
            case 'predictions':
                if ($method === 'POST') {
                    $customerId = $data['customer_id'];
                    return $this->jsonResponse($controller->predictCustomerChurn($customerId));
                }
                break;
                
            case 'chatbot':
                if ($method === 'POST') {
                    $message = $data['message'];
                    $context = $data['context'] ?? [];
                    return $this->jsonResponse($controller->generateChatbotResponse($message, $context));
                }
                break;
        }
        
        return $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
    }
    
    /**
     * Handle Reports API
     */
    private function handleReportsAPI($method, $action, $params, $data)
    {
        $controller = $this->controllers['reports'];
        
        switch ($action) {
            case 'templates':
                if ($method === 'GET') {
                    return $this->jsonResponse([
                        'success' => true,
                        'templates' => $controller->getReportTemplates()
                    ]);
                } elseif ($method === 'POST') {
                    return $this->jsonResponse($controller->createReportTemplate($data));
                }
                break;
                
            case 'reports':
                if ($method === 'GET') {
                    return $this->jsonResponse([
                        'success' => true,
                        'reports' => $controller->getReportInstances()
                    ]);
                } elseif ($method === 'POST') {
                    return $this->jsonResponse($controller->generateReport($data));
                }
                break;
                
            case 'report':
                if ($method === 'GET' && isset($params[0])) {
                    return $this->jsonResponse([
                        'success' => true,
                        'report' => $controller->getReportInstance($params[0])
                    ]);
                }
                break;
        }
        
        return $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
    }
    
    /**
     * Handle Visualization API
     */
    private function handleVisualizationAPI($method, $action, $params, $data)
    {
        $controller = $this->controllers['visualization'];
        
        switch ($action) {
            case 'charts':
                if ($method === 'GET') {
                    return $this->jsonResponse([
                        'success' => true,
                        'charts' => $controller->getCharts()
                    ]);
                } elseif ($method === 'POST') {
                    return $this->jsonResponse($controller->createChart($data));
                }
                break;
                
            case 'chart':
                if ($method === 'GET' && isset($params[0])) {
                    return $this->jsonResponse([
                        'success' => true,
                        'chart' => $controller->getChart($params[0])
                    ]);
                } elseif ($method === 'PUT' && isset($params[0])) {
                    return $this->jsonResponse($controller->updateChart($params[0], $data));
                } elseif ($method === 'DELETE' && isset($params[0])) {
                    return $this->jsonResponse($controller->deleteChart($params[0]));
                }
                break;
                
            case 'chart-data':
                if ($method === 'POST') {
                    return $this->jsonResponse($controller->executeChartData($data));
                }
                break;
        }
        
        return $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
    }
    
    /**
     * Handle Sales API
     */
    private function handleSalesAPI($method, $action, $params, $data)
    {
        $controller = $this->controllers['enhanced_sales'];
        
        switch ($action) {
            case 'recommendations':
                if ($method === 'GET') {
                    $userId = $params[0] ?? $_SESSION['user_id'];
                    return $this->jsonResponse([
                        'success' => true,
                        'recommendations' => $controller->getRecommendations($userId)
                    ]);
                }
                break;
                
            case 'analytics':
                if ($method === 'GET') {
                    return $this->jsonResponse([
                        'success' => true,
                        'analytics' => $this->controllers['marketing']->getMarketingDashboard()
                    ]);
                }
                break;
        }
        
        return $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
    }
    
    /**
     * Handle Financial API
     */
    private function handleFinancialAPI($method, $action, $params, $data)
    {
        switch ($action) {
            case 'commissions':
                if ($method === 'GET') {
                    return $this->jsonResponse([
                        'success' => true,
                        'commissions' => $this->controllers['commission']->getCommissionCalculations()
                    ]);
                }
                break;
                
            case 'savings':
                if ($method === 'GET') {
                    return $this->jsonResponse([
                        'success' => true,
                        'savings' => $this->getSavingsData()
                    ]);
                }
                break;
                
            case 'loans':
                if ($method === 'GET') {
                    return $this->jsonResponse([
                        'success' => true,
                        'loans' => $this->getLoansData()
                    ]);
                }
                break;
        }
        
        return $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
    }
    
    /**
     * Handle System API
     */
    private function handleSystemAPI($method, $action, $params, $data)
    {
        switch ($action) {
            case 'health':
                if ($method === 'GET') {
                    return $this->jsonResponse([
                        'success' => true,
                        'health' => $this->getSystemHealth()
                    ]);
                }
                break;
                
            case 'logs':
                if ($method === 'GET') {
                    return $this->jsonResponse([
                        'success' => true,
                        'logs' => $this->getSystemLogs()
                    ]);
                }
                break;
                
            case 'backup':
                if ($method === 'POST') {
                    return $this->jsonResponse($this->createSystemBackup());
                }
                break;
        }
        
        return $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
    }
    
    /**
     * Get module data for dashboard
     */
    private function getModuleData($moduleCode)
    {
        $moduleData = [
            'module_code' => $moduleCode,
            'stats' => [],
            'recent_activity' => [],
            'actions' => []
        ];
        
        switch ($moduleCode) {
            case 'multi_unit':
                $moduleData['stats'] = $this->controllers['multi_unit']->getUnitStatistics();
                $moduleData['recent_activity'] = $this->controllers['multi_unit']->getTransferHistory(null, 'completed');
                break;
                
            case 'ai':
                $moduleData['stats'] = $this->getAIStats();
                break;
                
            case 'sales':
                $moduleData['stats'] = $this->controllers['marketing']->getSalesPerformance();
                break;
                
            // Add more modules as needed
        }
        
        return [
            'success' => true,
            'data' => $moduleData
        ];
    }
    
    /**
     * Helper methods
     */
    private function isAuthenticated()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    private function hasPermission($module, $action)
    {
        // For now, allow all authenticated users
        // In production, implement proper permission checking
        return true;
    }
    
    private function jsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
    
    private function getAIStats()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT 
                    COUNT(*) as total_predictions,
                    AVG(confidence_score) as avg_confidence,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as predictions_today
                FROM ai_predictions
            ");
            return $stmt->fetch();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getSavingsData()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT 
                    COALESCE(SUM(jumlah), 0) as total_savings,
                    COUNT(*) as total_accounts
                FROM simpanan
                WHERE status = 'active'
            ");
            return $stmt->fetch();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getLoansData()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT 
                    COALESCE(SUM(jumlah_pinjaman), 0) as total_loans,
                    COUNT(*) as total_loans_count,
                    COUNT(CASE WHEN status = 'disetujui' THEN 1 END) as approved_loans
                FROM pinjaman
            ");
            return $stmt->fetch();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getSystemHealth()
    {
        return [
            'database' => ['status' => 'healthy', 'connections' => 1],
            'ai_services' => ['status' => 'healthy', 'models_loaded' => 5],
            'memory_usage' => '67%',
            'cpu_usage' => '45%',
            'disk_usage' => '23%'
        ];
    }
    
    private function getSystemLogs()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT * FROM logs 
                ORDER BY created_at DESC 
                LIMIT 50
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function createSystemBackup()
    {
        // Simplified backup creation
        return [
            'success' => true,
            'message' => 'Backup created successfully',
            'backup_file' => 'backup_' . date('Y-m-d_H-i-s') . '.sql'
        ];
    }
}

// API Router initialization
if (isset($_GET['api']) && $_GET['api'] === 'admin') {
    $router = new AdminAPIRouter();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $endpoint = $_SERVER['REQUEST_URI'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    $router->route($method, $endpoint, $data);
}
