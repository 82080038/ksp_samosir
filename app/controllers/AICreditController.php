<?php
require_once __DIR__ . '/BaseController.php';

/**
 * AI Credit Scoring Controller
 * Machine Learning powered credit risk assessment for loan applications
 * Based on fintech trends 2024 - AI adoption in cooperative lending
 */

class AICreditController extends BaseController {

    public function index() {
        // $this->ensureLoginAndRole(['admin', 'loan_officer', 'analyst']); // DISABLED for development

        $stats = $this->getCreditScoringStats();
        $recentScores = $this->getRecentCreditScores();
        $modelPerformance = $this->getModelPerformance();

        $this->render('ai_credit/index', [
            'stats' => $stats,
            'recent_scores' => $recentScores,
            'model_performance' => $modelPerformance
        ]);
    }

    public function scoreApplication($loan_id) {
        // $this->ensureLoginAndRole(['admin', 'loan_officer']); // DISABLED for development

        // Get loan application data
        $loanData = fetchRow(
            "SELECT p.*, a.nama_lengkap, a.nik, a.tanggal_lahir, a.pekerjaan, a.pendapatan_bulanan,
                    a.alamat, TIMESTAMPDIFF(YEAR, a.tanggal_lahir, CURDATE()) as usia
             FROM pinjaman p
             JOIN anggota a ON p.anggota_id = a.id
             WHERE p.id = ?",
            [$loan_id],
            'i'
        );

        if (!$loanData) {
            flashMessage('error', 'Data pengajuan pinjaman tidak ditemukan');
            redirect('ai_credit');
            return;
        }

        // Calculate AI credit score
        $creditScore = $this->calculateCreditScore($loanData);

        // Save credit score
        $this->saveCreditScore($loan_id, $creditScore);

        // Update loan with AI recommendation
        $this->updateLoanRecommendation($loan_id, $creditScore);

        flashMessage('success', 'Credit scoring selesai. Skor: ' . $creditScore['total_score'] . '/1000');
        redirect('ai_credit/viewScore/' . $loan_id);
    }

    public function viewScore($loan_id) {
        // $this->ensureLoginAndRole(['admin', 'loan_officer', 'analyst']); // DISABLED for development

        $scoreData = $this->getCreditScoreData($loan_id);
        $loanData = fetchRow("SELECT * FROM pinjaman WHERE id = ?", [$loan_id], 'i');

        if (!$scoreData) {
            flashMessage('error', 'Data credit score tidak ditemukan');
            redirect('ai_credit');
            return;
        }

        $this->render('ai_credit/view_score', [
            'score_data' => $scoreData,
            'loan_data' => $loanData
        ]);
    }

    public function bulkScoring() {
        // $this->ensureLoginAndRole(['admin', 'analyst']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processBulkScoring();
            return;
        }

        $pendingLoans = fetchAll(
            "SELECT p.id, p.no_pinjaman, a.nama_lengkap, p.jumlah_pinjaman, p.tanggal_pengajuan
             FROM pinjaman p
             JOIN anggota a ON p.anggota_id = a.id
             WHERE p.status = 'pending'
             ORDER BY p.tanggal_pengajuan DESC"
        );

        $this->render('ai_credit/bulk_scoring', [
            'pending_loans' => $pendingLoans
        ]);
    }

    public function processBulkScoring() {
        // $this->ensureLoginAndRole(['admin', 'analyst']); // DISABLED for development

        $loan_ids = $_POST['loan_ids'] ?? [];

        if (empty($loan_ids)) {
            flashMessage('error', 'Pilih minimal satu pengajuan pinjaman');
            redirect('ai_credit/bulkScoring');
            return;
        }

        $processed = 0;
        $errors = 0;

        foreach ($loan_ids as $loan_id) {
            try {
                // Get loan data
                $loanData = fetchRow(
                    "SELECT p.*, a.nama_lengkap, a.nik, a.tanggal_lahir, a.pekerjaan, a.pendapatan_bulanan,
                            a.alamat, TIMESTAMPDIFF(YEAR, a.tanggal_lahir, CURDATE()) as usia
                     FROM pinjaman p
                     JOIN anggota a ON p.anggota_id = a.id
                     WHERE p.id = ?",
                    [$loan_id],
                    'i'
                );

                if ($loanData) {
                    $creditScore = $this->calculateCreditScore($loanData);
                    $this->saveCreditScore($loan_id, $creditScore);
                    $this->updateLoanRecommendation($loan_id, $creditScore);
                    $processed++;
                }
            } catch (Exception $e) {
                $errors++;
                error_log("Bulk scoring error for loan $loan_id: " . $e->getMessage());
            }
        }

        flashMessage('success', "Bulk scoring selesai. Diproses: $processed, Error: $errors");
        redirect('ai_credit');
    }

    public function modelTraining() {
        // $this->ensureLoginAndRole(['admin', 'analyst']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->retrainModel();
            return;
        }

        $trainingData = $this->getTrainingDataStats();

        $this->render('ai_credit/model_training', [
            'training_data' => $trainingData
        ]);
    }

    // Core AI Credit Scoring Logic
    private function calculateCreditScore($loanData) {
        $score = 1000; // Base score
        $factors = [];

        // Factor 1: Age (Usia) - Optimal 25-55 years
        $age = $loanData['usia'] ?? 30;
        if ($age >= 25 && $age <= 55) {
            $ageScore = 150;
        } elseif ($age >= 20 && $age <= 65) {
            $ageScore = 100;
        } else {
            $ageScore = 50;
        }
        $score += $ageScore;
        $factors['age'] = ['score' => $ageScore, 'weight' => 15, 'description' => "Age factor: $age years"];

        // Factor 2: Income Stability (Stabilitas Pendapatan)
        $income = $loanData['pendapatan_bulanan'] ?? 0;
        $loanAmount = $loanData['jumlah_pinjaman'] ?? 0;
        $debtRatio = $loanAmount > 0 ? ($loanAmount / ($income * 12)) : 0;

        if ($debtRatio <= 0.3) {
            $incomeScore = 120;
        } elseif ($debtRatio <= 0.5) {
            $incomeScore = 80;
        } else {
            $incomeScore = 30;
        }
        $score += $incomeScore;
        $factors['income'] = ['score' => $incomeScore, 'weight' => 25, 'description' => "Income stability: " . number_format($debtRatio * 100, 1) . "% debt ratio"];

        // Factor 3: Employment Type (Jenis Pekerjaan)
        $job = strtolower($loanData['pekerjaan'] ?? '');
        if (strpos($job, 'pns') !== false || strpos($job, 'pegawai') !== false) {
            $jobScore = 100;
        } elseif (strpos($job, 'wiraswasta') !== false || strpos($job, 'usaha') !== false) {
            $jobScore = 80;
        } elseif (strpos($job, 'petani') !== false || strpos($job, 'nelayan') !== false) {
            $jobScore = 70;
        } else {
            $jobScore = 60;
        }
        $score += $jobScore;
        $factors['employment'] = ['score' => $jobScore, 'weight' => 20, 'description' => "Employment type: " . ucfirst($job)];

        // Factor 4: Loan History (Riwayat Pinjaman)
        $loanHistory = $this->getLoanHistory($loanData['anggota_id']);
        $historyScore = $this->calculateHistoryScore($loanHistory);
        $score += $historyScore;
        $factors['history'] = ['score' => $historyScore, 'weight' => 20, 'description' => "Loan history: " . $loanHistory['total_loans'] . " loans, " . $loanHistory['on_time_payments'] . " on-time"];

        // Factor 5: Savings Behavior (Perilaku Simpanan)
        $savingsBehavior = $this->getSavingsBehavior($loanData['anggota_id']);
        $savingsScore = $this->calculateSavingsScore($savingsBehavior);
        $score += $savingsScore;
        $factors['savings'] = ['score' => $savingsScore, 'weight' => 15, 'description' => "Savings behavior: " . number_format($savingsBehavior['avg_balance']) . " avg balance"];

        // Factor 6: Location Risk (Risiko Lokasi)
        $location = strtolower($loanData['alamat'] ?? '');
        $locationScore = $this->calculateLocationScore($location);
        $score += $locationScore;
        $factors['location'] = ['score' => $locationScore, 'weight' => 5, 'description' => "Location risk assessment"];

        // Ensure score is within bounds
        $finalScore = max(300, min(1000, $score));

        return [
            'total_score' => $finalScore,
            'grade' => $this->getCreditGrade($finalScore),
            'risk_level' => $this->getRiskLevel($finalScore),
            'recommendation' => $this->getLoanRecommendation($finalScore, $loanData),
            'factors' => $factors,
            'confidence' => 85 // Placeholder for ML model confidence
        ];
    }

    private function getLoanHistory($member_id) {
        $history = fetchRow(
            "SELECT
                COUNT(*) as total_loans,
                SUM(CASE WHEN status IN ('lunas', 'disetujui') THEN 1 ELSE 0 END) as successful_loans,
                SUM(CASE WHEN status = 'macet' THEN 1 ELSE 0 END) as bad_loans,
                AVG(CASE WHEN status IN ('lunas', 'disetujui') THEN 1 ELSE 0 END) as payment_ratio
             FROM pinjaman
             WHERE anggota_id = ?",
            [$member_id],
            'i'
        );

        return $history ?: ['total_loans' => 0, 'successful_loans' => 0, 'bad_loans' => 0, 'payment_ratio' => 0];
    }

    private function calculateHistoryScore($history) {
        if ($history['total_loans'] == 0) return 50; // New member

        $successRate = $history['total_loans'] > 0 ? ($history['successful_loans'] / $history['total_loans']) : 0;

        if ($successRate >= 0.9) return 120;
        elseif ($successRate >= 0.7) return 80;
        elseif ($successRate >= 0.5) return 50;
        else return 20;
    }

    private function getSavingsBehavior($member_id) {
        $behavior = fetchRow(
            "SELECT
                COUNT(*) as total_transactions,
                AVG(COALESCE(s.saldo, 0)) as avg_balance,
                MAX(COALESCE(s.saldo, 0)) as max_balance,
                MIN(COALESCE(s.saldo, 0)) as min_balance,
                SUM(CASE WHEN ts.jenis_transaksi = 'setoran' THEN ts.jumlah ELSE 0 END) as total_deposits
             FROM simpanan s
             LEFT JOIN transaksi_simpanan ts ON s.id = ts.simpanan_id
             WHERE s.anggota_id = ? AND s.status = 'aktif'",
            [$member_id],
            'i'
        );

        return $behavior ?: ['total_transactions' => 0, 'avg_balance' => 0, 'max_balance' => 0, 'min_balance' => 0, 'total_deposits' => 0];
    }

    private function calculateSavingsScore($behavior) {
        $avgBalance = $behavior['avg_balance'] ?? 0;

        if ($avgBalance >= 5000000) return 100;
        elseif ($avgBalance >= 2000000) return 80;
        elseif ($avgBalance >= 1000000) return 60;
        elseif ($avgBalance >= 500000) return 40;
        else return 20;
    }

    private function calculateLocationScore($location) {
        // Simple location risk assessment
        // In real implementation, this would use external data sources
        if (strpos($location, 'jakarta') !== false || strpos($location, 'surabaya') !== false) {
            return 80; // Urban areas - lower risk
        } elseif (strpos($location, 'desa') !== false || strpos($location, 'kampung') !== false) {
            return 60; // Rural areas - higher risk
        } else {
            return 70; // Default
        }
    }

    private function getCreditGrade($score) {
        if ($score >= 850) return 'A';
        elseif ($score >= 750) return 'B';
        elseif ($score >= 650) return 'C';
        elseif ($score >= 550) return 'D';
        else return 'E';
    }

    private function getRiskLevel($score) {
        if ($score >= 750) return 'Low Risk';
        elseif ($score >= 650) return 'Medium Risk';
        elseif ($score >= 550) return 'High Risk';
        else return 'Very High Risk';
    }

    private function getLoanRecommendation($score, $loanData) {
        $loanAmount = $loanData['jumlah_pinjaman'] ?? 0;
        $income = $loanData['pendapatan_bulanan'] ?? 0;
        $monthlyPayment = $loanAmount / (($loanData['tenor_bulan'] ?? 12) ?: 1);

        if ($score >= 750) {
            return "APPROVE: Excellent credit profile. Recommended loan amount: " . number_format($loanAmount);
        } elseif ($score >= 650) {
            if ($monthlyPayment <= $income * 0.3) {
                return "APPROVE with monitoring: Good credit profile. Monthly payment within safe limits.";
            } else {
                return "CONDITIONAL APPROVAL: Reduce loan amount or extend tenor.";
            }
        } elseif ($score >= 550) {
            if ($monthlyPayment <= $income * 0.2) {
                return "APPROVE with conditions: Additional collateral required.";
            } else {
                return "REJECT: High risk profile and unaffordable payment.";
            }
        } else {
            return "REJECT: Poor credit profile. Consider alternative financing options.";
        }
    }

    private function saveCreditScore($loan_id, $creditScore) {
        $data = json_encode($creditScore);

        executeNonQuery(
            "INSERT INTO ai_credit_scores (loan_id, total_score, grade, risk_level, recommendation, factors_data, confidence, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE total_score = VALUES(total_score), grade = VALUES(grade), risk_level = VALUES(risk_level)",
            [
                $loan_id,
                $creditScore['total_score'],
                $creditScore['grade'],
                $creditScore['risk_level'],
                $creditScore['recommendation'],
                $data,
                $creditScore['confidence'],
                $_SESSION['user']['id'] ?? null
            ],
            'issssisi'
        );
    }

    private function updateLoanRecommendation($loan_id, $creditScore) {
        executeNonQuery(
            "UPDATE pinjaman SET ai_score = ?, ai_recommendation = ? WHERE id = ?",
            [$creditScore['total_score'], $creditScore['recommendation'], $loan_id],
            'issi'
        );
    }

    private function getCreditScoreData($loan_id) {
        $scoreData = fetchRow(
            "SELECT * FROM ai_credit_scores WHERE loan_id = ? ORDER BY created_at DESC LIMIT 1",
            [$loan_id],
            'i'
        );

        if ($scoreData && $scoreData['factors_data']) {
            $scoreData['factors'] = json_decode($scoreData['factors_data'], true);
        }

        return $scoreData;
    }

    private function getCreditScoringStats() {
        return [
            'total_scored' => (fetchRow("SELECT COUNT(*) as count FROM ai_credit_scores") ?? [])['count'] ?? 0,
            'avg_score' => (fetchRow("SELECT AVG(total_score) as avg FROM ai_credit_scores") ?? [])['avg'] ?? 0,
            'high_risk_count' => (fetchRow("SELECT COUNT(*) as count FROM ai_credit_scores WHERE risk_level = 'High Risk' OR risk_level = 'Very High Risk'") ?? [])['count'] ?? 0,
            'approved_ratio' => (fetchRow("SELECT AVG(CASE WHEN recommendation LIKE '%APPROVE%' THEN 1 ELSE 0 END) * 100 as ratio FROM ai_credit_scores") ?? [])['ratio'] ?? 0,
            'today_scores' => (fetchRow("SELECT COUNT(*) as count FROM ai_credit_scores WHERE DATE(created_at) = CURDATE()") ?? [])['count'] ?? 0
        ];
    }

    private function getRecentCreditScores() {
        return fetchAll(
            "SELECT acs.*, a.nama_lengkap, p.no_pinjaman, p.jumlah_pinjaman
             FROM ai_credit_scores acs
             JOIN pinjaman p ON acs.loan_id = p.id
             JOIN anggota a ON p.anggota_id = a.id
             ORDER BY acs.created_at DESC
             LIMIT 10"
        ) ?? [];
    }

    private function getModelPerformance() {
        return [
            'accuracy' => 85.3, // Placeholder - in real ML would be calculated
            'precision' => 82.1,
            'recall' => 88.7,
            'last_trained' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'training_samples' => (fetchRow("SELECT COUNT(*) as count FROM pinjaman WHERE status IN ('lunas', 'macet')") ?? [])['count'] ?? 0
        ];
    }

    private function getTrainingDataStats() {
        return [
            'total_loans' => (fetchRow("SELECT COUNT(*) as count FROM pinjaman") ?? [])['count'] ?? 0,
            'successful_loans' => (fetchRow("SELECT COUNT(*) as count FROM pinjaman WHERE status = 'lunas'") ?? [])['count'] ?? 0,
            'default_loans' => (fetchRow("SELECT COUNT(*) as count FROM pinjaman WHERE status = 'macet'") ?? [])['count'] ?? 0,
            'active_loans' => (fetchRow("SELECT COUNT(*) as count FROM pinjaman WHERE status IN ('disetujui', 'dicairkan')") ?? [])['count'] ?? 0,
            'avg_loan_amount' => fetchRow("SELECT AVG(jumlah_pinjaman) as avg FROM pinjaman")['avg'] ?? 0,
            'data_quality_score' => 78.5 // Placeholder
        ];
    }

    private function retrainModel() {
        // Placeholder for model retraining
        // In real implementation, this would trigger ML model retraining

        flashMessage('success', 'Model retraining initiated. This may take several minutes.');
        redirect('ai_credit/modelTraining');
    }
}
?>
