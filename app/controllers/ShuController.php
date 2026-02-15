<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/config.php';

/**
 * ShuController handles SHU (Sisa Hasil Usaha) calculation and distribution.
 * Manages profit sharing for cooperative members and investors.
 */
class ShuController extends BaseController {
    /**
     * Display SHU management dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $stats = $this->getShuStats();
        $recent_distributions = $this->getRecentDistributions();

        $this->render('shu/index', [
            'stats' => $stats,
            'recent_distributions' => $recent_distributions
        ]);
    }

    /**
     * Calculate SHU for a given period.
     */
    public function calculate() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $periode_start = sanitize($_POST['periode_start'] ?? '');
            $periode_end = sanitize($_POST['periode_end'] ?? '');

            if (empty($periode_start) || empty($periode_end)) {
                flashMessage('error', 'Periode mulai dan akhir harus diisi');
                redirect('shu/calculate');
            }

            // Call stored procedure to calculate SHU
            $conn = getLegacyConnection();
            $stmt = $conn->prepare("CALL calculate_shu(?, ?)");
            $stmt->bind_param('ss', $periode_start, $periode_end);

            try {
                $stmt->execute();
                flashMessage('success', 'SHU berhasil dihitung untuk periode ' . $periode_start . ' sampai ' . $periode_end);
                redirect('shu');
            } catch (Exception $e) {
                flashMessage('error', 'Gagal menghitung SHU: ' . $e->getMessage());
                redirect('shu/calculate');
            } finally {
                $stmt->close();
                $conn->close();
            }
        }

        $this->render('shu/calculate');
    }

    /**
     * Distribute calculated SHU to members and investors.
     */
    public function distribute() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM profit_distributions") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $distributions = fetchAll("SELECT pd.*, u.full_name as approved_by_name FROM profit_distributions pd LEFT JOIN users u ON pd.approved_by = u.id ORDER BY pd.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('shu/distribute', [
            'distributions' => $distributions,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Create new profit distribution.
     */
    public function createDistribution() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $periode = sanitize($_POST['periode'] ?? '');
            $tanggal_distribusi = sanitize($_POST['tanggal_distribusi'] ?? '');
            $total_keuntungan = floatval($_POST['total_keuntungan'] ?? 0);
            $shu_anggota = floatval($_POST['shu_anggota'] ?? 0);
            $dividen_investor = floatval($_POST['dividen_investor'] ?? 0);
            $cadangan_koperasi = floatval($_POST['cadangan_koperasi'] ?? 0);

            if (empty($periode) || empty($tanggal_distribusi) || $total_keuntungan <= 0) {
                flashMessage('error', 'Data distribusi tidak lengkap');
                redirect('shu/createDistribution');
            }

            $approved_by = $_SESSION['user']['id'] ?? null;

            runInTransaction(function($conn) use ($periode, $tanggal_distribusi, $total_keuntungan, $shu_anggota, $dividen_investor, $cadangan_koperasi, $approved_by) {
                $stmt = $conn->prepare("INSERT INTO profit_distributions (periode, tanggal_distribusi, total_keuntungan, shu_anggota, dividen_investor, cadangan_koperasi, status, approved_by) VALUES (?, ?, ?, ?, ?, ?, 'approved', ?)");
                $stmt->bind_param('ssddddi', $periode, $tanggal_distribusi, $total_keuntungan, $shu_anggota, $dividen_investor, $cadangan_koperasi, $approved_by);
                $stmt->execute();
                $distribution_id = $stmt->insert_id;
                $stmt->close();

                // Distribute to members (simplified - in real implementation, calculate per member)
                // For now, create placeholder entries
                $members = fetchAll("SELECT id FROM anggota WHERE status = 'aktif'");
                $shu_per_member = count($members) > 0 ? $shu_anggota / count($members) : 0;

                foreach ($members as $member) {
                    $stmt2 = $conn->prepare("INSERT INTO member_shu (distribution_id, member_id, shu_dari_transaksi, shu_dari_partisipasi, total_shu, status_pembayaran) VALUES (?, ?, ?, ?, ?, 'belum_bayar')");
                    $stmt2->bind_param('iidd', $distribution_id, $member['id'], $shu_per_member * 0.7, $shu_per_member * 0.3, $shu_per_member);
                    $stmt2->execute();
                    $stmt2->close();
                }

                // Distribute to investors
                $investors = fetchAll("SELECT id FROM investors WHERE status = 'aktif'");
                $div_per_investor = count($investors) > 0 ? $dividen_investor / count($investors) : 0;

                foreach ($investors as $investor) {
                    $stmt3 = $conn->prepare("INSERT INTO investor_dividends (distribution_id, investor_id, persentase_dividen, jumlah_dividen, status_pembayaran) VALUES (?, ?, 100, ?, 'belum_bayar')");
                    $stmt3->bind_param('iid', $distribution_id, $investor['id'], $div_per_investor);
                    $stmt3->execute();
                    $stmt3->close();
                }
            });

            flashMessage('success', 'Distribusi keuntungan berhasil dibuat');
            redirect('shu/distribute');
        }

        $this->render('shu/create_distribution');
    }

    /**
     * View SHU reports for members.
     */
    public function reports() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'anggota']); // DISABLED for development

        $member_id = intval($_GET['member_id'] ?? 0);

        if ($member_id > 0) {
            // View specific member SHU
            $member = fetchRow("
                SELECT
                    a.id,
                    a.no_anggota,
                    a.nama_lengkap,
                    a.nik,
                    a.status,
                    a.tanggal_gabung,
                    COALESCE(addr.street_address, a.alamat) as alamat,
                    COALESCE(c_phone.contact_value, a.no_hp) as no_hp,
                    COALESCE(c_email.contact_value, a.email) as email
                FROM anggota a
                LEFT JOIN addresses addr ON a.address_id = addr.id AND addr.is_primary = 1
                LEFT JOIN contacts c_phone ON a.primary_contact_id = c_phone.id AND c_phone.contact_type = 'phone'
                LEFT JOIN contacts c_email ON c_email.id = (SELECT id FROM contacts WHERE contact_type = 'email' AND id IN (SELECT contact_id FROM entity_contacts WHERE entity_type = 'member' AND entity_id = a.id) LIMIT 1)
                WHERE a.id = ?
            ", [$member_id], 'i');
            $shu_history = fetchAll("SELECT ms.*, pd.periode, pd.tanggal_distribusi FROM member_shu ms JOIN profit_distributions pd ON ms.distribution_id = pd.id WHERE ms.member_id = ? ORDER BY pd.tanggal_distribusi DESC", [$member_id], 'i');

            $this->render('shu/member_report', [
                'member' => $member,
                'shu_history' => $shu_history
            ]);
        } else {
            // List all members with total SHU
            $page = intval($_GET['page'] ?? 1);
            $perPage = ITEMS_PER_PAGE;
            $offset = ($page - 1) * $perPage;

            $members = fetchAll("SELECT a.*, COALESCE(SUM(ms.total_shu), 0) as total_shu FROM anggota a LEFT JOIN member_shu ms ON a.id = ms.member_id GROUP BY a.id ORDER BY a.nama_lengkap LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

            $total = (fetchRow("SELECT COUNT(*) as count FROM anggota") ?? [])['count'] ?? 0;
            $totalPages = ceil($total / $perPage);

            $this->render('shu/reports', [
                'members' => $members,
                'page' => $page,
                'totalPages' => $totalPages
            ]);
        }
    }

    /**
     * Get SHU statistics.
     */
    private function getShuStats() {
        try {
            $stats = [
                'total_shu_distributed' => (fetchRow("SELECT COALESCE(SUM(shu_anggota), 0) as total FROM profit_distributions WHERE status = 'approved'") ?? [])['total'] ?? 0,
                'total_dividends_distributed' => (fetchRow("SELECT COALESCE(SUM(dividen_investor), 0) as total FROM profit_distributions WHERE status = 'approved'") ?? [])['total'] ?? 0,
                'total_distributions' => (fetchRow("SELECT COUNT(*) as total FROM profit_distributions WHERE status = 'approved'") ?? [])['total'] ?? 0,
                'pending_member_payments' => (fetchRow("SELECT COUNT(*) as total FROM member_shu WHERE status_pembayaran = 'belum_bayar'") ?? [])['total'] ?? 0,
                'pending_investor_payments' => (fetchRow("SELECT COUNT(*) as total FROM investor_dividends WHERE status_pembayaran = 'belum_bayar'") ?? [])['total'] ?? 0,
            ];
        } catch (Exception $e) {
            $stats = ['total_shu_distributed' => 0, 'total_dividends_distributed' => 0, 'total_distributions' => 0, 'pending_member_payments' => 0, 'pending_investor_payments' => 0];
        }
        return $stats;
    }

    /**
     * Get recent distributions.
     */
    private function getRecentDistributions() {
        return fetchAll("SELECT pd.*, u.full_name as approved_by_name FROM profit_distributions pd LEFT JOIN users u ON pd.approved_by = u.id ORDER BY pd.created_at DESC LIMIT 5") ?? [];
    }
}
