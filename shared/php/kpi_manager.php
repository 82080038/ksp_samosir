<?php
/**
 * Success Metrics & KPIs Monitoring System for KSP Samosir
 * Comprehensive tracking of all success metrics from strategic roadmap
 */

class KPIManager {
    private $pdo;
    private $monitoring;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
        $this->monitoring = getMonitoringSystem();
    }

    /**
     * Get comprehensive KPIs dashboard
     */
    public function getKPIDashboard() {
        return [
            'overall_score' => $this->calculateOverallScore(),
            'phase_progress' => $this->getPhaseProgress(),
            'key_metrics' => $this->getKeyMetrics(),
            'trends' => $this->getKPITrends(),
            'benchmarks' => $this->getBenchmarksComparison(),
            'alerts' => $this->getKPIAlerts()
        ];
    }

    /**
     * Calculate overall organizational score
     */
    private function calculateOverallScore() {
        $scores = [
            'phase1_completion' => $this->getPhase1Score(),
            'phase2_completion' => $this->getPhase2Score(),
            'phase3_completion' => $this->getPhase3Score(),
            'phase4_completion' => $this->getPhase4Score(),
            'operational_excellence' => $this->getOperationalExcellenceScore(),
            'member_satisfaction' => $this->getMemberSatisfactionScore(),
            'financial_performance' => $this->getFinancialPerformanceScore(),
            'innovation_index' => $this->getInnovationIndexScore()
        ];

        $weightedScore = (
            $scores['phase1_completion'] * 0.15 +
            $scores['phase2_completion'] * 0.20 +
            $scores['phase3_completion'] * 0.20 +
            $scores['phase4_completion'] * 0.15 +
            $scores['operational_excellence'] * 0.10 +
            $scores['member_satisfaction'] * 0.10 +
            $scores['financial_performance'] * 0.05 +
            $scores['innovation_index'] * 0.05
        );

        return [
            'overall_score' => round($weightedScore, 1),
            'grade' => $this->scoreToGrade($weightedScore),
            'component_scores' => $scores,
            'trend' => $this->getScoreTrend()
        ];
    }

    /**
     * Get phase-wise progress and metrics
     */
    private function getPhaseProgress() {
        return [
            'phase1' => [
                'name' => 'Foundation Strengthening',
                'completion_percentage' => $this->getPhase1Completion(),
                'key_metrics' => [
                    'digital_transactions' => $this->getMetricValue('digital_transaction_volume'),
                    'member_app_adoption' => $this->getMetricValue('member_app_adoption'),
                    'system_uptime' => $this->getMetricValue('system_uptime')
                ],
                'target_date' => 'Q2 2026',
                'status' => $this->getPhase1Completion() >= 100 ? 'completed' : 'in_progress'
            ],
            'phase2' => [
                'name' => 'Operational Excellence',
                'completion_percentage' => $this->getPhase2Completion(),
                'key_metrics' => [
                    'operational_efficiency' => $this->getMetricValue('operational_efficiency'),
                    'risk_reduction' => $this->getMetricValue('risk_reduction'),
                    'member_satisfaction' => $this->getMetricValue('member_satisfaction')
                ],
                'target_date' => 'Q4 2026',
                'status' => $this->getPhase2Completion() >= 100 ? 'completed' : 'in_progress'
            ],
            'phase3' => [
                'name' => 'Ecosystem Expansion',
                'completion_percentage' => $this->getPhase3Completion(),
                'key_metrics' => [
                    'ecosystem_growth' => $this->getMetricValue('ecosystem_growth'),
                    'member_engagement' => $this->getMetricValue('member_engagement'),
                    'revenue_diversification' => $this->getMetricValue('revenue_diversification')
                ],
                'target_date' => 'Q2 2027',
                'status' => $this->getPhase3Completion() >= 100 ? 'completed' : 'in_progress'
            ],
            'phase4' => [
                'name' => 'Innovation & Scale',
                'completion_percentage' => $this->getPhase4Completion(),
                'key_metrics' => [
                    'ai_adoption' => $this->getMetricValue('ai_adoption'),
                    'network_scale' => $this->getMetricValue('network_scale'),
                    'innovation_index' => $this->getMetricValue('innovation_index')
                ],
                'target_date' => 'Q2 2028',
                'status' => $this->getPhase4Completion() >= 100 ? 'completed' : 'planned'
            ]
        ];
    }

    /**
     * Get key performance indicators
     */
    private function getKeyMetrics() {
        return [
            'member_metrics' => [
                'total_active_members' => $this->getTotalActiveMembers(),
                'member_growth_rate' => $this->getMemberGrowthRate(),
                'member_retention_rate' => $this->getMemberRetentionRate(),
                'member_satisfaction_score' => $this->getMemberSatisfactionScore()
            ],
            'financial_metrics' => [
                'total_assets' => $this->getTotalAssets(),
                'monthly_revenue' => $this->getMonthlyRevenue(),
                'profit_margin' => $this->getProfitMargin(),
                'return_on_assets' => $this->getReturnOnAssets()
            ],
            'operational_metrics' => [
                'system_uptime' => $this->getSystemUptime(),
                'average_response_time' => $this->getAverageResponseTime(),
                'transaction_success_rate' => $this->getTransactionSuccessRate(),
                'automation_coverage' => $this->getAutomationCoverage()
            ],
            'innovation_metrics' => [
                'digital_transformation_index' => $this->getDigitalTransformationIndex(),
                'ai_adoption_rate' => $this->getAIAdoptionRate(),
                'innovation_projects_count' => $this->getInnovationProjectsCount(),
                'patent_filings' => $this->getPatentFilings()
            ]
        ];
    }

    /**
     * Get KPI trends over time
     */
    private function getKPITrends() {
        $periods = ['1_month', '3_month', '6_month', '12_month'];

        $trends = [];
        foreach ($periods as $period) {
            $trends[$period] = [
                'member_growth' => $this->getMetricTrend('member_growth_rate', $period),
                'revenue_growth' => $this->getMetricTrend('revenue_growth', $period),
                'operational_efficiency' => $this->getMetricTrend('operational_efficiency', $period),
                'digital_adoption' => $this->getMetricTrend('digital_transaction_volume', $period),
                'member_satisfaction' => $this->getMetricTrend('member_satisfaction', $period)
            ];
        }

        return $trends;
    }

    /**
     * Compare against industry benchmarks
     */
    private function getBenchmarksComparison() {
        $industryBenchmarks = $this->getIndustryBenchmarks();

        return [
            'digital_transformation' => [
                'ksp_score' => $this->getDigitalTransformationIndex(),
                'industry_average' => $industryBenchmarks['digital_transformation'],
                'performance' => $this->calculatePerformanceVsBenchmark(
                    $this->getDigitalTransformationIndex(),
                    $industryBenchmarks['digital_transformation']
                )
            ],
            'member_satisfaction' => [
                'ksp_score' => $this->getMemberSatisfactionScore(),
                'industry_average' => $industryBenchmarks['member_satisfaction'],
                'performance' => $this->calculatePerformanceVsBenchmark(
                    $this->getMemberSatisfactionScore(),
                    $industryBenchmarks['member_satisfaction']
                )
            ],
            'operational_efficiency' => [
                'ksp_score' => $this->getOperationalExcellenceScore(),
                'industry_average' => $industryBenchmarks['operational_efficiency'],
                'performance' => $this->calculatePerformanceVsBenchmark(
                    $this->getOperationalExcellenceScore(),
                    $industryBenchmarks['operational_efficiency']
                )
            ],
            'financial_performance' => [
                'ksp_score' => $this->getFinancialPerformanceScore(),
                'industry_average' => $industryBenchmarks['financial_performance'],
                'performance' => $this->calculatePerformanceVsBenchmark(
                    $this->getFinancialPerformanceScore(),
                    $industryBenchmarks['financial_performance']
                )
            ]
        ];
    }

    /**
     * Get KPI alerts and warnings
     */
    private function getKPIAlerts() {
        $alerts = [];

        // Check critical KPIs
        if ($this->getSystemUptime() < 99.5) {
            $alerts[] = [
                'type' => 'critical',
                'category' => 'system',
                'message' => 'System uptime below critical threshold',
                'current_value' => $this->getSystemUptime(),
                'target_value' => 99.9,
                'impact' => 'high'
            ];
        }

        if ($this->getMemberSatisfactionScore() < 4.0) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'member',
                'message' => 'Member satisfaction below target',
                'current_value' => $this->getMemberSatisfactionScore(),
                'target_value' => 4.5,
                'impact' => 'medium'
            ];
        }

        if ($this->getDigitalTransactionVolume() < 60) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'digital',
                'message' => 'Digital transaction volume below target',
                'current_value' => $this->getDigitalTransactionVolume(),
                'target_value' => 70,
                'impact' => 'medium'
            ];
        }

        return $alerts;
    }

    // Phase completion calculation methods
    private function getPhase1Completion() {
        $metrics = [
            'digital_transaction_volume' => 70,
            'member_app_adoption' => 50,
            'system_uptime' => 99.9
        ];

        $weights = [0.4, 0.3, 0.3];
        return $this->calculateWeightedCompletion($metrics, $weights);
    }

    private function getPhase2Completion() {
        $metrics = [
            'operational_efficiency' => 60,
            'risk_reduction' => 80,
            'member_satisfaction' => 4.5
        ];

        $weights = [0.4, 0.3, 0.3];
        return $this->calculateWeightedCompletion($metrics, $weights);
    }

    private function getPhase3Completion() {
        $metrics = [
            'ecosystem_growth' => 200,
            'member_engagement' => 80,
            'revenue_diversification' => 30
        ];

        $weights = [0.4, 0.3, 0.3];
        return $this->calculateWeightedCompletion($metrics, $weights);
    }

    private function getPhase4Completion() {
        $metrics = [
            'ai_adoption' => 90,
            'network_scale' => 100,
            'innovation_index' => 75
        ];

        $weights = [0.4, 0.3, 0.3];
        return $this->calculateWeightedCompletion($metrics, $weights);
    }

    // Score calculation methods
    private function getPhase1Score() { return $this->getPhase1Completion(); }
    private function getPhase2Score() { return $this->getPhase2Completion(); }
    private function getPhase3Score() { return $this->getPhase3Completion(); }
    private function getPhase4Score() { return $this->getPhase4Completion(); }

    private function getOperationalExcellenceScore() {
        return ($this->getMetricValue('operational_efficiency') +
                $this->getMetricValue('system_uptime') / 10 +
                $this->getAutomationCoverage()) / 3;
    }

    private function getMemberSatisfactionScore() {
        return $this->getMetricValue('member_satisfaction');
    }

    private function getFinancialPerformanceScore() {
        return min(100, ($this->getProfitMargin() * 10 + $this->getReturnOnAssets()));
    }

    private function getInnovationIndexScore() {
        return $this->getMetricValue('innovation_index');
    }

    private function getScoreTrend() {
        // Calculate trend over last 3 months
        return 'improving'; // Placeholder
    }

    // Utility methods
    private function scoreToGrade($score) {
        if ($score >= 90) return 'A+';
        if ($score >= 85) return 'A';
        if ($score >= 80) return 'A-';
        if ($score >= 75) return 'B+';
        if ($score >= 70) return 'B';
        if ($score >= 65) return 'B-';
        if ($score >= 60) return 'C+';
        if ($score >= 55) return 'C';
        return 'C-';
    }

    private function calculateWeightedCompletion($metrics, $weights) {
        $completion = 0;
        $i = 0;

        foreach ($metrics as $metric => $target) {
            $current = $this->getMetricValue($metric);
            $percentage = min(100, ($current / $target) * 100);
            $completion += $percentage * $weights[$i];
            $i++;
        }

        return round($completion, 1);
    }

    private function getMetricValue($metricName) {
        // Get from monitoring system
        $dashboardData = $this->monitoring->getDashboardMetrics();

        // Map to appropriate metric
        $mapping = [
            'digital_transaction_volume' => $dashboardData['business']['digital_transaction_volume'] ?? 0,
            'member_app_adoption' => $dashboardData['business']['member_app_adoption'] ?? 0,
            'system_uptime' => $dashboardData['business']['system_uptime'] ?? 99.9,
            'operational_efficiency' => $dashboardData['business']['operational_efficiency'] ?? 0,
            'risk_reduction' => $dashboardData['business']['risk_reduction'] ?? 0,
            'member_satisfaction' => $dashboardData['business']['member_satisfaction'] ?? 0,
            'ecosystem_growth' => $dashboardData['business']['ecosystem_growth'] ?? 0,
            'member_engagement' => $dashboardData['business']['member_engagement'] ?? 0,
            'revenue_diversification' => $dashboardData['business']['revenue_diversification'] ?? 0,
            'ai_adoption' => $dashboardData['business']['ai_adoption'] ?? 0,
            'network_scale' => $dashboardData['business']['network_scale'] ?? 0,
            'innovation_index' => $dashboardData['business']['innovation_index'] ?? 0
        ];

        return $mapping[$metricName] ?? 0;
    }

    private function getMetricTrend($metricName, $period) {
        // Placeholder for trend calculation
        return rand(-10, 20); // Random trend for demo
    }

    private function getIndustryBenchmarks() {
        return [
            'digital_transformation' => 65,
            'member_satisfaction' => 4.2,
            'operational_efficiency' => 75,
            'financial_performance' => 85
        ];
    }

    private function calculatePerformanceVsBenchmark($current, $benchmark) {
        if ($current >= $benchmark * 1.2) return 'excellent';
        if ($current >= $benchmark) return 'good';
        if ($current >= $benchmark * 0.8) return 'average';
        return 'below_average';
    }

    // Data retrieval methods
    private function getTotalActiveMembers() {
        return fetchRow("SELECT COUNT(*) as count FROM anggota WHERE status = 'aktif'")['count'];
    }

    private function getMemberGrowthRate() {
        // Calculate growth over last 12 months
        return 15.5; // Placeholder
    }

    private function getMemberRetentionRate() {
        return 87.3; // Placeholder
    }

    private function getTotalAssets() {
        return 500000000; // Placeholder
    }

    private function getMonthlyRevenue() {
        return 45000000; // Placeholder
    }

    private function getProfitMargin() {
        return 18.5; // Placeholder
    }

    private function getReturnOnAssets() {
        return 9.2; // Placeholder
    }

    private function getSystemUptime() {
        return $this->getMetricValue('system_uptime');
    }

    private function getAverageResponseTime() {
        return 245; // milliseconds
    }

    private function getTransactionSuccessRate() {
        return 98.7; // Placeholder
    }

    private function getAutomationCoverage() {
        return 45.2; // Placeholder
    }

    private function getDigitalTransformationIndex() {
        return 72.4; // Placeholder
    }

    private function getAIAdoptionRate() {
        return $this->getMetricValue('ai_adoption');
    }

    private function getInnovationProjectsCount() {
        return 8; // Placeholder
    }

    private function getPatentFilings() {
        return 2; // Placeholder
    }
}

// Helper functions
function getKPIDashboard() {
    $kpiManager = new KPIManager();
    return $kpiManager->getKPIDashboard();
}

function getOverallScore() {
    $kpiManager = new KPIManager();
    $dashboard = $kpiManager->getKPIDashboard();
    return $dashboard['overall_score'];
}

function getPhaseProgress() {
    $kpiManager = new KPIManager();
    $dashboard = $kpiManager->getKPIDashboard();
    return $dashboard['phase_progress'];
}

function getKeyMetrics() {
    $kpiManager = new KPIManager();
    $dashboard = $kpiManager->getKPIDashboard();
    return $dashboard['key_metrics'];
}

function getKPIAlerts() {
    $kpiManager = new KPIManager();
    $dashboard = $kpiManager->getKPIDashboard();
    return $dashboard['alerts'];
}
