<?php
/**
 * Dashboard Controller
 * Main dashboard with statistics and overview
 */

require_once __DIR__ . '/BaseController.php';

class DashboardController extends BaseController {
    
    public function index() {
        // requirePermission('view_dashboard'); // DISABLED for development
        
        // Get comprehensive dashboard statistics
        $stats = $this->getEnhancedDashboardStats();
        
        // Get recent activities with enhanced data
        $recent_activities = $this->getEnhancedRecentActivities();
        
        // Get monthly chart data with all financial data
        $monthly_data = $this->getEnhancedMonthlyData();
        
        // Get People DB statistics
        $people_stats = $this->getPeopleDBStats();
        
        // Merge all statistics
        $stats = array_merge($stats, $people_stats);
        
        $this->render('dashboard/enhanced', [
            'stats' => $stats,
            'recent_activities' => $recent_activities,
            'monthly_data' => $monthly_data
        ]);
    }
    
    private function getEnhancedDashboardStats() {
        $stats = [];

        try {
            // Total anggota with active count
            $stats['total_anggota'] = fetchRow("SELECT COUNT(*) as total FROM anggota")['total'] ?? 0;
            $stats['anggota_aktif'] = fetchRow("SELECT COUNT(*) as total FROM anggota WHERE status = 'aktif'")['total'] ?? 0;
        } catch (Exception $e) {
            $stats['total_anggota'] = 0;
            $stats['anggota_aktif'] = 0;
        }

        try {
            // Total simpanan with monthly growth
            $stats['total_simpanan'] = fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi_simpanan WHERE jenis_transaksi = 'setoran'")['total'] ?? 0;
            $stats['simpanan_bulan_ini'] = fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi_simpanan WHERE jenis_transaksi = 'setoran' AND MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)")['total'] ?? 0;
        } catch (Exception $e) {
            $stats['total_simpanan'] = 0;
            $stats['simpanan_bulan_ini'] = 0;
        }

        try {
            // Total pinjaman with active count
            $stats['total_pinjaman'] = fetchRow("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total FROM pinjaman WHERE status != 'lunas'")['total'] ?? 0;
            $stats['pinjaman_aktif'] = fetchRow("SELECT COUNT(*) as total FROM pinjaman WHERE status = 'aktif'")['total'] ?? 0;
        } catch (Exception $e) {
            $stats['total_pinjaman'] = 0;
            $stats['pinjaman_aktif'] = 0;
        }

        try {
            // Total SHU (Sisa Hasil Usaha)
            $stats['total_shu'] = fetchRow("SELECT COALESCE(SUM(total_shu), 0) as total FROM shu WHERE tahun = YEAR(CURRENT_DATE)")['total'] ?? 0;
        } catch (Exception $e) {
            $stats['total_shu'] = 0;
        }

        try {
            // Late payments
            $stats['angsuran_terlambat'] = fetchRow("SELECT COUNT(DISTINCT pinjaman_id) as total FROM angsuran WHERE jatuh_tempo < CURDATE() AND status = 'belum_bayar'")['total'] ?? 0;
        } catch (Exception $e) {
            $stats['angsuran_terlambat'] = 0;
        }

        try {
            // Bad loans
            $stats['pinjaman_macet'] = fetchRow("SELECT COUNT(*) as total FROM pinjaman WHERE status = 'macet' OR (DATEDIFF(CURDATE(), jatuh_tempo) > 90 AND status != 'lunas')")['total'] ?? 0;
        } catch (Exception $e) {
            $stats['pinjaman_macet'] = 0;
        }

        return $stats;
    }

    private function getPeopleDBStats() {
        $stats = [];
        
        try {
            // Get People DB statistics via API
            if (function_exists('base_url')) {
                $people_api_url = base_url('api/people.php?endpoint=statistics');
            } else {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
                if ($scriptDir === '/' || $scriptDir === '.') {
                    $scriptDir = '';
                }
                $people_api_url = rtrim($scheme . '://' . $host . $scriptDir, '/') . '/api/people.php?endpoint=statistics';
            }
            $response = @file_get_contents($people_api_url);
            if ($response) {
                $data = json_decode($response, true);
                if ($data && $data['success']) {
                    $stats['people_db_count'] = $data['data']['total_users'] ?? 0;
                    $stats['people_db_active'] = $data['data']['active_users'] ?? 0;
                }
            }
        } catch (Exception $e) {
            $stats['people_db_count'] = 0;
            $stats['people_db_active'] = 0;
        }
        
        return $stats;
    }

    private function getEnhancedRecentActivities() {
        $activities = [];
        
        try {
            // Get recent anggota registrations
            $anggota = fetchAll("SELECT id, nama_lengkap, created_at FROM anggota ORDER BY created_at DESC LIMIT 3");
            foreach ($anggota as $a) {
                $activities[] = [
                    'type' => 'anggota',
                    'title' => 'Anggota Baru',
                    'description' => $a['nama_lengkap'] . ' mendaftar sebagai anggota',
                    'created_at' => $a['created_at']
                ];
            }
            
            // Get recent simpanan transactions
            $simpanan = fetchAll("SELECT id, jumlah, anggota_id, created_at FROM transaksi_simpanan ORDER BY created_at DESC LIMIT 2");
            foreach ($simpanan as $s) {
                $member = fetchRow("SELECT nama_lengkap FROM anggota WHERE id = ?", [$s['anggota_id']]);
                $activities[] = [
                    'type' => 'simpanan',
                    'title' => 'Simpanan Masuk',
                    'description' => 'Rp ' . number_format($s['jumlah']) . ' dari ' . ($member['nama_lengkap'] ?? 'Anggota'),
                    'created_at' => $s['created_at']
                ];
            }
            
            // Get recent pinjaman approvals
            $pinjaman = fetchAll("SELECT id, jumlah_pinjaman, anggota_id, created_at FROM pinjaman ORDER BY created_at DESC LIMIT 2");
            foreach ($pinjaman as $p) {
                $member = fetchRow("SELECT nama_lengkap FROM anggota WHERE id = ?", [$p['anggota_id']]);
                $activities[] = [
                    'type' => 'pinjaman',
                    'title' => 'Pinjaman Disetujui',
                    'description' => 'Rp ' . number_format($p['jumlah_pinjaman']) . ' untuk ' . ($member['nama_lengkap'] ?? 'Anggota'),
                    'created_at' => $p['created_at']
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error getting recent activities: " . $e->getMessage());
        }
        
        // Sort by date
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 10);
    }

    private function getEnhancedMonthlyData() {
        $data = [];
        
        try {
            // Get last 6 months data
            for ($i = 5; $i >= 0; $i--) {
                $date = date('Y-m', strtotime("-$i months"));
                $month_name = date('M Y', strtotime("-$i months"));
                
                // Simpanan data
                $simpanan = fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi_simpanan WHERE DATE_FORMAT(created_at, '%Y-%m') = ? AND jenis_transaksi = 'setoran'", [$date])['total'] ?? 0;
                
                // Pinjaman data  
                $pinjaman = fetchRow("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total FROM pinjaman WHERE DATE_FORMAT(created_at, '%Y-%m') = ?", [$date])['total'] ?? 0;
                
                // Penjualan (if exists)
                $penjualan = fetchRow("SELECT COALESCE(SUM(total), 0) as total FROM penjualan WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?", [$date])['total'] ?? 0;
                
                $data[] = [
                    'month' => $month_name,
                    'simpanan' => (float)$simpanan,
                    'pinjaman' => (float)$pinjaman,
                    'penjualan' => (float)$penjualan
                ];
            }
        } catch (Exception $e) {
            error_log("Error getting monthly data: " . $e->getMessage());
            
            // Return empty data for last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $month_name = date('M Y', strtotime("-$i months"));
                $data[] = [
                    'month' => $month_name,
                    'simpanan' => 0,
                    'pinjaman' => 0,
                    'penjualan' => 0
                ];
            }
        }
        
        return $data;
    }
    
    // Legacy methods for backward compatibility
    private function getDashboardStats() {
        return $this->getEnhancedDashboardStats();
    }
    
    private function getRecentActivities() {
        return $this->getEnhancedRecentActivities();
    }
    
    private function getMonthlyData() {
        return $this->getEnhancedMonthlyData();
    }

    public function spa() {
        // SPA version of dashboard
        $stats = $this->getEnhancedDashboardStats();
        $recent_activities = $this->getEnhancedRecentActivities();
        $monthly_data = $this->getEnhancedMonthlyData();
        $people_stats = $this->getPeopleDBStats();
        $stats = array_merge($stats, $people_stats);
        
        $this->renderSPA('dashboard/enhanced', [
            'stats' => $stats,
            'recent_activities' => $recent_activities,
            'monthly_data' => $monthly_data
        ]);
    }
}
?>
