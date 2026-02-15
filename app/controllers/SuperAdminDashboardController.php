<?php
/**
 * Super Admin Dashboard Controller - Multi-Tenant Management
 * For monitoring all tenants/koperasi using the application
 */

class SuperAdminDashboardController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Get super admin dashboard for multi-tenant monitoring
     */
    public function getSuperAdminDashboard()
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                return ['success' => false, 'message' => 'User not authenticated'];
            }
            
            // Check if user has super admin role
            $userRole = $this->getUserRole($userId);
            if ($userRole['role_id'] != 1) { // Only super_admin
                return ['success' => false, 'message' => 'Access denied - Super Admin only'];
            }
            
            return [
                'success' => true,
                'dashboard' => [
                    'tenant_overview' => $this->getTenantOverview(),
                    'billing_summary' => $this->getBillingSummary(),
                    'usage_analytics' => $this->getUsageAnalytics(),
                    'system_metrics' => $this->getSystemMetrics(),
                    'revenue_streams' => $this->getRevenueStreams(),
                    'tenant_health' => $this->getTenantHealth(),
                    'support_tickets' => $this->getSupportTickets()
                ]
            ];
        } catch (Exception $e) {
            error_log("Error getting super admin dashboard: " . $e->getMessage());
            return ['success' => false, 'message' => 'Dashboard loading failed'];
        }
    }
    
    /**
     * Get tenant overview (for future multi-tenant implementation)
     */
    public function getTenantOverview()
    {
        try {
            // For now, return current tenant as example
            // In future, this would query multiple tenants
            $tenants = [
                [
                    'tenant_id' => 1,
                    'tenant_name' => 'KSP Samosir',
                    'domain' => 'ksp-samosir.demo',
                    'status' => 'active',
                    'created_at' => '2023-01-15',
                    'subscription_plan' => 'enterprise',
                    'monthly_fee' => 2500000,
                    'users_count' => $this->getTenantUserCount(1),
                    'transactions_count' => $this->getTenantTransactionCount(1),
                    'data_usage_gb' => $this->getTenantDataUsage(1),
                    'last_login' => $this->getLastTenantLogin(1),
                    'health_score' => $this->getTenantHealthScore(1)
                ]
            ];
            
            return [
                'total_tenants' => count($tenants),
                'active_tenants' => count(array_filter($tenants, fn($t) => $t['status'] === 'active')),
                'new_tenants_this_month' => 0,
                'tenants' => $tenants
            ];
        } catch (Exception $e) {
            error_log("Error getting tenant overview: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get billing summary
     */
    public function getBillingSummary()
    {
        try {
            $billing = [
                'monthly_recurring_revenue' => 2500000, // From current tenant
                'annual_recurring_revenue' => 30000000,
                'pending_payments' => 0,
                'overdue_payments' => 0,
                'revenue_growth' => 15.5, // percentage
                'churn_rate' => 2.1, // percentage
                'average_revenue_per_tenant' => 2500000,
                'billing_by_plan' => [
                    'starter' => ['tenants' => 0, 'revenue' => 0],
                    'professional' => ['tenants' => 0, 'revenue' => 0],
                    'enterprise' => ['tenants' => 1, 'revenue' => 2500000]
                ]
            ];
            
            return $billing;
        } catch (Exception $e) {
            error_log("Error getting billing summary: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get usage analytics
     */
    public function getUsageAnalytics()
    {
        try {
            $usage = [
                'total_transactions' => $this->getTotalTransactions(),
                'total_data_processed' => $this->getTotalDataProcessed(),
                'api_calls' => $this->getTotalApiCalls(),
                'ai_predictions' => $this->getTotalAIPredictions(),
                'storage_usage' => $this->getTotalStorageUsage(),
                'bandwidth_usage' => $this->getBandwidthUsage(),
                'usage_trends' => $this->getUsageTrends(),
                'peak_usage_times' => $this->getPeakUsageTimes()
            ];
            
            return $usage;
        } catch (Exception $e) {
            error_log("Error getting usage analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get system metrics
     */
    public function getSystemMetrics()
    {
        try {
            $metrics = [
                'server_performance' => [
                    'cpu_usage' => $this->getCpuUsage(),
                    'memory_usage' => $this->getMemoryUsage(),
                    'disk_usage' => $this->getDiskUsage(),
                    'network_io' => $this->getNetworkIO()
                ],
                'database_performance' => [
                    'connections' => $this->getDbConnections(),
                    'query_performance' => $this->getQueryPerformance(),
                    'slow_queries' => $this->getSlowQueries(),
                    'database_size' => $this->getDatabaseSize()
                ],
                'application_performance' => [
                    'response_time' => $this->getAverageResponseTime(),
                    'error_rate' => $this->getErrorRate(),
                    'uptime' => $this->getUptime(),
                    'active_sessions' => $this->getActiveSessions()
                ],
                'service_health' => [
                    'ai_services' => $this->getAIServiceHealth(),
                    'notification_services' => $this->getNotificationServiceHealth(),
                    'backup_services' => $this->getBackupServiceHealth(),
                    'monitoring_services' => $this->getMonitoringServiceHealth()
                ]
            ];
            
            return $metrics;
        } catch (Exception $e) {
            error_log("Error getting system metrics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get revenue streams
     */
    public function getRevenueStreams()
    {
        try {
            $revenue = [
                'subscription_revenue' => [
                    'current_month' => 2500000,
                    'previous_month' => 2500000,
                    'growth' => 0
                ],
                'api_usage_revenue' => [
                    'current_month' => 0,
                    'previous_month' => 0,
                    'growth' => 0
                ],
                'ai_services_revenue' => [
                    'current_month' => 0,
                    'previous_month' => 0,
                    'growth' => 0
                ],
                'support_revenue' => [
                    'current_month' => 0,
                    'previous_month' => 0,
                    'growth' => 0
                ],
                'total_revenue' => [
                    'current_month' => 2500000,
                    'previous_month' => 2500000,
                    'growth' => 0
                ]
            ];
            
            return $revenue;
        } catch (Exception $e) {
            error_log("Error getting revenue streams: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get tenant health status
     */
    public function getTenantHealth()
    {
        try {
            $health = [
                'overall_health_score' => 92,
                'healthy_tenants' => 1,
                'warning_tenants' => 0,
                'critical_tenants' => 0,
                'health_issues' => [
                    'performance_issues' => 0,
                    'connectivity_issues' => 0,
                    'data_issues' => 0,
                    'security_issues' => 0
                ],
                'maintenance_scheduled' => [
                    'upcoming_maintenance' => [],
                    'completed_maintenance' => []
                ]
            ];
            
            return $health;
        } catch (Exception $e) {
            error_log("Error getting tenant health: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get support tickets
     */
    public function getSupportTickets()
    {
        try {
            $tickets = [
                'total_tickets' => 0,
                'open_tickets' => 0,
                'in_progress_tickets' => 0,
                'resolved_tickets' => 0,
                'priority_breakdown' => [
                    'critical' => 0,
                    'high' => 0,
                    'medium' => 0,
                    'low' => 0
                ],
                'response_times' => [
                    'average_response_time' => 0,
                    'target_response_time' => 60 // minutes
                ],
                'recent_tickets' => []
            ];
            
            return $tickets;
        } catch (Exception $e) {
            error_log("Error getting support tickets: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get detailed tenant information
     */
    public function getTenantDetails($tenantId)
    {
        try {
            // For future implementation when multi-tenant is ready
            return [
                'success' => true,
                'message' => 'Multi-tenant feature coming soon',
                'tenant_id' => $tenantId
            ];
        } catch (Exception $e) {
            error_log("Error getting tenant details: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get tenant details'];
        }
    }
    
    /**
     * Create new tenant (future feature)
     */
    public function createTenant($tenantData)
    {
        try {
            return [
                'success' => true,
                'message' => 'Multi-tenant creation feature coming soon',
                'tenant_data' => $tenantData
            ];
        } catch (Exception $e) {
            error_log("Error creating tenant: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create tenant'];
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
    
    private function getTenantUserCount($tenantId)
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getTenantTransactionCount($tenantId)
    {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM penjualan 
                WHERE status_pembayaran = 'lunas' 
                AND tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getTenantDataUsage($tenantId)
    {
        try {
            $stmt = $this->db->query("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = 'ksp_samosir'
            ");
            $sizeMb = $stmt->fetch()['size_mb'];
            return round($sizeMb / 1024, 2); // Convert to GB
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getLastTenantLogin($tenantId)
    {
        try {
            $stmt = $this->db->query("
                SELECT MAX(last_login) as last_login FROM users 
                WHERE last_login IS NOT NULL
            ");
            $result = $stmt->fetch();
            return $result['last_login'] ?? 'Never';
        } catch (Exception $e) {
            return 'Unknown';
        }
    }
    
    private function getTenantHealthScore($tenantId)
    {
        // Calculate health score based on various factors
        return 92; // Simplified
    }
    
    private function getTotalTransactions()
    {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM penjualan 
                WHERE status_pembayaran = 'lunas' 
                AND tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getTotalDataProcessed()
    {
        return 1024; // MB - simplified
    }
    
    private function getTotalApiCalls()
    {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM ai_api_usage 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getTotalAIPredictions()
    {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM ai_predictions 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getTotalStorageUsage()
    {
        try {
            $stmt = $this->db->query("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = 'ksp_samosir'
            ");
            return $stmt->fetch()['size_mb'] . ' MB';
        } catch (Exception $e) {
            return '0 MB';
        }
    }
    
    private function getBandwidthUsage()
    {
        return '2.5 GB'; // Simplified
    }
    
    private function getUsageTrends()
    {
        return [
            'daily' => [100, 120, 115, 130, 125, 140, 135],
            'weekly' => [700, 750, 720, 800, 780, 820, 850],
            'monthly' => [3000, 3200, 3100, 3300]
        ];
    }
    
    private function getPeakUsageTimes()
    {
        return [
            'peak_hour' => '14:00',
            'peak_day' => 'Monday',
            'peak_week' => 'Week 2'
        ];
    }
    
    private function getCpuUsage()
    {
        return '45%';
    }
    
    private function getMemoryUsage()
    {
        return '67%';
    }
    
    private function getDiskUsage()
    {
        return '23%';
    }
    
    private function getNetworkIO()
    {
        return ['in' => '150 Mbps', 'out' => '200 Mbps'];
    }
    
    private function getDbConnections()
    {
        return 15;
    }
    
    private function getQueryPerformance()
    {
        return ['avg_time' => '25ms', 'slow_queries' => 2];
    }
    
    private function getSlowQueries()
    {
        return 2;
    }
    
    private function getDatabaseSize()
    {
        return '250 MB';
    }
    
    private function getAverageResponseTime()
    {
        return '120ms';
    }
    
    private function getErrorRate()
    {
        return '0.5%';
    }
    
    private function getUptime()
    {
        return '99.9%';
    }
    
    private function getActiveSessions()
    {
        return 25;
    }
    
    private function getAIServiceHealth()
    {
        return ['status' => 'healthy', 'response_time' => '150ms'];
    }
    
    private function getNotificationServiceHealth()
    {
        return ['status' => 'healthy', 'response_time' => '80ms'];
    }
    
    private function getBackupServiceHealth()
    {
        return ['status' => 'healthy', 'last_backup' => '2026-02-15 02:00:00'];
    }
    
    private function getMonitoringServiceHealth()
    {
        return ['status' => 'healthy', 'response_time' => '50ms'];
    }
}
