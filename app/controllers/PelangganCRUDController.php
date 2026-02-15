<?php
/**
 * Pelanggan CRUD Controller
 * Impact: Changes affect Admin, Sales, and Customer Service dashboards
 */

require_once __DIR__ . '/MasterCRUDController.php';

class PelangganCRUDController extends MasterCRUDController {
    
    protected $moduleName = 'Pelanggan';
    protected $tableName = 'pelanggan';
    protected $primaryKey = 'id';
    protected $requiredFields = ['nama_pelanggan', 'email'];
    protected $allowedRoles = ['admin', 'staff'];
    protected $viewPath = 'pelanggan';
    
    public function __construct() {
        parent::__construct($this->moduleName, $this->tableName, $this->viewPath);
    }
    
    /**
     * Enhanced index with purchase history
     */
    public function index() {
        $this->requireRole($this->allowedRoles);
        
        $page = intval($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $tipe = $_GET['tipe'] ?? '';
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Build query
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (nama_pelanggan LIKE ? OR email LIKE ? OR no_hp LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%");
        }
        
        if (!empty($status)) {
            $whereClause .= " AND status = ?";
            $params[] = $status;
        }
        
        if (!empty($tipe)) {
            $whereClause .= " AND jenis_pelanggan = ?";
            $params[] = $tipe;
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM {$this->tableName} $whereClause";
        $total = (fetchRow($countQuery, $params) ?? [])['total'] ?? 0;
        
        // Get data with purchase history
        $dataQuery = "SELECT p.*, 
                            (SELECT COUNT(*) FROM penjualan WHERE pelanggan_id = p.id) as total_transaksi,
                            (SELECT COALESCE(SUM(total_harga), 0) FROM penjualan WHERE pelanggan_id = p.id) as total_pembelanjaan,
                            (SELECT MAX(created_at) FROM penjualan WHERE pelanggan_id = p.id) as terakhir_beli
                     FROM {$this->tableName} p 
                     $whereClause 
                     ORDER BY p.created_at DESC 
                     LIMIT $perPage OFFSET $offset";
        
        $data = fetchAll($dataQuery, $params);
        
        // Get statistics
        $stats = $this->getPelangganStats();
        
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
            'tipe' => $tipe,
            'filters' => $this->getAvailableFilters()
        ]);
    }
    
    /**
     * Create new pelanggan
     */
    public function create() {
        $this->requireRole($this->allowedRoles);
        
        $formData = $this->getPelangganFormData();
        $this->render($this->viewPath . '/create', [
            'formData' => $formData,
            'action' => 'create',
            'next_kode' => $this->generateNextKode()
        ]);
    }
    
    /**
     * Store new pelanggan
     */
    public function store() {
        $this->requireRole($this->allowedRoles);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method');
        }
        
        // Validate
        $errors = $this->validatePelangganData($_POST);
        if (!empty($errors)) {
            $this->error('Validation failed: ' . implode(', ', $errors));
        }
        
        // Check duplicate email
        if ($this->isEmailExists($_POST['email'])) {
            $this->error('Email sudah terdaftar');
        }
        
        // Check duplicate phone
        if ($this->isPhoneExists($_POST['no_hp'])) {
            $this->error('No. HP sudah terdaftar');
        }
        
        $data = $this->preparePelangganData($_POST);
        
        try {
            // Insert pelanggan
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($values) - 1) . '?';
            
            $query = "INSERT INTO {$this->tableName} (" . implode(',', $fields) . ") VALUES ($placeholders)";
            $pelangganId = insertQuery($query, $values);
            
            // Log activity
            $this->logActivity('create', $pelangganId, $data);
            
            // Update dashboard stats
            $this->updateDashboardStats('pelanggan_added', $data);
            
            $this->success("Pelanggan {$data['nama_pelanggan']} berhasil ditambahkan");
            
        } catch (Exception $e) {
            $this->error('Gagal menambahkan pelanggan: ' . $e->getMessage());
        }
    }
    
    /**
     * Get pelanggan statistics
     */
    protected function getPelangganStats() {
        $stats = [
            'total_pelanggan' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName}") ?? [])['count'] ?? 0,
            'pelanggan_aktif' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = 'aktif'") ?? [])['count'] ?? 0,
            'pelanggan_baru_bulan_ini' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)") ?? [])['count'] ?? 0,
            'pelanggan_perusahaan' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE tipe_pelanggan = 'perusahaan'") ?? [])['count'] ?? 0,
            'pelanggan_individu' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE tipe_pelanggan = 'individu'") ?? [])['count'] ?? 0,
            'total_pembelanjaan' => (fetchRow("SELECT COALESCE(SUM(total_harga), 0) as total FROM penjualan") ?? [])['total'] ?? 0,
            'rata_rata_pembelanjaan' => (fetchRow("SELECT COALESCE(AVG(total_pembelanjaan), 0) as avg FROM (SELECT COALESCE(SUM(total_harga), 0) as total_pembelanjaan FROM penjualan GROUP BY pelanggan_id) as customer_stats") ?? [])['avg'] ?? 0,
            'pelanggan_tidak_aktif' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = 'tidak_aktif'") ?? [])['count'] ?? 0
        ];
        
        return $stats;
    }
    
    /**
     * Get form data structure
     */
    protected function getPelangganFormData($existingData = []) {
        return [
            'personal_info' => [
                'title' => 'Informasi Pribadi',
                'fields' => [
                    'kode_pelanggan' => ['type' => 'text', 'label' => 'Kode Pelanggan', 'required' => true, 'readonly' => true],
                    'nama_pelanggan' => ['type' => 'text', 'label' => 'Nama Lengkap', 'required' => true],
                    'email' => ['type' => 'email', 'label' => 'Email', 'required' => true],
                    'no_hp' => ['type' => 'text', 'label' => 'No. HP', 'required' => true],
                    'no_telp' => ['type' => 'text', 'label' => 'No. Telepon'],
                    'tanggal_lahir' => ['type' => 'date', 'label' => 'Tanggal Lahir'],
                    'jenis_kelamin' => ['type' => 'select', 'label' => 'Jenis Kelamin', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']]
                ],
                'data' => $existingData
            ],
            'address_info' => [
                'title' => 'Informasi Alamat',
                'fields' => [
                    'alamat_lengkap' => ['type' => 'textarea', 'label' => 'Alamat Lengkap'],
                    'kelurahan' => ['type' => 'text', 'label' => 'Kelurahan'],
                    'kecamatan' => ['type' => 'text', 'label' => 'Kecamatan'],
                    'kabupaten' => ['type' => 'text', 'label' => 'Kabupaten'],
                    'provinsi' => ['type' => 'text', 'label' => 'Provinsi'],
                    'kode_pos' => ['type' => 'text', 'label' => 'Kode Pos']
                ],
                'data' => $existingData
            ],
            'business_info' => [
                'title' => 'Informasi Bisnis',
                'fields' => [
                    'tipe_pelanggan' => ['type' => 'select', 'label' => 'Tipe Pelanggan', 'options' => [
                        'individu' => 'Individu',
                        'perusahaan' => 'Perusahaan'
                    ]],
                    'perusahaan' => ['type' => 'text', 'label' => 'Nama Perusahaan'],
                    'jabatan' => ['type' => 'text', 'label' => 'Jabatan'],
                    'npwp' => ['type' => 'text', 'label' => 'NPWP'],
                    'bidang_usaha' => ['type' => 'text', 'label' => 'Bidang Usaha']
                ],
                'data' => $existingData
            ],
            'preferences_info' => [
                'title' => 'Preferensi & Status',
                'fields' => [
                    'status' => ['type' => 'select', 'label' => 'Status', 'options' => [
                        'aktif' => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                        'blacklist' => 'Blacklist'
                    ]],
                    'catatan' => ['type' => 'textarea', 'label' => 'Catatan'],
                    'preferensi_kontak' => ['type' => 'select', 'label' => 'Preferensi Kontak', 'options' => [
                        'email' => 'Email',
                        'whatsapp' => 'WhatsApp',
                        'telepon' => 'Telepon',
                        'sms' => 'SMS'
                    ]]
                ],
                'data' => $existingData
            ]
        ];
    }
    
    /**
     * Helper methods
     */
    private function validatePelangganData($data) {
        $errors = [];
        
        if (empty($data['nama_pelanggan'])) $errors[] = 'Nama pelanggan wajib diisi';
        if (empty($data['email'])) $errors[] = 'Email wajib diisi';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid';
        if (empty($data['no_hp'])) $errors[] = 'No. HP wajib diisi';
        
        return $errors;
    }
    
    private function isEmailExists($email, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $query .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }
        
        return (fetchRow($query, $params) ?? [])['count'] ?? 0 > 0;
    }
    
    private function isPhoneExists($phone, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE no_hp = ?";
        $params = [$phone];
        
        if ($excludeId) {
            $query .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }
        
        return (fetchRow($query, $params) ?? [])['count'] ?? 0 > 0;
    }
    
    private function generateNextKode() {
        $prefix = 'CUS';
        $date = date('Ymd');
        
        $last = fetchRow("SELECT kode_pelanggan FROM {$this->tableName} WHERE kode_pelanggan LIKE ? ORDER BY kode_pelanggan DESC LIMIT 1", ["{$prefix}{$date}%"]);
        
        if ($last) {
            $lastNumber = intval(substr($last['kode_pelanggan'], -3));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . $date . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    private function preparePelangganData($data) {
        $prepared = [
            'kode_pelanggan' => $data['kode_pelanggan'],
            'nama_pelanggan' => $data['nama_pelanggan'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'],
            'no_telp' => $data['no_telp'] ?? '',
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? '',
            'alamat_lengkap' => $data['alamat_lengkap'] ?? '',
            'kelurahan' => $data['kelurahan'] ?? '',
            'kecamatan' => $data['kecamatan'] ?? '',
            'kabupaten' => $data['kabupaten'] ?? '',
            'provinsi' => $data['provinsi'] ?? '',
            'kode_pos' => $data['kode_pos'] ?? '',
            'tipe_pelanggan' => $data['tipe_pelanggan'] ?? 'individu',
            'perusahaan' => $data['perusahaan'] ?? '',
            'jabatan' => $data['jabatan'] ?? '',
            'npwp' => $data['npwp'] ?? '',
            'bidang_usaha' => $data['bidang_usaha'] ?? '',
            'status' => $data['status'] ?? 'aktif',
            'catatan' => $data['catatan'] ?? '',
            'preferensi_kontak' => $data['preferensi_kontak'] ?? 'email',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->user['id'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->user['id'] ?? null
        ];
        
        return $prepared;
    }
    
    private function getAvailableFilters() {
        return [
            'tipe' => [
                'all' => 'Semua Tipe',
                'individu' => 'Individu',
                'perusahaan' => 'Perusahaan'
            ],
            'status' => [
                'all' => 'Semua Status',
                'aktif' => 'Aktif',
                'tidak_aktif' => 'Tidak Aktif',
                'blacklist' => 'Blacklist'
            ]
        ];
    }
    
    private function updateDashboardStats($action, $newData = [], $oldData = []) {
        // Update dashboard statistics affecting all roles
    }
}
?>
