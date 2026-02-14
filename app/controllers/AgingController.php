<?php
require_once __DIR__ . '/BaseController.php';

/**
 * AgingController handles aging reports for receivables and payables.
 * Provides analysis of outstanding invoices and payments by age.
 */
class AgingController extends BaseController {
    /**
     * Display aging reports dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $receivables_aging = $this->getReceivablesAging();
        $payables_aging = $this->getPayablesAging();

        $this->render(__DIR__ . '/../views/aging/index.php', [
            'receivables_aging' => $receivables_aging,
            'payables_aging' => $payables_aging
        ]);
    }

    /**
     * Accounts receivable aging report.
     */
    public function receivablesAging() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $as_of_date = $_GET['date'] ?? date('Y-m-d');
        $aging_data = $this->getReceivablesAging($as_of_date);

        $this->render(__DIR__ . '/../views/aging/receivables_aging.php', [
            'aging_data' => $aging_data,
            'as_of_date' => $as_of_date
        ]);
    }

    /**
     * Accounts payable aging report.
     */
    public function payablesAging() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $as_of_date = $_GET['date'] ?? date('Y-m-d');
        $aging_data = $this->getPayablesAging($as_of_date);

        $this->render(__DIR__ . '/../views/aging/payables_aging.php', [
            'aging_data' => $aging_data,
            'as_of_date' => $as_of_date
        ]);
    }

    /**
     * Customer aging detail.
     */
    public function customerAgingDetail($customer_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $customer = fetchRow("SELECT * FROM users WHERE id = ?", [$customer_id], 'i');
        $as_of_date = $_GET['date'] ?? date('Y-m-d');

        $invoices = fetchAll("SELECT ci.*, DATEDIFF(?, ci.due_date) as days_overdue FROM customer_invoices ci WHERE ci.customer_id = ? AND ci.status = 'unpaid' ORDER BY ci.due_date ASC", [$as_of_date, $customer_id], 'si');

        // Categorize by aging buckets
        $aging_buckets = [
            'current' => [],
            '1-30_days' => [],
            '31-60_days' => [],
            '61-90_days' => [],
            'over_90_days' => []
        ];

        foreach ($invoices as $invoice) {
            $days = $invoice['days_overdue'];

            if ($days <= 0) {
                $aging_buckets['current'][] = $invoice;
            } elseif ($days <= 30) {
                $aging_buckets['1-30_days'][] = $invoice;
            } elseif ($days <= 60) {
                $aging_buckets['31-60_days'][] = $invoice;
            } elseif ($days <= 90) {
                $aging_buckets['61-90_days'][] = $invoice;
            } else {
                $aging_buckets['over_90_days'][] = $invoice;
            }
        }

        $this->render(__DIR__ . '/../views/aging/customer_aging_detail.php', [
            'customer' => $customer,
            'aging_buckets' => $aging_buckets,
            'as_of_date' => $as_of_date
        ]);
    }

    /**
     * Supplier aging detail.
     */
    public function supplierAgingDetail($supplier_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $supplier = fetchRow("SELECT * FROM suppliers WHERE id = ?", [$supplier_id], 'i');
        $as_of_date = $_GET['date'] ?? date('Y-m-d');

        $invoices = fetchAll("SELECT si.*, DATEDIFF(?, si.tanggal_jatuh_tempo) as days_overdue FROM supplier_invoices si WHERE si.supplier_id = ? AND si.status_pembayaran = 'belum_lunas' ORDER BY si.tanggal_jatuh_tempo ASC", [$as_of_date, $supplier_id], 'si');

        // Categorize by aging buckets
        $aging_buckets = [
            'current' => [],
            '1-30_days' => [],
            '31-60_days' => [],
            '61-90_days' => [],
            'over_90_days' => []
        ];

        foreach ($invoices as $invoice) {
            $days = $invoice['days_overdue'];

            if ($days <= 0) {
                $aging_buckets['current'][] = $invoice;
            } elseif ($days <= 30) {
                $aging_buckets['1-30_days'][] = $invoice;
            } elseif ($days <= 60) {
                $aging_buckets['31-60_days'][] = $invoice;
            } elseif ($days <= 90) {
                $aging_buckets['61-90_days'][] = $invoice;
            } else {
                $aging_buckets['over_90_days'][] = $invoice;
            }
        }

        $this->render(__DIR__ . '/../views/aging/supplier_aging_detail.php', [
            'supplier' => $supplier,
            'aging_buckets' => $aging_buckets,
            'as_of_date' => $as_of_date
        ]);
    }

    /**
     * Export aging report to Excel/CSV.
     */
    public function exportAging($type = 'receivables') {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $as_of_date = $_GET['date'] ?? date('Y-m-d');

        if ($type === 'receivables') {
            $data = $this->getReceivablesAging($as_of_date);
        } else {
            $data = $this->getPayablesAging($as_of_date);
        }

        // Simple CSV export (placeholder - in production use proper CSV library)
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $type . '_aging_' . $as_of_date . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, ['Customer/Supplier', 'Current', '1-30 Days', '31-60 Days', '61-90 Days', 'Over 90 Days', 'Total']);

        // CSV data
        foreach ($data['aging_summary'] as $row) {
            fputcsv($output, [
                $row['name'],
                $row['current'],
                $row['1_30_days'],
                $row['31_60_days'],
                $row['61_90_days'],
                $row['over_90_days'],
                $row['total']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Get receivables aging data.
     */
    private function getReceivablesAging($as_of_date = null) {
        if (!$as_of_date) $as_of_date = date('Y-m-d');

        // Get all unpaid customer invoices
        $invoices = fetchAll("SELECT ci.customer_id, u.full_name as customer_name, ci.total_amount, ci.due_date, DATEDIFF(?, ci.due_date) as days_overdue FROM customer_invoices ci LEFT JOIN users u ON ci.customer_id = u.id WHERE ci.status = 'unpaid'", [$as_of_date], 's');

        // Group by customer and categorize by aging
        $customer_aging = [];
        $aging_totals = [
            'current' => 0,
            '1_30_days' => 0,
            '31_60_days' => 0,
            '61_90_days' => 0,
            'over_90_days' => 0,
            'total' => 0
        ];

        foreach ($invoices as $invoice) {
            $customer_id = $invoice['customer_id'];
            $days = $invoice['days_overdue'];
            $amount = $invoice['total_amount'];

            if (!isset($customer_aging[$customer_id])) {
                $customer_aging[$customer_id] = [
                    'name' => $invoice['customer_name'],
                    'current' => 0,
                    '1_30_days' => 0,
                    '31_60_days' => 0,
                    '61_90_days' => 0,
                    'over_90_days' => 0,
                    'total' => 0
                ];
            }

            if ($days <= 0) {
                $customer_aging[$customer_id]['current'] += $amount;
                $aging_totals['current'] += $amount;
            } elseif ($days <= 30) {
                $customer_aging[$customer_id]['1_30_days'] += $amount;
                $aging_totals['1_30_days'] += $amount;
            } elseif ($days <= 60) {
                $customer_aging[$customer_id]['31_60_days'] += $amount;
                $aging_totals['31_60_days'] += $amount;
            } elseif ($days <= 90) {
                $customer_aging[$customer_id]['61_90_days'] += $amount;
                $aging_totals['61_90_days'] += $amount;
            } else {
                $customer_aging[$customer_id]['over_90_days'] += $amount;
                $aging_totals['over_90_days'] += $amount;
            }

            $customer_aging[$customer_id]['total'] += $amount;
            $aging_totals['total'] += $amount;
        }

        // Convert to indexed array for template
        $aging_summary = array_values($customer_aging);

        return [
            'aging_summary' => $aging_summary,
            'aging_totals' => $aging_totals,
            'total_customers' => count($customer_aging),
            'total_overdue' => $aging_totals['1_30_days'] + $aging_totals['31_60_days'] + $aging_totals['61_90_days'] + $aging_totals['over_90_days']
        ];
    }

    /**
     * Get payables aging data.
     */
    private function getPayablesAging($as_of_date = null) {
        if (!$as_of_date) $as_of_date = date('Y-m-d');

        // Get all unpaid supplier invoices
        $invoices = fetchAll("SELECT si.supplier_id, s.nama_perusahaan as supplier_name, si.total_nilai, si.tanggal_jatuh_tempo, DATEDIFF(?, si.tanggal_jatuh_tempo) as days_overdue FROM supplier_invoices si LEFT JOIN suppliers s ON si.supplier_id = s.id WHERE si.status_pembayaran = 'belum_lunas'", [$as_of_date], 's');

        // Group by supplier and categorize by aging
        $supplier_aging = [];
        $aging_totals = [
            'current' => 0,
            '1_30_days' => 0,
            '31_60_days' => 0,
            '61_90_days' => 0,
            'over_90_days' => 0,
            'total' => 0
        ];

        foreach ($invoices as $invoice) {
            $supplier_id = $invoice['supplier_id'];
            $days = $invoice['days_overdue'];
            $amount = $invoice['total_nilai'];

            if (!isset($supplier_aging[$supplier_id])) {
                $supplier_aging[$supplier_id] = [
                    'name' => $invoice['supplier_name'],
                    'current' => 0,
                    '1_30_days' => 0,
                    '31_60_days' => 0,
                    '61_90_days' => 0,
                    'over_90_days' => 0,
                    'total' => 0
                ];
            }

            if ($days <= 0) {
                $supplier_aging[$supplier_id]['current'] += $amount;
                $aging_totals['current'] += $amount;
            } elseif ($days <= 30) {
                $supplier_aging[$supplier_id]['1_30_days'] += $amount;
                $aging_totals['1_30_days'] += $amount;
            } elseif ($days <= 60) {
                $supplier_aging[$supplier_id]['31_60_days'] += $amount;
                $aging_totals['31_60_days'] += $amount;
            } elseif ($days <= 90) {
                $supplier_aging[$supplier_id]['61_90_days'] += $amount;
                $aging_totals['61_90_days'] += $amount;
            } else {
                $supplier_aging[$supplier_id]['over_90_days'] += $amount;
                $aging_totals['over_90_days'] += $amount;
            }

            $supplier_aging[$supplier_id]['total'] += $amount;
            $aging_totals['total'] += $amount;
        }

        // Convert to indexed array for template
        $aging_summary = array_values($supplier_aging);

        return [
            'aging_summary' => $aging_summary,
            'aging_totals' => $aging_totals,
            'total_suppliers' => count($supplier_aging),
            'total_overdue' => $aging_totals['1_30_days'] + $aging_totals['31_60_days'] + $aging_totals['61_90_days'] + $aging_totals['over_90_days']
        ];
    }
}
