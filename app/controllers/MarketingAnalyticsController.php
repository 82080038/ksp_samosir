<?php
/**
 * Advanced Marketing Analytics
 * Batch 5: Advanced Sales & Marketing
 */

class MarketingAnalyticsController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Get comprehensive marketing analytics dashboard
     */
    public function getMarketingDashboard($startDate = null, $endDate = null)
    {
        try {
            $startDate = $startDate ?? date('Y-m-01');
            $endDate = $endDate ?? date('Y-m-t');
            
            return [
                'sales_performance' => $this->getSalesPerformance($startDate, $endDate),
                'customer_analytics' => $this->getCustomerAnalytics($startDate, $endDate),
                'product_analytics' => $this->getProductAnalytics($startDate, $endDate),
                'campaign_performance' => $this->getCampaignPerformance($startDate, $endDate),
                'commission_analytics' => $this->getCommissionAnalytics($startDate, $endDate),
                'ai_recommendations_performance' => $this->getAIRecommendationsPerformance($startDate, $endDate),
                'market_trends' => $this->getMarketTrends($startDate, $endDate)
            ];
        } catch (Exception $e) {
            error_log("Error getting marketing dashboard: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get sales performance metrics
     */
    public function getSalesPerformance($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT p.id) as total_orders,
                    COUNT(DISTINCT p.id_user) as unique_customers,
                    COALESCE(SUM(p.total_harga), 0) as total_revenue,
                    COALESCE(AVG(p.total_harga), 0) as avg_order_value,
                    COUNT(DISTINCT CASE WHEN p.status = 'selesai' THEN p.id END) as completed_orders,
                    ROUND(COUNT(DISTINCT CASE WHEN p.status = 'selesai' THEN p.id END) * 100.0 / COUNT(DISTINCT p.id), 2) as completion_rate,
                    COALESCE(SUM(CASE WHEN p.status = 'selesai' THEN p.total_harga ELSE 0 END), 0) as completed_revenue
                FROM penjualan p
                WHERE p.created_at BETWEEN ? AND ?
            ");
            $stmt->execute([$startDate, $endDate]);
            $overall = $stmt->fetch();
            
            // Get daily trend
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as orders,
                    SUM(total_harga) as revenue,
                    COUNT(DISTINCT id_user) as customers
                FROM penjualan
                WHERE created_at BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$startDate, $endDate]);
            $dailyTrend = $stmt->fetchAll();
            
            // Get top performers
            $stmt = $this->db->prepare("
                SELECT 
                    u.name,
                    u.role,
                    COUNT(p.id) as total_sales,
                    SUM(p.total_harga) as total_revenue,
                    AVG(p.total_harga) as avg_sale_value
                FROM penjualan p
                JOIN users u ON p.id_user = u.id
                WHERE p.status = 'selesai'
                AND p.created_at BETWEEN ? AND ?
                GROUP BY u.id, u.name, u.role
                ORDER BY total_revenue DESC
                LIMIT 10
            ");
            $stmt->execute([$startDate, $endDate]);
            $topPerformers = $stmt->fetchAll();
            
            return [
                'overall' => $overall,
                'daily_trend' => $dailyTrend,
                'top_performers' => $topPerformers
            ];
        } catch (Exception $e) {
            error_log("Error getting sales performance: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get customer analytics
     */
    public function getCustomerAnalytics($startDate, $endDate)
    {
        try {
            // Customer segmentation
            $stmt = $this->db->prepare("
                SELECT 
                    CASE 
                        WHEN total_spent >= 10000000 THEN 'Platinum'
                        WHEN total_spent >= 5000000 THEN 'Gold'
                        WHEN total_spent >= 1000000 THEN 'Silver'
                        ELSE 'Bronze'
                    END as segment,
                    COUNT(*) as customer_count,
                    SUM(total_spent) as total_segment_revenue,
                    AVG(total_spent) as avg_customer_value,
                    AVG(order_count) as avg_orders_per_customer
                FROM (
                    SELECT 
                        p.id_user,
                        SUM(p.total_harga) as total_spent,
                        COUNT(p.id) as order_count
                    FROM penjualan p
                    WHERE p.status = 'selesai'
                    AND p.created_at BETWEEN ? AND ?
                    GROUP BY p.id_user
                ) customer_data
                GROUP BY segment
                ORDER BY total_segment_revenue DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            $segmentation = $stmt->fetchAll();
            
            // Customer retention
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT p.id_user) as new_customers,
                    COUNT(DISTINCT CASE WHEN p.prev_orders > 0 THEN p.id_user END) as returning_customers,
                    ROUND(COUNT(DISTINCT CASE WHEN p.prev_orders > 0 THEN p.id_user END) * 100.0 / COUNT(DISTINCT p.id_user), 2) as retention_rate
                FROM (
                    SELECT 
                        p.id_user,
                        COUNT(CASE WHEN p.created_at < ? THEN 1 END) as prev_orders
                    FROM penjualan p
                    WHERE p.status = 'selesai'
                    AND p.created_at <= ?
                    GROUP BY p.id_user
                ) p
            ");
            $stmt->execute([$startDate, $endDate]);
            $retention = $stmt->fetch();
            
            // Customer lifetime value
            $stmt = $this->db->prepare("
                SELECT 
                    AVG(lifetime_value) as avg_ltv,
                    MAX(lifetime_value) as max_ltv,
                    MIN(lifetime_value) as min_ltv,
                    PERCENTILE_CONT(0.5) WITHIN GROUP (ORDER BY lifetime_value) as median_ltv
                FROM (
                    SELECT 
                        p.id_user,
                        SUM(p.total_harga) as lifetime_value
                    FROM penjualan p
                    WHERE p.status = 'selesai'
                    GROUP BY p.id_user
                ) ltv_data
            ");
            $stmt->execute();
            $ltv = $stmt->fetch();
            
            return [
                'segmentation' => $segmentation,
                'retention' => $retention,
                'lifetime_value' => $ltv
            ];
        } catch (Exception $e) {
            error_log("Error getting customer analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get product analytics
     */
    public function getProductAnalytics($startDate, $endDate)
    {
        try {
            // Top selling products
            $stmt = $this->db->prepare("
                SELECT 
                    p.id,
                    p.nama_produk,
                    p.harga,
                    k.nama_kategori,
                    SUM(dp.jumlah) as total_sold,
                    SUM(dp.total_harga) as total_revenue,
                    COUNT(DISTINCT pen.id_user) as unique_buyers,
                    AVG(dp.harga) as avg_selling_price
                FROM penjualan pen
                JOIN detail_penjualan dp ON pen.id = dp.id_penjualan
                JOIN produk p ON dp.id_produk = p.id
                JOIN kategori_produk k ON p.kategori_id = k.id
                WHERE pen.status = 'selesai'
                AND pen.created_at BETWEEN ? AND ?
                GROUP BY p.id, p.nama_produk, p.harga, k.nama_kategori
                ORDER BY total_revenue DESC
                LIMIT 20
            ");
            $stmt->execute([$startDate, $endDate]);
            $topProducts = $stmt->fetchAll();
            
            // Category performance
            $stmt = $this->db->prepare("
                SELECT 
                    k.id,
                    k.nama_kategori,
                    COUNT(DISTINCT p.id) as total_products,
                    SUM(dp.jumlah) as total_sold,
                    SUM(dp.total_harga) as total_revenue,
                    AVG(dp.total_harga) as avg_category_revenue
                FROM penjualan pen
                JOIN detail_penjualan dp ON pen.id = dp.id_penjualan
                JOIN produk p ON dp.id_produk = p.id
                JOIN kategori_produk k ON p.kategori_id = k.id
                WHERE pen.status = 'selesai'
                AND pen.created_at BETWEEN ? AND ?
                GROUP BY k.id, k.nama_kategori
                ORDER BY total_revenue DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            $categoryPerformance = $stmt->fetchAll();
            
            // Product affinity (products bought together)
            $stmt = $this->db->prepare("
                SELECT 
                    p1.nama_produk as product_1,
                    p2.nama_produk as product_2,
                    COUNT(*) as bought_together_count,
                    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM penjualan WHERE status = 'selesai' AND created_at BETWEEN ? AND ?), 2) as affinity_percentage
                FROM penjualan pen
                JOIN detail_penjualan dp1 ON pen.id = dp1.id_penjualan
                JOIN detail_penjualan dp2 ON pen.id = dp2.id_penjualan
                JOIN produk p1 ON dp1.id_produk = p1.id
                JOIN produk p2 ON dp2.id_produk = p2.id
                WHERE pen.status = 'selesai'
                AND pen.created_at BETWEEN ? AND ?
                AND dp1.id_produk < dp2.id_produk
                GROUP BY p1.id, p1.nama_produk, p2.id, p2.nama_produk
                HAVING bought_together_count >= 5
                ORDER BY bought_together_count DESC
                LIMIT 10
            ");
            $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
            $productAffinity = $stmt->fetchAll();
            
            return [
                'top_products' => $topProducts,
                'category_performance' => $categoryPerformance,
                'product_affinity' => $productAffinity
            ];
        } catch (Exception $e) {
            error_log("Error getting product analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get campaign performance
     */
    public function getCampaignPerformance($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    mc.id,
                    mc.campaign_name,
                    mc.campaign_type,
                    mc.status,
                    mc.start_date,
                    mc.end_date,
                    mc.budget,
                    COALESCE(SUM(cp.impressions), 0) as total_impressions,
                    COALESCE(SUM(cp.clicks), 0) as total_clicks,
                    COALESCE(SUM(cp.conversions), 0) as total_conversions,
                    COALESCE(SUM(cp.revenue), 0) as total_revenue,
                    COALESCE(SUM(cp.cost), 0) as total_cost,
                    ROUND(COALESCE(SUM(cp.revenue), 0) - COALESCE(SUM(cp.cost), 0), 2) as roi,
                    ROUND(COALESCE(SUM(cp.clicks), 0) * 100.0 / NULLIF(SUM(cp.impressions), 0), 2) as ctr,
                    ROUND(COALESCE(SUM(cp.conversions), 0) * 100.0 / NULLIF(SUM(cp.clicks), 0), 2) as conversion_rate
                FROM marketing_campaigns mc
                LEFT JOIN campaign_performance cp ON mc.id = cp.campaign_id
                WHERE (mc.start_date BETWEEN ? AND ? OR mc.end_date BETWEEN ? AND ?)
                GROUP BY mc.id, mc.campaign_name, mc.campaign_type, mc.status, mc.start_date, mc.end_date, mc.budget
                ORDER BY total_revenue DESC
            ");
            $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting campaign performance: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get commission analytics
     */
    public function getCommissionAnalytics($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    u.role,
                    COUNT(DISTINCT cc.user_id) as active_users,
                    COUNT(cc.id) as total_commissions,
                    SUM(cc.commission_amount) as total_commission_amount,
                    AVG(cc.commission_amount) as avg_commission,
                    SUM(CASE WHEN cc.status = 'paid' THEN cc.commission_amount ELSE 0 END) as paid_amount,
                    COUNT(CASE WHEN cc.status = 'paid' THEN 1 END) as paid_count,
                    ROUND(SUM(CASE WHEN cc.status = 'paid' THEN cc.commission_amount ELSE 0 END) * 100.0 / NULLIF(SUM(cc.commission_amount), 0), 2) as payout_rate
                FROM commission_calculations cc
                JOIN users u ON cc.user_id = u.id
                WHERE cc.calculation_date BETWEEN ? AND ?
                GROUP BY u.role
                ORDER BY total_commission_amount DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting commission analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get AI recommendations performance
     */
    public function getAIRecommendationsPerformance($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    recommendation_type,
                    COUNT(*) as total_recommendations,
                    SUM(CASE WHEN is_clicked = 1 THEN 1 ELSE 0 END) as total_clicks,
                    SUM(CASE WHEN is_purchased = 1 THEN 1 ELSE 0 END) as total_purchases,
                    AVG(confidence_score) as avg_confidence,
                    ROUND(SUM(CASE WHEN is_clicked = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as click_rate,
                    ROUND(SUM(CASE WHEN is_purchased = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as conversion_rate,
                    ROUND(SUM(CASE WHEN is_purchased = 1 THEN 1 ELSE 0 END) * 100.0 / NULLIF(SUM(CASE WHEN is_clicked = 1 THEN 1 ELSE 0 END), 0), 2) as click_to_purchase_rate
                FROM ai_recommendations
                WHERE created_at BETWEEN ? AND ?
                GROUP BY recommendation_type
                ORDER BY conversion_rate DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting AI recommendations performance: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get market trends
     */
    public function getMarketTrends($startDate, $endDate)
    {
        try {
            // Monthly trend comparison
            $stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as orders,
                    SUM(total_harga) as revenue,
                    COUNT(DISTINCT id_user) as customers,
                    AVG(total_harga) as avg_order_value
                FROM penjualan
                WHERE status = 'selesai'
                AND created_at BETWEEN DATE_SUB(?, INTERVAL 11 MONTH) AND ?
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
            ");
            $stmt->execute([$startDate, $endDate]);
            $monthlyTrend = $stmt->fetchAll();
            
            // Seasonal patterns
            $stmt = $this->db->prepare("
                SELECT 
                    MONTH(created_at) as month,
                    COUNT(*) as orders,
                    SUM(total_harga) as revenue,
                    AVG(total_harga) as avg_order_value
                FROM penjualan
                WHERE status = 'selesai'
                AND created_at >= DATE_SUB(?, INTERVAL 2 YEAR)
                GROUP BY MONTH(created_at)
                ORDER BY month
            ");
            $stmt->execute([$endDate]);
            $seasonalPatterns = $stmt->fetchAll();
            
            // Growth metrics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT id_user) as total_customers,
                    SUM(total_harga) as total_revenue,
                    COUNT(*) as total_orders,
                    AVG(total_harga) as avg_order_value
                FROM penjualan
                WHERE status = 'selesai'
                AND created_at BETWEEN ? AND ?
            ");
            $stmt->execute([$startDate, $endDate]);
            $currentPeriod = $stmt->fetch();
            
            // Previous period for comparison
            $prevStart = date('Y-m-d', strtotime($startDate . ' -1 month'));
            $prevEnd = date('Y-m-d', strtotime($endDate . ' -1 month'));
            
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT id_user) as total_customers,
                    SUM(total_harga) as total_revenue,
                    COUNT(*) as total_orders,
                    AVG(total_harga) as avg_order_value
                FROM penjualan
                WHERE status = 'selesai'
                AND created_at BETWEEN ? AND ?
            ");
            $stmt->execute([$prevStart, $prevEnd]);
            $previousPeriod = $stmt->fetch();
            
            // Calculate growth rates
            $growthRates = [
                'revenue_growth' => $previousPeriod['total_revenue'] > 0 ? 
                    round((($currentPeriod['total_revenue'] - $previousPeriod['total_revenue']) / $previousPeriod['total_revenue']) * 100, 2) : 0,
                'customer_growth' => $previousPeriod['total_customers'] > 0 ? 
                    round((($currentPeriod['total_customers'] - $previousPeriod['total_customers']) / $previousPeriod['total_customers']) * 100, 2) : 0,
                'order_growth' => $previousPeriod['total_orders'] > 0 ? 
                    round((($currentPeriod['total_orders'] - $previousPeriod['total_orders']) / $previousPeriod['total_orders']) * 100, 2) : 0
            ];
            
            return [
                'monthly_trend' => $monthlyTrend,
                'seasonal_patterns' => $seasonalPatterns,
                'current_period' => $currentPeriod,
                'previous_period' => $previousPeriod,
                'growth_rates' => $growthRates
            ];
        } catch (Exception $e) {
            error_log("Error getting market trends: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate automated insights
     */
    public function generateInsights($startDate, $endDate)
    {
        try {
            $insights = [];
            
            // Get top performing product
            $stmt = $this->db->prepare("
                SELECT p.nama_produk, SUM(dp.total_harga) as revenue
                FROM penjualan pen
                JOIN detail_penjualan dp ON pen.id = dp.id_penjualan
                JOIN produk p ON dp.id_produk = p.id
                WHERE pen.status = 'selesai'
                AND pen.created_at BETWEEN ? AND ?
                GROUP BY p.id, p.nama_produk
                ORDER BY revenue DESC
                LIMIT 1
            ");
            $stmt->execute([$startDate, $endDate]);
            $topProduct = $stmt->fetch();
            
            if ($topProduct) {
                $insights[] = [
                    'type' => 'top_product',
                    'message' => "Produk terlaris periode ini: {$topProduct['nama_produk']} dengan revenue Rp" . number_format($topProduct['revenue'], 0, ',', '.'),
                    'priority' => 'high'
                ];
            }
            
            // Check for declining sales
            $stmt = $this->db->prepare("
                SELECT 
                    SUM(CASE WHEN created_at BETWEEN ? AND ? THEN total_harga ELSE 0 END) as current_revenue,
                    SUM(CASE WHEN created_at BETWEEN DATE_SUB(?, INTERVAL 1 MONTH) AND DATE_SUB(?, INTERVAL 1 MONTH) THEN total_harga ELSE 0 END) as previous_revenue
                FROM penjualan
                WHERE status = 'selesai'
            ");
            $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
            $revenueComparison = $stmt->fetch();
            
            if ($revenueComparison['previous_revenue'] > 0) {
                $growth = (($revenueComparison['current_revenue'] - $revenueComparison['previous_revenue']) / $revenueComparison['previous_revenue']) * 100;
                
                if ($growth < -10) {
                    $insights[] = [
                        'type' => 'declining_sales',
                        'message' => "Penjualan menurun " . number_format(abs($growth), 1) . "% dibanding bulan lalu",
                        'priority' => 'high'
                    ];
                } elseif ($growth > 20) {
                    $insights[] = [
                        'type' => 'growing_sales',
                        'message' => "Penjualan tumbuh " . number_format($growth, 1) . "% dibanding bulan lalu",
                        'priority' => 'positive'
                    ];
                }
            }
            
            return $insights;
        } catch (Exception $e) {
            error_log("Error generating insights: " . $e->getMessage());
            return [];
        }
    }
}
