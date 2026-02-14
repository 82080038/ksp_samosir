<?php
require_once __DIR__ . '/BaseController.php';

class LaporanController extends BaseController {
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $stats = $this->getReportStats();
        $recent_reports = $this->getRecentReports();

        $this->render(__DIR__ . '/../views/laporan/index.php', [
            'stats' => $stats,
            'recent_reports' => $recent_reports
        ]);
    }

    public function analytics() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        // Get chart data
        $sales_chart = $this->getSalesChartData();
        $member_chart = $this->getMemberChartData();
        $finance_chart = $this->getFinanceChartData();

        $this->render(__DIR__ . '/../views/laporan/analytics.php', [
            'sales_chart' => $sales_chart,
            'member_chart' => $member_chart,
            'finance_chart' => $finance_chart
        ]);
    }

    public function customReport() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $report_type = sanitize($_POST['report_type']);
            $start_date = sanitize($_POST['start_date']);
            $end_date = sanitize($_POST['end_date']);
            $filters = $_POST['filters'] ?? [];

            $data = $this->generateCustomReport($report_type, $start_date, $end_date, $filters);

            $this->render(__DIR__ . '/../views/laporan/custom_report_result.php', [
                'report_type' => $report_type,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'data' => $data
            ]);
        } else {
            $this->render(__DIR__ . '/../views/laporan/custom_report.php');
        }
    }

    public function simpanan() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $period = $_GET['period'] ?? 'all';
        $query = "SELECT js.nama_simpanan, SUM(s.saldo) as total_saldo, COUNT(s.id) as jumlah_rekening FROM simpanan s LEFT JOIN jenis_simpanan js ON s.jenis_simpanan_id=js.id WHERE s.status='aktif'";

        if ($period === 'month') {
            $query .= " AND MONTH(s.created_at) = MONTH(CURRENT_DATE) AND YEAR(s.created_at) = YEAR(CURRENT_DATE)";
        } elseif ($period === 'year') {
            $query .= " AND YEAR(s.created_at) = YEAR(CURRENT_DATE)";
        }

        $query .= " GROUP BY js.id ORDER BY total_saldo DESC";
        $laporan = fetchAll($query);

        if (isset($_GET['format']) && $_GET['format'] == 'pdf') {
            // Stub: Implement PDF generation
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="laporan_simpanan.pdf"');
            echo 'PDF Stub: ' . json_encode($laporan);
            exit;
        }

        if (isset($_GET['format']) && $_GET['format'] == 'excel') {
            // Stub: Implement Excel generation
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="laporan_simpanan.xls"');
            echo 'Excel Stub: ' . json_encode($laporan);
            exit;
        }

        $this->render(__DIR__ . '/../views/laporan/simpanan.php', [
            'laporan' => $laporan,
            'period' => $period
        ]);
    }

    public function pinjaman() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $status_filter = $_GET['status'] ?? 'all';
        $query = "SELECT jp.nama_pinjaman, SUM(p.jumlah_pinjaman) as total_pinjaman, COUNT(p.id) as jumlah_pinjaman, AVG(p.bunga_persen) as avg_bunga FROM pinjaman p LEFT JOIN jenis_pinjaman jp ON p.jenis_pinjaman_id=jp.id WHERE 1=1";

        if ($status_filter === 'active') {
            $query .= " AND p.status IN ('disetujui', 'dicairkan')";
        } elseif ($status_filter === 'completed') {
            $query .= " AND p.status = 'lunas'";
        }

        $query .= " GROUP BY jp.id ORDER BY total_pinjaman DESC";
        $laporan = fetchAll($query);

        $this->render(__DIR__ . '/../views/laporan/pinjaman.php', [
            'laporan' => $laporan,
            'status_filter' => $status_filter
        ]);
    }

    public function penjualan() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $period = $_GET['period'] ?? 'month';
        $query = "SELECT DATE(tanggal_penjualan) as tanggal, SUM(total_harga) as total_penjualan, COUNT(id) as jumlah_transaksi FROM penjualan WHERE status_pembayaran='lunas'";

        if ($period === 'month') {
            $query .= " AND MONTH(tanggal_penjualan) = MONTH(CURRENT_DATE) AND YEAR(tanggal_penjualan) = YEAR(CURRENT_DATE)";
        } elseif ($period === 'year') {
            $query .= " AND YEAR(tanggal_penjualan) = YEAR(CURRENT_DATE)";
        }

        $query .= " GROUP BY DATE(tanggal_penjualan) ORDER BY tanggal DESC";
        $laporan = fetchAll($query);

        $this->render(__DIR__ . '/../views/laporan/penjualan.php', [
            'laporan' => $laporan,
            'period' => $period
        ]);
    }

    public function neraca() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $as_of_date = $_GET['date'] ?? date('Y-m-d');

        // Calculate assets
        $kas_bank = fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM coa WHERE kode_coa LIKE '111%'")['total'];
        $simpanan = fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM simpanan WHERE status='aktif'")['total'];
        $piutang_pinjaman = fetchRow("SELECT COALESCE(SUM(jumlah_pinjaman - COALESCE((SELECT SUM(pokok) FROM angsuran WHERE pinjaman_id = p.id), 0)), 0) as total FROM pinjaman p WHERE p.status IN ('disetujui', 'dicairkan')")['total'];

        $total_aktiva = $kas_bank + $simpanan + $piutang_pinjaman;

        // Calculate liabilities & equity
        $simpanan_anggota = $simpanan; // Simpanan anggota sebagai liability
        $pinjaman_diterima = fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM coa WHERE kode_coa LIKE '21%'")['total'];
        $modal = fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM coa WHERE kode_coa LIKE '31%'")['total'];
        $shu = fetchRow("SELECT COALESCE(SUM(shu_anggota), 0) as total FROM profit_distributions")['total'];

        $total_passiva = $simpanan_anggota + $pinjaman_diterima + $modal + $shu;

        $this->render(__DIR__ . '/../views/laporan/neraca.php', [
            'as_of_date' => $as_of_date,
            'kas_bank' => $kas_bank,
            'simpanan' => $simpanan,
            'piutang_pinjaman' => $piutang_pinjaman,
            'total_aktiva' => $total_aktiva,
            'simpanan_anggota' => $simpanan_anggota,
            'pinjaman_diterima' => $pinjaman_diterima,
            'modal' => $modal,
            'shu' => $shu,
            'total_passiva' => $total_passiva
        ]);
    }

    public function labaRugi() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $period = $_GET['period'] ?? 'month';
        $start_date = $end_date = null;

        if ($period === 'month') {
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
        } elseif ($period === 'year') {
            $start_date = date('Y-01-01');
            $end_date = date('Y-12-31');
        } elseif ($period === 'custom') {
            $start_date = $_GET['start_date'] ?? date('Y-m-01');
            $end_date = $_GET['end_date'] ?? date('Y-m-t');
        }

        // Revenue
        $penjualan = fetchRow("SELECT COALESCE(SUM(total_harga), 0) as total FROM penjualan WHERE status_pembayaran='lunas' AND DATE(tanggal_penjualan) BETWEEN ? AND ?", [$start_date, $end_date], 'ss')['total'];
        $bunga_simpanan = fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi_simpanan WHERE jenis_transaksi='bunga' AND DATE(tanggal_transaksi) BETWEEN ? AND ?", [$start_date, $end_date], 'ss')['total'];
        $total_pendapatan = $penjualan + $bunga_simpanan;

        // Expenses
        $beban_bunga_pinjaman = fetchRow("SELECT COALESCE(SUM(bunga), 0) as total FROM angsuran WHERE DATE(tanggal_bayar) BETWEEN ? AND ?", [$start_date, $end_date], 'ss')['total'];
        $beban_operasional = fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM operational_costs WHERE DATE(tanggal) BETWEEN ? AND ?", [$start_date, $end_date], 'ss')['total'];
        $total_beban = $beban_bunga_pinjaman + $beban_operasional;

        $laba_bersih = $total_pendapatan - $total_beban;

        $this->render(__DIR__ . '/../views/laporan/laba_rugi.php', [
            'period' => $period,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'penjualan' => $penjualan,
            'bunga_simpanan' => $bunga_simpanan,
            'total_pendapatan' => $total_pendapatan,
            'beban_bunga_pinjaman' => $beban_bunga_pinjaman,
            'beban_operasional' => $beban_operasional,
            'total_beban' => $total_beban,
            'laba_bersih' => $laba_bersih
        ]);
    }

    private function getReportStats() {
        $stats = [];

        $stats['total_reports_generated'] = fetchRow("SELECT COUNT(*) as total FROM reports")['total'];
        $stats['reports_this_month'] = fetchRow("SELECT COUNT(*) as total FROM reports WHERE MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)")['total'];

        // Most requested reports
        $stats['popular_reports'] = fetchAll("SELECT jenis_laporan, COUNT(*) as count FROM reports GROUP BY jenis_laporan ORDER BY count DESC LIMIT 3");

        return $stats;
    }

    private function getRecentReports() {
        return fetchAll("SELECT * FROM reports ORDER BY created_at DESC LIMIT 5");
    }

    private function getSalesChartData() {
        // Monthly sales data for last 12 months
        $data = fetchAll("SELECT DATE_FORMAT(tanggal_penjualan, '%Y-%m') as month, SUM(total_harga) as total FROM penjualan WHERE status_pembayaran='lunas' AND tanggal_penjualan >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH) GROUP BY DATE_FORMAT(tanggal_penjualan, '%Y-%m') ORDER BY month");

        return [
            'labels' => array_column($data, 'month'),
            'data' => array_column($data, 'total')
        ];
    }

    private function getMemberChartData() {
        // Member growth over time
        $data = fetchAll("SELECT DATE_FORMAT(tanggal_gabung, '%Y-%m') as month, COUNT(*) as count FROM anggota WHERE tanggal_gabung >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH) GROUP BY DATE_FORMAT(tanggal_gabung, '%Y-%m') ORDER BY month");

        return [
            'labels' => array_column($data, 'month'),
            'data' => array_column($data, 'count')
        ];
    }

    private function getFinanceChartData() {
        // Savings vs Loans over time
        $savings = fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(saldo) as total FROM simpanan WHERE status='aktif' AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month");
        $loans = fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(jumlah_pinjaman) as total FROM pinjaman WHERE status IN ('disetujui', 'dicairkan') AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month");

        return [
            'labels' => array_column($savings, 'month'),
            'savings' => array_column($savings, 'total'),
            'loans' => array_column($loans, 'total')
        ];
    }

    private function generateCustomReport($type, $start_date, $end_date, $filters) {
        $data = [];

        switch ($type) {
            case 'sales_summary':
                $data = fetchAll("SELECT DATE(tanggal_penjualan) as date, COUNT(*) as transactions, SUM(total_harga) as revenue FROM penjualan WHERE status_pembayaran='lunas' AND DATE(tanggal_penjualan) BETWEEN ? AND ? GROUP BY DATE(tanggal_penjualan) ORDER BY date", [$start_date, $end_date], 'ss');
                break;

            case 'member_activity':
                $data = fetchAll("
                    SELECT 
                        a.nama_lengkap, 
                        COUNT(s.id) as savings_accounts, 
                        COUNT(p.id) as loans 
                    FROM anggota a 
                    LEFT JOIN simpanan s ON a.id = s.anggota_id AND s.status='aktif' 
                    LEFT JOIN pinjaman p ON a.id = p.anggota_id AND p.status IN ('disetujui', 'dicairkan') 
                    WHERE a.status='aktif' 
                    GROUP BY a.id 
                    ORDER BY a.nama_lengkap
                ");
                break;

            case 'financial_overview':
                $data = [
                    'total_savings' => fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM simpanan WHERE status='aktif'")['total'],
                    'total_loans' => fetchRow("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total FROM pinjaman WHERE status IN ('disetujui', 'dicairkan')")['total'],
                    'total_sales' => fetchRow("SELECT COALESCE(SUM(total_harga), 0) as total FROM penjualan WHERE status_pembayaran='lunas' AND DATE(tanggal_penjualan) BETWEEN ? AND ?", [$start_date, $end_date], 'ss')['total'],
                    'total_shu' => fetchRow("SELECT COALESCE(SUM(shu_anggota), 0) as total FROM profit_distributions")['total']
                ];
                break;

            case 'product_performance':
                $data = fetchAll("SELECT pr.nama_produk, SUM(dp.jumlah) as total_sold, SUM(dp.subtotal) as revenue FROM detail_penjualan dp LEFT JOIN produk pr ON dp.produk_id = pr.id LEFT JOIN penjualan p ON dp.penjualan_id = p.id WHERE p.status_pembayaran='lunas' AND DATE(p.tanggal_penjualan) BETWEEN ? AND ? GROUP BY pr.id ORDER BY revenue DESC", [$start_date, $end_date], 'ss');
                break;
        }

        return $data;
    }
}
