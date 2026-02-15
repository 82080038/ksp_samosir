<?php
/**
 * KSP Samosir - Multi Jenis Koperasi Management
 * Controller untuk admin mengelola jenis koperasi
 */

class MultiJenisKoperasiController
{
    private $conn;
    
    public function __construct()
    {
        $this->conn = new mysqli('localhost', 'root', 'root', 'ksp_samosir');
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
    }
    
    /**
     * Get semua jenis koperasi yang tersedia
     */
    public function getJenisKoperasi()
    {
        $sql = "SELECT * FROM koperasi_jenis WHERE is_active = 1 ORDER BY urutan_tampil";
        $result = $this->conn->query($sql);
        
        $jenis_koperasi = [];
        while ($row = $result->fetch_assoc()) {
            $jenis_koperasi[] = [
                'id' => $row['id'],
                'kode_jenis' => $row['kode_jenis'],
                'nama_jenis' => $row['nama_jenis'],
                'deskripsi' => $row['deskripsi'],
                'icon' => $row['icon'],
                'warna_tema' => $row['warna_tema'],
                'modul_count' => $this->getModulCount($row['id']),
                'unit_count' => $this->getUnitCount($row['id'])
            ];
        }
        
        return [
            'success' => true,
            'data' => $jenis_koperasi
        ];
    }
    
    /**
     * Get detail jenis koperasi dengan modulnya
     */
    public function getDetailJenisKoperasi($id)
    {
        // Get jenis koperasi info
        $sql = "SELECT * FROM koperasi_jenis WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $jenis_info = $stmt->get_result()->fetch_assoc();
        
        if (!$jenis_info) {
            return ['success' => false, 'message' => 'Jenis koperasi tidak ditemukan'];
        }
        
        // Get modul untuk jenis ini
        $sql = "SELECT m.*, mm.is_default, mm.is_required, mm.urutan
                FROM koperasi_modul m
                JOIN koperasi_modul_mapping mm ON m.id = mm.modul_id
                WHERE mm.jenis_koperasi_id = ?
                ORDER BY mm.is_required DESC, mm.urutan, m.nama_modul";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $modul = [];
        while ($row = $stmt->get_result()->fetch_assoc()) {
            $modul[] = [
                'id' => $row['id'],
                'kode_modul' => $row['kode_modul'],
                'nama_modul' => $row['nama_modul'],
                'deskripsi' => $row['deskripsi'],
                'icon' => $row['icon'],
                'kategori' => $row['kategori'],
                'is_core' => $row['is_core'],
                'is_default' => $row['is_default'],
                'is_required' => $row['is_required'],
                'urutan' => $row['urutan']
            ];
        }
        
        // Get produk untuk jenis ini
        $sql = "SELECT * FROM koperasi_jenis_produk WHERE jenis_koperasi_id = ? AND is_active = 1 ORDER BY kategori_produk, nama_produk";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $produk = [];
        while ($row = $stmt->get_result()->fetch_assoc()) {
            $produk[] = [
                'id' => $row['id'],
                'kategori_produk' => $row['kategori_produk'],
                'kode_produk' => $row['kode_produk'],
                'nama_produk' => $row['nama_produk'],
                'deskripsi' => $row['deskripsi'],
                'parameter_produk' => json_decode($row['parameter_produk'], true)
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'jenis_info' => $jenis_info,
                'modul' => $modul,
                'produk' => $this->groupProdukByKategori($produk)
            ]
        ];
    }
    
    /**
     * Setup unit dengan jenis koperasi tertentu
     */
    public function setupUnitJenisKoperasi($unit_id, $jenis_koperasi_id, $modul_config = [])
    {
        // Check if already setup
        $sql = "SELECT id FROM koperasi_unit_extended WHERE unit_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $unit_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Unit sudah di-setup jenis koperasi'];
        }
        
        // Get default modul for this jenis
        $sql = "SELECT modul_id FROM koperasi_modul_mapping 
                WHERE jenis_koperasi_id = ? AND is_default = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $jenis_koperasi_id);
        $stmt->execute();
        
        $default_modul = [];
        while ($row = $stmt->get_result()->fetch_assoc()) {
            $default_modul[] = $row['modul_id'];
        }
        
        // Override with user config if provided
        if (!empty($modul_config)) {
            $default_modul = $modul_config;
        }
        
        // Insert setup
        $sql = "INSERT INTO koperasi_unit_extended 
                (unit_id, jenis_koperasi_id, konfigurasi_modul, fitur_unggulan, target_pasar)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        $konfigurasi_modul = json_encode([
            'enabled_modul' => $default_modul,
            'setup_date' => date('Y-m-d H:i:s')
        ]);
        
        $fitur_unggulan = json_encode([
            'ai_credit_scoring' => true,
            'mobile_banking' => true,
            'digital_payment' => true
        ]);
        
        $target_pasar = 'Pasar umum dan khusus sesuai jenis koperasi';
        
        $stmt->bind_param("iisss", $unit_id, $jenis_koperasi_id, $konfigurasi_modul, $fitur_unggulan, $target_pasar);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Unit berhasil di-setup dengan jenis koperasi',
                'setup_id' => $this->conn->insert_id
            ];
        } else {
            return ['success' => false, 'message' => 'Gagal setup unit: ' . $stmt->error];
        }
    }
    
    /**
     * Update konfigurasi unit
     */
    public function updateUnitKonfigurasi($unit_id, $konfigurasi)
    {
        $sql = "UPDATE koperasi_unit_extended 
                SET konfigurasi_modul = ?, fitur_unggulan = ?, target_pasar = ?, updated_at = NOW()
                WHERE unit_id = ?";
        $stmt = $this->conn->prepare($sql);
        
        $konfigurasi_modul = json_encode($konfigurasi['modul'] ?? []);
        $fitur_unggulan = json_encode($konfigurasi['fitur_unggulan'] ?? []);
        $target_pasar = $konfigurasi['target_pasar'] ?? '';
        
        $stmt->bind_param("sssi", $konfigurasi_modul, $fitur_unggulan, $target_pasar, $unit_id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Konfigurasi unit berhasil diupdate'];
        } else {
            return ['success' => false, 'message' => 'Gagal update konfigurasi: ' . $stmt->error];
        }
    }
    
    /**
     * Get unit yang sudah di-setup
     */
    public function getUnitSetup()
    {
        $sql = "SELECT ue.*, u.nama_unit, u.kode_unit, j.nama_jenis, j.warna_tema, j.icon
                FROM koperasi_unit_extended ue
                JOIN koperasi_unit u ON ue.unit_id = u.id
                JOIN koperasi_jenis j ON ue.jenis_koperasi_id = j.id
                ORDER BY u.nama_unit";
        $result = $this->conn->query($sql);
        
        $units = [];
        while ($row = $result->fetch_assoc()) {
            $units[] = [
                'id' => $row['id'],
                'unit_id' => $row['unit_id'],
                'nama_unit' => $row['nama_unit'],
                'kode_unit' => $row['kode_unit'],
                'jenis_koperasi' => [
                    'nama_jenis' => $row['nama_jenis'],
                    'warna_tema' => $row['warna_tema'],
                    'icon' => $row['icon']
                ],
                'konfigurasi_modul' => json_decode($row['konfigurasi_modul'], true),
                'fitur_unggulan' => json_decode($row['fitur_unggulan'], true),
                'target_pasar' => $row['target_pasar'],
                'created_at' => $row['created_at']
            ];
        }
        
        return [
            'success' => true,
            'data' => $units
        ];
    }
    
    /**
     * Get statistik setup
     */
    public function getStatistikSetup()
    {
        $sql = "SELECT 
                    COUNT(*) as total_unit,
                    COUNT(CASE WHEN jenis_koperasi_id = 1 THEN 1 END) as ksp_count,
                    COUNT(CASE WHEN jenis_koperasi_id = 2 THEN 1 END) as kpn_count,
                    COUNT(CASE WHEN jenis_koperasi_id = 3 THEN 1 END) as kpt_count,
                    COUNT(CASE WHEN jenis_koperasi_id > 3 THEN 1 END) as other_count
                FROM koperasi_unit_extended";
        $result = $this->conn->query($sql);
        $stats = $result->fetch_assoc();
        
        // Get jenis koperasi distribution
        $sql = "SELECT j.nama_jenis, COUNT(ue.id) as unit_count
                FROM koperasi_jenis j
                LEFT JOIN koperasi_unit_extended ue ON j.id = ue.jenis_koperasi_id
                WHERE j.is_active = 1
                GROUP BY j.id, j.nama_jenis
                ORDER BY unit_count DESC";
        $result = $this->conn->query($sql);
        
        $distribution = [];
        while ($row = $result->fetch_assoc()) {
            $distribution[] = [
                'jenis' => $row['nama_jenis'],
                'count' => $row['unit_count']
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'total_unit' => $stats['total_unit'],
                'distribution' => $distribution,
                'most_popular' => $distribution[0]['jenis'] ?? 'None'
            ]
        ];
    }
    
    /**
     * Helper methods
     */
    private function getModulCount($jenis_id)
    {
        $sql = "SELECT COUNT(*) as count FROM koperasi_modul_mapping WHERE jenis_koperasi_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $jenis_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'];
    }
    
    private function getUnitCount($jenis_id)
    {
        $sql = "SELECT COUNT(*) as count FROM koperasi_unit_extended WHERE jenis_koperasi_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $jenis_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'];
    }
    
    private function groupProdukByKategori($produk)
    {
        $grouped = [];
        foreach ($produk as $p) {
            $kategori = $p['kategori_produk'];
            unset($p['kategori_produk']);
            $grouped[$kategori][] = $p;
        }
        return $grouped;
    }
    
    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
