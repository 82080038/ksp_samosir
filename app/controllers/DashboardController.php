<?php
/**
 * Dashboard Controller
 * Main dashboard with statistics and overview
 */

require_once __DIR__ . '/BaseController.php';

class DashboardController extends BaseController {
    
    public function index() {
        // requirePermission('view_dashboard'); // DISABLED for development
        
        // Temporarily disable cache
        // $stats = getCache('dashboard_stats');
        // if (!$stats) {
            $stats = $this->getDashboardStats();
        //     setCache('dashboard_stats', $stats);
        // }
        
        // Get recent activities
        $recent_activities = $this->getRecentActivities();
        
        // Get monthly chart data
        $monthly_data = $this->getMonthlyData();
        
        $this->render(__DIR__ . '/../views/dashboard/index.php', [
            'stats' => $stats,
            'recent_activities' => $recent_activities,
            'monthly_data' => $monthly_data
        ]);
    }
    
    private function getDashboardStats() {
        $stats = [];
        
        // Total anggota
        $stats['total_anggota'] = fetchRow("SELECT COUNT(*) as total FROM anggota WHERE status = 'aktif'")['total'];
        
        // Total simpanan
        $stats['total_simpanan'] = fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM simpanan WHERE status='aktif'")['total'];
        
        // Total pinjaman
        $stats['total_pinjaman'] = fetchRow("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total FROM pinjaman WHERE status IN ('disetujui', 'dicairkan')")['total'];
        
        // Total penjualan bulan ini
        $stats['penjualan_bulan'] = fetchRow(
            "SELECT COALESCE(SUM(total_harga), 0) as total FROM penjualan WHERE MONTH(tanggal_penjualan) = MONTH(CURRENT_DATE) AND YEAR(tanggal_penjualan) = YEAR(CURRENT_DATE)"
        )['total'];
        
        // Angsuran terlambat
        $stats['angsuran_terlambat'] = fetchRow(
            "SELECT COUNT(*) as total FROM angsuran WHERE status = 'terlambat'"
        )['total'];
        
        // Produk stok rendah
        $stats['stok_rendah'] = fetchRow(
            "SELECT COUNT(*) as total FROM produk WHERE stok <= stok_minimal AND is_active = 1"
        )['total'];
        
        return $stats;
    }
    
    private function getRecentActivities() {
        return fetchAll(
            "SELECT l.action, l.table_name, l.record_id, l.created_at, u.full_name 
             FROM logs l 
             LEFT JOIN users u ON l.user_id = u.id 
             ORDER BY l.created_at DESC 
             LIMIT 10"
        );
    }
    
    private function getMonthlyData() {
        $data = [];
        
        // Get last 6 months data
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            
            $simpanan = fetchRow(
                "SELECT COALESCE(SUM(jumlah), 0) as total 
                 FROM transaksi_simpanan 
                 WHERE jenis_transaksi = 'setoran' 
                 AND DATE_FORMAT(tanggal_transaksi, '%Y-%m') = ?",
                [$month],
                's'
            )['total'];
            
            $penjualan = fetchRow(
                "SELECT COALESCE(SUM(total_harga), 0) as total 
                 FROM penjualan 
                 WHERE DATE_FORMAT(tanggal_penjualan, '%Y-%m') = ?",
                [$month],
                's'
            )['total'];
            
            $data[] = [
                'month' => date('M Y', strtotime("-$i months")),
                'simpanan' => $simpanan,
                'penjualan' => $penjualan
            ];
        }
        
        return $data;
    }
}
?>
