<?php
/**
 * Multi-Unit Management Controller
 * KSP Samosir Multi-Unit Implementation
 */

class MultiUnitController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Get all koperasi units
     */
    public function getAllUnits($indukId = null)
    {
        try {
            $sql = "
                SELECT ku.*, ki.nama_induk, ki.kode_induk
                FROM koperasi_unit ku
                JOIN koperasi_induk ki ON ku.induk_id = ki.id
                WHERE 1=1
            ";
            
            $params = [];
            if ($indukId) {
                $sql .= " AND ku.induk_id = ?";
                $params[] = $indukId;
            }
            
            $sql .= " ORDER BY ku.level_unit, ku.nama_unit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting units: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get unit by ID
     */
    public function getUnitById($unitId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ku.*, ki.nama_induk, ki.kode_induk
                FROM koperasi_unit ku
                JOIN koperasi_induk ki ON ku.induk_id = ki.id
                WHERE ku.id = ?
            ");
            $stmt->execute([$unitId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting unit: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new unit
     */
    public function createUnit($unitData)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO koperasi_unit 
                (induk_id, nama_unit, kode_unit, jenis_unit, level_unit, alamat, telepon, email, 
                 kepala_unit, nik_kepala, latitude, longitude, radius_operasi_km, jam_operasi,
                 layanan_unggulan, target_bulanan, is_active, tanggal_berdiri)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $unitData['induk_id'],
                $unitData['nama_unit'],
                $unitData['kode_unit'],
                $unitData['jenis_unit'],
                $unitData['level_unit'],
                $unitData['alamat'] ?? null,
                $unitData['telepon'] ?? null,
                $unitData['email'] ?? null,
                $unitData['kepala_unit'] ?? null,
                $unitData['nik_kepala'] ?? null,
                $unitData['latitude'] ?? null,
                $unitData['longitude'] ?? null,
                $unitData['radius_operasi_km'] ?? 10,
                json_encode($unitData['jam_operasi'] ?? []),
                json_encode($unitData['layanan_unggulan'] ?? []),
                $unitData['target_bulanan'] ?? 0,
                $unitData['is_active'] ?? 1,
                $unitData['tanggal_berdiri'] ?? null
            ]);
            
            if ($result) {
                $unitId = $this->db->lastInsertId();
                return [
                    'success' => true,
                    'unit_id' => $unitId,
                    'message' => 'Unit created successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create unit'];
        } catch (Exception $e) {
            error_log("Error creating unit: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Update unit
     */
    public function updateUnit($unitId, $unitData)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE koperasi_unit 
                SET nama_unit = ?, jenis_unit = ?, level_unit = ?, alamat = ?, telepon = ?, 
                    email = ?, kepala_unit = ?, nik_kepala = ?, latitude = ?, longitude = ?, 
                    radius_operasi_km = ?, jam_operasi = ?, layanan_unggulan = ?, 
                    target_bulanan = ?, is_active = ?, tanggal_berdiri = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $unitData['nama_unit'],
                $unitData['jenis_unit'],
                $unitData['level_unit'],
                $unitData['alamat'] ?? null,
                $unitData['telepon'] ?? null,
                $unitData['email'] ?? null,
                $unitData['kepala_unit'] ?? null,
                $unitData['nik_kepala'] ?? null,
                $unitData['latitude'] ?? null,
                $unitData['longitude'] ?? null,
                $unitData['radius_operasi_km'] ?? 10,
                json_encode($unitData['jam_operasih'] ?? []),
                json_encode($unitData['layanan_unggulan'] ?? []),
                $unitData['target_bulanan'] ?? 0,
                $unitData['is_active'] ?? 1,
                $unitData['tanggal_berdiri'] ?? null,
                $unitId
            ]);
            
            return $result ? 
                ['success' => true, 'message' => 'Unit updated successfully'] : 
                ['success' => false, 'message' => 'Failed to update unit'];
        } catch (Exception $e) {
            error_log("Error updating unit: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Delete unit
     */
    public function deleteUnit($unitId)
    {
        try {
            // Check if unit has related data
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM users WHERE unit_id = ?
                UNION ALL
                SELECT COUNT(*) as count FROM anggota WHERE unit_id = ?
                UNION ALL
                SELECT COUNT(*) as count FROM penjualan WHERE unit_id = ?
            ");
            $stmt->execute([$unitId, $unitId, $unitId]);
            $results = $stmt->fetchAll();
            
            $totalRelated = array_sum(array_column($results, 'count'));
            if ($totalRelated > 0) {
                return ['success' => false, 'message' => 'Cannot delete unit with related data'];
            }
            
            $stmt = $this->db->prepare("DELETE FROM koperasi_unit WHERE id = ?");
            $result = $stmt->execute([$unitId]);
            
            return $result ? 
                ['success' => true, 'message' => 'Unit deleted successfully'] : 
                ['success' => false, 'message' => 'Failed to delete unit'];
        } catch (Exception $e) {
            error_log("Error deleting unit: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Get unit hierarchy
     */
    public function getUnitHierarchy($indukId = 1)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM v_struktur_organisasi 
                WHERE induk_id = ?
                ORDER BY hierarchy_level, nama_unit
            ");
            $stmt->execute([$indukId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting unit hierarchy: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get unit performance
     */
    public function getUnitPerformance($unitId = null, $periode = null)
    {
        try {
            $sql = "
                SELECT * FROM v_performance_unit 
                WHERE 1=1
            ";
            
            $params = [];
            if ($unitId) {
                $sql .= " AND unit_id = ?";
                $params[] = $unitId;
            }
            
            if ($periode) {
                $sql .= " AND periode_bulan = ?";
                $params[] = $periode;
            }
            
            $sql .= " ORDER BY periode_bulan DESC, nama_unit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting unit performance: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Set unit targets
     */
    public function setUnitTargets($unitId, $periode, $targets)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO unit_target_performance 
                (unit_id, periode_bulan, target_anggota, target_simpanan, target_pinjaman, target_pendapatan)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    target_anggota = VALUES(target_anggota),
                    target_simpanan = VALUES(target_simpanan),
                    target_pinjaman = VALUES(target_pinjaman),
                    target_pendapatan = VALUES(target_pendapatan),
                    updated_at = NOW()
            ");
            
            $result = $stmt->execute([
                $unitId,
                $periode,
                $targets['target_anggota'] ?? 0,
                $targets['target_simpanan'] ?? 0,
                $targets['target_pinjaman'] ?? 0,
                $targets['target_pendapatan'] ?? 0
            ]);
            
            return $result ? 
                ['success' => true, 'message' => 'Targets set successfully'] : 
                ['success' => false, 'message' => 'Failed to set targets'];
        } catch (Exception $e) {
            error_log("Error setting unit targets: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Update unit performance realization
     */
    public function updateUnitRealization($unitId, $periode, $realization)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE unit_target_performance 
                SET realisasi_anggota = ?, realisasi_simpanan = ?, realisasi_pinjaman = ?, 
                    realisasi_pendapatan = ?, updated_at = NOW()
                WHERE unit_id = ? AND periode_bulan = ?
            ");
            
            $result = $stmt->execute([
                $realization['realisasi_anggota'] ?? 0,
                $realization['realisasi_simpanan'] ?? 0,
                $realization['realisasi_pinjaman'] ?? 0,
                $realization['realisasi_pendapatan'] ?? 0,
                $unitId,
                $periode
            ]);
            
            if ($result) {
                // Calculate percentages
                $this->calculatePerformancePercentages($unitId, $periode);
            }
            
            return $result ? 
                ['success' => true, 'message' => 'Realization updated successfully'] : 
                ['success' => false, 'message' => 'Failed to update realization'];
        } catch (Exception $e) {
            error_log("Error updating unit realization: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Calculate performance percentages
     */
    private function calculatePerformancePercentages($unitId, $periode)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE unit_target_performance 
                SET persentase_capai_anggota = CASE 
                    WHEN target_anggota > 0 THEN ROUND((realisasi_anggota / target_anggota) * 100, 2)
                    ELSE 0 END,
                    persentase_capai_simpanan = CASE 
                    WHEN target_simpanan > 0 THEN ROUND((realisasi_simpanan / target_simpanan) * 100, 2)
                    ELSE 0 END,
                    persentase_capai_pinjaman = CASE 
                    WHEN target_pinjaman > 0 THEN ROUND((realisasi_pinjaman / target_pinjaman) * 100, 2)
                    ELSE 0 END,
                    persentase_capai_pendapatan = CASE 
                    WHEN target_pendapatan > 0 THEN ROUND((realisasi_pendapatan / target_pendapatan) * 100, 2)
                    ELSE 0 END
                WHERE unit_id = ? AND periode_bulan = ?
            ");
            return $stmt->execute([$unitId, $periode]);
        } catch (Exception $e) {
            error_log("Error calculating percentages: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get consolidated financial report
     */
    public function getConsolidatedReport($indukId = 1, $periodeMulai, $periodeSelesai)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM v_konsolidasi_keuangan 
                WHERE induk_id = ?
                AND periode_bulan BETWEEN ? AND ?
            ");
            $stmt->execute([$indukId, $periodeMulai, $periodeSelesai]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting consolidated report: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Transfer funds between units
     */
    public function transferFunds($transferData)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO unit_transfer_dana 
                (dari_unit_id, ke_unit_id, induk_id, jumlah_transfer, keterangan, created_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $transferData['dari_unit_id'],
                $transferData['ke_unit_id'],
                $transferData['induk_id'],
                $transferData['jumlah_transfer'],
                $transferData['keterangan'] ?? null,
                $_SESSION['user_id']
            ]);
            
            if ($result) {
                $transferId = $this->db->lastInsertId();
                return [
                    'success' => true,
                    'transfer_id' => $transferId,
                    'message' => 'Transfer request created successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create transfer request'];
        } catch (Exception $e) {
            error_log("Error creating transfer: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Get transfer history
     */
    public function getTransferHistory($unitId = null, $status = null)
    {
        try {
            $sql = "
                SELECT utd, 
                       dari.nama_unit as dari_unit, 
                       ke.nama_unit as ke_unit,
                       dari_user.full_name as created_by_name
                FROM unit_transfer_dana utd
                JOIN koperasi_unit dari ON utd.dari_unit_id = dari.id
                JOIN koperasi_unit ke ON utd.ke_unit_id = ke.id
                LEFT JOIN users dari_user ON utd.created_by = dari_user.id
                WHERE 1=1
            ";
            
            $params = [];
            if ($unitId) {
                $sql .= " AND (utd.dari_unit_id = ? OR utd.ke_unit_id = ?)";
                $params[] = $unitId;
                $params[] = $unitId;
            }
            
            if ($status) {
                $sql .= " AND utd.status_transfer = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY utd.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting transfer history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Approve transfer
     */
    public function approveTransfer($transferId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE unit_transfer_dana 
                SET status_transfer = 'approved', approved_by = ?, approved_at = NOW()
                WHERE id = ? AND status_transfer = 'pending'
            ");
            
            $result = $stmt->execute([$_SESSION['user_id'], $transferId]);
            
            return $result ? 
                ['success' => true, 'message' => 'Transfer approved successfully'] : 
                ['success' => false, 'message' => 'Failed to approve transfer'];
        } catch (Exception $e) {
            error_log("Error approving transfer: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Get unit statistics
     */
    public function getUnitStatistics($unitId = null)
    {
        try {
            $sql = "
                SELECT 
                    ku.id,
                    ku.nama_unit,
                    ku.kode_unit,
                    COUNT(DISTINCT u.id) as total_users,
                    COUNT(DISTINCT a.id) as total_anggota,
                    COUNT(DISTINCT p.id) as total_penjualan,
                    COALESCE(SUM(p.total_harga), 0) as total_penjualan_amount,
                    COALESCE(SUM(s.total_simpanan), 0) as total_simpanan,
                    COALESCE(SUM(pin.total_pinjaman), 0) as total_pinjaman
                FROM koperasi_unit ku
                LEFT JOIN users u ON ku.id = u.unit_id
                LEFT JOIN anggota a ON ku.id = a.unit_id
                LEFT JOIN penjualan p ON ku.id = p.unit_id AND p.status_pembayaran = 'lunas'
                LEFT JOIN (
                    SELECT unit_id, SUM(jumlah) as total_simpanan
                    FROM simpanan
                    WHERE unit_id IS NOT NULL
                    GROUP BY unit_id
                ) s ON ku.id = s.unit_id
                LEFT JOIN (
                    SELECT unit_id, SUM(jumlah_pinjaman) as total_pinjaman
                    FROM pinjaman
                    WHERE unit_id IS NOT NULL
                    GROUP BY unit_id
                ) pin ON ku.id = pin.unit_id
            ";
            
            $params = [];
            if ($unitId) {
                $sql .= " WHERE ku.id = ?";
                $params[] = $unitId;
            }
            
            $sql .= " GROUP BY ku.id, ku.nama_unit, ku.kode_unit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting unit statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get available units for user
     */
    public function getUserUnits($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ku.*, ki.nama_induk
                FROM koperasi_unit ku
                JOIN koperasi_induk ki ON ku.induk_id = ki.id
                WHERE ku.is_active = 1
                AND (
                    ku.id = (SELECT unit_id FROM users WHERE id = ?)
                    OR (SELECT role_id FROM users WHERE id = ?) IN (1, 2) -- Admin and Manager can see all
                )
                ORDER BY ku.level_unit, ku.nama_unit
            ");
            $stmt->execute([$userId, $userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting user units: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Switch active unit for user
     */
    public function switchUserUnit($userId, $unitId)
    {
        try {
            // Validate user has access to this unit
            $userUnits = $this->getUserUnits($userId);
            $hasAccess = false;
            
            foreach ($userUnits as $unit) {
                if ($unit['id'] == $unitId) {
                    $hasAccess = true;
                    break;
                }
            }
            
            if (!$hasAccess) {
                return ['success' => false, 'message' => 'Access denied to this unit'];
            }
            
            $stmt = $this->db->prepare("
                UPDATE users SET unit_id = ?, updated_at = NOW() WHERE id = ?
            ");
            $result = $stmt->execute([$unitId, $userId]);
            
            return $result ? 
                ['success' => true, 'message' => 'Unit switched successfully'] : 
                ['success' => false, 'message' => 'Failed to switch unit'];
        } catch (Exception $e) {
            error_log("Error switching user unit: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
}
