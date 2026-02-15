<?php
/**
 * Koperasi Management Controller
 * Handles all koperasi operations including simpanan, pinjaman, anggota
 */

class KoperasiController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Get dashboard overview
     */
    public function getDashboardOverview()
    {
        try {
            $overview = [];
            
            // Total anggota
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM anggota WHERE status = 'aktif'");
            $overview['total_anggota'] = $stmt->fetch()['total'];
            
            // Total simpanan
            $stmt = $this->db->query("SELECT COALESCE(SUM(jumlah), 0) as total FROM simpanan WHERE status = 'aktif'");
            $overview['total_simpanan'] = $stmt->fetch()['total'];
            
            // Total pinjaman
            $stmt = $this->db->query("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total FROM pinjaman WHERE status = 'disetujui'");
            $overview['total_pinjaman'] = $stmt->fetch()['total'];
            
            // Total angsuran bulan ini
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(total_bayar), 0) as total 
                FROM angsuran 
                WHERE MONTH(tanggal_bayar) = MONTH(CURRENT_DATE()) 
                AND YEAR(tanggal_bayar) = YEAR(CURRENT_DATE())
                AND status = 'lunas'
            ");
            $stmt->execute();
            $overview['angsuran_bulan_ini'] = $stmt->fetch()['total'];
            
            // Pinjaman menunggu persetujuan
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM pinjaman WHERE status = 'pending'");
            $overview['pinjaman_pending'] = $stmt->fetch()['total'];
            
            // Angsuran jatuh tempo bulan ini
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM pinjaman 
                WHERE MONTH(tanggal_jatuh_tempo) = MONTH(CURRENT_DATE()) 
                AND YEAR(tanggal_jatuh_tempo) = YEAR(CURRENT_DATE())
                AND status = 'disetujui'
            ");
            $stmt->execute();
            $overview['angsuran_jatuh_tempo'] = $stmt->fetch()['total'];
            
            return [
                'success' => true,
                'data' => $overview
            ];
        } catch (Exception $e) {
            error_log("Error getting dashboard overview: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get dashboard overview'];
        }
    }
    
    /**
     * Get all anggota with filters
     */
    public function getAnggota($filters = [])
    {
        try {
            $sql = "
                SELECT a.*, u.nama_unit, u.kode_unit
                FROM anggota a
                LEFT JOIN koperasi_unit u ON a.unit_id = u.id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND a.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['unit_id'])) {
                $sql .= " AND a.unit_id = ?";
                $params[] = $filters['unit_id'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (a.nama_lengkap LIKE ? OR a.no_anggota LIKE ? OR a.email LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY a.created_at DESC";
            
            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $anggota = $stmt->fetchAll();
            
            // Get simpanan and pinjaman info for each anggota
            foreach ($anggota as &$member) {
                $member['total_simpanan'] = $this->getTotalSimpanan($member['id']);
                $member['total_pinjaman'] = $this->getTotalPinjaman($member['id']);
                $member['sisa_pinjaman'] = $this->getSisaPinjaman($member['id']);
            }
            
            return [
                'success' => true,
                'data' => $anggota
            ];
        } catch (Exception $e) {
            error_log("Error getting anggota: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get anggota data'];
        }
    }
    
    /**
     * Create new anggota
     */
    public function createAnggota($data)
    {
        try {
            // Generate no_anggota
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM anggota");
            $count = $stmt->fetch()['count'];
            $no_anggota = 'KSP-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            
            $sql = "
                INSERT INTO anggota (
                    no_anggota, nama_lengkap, nik, tempat_lahir, tanggal_lahir,
                    jenis_kelamin, alamat, no_hp, email, pekerjaan, pendapatan_bulanan,
                    tanggal_gabung, status, created_by, province_id, regency_id,
                    district_id, village_id, unit_id, induk_id, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $no_anggota,
                $data['nama_lengkap'],
                $data['nik'],
                $data['tempat_lahir'],
                $data['tanggal_lahir'],
                $data['jenis_kelamin'],
                $data['alamat'],
                $data['no_hp'],
                $data['email'],
                $data['pekerjaan'],
                $data['pendapatan_bulanan'],
                $data['tanggal_gabung'] ?? date('Y-m-d'),
                $data['status'] ?? 'aktif',
                $_SESSION['user_id'] ?? 1,
                $data['province_id'],
                $data['regency_id'],
                $data['district_id'],
                $data['village_id'],
                $data['unit_id'],
                $data['induk_id'] ?? 1
            ]);
            
            if ($result) {
                $anggota_id = $this->db->lastInsertId();
                
                // Auto create simpanan pokok
                $this->createSimpananPokok($anggota_id);
                
                return [
                    'success' => true,
                    'message' => 'Anggota created successfully',
                    'anggota_id' => $anggota_id,
                    'no_anggota' => $no_anggota
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create anggota'];
        } catch (Exception $e) {
            error_log("Error creating anggota: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create anggota'];
        }
    }
    
    /**
     * Get simpanan data
     */
    public function getSimpanan($filters = [])
    {
        try {
            $sql = "
                SELECT s.*, a.nama_lengkap, a.no_anggota, js.nama_simpanan, js.jenis
                FROM simpanan s
                JOIN anggota a ON s.anggota_id = a.id
                JOIN jenis_simpanan js ON s.jenis_simpanan_id = js.id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($filters['anggota_id'])) {
                $sql .= " AND s.anggota_id = ?";
                $params[] = $filters['anggota_id'];
            }
            
            if (!empty($filters['jenis_simpanan_id'])) {
                $sql .= " AND s.jenis_simpanan_id = ?";
                $params[] = $filters['jenis_simpanan_id'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND s.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND s.tanggal_setor >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND s.tanggal_setor <= ?";
                $params[] = $filters['date_to'];
            }
            
            $sql .= " ORDER BY s.tanggal_setor DESC";
            
            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $simpanan = $stmt->fetchAll();
            
            return [
                'success' => true,
                'data' => $simpanan
            ];
        } catch (Exception $e) {
            error_log("Error getting simpanan: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get simpanan data'];
        }
    }
    
    /**
     * Create simpanan
     */
    public function createSimpanan($data)
    {
        try {
            $sql = "
                INSERT INTO simpanan (
                    anggota_id, jenis_simpanan_id, jumlah, tanggal_setor,
                    keterangan, status, created_by, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['anggota_id'],
                $data['jenis_simpanan_id'],
                $data['jumlah'],
                $data['tanggal_setor'],
                $data['keterangan'] ?? '',
                $data['status'] ?? 'aktif',
                $_SESSION['user_id'] ?? 1
            ]);
            
            if ($result) {
                // Create journal entry
                $this->createJournalEntrySimpanan($this->db->lastInsertId(), $data);
                
                return [
                    'success' => true,
                    'message' => 'Simpanan created successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create simpanan'];
        } catch (Exception $e) {
            error_log("Error creating simpanan: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create simpanan'];
        }
    }
    
    /**
     * Get pinjaman data
     */
    public function getPinjaman($filters = [])
    {
        try {
            $sql = "
                SELECT p.*, a.nama_lengkap, a.no_anggota, jp.nama_pinjaman, jp.bunga_pertahun
                FROM pinjaman p
                JOIN anggota a ON p.anggota_id = a.id
                JOIN jenis_pinjaman jp ON p.jenis_pinjaman_id = jp.id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($filters['anggota_id'])) {
                $sql .= " AND p.anggota_id = ?";
                $params[] = $filters['anggota_id'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND p.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['jenis_pinjaman_id'])) {
                $sql .= " AND p.jenis_pinjaman_id = ?";
                $params[] = $filters['jenis_pinjaman_id'];
            }
            
            $sql .= " ORDER BY p.created_at DESC";
            
            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $pinjaman = $stmt->fetchAll();
            
            // Calculate remaining balance and payment status
            foreach ($pinjaman as &$loan) {
                $loan['total_dibayar'] = $this->getTotalDibayar($loan['id']);
                $loan['sisa_pinjaman'] = $loan['jumlah_pinjaman'] - $loan['total_dibayar'];
                $loan['jumlah_angsuran'] = $this->calculateAngsuran($loan['jumlah_pinjaman'], $loan['bunga'], $loan['tenor']);
                $loan['angsuran_terbayar'] = $this->getAngsuranTerbayar($loan['id']);
                $loan['status_pembayaran'] = $this->getStatusPembayaran($loan['id'], $loan['tenor']);
            }
            
            return [
                'success' => true,
                'data' => $pinjaman
            ];
        } catch (Exception $e) {
            error_log("Error getting pinjaman: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get pinjaman data'];
        }
    }
    
    /**
     * Create pinjaman application
     */
    public function createPinjaman($data)
    {
        try {
            // Calculate jatuh tempo
            $tanggal_cair = $data['tanggal_cair'] ?? date('Y-m-d');
            $tanggal_jatuh_tempo = date('Y-m-d', strtotime($tanggal_cair . ' + ' . $data['tenor'] . ' months'));
            
            $sql = "
                INSERT INTO pinjaman (
                    anggota_id, jenis_pinjaman_id, jumlah_pinjaman, bunga, tenor,
                    tanggal_pengajuan, tanggal_disetujui, tanggal_cair, tanggal_jatuh_tempo,
                    keperluan, status, disetujui_oleh, created_by, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['anggota_id'],
                $data['jenis_pinjaman_id'],
                $data['jumlah_pinjaman'],
                $data['bunga'],
                $data['tenor'],
                $data['tanggal_pengajuan'],
                $data['tanggal_disetujui'] ?? null,
                $tanggal_cair,
                $tanggal_jatuh_tempo,
                $data['keperluan'],
                $data['status'] ?? 'pending',
                $data['disetujui_oleh'] ?? null,
                $_SESSION['user_id'] ?? 1
            ]);
            
            if ($result) {
                $pinjaman_id = $this->db->lastInsertId();
                
                // If approved, create journal entry
                if ($data['status'] === 'disetujui') {
                    $this->createJournalEntryPinjaman($pinjaman_id, $data);
                }
                
                return [
                    'success' => true,
                    'message' => 'Pinjaman application created successfully',
                    'pinjaman_id' => $pinjaman_id
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create pinjaman application'];
        } catch (Exception $e) {
            error_log("Error creating pinjaman: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create pinjaman application'];
        }
    }
    
    /**
     * Approve pinjaman
     */
    public function approvePinjaman($pinjaman_id, $approval_data)
    {
        try {
            $sql = "
                UPDATE pinjaman 
                SET status = 'disetujui',
                    tanggal_disetujui = ?,
                    tanggal_cair = ?,
                    disetujui_olel = ?,
                    updated_at = NOW()
                WHERE id = ?
            ";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $approval_data['tanggal_disetujui'],
                $approval_data['tanggal_cair'],
                $_SESSION['user_id'],
                $pinjaman_id
            ]);
            
            if ($result) {
                // Create journal entry
                $this->createJournalEntryPinjaman($pinjaman_id, $approval_data);
                
                return [
                    'success' => true,
                    'message' => 'Pinjaman approved successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to approve pinjaman'];
        } catch (Exception $e) {
            error_log("Error approving pinjaman: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to approve pinjaman'];
        }
    }
    
    /**
     * Get angsuran data
     */
    public function getAngsuran($filters = [])
    {
        try {
            $sql = "
                SELECT a.*, p.no_anggota, p.nama_lengkap, pj.nama_pinjaman
                FROM angsuran a
                JOIN pinjaman pin ON a.pinjaman_id = pin.id
                JOIN anggota p ON pin.anggota_id = p.id
                JOIN jenis_pinjaman pj ON pin.jenis_pinjaman_id = pj.id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($filters['pinjaman_id'])) {
                $sql .= " AND a.pinjaman_id = ?";
                $params[] = $filters['pinjaman_id'];
            }
            
            if (!empty($filters['anggota_id'])) {
                $sql .= " AND pin.anggota_id = ?";
                $params[] = $filters['anggota_id'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND a.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND a.tanggal_bayar >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND a.tanggal_bayar <= ?";
                $params[] = $filters['date_to'];
            }
            
            $sql .= " ORDER BY a.tanggal_bayar DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $angsuran = $stmt->fetchAll();
            
            return [
                'success' => true,
                'data' => $angsuran
            ];
        } catch (Exception $e) {
            error_log("Error getting angsuran: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get angsuran data'];
        }
    }
    
    /**
     * Create angsuran payment
     */
    public function createAngsuran($data)
    {
        try {
            $sql = "
                INSERT INTO angsuran (
                    pinjaman_id, jumlah_angsuran, bunga, pokok, total_bayar,
                    tanggal_bayar, status, created_by, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['pinjaman_id'],
                $data['jumlah_angsuran'],
                $data['bunga'],
                $data['pokok'],
                $data['total_bayar'],
                $data['tanggal_bayar'],
                $data['status'] ?? 'lunas',
                $_SESSION['user_id'] ?? 1
            ]);
            
            if ($result) {
                // Create journal entry
                $this->createJournalEntryAngsuran($this->db->lastInsertId(), $data);
                
                return [
                    'success' => true,
                    'message' => 'Angsuran payment created successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create angsuran payment'];
        } catch (Exception $e) {
            error_log("Error creating angsuran: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create angsuran payment'];
        }
    }
    
    /**
     * Helper methods
     */
    private function getTotalSimpanan($anggota_id)
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(jumlah), 0) as total FROM simpanan WHERE anggota_id = ? AND status = 'aktif'");
        $stmt->execute([$anggota_id]);
        return $stmt->fetch()['total'];
    }
    
    private function getTotalPinjaman($anggota_id)
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total FROM pinjaman WHERE anggota_id = ? AND status = 'disetujui'");
        $stmt->execute([$anggota_id]);
        return $stmt->fetch()['total'];
    }
    
    private function getSisaPinjaman($anggota_id)
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(p.jumlah_pinjaman) - COALESCE(SUM(a.total_bayar), 0), 0) as sisa 
            FROM pinjaman p 
            LEFT JOIN angsuran a ON p.id = a.pinjaman_id AND a.status = 'lunas'
            WHERE p.anggota_id = ? AND p.status = 'disetujui'
        ");
        $stmt->execute([$anggota_id]);
        return $stmt->fetch()['sisa'];
    }
    
    private function createSimpananPokok($anggota_id)
    {
        $stmt = $this->db->prepare("SELECT id FROM jenis_simpanan WHERE nama_simpanan = 'Simpanan Pokok'");
        $stmt->execute();
        $jenis_simpanan_id = $stmt->fetch()['id'];
        
        if ($jenis_simpanan_id) {
            $stmt = $this->db->prepare("
                INSERT INTO simpanan (anggota_id, jenis_simpanan_id, jumlah, tanggal_setor, keterangan, status, created_by, created_at)
                VALUES (?, ?, 100000, ?, 'Simpanan pokok otomatis', 'aktif', 1, NOW())
            ");
            $stmt->execute([$anggota_id, date('Y-m-d')]);
        }
    }
    
    private function calculateAngsuran($jumlah_pinjaman, $bunga, $tenor)
    {
        $bunga_per_bulan = $bunga / 100 / 12;
        $angsuran = $jumlah_pinjaman * ($bunga_per_bulan * pow(1 + $bunga_per_bulan, $tenor)) / (pow(1 + $bunga_per_bulan, $tenor) - 1);
        return round($angsuran);
    }
    
    private function getTotalDibayar($pinjaman_id)
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(total_bayar), 0) as total FROM angsuran WHERE pinjaman_id = ? AND status = 'lunas'");
        $stmt->execute([$pinjaman_id]);
        return $stmt->fetch()['total'];
    }
    
    private function getAngsuranTerbayar($pinjaman_id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM angsuran WHERE pinjaman_id = ? AND status = 'lunas'");
        $stmt->execute([$pinjaman_id]);
        return $stmt->fetch()['total'];
    }
    
    private function getStatusPembayaran($pinjaman_id, $tenor)
    {
        $terbayar = $this->getAngsuranTerbayar($pinjaman_id);
        if ($terbayar >= $tenor) return 'lunas';
        if ($terbayar > 0) return 'proses';
        return 'belum';
    }
    
    private function createJournalEntrySimpanan($simpanan_id, $data)
    {
        // Journal entry for simpanan
        // Debit: Kas, Credit: Simpanan Anggota
        // Implementation depends on COA structure
    }
    
    private function createJournalEntryPinjaman($pinjaman_id, $data)
    {
        // Journal entry for pinjaman
        // Debit: Piutang Pinjaman, Credit: Kas
        // Implementation depends on COA structure
    }
    
    private function createJournalEntryAngsuran($angsuran_id, $data)
    {
        // Journal entry for angsuran
        // Debit: Kas, Credit: Piutang Pinjaman, Credit: Pendapatan Bunga
        // Implementation depends on COA structure
    }
}
