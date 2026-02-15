<?php
/**
 * KSP Samosir - Agricultural Management Controller
 * Controller untuk mengelola koperasi pertanian
 */

class AgriculturalController
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
     * Get dashboard agricultural data
     */
    public function getDashboard()
    {
        $sql = "SELECT 
                    COUNT(DISTINCT l.id) as total_lahan,
                    COUNT(DISTINCT p.id) as total_planning,
                    SUM(l.luas_lahan) as total_luas_lahan,
                    COUNT(DISTINCT CASE WHEN p.status = 'proses_tanam' THEN p.id END) as sedang_ditanam,
                    COUNT(DISTINCT CASE WHEN p.status = 'siap_panen' THEN p.id END) as siap_panen,
                    SUM(CASE WHEN p.status = 'dipanen' THEN p.hasil_panen ELSE 0 END) as total_produksi
                FROM ksp_agricultural_lahan l
                LEFT JOIN ksp_agricultural_planning p ON l.id = p.lahan_id";
        
        $result = $this->conn->query($sql);
        $stats = $result->fetch_assoc();
        
        // Get recent activities
        $sql = "SELECT p.*, a.nama_lengkap, t.nama_tanaman, l.nama_lahan
                FROM ksp_agricultural_planning p
                JOIN anggota a ON p.anggota_id = a.id
                JOIN ksp_agricultural_tanaman t ON p.tanaman_id = t.id
                JOIN ksp_agricultural_lahan l ON p.lahan_id = l.id
                ORDER BY p.updated_at DESC
                LIMIT 5";
        
        $result = $this->conn->query($sql);
        $recent_activities = [];
        while ($row = $result->fetch_assoc()) {
            $recent_activities[] = $row;
        }
        
        return [
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_activities' => $recent_activities
            ]
        ];
    }
    
    /**
     * Get all lahan
     */
    public function getLahan($anggota_id = null)
    {
        $sql = "SELECT l.*, a.nama_lengkap, a.no_anggota
                FROM ksp_agricultural_lahan l
                JOIN anggota a ON l.anggota_id = a.id";
        
        if ($anggota_id) {
            $sql .= " WHERE l.anggota_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $anggota_id);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }
        
        $lahan = [];
        while ($row = $result->fetch_assoc()) {
            $lahan[] = $row;
        }
        
        return [
            'success' => true,
            'data' => $lahan
        ];
    }
    
    /**
     * Get all tanaman
     */
    public function getTanaman()
    {
        $sql = "SELECT * FROM ksp_agricultural_tanaman WHERE is_active = 1 ORDER BY kategori, nama_tanaman";
        $result = $this->conn->query($sql);
        
        $tanaman = [];
        while ($row = $result->fetch_assoc()) {
            $tanaman[] = $row;
        }
        
        return [
            'success' => true,
            'data' => $tanaman
        ];
    }
    
    /**
     * Get planning tanam
     */
    public function getPlanning($status = null)
    {
        $sql = "SELECT p.*, a.nama_lengkap, a.no_anggota, t.nama_tanaman, l.nama_lahan, l.luas_lahan
                FROM ksp_agricultural_planning p
                JOIN anggota a ON p.anggota_id = a.id
                JOIN ksp_agricultural_tanaman t ON p.tanaman_id = t.id
                JOIN ksp_agricultural_lahan l ON p.lahan_id = l.id";
        
        if ($status) {
            $sql .= " WHERE p.status = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }
        
        $planning = [];
        while ($row = $result->fetch_assoc()) {
            $planning[] = $row;
        }
        
        return [
            'success' => true,
            'data' => $planning
        ];
    }
    
    /**
     * Create new planning
     */
    public function createPlanning($data)
    {
        // Validate required fields
        $required = ['anggota_id', 'lahan_id', 'tanaman_id', 'periode_tanam', 'luas_tanam'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Field {$field} is required"];
            }
        }
        
        // Get tanaman data for calculations
        $sql = "SELECT * FROM ksp_agricultural_tanaman WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $data['tanaman_id']);
        $stmt->execute();
        $tanaman = $stmt->get_result()->fetch_assoc();
        
        if (!$tanaman) {
            return ['success' => false, 'message' => 'Tanaman not found'];
        }
        
        // Calculate estimations
        $avg_productivity = ($tanaman['produktivitas_min'] + $tanaman['produktivitas_max']) / 2;
        $avg_price = ($tanaman['harga_jual_min'] + $tanaman['harga_jual_max']) / 2;
        
        $estimasi_produksi = $data['luas_tanam'] * $avg_productivity;
        $estimasi_pendapatan = $estimasi_produksi * $avg_price;
        $kebutuhan_modal = $estimasi_pendapatan * 0.3; // 30% dari estimasi pendapatan
        
        // Calculate dates
        $estimasi_tanam = date('Y-m-d', strtotime($data['periode_tanam'] . '-01'));
        $estimasi_panen = date('Y-m-d', strtotime($estimasi_tanam . ' +' . $tanaman['masa_tanam'] . ' days'));
        
        // Insert planning
        $sql = "INSERT INTO ksp_agricultural_planning 
                (anggota_id, lahan_id, tanaman_id, periode_tanam, luas_tanam, estimasi_tanam, estimasi_panen, 
                 estimasi_produksi, estimasi_pendapatan, kebutuhan_modal, status, catatan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'rencana', ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiisssdddds", 
            $data['anggota_id'],
            $data['lahan_id'],
            $data['tanaman_id'],
            $data['periode_tanam'],
            $data['luas_tanam'],
            $estimasi_tanam,
            $estimasi_panen,
            $estimasi_produksi,
            $estimasi_pendapatan,
            $kebutuhan_modal,
            $data['catatan'] ?? ''
        );
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Planning berhasil dibuat',
                'planning_id' => $this->conn->insert_id,
                'estimasi' => [
                    'produksi' => $estimasi_produksi,
                    'pendapatan' => $estimasi_pendapatan,
                    'modal' => $kebutuhan_modal
                ]
            ];
        } else {
            return ['success' => false, 'message' => 'Gagal membuat planning: ' . $stmt->error];
        }
    }
    
    /**
     * Update planning status
     */
    public function updatePlanningStatus($planning_id, $status, $data = [])
    {
        $sql = "UPDATE ksp_agricultural_planning SET status = ?";
        $params = [$status];
        $types = "s";
        
        // Add additional data based on status
        if ($status === 'proses_tanam' && !empty($data['tanggal_tanam'])) {
            $sql .= ", estimasi_tanam = ?";
            $params[] = $data['tanggal_tanam'];
            $types .= "s";
        } elseif ($status === 'dipanen' && !empty($data['hasil_panen'])) {
            $sql .= ", tanggal_panen = ?, hasil_panen = ?, total_pendapatan = ?";
            $params[] = $data['tanggal_panen'];
            $params[] = $data['hasil_panen'];
            $params[] = $data['total_pendapatan'] ?? 0;
            $types .= "sdd";
        }
        
        $sql .= ", updated_at = NOW() WHERE id = ?";
        $params[] = $planning_id;
        $types .= "i";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Status berhasil diupdate'];
        } else {
            return ['success' => false, 'message' => 'Gagal update status: ' . $stmt->error];
        }
    }
    
    /**
     * Get inventory
     */
    public function getInventory($jenis_barang = null)
    {
        $sql = "SELECT i.*, a.nama_lengkap, a.no_anggota,
                       CASE i.jenis_barang
                           WHEN 'pupuk' THEN (SELECT nama_pupuk FROM ksp_agricultural_pupuk WHERE id = i.barang_id)
                           WHEN 'pestisida' THEN (SELECT nama_pestisida FROM ksp_agricultural_pestisida WHERE id = i.barang_id)
                           WHEN 'benih' THEN (SELECT nama_tanaman FROM ksp_agricultural_tanaman WHERE id = i.barang_id)
                           ELSE 'Unknown'
                       END as nama_barang
                FROM ksp_agricultural_inventory i
                LEFT JOIN anggota a ON i.anggota_id = a.id";
        
        if ($jenis_barang) {
            $sql .= " WHERE i.jenis_barang = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $jenis_barang);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }
        
        $inventory = [];
        while ($row = $result->fetch_assoc()) {
            $inventory[] = $row;
        }
        
        return [
            'success' => true,
            'data' => $inventory
        ];
    }
    
    /**
     * Add inventory transaction
     */
    public function addInventoryTransaction($data)
    {
        $required = ['jenis_barang', 'barang_id', 'stok_masuk', 'satuan', 'harga_beli', 'tanggal_transaksi', 'jenis_transaksi'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Field {$field} is required"];
            }
        }
        
        // Calculate stok_akhir
        $sql = "SELECT COALESCE(SUM(stok_akhir), 0) as current_stock 
                FROM ksp_agricultural_inventory 
                WHERE jenis_barang = ? AND barang_id = ? AND anggota_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $data['jenis_barang'], $data['barang_id'], $data['anggota_id']);
        $stmt->execute();
        $current_stock = $stmt->get_result()->fetch_assoc()['current_stock'];
        
        $stok_akhir = $current_stock + $data['stok_masuk'];
        
        // Insert transaction
        $sql = "INSERT INTO ksp_agricultural_inventory 
                (jenis_barang, barang_id, anggota_id, stok_awal, stok_masuk, stok_keluar, stok_akhir, 
                 satuan, harga_beli, harga_jual, tanggal_transaksi, jenis_transaksi, keterangan)
                VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siiiddisssss", 
            $data['jenis_barang'],
            $data['barang_id'],
            $data['anggota_id'],
            $current_stock,
            $data['stok_masuk'],
            $stok_akhir,
            $data['satuan'],
            $data['harga_beli'],
            $data['harga_jual'] ?? $data['harga_beli'] * 1.1,
            $data['tanggal_transaksi'],
            $data['jenis_transaksi'],
            $data['keterangan'] ?? ''
        );
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Transaksi inventory berhasil ditambahkan',
                'stok_akhir' => $stok_akhir
            ];
        } else {
            return ['success' => false, 'message' => 'Gagal menambah transaksi: ' . $stmt->error];
        }
    }
    
    /**
     * Get agricultural statistics
     */
    public function getStatistics()
    {
        // Planning by status
        $sql = "SELECT status, COUNT(*) as count, SUM(luas_tanam) as total_luas
                FROM ksp_agricultural_planning
                GROUP BY status";
        $result = $this->conn->query($sql);
        $planning_stats = [];
        while ($row = $result->fetch_assoc()) {
            $planning_stats[] = $row;
        }
        
        // Tanaman distribution
        $sql = "SELECT t.nama_tanaman, COUNT(p.id) as count, SUM(p.luas_tanam) as total_luas
                FROM ksp_agricultural_planning p
                JOIN ksp_agricultural_tanaman t ON p.tanaman_id = t.id
                GROUP BY t.id, t.nama_tanaman
                ORDER BY count DESC";
        $result = $this->conn->query($sql);
        $tanaman_stats = [];
        while ($row = $result->fetch_assoc()) {
            $tanaman_stats[] = $row;
        }
        
        // Inventory summary
        $sql = "SELECT jenis_barang, SUM(stok_akhir) as total_stok, SUM(stok_akhir * harga_jual) as total_value
                FROM ksp_agricultural_inventory
                GROUP BY jenis_barang";
        $result = $this->conn->query($sql);
        $inventory_stats = [];
        while ($row = $result->fetch_assoc()) {
            $inventory_stats[] = $row;
        }
        
        return [
            'success' => true,
            'data' => [
                'planning_stats' => $planning_stats,
                'tanaman_stats' => $tanaman_stats,
                'inventory_stats' => $inventory_stats
            ]
        ];
    }
    
    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
