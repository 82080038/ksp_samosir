<?php
/**
 * Advanced Monitoring & Analytics System for KSP Samosir
 * Real-time monitoring, KPIs tracking, and optimization framework
 */

class MonitoringSystem {
    private $pdo;
    private $metrics = [];
    private $alerts = [];

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
        $this->initializeMetrics();
    }

    private function initializeMetrics() {
        // Phase 1 Metrics
        $this->metrics['phase1'] = [
            'digital_transaction_volume' => 0,
            'member_app_adoption' => 0,
            'digital_onboarding_time' => 0,
            'system_uptime' => 0
        ];

        // Phase 2 Metrics
        $this->metrics['phase2'] = [
            'operational_efficiency' => 0,
            'risk_reduction' => 0,
            'member_satisfaction' => 0,
            'cost_reduction' => 0
        ];

        // Phase 3 Metrics
        $this->metrics['phase3'] = [
            'ecosystem_growth' => 0,
            'member_engagement' => 0,
            'revenue_diversification' => 0,
            'community_participation' => 0
        ];

        // Phase 4 Metrics
        $this->metrics['phase4'] = [
            'ai_adoption' => 0,
            'innovation_index' => 0,
            'network_scale' => 0,
            'market_leadership' => 0
        ];

        // Real-time System Metrics
        $this->metrics['system'] = [
            'response_time' => 0,
            'cpu_usage' => 0,
            'memory_usage' => 0,
            'active_users' => 0,
            'error_rate' => 0
        ];
    }

    // Real-time metrics collection
    public function collectMetrics() {
        $this->collectSystemMetrics();
        $this->collectBusinessMetrics();
        $this->collectUserMetrics();
        $this->saveMetricsToDatabase();
        $this->checkAlerts();
    }

    private function collectSystemMetrics() {
        // System performance metrics
        $this->metrics['system']['response_time'] = $this->getAverageResponseTime();
        $this->metrics['system']['cpu_usage'] = $this->getCPUUsage();
        $this->metrics['system']['memory_usage'] = $this->getMemoryUsage();
        $this->metrics['system']['active_users'] = $this->getActiveUsers();
        $this->metrics['system']['error_rate'] = $this->getErrorRate();
    }

    private function collectBusinessMetrics() {
        // Phase-specific business metrics
        $this->metrics['phase1']['digital_transaction_volume'] = $this->getDigitalTransactionVolume();
        $this->metrics['phase1']['member_app_adoption'] = $this->getMemberAppAdoption();
        $this->metrics['phase1']['digital_onboarding_time'] = $this->getDigitalOnboardingTime();
        $this->metrics['phase1']['system_uptime'] = $this->getSystemUptime();

        $this->metrics['phase2']['operational_efficiency'] = $this->getOperationalEfficiency();
        $this->metrics['phase2']['risk_reduction'] = $this->getRiskReduction();
        $this->metrics['phase2']['member_satisfaction'] = $this->getMemberSatisfaction();
        $this->metrics['phase2']['cost_reduction'] = $this->getCostReduction();

        $this->metrics['phase3']['ecosystem_growth'] = $this->getEcosystemGrowth();
        $this->metrics['phase3']['member_engagement'] = $this->getMemberEngagement();
        $this->metrics['phase3']['revenue_diversification'] = $this->getRevenueDiversification();
        $this->metrics['phase3']['community_participation'] = $this->getCommunityParticipation();

        $this->metrics['phase4']['ai_adoption'] = $this->getAIAdoption();
        $this->metrics['phase4']['innovation_index'] = $this->getInnovationIndex();
        $this->metrics['phase4']['network_scale'] = $this->getNetworkScale();
        $this->metrics['phase4']['market_leadership'] = $this->getMarketLeadership();
    }

    private function collectUserMetrics() {
        // User behavior and engagement metrics
        $this->metrics['user'] = [
            'page_views' => $this->getPageViews(),
            'session_duration' => $this->getAverageSessionDuration(),
            'bounce_rate' => $this->getBounceRate(),
            'conversion_rate' => $this->getConversionRate(),
            'user_retention' => $this->getUserRetention()
        ];
    }

    // System metrics getters
    private function getAverageResponseTime() {
        // Calculate average response time from logs
        $stmt = $this->pdo->query("
            SELECT AVG(duration) as avg_response
            FROM system_metrics
            WHERE metric_type = 'response_time'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['avg_response'] ?? 0, 2);
    }

    private function getCPUUsage() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0] * 100, 2);
        }
        return 0;
    }

    private function getMemoryUsage() {
        $memory_usage = memory_get_peak_usage(true) / 1024 / 1024;
        return round($memory_usage, 2);
    }

    private function getActiveUsers() {
        $stmt = $this->pdo->query("
            SELECT COUNT(DISTINCT user_id) as active_users
            FROM user_sessions
            WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['active_users'] ?? 0;
    }

    private function getErrorRate() {
        $stmt = $this->pdo->query("
            SELECT
                (SELECT COUNT(*) FROM error_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)) /
                (SELECT COUNT(*) FROM access_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)) * 100 as error_rate
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['error_rate'] ?? 0, 2);
    }

    // Business metrics getters
    private function getDigitalTransactionVolume() {
        $stmt = $this->pdo->query("
            SELECT
                (SELECT COUNT(*) FROM transactions WHERE payment_method IN ('qris', 'ewallet', 'digital') AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) /
                (SELECT COUNT(*) FROM transactions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) * 100 as digital_percentage
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['digital_percentage'] ?? 0, 2);
    }

    private function getMemberAppAdoption() {
        $stmt = $this->pdo->query("
            SELECT
                (SELECT COUNT(DISTINCT user_id) FROM app_sessions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) /
                (SELECT COUNT(*) FROM anggota WHERE status = 'aktif') * 100 as app_adoption
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['app_adoption'] ?? 0, 2);
    }

    private function getDigitalOnboardingTime() {
        $stmt = $this->pdo->query("
            SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, activated_at)) as avg_onboarding_time
            FROM anggota
            WHERE activated_at IS NOT NULL
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['avg_onboarding_time'] ?? 0, 2);
    }

    private function getSystemUptime() {
        // Calculate uptime from monitoring data
        $stmt = $this->pdo->query("
            SELECT
                (1 - (SUM(downtime_minutes) / (24 * 60))) * 100 as uptime_percentage
            FROM system_uptime
            WHERE date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['uptime_percentage'] ?? 99.9, 2);
    }

    private function getOperationalEfficiency() {
        // Measure automation impact on manual processes
        $stmt = $this->pdo->query("
            SELECT
                (SELECT COUNT(*) FROM automated_processes WHERE status = 'completed') /
                (SELECT COUNT(*) FROM all_processes) * 100 as efficiency
            FROM process_metrics
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['efficiency'] ?? 0, 2);
    }

    private function getRiskReduction() {
        $stmt = $this->pdo->query("
            SELECT
                (1 - (SELECT COUNT(*) FROM delinquent_loans WHERE status = 'delinquent') /
                     (SELECT COUNT(*) FROM loans WHERE status = 'active')) * 100 as risk_reduction
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['risk_reduction'] ?? 0, 2);
    }

    private function getMemberSatisfaction() {
        $stmt = $this->pdo->query("
            SELECT AVG(rating) as avg_satisfaction
            FROM member_feedback
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['avg_satisfaction'] ?? 0, 2);
    }

    private function getCostReduction() {
        $stmt = $this->pdo->query("
            SELECT
                ((SELECT SUM(amount) FROM operational_costs WHERE category = 'old_process') -
                 (SELECT SUM(amount) FROM operational_costs WHERE category = 'new_process')) /
                (SELECT SUM(amount) FROM operational_costs WHERE category = 'old_process') * 100 as cost_reduction
            FROM cost_metrics
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['cost_reduction'] ?? 0, 2);
    }

    private function getEcosystemGrowth() {
        $stmt = $this->pdo->query("
            SELECT
                ((SELECT COUNT(*) FROM marketplace_transactions) /
                 (SELECT COUNT(*) FROM marketplace_transactions WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY))) - 1 * 100 as growth_rate
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['growth_rate'] ?? 0, 2);
    }

    private function getMemberEngagement() {
        $stmt = $this->pdo->query("
            SELECT
                (SELECT COUNT(DISTINCT user_id) FROM user_activities WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) /
                (SELECT COUNT(*) FROM anggota WHERE status = 'aktif') * 100 as engagement_rate
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['engagement_rate'] ?? 0, 2);
    }

    private function getRevenueDiversification() {
        $stmt = $this->pdo->query("
            SELECT
                (SELECT SUM(amount) FROM revenue WHERE source_category = 'digital_products') /
                (SELECT SUM(amount) FROM revenue) * 100 as diversification_percentage
            FROM revenue_metrics
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['diversification_percentage'] ?? 0, 2);
    }

    private function getCommunityParticipation() {
        $stmt = $this->pdo->query("
            SELECT COUNT(DISTINCT user_id) as active_participants
            FROM community_activities
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['active_participants'] ?? 0;
    }

    private function getAIAdoption() {
        $stmt = $this->pdo->query("
            SELECT
                (SELECT COUNT(*) FROM ai_decisions WHERE status = 'automated') /
                (SELECT COUNT(*) FROM all_decisions) * 100 as ai_adoption_rate
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['ai_adoption_rate'] ?? 0, 2);
    }

    private function getInnovationIndex() {
        // Composite score of innovation metrics
        $patents = 0; // Placeholder
        $publications = 0; // Placeholder
        $awards = 0; // Placeholder
        $partnerships = 0; // Placeholder

        $innovation_score = ($patents * 0.3) + ($publications * 0.25) + ($awards * 0.25) + ($partnerships * 0.2);
        return round($innovation_score, 2);
    }

    private function getNetworkScale() {
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as connected_cooperatives
            FROM cooperative_network
            WHERE status = 'active'
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['connected_cooperatives'] ?? 0;
    }

    private function getMarketLeadership() {
        // Market share calculation (placeholder - would need market data)
        return 0; // Placeholder
    }

    private function getPageViews() {
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as page_views
            FROM page_views
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['page_views'] ?? 0;
    }

    private function getAverageSessionDuration() {
        $stmt = $this->pdo->query("
            SELECT AVG(session_duration) as avg_duration
            FROM user_sessions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round(($result['avg_duration'] ?? 0) / 60, 2); // Convert to minutes
    }

    private function getBounceRate() {
        $stmt = $this->pdo->query("
            SELECT
                (SELECT COUNT(*) FROM sessions WHERE page_views = 1) /
                (SELECT COUNT(*) FROM sessions) * 100 as bounce_rate
            FROM session_metrics
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['bounce_rate'] ?? 0, 2);
    }

    private function getConversionRate() {
        $stmt = $this->pdo->query("
            SELECT
                (SELECT COUNT(*) FROM conversions) /
                (SELECT COUNT(*) FROM sessions) * 100 as conversion_rate
            FROM conversion_metrics
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['conversion_rate'] ?? 0, 2);
    }

    private function getUserRetention() {
        $stmt = $this->pdo->query("
            SELECT
                (SELECT COUNT(DISTINCT user_id) FROM user_sessions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) /
                (SELECT COUNT(DISTINCT user_id) FROM user_sessions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)) * 100 as retention_rate
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['retention_rate'] ?? 0, 2);
    }

    // Save metrics to database
    private function saveMetricsToDatabase() {
        $timestamp = date('Y-m-d H:i:s');

        foreach ($this->metrics as $category => $categoryMetrics) {
            foreach ($categoryMetrics as $metric => $value) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO monitoring_metrics (category, metric_name, metric_value, timestamp)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE metric_value = VALUES(metric_value), timestamp = VALUES(timestamp)
                ");
                $stmt->execute([$category, $metric, $value, $timestamp]);
            }
        }
    }

    // Alert system
    private function checkAlerts() {
        $alerts = [];

        // System alerts
        if ($this->metrics['system']['cpu_usage'] > 80) {
            $alerts[] = ['type' => 'system', 'level' => 'critical', 'message' => 'High CPU usage detected'];
        }

        if ($this->metrics['system']['memory_usage'] > 85) {
            $alerts[] = ['type' => 'system', 'level' => 'warning', 'message' => 'High memory usage detected'];
        }

        if ($this->metrics['system']['error_rate'] > 5) {
            $alerts[] = ['type' => 'system', 'level' => 'critical', 'message' => 'High error rate detected'];
        }

        // Business alerts
        if ($this->metrics['phase1']['system_uptime'] < 99) {
            $alerts[] = ['type' => 'business', 'level' => 'warning', 'message' => 'System uptime below target'];
        }

        if ($this->metrics['phase2']['risk_reduction'] < 70) {
            $alerts[] = ['type' => 'business', 'level' => 'warning', 'message' => 'Risk reduction below target'];
        }

        // Save alerts
        foreach ($alerts as $alert) {
            $stmt = $this->pdo->prepare("
                INSERT INTO system_alerts (alert_type, alert_level, message, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$alert['type'], $alert['level'], $alert['message']]);
        }

        $this->alerts = $alerts;
    }

    // Get metrics for dashboard
    public function getDashboardMetrics() {
        return [
            'system' => $this->metrics['system'],
            'business' => array_merge(
                $this->metrics['phase1'],
                $this->metrics['phase2'],
                $this->metrics['phase3'],
                $this->metrics['phase4']
            ),
            'user' => $this->metrics['user'],
            'alerts' => $this->alerts
        ];
    }

    // ROI calculation
    public function calculateROI($investment, $period = 'monthly') {
        $revenueIncrease = $this->calculateRevenueIncrease($period);
        $costReduction = $this->calculateCostReduction($period);

        $totalBenefits = $revenueIncrease + $costReduction;
        $roi = (($totalBenefits - $investment) / $investment) * 100;

        return [
            'investment' => $investment,
            'revenue_increase' => $revenueIncrease,
            'cost_reduction' => $costReduction,
            'total_benefits' => $totalBenefits,
            'roi_percentage' => round($roi, 2)
        ];
    }

    private function calculateRevenueIncrease($period) {
        // Calculate additional revenue from digital transformation
        $digitalRevenue = $this->getDigitalRevenue($period);
        $marketplaceRevenue = $this->getMarketplaceRevenue($period);

        return $digitalRevenue + $marketplaceRevenue;
    }

    private function calculateCostReduction($period) {
        // Calculate cost savings from automation
        $automationSavings = $this->getAutomationSavings($period);
        $riskReductionSavings = $this->getRiskReductionSavings($period);

        return $automationSavings + $riskReductionSavings;
    }

    private function getDigitalRevenue($period) {
        // Calculate revenue from digital channels
        return 0; // Placeholder - implement based on actual data
    }

    private function getMarketplaceRevenue($period) {
        // Calculate revenue from marketplace
        return 0; // Placeholder - implement based on actual data
    }

    private function getAutomationSavings($period) {
        // Calculate savings from automation
        return 0; // Placeholder - implement based on actual data
    }

    private function getRiskReductionSavings($period) {
        // Calculate savings from reduced risk
        return 0; // Placeholder - implement based on actual data
    }

    // Optimization recommendations
    public function getOptimizationRecommendations() {
        $recommendations = [];

        // System optimizations
        if ($this->metrics['system']['response_time'] > 1000) {
            $recommendations[] = [
                'category' => 'system',
                'priority' => 'high',
                'title' => 'Optimize Response Time',
                'description' => 'Implement caching and database optimization',
                'impact' => 'Reduce response time by 50%'
            ];
        }

        // Business optimizations
        if ($this->metrics['phase1']['member_app_adoption'] < 30) {
            $recommendations[] = [
                'category' => 'business',
                'priority' => 'high',
                'title' => 'Increase App Adoption',
                'description' => 'Enhance mobile app features and user experience',
                'impact' => 'Increase adoption by 25%'
            ];
        }

        if ($this->metrics['phase2']['operational_efficiency'] < 50) {
            $recommendations[] = [
                'category' => 'business',
                'priority' => 'medium',
                'title' => 'Improve Operational Efficiency',
                'description' => 'Implement additional RPA workflows',
                'impact' => 'Reduce manual processes by 30%'
            ];
        }

        return $recommendations;
    }
}

// Helper functions
function getMonitoringSystem() {
    static $monitoring = null;
    if ($monitoring === null) {
        $monitoring = new MonitoringSystem();
    }
    return $monitoring;
}

function collectSystemMetrics() {
    $monitoring = getMonitoringSystem();
    $monitoring->collectMetrics();
}

function getDashboardData() {
    $monitoring = getMonitoringSystem();
    return $monitoring->getDashboardMetrics();
}

function calculateCurrentROI($investment) {
    $monitoring = getMonitoringSystem();
    return $monitoring->calculateROI($investment);
}

function getOptimizationRecommendations() {
    $monitoring = getMonitoringSystem();
    return $monitoring->getOptimizationRecommendations();
}
?>
