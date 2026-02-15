<?php
/**
 * Enhanced Anggota CRUD Controller
 * Impact: Changes affect all role dashboards (Admin, Member, Pengawas)
 */

require_once __DIR__ . '/MasterCRUDController.php';

class AnggotaCRUDController extends MasterCRUDController {
    
    protected $moduleName = 'Anggota';
    protected $tableName = 'anggota';
    protected $primaryKey = 'id';
    protected $requiredFields = ['nama', 'email', 'no_ktp', 'no_hp'];
    protected $allowedRoles = ['admin', 'staff'];
    protected $viewPath = 'anggota';
    
    public function __construct() {
        parent::__construct($this->moduleName, $this->tableName, $this->viewPath);
    }
    
    /**
     * Enhanced index with role-based data filtering
     * Impact: Different data shown to different roles
     */
    public function index() {
        $this->requireRole($this->allowedRoles);
        
        $page = intval($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Build query with filters
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (nama LIKE ? OR email LIKE ? OR no_ktp LIKE ? OR kode LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
        }
        
        if (!empty($status)) {
            $whereClause .= " AND status = ?";
            $params[] = $status;
        }
        
        // Role-based filtering
        if ($this->role === 'staff') {
            // Staff can only see active members
            $whereClause .= " AND status = 'active'";
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM {$this->tableName} $whereClause";
        $total = (fetchRow($countQuery, $params) ?? [])['total'] ?? 0;
        
        // Get data with related information
        $dataQuery = "SELECT a.*, 
                            (SELECT COUNT(*) FROM simpanan WHERE anggota_id = a.id) as total_simpanan,
                            (SELECT COUNT(*) FROM pinjaman WHERE anggota_id = a.id AND status = 'aktif') as total_pinjaman_aktif,
                            (SELECT COALESCE(SUM(saldo), 0) FROM simpanan WHERE anggota_id = a.id) as total_saldo_simpanan
                     FROM {$this->tableName} a 
                     $whereClause 
                     ORDER BY a.created_at DESC 
                     LIMIT $perPage OFFSET $offset";
        
        $data = fetchAll($dataQuery, $params);
        
        // Get enhanced stats
        $stats = $this->getAnggotaStats();
        
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
            'filters' => $this->getAvailableFilters()
        ]);
    }
    
    /**
     * Enhanced create with validation
     * Impact: New member appears in all dashboards
     */
    public function create() {
        $this->requireRole($this->allowedRoles);
        
        $formData = $this->getAnggotaFormData();
        $this->render($this->viewPath . '/create', [
            'formData' => $formData,
            'action' => 'create',
            'next_kode' => $this->generateNextKode()
        ]);
    }
    
    /**
     * Enhanced store with business logic
     * Impact: New member affects all dashboard statistics
     */
    public function store() {
        $this->requireRole($this->allowedRoles);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method');
        }
        
        // Enhanced validation
        $errors = $this->validateAnggotaData($_POST);
        if (!empty($errors)) {
            $this->error('Validation failed: ' . implode(', ', $errors));
        }
        
        // Check duplicate email and KTP
        if ($this->isEmailExists($_POST['email'])) {
            $this->error('Email sudah terdaftar');
        }
        
        if ($this->isKTPExists($_POST['no_ktp'])) {
            $this->error('No. KTP sudah terdaftar');
        }
        
        // Prepare data
        $data = $this->prepareAnggotaData($_POST);
        
        try {
            // Start transaction
            beginTransaction();
            
            // Insert anggota
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($values) - 1) . '?';
            
            $query = "INSERT INTO {$this->tableName} (" . implode(',', $fields) . ") VALUES ($placeholders)";
            $anggotaId = insertQuery($query, $values);
            
            // Create default simpanan account
            $this->createDefaultSimpanan($anggotaId, $data['nama']);
            
            // Create user account if needed
            if (!empty($_POST['create_user_account']) && $_POST['create_user_account'] == '1') {
                $this->createUserAccount($anggotaId, $data);
            }
            
            // Commit transaction
            commit();
            
            // Log activity
            $this->logActivity('create', $anggotaId, $data);
            
            // Update dashboard stats
            $this->updateDashboardStats('anggota_added', $data);
            
            $this->success("Anggota {$data['nama']} berhasil ditambahkan");
            
        } catch (Exception $e) {
            rollback();
            $this->error('Gagal menambahkan anggota: ' . $e->getMessage());
        }
    }
    
    /**
     * Enhanced update with cascade effects
     * Impact: Updates affect member portal and all dashboards
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
        $errors = $this->validateAnggotaData($_POST, $id);
        if (!empty($errors)) {
            $this->error('Validation failed: ' . implode(', ', $errors));
        }
        
        // Check email/KTP changes
        if ($_POST['email'] !== $existing['email'] && $this->isEmailExists($_POST['email'], $id)) {
            $this->error('Email sudah terdaftar');
        }
        
        if ($_POST['no_ktp'] !== $existing['no_ktp'] && $this->isKTPExists($_POST['no_ktp'], $id)) {
            $this->error('No. KTP sudah terdaftar');
        }
        
        $data = $this->prepareAnggotaData($_POST);
        
        try {
            beginTransaction();
            
            // Update anggota
            $fields = [];
            $values = [];
            
            foreach ($data as $field => $value) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
            $values[] = $id;
            
            $query = "UPDATE {$this->tableName} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = ?";
            updateQuery($query, $values);
            
            // Update user account if exists
            if (isset($data['email']) || isset($data['nama'])) {
                $this->updateUserAccount($id, $data);
            }
            
            commit();
            
            // Log activity
            $this->logActivity('update', $id, $data, $existing);
            
            // Update dashboard stats
            $this->updateDashboardStats('anggota_updated', $data, $existing);
            
            $this->success("Anggota {$data['nama']} berhasil diperbarui");
            
        } catch (Exception $e) {
            rollback();
            $this->error('Gagal memperbarui anggota: ' . $e->getMessage());
        }
    }
    
    /**
     * Enhanced delete with cascade handling
     * Impact: Removal affects all related dashboards
     */
    public function delete($id) {
        $this->requireRole(['admin']); // Only admin can delete
        
        $existing = fetchRow("SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = ?", [$id]);
        if (!$existing) {
            $this->error('Data tidak ditemukan');
        }
        
        // Check for active loans
        $activeLoans = (fetchRow("SELECT COUNT(*) as count FROM pinjaman WHERE anggota_id = ? AND status = 'aktif'", [$id]) ?? [])['count'] ?? 0;
        if ($activeLoans > 0) {
            $this->error('Tidak dapat menghapus anggota dengan pinjaman aktif');
        }
        
        try {
            beginTransaction();
            
            // Soft delete (update status)
            updateQuery("UPDATE {$this->tableName} SET status = 'deleted', deleted_at = NOW() WHERE {$this->primaryKey} = ?", [$id]);
            
            // Deactivate user account
            updateQuery("UPDATE users SET status = 'inactive' WHERE anggota_id = ?", [$id]);
            
            commit();
            
            // Log activity
            $this->logActivity('delete', $id, [], $existing);
            
            // Update dashboard stats
            $this->updateDashboardStats('anggota_deleted', [], $existing);
            
            $this->success("Anggota {$existing['nama']} berhasil dihapus");
            
        } catch (Exception $e) {
            rollback();
            $this->error('Gagal menghapus anggota: ' . $e->getMessage());
        }
    }
    
    /**
     * Get comprehensive anggota statistics
     * Impact: Stats shown in admin, member, and pengawas dashboards
     */
    protected function getAnggotaStats() {
        $stats = [
            'total' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE status != 'deleted'") ?? [])['count'] ?? 0,
            'active' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = 'active'") ?? [])['count'] ?? 0,
            'inactive' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = 'inactive'") ?? [])['count'] ?? 0,
            'new_this_month' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)") ?? [])['count'] ?? 0,
            'total_savings' => (fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM simpanan s JOIN {$this->tableName} a ON s.anggota_id = a.id WHERE a.status != 'deleted'") ?? [])['total'] ?? 0,
            'active_loans' => (fetchRow("SELECT COUNT(*) as count FROM pinjaman p JOIN {$this->tableName} a ON p.anggota_id = a.id WHERE p.status = 'aktif' AND a.status != 'deleted'") ?? [])['count'] ?? 0
        ];
        
        return $stats;
    }
    
    /**
     * Get form data structure
     */
    protected function getAnggotaFormData($existingData = []) {
        return [
            'personal_info' => [
                'title' => 'Informasi Pribadi',
                'fields' => [
                    'nama' => ['type' => 'text', 'label' => 'Nama Lengkap', 'required' => true],
                    'no_ktp' => ['type' => 'text', 'label' => 'No. KTP', 'required' => true],
                    'tempat_lahir' => ['type' => 'text', 'label' => 'Tempat Lahir'],
                    'tanggal_lahir' => ['type' => 'date', 'label' => 'Tanggal Lahir'],
                    'jenis_kelamin' => ['type' => 'select', 'label' => 'Jenis Kelamin', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
                    'status_pernikahan' => ['type' => 'select', 'label' => 'Status Pernikahan', 'options' => ['single' => 'Belum Menikah', 'married' => 'Menikah', 'divorced' => 'Cerai']],
                    'agama' => ['type' => 'select', 'label' => 'Agama', 'options' => ['islam' => 'Islam', 'kristen' => 'Kristen', 'katolik' => 'Katolik', 'hindu' => 'Hindu', 'budha' => 'Budha']]
                ],
                'data' => $existingData
            ],
            'contact_info' => [
                'title' => 'Informasi Kontak',
                'fields' => [
                    'email' => ['type' => 'email', 'label' => 'Email', 'required' => true],
                    'no_hp' => ['type' => 'text', 'label' => 'No. HP', 'required' => true],
                    'no_telp' => ['type' => 'text', 'label' => 'No. Telepon'],
                    'alamat' => ['type' => 'textarea', 'label' => 'Alamat Lengkap'],
                    'kelurahan' => ['type' => 'text', 'label' => 'Kelurahan'],
                    'kecamatan' => ['type' => 'text', 'label' => 'Kecamatan'],
                    'kabupaten' => ['type' => 'text', 'label' => 'Kabupaten'],
                    'provinsi' => ['type' => 'text', 'label' => 'Provinsi'],
                    'kode_pos' => ['type' => 'text', 'label' => 'Kode Pos']
                ],
                'data' => $existingData
            ],
            'employment_info' => [
                'title' => 'Informasi Pekerjaan',
                'fields' => [
                    'pekerjaan' => ['type' => 'text', 'label' => 'Pekerjaan'],
                    'nama_perusahaan' => ['type' => 'text', 'label' => 'Nama Perusahaan'],
                    'jabatan' => ['type' => 'text', 'label' => 'Jabatan'],
                    'penghasilan_per_bulan' => ['type' => 'number', 'label' => 'Penghasilan per Bulan'],
                    'alamat_kantor' => ['type' => 'textarea', 'label' => 'Alamat Kantor']
                ],
                'data' => $existingData
            ],
            'system_info' => [
                'title' => 'Informasi Sistem',
                'fields' => [
                    'kode' => ['type' => 'text', 'label' => 'Kode Anggota', 'readonly' => true],
                    'tanggal_gabung' => ['type' => 'date', 'label' => 'Tanggal Gabung'],
                    'status' => ['type' => 'select', 'label' => 'Status', 'options' => ['active' => 'Aktif', 'inactive' => 'Tidak Aktif']],
                    'create_user_account' => ['type' => 'checkbox', 'label' => 'Buat Akun User']
                ],
                'data' => $existingData
            ]
        ];
    }
    
    /**
     * Additional helper methods
     */
    private function validateAnggotaData($data, $excludeId = null) {
        $errors = [];
        
        if (empty($data['nama'])) $errors[] = 'Nama wajib diisi';
        if (empty($data['email'])) $errors[] = 'Email wajib diisi';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid';
        if (empty($data['no_ktp'])) $errors[] = 'No. KTP wajib diisi';
        if (strlen($data['no_ktp']) < 16) $errors[] = 'No. KTP minimal 16 digit';
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
    
    private function isKTPExists($ktp, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE no_ktp = ?";
        $params = [$ktp];
        
        if ($excludeId) {
            $query .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }
        
        return (fetchRow($query, $params) ?? [])['count'] ?? 0 > 0;
    }
    
    private function generateNextKode() {
        $prefix = 'AG';
        $year = date('y');
        $month = date('m');
        
        $last = fetchRow("SELECT kode FROM {$this->tableName} WHERE kode LIKE ? ORDER BY kode DESC LIMIT 1", ["{$prefix}{$year}{$month}%"]);
        
        if ($last) {
            $lastNumber = intval(substr($last['kode'], -4));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    private function prepareAnggotaData($data) {
        $prepared = [
            'nama' => $data['nama'],
            'email' => $data['email'],
            'no_ktp' => $data['no_ktp'],
            'no_hp' => $data['no_hp'],
            'tempat_lahir' => $data['tempat_lahir'] ?? '',
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? '',
            'status_pernikahan' => $data['status_pernikahan'] ?? '',
            'agama' => $data['agama'] ?? '',
            'no_telp' => $data['no_telp'] ?? '',
            'alamat' => $data['alamat'] ?? '',
            'kelurahan' => $data['kelurahan'] ?? '',
            'kecamatan' => $data['kecamatan'] ?? '',
            'kabupaten' => $data['kabupaten'] ?? '',
            'provinsi' => $data['provinsi'] ?? '',
            'kode_pos' => $data['kode_pos'] ?? '',
            'pekerjaan' => $data['pekerjaan'] ?? '',
            'nama_perusahaan' => $data['nama_perusahaan'] ?? '',
            'jabatan' => $data['jabatan'] ?? '',
            'penghasilan_per_bulan' => $data['penghasilan_per_bulan'] ?? 0,
            'alamat_kantor' => $data['alamat_kantor'] ?? '',
            'tanggal_gabung' => $data['tanggal_gabung'] ?? date('Y-m-d'),
            'status' => $data['status'] ?? 'active',
            'kode' => $data['kode'] ?? $this->generateNextKode(),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->user['id'] ?? null
        ];
        
        if (!isset($data['id'])) {
            $prepared['created_at'] = date('Y-m-d H:i:s');
            $prepared['created_by'] = $this->user['id'] ?? null;
        }
        
        return $prepared;
    }
    
    private function createDefaultSimpanan($anggotaId, $nama) {
        $simpananData = [
            'anggota_id' => $anggotaId,
            'jenis_simpanan' => 'wajib',
            'no_rekening' => 'SW-' . date('Ymd') . '-' . str_pad($anggotaId, 4, '0', STR_PAD_LEFT),
            'saldo' => 0,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->user['id'] ?? null
        ];
        
        $fields = array_keys($simpananData);
        $values = array_values($simpananData);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $query = "INSERT INTO simpanan (" . implode(',', $fields) . ") VALUES ($placeholders)";
        insertQuery($query, $values);
    }
    
    private function createUserAccount($anggotaId, $anggotaData) {
        $userData = [
            'anggota_id' => $anggotaId,
            'username' => $anggotaData['email'],
            'email' => $anggotaData['email'],
            'full_name' => $anggotaData['nama'],
            'role' => 'member',
            'status' => 'active',
            'password' => password_hash('password123', PASSWORD_DEFAULT), // Default password
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->user['id'] ?? null
        ];
        
        $fields = array_keys($userData);
        $values = array_values($userData);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $query = "INSERT INTO users (" . implode(',', $fields) . ") VALUES ($placeholders)";
        insertQuery($query, $values);
    }
    
    private function updateUserAccount($anggotaId, $anggotaData) {
        $updateData = [];
        
        if (isset($anggotaData['email'])) {
            $updateData['email'] = $anggotaData['email'];
            $updateData['username'] = $anggotaData['email'];
        }
        
        if (isset($anggotaData['nama'])) {
            $updateData['full_name'] = $anggotaData['nama'];
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            
            $fields = [];
            $values = [];
            
            foreach ($updateData as $field => $value) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
            $values[] = $anggotaId;
            
            $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE anggota_id = ?";
            updateQuery($query, $values);
        }
    }
    
    private function getAvailableFilters() {
        return [
            'status' => [
                'all' => 'Semua Status',
                'active' => 'Aktif',
                'inactive' => 'Tidak Aktif',
                'deleted' => 'Dihapus'
            ]
        ];
    }
    
    private function updateDashboardStats($action, $newData = [], $oldData = []) {
        // This method updates dashboard statistics that affect all roles
        // Implementation depends on specific dashboard requirements
    }
}
?>
