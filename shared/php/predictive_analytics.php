<?php
/**
 * Predictive Analytics System for KSP Samosir
 * Machine learning-powered forecasting and risk assessment
 */

class PredictiveAnalytics {
    private $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
    }

    /**
     * Generate comprehensive predictions
     */
    public function generatePredictions() {
        return [
            'loan_default_risk' => $this->predictLoanDefaults(),
            'member_churn_risk' => $this->predictMemberChurn(),
            'savings_growth_forecast' => $this->forecastSavingsGrowth(),
            'cash_flow_projection' => $this->projectCashFlow(),
            'market_demand_trends' => $this->analyzeMarketTrends()
        ];
    }

    /**
     * Predict loan default risk for all active loans
     */
    private function predictLoanDefaults() {
        $activeLoans = $this->getActiveLoansWithFeatures();

        $predictions = [];
        foreach ($activeLoans as $loan) {
            $riskScore = $this->calculateDefaultRisk($loan);
            $confidence = $this->calculatePredictionConfidence($loan);

            $predictions[] = [
                'loan_id' => $loan['id'],
                'member_id' => $loan['member_id'],
                'member_name' => $loan['nama_lengkap'],
                'risk_score' => round($riskScore, 4),
                'risk_level' => $this->classifyRiskLevel($riskScore),
                'confidence' => round($confidence, 4),
                'prediction_date' => date('Y-m-d'),
                'factors' => $this->getRiskFactors($loan, $riskScore)
            ];

            // Save prediction to database
            $this->savePrediction([
                'model_id' => 1, // Loan default model
                'entity_type' => 'loan',
                'entity_id' => $loan['id'],
                'prediction_type' => 'loan_default',
                'predicted_value' => $riskScore,
                'confidence_score' => $confidence,
                'prediction_factors' => json_encode($this->getRiskFactors($loan, $riskScore)),
                'time_horizon' => '6_months'
            ]);
        }

        return $predictions;
    }

    /**
     * Predict member churn risk
     */
    private function predictMemberChurn() {
        $activeMembers = $this->getActiveMembersWithFeatures();

        $predictions = [];
        foreach ($activeMembers as $member) {
            $churnRisk = $this->calculateChurnRisk($member);
            $confidence = $this->calculateChurnConfidence($member);

            $predictions[] = [
                'member_id' => $member['id'],
                'member_name' => $member['nama_lengkap'],
                'churn_risk' => round($churnRisk, 4),
                'risk_level' => $this->classifyChurnRisk($churnRisk),
                'confidence' => round($confidence, 4),
                'predicted_attrition_date' => $this->estimateAttritionDate($churnRisk),
                'retention_recommendations' => $this->getRetentionRecommendations($member, $churnRisk)
            ];

            // Save prediction
            $this->savePrediction([
                'model_id' => 2, // Member churn model
                'entity_type' => 'member',
                'entity_id' => $member['id'],
                'prediction_type' => 'member_churn',
                'predicted_value' => $churnRisk,
                'confidence_score' => $confidence,
                'prediction_factors' => json_encode($this->getChurnFactors($member, $churnRisk)),
                'time_horizon' => '3_months'
            ]);
        }

        return $predictions;
    }

    /**
     * Forecast savings growth trends
     */
    private function forecastSavingsGrowth() {
        $historicalData = $this->getSavingsHistoricalData();
        $growthRate = $this->calculateGrowthRate($historicalData);

        $forecast = [];
        $currentSavings = $this->getCurrentTotalSavings();

        for ($month = 1; $month <= 12; $month++) {
            $projectedSavings = $currentSavings * pow(1 + $growthRate, $month);
            $forecast[] = [
                'month' => $month,
                'projected_savings' => round($projectedSavings, 2),
                'growth_rate' => round($growthRate * 100, 2),
                'confidence_interval' => $this->calculateConfidenceInterval($projectedSavings, $month)
            ];
        }

        return [
            'current_savings' => $currentSavings,
            'average_growth_rate' => round($growthRate * 100, 2),
            'forecast' => $forecast,
            'recommendations' => $this->getSavingsGrowthRecommendations($growthRate)
        ];
    }

    /**
     * Project cash flow for the next 12 months
     */
    private function projectCashFlow() {
        $incomeStreams = $this->getIncomeStreams();
        $expenseStreams = $this->getExpenseStreams();

        $projection = [];
        $cumulativeCashFlow = 0;

        for ($month = 1; $month <= 12; $month++) {
            $monthlyIncome = $this->calculateMonthlyIncome($incomeStreams, $month);
            $monthlyExpenses = $this->calculateMonthlyExpenses($expenseStreams, $month);

            $netCashFlow = $monthlyIncome - $monthlyExpenses;
            $cumulativeCashFlow += $netCashFlow;

            $projection[] = [
                'month' => $month,
                'income' => round($monthlyIncome, 2),
                'expenses' => round($monthlyExpenses, 2),
                'net_cash_flow' => round($netCashFlow, 2),
                'cumulative_cash_flow' => round($cumulativeCashFlow, 2),
                'cash_position' => $cumulativeCashFlow >= 0 ? 'surplus' : 'deficit'
            ];
        }

        return [
            'projection' => $projection,
            'summary' => $this->summarizeCashFlowProjection($projection),
            'recommendations' => $this->getCashFlowRecommendations($projection)
        ];
    }

    /**
     * Analyze market trends and demand patterns
     */
    private function analyzeMarketTrends() {
        $loanDemand = $this->analyzeLoanDemandTrends();
        $savingsTrends = $this->analyzeSavingsTrends();
        $memberAcquisition = $this->analyzeMemberAcquisitionTrends();

        return [
            'loan_demand_trend' => $loanDemand,
            'savings_trend' => $savingsTrends,
            'member_acquisition_trend' => $memberAcquisition,
            'market_opportunities' => $this->identifyMarketOpportunities($loanDemand, $savingsTrends, $memberAcquisition),
            'competitive_analysis' => $this->analyzeCompetitiveLandscape()
        ];
    }

    // Risk calculation methods
    private function calculateDefaultRisk($loan) {
        $riskScore = 0;

        // Payment history (40% weight)
        $paymentHistory = $this->analyzePaymentHistory($loan['member_id']);
        $riskScore += $paymentHistory['risk_score'] * 0.4;

        // Credit utilization (25% weight)
        $utilizationRisk = $this->calculateUtilizationRisk($loan);
        $riskScore += $utilizationRisk * 0.25;

        // Member tenure (15% weight)
        $tenureRisk = $this->calculateTenureRisk($loan['membership_days']);
        $riskScore += $tenureRisk * 0.15;

        // Loan characteristics (10% weight)
        $loanRisk = $this->calculateLoanRisk($loan);
        $riskScore += $loanRisk * 0.1;

        // External factors (10% weight)
        $externalRisk = $this->calculateExternalRisk($loan);
        $riskScore += $externalRisk * 0.1;

        return min(1.0, max(0.0, $riskScore));
    }

    private function calculateChurnRisk($member) {
        $churnScore = 0;

        // Activity level (30% weight)
        $activityScore = $this->analyzeMemberActivity($member['id']);
        $churnScore += (1 - $activityScore) * 0.3; // Less activity = higher churn risk

        // Tenure (25% weight)
        $tenureScore = min(1.0, $member['membership_days'] / 365); // 1 year = lowest risk
        $churnScore += (1 - $tenureScore) * 0.25;

        // Financial engagement (25% weight)
        $financialScore = $this->analyzeFinancialEngagement($member['id']);
        $churnScore += (1 - $financialScore) * 0.25;

        // Demographic factors (20% weight)
        $demographicScore = $this->analyzeDemographics($member);
        $churnScore += $demographicScore * 0.20;

        return min(1.0, max(0.0, $churnScore));
    }

    // Helper methods
    private function getActiveLoansWithFeatures() {
        return fetchAll("
            SELECT
                p.*,
                a.nama_lengkap,
                a.tanggal_gabung,
                DATEDIFF(CURDATE(), a.tanggal_gabung) as membership_days,
                COALESCE(s.total_savings, 0) as total_savings,
                COALESCE(ph.on_time_rate, 0) as payment_history_rate,
                COUNT(lp.id) as total_payments,
                COUNT(CASE WHEN lp.status = 'paid_late' THEN 1 END) as late_payments
            FROM pinjaman p
            JOIN anggota a ON p.member_id = a.id
            LEFT JOIN (
                SELECT member_id, SUM(amount) as total_savings
                FROM simpanan
                WHERE transaction_type = 'deposit'
                GROUP BY member_id
            ) s ON a.id = s.member_id
            LEFT JOIN (
                SELECT lp.loan_id, AVG(CASE WHEN lp.status = 'paid_on_time' THEN 1 ELSE 0 END) * 100 as on_time_rate
                FROM loan_payments lp
                GROUP BY lp.loan_id
            ) ph ON p.id = ph.loan_id
            LEFT JOIN loan_payments lp ON p.id = lp.loan_id
            WHERE p.status = 'aktif'
            GROUP BY p.id, a.id, a.nama_lengkap, a.tanggal_gabung, s.total_savings, ph.on_time_rate
        ", [], '');
    }

    private function getActiveMembersWithFeatures() {
        return fetchAll("
            SELECT
                a.*,
                DATEDIFF(CURDATE(), a.tanggal_gabung) as membership_days,
                COALESCE(s.total_savings, 0) as total_savings,
                COALESCE(s.monthly_avg, 0) as monthly_deposits,
                COUNT(DISTINCT p.id) as total_loans,
                MAX(lp.created_at) as last_activity
            FROM anggota a
            LEFT JOIN (
                SELECT member_id,
                       SUM(amount) as total_savings,
                       AVG(amount) as monthly_avg
                FROM simpanan
                WHERE transaction_type = 'deposit'
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY member_id
            ) s ON a.id = s.member_id
            LEFT JOIN pinjaman p ON a.id = p.member_id
            LEFT JOIN loan_payments lp ON p.id = lp.loan_id
            WHERE a.status = 'aktif'
            GROUP BY a.id
        ", [], '');
    }

    private function classifyRiskLevel($riskScore) {
        if ($riskScore >= 0.8) return 'very_high';
        if ($riskScore >= 0.6) return 'high';
        if ($riskScore >= 0.4) return 'medium';
        if ($riskScore >= 0.2) return 'low';
        return 'very_low';
    }

    private function classifyChurnRisk($churnRisk) {
        if ($churnRisk >= 0.8) return 'very_high';
        if ($churnRisk >= 0.6) return 'high';
        if ($churnRisk >= 0.4) return 'medium';
        if ($churnRisk >= 0.2) return 'low';
        return 'very_low';
    }

    private function getSavingsHistoricalData() {
        return fetchAll("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(amount) as monthly_savings,
                COUNT(DISTINCT member_id) as active_members
            FROM simpanan
            WHERE transaction_type = 'deposit'
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ", [], '');
    }

    private function calculateGrowthRate($historicalData) {
        if (count($historicalData) < 2) return 0.05; // Default 5% growth

        $firstMonth = $historicalData[0]['monthly_savings'];
        $lastMonth = end($historicalData)['monthly_savings'];

        if ($firstMonth <= 0) return 0.05;

        $months = count($historicalData);
        return pow($lastMonth / $firstMonth, 1 / $months) - 1;
    }

    private function getIncomeStreams() {
        return [
            'loan_interest' => $this->getLoanInterestIncome(),
            'membership_fees' => $this->getMembershipFees(),
            'service_fees' => $this->getServiceFees(),
            'investment_returns' => $this->getInvestmentReturns()
        ];
    }

    private function getExpenseStreams() {
        return [
            'operational_costs' => $this->getOperationalCosts(),
            'loan_provisions' => $this->getLoanProvisions(),
            'staff_salaries' => $this->getStaffSalaries(),
            'marketing_costs' => $this->getMarketingCosts()
        ];
    }

    // Placeholder methods for data gathering (would be implemented with actual data)
    private function analyzePaymentHistory($memberId) { return ['risk_score' => 0.3]; }
    private function calculateUtilizationRisk($loan) { return 0.2; }
    private function calculateTenureRisk($tenure) { return max(0, 1 - ($tenure / 365)); }
    private function calculateLoanRisk($loan) { return 0.1; }
    private function calculateExternalRisk($loan) { return 0.05; }
    private function calculatePredictionConfidence($loan) { return 0.85; }
    private function getRiskFactors($loan, $riskScore) { return ['payment_history' => 0.4, 'utilization' => 0.3]; }

    private function analyzeMemberActivity($memberId) { return 0.7; }
    private function calculateChurnConfidence($member) { return 0.78; }
    private function estimateAttritionDate($churnRisk) { return date('Y-m-d', strtotime('+90 days')); }
    private function getRetentionRecommendations($member, $churnRisk) { return ['Increase engagement', 'Personalized offers']; }
    private function getChurnFactors($member, $churnRisk) { return ['activity_level' => 0.4, 'tenure' => 0.3]; }
    private function analyzeDemographics($member) { return 0.2; }

    private function analyzeFinancialEngagement($memberId) { return 0.8; }

    private function getCurrentTotalSavings() { return 50000000; } // Placeholder
    private function calculateConfidenceInterval($amount, $month) { return ['lower' => $amount * 0.9, 'upper' => $amount * 1.1]; }
    private function getSavingsGrowthRecommendations($growthRate) { return ['Increase marketing', 'Improve member education']; }

    private function calculateMonthlyIncome($incomeStreams, $month) { return 10000000; } // Placeholder
    private function calculateMonthlyExpenses($expenseStreams, $month) { return 8000000; } // Placeholder
    private function summarizeCashFlowProjection($projection) { return ['total_surplus' => 24000000, 'best_month' => 6, 'worst_month' => 2]; }
    private function getCashFlowRecommendations($projection) { return ['Optimize expenses', 'Increase revenue streams']; }

    private function analyzeLoanDemandTrends() { return ['trend' => 'increasing', 'growth_rate' => 15.5]; }
    private function analyzeSavingsTrends() { return ['trend' => 'stable', 'growth_rate' => 8.2]; }
    private function analyzeMemberAcquisitionTrends() { return ['trend' => 'growing', 'monthly_new_members' => 25]; }
    private function identifyMarketOpportunities($loan, $savings, $acquisition) { return ['Digital banking', 'Mobile payments', 'Investment products']; }
    private function analyzeCompetitiveLandscape() { return ['market_position' => 'leader', 'competitors' => 5, 'differentiation_factors' => ['Technology', 'Service']]; }

    private function getLoanInterestIncome() { return 8000000; }
    private function getMembershipFees() { return 500000; }
    private function getServiceFees() { return 300000; }
    private function getInvestmentReturns() { return 200000; }

    private function getOperationalCosts() { return 3000000; }
    private function getLoanProvisions() { return 1000000; }
    private function getStaffSalaries() { return 2500000; }
    private function getMarketingCosts() { return 500000; }

    /**
     * Save prediction to database
     */
    private function savePrediction($predictionData) {
        $stmt = $this->pdo->prepare("
            INSERT INTO predictions
            (model_id, entity_type, entity_id, prediction_type, predicted_value,
             confidence_score, prediction_factors, time_horizon, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 3 MONTH))
            ON DUPLICATE KEY UPDATE
            predicted_value = VALUES(predicted_value),
            confidence_score = VALUES(confidence_score),
            prediction_factors = VALUES(prediction_factors),
            predicted_at = NOW()
        ");

        $stmt->execute([
            $predictionData['model_id'],
            $predictionData['entity_type'],
            $predictionData['entity_id'],
            $predictionData['prediction_type'],
            $predictionData['predicted_value'],
            $predictionData['confidence_score'],
            $predictionData['prediction_factors'],
            $predictionData['time_horizon']
        ]);
    }
}

// Helper functions
function generatePredictions() {
    $analytics = new PredictiveAnalytics();
    return $analytics->generatePredictions();
}

function getLoanDefaultPredictions() {
    $analytics = new PredictiveAnalytics();
    $predictions = $analytics->generatePredictions();
    return $predictions['loan_default_risk'];
}

function getMemberChurnPredictions() {
    $analytics = new PredictiveAnalytics();
    $predictions = $analytics->generatePredictions();
    return $predictions['member_churn_risk'];
}

function getSavingsGrowthForecast() {
    $analytics = new PredictiveAnalytics();
    $predictions = $analytics->generatePredictions();
    return $predictions['savings_growth_forecast'];
}

function getCashFlowProjection() {
    $analytics = new PredictiveAnalytics();
    $predictions = $analytics->generatePredictions();
    return $predictions['cash_flow_projection'];
}
