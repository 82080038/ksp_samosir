<?php
require_once __DIR__ . '/BaseController.php';

/**
 * TaxController handles tax calculations, reporting, and compliance.
 * Manages PPh 21, PPh 23, PPh 25, and tax reporting for cooperatives.
 */
class TaxController extends BaseController {
    /**
     * Display tax management dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $stats = $this->getTaxStats();
        $upcoming_deadlines = $this->getUpcomingTaxDeadlines();

        $this->render(__DIR__ . '/../views/tax/index.php', [
            'stats' => $stats,
            'upcoming_deadlines' => $upcoming_deadlines
        ]);
    }

    /**
     * Calculate PPh 21 for employees.
     */
    public function calculatePPh21() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $period = $_GET['period'] ?? date('Y-m');

        // Get all payrolls for the period
        $payrolls = fetchAll("SELECT p.*, e.employee_id, e.full_name, e.position FROM payrolls p LEFT JOIN employees e ON p.employee_id = e.id WHERE p.period = ? AND p.status = 'processed' ORDER BY e.full_name", [$period]);

        // Calculate PPh 21 for each employee
        $pph21_calculations = [];
        foreach ($payrolls as $payroll) {
            $calculation = $this->calculateEmployeePPh21($payroll, $period);
            $pph21_calculations[] = array_merge($payroll, $calculation);
        }

        $this->render(__DIR__ . '/../views/tax/pph21_calculation.php', [
            'pph21_calculations' => $pph21_calculations,
            'period' => $period
        ]);
    }

    /**
     * Calculate PPh 23 for services/third parties.
     */
    public function calculatePPh23() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $period = $_GET['period'] ?? date('Y-m');

        // Get service payments/transactions that require PPh 23
        $service_payments = fetchAll("SELECT sp.*, s.nama_perusahaan as supplier_name FROM service_payments sp LEFT JOIN suppliers s ON sp.supplier_id = s.id WHERE DATE_FORMAT(sp.payment_date, '%Y-%m') = ? ORDER BY sp.payment_date DESC", [$period]);

        // Calculate PPh 23 (2% rate for most services)
        $pph23_calculations = [];
        foreach ($service_payments as $payment) {
            $pph23_amount = $payment['amount'] * 0.02; // 2% PPh 23
            $pph23_calculations[] = array_merge($payment, [
                'pph23_amount' => $pph23_amount,
                'net_amount' => $payment['amount'] - $pph23_amount
            ]);
        }

        $this->render(__DIR__ . '/../views/tax/pph23_calculation.php', [
            'pph23_calculations' => $pph23_calculations,
            'period' => $period
        ]);
    }

    /**
     * Calculate PPh 25 for cooperative income.
     */
    public function calculatePPh25() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $year = $_GET['year'] ?? date('Y');

        // Calculate cooperative taxable income
        $income_data = $this->calculateCooperativeTaxableIncome($year);

        // Calculate PPh 25 (preliminary tax payment)
        $pph25_amount = $income_data['taxable_income'] * 0.25; // 25% corporate tax rate for cooperatives

        $this->render(__DIR__ . '/../views/tax/pph25_calculation.php', [
            'income_data' => $income_data,
            'pph25_amount' => $pph25_amount,
            'year' => $year
        ]);
    }

    /**
     * Generate tax reports.
     */
    public function taxReports() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $report_type = $_GET['type'] ?? 'annual';
        $period = $_GET['period'] ?? date('Y');

        $tax_data = $this->generateTaxReport($report_type, $period);

        $this->render(__DIR__ . '/../views/tax/tax_reports.php', [
            'tax_data' => $tax_data,
            'report_type' => $report_type,
            'period' => $period
        ]);
    }

    /**
     * Tax compliance monitoring.
     */
    public function compliance() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $compliance_checks = [
            'pph21_filing' => $this->checkPPh21Compliance(),
            'pph23_filing' => $this->checkPPh23Compliance(),
            'pph25_payment' => $this->checkPPh25Compliance(),
            'annual_tax_return' => $this->checkAnnualTaxReturn(),
            'withholding_tax' => $this->checkWithholdingTaxCompliance()
        ];

        $this->render(__DIR__ . '/../views/tax/compliance.php', [
            'compliance_checks' => $compliance_checks
        ]);
    }

    /**
     * File tax returns (placeholder for integration with tax authorities).
     */
    public function fileTaxReturn() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $tax_type = $_POST['tax_type'] ?? '';
        $period = $_POST['period'] ?? '';
        $tax_amount = floatval($_POST['tax_amount'] ?? 0);

        // In production, this would integrate with DJP (Direktorat Jenderal Pajak) API
        runInTransaction(function($conn) use ($tax_type, $period, $tax_amount) {
            $stmt = $conn->prepare("INSERT INTO tax_filings (tax_type, period, tax_amount, filing_date, status, filed_by, created_at) VALUES (?, ?, ?, CURDATE(), 'filed', ?, NOW())");
            $filed_by = $_SESSION['user']['id'] ?? 1;
            $stmt->bind_param('ssdii', $tax_type, $period, $tax_amount, $filed_by);
            $stmt->execute();
            $stmt->close();
        });

        flashMessage('success', 'Pelaporan pajak berhasil diajukan (placeholder untuk integrasi DJP)');
        redirect('tax/compliance');
    }

    /**
     * Calculate PPh 21 for a single employee.
     */
    private function calculateEmployeePPh21($payroll, $period) {
        $annual_salary = $payroll['gross_salary'] * 12;
        $biaya_jabatan = min($annual_salary * 0.05, 6000000); // 5% of salary or max 6M IDR
        $iuran_pensiun = $annual_salary * 0.0475; // 4.75% pension contribution

        $taxable_income = $annual_salary - $biaya_jabatan - $iuran_pensiun - 54000000; // PTKP for single employee

        $tax = 0;
        if ($taxable_income > 0) {
            if ($taxable_income <= 50000000) {
                $tax = $taxable_income * 0.05;
            } elseif ($taxable_income <= 250000000) {
                $tax = 50000000 * 0.05 + ($taxable_income - 50000000) * 0.15;
            } elseif ($taxable_income <= 500000000) {
                $tax = 50000000 * 0.05 + 200000000 * 0.15 + ($taxable_income - 250000000) * 0.25;
            } else {
                $tax = 50000000 * 0.05 + 200000000 * 0.15 + 250000000 * 0.25 + ($taxable_income - 500000000) * 0.30;
            }
        }

        $monthly_tax = $tax / 12;

        return [
            'annual_salary' => $annual_salary,
            'biaya_jabatan' => $biaya_jabatan,
            'iuran_pensiun' => $iuran_pensiun,
            'taxable_income' => max(0, $taxable_income),
            'annual_tax' => max(0, $tax),
            'monthly_tax' => max(0, $monthly_tax),
            'effective_tax_rate' => $annual_salary > 0 ? ($tax / $annual_salary) * 100 : 0
        ];
    }

    /**
     * Calculate cooperative taxable income.
     */
    private function calculateCooperativeTaxableIncome($year) {
        // Revenue
        $sales_revenue = fetchRow("SELECT COALESCE(SUM(total_harga), 0) as total FROM penjualan WHERE status_pembayaran='lunas' AND YEAR(tanggal_penjualan) = ?", [$year], 'i')['total'];
        $interest_income = fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi_simpanan WHERE jenis_transaksi='bunga' AND YEAR(tanggal_transaksi) = ?", [$year], 'i')['total'];

        $total_revenue = $sales_revenue + $interest_income;

        // Expenses
        $salary_expenses = fetchRow("SELECT COALESCE(SUM(net_salary), 0) as total FROM payrolls WHERE YEAR(processed_at) = ?", [$year], 'i')['total'];
        $operational_costs = fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM operational_costs WHERE YEAR(tanggal) = ?", [$year], 'i')['total'];
        $interest_expenses = fetchRow("SELECT COALESCE(SUM(bunga), 0) as total FROM angsuran WHERE YEAR(tanggal_bayar) = ?", [$year], 'i')['total'];

        $total_expenses = $salary_expenses + $operational_costs + $interest_expenses;

        // Taxable income
        $taxable_income = $total_revenue - $total_expenses;

        return [
            'year' => $year,
            'sales_revenue' => $sales_revenue,
            'interest_income' => $interest_income,
            'total_revenue' => $total_revenue,
            'salary_expenses' => $salary_expenses,
            'operational_costs' => $operational_costs,
            'interest_expenses' => $interest_expenses,
            'total_expenses' => $total_expenses,
            'taxable_income' => max(0, $taxable_income)
        ];
    }

    /**
     * Get tax statistics.
     */
    private function getTaxStats() {
        $stats = [];

        $current_year = date('Y');
        $stats['total_tax_paid_year'] = fetchRow("SELECT COALESCE(SUM(tax_amount), 0) as total FROM tax_filings WHERE YEAR(filing_date) = ?", [$current_year], 'i')['total'];
        $stats['pending_tax_filings'] = fetchRow("SELECT COUNT(*) as total FROM tax_filings WHERE status = 'pending'")['total'];
        $stats['employees_with_tax'] = fetchRow("SELECT COUNT(DISTINCT employee_id) as total FROM payrolls WHERE tax > 0 AND YEAR(processed_at) = ?", [$current_year], 'i')['total'];
        $stats['tax_compliance_rate'] = $this->calculateTaxComplianceRate();

        return $stats;
    }

    /**
     * Get upcoming tax deadlines.
     */
    private function getUpcomingTaxDeadlines() {
        $deadlines = [];

        $current_month = date('n');
        $current_year = date('Y');

        // SPT Tahunan deadline: April 30
        if ($current_month <= 4) {
            $deadlines[] = [
                'type' => 'SPT Tahunan PPh Badan',
                'deadline' => date('Y-04-30'),
                'description' => 'Pelaporan SPT Tahunan untuk tahun ' . ($current_year - 1)
            ];
        }

        // Monthly PPh 23 reporting
        $next_month = date('Y-m-d', strtotime('+1 month'));
        $deadlines[] = [
            'type' => 'PPh 23 Bulanan',
            'deadline' => date('Y-m-20', strtotime($next_month)),
            'description' => 'Pelaporan PPh 23 untuk bulan ' . date('M Y')
        ];

        // PPh 21 reporting
        $deadlines[] = [
            'type' => 'PPh 21 Bulanan',
            'deadline' => date('Y-m-20', strtotime('+1 month')),
            'description' => 'Pelaporan PPh 21 untuk bulan ' . date('M Y')
        ];

        return array_filter($deadlines, function($deadline) {
            return strtotime($deadline['deadline']) >= time();
        });
    }

    /**
     * Generate tax report data.
     */
    private function generateTaxReport($type, $period) {
        $data = [];

        switch ($type) {
            case 'annual':
                $data = $this->calculateCooperativeTaxableIncome($period);
                break;

            case 'monthly':
                $data['pph21'] = $this->getMonthlyPPh21Data($period);
                $data['pph23'] = $this->getMonthlyPPh23Data($period);
                break;

            case 'compliance':
                $data = $this->getComplianceReportData($period);
                break;
        }

        return $data;
    }

    /**
     * Check PPh 21 compliance.
     */
    private function checkPPh21Compliance() {
        $last_filing = fetchRow("SELECT filing_date FROM tax_filings WHERE tax_type = 'pph21' ORDER BY filing_date DESC LIMIT 1");
        $days_since_filing = $last_filing ? (time() - strtotime($last_filing['filing_date'])) / (60 * 60 * 24) : 999;

        return [
            'status' => $days_since_filing <= 31 ? 'compliant' : 'warning',
            'last_filing' => $last_filing['filing_date'] ?? null,
            'days_overdue' => max(0, $days_since_filing - 31)
        ];
    }

    /**
     * Check PPh 23 compliance.
     */
    private function checkPPh23Compliance() {
        $last_filing = fetchRow("SELECT filing_date FROM tax_filings WHERE tax_type = 'pph23' ORDER BY filing_date DESC LIMIT 1");
        $days_since_filing = $last_filing ? (time() - strtotime($last_filing['filing_date'])) / (60 * 60 * 24) : 999;

        return [
            'status' => $days_since_filing <= 31 ? 'compliant' : 'warning',
            'last_filing' => $last_filing['filing_date'] ?? null,
            'days_overdue' => max(0, $days_since_filing - 31)
        ];
    }

    /**
     * Check PPh 25 compliance.
     */
    private function checkPPh25Compliance() {
        $last_payment = fetchRow("SELECT payment_date FROM tax_payments WHERE tax_type = 'pph25' ORDER BY payment_date DESC LIMIT 1");
        $days_since_payment = $last_payment ? (time() - strtotime($last_payment['payment_date'])) / (60 * 60 * 24) : 999;

        return [
            'status' => $days_since_payment <= 31 ? 'compliant' : 'warning',
            'last_payment' => $last_payment['payment_date'] ?? null,
            'days_overdue' => max(0, $days_since_payment - 31)
        ];
    }

    /**
     * Check annual tax return.
     */
    private function checkAnnualTaxReturn() {
        $current_year = date('Y');
        $last_return = fetchRow("SELECT filing_date FROM tax_filings WHERE tax_type = 'annual_return' AND YEAR(filing_date) = ?", [$current_year - 1], 'i');

        return [
            'status' => $last_return ? 'compliant' : 'warning',
            'last_return' => $last_return['filing_date'] ?? null,
            'year' => $current_year - 1
        ];
    }

    /**
     * Check withholding tax compliance.
     */
    private function checkWithholdingTaxCompliance() {
        $unreported_withholding = fetchRow("SELECT COUNT(*) as total FROM withholding_tax WHERE reported = 0 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)")['total'];

        return [
            'status' => $unreported_withholding == 0 ? 'compliant' : 'warning',
            'unreported_count' => $unreported_withholding
        ];
    }

    /**
     * Calculate tax compliance rate.
     */
    private function calculateTaxComplianceRate() {
        $total_required_filings = 12; // Monthly filings for a year
        $completed_filings = fetchRow("SELECT COUNT(*) as total FROM tax_filings WHERE YEAR(filing_date) = YEAR(CURDATE())")['total'];

        return $total_required_filings > 0 ? min(100, ($completed_filings / $total_required_filings) * 100) : 0;
    }

    /**
     * Get monthly PPh 21 data.
     */
    private function getMonthlyPPh21Data($period) {
        return fetchAll("SELECT SUM(tax) as total_pph21, COUNT(*) as employee_count FROM payrolls WHERE period = ? AND status = 'processed'", [$period]);
    }

    /**
     * Get monthly PPh 23 data.
     */
    private function getMonthlyPPh23Data($period) {
        return fetchAll("SELECT SUM(amount * 0.02) as total_pph23, COUNT(*) as transaction_count FROM service_payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = ?", [$period]);
    }

    /**
     * Get compliance report data.
     */
    private function getComplianceReportData($period) {
        return [
            'pph21_filings' => fetchRow("SELECT COUNT(*) as total FROM tax_filings WHERE tax_type = 'pph21' AND period = ?", [$period], 's')['total'],
            'pph23_filings' => fetchRow("SELECT COUNT(*) as total FROM tax_filings WHERE tax_type = 'pph23' AND period = ?", [$period], 's')['total'],
            'pph25_payments' => fetchRow("SELECT COUNT(*) as total FROM tax_payments WHERE tax_type = 'pph25' AND YEAR(payment_date) = ?", [$period], 'i')['total']
        ];
    }
}
