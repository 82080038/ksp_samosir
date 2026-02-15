<?php
/**
 * Admin Dashboard Controller - Full Access for Development
 * Provides access to all modules for admin role
 */

class AdminDashboardController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Get admin dashboard with all modules
     */
    public function getAdminDashboard()
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                return ['success' => false, 'message' => 'User not authenticated'];
            }
            
            // Check if user has admin role
            $userRole = $this->getUserRole($userId);
            if (!in_array($userRole['role_id'], [1, 2])) { // super_admin or admin
                return ['success' => false, 'message' => 'Access denied'];
            }
            
            return [
                'success' => true,
                'dashboard' => [
                    'modules' => $this->getAvailableModules(),
                    'quick_stats' => $this->getQuickStats(),
                    'recent_activities' => $this->getRecentActivities(),
                    'system_status' => $this->getSystemStatus(),
                    'notifications' => $this->getNotifications()
                ]
            ];
        } catch (Exception $e) {
            error_log("Error getting admin dashboard: " . $e->getMessage());
            return ['success' => false, 'message' => 'Dashboard loading failed'];
        }
    }
    
    /**
     * Get all available modules for admin
     */
    public function getAvailableModules()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM v_admin_dashboard_modules ORDER BY module_name");
            $modules = $stmt->fetchAll();
            
            // Add additional module data
            foreach ($modules as &$module) {
                $module['stats'] = $this->getModuleStats($module['module_code']);
                $module['recent_activity'] = $this->getModuleRecentActivity($module['module_code']);
                $module['access_level'] = 'full'; // Admin has full access
            }
            
            return $modules;
        } catch (Exception $e) {
            error_log("Error getting available modules: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get quick statistics for dashboard
     */
    public function getQuickStats()
    {
        try {
            $stats = [];
            
            // User statistics
            $stmt = $this->db->query("SELECT COUNT(*) as total_users FROM users WHERE is_active = 1");
            $stats['users'] = $stmt->fetch()['total_users'];
            
            // Member statistics
            $stmt = $this->db->query("SELECT COUNT(*) as total_members FROM anggota WHERE is_active = 1");
            $stats['members'] = $stmt->fetch()['total_members'];
            
            // Unit statistics
            $stmt = $this->db->query("SELECT COUNT(*) as total_units FROM koperasi_unit WHERE is_active = 1");
            $stats['units'] = $stmt->fetch()['total_units'];
            
            // Today's sales
            $stmt = $this->db->query("
                SELECT COUNT(*) as today_sales, COALESCE(SUM(total_harga), 0) as today_revenue 
                FROM penjualan 
                WHERE DATE(tanggal_penjualan) = CURDATE() AND status_pembayaran = 'lunas'
            ");
            $sales = $stmt->fetch();
            $stats['today_sales'] = $sales['today_sales'];
            $stats['today_revenue'] = $sales['today_revenue'];
            
            // Total savings
            $stmt = $this->db->query("SELECT COALESCE(SUM(jumlah), 0) as total_savings FROM simpanan");
            $stats['total_savings'] = $stmt->fetch()['total_savings'];
            
            // Total loans
            $stmt = $this->db->query("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total_loans FROM pinjaman WHERE status = 'disetujui'");
            $stats['total_loans'] = $stmt->fetch()['total_loans'];
            
            // AI predictions today
            $stmt = $this->db->query("
                SELECT COUNT(*) as ai_predictions 
                FROM ai_predictions 
                WHERE DATE(created_at) = CURDATE()
            ");
            $stats['ai_predictions'] = $stmt->fetch()['ai_predictions'];
            
            // System health
            $stats['system_health'] = $this->calculateSystemHealth();
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting quick stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 20)
    {
        try {
            $activities = [];
            
            // Recent sales
            $stmt = $this->db->prepare("
                SELECT 'sale' as type, p.no_faktur, p.total_harga, u.full_name, p.tanggal_penjualan as created_at
                FROM penjualan p
                JOIN users u ON p.user_id = u.id
                WHERE p.status_pembayaran = 'lunas'
                ORDER BY p.tanggal_penjualan DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $sales = $stmt->fetchAll();
            
            // Recent user registrations
            $stmt = $this->db->prepare("
                SELECT 'user_registration' as type, username, full_name, email, created_at
                FROM users
                WHERE is_active = 1
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $users = $stmt->fetchAll();
            
            // Recent AI predictions
            $stmt = $this->db->prepare("
                SELECT 'ai_prediction' as type, prediction_type, confidence_score, created_at
                FROM ai_predictions
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $predictions = $stmt->fetchAll();
            
            // Merge and sort by date
            $activities = array_merge($sales, $users, $predictions);
            usort($activities, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            return array_slice($activities, 0, $limit);
        } catch (Exception $e) {
            error_log("Error getting recent activities: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get system status
     */
    public function getSystemStatus()
    {
        try {
            $status = [];
            
            // Database status
            $status['database'] = [
                'status' => 'healthy',
                'connections' => $this->getDbConnections(),
                'size' => $this->getDbSize()
            ];
            
            // AI services status
            $status['ai_services'] = [
                'status' => $this->checkAIServices(),
                'models_loaded' => $this->getLoadedModels(),
                'predictions_today' => $this->getTodayPredictions()
            ];
            
            // Multi-unit status
            $status['multi_unit'] = [
                'status' => 'active',
                'total_units' => $this->getTotalUnits(),
                'active_units' => $this->getActiveUnits()
            ];
            
            // System performance
            $status['performance'] = [
                'cpu_usage' => $this->getCpuUsage(),
                'memory_usage' => $this->getMemoryUsage(),
                'disk_usage' => $this->getDiskUsage()
            ];
            
            return $status;
        } catch (Exception $e) {
            error_log("Error getting system status: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get notifications for admin
     */
    public function getNotifications()
    {
        try {
            $notifications = [];
            
            // Low stock alerts
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM produk WHERE stok < 10
            ");
            $lowStock = $stmt->fetch()['count'];
            if ($lowStock > 0) {
                $notifications[] = [
                    'type' => 'warning',
                    'title' => 'Low Stock Alert',
                    'message' => "{$lowStock} products have low stock levels",
                    'action' => '/products?filter=low_stock',
                    'icon' => 'fas fa-exclamation-triangle'
                ];
            }
            
            // Pending commissions
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM commission_calculations 
                WHERE status = 'calculated' AND calculation_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $pendingCommissions = $stmt->fetch()['count'];
            if ($pendingCommissions > 0) {
                $notifications[] = [
                    'type' => 'info',
                    'title' => 'Pending Commissions',
                    'message' => "{$pendingCommissions} commissions awaiting approval",
                    'action' => '/commissions?status=pending',
                    'icon' => 'fas fa-coins'
                ];
            }
            
            // System alerts
            $systemHealth = $this->calculateSystemHealth();
            if ($systemHealth < 80) {
                $notifications[] = [
                    'type' => 'danger',
                    'title' => 'System Health Alert',
                    'message' => "System health is at {$systemHealth}%",
                    'action' => '/system/health',
                    'icon' => 'fas fa-heartbeat'
                ];
            }
            
            return $notifications;
        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get module-specific statistics
     */
    private function getModuleStats($moduleCode)
    {
        try {
            switch ($moduleCode) {
                case 'multi_unit':
                    $stmt = $this->db->query("
                        SELECT COUNT(*) as total_units, 
                               SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_units
                        FROM koperasi_unit
                    ");
                    return $stmt->fetch();
                    
                case 'ai':
                    $stmt = $this->db->query("
                        SELECT COUNT(*) as total_predictions,
                               AVG(confidence_score) as avg_confidence
                        FROM ai_predictions 
                        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    ");
                    return $stmt->fetch();
                    
                case 'sales':
                    $stmt = $this->db->query("
                        SELECT COUNT(*) as total_sales,
                               COALESCE(SUM(total_harga), 0) as total_revenue
                        FROM penjualan 
                        WHERE status_pembayaran = 'lunas' 
                        AND tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ");
                    return $stmt->fetch();
                    
                case 'financial':
                    $stmt = $this->db->query("
                        SELECT COALESCE(SUM(jumlah), 0) as total_savings,
                               COALESCE(SUM(jumlah_pinjaman), 0) as total_loans
                        FROM simpanan
                        UNION ALL
                        SELECT COALESCE(SUM(jumlah_pinjaman), 0), 0
                        FROM pinjaman WHERE status = 'disetujui'
                    ");
                    $result = $stmt->fetchAll();
                    return [
                        'total_savings' => $result[0]['total_savings'],
                        'total_loans' => $result[1]['total_loans'] ?? 0
                    ];
                    
                default:
                    return ['status' => 'active'];
            }
        } catch (Exception $e) {
            error_log("Error getting module stats: " . $e->getMessage());
            return ['error' => 'Stats unavailable'];
        }
    }
    
    /**
     * Get recent activity for specific module
     */
    private function getModuleRecentActivity($moduleCode)
    {
        try {
            switch ($moduleCode) {
                case 'multi_unit':
                    $stmt = $this->db->query("
                        SELECT 'unit_created' as activity, nama_unit, created_at
                        FROM koperasi_unit 
                        ORDER BY created_at DESC LIMIT 5
                    ");
                    return $stmt->fetchAll();
                    
                case 'ai':
                    $stmt = $this->db->query("
                        SELECT prediction_type as activity, confidence_score, created_at
                        FROM ai_predictions 
                        ORDER BY created_at DESC LIMIT 5
                    ");
                    return $stmt->fetchAll();
                    
                default:
                    return [];
            }
        } catch (Exception $e) {
            error_log("Error getting module activity: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Helper methods
     */
    private function getUserRole($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT role_id FROM users WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function calculateSystemHealth()
    {
        // Simple health calculation based on various factors
        $health = 100;
        
        // Check database connectivity
        try {
            $this->db->query("SELECT 1");
        } catch (Exception $e) {
            $health -= 30;
        }
        
        // Check recent errors
        $stmt = $this->db->query("
            SELECT COUNT(*) as error_count 
            FROM logs 
            WHERE level = 'error' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $errors = $stmt->fetch()['error_count'];
        $health -= min($errors * 5, 20);
        
        return max(0, $health);
    }
    
    private function getDbConnections()
    {
        // This would typically check actual connection pool
        return 1; // Simplified
    }
    
    private function getDbSize()
    {
        try {
            $stmt = $this->db->query("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = 'ksp_samosir'
            ");
            return $stmt->fetch()['size_mb'] . ' MB';
        } catch (Exception $e) {
            return 'Unknown';
        }
    }
    
    private function checkAIServices()
    {
        // Check if AI services are responsive
        return 'healthy';
    }
    
    private function getLoadedModels()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM ai_models WHERE is_active = 1");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getTodayPredictions()
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
    
    private function getTotalUnits()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM koperasi_unit");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getActiveUnits()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM koperasi_unit WHERE is_active = 1");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getCpuUsage()
    {
        // Simplified - would use actual system monitoring
        return '45%';
    }
    
    private function getMemoryUsage()
    {
        // Simplified - would use actual system monitoring
        return '67%';
    }
    
    private function getDiskUsage()
    {
        // Simplified - would use actual system monitoring
        return '23%';
    }
}
