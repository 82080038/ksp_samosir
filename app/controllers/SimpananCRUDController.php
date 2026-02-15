<?php
/**
 * Simpanan CRUD Controller
 * Impact: Changes affect Admin, Member, and Pengawas dashboards
 */

require_once __DIR__ . '/MasterCRUDController.php';

class SimpananCRUDController extends MasterCRUDController {
    
    protected $moduleName = 'Simpanan';
    protected $tableName = 'simpanan';
    protected $primaryKey = 'id';
    protected $requiredFields = ['anggota_id', 'jenis_simpanan', 'no_rekening'];
    protected $allowedRoles = ['admin', 'staff'];
    protected $viewPath = 'simpanan';
    
    public function __construct() {
        parent::__construct($this->moduleName, $this->tableName, $this->viewPath);
    }
    
    /**
     * Enhanced index with member details
     * Impact: Shows comprehensive data in all dashboards
     */
    public function index() {
        $this->requireRole($this->allowedRoles);
        
        $page = intval($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $jenis = $_GET['jenis'] ?? '';
        $status = $_GET['status'] ?? '';
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Build query
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (a.nama LIKE ? OR s.no_rekening LIKE ? OR a.email LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%");
        }
        
        if (!empty($jenis)) {
            $whereClause .= " AND s.jenis_simpanan = ?";
            $params[] = $jenis;
        }
        
        if (!empty($status)) {
            $whereClause .= " AND s.status = ?";
            $params[] = $status;
        }
        
        // Role-based filtering
        if ($this->role === 'staff') {
            $whereClause .= " AND s.status = 'active'";
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM {$this->tableName} s 
                      JOIN anggota a ON s.anggota_id = a.id 
                      $whereClause";
        $total = (fetchRow($countQuery, $params) ?? [])['total'] ?? 0;
        
        // Get data with member info
        $dataQuery = "SELECT s.*, a.nama as nama_anggota, a.email, a.no_hp,
                            (SELECT COUNT(*) FROM transaksi_simpanan WHERE simpanan_id = s.id) as total_transaksi
                     FROM {$this->tableName} s 
                     JOIN anggota a ON s.anggota_id = a.id 
                     $whereClause 
                     ORDER BY s.created_at DESC 
                     LIMIT $perPage OFFSET $offset";
        
        $data = fetchAll($dataQuery, $params);
        
        // Get statistics
        $stats = $this->getSimpananStats();
        
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
            'jenis' => $jenis,
            'status' => $status,
            'filters' => $this->getAvailableFilters()
        ]);
    }
    
    /**
     * Create new simpanan
     * Impact: New account visible in member portal
     */
    public function create() {
        $this->requireRole($this->allowedRoles);
        
        $formData = $this->getSimpananFormData();
        $this->render($this->viewPath . '/create', [
            'formData' => $formData,
            'action' => 'create',
            'members' => $this->getActiveMembers(),
            'next_no_rekening' => $this->generateNextNoRekening($_GET['jenis'] ?? 'wajib')
        ]);
    }
    
    /**
     * Store new simpanan
     * Impact: Affects member dashboard and admin statistics
     */
    public function store() {
        $this->requireRole($this->allowedRoles);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method');
        }
        
        // Validate
        $errors = $this->validateSimpananData($_POST);
        if (!empty($errors)) {
            $this->error('Validation failed: ' . implode(', ', $errors));
        }
        
        // Check duplicate rekening
        if ($this->isRekeningExists($_POST['no_rekening'])) {
            $this->error('No. Rekening sudah ada');
        }
        
        $data = $this->prepareSimpananData($_POST);
        
        try {
            beginTransaction();
            
            // Insert simpanan
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($values) - 1) . '?';
            
            $query = "INSERT INTO {$this->tableName} (" . implode(',', $fields) . ") VALUES ($placeholders)";
            $simpananId = insertQuery($query, $values);
            
            // Create initial transaction if deposit amount provided
            if (!empty($_POST['initial_deposit']) && $_POST['initial_deposit'] > 0) {
                $this->createInitialTransaction($simpananId, $_POST['initial_deposit'], $_POST['keterangan'] ?? 'Setoran awal');
            }
            
            commit();
            
            // Log activity
            $this->logActivity('create', $simpananId, $data);
            
            // Update dashboard stats
            $this->updateDashboardStats('simpanan_added', $data);
            
            $this->success("Simpanan berhasil ditambahkan");
            
        } catch (Exception $e) {
            rollback();
            $this->error('Gagal menambahkan simpanan: ' . $e->getMessage());
        }
    }
    
    /**
     * Update simpanan
     * Impact: Changes reflected in member portal
     */
    public function update($id) {
        $this->requireRole($this->allowedRoles);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method');
        }
        
        $existing = fetchRow("SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = ?", [$id]);
        if (!$existing) {
            $this->error('Data tidak ditemukan');
        }
        
        // Validate
        $errors = $this->validateSimpananData($_POST, $id);
        if (!empty($errors)) {
            $this->error('Validation failed: ' . implode(', ', $errors));
        }
        
        $data = $this->prepareSimpananData($_POST);
        
        try {
            // Update simpanan
            $fields = [];
            $values = [];
            
            foreach ($data as $field => $value) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
            $values[] = $id;
            
            $query = "UPDATE {$this->tableName} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = ?";
            updateQuery($query, $values);
            
            // Log activity
            $this->logActivity('update', $id, $data, $existing);
            
            // Update dashboard stats
            $this->updateDashboardStats('simpanan_updated', $data, $existing);
            
            $this->success("Simpanan berhasil diperbarui");
            
        } catch (Exception $e) {
            $this->error('Gagal memperbarui simpanan: ' . $e->getMessage());
        }
    }
    
    /**
     * Get simpanan statistics
     * Impact: Stats shown in all role dashboards
     */
    protected function getSimpananStats() {
        try {
            $stats = [
                'total_rekening' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = 'active'") ?? [])['count'] ?? 0,
                'total_saldo' => (fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM {$this->tableName} WHERE status = 'active'") ?? [])['total'] ?? 0,
                'simpanan_wajib' => (fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM {$this->tableName} WHERE jenis_simpanan = 'wajib' AND status = 'active'") ?? [])['total'] ?? 0,
                'simpanan_sukarela' => (fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM {$this->tableName} WHERE jenis_simpanan = 'sukarela' AND status = 'active'") ?? [])['total'] ?? 0,
                'simpanan_berjangka' => (fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM {$this->tableName} WHERE jenis_simpanan = 'berjangka' AND status = 'active'") ?? [])['total'] ?? 0,
                'total_transaksi_hari_ini' => (fetchRow("SELECT COUNT(*) as count FROM transaksi_simpanan WHERE DATE(created_at) = CURDATE()") ?? [])['count'] ?? 0,
                'setoran_hari_ini' => (fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi_simpanan WHERE DATE(created_at) = CURDATE() AND jenis_transaksi = 'setoran'") ?? [])['total'] ?? 0
            ];
        } catch (Exception $e) {
            $stats = ['total_rekening' => 0, 'total_saldo' => 0, 'simpanan_wajib' => 0, 'simpanan_sukarela' => 0, 'simpanan_berjangka' => 0, 'total_transaksi_hari_ini' => 0, 'setoran_hari_ini' => 0];
        }
        return $stats;
    }
    
    /**
     * Get form data structure
     */
    protected function getSimpananFormData($existingData = []) {
        return [
            'account_info' => [
                'title' => 'Informasi Rekening',
                'fields' => [
                    'anggota_id' => ['type' => 'select', 'label' => 'Anggota', 'required' => true, 'options' => $this->getActiveMembers()],
                    'jenis_simpanan' => ['type' => 'select', 'label' => 'Jenis Simpanan', 'required' => true, 'options' => [
                        'wajib' => 'Simpanan Wajib',
                        'sukarela' => 'Simpanan Sukarela',
                        'berjangka' => 'Simpanan Berjangka'
                    ]],
                    'no_rekening' => ['type' => 'text', 'label' => 'No. Rekening', 'required' => true, 'readonly' => true],
                    'status' => ['type' => 'select', 'label' => 'Status', 'options' => ['active' => 'Aktif', 'inactive' => 'Tidak Aktif']]
                ],
                'data' => $existingData
            ],
            'initial_deposit' => [
                'title' => 'Setoran Awal (Opsional)',
                'fields' => [
                    'initial_deposit' => ['type' => 'number', 'label' => 'Jumlah Setoran Awal'],
                    'keterangan' => ['type' => 'textarea', 'label' => 'Keterangan']
                ],
                'data' => $existingData
            ]
        ];
    }
    
    /**
     * Helper methods
     */
    private function validateSimpananData($data, $excludeId = null) {
        $errors = [];
        
        if (empty($data['anggota_id'])) $errors[] = 'Anggota wajib dipilih';
        if (empty($data['jenis_simpanan'])) $errors[] = 'Jenis simpanan wajib dipilih';
        if (empty($data['no_rekening'])) $errors[] = 'No. Rekening wajib diisi';
        
        if (!$excludeId && $this->isRekeningExists($data['no_rekening'])) {
            $errors[] = 'No. Rekening sudah ada';
        }
        
        return $errors;
    }
    
    private function isRekeningExists($noRekening, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE no_rekening = ?";
        $params = [$noRekening];
        
        if ($excludeId) {
            $query .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }
        
        return (fetchRow($query, $params) ?? [])['count'] ?? 0 > 0;
    }
    
    private function generateNextNoRekening($jenis) {
        $prefix = [
            'wajib' => 'SW',
            'sukarela' => 'SS',
            'berjangka' => 'SB'
        ];
        
        $prefix = $prefix[$jenis] ?? 'SW';
        $date = date('Ymd');
        
        $last = fetchRow("SELECT no_rekening FROM {$this->tableName} WHERE no_rekening LIKE ? ORDER BY no_rekening DESC LIMIT 1", ["{$prefix}{$date}%"]);
        
        if ($last) {
            $lastNumber = intval(substr($last['no_rekening'], -3));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . $date . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    private function prepareSimpananData($data) {
        $prepared = [
            'anggota_id' => $data['anggota_id'],
            'jenis_simpanan' => $data['jenis_simpanan'],
            'no_rekening' => $data['no_rekening'],
            'status' => $data['status'] ?? 'active',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->user['id'] ?? null
        ];
        
        if (!isset($data['id'])) {
            $prepared['created_at'] = date('Y-m-d H:i:s');
            $prepared['created_by'] = $this->user['id'] ?? null;
            $prepared['saldo'] = 0;
        }
        
        return $prepared;
    }
    
    private function getActiveMembers() {
        $members = fetchAll("SELECT id, nama, email, kode FROM anggota WHERE status = 'active' ORDER BY nama");
        $options = [];
        foreach ($members as $member) {
            $options[$member['id']] = "{$member['nama']} ({$member['kode']})";
        }
        return $options;
    }
    
    private function createInitialTransaction($simpananId, $amount, $keterangan) {
        $transaksiData = [
            'simpanan_id' => $simpananId,
            'jenis_transaksi' => 'setoran',
            'jumlah' => $amount,
            'saldo_sebelum' => 0,
            'saldo_sesudah' => $amount,
            'keterangan' => $keterangan,
            'created_by' => $this->user['id'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $fields = array_keys($transaksiData);
        $values = array_values($transaksiData);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $query = "INSERT INTO transaksi_simpanan (" . implode(',', $fields) . ") VALUES ($placeholders)";
        insertQuery($query, $values);
        
        // Update simpanan saldo
        updateQuery("UPDATE {$this->tableName} SET saldo = ? WHERE {$this->primaryKey} = ?", [$amount, $simpananId]);
    }
    
    private function getAvailableFilters() {
        return [
            'jenis' => [
                'all' => 'Semua Jenis',
                'wajib' => 'Simpanan Wajib',
                'sukarela' => 'Simpanan Sukarela',
                'berjangka' => 'Simpanan Berjangka'
            ],
            'status' => [
                'all' => 'Semua Status',
                'active' => 'Aktif',
                'inactive' => 'Tidak Aktif'
            ]
        ];
    }
    
    private function updateDashboardStats($action, $newData = [], $oldData = []) {
        // Update dashboard statistics affecting all roles
    }
}
?>
