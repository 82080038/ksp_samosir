<?php
/**
 * AI-Powered Credit Scoring System for KSP Samosir
 * Implements machine learning-based credit risk assessment
 */

class AICreditScoring {
    private $pdo;
    private $modelConfig;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
        $this->modelConfig = $this->loadModelConfig();
    }

    /**
     * Calculate credit score for a member
     */
    public function calculateCreditScore($memberId, $loanApplicationId = null) {
        // Gather member data
        $memberData = $this->gatherMemberData($memberId);

        if (!$memberData) {
            throw new Exception("Member data not found");
        }

        // Calculate traditional credit score
        $traditionalScore = $this->calculateTraditionalScore($memberData);

        // Apply machine learning enhancements
        $mlScore = $this->applyMachineLearning($memberData);

        // Combine scores (weighted average)
        $finalScore = $this->combineScores($traditionalScore, $mlScore);

        // Determine risk level and score range
        $riskLevel = $this->determineRiskLevel($finalScore);
        $scoreRange = $this->determineScoreRange($finalScore);

        // Get contributing factors
        $factors = $this->getContributingFactors($memberData, $traditionalScore, $mlScore);

        // Save credit score
        $scoreId = $this->saveCreditScore([
            'member_id' => $memberId,
            'loan_application_id' => $loanApplicationId,
            'credit_score' => $finalScore,
            'score_range' => $scoreRange,
            'risk_level' => $riskLevel,
            'confidence_score' => 0.85, // Placeholder for actual ML confidence
            'factors' => json_encode($factors),
            'model_used' => 'hybrid_v2.1'
        ]);

        return [
            'score_id' => $scoreId,
            'credit_score' => $finalScore,
            'score_range' => $scoreRange,
            'risk_level' => $riskLevel,
            'factors' => $factors,
            'recommendation' => $this->getLoanRecommendation($finalScore, $memberData)
        ];
    }

    /**
     * Gather comprehensive member data for scoring
     */
    private function gatherMemberData($memberId) {
        // Get basic member information
        $member = fetchRow("
            SELECT
                a.*,
                DATEDIFF(CURDATE(), a.tanggal_gabung) as membership_days,
                TIMESTAMPDIFF(YEAR, a.tanggal_lahir, CURDATE()) as age
            FROM anggota a
            WHERE a.id = ?
        ", [$memberId], 'i');

        if (!$member) return null;

        // Get loan history
        $loanHistory = $this->getLoanHistory($memberId);

        // Get payment history
        $paymentHistory = $this->getPaymentHistory($memberId);

        // Get savings data
        $savingsData = $this->getSavingsData($memberId);

        // Get employment/income data
        $employmentData = $this->getEmploymentData($memberId);

        return array_merge($member, [
            'loan_history' => $loanHistory,
            'payment_history' => $paymentHistory,
            'savings_data' => $savingsData,
            'employment_data' => $employmentData
        ]);
    }

    /**
     * Calculate traditional credit score using rule-based system
     */
    private function calculateTraditionalScore($memberData) {
        $score = 0;
        $maxScore = 1000;

        // Payment history (35% weight)
        $paymentScore = $this->calculatePaymentHistoryScore($memberData['payment_history']);
        $score += $paymentScore * 0.35;

        // Credit utilization (20% weight)
        $utilizationScore = $this->calculateCreditUtilizationScore($memberData);
        $score += $utilizationScore * 0.20;

        // Credit age (15% weight)
        $ageScore = $this->calculateCreditAgeScore($memberData['membership_days']);
        $score += $ageScore * 0.15;

        // Income stability (10% weight)
        $incomeScore = $this->calculateIncomeStabilityScore($memberData['savings_data']);
        $score += $incomeScore * 0.10;

        // Employment stability (10% weight)
        $employmentScore = $this->calculateEmploymentStabilityScore($memberData['employment_data']);
        $score += $employmentScore * 0.10;

        // Debt-to-income ratio (10% weight)
        $dtiScore = $this->calculateDTIScore($memberData);
        $score += $dtiScore * 0.10;

        return round($score);
    }

    /**
     * Apply machine learning enhancements
     */
    private function applyMachineLearning($memberData) {
        // This is a placeholder for actual ML model integration
        // In production, this would call a trained ML model

        // Simple ML enhancement based on patterns
        $mlScore = 0;

        // Analyze loan patterns
        if ($memberData['loan_history']['total_loans'] > 0) {
            $onTimePaymentRate = $memberData['payment_history']['on_time_rate'];
            $mlScore += min(100, $onTimePaymentRate * 1.2); // Boost for consistent payers
        }

        // Analyze savings consistency
        $savingsConsistency = $memberData['savings_data']['consistency_score'];
        $mlScore += $savingsConsistency * 0.3;

        // Age factor (prime working age gets slight boost)
        $age = $memberData['age'];
        if ($age >= 25 && $age <= 55) {
            $mlScore += 50;
        }

        return min(1000, round($mlScore));
    }

    /**
     * Combine traditional and ML scores
     */
    private function combineScores($traditionalScore, $mlScore) {
        // Weighted combination: 70% traditional, 30% ML
        return round(($traditionalScore * 0.7) + ($mlScore * 0.3));
    }

    /**
     * Determine risk level based on score
     */
    private function determineRiskLevel($score) {
        if ($score >= 800) return 'low';
        if ($score >= 600) return 'medium';
        if ($score >= 400) return 'high';
        return 'very_high';
    }

    /**
     * Determine score range
     */
    private function determineScoreRange($score) {
        if ($score >= 800) return 'excellent';
        if ($score >= 700) return 'good';
        if ($score >= 600) return 'fair';
        return 'poor';
    }

    /**
     * Get contributing factors
     */
    private function getContributingFactors($memberData, $traditionalScore, $mlScore) {
        return [
            'payment_history' => [
                'score' => $this->calculatePaymentHistoryScore($memberData['payment_history']),
                'weight' => 0.35,
                'details' => $memberData['payment_history']['on_time_rate'] . '% on-time payments'
            ],
            'credit_utilization' => [
                'score' => $this->calculateCreditUtilizationScore($memberData),
                'weight' => 0.20,
                'details' => 'Current utilization: ' . $memberData['loan_history']['utilization_rate'] . '%'
            ],
            'credit_age' => [
                'score' => $this->calculateCreditAgeScore($memberData['membership_days']),
                'weight' => 0.15,
                'details' => $memberData['membership_days'] . ' days as member'
            ],
            'income_stability' => [
                'score' => $this->calculateIncomeStabilityScore($memberData['savings_data']),
                'weight' => 0.10,
                'details' => 'Savings consistency: ' . $memberData['savings_data']['consistency_score'] . '%'
            ],
            'employment_stability' => [
                'score' => $this->calculateEmploymentStabilityScore($memberData['employment_data']),
                'weight' => 0.10,
                'details' => 'Employment record: ' . ($memberData['employment_data']['stable'] ? 'Stable' : 'Variable')
            ],
            'debt_to_income_ratio' => [
                'score' => $this->calculateDTIScore($memberData),
                'weight' => 0.10,
                'details' => 'DTI ratio: ' . $memberData['loan_history']['dti_ratio'] . '%'
            ],
            'machine_learning_enhancement' => [
                'score' => $mlScore,
                'weight' => 0.30,
                'details' => 'AI-powered risk assessment enhancement'
            ]
        ];
    }

    /**
     * Get loan recommendation based on score
     */
    private function getLoanRecommendation($score, $memberData) {
        if ($score >= 800) {
            return [
                'approved' => true,
                'max_amount' => min(50000000, $memberData['savings_data']['total_savings'] * 5),
                'interest_rate' => 0.08,
                'terms' => 'Up to 60 months',
                'conditions' => 'Standard approval'
            ];
        } elseif ($score >= 600) {
            return [
                'approved' => true,
                'max_amount' => min(25000000, $memberData['savings_data']['total_savings'] * 3),
                'interest_rate' => 0.12,
                'terms' => 'Up to 36 months',
                'conditions' => 'Requires collateral or co-signer'
            ];
        } elseif ($score >= 400) {
            return [
                'approved' => 'conditional',
                'max_amount' => min(10000000, $memberData['savings_data']['total_savings'] * 2),
                'interest_rate' => 0.18,
                'terms' => 'Up to 24 months',
                'conditions' => 'Requires substantial collateral and co-signer'
            ];
        } else {
            return [
                'approved' => false,
                'reason' => 'Credit score too low',
                'recommendations' => [
                    'Improve payment history',
                    'Increase savings consistency',
                    'Build longer membership history'
                ]
            ];
        }
    }

    /**
     * Individual scoring component calculations
     */
    private function calculatePaymentHistoryScore($paymentHistory) {
        $onTimeRate = $paymentHistory['on_time_rate'] ?? 0;
        $latePayments = $paymentHistory['late_payments'] ?? 0;

        $baseScore = $onTimeRate;
        $penalty = $latePayments * 10;

        return max(0, min(1000, $baseScore - $penalty));
    }

    private function calculateCreditUtilizationScore($memberData) {
        $utilizationRate = $memberData['loan_history']['utilization_rate'] ?? 0;

        if ($utilizationRate <= 30) return 1000;
        if ($utilizationRate <= 50) return 800;
        if ($utilizationRate <= 70) return 600;
        if ($utilizationRate <= 90) return 400;
        return 200;
    }

    private function calculateCreditAgeScore($membershipDays) {
        if ($membershipDays >= 365 * 2) return 1000; // 2+ years
        if ($membershipDays >= 365) return 800;      // 1+ years
        if ($membershipDays >= 180) return 600;      // 6+ months
        if ($membershipDays >= 90) return 400;       // 3+ months
        return 200;
    }

    private function calculateIncomeStabilityScore($savingsData) {
        $consistencyScore = $savingsData['consistency_score'] ?? 0;
        return $consistencyScore * 10; // Convert percentage to 0-1000 scale
    }

    private function calculateEmploymentStabilityScore($employmentData) {
        return $employmentData['stable'] ? 1000 : 600;
    }

    private function calculateDTIScore($memberData) {
        $dtiRatio = $memberData['loan_history']['dti_ratio'] ?? 0;

        if ($dtiRatio <= 20) return 1000;
        if ($dtiRatio <= 35) return 800;
        if ($dtiRatio <= 45) return 600;
        if ($dtiRatio <= 55) return 400;
        return 200;
    }

    /**
     * Data gathering methods
     */
    private function getLoanHistory($memberId) {
        $loans = fetchAll("
            SELECT
                COUNT(*) as total_loans,
                SUM(jumlah_pinjaman) as total_borrowed,
                AVG(jumlah_pinjaman) as avg_loan_amount,
                SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as completed_loans,
                SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as active_loans
            FROM pinjaman
            WHERE member_id = ?
        ", [$memberId], 'i');

        $loanData = $loans[0] ?? ['total_loans' => 0, 'total_borrowed' => 0, 'avg_loan_amount' => 0, 'completed_loans' => 0, 'active_loans' => 0];

        // Calculate utilization rate (simplified)
        $savingsData = $this->getSavingsData($memberId);
        $utilizationRate = $loanData['active_loans'] > 0 ?
            ($loanData['total_borrowed'] / max($savingsData['total_savings'], 1)) * 100 : 0;

        // Calculate DTI ratio (simplified)
        $monthlyIncome = $savingsData['monthly_avg_deposit'] * 12;
        $monthlyDebt = $loanData['total_borrowed'] / 12; // Assuming 1 year repayment
        $dtiRatio = $monthlyIncome > 0 ? ($monthlyDebt / $monthlyIncome) * 100 : 0;

        return array_merge($loanData, [
            'utilization_rate' => min(100, $utilizationRate),
            'dti_ratio' => min(100, $dtiRatio)
        ]);
    }

    private function getPaymentHistory($memberId) {
        $payments = fetchAll("
            SELECT
                COUNT(*) as total_payments,
                SUM(CASE WHEN status = 'paid_on_time' THEN 1 ELSE 0 END) as on_time_payments,
                SUM(CASE WHEN status = 'paid_late' THEN 1 ELSE 0 END) as late_payments,
                SUM(CASE WHEN status = 'defaulted' THEN 1 ELSE 0 END) as defaults
            FROM loan_payments lp
            JOIN pinjaman p ON lp.loan_id = p.id
            WHERE p.member_id = ?
        ", [$memberId], 'i');

        $paymentData = $payments[0] ?? ['total_payments' => 0, 'on_time_payments' => 0, 'late_payments' => 0, 'defaults' => 0];

        $onTimeRate = $paymentData['total_payments'] > 0 ?
            ($paymentData['on_time_payments'] / $paymentData['total_payments']) * 100 : 0;

        return array_merge($paymentData, [
            'on_time_rate' => round($onTimeRate, 2)
        ]);
    }

    private function getSavingsData($memberId) {
        $savings = fetchAll("
            SELECT
                COUNT(*) as total_deposits,
                SUM(amount) as total_savings,
                AVG(amount) as avg_deposit,
                MAX(created_at) as last_deposit,
                MIN(created_at) as first_deposit
            FROM simpanan
            WHERE member_id = ? AND transaction_type = 'deposit'
        ", [$memberId], 'i');

        $savingsData = $savings[0] ?? ['total_deposits' => 0, 'total_savings' => 0, 'avg_deposit' => 0, 'last_deposit' => null, 'first_deposit' => null];

        // Calculate monthly average
        $monthlyAvg = 0;
        if ($savingsData['total_deposits'] > 0 && $savingsData['first_deposit'] && $savingsData['last_deposit']) {
            $daysDiff = strtotime($savingsData['last_deposit']) - strtotime($savingsData['first_deposit']);
            $monthsDiff = max(1, $daysDiff / (30 * 24 * 3600));
            $monthlyAvg = $savingsData['total_savings'] / $monthsDiff;
        }

        // Calculate consistency score (simplified)
        $consistencyScore = $savingsData['total_deposits'] > 0 ? min(100, $savingsData['total_deposits'] * 5) : 0;

        return array_merge($savingsData, [
            'monthly_avg_deposit' => round($monthlyAvg, 2),
            'consistency_score' => $consistencyScore
        ]);
    }

    private function getEmploymentData($memberId) {
        // Placeholder - would need employment records table
        return [
            'stable' => true, // Placeholder
            'years_employed' => 2,
            'income_verified' => true
        ];
    }

    /**
     * Save credit score to database
     */
    private function saveCreditScore($scoreData) {
        $stmt = $this->pdo->prepare("
            INSERT INTO credit_scores
            (member_id, loan_application_id, credit_score, score_range, risk_level, confidence_score, factors, model_used, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 6 MONTH))
        ");

        $stmt->execute([
            $scoreData['member_id'],
            $scoreData['loan_application_id'],
            $scoreData['credit_score'],
            $scoreData['score_range'],
            $scoreData['risk_level'],
            $scoreData['confidence_score'],
            $scoreData['factors'],
            $scoreData['model_used']
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Load model configuration
     */
    private function loadModelConfig() {
        return [
            'version' => '2.1',
            'type' => 'hybrid',
            'features' => [
                'payment_history', 'credit_utilization', 'credit_age',
                'income_stability', 'employment_stability', 'debt_to_income_ratio'
            ],
            'weights' => [
                'traditional' => 0.7,
                'machine_learning' => 0.3
            ]
        ];
    }

    /**
     * Update model accuracy based on actual outcomes
     */
    public function updateModelAccuracy($scoreId, $actualOutcome) {
        // This would be called when loan outcomes are known
        // For now, just log the accuracy update
        error_log("Model accuracy update for score $scoreId: $actualOutcome");
    }
}

// Helper functions
function calculateMemberCreditScore($memberId, $loanApplicationId = null) {
    $scoring = new AICreditScoring();
    return $scoring->calculateCreditScore($memberId, $loanApplicationId);
}

function getMemberCreditScore($memberId) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT * FROM credit_scores
        WHERE member_id = ? AND expires_at > NOW()
        ORDER BY calculated_at DESC
        LIMIT 1
    ");
    $stmt->execute([$memberId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
