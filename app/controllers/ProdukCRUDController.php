<?php
/**
 * Produk CRUD Controller
 * Impact: Changes affect Admin, Member, and Sales dashboards
 */

require_once __DIR__ . '/MasterCRUDController.php';

class ProdukCRUDController extends MasterCRUDController {
    
    protected $moduleName = 'Produk';
    protected $tableName = 'produk';
    protected $primaryKey = 'id';
    protected $requiredFields = ['nama_produk', 'kategori_id', 'harga_jual'];
    protected $allowedRoles = ['admin', 'staff'];
    protected $viewPath = 'produk';
    
    public function __construct() {
        parent::__construct($this->moduleName, $this->tableName, $this->viewPath);
    }
    
    /**
     * Enhanced index with category and sales info
     */
    public function index() {
        $this->requireRole($this->allowedRoles);
        
        $page = intval($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $kategori = $_GET['kategori'] ?? '';
        $status = $_GET['status'] ?? '';
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Build query
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (p.nama_produk LIKE ? OR p.kode_produk LIKE ? OR p.deskripsi LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%");
        }
        
        if (!empty($kategori)) {
            $whereClause .= " AND p.kategori_id = ?";
            $params[] = $kategori;
        }
        
        if (!empty($status)) {
            $whereClause .= " AND p.is_active = ?";
            $params[] = $status === 'aktif' ? 1 : 0;
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM {$this->tableName} p 
                      LEFT JOIN kategori_produk k ON p.kategori_id = k.id 
                      $whereClause";
        $total = (fetchRow($countQuery, $params) ?? [])['total'] ?? 0;
        
        // Get data with category and sales info
        $dataQuery = "SELECT p.*, k.nama_kategori, 
                            (SELECT COUNT(*) FROM penjualan WHERE FIND_IN_SET(p.id, produk_ids) > 0) as total_penjualan,
                            (SELECT COALESCE(SUM(quantity), 0) FROM detail_penjualan WHERE produk_id = p.id) as total_terjual
                     FROM {$this->tableName} p 
                     LEFT JOIN kategori_produk k ON p.kategori_id = k.id 
                     $whereClause 
                     ORDER BY p.created_at DESC 
                     LIMIT $perPage OFFSET $offset";
        
        $data = fetchAll($dataQuery, $params);
        
        // Get statistics
        $stats = $this->getProdukStats();
        
        $this->render($this->viewPath . '/index', [
            'data' => $data,
            'stats' => $stats,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_next' => $page < ceil($total / $perPage),
                'has_prev' => $page > 1
            ],
            'search' => $search,
            'kategori' => $kategori,
            'status' => $status,
            'filters' => $this->getAvailableFilters()
        ]);
    }
    
    /**
     * Create new produk
     */
    public function create() {
        $this->requireRole($this->allowedRoles);
        
        $formData = $this->getProdukFormData();
        $this->render($this->viewPath . '/create', [
            'formData' => $formData,
            'action' => 'create',
            'categories' => $this->getCategories(),
            'next_kode' => $this->generateNextKode()
        ]);
    }
    
    /**
     * Store new produk
     */
    public function store() {
        $this->requireRole($this->allowedRoles);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method');
        }
        
        // Validate
        $errors = $this->validateProdukData($_POST);
        if (!empty($errors)) {
            $this->error('Validation failed: ' . implode(', ', $errors));
        }
        
        // Check duplicate kode
        if ($this->isKodeExists($_POST['kode_produk'])) {
            $this->error('Kode produk sudah ada');
        }
        
        $data = $this->prepareProdukData($_POST);
        
        try {
            beginTransaction();
            
            // Insert produk
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($values) - 1) . '?';
            
            $query = "INSERT INTO {$this->tableName} (" . implode(',', $fields) . ") VALUES ($placeholders)";
            $produkId = insertQuery($query, $values);
            
            // Handle image upload
            if (!empty($_FILES['gambar']['name'])) {
                $this->uploadProductImage($produkId, $_FILES['gambar']);
            }
            
            commit();
            
            // Log activity
            $this->logActivity('create', $produkId, $data);
            
            // Update dashboard stats
            $this->updateDashboardStats('produk_added', $data);
            
            $this->success("Produk {$data['nama_produk']} berhasil ditambahkan");
            
        } catch (Exception $e) {
            rollback();
            $this->error('Gagal menambahkan produk: ' . $e->getMessage());
        }
    }
    
    /**
     * Get produk statistics
     */
    protected function getProdukStats() {
        $stats = [
            'total_produk' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName}") ?? [])['count'] ?? 0,
            'produk_aktif' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE is_active = 1") ?? [])['count'] ?? 0,
            'total_kategori' => (fetchRow("SELECT COUNT(*) as count FROM kategori_produk") ?? [])['count'] ?? 0,
            'stok_total' => (fetchRow("SELECT COALESCE(SUM(stok), 0) as total FROM {$this->tableName}") ?? [])['total'] ?? 0,
            'stok_minimum' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE stok <= stok_minimal AND is_active = 1") ?? [])['count'] ?? 0,
            'total_penjualan_hari_ini' => (fetchRow("SELECT COUNT(*) as count FROM penjualan WHERE DATE(tanggal_penjualan) = CURDATE()") ?? [])['count'] ?? 0,
            'pendapatan_hari_ini' => (fetchRow("SELECT COALESCE(SUM(total_harga), 0) as total FROM penjualan WHERE DATE(tanggal_penjualan) = CURDATE()") ?? [])['total'] ?? 0
        ];
        
        return $stats;
    }
    
    /**
     * Get form data structure
     */
    protected function getProdukFormData($existingData = []) {
        return [
            'basic_info' => [
                'title' => 'Informasi Dasar Produk',
                'fields' => [
                    'kode_produk' => ['type' => 'text', 'label' => 'Kode Produk', 'required' => true, 'readonly' => true],
                    'nama_produk' => ['type' => 'text', 'label' => 'Nama Produk', 'required' => true],
                    'kategori_id' => ['type' => 'select', 'label' => 'Kategori', 'required' => true, 'options' => $this->getCategories()],
                    'deskripsi' => ['type' => 'textarea', 'label' => 'Deskripsi'],
                    'gambar' => ['type' => 'file', 'label' => 'Gambar Produk']
                ],
                'data' => $existingData
            ],
            'pricing_info' => [
                'title' => 'Informasi Harga',
                'fields' => [
                    'harga_beli' => ['type' => 'number', 'label' => 'Harga Beli', 'step' => '0.01'],
                    'harga_jual' => ['type' => 'number', 'label' => 'Harga Jual', 'required' => true, 'step' => '0.01']
                ],
                'data' => $existingData
            ],
            'inventory_info' => [
                'title' => 'Informasi Stok',
                'fields' => [
                    'stok' => ['type' => 'number', 'label' => 'Stok Awal', 'required' => true],
                    'stok_minimum' => ['type' => 'number', 'label' => 'Stok Minimum'],
                    'satuan' => ['type' => 'text', 'label' => 'Satuan'],
                    'berat' => ['type' => 'number', 'label' => 'Berat (kg)', 'step' => '0.01'],
                    'dimensi' => ['type' => 'text', 'label' => 'Dimensi (PxLxT)']
                ],
                'data' => $existingData
            ],
            'status_info' => [
                'title' => 'Status Produk',
                'fields' => [
                    'is_active' => ['type' => 'select', 'label' => 'Status', 'options' => [
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif'
                    ]]
                ],
                'data' => $existingData
            ]
        ];
    }
    
    /**
     * Helper methods
     */
    private function validateProdukData($data) {
        $errors = [];
        
        if (empty($data['nama_produk'])) $errors[] = 'Nama produk wajib diisi';
        if (empty($data['kategori_id'])) $errors[] = 'Kategori wajib dipilih';
        if (empty($data['harga_jual']) || $data['harga_jual'] <= 0) $errors[] = 'Harga jual harus lebih dari 0';
        if (isset($data['stok']) && $data['stok'] < 0) $errors[] = 'Stok tidak boleh negatif';
        
        return $errors;
    }
    
    private function isKodeExists($kode, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE kode_produk = ?";
        $params = [$kode];
        
        if ($excludeId) {
            $query .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }
        
        return (fetchRow($query, $params) ?? [])['count'] ?? 0 > 0;
    }
    
    private function generateNextKode() {
        $prefix = 'PR';
        $date = date('Ymd');
        
        $last = fetchRow("SELECT kode_produk FROM {$this->tableName} WHERE kode_produk LIKE ? ORDER BY kode_produk DESC LIMIT 1", ["{$prefix}{$date}%"]);
        
        if ($last) {
            $lastNumber = intval(substr($last['kode_produk'], -3));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . $date . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    private function prepareProdukData($data) {
        $prepared = [
            'kode_produk' => $data['kode_produk'],
            'nama_produk' => $data['nama_produk'],
            'kategori_id' => $data['kategori_id'],
            'deskripsi' => $data['deskripsi'] ?? '',
            'harga_beli' => $data['harga_beli'] ?? 0,
            'harga_jual' => $data['harga_jual'],
            'stok' => $data['stok'] ?? 0,
            'stok_minimal' => $data['stok_minimum'] ?? 0,
            'satuan' => $data['satuan'] ?? 'pcs',
            'is_active' => $data['is_active'] ?? 1,
            'created_by' => $this->user['id'] ?? null
        ];
        
        if (!isset($data['id'])) {
            $prepared['created_at'] = date('Y-m-d H:i:s');
        }
        
        return $prepared;
    }
    
    private function getCategories() {
        $categories = fetchAll("SELECT id, nama_kategori FROM kategori_produk WHERE status = 'aktif' ORDER BY nama_kategori");
        $options = [];
        foreach ($categories as $category) {
            $options[$category['id']] = $category['nama_kategori'];
        }
        return $options;
    }
    
    private function getActiveProducts() {
        $products = fetchAll("SELECT id, nama_produk, kode_produk, harga_jual, stok FROM produk WHERE is_active = 1 ORDER BY nama_produk");
        $options = [];
        foreach ($products as $product) {
            $options[$product['id']] = "{$product['nama_produk']} ({$product['kode_produk']}) - Stok: {$product['stok']} - Rp " . number_format($product['harga_jual'], 0, ',', '.');
        }
        return $options;
    }
    
    private function uploadProductImage($produkId, $file) {
        $uploadDir = __DIR__ . '/../../public/assets/images/produk/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = 'produk_' . $produkId . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            updateQuery("UPDATE {$this->tableName} SET gambar = ? WHERE {$this->primaryKey} = ?", [$fileName, $produkId]);
        }
    }
    
    private function getAvailableFilters() {
        return [
            'kategori' => ['all' => 'Semua Kategori'] + $this->getCategories(),
            'status' => [
                'all' => 'Semua Status',
                'aktif' => 'Aktif',
                'nonaktif' => 'Tidak Aktif',
                'discontinue' => 'Discontinue'
            ]
        ];
    }
    
    private function updateDashboardStats($action, $newData = [], $oldData = []) {
        // Update dashboard statistics affecting all roles
    }
}
?>
