<?php
/**
 * Penjualan CRUD Controller
 * Impact: Changes affect Admin, Member, and Sales dashboards
 */

require_once __DIR__ . '/MasterCRUDController.php';

class PenjualanCRUDController extends MasterCRUDController {
    
    protected $moduleName = 'Penjualan';
    protected $tableName = 'penjualan';
    protected $primaryKey = 'id';
    protected $requiredFields = ['total_harga'];
    protected $allowedRoles = ['admin', 'staff'];
    protected $viewPath = 'penjualan';
    
    public function __construct() {
        parent::__construct($this->moduleName, $this->tableName, $this->viewPath);
    }
    
    /**
     * Enhanced index with product and customer info
     */
    public function index() {
        $this->requireRole($this->allowedRoles);
        
        $page = intval($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $tanggal = $_GET['tanggal'] ?? '';
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Build query
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (pen.no_faktur LIKE ? OR pel.nama_pelanggan LIKE ?)";
            array_push($params, "%$search%", "%$search%");
        }
        
        if (!empty($status)) {
            $whereClause .= " AND pen.status_pembayaran = ?";
            $params[] = $status;
        }
        
        if (!empty($tanggal)) {
            $whereClause .= " AND DATE(pen.tanggal_penjualan) = ?";
            $params[] = $tanggal;
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM {$this->tableName} pen 
                      LEFT JOIN pelanggan pel ON pen.pelanggan_id = pel.id 
                      $whereClause";
        $total = (fetchRow($countQuery, $params) ?? [])['total'] ?? 0;
        
        // Get data with customer info
        $dataQuery = "SELECT pen.*, pel.nama_pelanggan, pel.no_hp,
                            (SELECT COUNT(*) FROM detail_penjualan WHERE penjualan_id = pen.id) as total_items
                     FROM {$this->tableName} pen 
                     LEFT JOIN pelanggan pel ON pen.pelanggan_id = pel.id 
                     $whereClause 
                     ORDER BY pen.tanggal_penjualan DESC 
                     LIMIT $perPage OFFSET $offset";
        
        $data = fetchAll($dataQuery, $params);
        
        // Get statistics
        $stats = $this->getPenjualanStats();
        
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
            'status' => $status,
            'tanggal' => $tanggal,
            'filters' => $this->getAvailableFilters()
        ]);
    }
    
    /**
     * Create new penjualan
     */
    public function create() {
        $this->requireRole($this->allowedRoles);
        
        $formData = $this->getPenjualanFormData();
        $this->render($this->viewPath . '/create', [
            'formData' => $formData,
            'action' => 'create',
            'products' => $this->getActiveProducts(),
            'customers' => $this->getCustomers(),
            'next_no_transaksi' => $this->generateNextNoTransaksi()
        ]);
    }
    
    /**
     * Store new penjualan
     */
    public function store() {
        $this->requireRole($this->allowedRoles);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method');
        }
        
        // Validate
        $errors = $this->validatePenjualanData($_POST);
        if (!empty($errors)) {
            $this->error('Validation failed: ' . implode(', ', $errors));
        }
        
        $data = $this->preparePenjualanData($_POST);
        
        try {
            beginTransaction();
            
            // Insert penjualan
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($values) - 1) . '?';
            
            $query = "INSERT INTO {$this->tableName} (" . implode(',', $fields) . ") VALUES ($placeholders)";
            $penjualanId = insertQuery($query, $values);
            
            // Update product stock
            $this->updateProductStock($data['produk_id'], $data['quantity']);
            
            // Create detail penjualan if multiple items
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                $this->createDetailPenjualan($penjualanId, $_POST['items']);
            }
            
            commit();
            
            // Log activity
            $this->logActivity('create', $penjualanId, $data);
            
            // Update dashboard stats
            $this->updateDashboardStats('penjualan_added', $data);
            
            $this->success("Penjualan berhasil ditambahkan");
            
        } catch (Exception $e) {
            rollback();
            $this->error('Gagal menambahkan penjualan: ' . $e->getMessage());
        }
    }
    
    /**
     * Get penjualan statistics
     */
    protected function getPenjualanStats() {
        $stats = [
            'total_penjualan' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName}") ?? [])['count'] ?? 0,
            'penjualan_hari_ini' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE DATE(created_at) = CURDATE()") ?? [])['count'] ?? 0,
            'total_pendapatan' => (fetchRow("SELECT COALESCE(SUM(total_harga), 0) as total FROM {$this->tableName}") ?? [])['total'] ?? 0,
            'pendapatan_hari_ini' => (fetchRow("SELECT COALESCE(SUM(total_harga), 0) as total FROM {$this->tableName} WHERE DATE(created_at) = CURDATE()") ?? [])['total'] ?? 0,
            'pendapatan_bulan_ini' => (fetchRow("SELECT COALESCE(SUM(total_harga), 0) as total FROM {$this->tableName} WHERE MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)") ?? [])['total'] ?? 0,
            'total_pelanggan' => (fetchRow("SELECT COUNT(*) as count FROM pelanggan") ?? [])['count'] ?? 0,
            'produk_terlaris' => $this->getTopProduct(),
            'rata_rata_transaksi' => (fetchRow("SELECT COALESCE(AVG(total_harga), 0) as avg FROM {$this->tableName}") ?? [])['avg'] ?? 0
        ];
        
        return $stats;
    }
    
    /**
     * Get form data structure
     */
    protected function getPenjualanFormData($existingData = []) {
        return [
            'transaction_info' => [
                'title' => 'Informasi Transaksi',
                'fields' => [
                    'no_transaksi' => ['type' => 'text', 'label' => 'No. Transaksi', 'required' => true, 'readonly' => true],
                    'tanggal_transaksi' => ['type' => 'datetime-local', 'label' => 'Tanggal Transaksi', 'required' => true],
                    'pelanggan_id' => ['type' => 'select', 'label' => 'Pelanggan', 'options' => $this->getCustomers()],
                    'metode_pembayaran' => ['type' => 'select', 'label' => 'Metode Pembayaran', 'options' => [
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'debit' => 'Kartu Debit',
                        'credit' => 'Kartu Kredit',
                        'ewallet' => 'E-Wallet'
                    ]]
                ],
                'data' => $existingData
            ],
            'product_info' => [
                'title' => 'Informasi Produk',
                'fields' => [
                    'produk_id' => ['type' => 'select', 'label' => 'Produk', 'required' => true, 'options' => $this->getActiveProducts()],
                    'quantity' => ['type' => 'number', 'label' => 'Quantity', 'required' => true, 'min' => 1],
                    'harga_satuan' => ['type' => 'number', 'label' => 'Harga Satuan', 'required' => true, 'step' => '0.01', 'readonly' => true],
                    'diskon' => ['type' => 'number', 'label' => 'Diskon', 'step' => '0.01'],
                    'total_harga' => ['type' => 'number', 'label' => 'Total Harga', 'required' => true, 'step' => '0.01', 'readonly' => true]
                ],
                'data' => $existingData
            ],
            'additional_info' => [
                'title' => 'Informasi Tambahan',
                'fields' => [
                    'catatan' => ['type' => 'textarea', 'label' => 'Catatan'],
                    'status' => ['type' => 'select', 'label' => 'Status', 'options' => [
                        'pending' => 'Pending',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'returned' => 'Dikembalikan'
                    ]]
                ],
                'data' => $existingData
            ]
        ];
    }
    
    /**
     * Helper methods
     */
    private function validatePenjualanData($data) {
        $errors = [];
        
        if (empty($data['produk_id'])) $errors[] = 'Produk wajib dipilih';
        if (empty($data['quantity']) || $data['quantity'] <= 0) $errors[] = 'Quantity harus lebih dari 0';
        if (empty($data['harga_satuan']) || $data['harga_satuan'] <= 0) $errors[] = 'Harga satuan harus lebih dari 0';
        if (empty($data['total_harga']) || $data['total_harga'] <= 0) $errors[] = 'Total harga harus lebih dari 0';
        
        // Check stock availability
        $product = fetchRow("SELECT stok FROM produk WHERE id = ?", [$data['produk_id']]);
        if ($product && $product['stok'] < $data['quantity']) {
            $errors[] = 'Stok tidak mencukupi';
        }
        
        return $errors;
    }
    
    private function generateNextNoTransaksi() {
        $prefix = 'TRX';
        $date = date('Ymd');
        
        $last = fetchRow("SELECT no_transaksi FROM {$this->tableName} WHERE no_transaksi LIKE ? ORDER BY no_transaksi DESC LIMIT 1", ["{$prefix}{$date}%"]);
        
        if ($last) {
            $lastNumber = intval(substr($last['no_transaksi'], -4));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . $date . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    private function preparePenjualanData($data) {
        $prepared = [
            'no_transaksi' => $data['no_transaksi'],
            'tanggal_transaksi' => $data['tanggal_transaksi'] ?? date('Y-m-d H:i:s'),
            'pelanggan_id' => $data['pelanggan_id'] ?? null,
            'produk_id' => $data['produk_id'],
            'quantity' => $data['quantity'],
            'harga_satuan' => $data['harga_satuan'],
            'diskon' => $data['diskon'] ?? 0,
            'total_harga' => $data['total_harga'],
            'metode_pembayaran' => $data['metode_pembayaran'] ?? 'cash',
            'catatan' => $data['catatan'] ?? '',
            'status' => $data['status'] ?? 'completed',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->user['id'] ?? null
        ];
        
        return $prepared;
    }
    
    private function getActiveProducts() {
        $products = fetchAll("SELECT id, nama_produk, kode_produk, harga, stok FROM produk WHERE status = 'aktif' AND stok > 0 ORDER BY nama_produk");
        $options = [];
        foreach ($products as $product) {
            $options[$product['id']] = "{$product['nama_produk']} ({$product['kode_produk']}) - Stok: {$product['stok']} - Rp " . number_format($product['harga'], 0, ',', '.');
        }
        return $options;
    }
    
    private function getCustomers() {
        $customers = fetchAll("SELECT id, nama_pelanggan, no_hp FROM pelanggan WHERE status = 'aktif' ORDER BY nama_pelanggan");
        $options = ['' => 'Pilih Pelanggan'] + array_column($customers, 'nama_pelanggan', 'id');
        return $options;
    }
    
    private function updateProductStock($productId, $quantity) {
        updateQuery("UPDATE produk SET stok = stok - ?, updated_at = NOW() WHERE id = ?", [$quantity, $productId]);
    }
    
    private function createDetailPenjualan($penjualanId, $items) {
        foreach ($items as $item) {
            $detailData = [
                'penjualan_id' => $penjualanId,
                'produk_id' => $item['produk_id'],
                'quantity' => $item['quantity'],
                'harga_satuan' => $item['harga_satuan'],
                'subtotal' => $item['subtotal'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $fields = array_keys($detailData);
            $values = array_values($detailData);
            $placeholders = str_repeat('?,', count($values) - 1) . '?';
            
            $query = "INSERT INTO detail_penjualan (" . implode(',', $fields) . ") VALUES ($placeholders)";
            insertQuery($query, $values);
            
            // Update stock for each item
            $this->updateProductStock($item['produk_id'], $item['quantity']);
        }
    }
    
    private function getTopProduct() {
        $top = fetchRow("SELECT p.nama_produk, COUNT(*) as total FROM penjualan pen 
                         JOIN produk p ON pen.produk_id = p.id 
                         GROUP BY pen.produk_id 
                         ORDER BY total DESC LIMIT 1");
        return $top ? $top['nama_produk'] : 'N/A';
    }
    
    private function getAvailableFilters() {
        return [
            'status' => [
                'all' => 'Semua Status',
                'pending' => 'Pending',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                'returned' => 'Dikembalikan'
            ]
        ];
    }
    
    private function updateDashboardStats($action, $newData = [], $oldData = []) {
        // Update dashboard statistics affecting all roles
    }
}
?>
