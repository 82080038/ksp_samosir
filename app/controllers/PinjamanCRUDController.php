<?php
/**
 * Pinjaman CRUD Controller
 * Impact: Changes affect Admin, Member, and Pengawas dashboards
 */

require_once __DIR__ . '/MasterCRUDController.php';

class PinjamanCRUDController extends MasterCRUDController {
    
    protected $moduleName = 'Pinjaman';
    protected $tableName = 'pinjaman';
    protected $primaryKey = 'id';
    protected $requiredFields = ['anggota_id', 'jumlah_pinjaman', 'jangka_waktu', 'bunga'];
    protected $allowedRoles = ['admin', 'staff'];
    protected $viewPath = 'pinjaman';
    
    public function __construct() {
        parent::__construct($this->moduleName, $this->tableName, $this->viewPath);
    }
    
    /**
     * Enhanced index with loan details
     * Impact: Comprehensive data in all dashboards
     */
    public function index() {
        $this->requireRole($this->allowedRoles);
        
        $page = intval($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Build query
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (a.nama LIKE ? OR p.no_pinjaman LIKE ? OR a.email LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%");
        }
        
        if (!empty($status)) {
            $whereClause .= " AND p.status = ?";
            $params[] = $status;
        }
        
        // Role-based filtering
        if ($this->role === 'staff') {
            $whereClause .= " AND p.status IN ('pending', 'approved', 'aktif')";
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM {$this->tableName} p 
                      JOIN anggota a ON p.anggota_id = a.id 
                      $whereClause";
        $total = (fetchRow($countQuery, $params) ?? [])['total'] ?? 0;
        
        // Get data with member info and payment status
        $dataQuery = "SELECT p.*, a.nama as nama_anggota, a.email, a.no_hp,
                            (SELECT COUNT(*) FROM angsuran WHERE pinjaman_id = p.id) as total_angsuran,
                            (SELECT COUNT(*) FROM angsuran WHERE pinjaman_id = p.id AND status = 'lunas') as angsuran_lunas,
                            (SELECT COALESCE(SUM(jumlah), 0) FROM angsuran WHERE pinjaman_id = p.id) as total_dibayar
                     FROM {$this->tableName} p 
                     JOIN anggota a ON p.anggota_id = a.id 
                     $whereClause 
                     ORDER BY p.created_at DESC 
                     LIMIT $perPage OFFSET $offset";
        
        $data = fetchAll($dataQuery, $params);
        
        // Calculate remaining balance for each loan
        foreach ($data as &$loan) {
            $loan['sisa_pinjaman'] = $loan['jumlah_pinjaman'] - $loan['total_dibayar'];
            $loan['persentase_dibayar'] = $loan['jumlah_pinjaman'] > 0 ? ($loan['total_dibayar'] / $loan['jumlah_pinjaman']) * 100 : 0;
        }
        
        // Get statistics
        $stats = $this->getPinjamanStats();
        
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
     * Create new pinjaman
     * Impact: New loan visible in member portal
     */
    public function create() {
        $this->requireRole($this->allowedRoles);
        
        $formData = $this->getPinjamanFormData();
        $this->render($this->viewPath . '/create', [
            'formData' => $formData,
            'action' => 'create',
            'members' => $this->getActiveMembers(),
            'next_no_pinjaman' => $this->generateNextNoPinjaman()
        ]);
    }
    
    /**
     * Store new pinjaman
     * Impact: Affects member dashboard and admin statistics
     */
    public function store() {
        $this->requireRole($this->allowedRoles);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method');
        }
        
        // Validate
        $errors = $this->validatePinjamanData($_POST);
        if (!empty($errors)) {
            $this->error('Validation failed: ' . implode(', ', $errors));
        }
        
        // Check member eligibility
        if (!$this->isMemberEligibleForLoan($_POST['anggota_id'], $_POST['jumlah_pinjaman'])) {
            $this->error('Anggota tidak memenuhi syarat untuk pinjaman ini');
        }
        
        $data = $this->preparePinjamanData($_POST);
        
        try {
            beginTransaction();
            
            // Insert pinjaman
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($values) - 1) . '?';
            
            $query = "INSERT INTO {$this->tableName} (" . implode(',', $fields) . ") VALUES ($placeholders)";
            $pinjamanId = insertQuery($query, $values);
            
            // Create payment schedule
            $this->createPaymentSchedule($pinjamanId, $data);
            
            // Update member loan status
            $this->updateMemberLoanStatus($_POST['anggota_id']);
            
            commit();
            
            // Log activity
            $this->logActivity('create', $pinjamanId, $data);
            
            // Update dashboard stats
            $this->updateDashboardStats('pinjaman_added', $data);
            
            $this->success("Pinjaman berhasil diajukan");
            
        } catch (Exception $e) {
            rollback();
            $this->error('Gagal mengajukan pinjaman: ' . $e->getMessage());
        }
    }
    
    /**
     * Update pinjaman status
     * Impact: Status changes visible in member portal
     */
    public function updateStatus($id) {
        $this->requireRole($this->allowedRoles);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method');
        }
        
        $existing = fetchRow("SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = ?", [$id]);
        if (!$existing) {
            $this->error('Data tidak ditemukan');
        }
        
        $newStatus = $_POST['status'] ?? $existing['status'];
        $catatan = $_POST['catatan'] ?? '';
        
        try {
            beginTransaction();
            
            // Update pinjaman status
            updateQuery("UPDATE {$this->tableName} SET status = ?, catatan = ?, updated_at = NOW() WHERE {$this->primaryKey} = ?", 
                       [$newStatus, $catatan, $id]);
            
            // If approved, disburse funds
            if ($newStatus === 'approved' && $existing['status'] === 'pending') {
                $this->disburseLoan($id, $existing);
            }
            
            // Update member loan status
            $this->updateMemberLoanStatus($existing['anggota_id']);
            
            commit();
            
            // Log activity
            $this->logActivity('status_change', $id, ['status' => $newStatus, 'catatan' => $catatan], $existing);
            
            // Update dashboard stats
            $this->updateDashboardStats('pinjaman_status_updated', ['status' => $newStatus], $existing);
            
            $this->success("Status pinjaman berhasil diperbarui");
            
        } catch (Exception $e) {
            rollback();
            $this->error('Gagal memperbarui status: ' . $e->getMessage());
        }
    }
    
    /**
     * Get pinjaman statistics
     * Impact: Stats shown in all role dashboards
     */
    protected function getPinjamanStats() {
        try {
            $stats = [
                'total_pinjaman' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName}") ?? [])['count'] ?? 0,
                'pinjaman_aktif' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = 'aktif'") ?? [])['count'] ?? 0,
                'pinjaman_pending' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = 'pending'") ?? [])['count'] ?? 0,
                'total_outstanding' => (fetchRow("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total FROM {$this->tableName} WHERE status = 'aktif'") ?? [])['total'] ?? 0,
                'total_dibayar' => (fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM angsuran WHERE status = 'lunas'") ?? [])['total'] ?? 0,
                'total_tunggakan' => (fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM angsuran WHERE status = 'belum_lunas' AND jatuh_tempo < CURDATE()") ?? [])['total'] ?? 0,
                'angsuran_hari_ini' => (fetchRow("SELECT COUNT(*) as count FROM angsuran WHERE DATE(tanggal_bayar) = CURDATE()") ?? [])['count'] ?? 0,
                'pembayaran_hari_ini' => (fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM angsuran WHERE DATE(tanggal_bayar) = CURDATE() AND status = 'lunas'") ?? [])['total'] ?? 0
            ];
        } catch (Exception $e) {
            $stats = ['total_pinjaman' => 0, 'pinjaman_aktif' => 0, 'pinjaman_pending' => 0, 'total_outstanding' => 0, 'total_dibayar' => 0, 'total_tunggakan' => 0, 'angsuran_hari_ini' => 0, 'pembayaran_hari_ini' => 0];
        }
        return $stats;
    }
    
    /**
     * Get form data structure
     */
    protected function getPinjamanFormData($existingData = []) {
        return [
            'loan_info' => [
                'title' => 'Informasi Pinjaman',
                'fields' => [
                    'anggota_id' => ['type' => 'select', 'label' => 'Anggota', 'required' => true, 'options' => $this->getActiveMembers()],
                    'no_pinjaman' => ['type' => 'text', 'label' => 'No. Pinjaman', 'required' => true, 'readonly' => true],
                    'jumlah_pinjaman' => ['type' => 'number', 'label' => 'Jumlah Pinjaman', 'required' => true],
                    'jangka_waktu' => ['type' => 'number', 'label' => 'Jangka Waktu (bulan)', 'required' => true],
                    'bunga' => ['type' => 'number', 'label' => 'Bunga (%)', 'required' => true, 'step' => '0.01'],
                    'jenis_pinjaman' => ['type' => 'select', 'label' => 'Jenis Pinjaman', 'options' => [
                        'produktif' => 'Pinjaman Produktif',
                        'konsumtif' => 'Pinjaman Konsumtif',
                        'modal_kerja' => 'Pinjaman Modal Kerja'
                    ]],
                    'tujuan' => ['type' => 'textarea', 'label' => 'Tujuan Pinjaman', 'required' => true]
                ],
                'data' => $existingData
            ],
            'collateral_info' => [
                'title' => 'Informasi Jaminan',
                'fields' => [
                    'jenis_jaminan' => ['type' => 'select', 'label' => 'Jenis Jaminan', 'options' => [
                        'tanah' => 'Sertifikat Tanah',
                        'kendaraan' => 'BPKB Kendaraan',
                        'rumah' => 'Sertifikat Rumah',
                        'lainnya' => 'Lainnya'
                    ]],
                    'deskripsi_jaminan' => ['type' => 'textarea', 'label' => 'Deskripsi Jaminan'],
                    'nilai_jaminan' => ['type' => 'number', 'label' => 'Nilai Jaminan']
                ],
                'data' => $existingData
            ]
        ];
    }
    
    /**
     * Helper methods
     */
    private function validatePinjamanData($data) {
        $errors = [];
        
        if (empty($data['anggota_id'])) $errors[] = 'Anggota wajib dipilih';
        if (empty($data['jumlah_pinjaman']) || $data['jumlah_pinjaman'] <= 0) $errors[] = 'Jumlah pinjaman harus lebih dari 0';
        if (empty($data['jangka_waktu']) || $data['jangka_waktu'] <= 0) $errors[] = 'Jangka waktu harus lebih dari 0';
        if (empty($data['bunga']) || $data['bunga'] < 0) $errors[] = 'Bunga tidak valid';
        if (empty($data['tujuan'])) $errors[] = 'Tujuan pinjaman wajib diisi';
        
        return $errors;
    }
    
    private function isMemberEligibleForLoan($anggotaId, $amount) {
        // Check if member has active loans
        $activeLoans = (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE anggota_id = ? AND status = 'aktif'", [$anggotaId]) ?? [])['count'] ?? 0;
        
        // Check member's savings
        $totalSavings = (fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM simpanan WHERE anggota_id = ? AND status = 'active'", [$anggotaId]) ?? [])['total'] ?? 0;
        
        // Basic eligibility: max 2 active loans, savings >= 10% of loan amount
        return $activeLoans < 2 && $totalSavings >= ($amount * 0.1);
    }
    
    private function generateNextNoPinjaman() {
        $prefix = 'PJ';
        $date = date('Ymd');
        
        $last = fetchRow("SELECT no_pinjaman FROM {$this->tableName} WHERE no_pinjaman LIKE ? ORDER BY no_pinjaman DESC LIMIT 1", ["{$prefix}{$date}%"]);
        
        if ($last) {
            $lastNumber = intval(substr($last['no_pinjaman'], -3));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . $date . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    private function preparePinjamanData($data) {
        $monthlyInterest = $data['bunga'] / 100 / 12;
        $monthlyPayment = $this->calculateMonthlyPayment($data['jumlah_pinjaman'], $monthlyInterest, $data['jangka_waktu']);
        $totalPayment = $monthlyPayment * $data['jangka_waktu'];
        
        $prepared = [
            'anggota_id' => $data['anggota_id'],
            'no_pinjaman' => $data['no_pinjaman'],
            'jumlah_pinjaman' => $data['jumlah_pinjaman'],
            'jangka_waktu' => $data['jangka_waktu'],
            'bunga' => $data['bunga'],
            'jenis_pinjaman' => $data['jenis_pinjaman'] ?? 'produktif',
            'tujuan' => $data['tujuan'],
            'jenis_jaminan' => $data['jenis_jaminan'] ?? '',
            'deskripsi_jaminan' => $data['deskripsi_jaminan'] ?? '',
            'nilai_jaminan' => $data['nilai_jaminan'] ?? 0,
            'angsuran_per_bulan' => $monthlyPayment,
            'total_angsuran' => $totalPayment,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->user['id'] ?? null
        ];
        
        return $prepared;
    }
    
    private function calculateMonthlyPayment($principal, $monthlyRate, $months) {
        if ($monthlyRate == 0) {
            return $principal / $months;
        }
        
        return $principal * ($monthlyRate * pow(1 + $monthlyRate, $months)) / (pow(1 + $monthlyRate, $months) - 1);
    }
    
    private function createPaymentSchedule($pinjamanId, $loanData) {
        $startDate = date('Y-m-d', strtotime('+1 month'));
        $monthlyPayment = $loanData['angsuran_per_bulan'];
        
        for ($i = 1; $i <= $loanData['jangka_waktu']; $i++) {
            $dueDate = date('Y-m-d', strtotime($startDate . ' +' . ($i - 1) . ' months'));
            
            $angsuranData = [
                'pinjaman_id' => $pinjamanId,
                'angsuran_ke' => $i,
                'jumlah' => $monthlyPayment,
                'jatuh_tempo' => $dueDate,
                'status' => 'belum_lunas',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $fields = array_keys($angsuranData);
            $values = array_values($angsuranData);
            $placeholders = str_repeat('?,', count($values) - 1) . '?';
            
            $query = "INSERT INTO angsuran (" . implode(',', $fields) . ") VALUES ($placeholders)";
            insertQuery($query, $values);
        }
    }
    
    private function disburseLoan($pinjamanId, $loanData) {
        // Update loan status to active
        updateQuery("UPDATE {$this->tableName} SET status = 'aktif', tanggal_cair = NOW() WHERE {$this->primaryKey} = ?", [$pinjamanId]);
        
        // Create transaction record
        $transaksiData = [
            'pinjaman_id' => $pinjamanId,
            'jenis_transaksi' => 'pencairan',
            'jumlah' => $loanData['jumlah_pinjaman'],
            'tanggal' => date('Y-m-d H:i:s'),
            'created_by' => $this->user['id'] ?? null
        ];
        
        $fields = array_keys($transaksiData);
        $values = array_values($transaksiData);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $query = "INSERT INTO transaksi_pinjaman (" . implode(',', $fields) . ") VALUES ($placeholders)";
        insertQuery($query, $values);
    }
    
    private function updateMemberLoanStatus($anggotaId) {
        $activeLoans = (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE anggota_id = ? AND status = 'aktif'", [$anggotaId]) ?? [])['count'] ?? 0;
        $pendingLoans = (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE anggota_id = ? AND status = 'pending'", [$anggotaId]) ?? [])['count'] ?? 0;
        
        $loanStatus = 'none';
        if ($activeLoans > 0) {
            $loanStatus = 'active';
        } elseif ($pendingLoans > 0) {
            $loanStatus = 'pending';
        }
        
        updateQuery("UPDATE anggota SET status_pinjaman = ?, updated_at = NOW() WHERE id = ?", [$loanStatus, $anggotaId]);
    }
    
    private function getActiveMembers() {
        $members = fetchAll("SELECT id, nama, email, kode FROM anggota WHERE status = 'active' ORDER BY nama");
        $options = [];
        foreach ($members as $member) {
            $options[$member['id']] = "{$member['nama']} ({$member['kode']})";
        }
        return $options;
    }
    
    private function getAvailableFilters() {
        return [
            'status' => [
                'all' => 'Semua Status',
                'pending' => 'Pending',
                'approved' => 'Disetujui',
                'aktif' => 'Aktif',
                'lunas' => 'Lunas',
                'ditolak' => 'Ditolak'
            ]
        ];
    }
    
    private function updateDashboardStats($action, $newData = [], $oldData = []) {
        // Update dashboard statistics affecting all roles
    }
}
?>
