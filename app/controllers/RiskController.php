<?php
require_once __DIR__ . '/BaseController.php';

/**
 * RiskController handles risk management and compliance monitoring.
 * Monitors suspicious activities, overdue payments, and compliance issues.
 */
class RiskController extends BaseController {
    /**
     * Display risk management dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $risk_stats = $this->getRiskStats();
        $recent_alerts = $this->getRecentAlerts();
        $compliance_status = $this->getComplianceStatus();

        $this->render(__DIR__ . '/../views/risk/index.php', [
            'risk_stats' => $risk_stats,
            'recent_alerts' => $recent_alerts,
            'compliance_status' => $compliance_status
        ]);
    }

    /**
     * Monitor suspicious transactions.
     */
    public function monitorTransactions() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        // Check for suspicious patterns
        $this->checkLargeTransactions();
        $this->checkFrequentReturns();
        $this->checkUnusualLoginPatterns();
        $this->checkOverduePayments();

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = fetchRow("SELECT COUNT(*) as count FROM risk_alerts WHERE type = 'transaction'")['count'];
        $totalPages = ceil($total / $perPage);

        $alerts = fetchAll("SELECT ra.*, u.full_name as user_name FROM risk_alerts ra LEFT JOIN users u ON ra.user_id = u.id WHERE ra.type = 'transaction' ORDER BY ra.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render(__DIR__ . '/../views/risk/transactions.php', [
            'alerts' => $alerts,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Monitor compliance status.
     */
    public function compliance() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $compliance_checks = [
            'member_data_complete' => $this->checkMemberDataCompleteness(),
            'financial_records_accurate' => $this->checkFinancialRecordsAccuracy(),
            'regulatory_compliance' => $this->checkRegulatoryCompliance(),
            'audit_trail_complete' => $this->checkAuditTrailCompleteness(),
            'data_backup_regular' => $this->checkDataBackupRegularity()
        ];

        $this->render(__DIR__ . '/../views/risk/compliance.php', [
            'compliance_checks' => $compliance_checks
        ]);
    }

    /**
     * Generate compliance report.
     */
    public function generateComplianceReport() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $report_date = date('Y-m-d');
        $compliance_data = $this->getComplianceStatus();

        // Generate PDF report (placeholder)
        flashMessage('info', 'Laporan compliance akan dihasilkan dalam format PDF (placeholder)');

        $this->render(__DIR__ . '/../views/risk/compliance_report.php', [
            'report_date' => $report_date,
            'compliance_data' => $compliance_data
        ]);
    }

    /**
     * Fraud detection system.
     */
    public function fraudDetection() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        // Run fraud detection algorithms
        $suspicious_transactions = $this->detectFraudulentTransactions();
        $unusual_patterns = $this->detectUnusualPatterns();

        $this->render(__DIR__ . '/../views/risk/fraud_detection.php', [
            'suspicious_transactions' => $suspicious_transactions,
            'unusual_patterns' => $unusual_patterns
        ]);
    }

    /**
     * Risk assessment for members.
     */
    public function memberRiskAssessment() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        // Calculate risk scores for members
        $members = fetchAll("SELECT a.id, a.nama_lengkap, a.status, 
            COUNT(p.id) as loan_count,
            COUNT(r.id) as return_count,
            DATEDIFF(CURDATE(), MAX(p.created_at)) as days_since_last_loan
            FROM anggota a 
            LEFT JOIN pinjaman p ON a.id = p.anggota_id 
            LEFT JOIN returns r ON a.id = r.customer_id 
            GROUP BY a.id 
            ORDER BY a.nama_lengkap 
            LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        // Calculate risk scores
        foreach ($members as &$member) {
            $member['risk_score'] = $this->calculateMemberRiskScore($member);
            $member['risk_level'] = $this->getRiskLevel($member['risk_score']);
        }

        $total = fetchRow("SELECT COUNT(*) as count FROM anggota")['count'];
        $totalPages = ceil($total / $perPage);

        $this->render(__DIR__ . '/../views/risk/member_risk.php', [
            'members' => $members,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Check large transactions.
     */
    private function checkLargeTransactions() {
        $threshold = 10000000; // 10 million IDR

        $large_transactions = fetchAll("SELECT p.id, p.total_harga, u.full_name as customer_name, p.created_at FROM penjualan p LEFT JOIN users u ON p.pelanggan_id = u.id WHERE p.total_harga > ? AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)", [$threshold], 'd');

        foreach ($large_transactions as $transaction) {
            $this->createRiskAlert('transaction', $transaction['id'], 'large_transaction', 'Transaksi besar: Rp ' . formatCurrency($transaction['total_harga']), 'high', $transaction['customer_name']);
        }
    }

    /**
     * Check frequent returns.
     */
    private function checkFrequentReturns() {
        $frequent_returners = fetchAll("SELECT u.id, u.full_name, COUNT(r.id) as return_count FROM users u LEFT JOIN returns r ON u.id = r.customer_id WHERE r.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY u.id HAVING return_count > 3");

        foreach ($frequent_returners as $customer) {
            $this->createRiskAlert('customer', $customer['id'], 'frequent_returns', 'Return berulang: ' . $customer['return_count'] . ' kali dalam 30 hari', 'medium', $customer['full_name']);
        }
    }

    /**
     * Check unusual login patterns.
     */
    private function checkUnusualLoginPatterns() {
        // Placeholder for login pattern analysis
        // Would require login logs table
    }

    /**
     * Check overdue payments.
     */
    private function checkOverduePayments() {
        $overdue_invoices = fetchAll("SELECT ci.id, ci.invoice_number, u.full_name as customer_name, DATEDIFF(CURDATE(), ci.due_date) as days_overdue FROM customer_invoices ci LEFT JOIN users u ON ci.customer_id = u.id WHERE ci.status = 'unpaid' AND ci.due_date < CURDATE()");

        foreach ($overdue_invoices as $invoice) {
            $this->createRiskAlert('invoice', $invoice['id'], 'overdue_payment', 'Invoice overdue ' . $invoice['days_overdue'] . ' hari', 'high', $invoice['customer_name']);
        }
    }

    /**
     * Create risk alert.
     */
    private function createRiskAlert($type, $reference_id, $risk_type, $description, $severity, $entity_name = '') {
        runInTransaction(function($conn) use ($type, $reference_id, $risk_type, $description, $severity, $entity_name) {
            // Check if alert already exists for this reference
            $existing = fetchRow("SELECT id FROM risk_alerts WHERE type = ? AND reference_id = ? AND risk_type = ? AND DATE(created_at) = CURDATE()", [$type, $reference_id, $risk_type], 'sis');
            if ($existing) return;

            $stmt = $conn->prepare("INSERT INTO risk_alerts (type, reference_id, risk_type, description, severity, entity_name, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, 'active', ?, NOW())");
            $created_by = $_SESSION['user']['id'] ?? 1;
            $stmt->bind_param('sisssis', $type, $reference_id, $risk_type, $description, $severity, $entity_name, $created_by);
            $stmt->execute();
            $stmt->close();
        });
    }

    /**
     * Get risk statistics.
     */
    private function getRiskStats() {
        $stats = [];

        $stats['total_alerts'] = fetchRow("SELECT COUNT(*) as total FROM risk_alerts WHERE status = 'active'")['total'];
        $stats['high_severity_alerts'] = fetchRow("SELECT COUNT(*) as total FROM risk_alerts WHERE severity = 'high' AND status = 'active'")['total'];
        $stats['overdue_invoices'] = fetchRow("SELECT COUNT(*) as total FROM customer_invoices WHERE status = 'unpaid' AND due_date < CURDATE()")['total'];
        $stats['high_risk_members'] = fetchRow("SELECT COUNT(*) as total FROM anggota WHERE status = 'aktif' AND id IN (SELECT DISTINCT customer_id FROM returns WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY customer_id HAVING COUNT(*) > 2)")['total'];

        return $stats;
    }

    /**
     * Get recent risk alerts.
     */
    private function getRecentAlerts() {
        return fetchAll("SELECT ra.*, u.full_name as created_by_name FROM risk_alerts ra LEFT JOIN users u ON ra.created_by = u.id WHERE ra.status = 'active' ORDER BY ra.created_at DESC LIMIT 10");
    }

    /**
     * Get compliance status.
     */
    private function getComplianceStatus() {
        return [
            'member_data_completeness' => $this->checkMemberDataCompleteness(),
            'financial_record_accuracy' => $this->checkFinancialRecordsAccuracy(),
            'regulatory_compliance' => $this->checkRegulatoryCompliance(),
            'audit_trail_integrity' => $this->checkAuditTrailCompleteness(),
            'backup_regularity' => $this->checkDataBackupRegularity()
        ];
    }

    /**
     * Check member data completeness.
     */
    private function checkMemberDataCompleteness() {
        $total_members = fetchRow("SELECT COUNT(*) as total FROM anggota")['total'];
        $complete_members = fetchRow("SELECT COUNT(*) as total FROM anggota WHERE nama_lengkap IS NOT NULL AND nik IS NOT NULL AND alamat IS NOT NULL")['total'];

        return [
            'status' => $complete_members / $total_members >= 0.95 ? 'compliant' : 'warning',
            'percentage' => round(($complete_members / $total_members) * 100, 1),
            'message' => "Data lengkap: {$complete_members}/{$total_members} anggota"
        ];
    }

    /**
     * Check financial records accuracy.
     */
    private function checkFinancialRecordsAccuracy() {
        // Simple check: ensure no negative balances
        $negative_accounts = fetchRow("SELECT COUNT(*) as total FROM coa WHERE saldo < 0")['total'];

        return [
            'status' => $negative_accounts == 0 ? 'compliant' : 'error',
            'issues' => $negative_accounts,
            'message' => $negative_accounts == 0 ? 'Tidak ada saldo negatif' : "Ditemukan {$negative_accounts} rekening dengan saldo negatif"
        ];
    }

    /**
     * Check regulatory compliance.
     */
    private function checkRegulatoryCompliance() {
        // Placeholder checks
        $active_members = fetchRow("SELECT COUNT(*) as total FROM anggota WHERE status = 'aktif'")['total'];

        return [
            'status' => $active_members > 0 ? 'compliant' : 'warning',
            'members' => $active_members,
            'message' => "Koperasi memiliki {$active_members} anggota aktif"
        ];
    }

    /**
     * Check audit trail completeness.
     */
    private function checkAuditTrailCompleteness() {
        $total_actions = fetchRow("SELECT COUNT(*) as total FROM logs WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")['total'];

        return [
            'status' => $total_actions > 0 ? 'compliant' : 'warning',
            'actions_logged' => $total_actions,
            'message' => "Audit trail lengkap dengan {$total_actions} aktivitas tercatat"
        ];
    }

    /**
     * Check data backup regularity.
     */
    private function checkDataBackupRegularity() {
        $last_backup = fetchRow("SELECT created_at FROM backup_files ORDER BY created_at DESC LIMIT 1")['created_at'];
        $days_since_backup = $last_backup ? (time() - strtotime($last_backup)) / (60 * 60 * 24) : 999;

        return [
            'status' => $days_since_backup <= 7 ? 'compliant' : 'warning',
            'days_since_backup' => round($days_since_backup),
            'message' => $days_since_backup <= 7 ? 'Backup teratur dilakukan' : "Backup terakhir {$days_since_backup} hari yang lalu"
        ];
    }

    /**
     * Calculate member risk score.
     */
    private function calculateMemberRiskScore($member) {
        $score = 0;

        // Risk factors
        if ($member['loan_count'] > 5) $score += 20; // Multiple loans
        if ($member['return_count'] > 2) $score += 30; // Frequent returns
        if ($member['days_since_last_loan'] > 365) $score += 10; // Inactive loan history

        // Status factors
        if ($member['status'] !== 'aktif') $score += 50;

        return min($score, 100);
    }

    /**
     * Get risk level from score.
     */
    private function getRiskLevel($score) {
        if ($score >= 70) return 'high';
        if ($score >= 40) return 'medium';
        return 'low';
    }

    /**
     * Detect fraudulent transactions.
     */
    private function detectFraudulentTransactions() {
        // Placeholder fraud detection logic
        return fetchAll("SELECT p.id, p.total_harga, u.full_name, p.created_at FROM penjualan p LEFT JOIN users u ON p.pelanggan_id = u.id WHERE p.total_harga > 50000000 AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY p.total_harga DESC LIMIT 5");
    }

    /**
     * Detect unusual patterns.
     */
    private function detectUnusualPatterns() {
        // Placeholder for pattern detection
        return [];
    }
}
