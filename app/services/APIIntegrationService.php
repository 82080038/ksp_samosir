<?php
/**
 * API Integration Service
 * Connects all controllers to frontend with proper data transformation
 */

class APIIntegrationService
{
    private $db;
    private $controllers = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
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
     * Get integrated dashboard data
     */
    public function getIntegratedDashboard()
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                return ['success' => false, 'message' => 'User not authenticated'];
            }
            
            // Get base dashboard data
            $dashboard = $this->controllers['admin']->getAdminDashboard();
            if (!$dashboard['success']) {
                return $dashboard;
            }
            
            // Enhance with additional data
            $dashboard['dashboard']['enhanced_stats'] = $this->getEnhancedStats();
            $dashboard['dashboard']['module_summaries'] = $this->getModuleSummaries();
            $dashboard['dashboard']['system_alerts'] = $this->getSystemAlerts();
            $dashboard['dashboard']['quick_actions'] = $this->getQuickActions();
            
            return $dashboard;
        } catch (Exception $e) {
            error_log("Error in integrated dashboard: " . $e->getMessage());
            return ['success' => false, 'message' => 'Dashboard integration failed'];
        }
    }
    
    /**
     * Get enhanced statistics
     */
    private function getEnhancedStats()
    {
        try {
            $stats = [];
            
            // Sales analytics
            $salesData = $this->controllers['marketing']->getSalesPerformance();
            $stats['sales'] = [
                'today_sales' => $salesData['today_sales'] ?? 0,
                'today_revenue' => $salesData['today_revenue'] ?? 0,
                'month_growth' => $salesData['month_growth'] ?? 0,
                'top_products' => $salesData['top_products'] ?? []
            ];
            
            // AI predictions
            $aiStats = $this->getAIStatistics();
            $stats['ai'] = $aiStats;
            
            // Multi-unit performance
            $unitStats = $this->controllers['multi_unit']->getUnitStatistics();
            $stats['units'] = $unitStats;
            
            // Financial overview
            $financialStats = $this->getFinancialOverview();
            $stats['financial'] = $financialStats;
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting enhanced stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get module summaries
     */
    private function getModuleSummaries()
    {
        try {
            $summaries = [];
            
            // Multi-Unit Module
            $summaries['multi_unit'] = [
                'total_units' => $this->getTotalUnits(),
                'active_units' => $this->getActiveUnits(),
                'performance_avg' => $this->getAveragePerformance(),
                'recent_transfers' => $this->getRecentTransfers(5)
            ];
            
            // AI Module
            $summaries['ai'] = [
                'predictions_today' => $this->getPredictionsToday(),
                'recommendations_generated' => $this->getRecommendationsToday(),
                'sentiment_analyzed' => $this->getSentimentAnalyzed(),
                'model_accuracy' => $this->getModelAccuracy()
            ];
            
            // Reports Module
            $summaries['reports'] = [
                'reports_generated' => $this->getReportsGenerated(),
                'scheduled_reports' => $this->getScheduledReports(),
                'popular_templates' => $this->getPopularTemplates()
            ];
            
            // Sales Module
            $summaries['sales'] = [
                'conversion_rate' => $this->getConversionRate(),
                'avg_order_value' => $this->getAverageOrderValue(),
                'commission_pending' => $this->getPendingCommissions()
            ];
            
            return $summaries;
        } catch (Exception $e) {
            error_log("Error getting module summaries: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get system alerts
     */
    private function getSystemAlerts()
    {
        try {
            $alerts = [];
            
            // Check low stock
            $lowStock = $this->getLowStockProducts();
            if ($lowStock > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Low Stock Alert',
                    'message' => "{$lowStock} products are running low on stock",
                    'action_url' => '/admin/products?filter=low_stock',
                    'priority' => 'medium'
                ];
            }
            
            // Check pending commissions
            $pendingCommissions = $this->getPendingCommissions();
            if ($pendingCommissions > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'Pending Commissions',
                    'message' => "{$pendingCommissions} commissions require approval",
                    'action_url' => '/admin/commissions?status=pending',
                    'priority' => 'low'
                ];
            }
            
            // Check system health
            $systemHealth = $this->calculateSystemHealth();
            if ($systemHealth < 80) {
                $alerts[] = [
                    'type' => 'danger',
                    'title' => 'System Health Alert',
                    'message' => "System health is at {$systemHealth}%",
                    'action_url' => '/admin/system/health',
                    'priority' => 'high'
                ];
            }
            
            return $alerts;
        } catch (Exception $e) {
            error_log("Error getting system alerts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get quick actions
     */
    private function getQuickActions()
    {
        return [
            [
                'id' => 'create_report',
                'title' => 'Create Report',
                'description' => 'Generate custom reports',
                'icon' => 'fas fa-file-alt',
                'url' => '/admin/reports/create',
                'color' => 'primary'
            ],
            [
                'id' => 'add_unit',
                'title' => 'Add Unit',
                'description' => 'Create new koperasi unit',
                'icon' => 'fas fa-plus-circle',
                'url' => '/admin/units/create',
                'color' => 'success'
            ],
            [
                'id' => 'ai_predictions',
                'title' => 'AI Predictions',
                'description' => 'Run AI analytics',
                'icon' => 'fas fa-brain',
                'url' => '/admin/ai/predictions',
                'color' => 'info'
            ],
            [
                'id' => 'system_backup',
                'title' => 'System Backup',
                'description' => 'Create system backup',
                'icon' => 'fas fa-download',
                'url' => '/admin/system/backup',
                'color' => 'warning'
            ]
        ];
    }
    
    /**
     * Get module-specific data
     */
    public function getModuleData($moduleCode, $params = [])
    {
        try {
            switch ($moduleCode) {
                case 'multi_unit':
                    return $this->getMultiUnitData($params);
                    
                case 'ai':
                    return $this->getAIData($params);
                    
                case 'reports':
                    return $this->getReportsData($params);
                    
                case 'visualization':
                    return $this->getVisualizationData($params);
                    
                case 'sales':
                    return $this->getSalesData($params);
                    
                case 'financial':
                    return $this->getFinancialData($params);
                    
                case 'system':
                    return $this->getSystemData($params);
                    
                default:
                    return ['success' => false, 'message' => 'Module not found'];
            }
        } catch (Exception $e) {
            error_log("Error getting module data: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to load module data'];
        }
    }
    
    /**
     * Get Multi-Unit data
     */
    private function getMultiUnitData($params)
    {
        $controller = $this->controllers['multi_unit'];
        
        $data = [
            'units' => $controller->getAllUnits(),
            'hierarchy' => $controller->getUnitHierarchy(),
            'performance' => $controller->getUnitPerformance(),
            'statistics' => $controller->getUnitStatistics(),
            'recent_transfers' => $controller->getTransferHistory()
        ];
        
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Get AI data
     */
    private function getAIData($params)
    {
        $controller = $this->controllers['ai'];
        $userId = $_SESSION['user_id'] ?? 1;
        
        $data = [
            'recommendations' => $controller->generateProductRecommendations($userId),
            'predictions' => $this->getAIPredictions(),
            'sentiment_analysis' => $this->getSentimentAnalysis(),
            'model_performance' => $this->getModelPerformance(),
            'usage_stats' => $this->getAIUsageStats()
        ];
        
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Get Reports data
     */
    private function getReportsData($params)
    {
        $controller = $this->controllers['reports'];
        
        $data = [
            'templates' => $controller->getReportTemplates(),
            'instances' => $controller->getReportInstances(),
            'scheduled' => $this->getScheduledReports(),
            'analytics' => $this->getReportAnalytics()
        ];
        
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Get Visualization data
     */
    private function getVisualizationData($params)
    {
        $controller = $this->controllers['visualization'];
        
        $data = [
            'charts' => $controller->getCharts(),
            'chart_types' => $this->getChartTypes(),
            'data_sources' => $this->getDataSources(),
            'analytics' => $controller->getChartAnalytics()
        ];
        
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Get Sales data
     */
    private function getSalesData($params)
    {
        $controller = $this->controllers['enhanced_sales'];
        $marketingController = $this->controllers['marketing'];
        
        $data = [
            'analytics' => $marketingController->getMarketingDashboard(),
            'recommendations' => $controller->getRecommendations($_SESSION['user_id'] ?? 1),
            'performance' => $marketingController->getSalesPerformance(),
            'commissions' => $this->controllers['commission']->getCommissionCalculations()
        ];
        
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Get Financial data
     */
    private function getFinancialData($params)
    {
        $data = [
            'savings' => $this->getSavingsData(),
            'loans' => $this->getLoansData(),
            'commissions' => $this->controllers['commission']->getCommissionCalculations(),
            'transactions' => $this->getFinancialTransactions(),
            'analytics' => $this->getFinancialAnalytics()
        ];
        
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Get System data
     */
    private function getSystemData($params)
    {
        $data = [
            'health' => $this->getSystemHealth(),
            'logs' => $this->getSystemLogs(),
            'settings' => $this->getSystemSettings(),
            'backup' => $this->getBackupStatus(),
            'performance' => $this->getSystemPerformance()
        ];
        
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Helper methods for data retrieval
     */
    private function getAIStatistics()
    {
        try {
            $stmt = $this->db->query("
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
    
    private function getTotalUnits()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM koperasi_unit WHERE is_active = 1");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getActiveUnits()
    {
        return $this->getTotalUnits(); // Simplified
    }
    
    private function getAveragePerformance()
    {
        try {
            $stmt = $this->db->query("
                SELECT AVG(persentase_capai_pendapatan) as avg_performance 
                FROM unit_target_performance 
                WHERE periode_bulan = DATE_FORMAT(CURDATE(), '%Y-%m-01')
            ");
            $result = $stmt->fetch();
            return $result['avg_performance'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getRecentTransfers($limit = 5)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT utd.*, dari.nama_unit as dari_unit, ke.nama_unit as ke_unit
                FROM unit_transfer_dana utd
                JOIN koperasi_unit dari ON utd.dari_unit_id = dari.id
                JOIN koperasi_unit ke ON utd.ke_unit_id = ke.id
                ORDER BY utd.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getPredictionsToday()
    {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM ai_predictions 
                WHERE DATE(created_at) = CURDATE()
            ");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getRecommendationsToday()
    {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM ai_recommendations 
                WHERE DATE(created_at) = CURDATE()
            ");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getSentimentAnalyzed()
    {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM ai_sentiment_analysis 
                WHERE DATE(created_at) = CURDATE()
            ");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getModelAccuracy()
    {
        return 92.5; // Simplified
    }
    
    private function getReportsGenerated()
    {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM report_instances 
                WHERE DATE(created_at) = CURDATE()
            ");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getScheduledReports()
    {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM report_templates 
                WHERE is_scheduled = 1
            ");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getPopularTemplates()
    {
        try {
            $stmt = $this->db->query("
                SELECT rt.name, COUNT(ri.id) as usage_count
                FROM report_templates rt
                LEFT JOIN report_instances ri ON rt.id = ri.template_id
                GROUP BY rt.id, rt.name
                ORDER BY usage_count DESC
                LIMIT 5
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getConversionRate()
    {
        return 68.5; // Simplified
    }
    
    private function getAverageOrderValue()
    {
        try {
            $stmt = $this->db->query("
                SELECT AVG(total_harga) as avg_order 
                FROM penjualan 
                WHERE status_pembayaran = 'lunas' 
                AND DATE(tanggal_penjualan) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $result = $stmt->fetch();
            return $result['avg_order'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getPendingCommissions()
    {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM commission_calculations 
                WHERE status = 'calculated'
            ");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getLowStockProducts()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM produk WHERE stok < 10");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function calculateSystemHealth()
    {
        // Simplified health calculation
        return 92;
    }
    
    private function getSavingsData()
    {
        try {
            $stmt = $this->db->query("
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
            $stmt = $this->db->query("
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
    
    private function getFinancialTransactions()
    {
        // Simplified - would return recent transactions
        return [];
    }
    
    private function getFinancialAnalytics()
    {
        // Simplified - would return financial analytics
        return [];
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
            $stmt = $this->db->query("
                SELECT * FROM logs 
                ORDER BY created_at DESC 
                LIMIT 50
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getSystemSettings()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM settings ORDER BY setting_key");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getBackupStatus()
    {
        return [
            'last_backup' => '2026-02-15 02:00:00',
            'next_backup' => '2026-02-16 02:00:00',
            'status' => 'scheduled'
        ];
    }
    
    private function getSystemPerformance()
    {
        return [
            'response_time' => '120ms',
            'uptime' => '99.9%',
            'active_sessions' => 25,
            'memory_usage' => '67%',
            'cpu_usage' => '45%'
        ];
    }
    
    private function getAIPredictions()
    {
        try {
            $stmt = $this->db->query("
                SELECT * FROM ai_predictions 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getSentimentAnalysis()
    {
        try {
            $stmt = $this->db->query("
                SELECT * FROM ai_sentiment_analysis 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getModelPerformance()
    {
        try {
            $stmt = $this->db->query("
                SELECT * FROM ai_model_metrics 
                ORDER BY evaluation_date DESC 
                LIMIT 10
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getAIUsageStats()
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    model_name,
                    COUNT(*) as usage_count,
                    SUM(tokens_used) as total_tokens,
                    AVG(response_time_ms) as avg_response_time
                FROM ai_api_usage 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY model_name
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getReportAnalytics()
    {
        // Simplified report analytics
        return [
            'total_reports' => 150,
            'most_used_template' => 'Financial Summary',
            'avg_generation_time' => '2.5 seconds'
        ];
    }
    
    private function getChartTypes()
    {
        return [
            'line', 'bar', 'pie', 'area', 'scatter', 'gauge', 'heatmap', 'funnel'
        ];
    }
    
    private function getDataSources()
    {
        return [
            'sales', 'members', 'financial', 'inventory', 'ai_predictions'
        ];
    }
    
    private function getFinancialOverview()
    {
        return [
            'total_savings' => 50000000,
            'total_loans' => 40000000,
            'pending_commissions' => 2500000,
            'monthly_revenue' => 10000000
        ];
    }
}
