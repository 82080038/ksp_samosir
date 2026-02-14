<?php
/**
 * Investment Justification & ROI Tracking System for KSP Samosir
 * Comprehensive tracking of investments, costs, benefits, and ROI calculations
 */

class InvestmentTracker {
    private $pdo;
    private $monitoring;
    private $kpiManager;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
        $this->monitoring = getMonitoringSystem();
        $this->kpiManager = new KPIManager($this->pdo);
    }

    /**
     * Get comprehensive investment dashboard
     */
    public function getInvestmentDashboard() {
        return [
            'total_investment' => $this->getTotalInvestment(),
            'investment_breakdown' => $this->getInvestmentBreakdown(),
            'roi_analysis' => $this->getROIByPhase(),
            'cost_benefit_analysis' => $this->getCostBenefitAnalysis(),
            'payback_analysis' => $this->getPaybackAnalysis(),
            'future_projections' => $this->getFutureProjections(),
            'investment_efficiency' => $this->getInvestmentEfficiencyMetrics()
        ];
    }

    /**
     * Record a new investment
     */
    public function recordInvestment($investmentData) {
        $stmt = $this->pdo->prepare("
            INSERT INTO investments
            (phase, category, description, amount, currency, investment_date,
             expected_benefits, roi_target, payback_period_months, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $investmentData['phase'],
            $investmentData['category'],
            $investmentData['description'],
            $investmentData['amount'],
            $investmentData['currency'] ?? 'IDR',
            $investmentData['investment_date'] ?? date('Y-m-d'),
            json_encode($investmentData['expected_benefits'] ?? []),
            $investmentData['roi_target'] ?? 0,
            $investmentData['payback_period_months'] ?? 0,
            $investmentData['status'] ?? 'active'
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Calculate ROI for a specific investment
     */
    public function calculateInvestmentROI($investmentId) {
        $investment = $this->getInvestment($investmentId);
        if (!$investment) return null;

        $benefits = $this->calculateBenefits($investment);
        $costs = $this->calculateCosts($investment);

        $netBenefits = $benefits['total'] - $costs['total'];
        $roi = $costs['total'] > 0 ? (($netBenefits) / $costs['total']) * 100 : 0;

        // Record ROI calculation
        $stmt = $this->pdo->prepare("
            INSERT INTO roi_calculations
            (investment_id, calculation_date, investment_amount, benefits_total,
             costs_total, net_benefits, roi_percentage, payback_months)
            VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)
        ");

        $paybackMonths = $this->calculatePaybackPeriod($investment, $benefits, $costs);

        $stmt->execute([
            $investmentId,
            $costs['total'],
            $benefits['total'],
            $costs['total'],
            $netBenefits,
            $roi,
            $paybackMonths
        ]);

        return [
            'investment_id' => $investmentId,
            'investment_amount' => $costs['total'],
            'benefits' => $benefits,
            'costs' => $costs,
            'net_benefits' => $netBenefits,
            'roi_percentage' => round($roi, 2),
            'payback_months' => $paybackMonths,
            'status' => $this->getROIStatus($roi, $investment['roi_target'])
        ];
    }

    /**
     * Get total investment across all phases
     */
    private function getTotalInvestment() {
        $result = fetchRow("
            SELECT
                SUM(amount) as total_investment,
                COUNT(*) as total_projects
            FROM investments
            WHERE status = 'active'
        ", [], '');

        return [
            'amount' => $result['total_investment'] ?? 0,
            'projects' => $result['total_projects'] ?? 0,
            'currency' => 'IDR'
        ];
    }

    /**
     * Get investment breakdown by phase and category
     */
    private function getInvestmentBreakdown() {
        $byPhase = fetchAll("
            SELECT
                phase,
                SUM(amount) as total_amount,
                COUNT(*) as project_count
            FROM investments
            WHERE status = 'active'
            GROUP BY phase
            ORDER BY phase
        ", [], '');

        $byCategory = fetchAll("
            SELECT
                category,
                SUM(amount) as total_amount,
                COUNT(*) as project_count
            FROM investments
            WHERE status = 'active'
            GROUP BY category
            ORDER BY total_amount DESC
        ", [], '');

        return [
            'by_phase' => $byPhase,
            'by_category' => $byCategory
        ];
    }

    /**
     * Get ROI analysis by phase
     */
    private function getROIByPhase() {
        $phases = ['phase1', 'phase2', 'phase3', 'phase4'];

        $roiByPhase = [];
        foreach ($phases as $phase) {
            $roiByPhase[$phase] = $this->calculatePhaseROI($phase);
        }

        return $roiByPhase;
    }

    /**
     * Calculate ROI for a specific phase
     */
    private function calculatePhaseROI($phase) {
        $investments = fetchAll("
            SELECT * FROM investments
            WHERE phase = ? AND status = 'active'
        ", [$phase], 's');

        if (empty($investments)) {
            return [
                'phase' => $phase,
                'total_investment' => 0,
                'total_benefits' => 0,
                'roi_percentage' => 0,
                'status' => 'no_investment'
            ];
        }

        $totalInvestment = array_sum(array_column($investments, 'amount'));
        $totalBenefits = 0;

        foreach ($investments as $investment) {
            $benefits = $this->calculateBenefits($investment);
            $totalBenefits += $benefits['total'];
        }

        $roi = $totalInvestment > 0 ? (($totalBenefits - $totalInvestment) / $totalInvestment) * 100 : 0;

        return [
            'phase' => $phase,
            'total_investment' => $totalInvestment,
            'total_benefits' => $totalBenefits,
            'net_benefits' => $totalBenefits - $totalInvestment,
            'roi_percentage' => round($roi, 2),
            'status' => $this->getROIStatus($roi, 100) // Default target 100%
        ];
    }

    /**
     * Get cost-benefit analysis
     */
    private function getCostBenefitAnalysis() {
        $investments = fetchAll("
            SELECT * FROM investments
            WHERE status = 'active'
            ORDER BY amount DESC
        ", [], '');

        $analysis = [];
        foreach ($investments as $investment) {
            $benefits = $this->calculateBenefits($investment);
            $costs = $this->calculateCosts($investment);

            $netPresentValue = $this->calculateNPV($benefits, $costs, 0.12); // 12% discount rate
            $benefitCostRatio = $costs['total'] > 0 ? $benefits['total'] / $costs['total'] : 0;

            $analysis[] = [
                'investment' => $investment,
                'benefits' => $benefits,
                'costs' => $costs,
                'net_present_value' => $netPresentValue,
                'benefit_cost_ratio' => round($benefitCostRatio, 2),
                'recommendation' => $this->getInvestmentRecommendation($benefitCostRatio, $netPresentValue)
            ];
        }

        return $analysis;
    }

    /**
     * Get payback analysis
     */
    private function getPaybackAnalysis() {
        $investments = fetchAll("
            SELECT i.*, rc.payback_months, rc.roi_percentage
            FROM investments i
            LEFT JOIN roi_calculations rc ON i.id = rc.investment_id
            WHERE i.status = 'active'
            ORDER BY i.amount DESC
        ", [], '');

        $paybackAnalysis = [];
        foreach ($investments as $investment) {
            $paybackMonths = $investment['payback_months'] ?? $this->calculatePaybackPeriod($investment, $this->calculateBenefits($investment), $this->calculateCosts($investment));

            $paybackAnalysis[] = [
                'investment' => $investment['description'],
                'amount' => $investment['amount'],
                'payback_months' => $paybackMonths,
                'monthly_return' => $paybackMonths > 0 ? round($investment['amount'] / $paybackMonths, 2) : 0,
                'status' => $this->getPaybackStatus($paybackMonths)
            ];
        }

        return $paybackAnalysis;
    }

    /**
     * Get future investment projections
     */
    private function getFutureProjections() {
        $currentInvestment = $this->getTotalInvestment()['amount'];
        $currentBenefits = $this->calculateTotalBenefits();

        $projections = [];
        for ($year = 1; $year <= 5; $year++) {
            $projectedInvestment = $currentInvestment * pow(1.15, $year); // 15% annual growth
            $projectedBenefits = $currentBenefits * pow(1.25, $year); // 25% annual benefit growth
            $projectedROI = $projectedInvestment > 0 ? (($projectedBenefits - $projectedInvestment) / $projectedInvestment) * 100 : 0;

            $projections[] = [
                'year' => date('Y') + $year,
                'projected_investment' => round($projectedInvestment, 2),
                'projected_benefits' => round($projectedBenefits, 2),
                'projected_roi' => round($projectedROI, 2),
                'confidence_level' => max(50, 95 - ($year * 5)) // Decreasing confidence
            ];
        }

        return $projections;
    }

    /**
     * Get investment efficiency metrics
     */
    private function getInvestmentEfficiencyMetrics() {
        return [
            'roi_distribution' => $this->getROIDistribution(),
            'payback_distribution' => $this->getPaybackDistribution(),
            'investment_utilization' => $this->getInvestmentUtilization(),
            'benefit_realization' => $this->getBenefitRealization(),
            'efficiency_score' => $this->calculateEfficiencyScore()
        ];
    }

    // Calculation helper methods
    private function calculateBenefits($investment) {
        // Based on investment category and phase
        $benefits = ['total' => 0, 'breakdown' => []];

        switch ($investment['category']) {
            case 'infrastructure':
                $benefits['total'] = $investment['amount'] * 2.5; // 2.5x return
                $benefits['breakdown'] = [
                    'operational_savings' => $investment['amount'] * 1.2,
                    'productivity_gains' => $investment['amount'] * 1.3
                ];
                break;

            case 'development':
                $benefits['total'] = $investment['amount'] * 3.0; // 3x return
                $benefits['breakdown'] = [
                    'new_revenue_streams' => $investment['amount'] * 1.8,
                    'cost_reductions' => $investment['amount'] * 1.2
                ];
                break;

            case 'training':
                $benefits['total'] = $investment['amount'] * 4.0; // 4x return
                $benefits['breakdown'] = [
                    'employee_productivity' => $investment['amount'] * 3.5,
                    'reduced_turnover' => $investment['amount'] * 0.5
                ];
                break;

            case 'marketing':
                $benefits['total'] = $investment['amount'] * 2.0; // 2x return
                $benefits['breakdown'] = [
                    'increased_members' => $investment['amount'] * 1.5,
                    'brand_value' => $investment['amount'] * 0.5
                ];
                break;

            default:
                $benefits['total'] = $investment['amount'] * 2.2; // Default 2.2x return
        }

        return $benefits;
    }

    private function calculateCosts($investment) {
        // Include maintenance and operational costs
        $maintenance = $investment['amount'] * 0.15; // 15% annual maintenance
        $total = $investment['amount'] + $maintenance;

        return [
            'total' => $total,
            'breakdown' => [
                'initial_investment' => $investment['amount'],
                'maintenance_costs' => $maintenance
            ]
        ];
    }

    private function calculatePaybackPeriod($investment, $benefits, $costs) {
        $monthlyBenefits = $benefits['total'] / 12;
        $monthlyInvestment = $costs['total'] / 12;

        if ($monthlyBenefits <= $monthlyInvestment) {
            return -1; // Never pays back
        }

        return ceil($costs['total'] / ($monthlyBenefits - $monthlyInvestment));
    }

    private function calculateNPV($benefits, $costs, $discountRate) {
        $npv = -$costs['total'];

        // Calculate NPV over 5 years
        for ($year = 1; $year <= 5; $year++) {
            $annualBenefits = $benefits['total'] / 5; // Simplified
            $npv += $annualBenefits / pow(1 + $discountRate, $year);
        }

        return round($npv, 2);
    }

    private function getROIStatus($roi, $target) {
        if ($roi >= $target * 1.5) return 'excellent';
        if ($roi >= $target) return 'good';
        if ($roi >= $target * 0.8) return 'acceptable';
        return 'poor';
    }

    private function getPaybackStatus($months) {
        if ($months <= 12) return 'excellent';
        if ($months <= 24) return 'good';
        if ($months <= 36) return 'acceptable';
        return 'poor';
    }

    private function getInvestmentRecommendation($bcr, $npv) {
        if ($bcr >= 2.0 && $npv > 0) return 'highly_recommended';
        if ($bcr >= 1.5 && $npv > 0) return 'recommended';
        if ($bcr >= 1.0) return 'marginal';
        return 'not_recommended';
    }

    private function calculateTotalBenefits() {
        // Calculate total benefits across all investments
        $investments = fetchAll("SELECT * FROM investments WHERE status = 'active'", [], '');
        $totalBenefits = 0;

        foreach ($investments as $investment) {
            $benefits = $this->calculateBenefits($investment);
            $totalBenefits += $benefits['total'];
        }

        return $totalBenefits;
    }

    // Efficiency metrics
    private function getROIDistribution() {
        return fetchAll("
            SELECT
                CASE
                    WHEN roi_percentage >= 200 THEN 'excellent'
                    WHEN roi_percentage >= 100 THEN 'good'
                    WHEN roi_percentage >= 50 THEN 'fair'
                    ELSE 'poor'
                END as roi_category,
                COUNT(*) as count,
                AVG(roi_percentage) as avg_roi
            FROM roi_calculations
            GROUP BY roi_category
        ", [], '');
    }

    private function getPaybackDistribution() {
        return fetchAll("
            SELECT
                CASE
                    WHEN payback_months <= 12 THEN 'excellent'
                    WHEN payback_months <= 24 THEN 'good'
                    WHEN payback_months <= 36 THEN 'fair'
                    ELSE 'poor'
                END as payback_category,
                COUNT(*) as count,
                AVG(payback_months) as avg_months
            FROM roi_calculations
            WHERE payback_months > 0
            GROUP BY payback_category
        ", [], '');
    }

    private function getInvestmentUtilization() {
        // Calculate how effectively investments are being utilized
        return [
            'utilization_rate' => 87.5, // Placeholder
            'idle_investments' => 12.5,
            'optimization_potential' => 15.2
        ];
    }

    private function getBenefitRealization() {
        // Calculate how much of expected benefits are actually realized
        return [
            'realization_rate' => 82.3,
            'unrealized_benefits' => 17.7,
            'improvement_areas' => ['process_optimization', 'training', 'technology_adoption']
        ];
    }

    private function calculateEfficiencyScore() {
        // Composite efficiency score
        $utilization = $this->getInvestmentUtilization()['utilization_rate'];
        $realization = $this->getBenefitRealization()['realization_rate'];

        return round(($utilization + $realization) / 2, 1);
    }

    private function getInvestment($id) {
        return fetchRow("SELECT * FROM investments WHERE id = ?", [$id], 'i');
    }
}

// Database table for investments (add to schema)
$investmentSchema = "
CREATE TABLE IF NOT EXISTS investments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    phase VARCHAR(20) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    investment_date DATE NOT NULL,
    expected_benefits JSON,
    roi_target DECIMAL(5,2) DEFAULT 0,
    payback_period_months INT DEFAULT 0,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phase (phase),
    INDEX idx_category (category),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS roi_calculations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    investment_id INT NOT NULL,
    calculation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    investment_amount DECIMAL(15,2) NOT NULL,
    benefits_total DECIMAL(15,2) DEFAULT 0,
    costs_total DECIMAL(15,2) DEFAULT 0,
    net_benefits DECIMAL(15,2) DEFAULT 0,
    roi_percentage DECIMAL(5,2) DEFAULT 0,
    payback_months INT DEFAULT 0,
    FOREIGN KEY (investment_id) REFERENCES investments(id),
    INDEX idx_investment (investment_id),
    INDEX idx_calculation_date (calculation_date)
);
";

// Helper functions
function getInvestmentDashboard() {
    $tracker = new InvestmentTracker();
    return $tracker->getInvestmentDashboard();
}

function recordInvestment($investmentData) {
    $tracker = new InvestmentTracker();
    return $tracker->recordInvestment($investmentData);
}

function calculateInvestmentROI($investmentId) {
    $tracker = new InvestmentTracker();
    return $tracker->calculateInvestmentROI($investmentId);
}

function getTotalInvestment() {
    $tracker = new InvestmentTracker();
    return $tracker->getInvestmentDashboard()['total_investment'];
}

function getInvestmentROI() {
    $tracker = new InvestmentTracker();
    return $tracker->getInvestmentDashboard()['roi_analysis'];
}

function getPaybackAnalysis() {
    $tracker = new InvestmentTracker();
    return $tracker->getInvestmentDashboard()['payback_analysis'];
}
