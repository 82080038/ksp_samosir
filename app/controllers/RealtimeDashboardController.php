<?php
/**
 * Real-time Dashboard Controller
 * Batch 7: Advanced Analytics
 */

class RealtimeDashboardController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Get real-time dashboard data
     */
    public function getRealtimeDashboard($userId = null)
    {
        try {
            $userId = $userId ?? $_SESSION['user_id'] ?? null;
            
            return [
                'metrics' => $this->getRealtimeMetrics(),
                'widgets' => $this->getUserWidgets($userId),
                'activities' => $this->getRecentActivities(),
                'predictions' => $this->getPredictiveInsights(),
                'alerts' => $this->getSystemAlerts()
            ];
        } catch (Exception $e) {
            error_log("Error getting realtime dashboard: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get real-time metrics
     */
    public function getRealtimeMetrics()
    {
        try {
            // Update metrics if needed
            $this->updateRealtimeMetrics();
            
            $stmt = $this->db->query("
                SELECT * FROM v_realtime_dashboard_metrics
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting realtime metrics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update real-time metrics
     */
    private function updateRealtimeMetrics()
    {
        try {
            // Check if metrics need updating
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as needs_update
                FROM realtime_cache 
                WHERE cache_key IN ('sales_total_revenue', 'sales_total_orders', 'customers_active_count')
                AND expires_at <= NOW()
            ");
            $result = $stmt->fetch();
            
            if ($result['needs_update'] > 0) {
                $this->db->query("CALL UpdateRealtimeSalesMetrics()");
            }
        } catch (Exception $e) {
            error_log("Error updating realtime metrics: " . $e->getMessage());
        }
    }
    
    /**
     * Get user widgets
     */
    public function getUserWidgets($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    dw.*,
                    dup.is_visible,
                    dup.position_x,
                    dup.position_y,
                    dup.width,
                    dup.height,
                    dup.custom_config
                FROM dashboard_widgets dw
                LEFT JOIN dashboard_user_preferences dup ON dw.id = dup.widget_id AND dup.user_id = ?
                WHERE dw.is_active = 1
                ORDER BY dup.position_y, dup.position_x
            ");
            $stmt->execute([$userId]);
            $widgets = $stmt->fetchAll();
            
            // Get widget data for each widget
            foreach ($widgets as &$widget) {
                $widget['data'] = $this->getWidgetData($widget);
            }
            
            return $widgets;
        } catch (Exception $e) {
            error_log("Error getting user widgets: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get widget data
     */
    private function getWidgetData($widget)
    {
        try {
            $config = json_decode($widget['config'] ?? '{}', true);
            $customConfig = json_decode($widget['custom_config'] ?? '{}', true);
            $config = array_merge($config, $customConfig);
            
            switch ($widget['data_source']) {
                case 'sales_total_revenue':
                    return $this->getSalesRevenueMetric($config);
                case 'sales_total_orders':
                    return $this->getSalesOrdersMetric($config);
                case 'customers_active_count':
                    return $this->getActiveCustomersMetric($config);
                case 'sales_conversion_rate':
                    return $this->getConversionRateMetric($config);
                case 'sales_daily_trend':
                    return $this->getSalesTrendData($config);
                case 'products_top_selling':
                    return $this->getTopProductsData($config);
                case 'commission_by_role':
                    return $this->getCommissionByRoleData($config);
                case 'activities_recent':
                    return $this->getRecentActivitiesData($config);
                case 'ai_recommendations_performance':
                    return $this->getAIRecommendationsData($config);
                default:
                    return [];
            }
        } catch (Exception $e) {
            error_log("Error getting widget data: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get sales revenue metric
     */
    private function getSalesRevenueMetric($config)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COALESCE(SUM(total_harga), 0) as today_revenue,
                    COALESCE((SELECT SUM(total_harga) FROM penjualan WHERE DATE(tanggal_penjualan) = CURDATE() - INTERVAL 1 DAY AND status_pembayaran = 'lunas'), 0) as yesterday_revenue,
                    COALESCE((SELECT SUM(total_harga) FROM penjualan WHERE DATE(tanggal_penjualan) >= CURDATE() - INTERVAL 7 DAY AND status_pembayaran = 'lunas'), 0) as week_revenue
                FROM penjualan 
                WHERE DATE(tanggal_penjualan) = CURDATE() AND status_pembayaran = 'lunas'
            ");
            $result = $stmt->fetch();
            
            $change = $result['yesterday_revenue'] > 0 ? 
                (($result['today_revenue'] - $result['yesterday_revenue']) / $result['yesterday_revenue']) * 100 : 0;
            
            return [
                'value' => $result['today_revenue'],
                'change' => round($change, 2),
                'week_total' => $result['week_revenue'],
                'formatted' => 'Rp' . number_format($result['today_revenue'], 0, ',', '.'),
                'color' => $config['color'] ?? '#28a745'
            ];
        } catch (Exception $e) {
            error_log("Error getting sales revenue metric: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get sales orders metric
     */
    private function getSalesOrdersMetric($config)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as today_orders,
                    (SELECT COUNT(*) FROM penjualan WHERE DATE(tanggal_penjualan) = CURDATE() - INTERVAL 1 DAY) as yesterday_orders
                FROM penjualan 
                WHERE DATE(tanggal_penjualan) = CURDATE()
            ");
            $result = $stmt->fetch();
            
            $change = $result['yesterday_orders'] > 0 ? 
                (($result['today_orders'] - $result['yesterday_orders']) / $result['yesterday_orders']) * 100 : 0;
            
            return [
                'value' => $result['today_orders'],
                'change' => round($change, 2),
                'formatted' => number_format($result['today_orders'], 0),
                'color' => $config['color'] ?? '#007bff'
            ];
        } catch (Exception $e) {
            error_log("Error getting sales orders metric: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get active customers metric
     */
    private function getActiveCustomersMetric($config)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT user_id) as active_customers
                FROM penjualan 
                WHERE DATE(tanggal_penjualan) = CURDATE()
            ");
            $result = $stmt->fetch();
            
            return [
                'value' => $result['active_customers'],
                'formatted' => number_format($result['active_customers'], 0),
                'color' => $config['color'] ?? '#ffc107'
            ];
        } catch (Exception $e) {
            error_log("Error getting active customers metric: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get conversion rate metric
     */
    private function getConversionRateMetric($config)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_visitors,
                    COUNT(CASE WHEN status_pembayaran = 'lunas' THEN 1 END) as converted_customers
                FROM penjualan 
                WHERE DATE(tanggal_penjualan) = CURDATE()
            ");
            $result = $stmt->fetch();
            
            $conversionRate = $result['total_visitors'] > 0 ? 
                ($result['converted_customers'] / $result['total_visitors']) * 100 : 0;
            
            return [
                'value' => round($conversionRate, 2),
                'formatted' => round($conversionRate, 2) . '%',
                'color' => $config['color'] ?? '#dc3545'
            ];
        } catch (Exception $e) {
            error_log("Error getting conversion rate metric: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get sales trend data
     */
    private function getSalesTrendData($config)
    {
        try {
            $period = $config['period'] ?? '7d';
            $days = $period === '7d' ? 7 : ($period === '30d' ? 30 : 7);
            
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(tanggal_penjualan) as date,
                    COUNT(*) as orders,
                    SUM(total_harga) as revenue
                FROM penjualan 
                WHERE status_pembayaran = 'lunas'
                AND tanggal_penjualan >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(tanggal_penjualan)
                ORDER BY date
            ");
            $stmt->execute([$days]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting sales trend data: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get top products data
     */
    private function getTopProductsData($config)
    {
        try {
            $limit = $config['limit'] ?? 5;
            $showRevenue = $config['show_revenue'] ?? false;
            
            $stmt = $this->db->prepare("
                SELECT 
                    p.nama_produk as name,
                    SUM(dp.jumlah) as quantity_sold,
                    " . ($showRevenue ? "SUM(dp.total_harga) as revenue," : "") . "
                    p.harga as price
                FROM penjualan pen
                JOIN detail_penjualan dp ON pen.id = dp.id_penjualan
                JOIN produk p ON dp.id_produk = p.id
                WHERE pen.status_pembayaran = 'lunas'
                AND DATE(pen.tanggal_penjualan) = CURDATE()
                GROUP BY p.id, p.nama_produk, p.harga
                ORDER BY quantity_sold DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting top products data: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get commission by role data
     */
    private function getCommissionByRoleData($config)
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    r.name as role,
                    COALESCE(SUM(cc.commission_amount), 0) as total_commission
                FROM commission_calculations cc
                JOIN users u ON cc.user_id = u.id
                JOIN roles r ON u.role_id = r.id
                WHERE cc.status = 'paid'
                AND cc.calculation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY r.id, r.name
                ORDER BY total_commission DESC
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting commission by role data: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent activities data
     */
    private function getRecentActivitiesData($config)
    {
        try {
            $limit = $config['limit'] ?? 10;
            $showTimestamp = $config['show_timestamp'] ?? true;
            
            $stmt = $this->db->prepare("
                SELECT 
                    re.event_type,
                    re.event_data,
                    u.full_name as user_name,
                    re.created_at
                FROM realtime_events re
                LEFT JOIN users u ON re.user_id = u.id
                ORDER BY re.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting recent activities data: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get AI recommendations data
     */
    private function getAIRecommendationsData($config)
    {
        try {
            $stmt = $this->db->query("
                SELECT * FROM v_ai_recommendation_performance
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting AI recommendations data: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 20)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM v_recent_activities
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting recent activities: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get predictive insights
     */
    public function getPredictiveInsights($limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM v_predictive_insights
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting predictive insights: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get system alerts
     */
    public function getSystemAlerts()
    {
        try {
            $alerts = [];
            
            // Check for low stock products
            $stmt = $this->db->query("
                SELECT COUNT(*) as low_stock_count
                FROM produk 
                WHERE stok < 10
            ");
            $result = $stmt->fetch();
            if ($result['low_stock_count'] > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => "{$result['low_stock_count']} products have low stock",
                    'action' => '/products?filter=low_stock'
                ];
            }
            
            // Check for pending commissions
            $stmt = $this->db->query("
                SELECT COUNT(*) as pending_count
                FROM commission_calculations 
                WHERE status = 'calculated'
                AND calculation_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ");
            $result = $stmt->fetch();
            if ($result['pending_count'] > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'message' => "{$result['pending_count']} commissions pending approval",
                    'action' => '/commissions?status=pending'
                ];
            }
            
            // Check for high-risk customers (from predictions)
            $stmt = $this->db->query("
                SELECT COUNT(*) as high_risk_count
                FROM v_predictive_insights 
                WHERE insight_level = 'High Risk'
            ");
            $result = $stmt->fetch();
            if ($result['high_risk_count'] > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'message' => "{$result['high_risk_count']} customers identified as high risk",
                    'action' => '/customers?filter=high_risk'
                ];
            }
            
            return $alerts;
        } catch (Exception $e) {
            error_log("Error getting system alerts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update user widget preferences
     */
    public function updateWidgetPreferences($userId, $widgetId, $preferences)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO dashboard_user_preferences 
                (user_id, widget_id, is_visible, position_x, position_y, width, height, custom_config)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    is_visible = VALUES(is_visible),
                    position_x = VALUES(position_x),
                    position_y = VALUES(position_y),
                    width = VALUES(width),
                    height = VALUES(height),
                    custom_config = VALUES(custom_config),
                    updated_at = NOW()
            ");
            
            $result = $stmt->execute([
                $userId,
                $widgetId,
                $preferences['is_visible'] ?? true,
                $preferences['position_x'] ?? 0,
                $preferences['position_y'] ?? 0,
                $preferences['width'] ?? 4,
                $preferences['height'] ?? 3,
                json_encode($preferences['custom_config'] ?? [])
            ]);
            
            return $result ? 
                ['success' => true, 'message' => 'Widget preferences updated'] : 
                ['success' => false, 'message' => 'Failed to update preferences'];
        } catch (Exception $e) {
            error_log("Error updating widget preferences: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Refresh specific widget data
     */
    public function refreshWidgetData($widgetId, $userId = null)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM dashboard_widgets 
                WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$widgetId]);
            $widget = $stmt->fetch();
            
            if ($widget) {
                $widget['data'] = $this->getWidgetData($widget);
                return [
                    'success' => true,
                    'widget' => $widget
                ];
            }
            
            return ['success' => false, 'message' => 'Widget not found'];
        } catch (Exception $e) {
            error_log("Error refreshing widget data: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Get dashboard analytics
     */
    public function getDashboardAnalytics($startDate = null, $endDate = null)
    {
        try {
            $startDate = $startDate ?? date('Y-m-01');
            $endDate = $endDate ?? date('Y-m-t');
            
            return [
                'widget_usage' => $this->getWidgetUsageAnalytics($startDate, $endDate),
                'user_engagement' => $this->getUserEngagementAnalytics($startDate, $endDate),
                'prediction_accuracy' => $this->getPredictionAccuracyAnalytics($startDate, $endDate),
                'realtime_performance' => $this->getRealtimePerformanceAnalytics($startDate, $endDate)
            ];
        } catch (Exception $e) {
            error_log("Error getting dashboard analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get widget usage analytics
     */
    private function getWidgetUsageAnalytics($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    dw.widget_name,
                    COUNT(dup.user_id) as unique_users,
                    COUNT(dup.id) as total_impressions,
                    SUM(CASE WHEN dup.is_visible = 1 THEN 1 ELSE 0 END) as visible_count
                FROM dashboard_widgets dw
                LEFT JOIN dashboard_user_preferences dup ON dw.id = dup.widget_id
                WHERE dup.updated_at BETWEEN ? AND ?
                GROUP BY dw.id, dw.widget_name
                ORDER BY total_impressions DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting widget usage analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user engagement analytics
     */
    private function getUserEngagementAnalytics($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(DISTINCT user_id) as active_users,
                    COUNT(*) as total_interactions
                FROM realtime_events
                WHERE created_at BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting user engagement analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get prediction accuracy analytics
     */
    private function getPredictionAccuracyAnalytics($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    pm.model_name,
                    pm.model_type,
                    COUNT(pp.id) as total_predictions,
                    AVG(pp.confidence_score) as avg_confidence,
                    pm.accuracy_score
                FROM predictive_predictions pp
                JOIN predictive_models pm ON pp.model_id = pm.id
                WHERE pp.predicted_at BETWEEN ? AND ?
                GROUP BY pm.id, pm.model_name, pm.model_type, pm.accuracy_score
                ORDER BY avg_confidence DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting prediction accuracy analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get realtime performance analytics
     */
    private function getRealtimePerformanceAnalytics($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    cache_key,
                    AVG(TIMESTAMPDIFF(SECOND, updated_at, expires_at)) as avg_cache_duration,
                    COUNT(*) as update_count
                FROM realtime_cache
                WHERE updated_at BETWEEN ? AND ?
                GROUP BY cache_key
                ORDER BY update_count DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting realtime performance analytics: " . $e->getMessage());
            return [];
        }
    }
}
