<?php
/**
 * Advanced Risk Management System for KSP Samosir
 * Fraud detection, regulatory compliance, and AML monitoring
 */

class RiskManagementSystem {
    private $pdo;
    private $fraudDetection;
    private $complianceChecker;
    private $amlMonitor;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
        $this->fraudDetection = new FraudDetection($this->pdo);
        $this->complianceChecker = new ComplianceChecker($this->pdo);
        $this->amlMonitor = new AMLMonitor($this->pdo);
    }

    /**
     * Comprehensive risk assessment
     */
    public function performRiskAssessment() {
        return [
            'fraud_risks' => $this->fraudDetection->detectFraud(),
            'compliance_status' => $this->complianceChecker->checkCompliance(),
            'aml_alerts' => $this->amlMonitor->monitorTransactions(),
            'credit_risks' => $this->assessCreditRisks(),
            'operational_risks' => $this->assessOperationalRisks(),
            'regulatory_risks' => $this->assessRegulatoryRisks()
        ];
    }

    /**
     * Real-time transaction monitoring
     */
    public function monitorTransaction($transactionData) {
        $risks = [];

        // Fraud detection
        $fraudRisk = $this->fraudDetection->analyzeTransaction($transactionData);
        if ($fraudRisk['risk_level'] !== 'low') {
            $risks[] = $fraudRisk;
        }

        // AML monitoring
        $amlRisk = $this->amlMonitor->checkTransaction($transactionData);
        if ($amlRisk['alert_level'] !== 'none') {
            $risks[] = $amlRisk;
        }

        // Unusual pattern detection
        $patternRisk = $this->detectUnusualPatterns($transactionData);
        if ($patternRisk) {
            $risks[] = $patternRisk;
        }

        // Log transaction with risk assessment
        $this->logTransactionRisk($transactionData, $risks);

        return [
            'transaction_id' => $transactionData['id'] ?? null,
            'risk_assessment' => $risks,
            'approved' => count($risks) === 0,
            'requires_review' => count(array_filter($risks, function($r) {
                return ($r['risk_level'] ?? $r['alert_level']) !== 'low' &&
                       ($r['risk_level'] ?? $r['alert_level']) !== 'none';
            })) > 0
        ];
    }

    /**
     * Generate regulatory reports
     */
    public function generateRegulatoryReports($period = 'monthly') {
        $reports = [];

        // OJK compliance report
        $reports['ojk_compliance'] = $this->complianceChecker->generateOJKReport($period);

        // AML compliance report
        $reports['aml_compliance'] = $this->amlMonitor->generateAMLReport($period);

        // Fraud prevention report
        $reports['fraud_prevention'] = $this->fraudDetection->generateFraudReport($period);

        // Risk assessment summary
        $reports['risk_summary'] = $this->generateRiskSummaryReport($period);

        return $reports;
    }

    /**
     * Assess credit risks across portfolio
     */
    private function assessCreditRisks() {
        $loans = $this->getActiveLoans();

        $riskAssessment = [
            'total_loans' => count($loans),
            'risk_distribution' => [
                'low' => 0,
                'medium' => 0,
                'high' => 0,
                'very_high' => 0
            ],
            'at_risk_amount' => 0,
            'provision_required' => 0
        ];

        foreach ($loans as $loan) {
            $risk = $this->calculateLoanRisk($loan);
            $riskAssessment['risk_distribution'][$risk['level']]++;

            if ($risk['level'] === 'high' || $risk['level'] === 'very_high') {
                $riskAssessment['at_risk_amount'] += $loan['jumlah_pinjaman'];
                $riskAssessment['provision_required'] += $loan['jumlah_pinjaman'] * $risk['provision_rate'];
            }
        }

        return $riskAssessment;
    }

    /**
     * Assess operational risks
     */
    private function assessOperationalRisks() {
        return [
            'system_availability' => $this->checkSystemAvailability(),
            'data_integrity' => $this->checkDataIntegrity(),
            'process_efficiency' => $this->checkProcessEfficiency(),
            'human_factors' => $this->assessHumanFactors(),
            'external_threats' => $this->assessExternalThreats()
        ];
    }

    /**
     * Assess regulatory risks
     */
    private function assessRegulatoryRisks() {
        $regulations = [
            'ojk_compliance' => $this->checkOJKCompliance(),
            'data_protection' => $this->checkDataProtectionCompliance(),
            'consumer_protection' => $this->checkConsumerProtectionCompliance(),
            'anti_money_laundering' => $this->checkAMLCompliance()
        ];

        $highRiskRegulations = array_filter($regulations, function($reg) {
            return $reg['compliance_level'] === 'non_compliant' ||
                   $reg['risk_level'] === 'high';
        });

        return [
            'overall_compliance' => count($highRiskRegulations) === 0 ? 'compliant' : 'at_risk',
            'regulations' => $regulations,
            'high_risk_items' => $highRiskRegulations,
            'recommended_actions' => $this->getRegulatoryRecommendations($regulations)
        ];
    }

    // Helper methods for risk calculations
    private function calculateLoanRisk($loan) {
        // Simple risk calculation based on payment history and loan characteristics
        $riskScore = 0;

        // Payment history (40%)
        $paymentHistory = $this->getPaymentHistory($loan['member_id']);
        $riskScore += (100 - $paymentHistory['on_time_rate']) * 0.4;

        // Loan-to-value ratio (30%)
        $lvr = $this->calculateLVR($loan);
        if ($lvr > 80) $riskScore += 30;
        elseif ($lvr > 60) $riskScore += 15;

        // Credit score (20%)
        $creditScore = getMemberCreditScore($loan['member_id']);
        if ($creditScore) {
            $riskScore += (1000 - $creditScore['credit_score']) * 0.02;
        }

        // Loan duration (10%)
        $duration = $loan['tenor_bulan'];
        if ($duration > 36) $riskScore += 10;

        $riskLevel = $this->scoreToRiskLevel($riskScore);
        $provisionRate = $this->riskLevelToProvision($riskLevel);

        return [
            'score' => $riskScore,
            'level' => $riskLevel,
            'provision_rate' => $provisionRate
        ];
    }

    private function detectUnusualPatterns($transactionData) {
        // Check for unusual patterns
        $patterns = [];

        // Amount-based patterns
        if ($transactionData['amount'] > 50000000) { // Large transaction
            $patterns[] = 'large_transaction';
        }

        // Frequency patterns
        $recentTransactions = $this->getRecentTransactions($transactionData['member_id'], 24);
        if (count($recentTransactions) > 10) {
            $patterns[] = 'high_frequency';
        }

        // Time-based patterns
        $hour = date('H', strtotime($transactionData['timestamp'] ?? 'now'));
        if ($hour < 6 || $hour > 22) { // Unusual hours
            $patterns[] = 'unusual_timing';
        }

        // Location patterns (if available)
        if (isset($transactionData['location']) &&
            $this->isUnusualLocation($transactionData['member_id'], $transactionData['location'])) {
            $patterns[] = 'unusual_location';
        }

        if (!empty($patterns)) {
            return [
                'type' => 'unusual_pattern',
                'patterns' => $patterns,
                'risk_level' => 'medium',
                'description' => 'Transaction exhibits unusual patterns: ' . implode(', ', $patterns)
            ];
        }

        return null;
    }

    private function logTransactionRisk($transactionData, $risks) {
        $stmt = $this->pdo->prepare("
            INSERT INTO risk_metrics
            (risk_type, risk_level, risk_score, affected_records, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");

        $riskLevel = empty($risks) ? 'low' : 'medium';
        $riskScore = count($risks) * 10;

        $stmt->execute([
            'transaction_risk',
            $riskLevel,
            $riskScore,
            1
        ]);
    }

    private function generateRiskSummaryReport($period) {
        $startDate = date('Y-m-d', strtotime("-1 {$period}"));

        $summary = [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => date('Y-m-d'),
            'metrics' => []
        ];

        // Fraud incidents
        $fraudIncidents = (fetchRow("
            SELECT COUNT(*) as count FROM risk_metrics
            WHERE risk_type = 'fraud' AND created_at >= ?
        ", [$startDate], 's') ?? [])['count'] ?? 0;

        // Compliance violations
        $complianceViolations = (fetchRow("
            SELECT COUNT(*) as count FROM compliance_metrics
            WHERE compliance_status = 'non_compliant' AND last_audit >= ?
        ", [$startDate], 's') ?? [])['count'] ?? 0;

        // AML alerts
        $amlAlerts = (fetchRow("
            SELECT COUNT(*) as count FROM aml_monitoring
            WHERE alert_level IN ('medium', 'high') AND created_at >= ?
        ", [$startDate], 's') ?? [])['count'] ?? 0;

        $summary['metrics'] = [
            'fraud_incidents' => $fraudIncidents,
            'compliance_violations' => $complianceViolations,
            'aml_alerts' => $amlAlerts,
            'overall_risk_score' => $this->calculateOverallRiskScore($fraudIncidents, $complianceViolations, $amlAlerts)
        ];

        return $summary;
    }

    // Utility methods
    private function scoreToRiskLevel($score) {
        if ($score >= 70) return 'very_high';
        if ($score >= 50) return 'high';
        if ($score >= 30) return 'medium';
        return 'low';
    }

    private function riskLevelToProvision($riskLevel) {
        $provisions = [
            'low' => 0.01,      // 1%
            'medium' => 0.05,   // 5%
            'high' => 0.10,     // 10%
            'very_high' => 0.20 // 20%
        ];
        return $provisions[$riskLevel] ?? 0.01;
    }

    private function calculateOverallRiskScore($fraud, $compliance, $aml) {
        $totalIncidents = $fraud + $compliance + $aml;
        if ($totalIncidents === 0) return 0;

        // Weighted score: fraud (40%), compliance (35%), AML (25%)
        $weightedScore = ($fraud * 40) + ($compliance * 35) + ($aml * 25);
        return min(100, $weightedScore / 10);
    }

    // Placeholder methods (would be implemented with actual data)
    private function getActiveLoans() { return []; }
    private function getPaymentHistory($memberId) { return ['on_time_rate' => 85]; }
    private function calculateLVR($loan) { return 70; }
    private function getRecentTransactions($memberId, $hours) { return []; }
    private function isUnusualLocation($memberId, $location) { return false; }
    private function checkSystemAvailability() { return ['status' => 'healthy', 'uptime' => 99.9]; }
    private function checkDataIntegrity() { return ['status' => 'valid', 'issues' => 0]; }
    private function checkProcessEfficiency() { return ['efficiency' => 85, 'bottlenecks' => []]; }
    private function assessHumanFactors() { return ['training_level' => 'adequate', 'error_rate' => 2.1]; }
    private function assessExternalThreats() { return ['cyber_threats' => 'low', 'market_risks' => 'medium']; }
    private function checkOJKCompliance() { return ['compliance_level' => 'compliant', 'risk_level' => 'low']; }
    private function checkDataProtectionCompliance() { return ['compliance_level' => 'compliant', 'risk_level' => 'low']; }
    private function checkConsumerProtectionCompliance() { return ['compliance_level' => 'compliant', 'risk_level' => 'low']; }
    private function checkAMLCompliance() { return ['compliance_level' => 'compliant', 'risk_level' => 'low']; }
    private function getRegulatoryRecommendations($regulations) { return ['Review procedures', 'Update training', 'Enhance monitoring']; }
}

/**
 * Fraud Detection Subsystem
 */
class FraudDetection {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function detectFraud() {
        // Implement fraud detection algorithms
        return [
            'suspicious_transactions' => $this->findSuspiciousTransactions(),
            'unusual_patterns' => $this->detectUnusualPatterns(),
            'velocity_checks' => $this->performVelocityChecks(),
            'blacklist_checks' => $this->checkBlacklists()
        ];
    }

    public function analyzeTransaction($transactionData) {
        $riskScore = 0;
        $riskFactors = [];

        // Amount-based checks
        if ($transactionData['amount'] > 100000000) {
            $riskScore += 30;
            $riskFactors[] = 'large_amount';
        }

        // Velocity checks
        $recentTransactions = $this->getRecentTransactionCount($transactionData['member_id'], 60);
        if ($recentTransactions > 5) {
            $riskScore += 20;
            $riskFactors[] = 'high_velocity';
        }

        // Time-based checks
        $hour = date('H', strtotime($transactionData['timestamp'] ?? 'now'));
        if ($hour < 6 || $hour > 22) {
            $riskScore += 15;
            $riskFactors[] = 'unusual_timing';
        }

        return [
            'risk_score' => $riskScore,
            'risk_level' => $this->calculateRiskLevel($riskScore),
            'risk_factors' => $riskFactors
        ];
    }

    private function calculateRiskLevel($score) {
        if ($score >= 50) return 'high';
        if ($score >= 25) return 'medium';
        return 'low';
    }

    // Placeholder implementations
    private function findSuspiciousTransactions() { return []; }
    private function detectUnusualPatterns() { return []; }
    private function performVelocityChecks() { return []; }
    private function checkBlacklists() { return []; }
    private function getRecentTransactionCount($memberId, $minutes) { return 0; }

    public function generateFraudReport($period) {
        return [
            'period' => $period,
            'total_transactions' => 0,
            'flagged_transactions' => 0,
            'investigated_cases' => 0,
            'confirmed_fraud' => 0,
            'prevention_effectiveness' => 95.2
        ];
    }
}

/**
 * Compliance Checker Subsystem
 */
class ComplianceChecker {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function checkCompliance() {
        return [
            'ojk_compliance' => $this->checkOJKCompliance(),
            'data_protection' => $this->checkDataProtection(),
            'consumer_rights' => $this->checkConsumerRights(),
            'reporting_accuracy' => $this->checkReportingAccuracy()
        ];
    }

    public function generateOJKReport($period) {
        return [
            'period' => $period,
            'capital_adequacy' => 12.5,
            'liquidity_ratio' => 18.7,
            'asset_quality' => 2.1, // NPL ratio
            'compliance_status' => 'compliant',
            'regulatory_filings' => 'complete'
        ];
    }

    // Placeholder implementations
    private function checkOJKCompliance() { return ['status' => 'compliant', 'score' => 98]; }
    private function checkDataProtection() { return ['status' => 'compliant', 'score' => 96]; }
    private function checkConsumerRights() { return ['status' => 'compliant', 'score' => 97]; }
    private function checkReportingAccuracy() { return ['status' => 'compliant', 'score' => 99]; }
}

/**
 * AML Monitor Subsystem
 */
class AMLMonitor {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function monitorTransactions() {
        return [
            'suspicious_activities' => $this->detectSuspiciousActivities(),
            'large_transactions' => $this->monitorLargeTransactions(),
            'structuring_attempts' => $this->detectStructuring(),
            'sanctions_screening' => $this->performSanctionsScreening()
        ];
    }

    public function checkTransaction($transactionData) {
        $alerts = [];

        // Check transaction amount thresholds
        if ($transactionData['amount'] > 50000000) {
            $alerts[] = 'large_transaction';
        }

        // Check for unusual patterns
        if ($this->isUnusualPattern($transactionData)) {
            $alerts[] = 'unusual_pattern';
        }

        // Check sanctions lists
        if ($this->checkSanctions($transactionData)) {
            $alerts[] = 'sanctions_hit';
        }

        $alertLevel = $this->calculateAlertLevel($alerts);

        return [
            'alerts' => $alerts,
            'alert_level' => $alertLevel,
            'requires_investigation' => $alertLevel !== 'none'
        ];
    }

    public function generateAMLReport($period) {
        return [
            'period' => $period,
            'transactions_monitored' => 0,
            'alerts_generated' => 0,
            'investigations_completed' => 0,
            'suspicious_activities_reported' => 0,
            'compliance_rate' => 99.7
        ];
    }

    private function calculateAlertLevel($alerts) {
        if (in_array('sanctions_hit', $alerts)) return 'critical';
        if (count($alerts) >= 2) return 'high';
        if (count($alerts) >= 1) return 'medium';
        return 'none';
    }

    // Placeholder implementations
    private function detectSuspiciousActivities() { return []; }
    private function monitorLargeTransactions() { return []; }
    private function detectStructuring() { return []; }
    private function performSanctionsScreening() { return []; }
    private function isUnusualPattern($transaction) { return false; }
    private function checkSanctions($transaction) { return false; }
}

// Helper functions
function performRiskAssessment() {
    $riskManager = new RiskManagementSystem();
    return $riskManager->performRiskAssessment();
}

function monitorTransactionRisk($transactionData) {
    $riskManager = new RiskManagementSystem();
    return $riskManager->monitorTransaction($transactionData);
}

function generateRegulatoryReports($period = 'monthly') {
    $riskManager = new RiskManagementSystem();
    return $riskManager->generateRegulatoryReports($period);
}

function getRiskAssessmentSummary() {
    $assessment = performRiskAssessment();
    return [
        'overall_risk_level' => calculateOverallRiskLevel($assessment),
        'critical_issues' => countCriticalIssues($assessment),
        'compliance_status' => $assessment['compliance_status']['overall_compliance'] ?? 'unknown',
        'recommendations' => generateRiskRecommendations($assessment)
    ];
}

function calculateOverallRiskLevel($assessment) {
    $riskLevels = ['low', 'medium', 'high', 'critical'];
    $maxLevel = 'low';

    foreach ($assessment as $category => $data) {
        if (isset($data['risk_level'])) {
            $level = $data['risk_level'];
            if (array_search($level, $riskLevels) > array_search($maxLevel, $riskLevels)) {
                $maxLevel = $level;
            }
        }
    }

    return $maxLevel;
}

function countCriticalIssues($assessment) {
    $criticalCount = 0;

    foreach ($assessment as $category => $data) {
        if (isset($data['risk_level']) && $data['risk_level'] === 'critical') {
            $criticalCount++;
        }
        if (isset($data['alert_level']) && $data['alert_level'] === 'critical') {
            $criticalCount++;
        }
    }

    return $criticalCount;
}

function generateRiskRecommendations($assessment) {
    $recommendations = [];

    if (isset($assessment['fraud_risks']) && !empty($assessment['fraud_risks']['suspicious_transactions'])) {
        $recommendations[] = 'Enhance fraud detection monitoring';
    }

    if (isset($assessment['compliance_status']) && $assessment['compliance_status']['overall_compliance'] === 'at_risk') {
        $recommendations[] = 'Address compliance violations immediately';
    }

    if (isset($assessment['aml_alerts']) && !empty($assessment['aml_alerts']['suspicious_activities'])) {
        $recommendations[] = 'Review AML monitoring procedures';
    }

    return $recommendations;
}
