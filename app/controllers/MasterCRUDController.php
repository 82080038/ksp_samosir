<?php
/**
 * KSP Samosir Master CRUD System
 * Base class for all CRUD operations with role-based access
 * Impact: Changes here affect all role dashboards
 */

require_once __DIR__ . '/BaseController.php';

abstract class MasterCRUDController extends BaseController {
    
    protected $moduleName;
    protected $tableName;
    protected $primaryKey = 'id';
    protected $requiredFields = [];
    protected $allowedRoles = ['admin'];
    protected $viewPath;
    
    public function __construct($moduleName, $tableName, $viewPath) {
        parent::__construct();
        $this->moduleName = $moduleName;
        $this->tableName = $tableName;
        $this->viewPath = $viewPath;
    }
    
    /**
     * INDEX - Display list with pagination and search
     * Impact: Affects all role dashboards that display this data
     */
    public function index() {
        $this->requireRole($this->allowedRoles);
        
        $page = intval($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Build query with search
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            $whereClause = "WHERE nama LIKE ? OR email LIKE ? OR kode LIKE ?";
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM {$this->tableName} $whereClause";
        $total = (fetchRow($countQuery, $params) ?? [])['total'] ?? 0;
        
        // Get data
        $dataQuery = "SELECT * FROM {$this->tableName} $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
        $data = fetchAll($dataQuery, $params);
        
        // Get additional stats based on module
        $stats = $this->getModuleStats();
        
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
            'search' => $search
        ]);
    }
    
    /**
     * CREATE - Display create form
     * Impact: New data appears in all role dashboards
     */
    public function create() {
        $this->requireRole($this->allowedRoles);
        
        $formData = $this->getFormData();
        $this->render($this->viewPath . '/create', [
            'formData' => $formData,
            'action' => 'create'
        ]);
    }
    
    /**
     * STORE - Save new data
     * Impact: Immediately visible in all role dashboards
     */
    public function store() {
        $this->requireRole($this->allowedRoles);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method');
        }
        
        // Validate required fields
        $errors = $this->validateRequired($this->requiredFields);
        if (!empty($errors)) {
            $this->error('Validation failed: ' . implode(', ', $errors));
        }
        
        // Prepare data
        $data = $this->prepareData($_POST);
        
        // Insert data
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $query = "INSERT INTO {$this->tableName} (" . implode(',', $fields) . ") VALUES ($placeholders)";
        
        try {
            $id = insertQuery($query, $values);
            
            // Log activity
            $this->logActivity('create', $id, $data);
            
            // Update related stats
            $this->updateRelatedStats($data);
            
            $this->success("{$this->moduleName} berhasil ditambahkan");
            
        } catch (Exception $e) {
            $this->error('Gagal menambahkan data: ' . $e->getMessage());
        }
    }
    
    /**
     * EDIT - Display edit form
     * Impact: Changes affect all role dashboards
     */
    public function edit($id) {
        $this->requireRole($this->allowedRoles);
        
        $data = fetchRow("SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = ?", [$id]);
        
        if (!$data) {
            $this->error('Data tidak ditemukan');
        }
        
        $formData = $this->getFormData($data);
        $this->render($this->viewPath . '/edit', [
            'data' => $data,
            'formData' => $formData,
            'action' => 'edit'
        ]);
    }
    
    /**
     * UPDATE - Save changes
     * Impact: Updates reflected in all role dashboards
     */
    public function update($id) {
        $this->requireRole($this->allowedRoles);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method');
        }
        
        // Check if data exists
        $existing = fetchRow("SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = ?", [$id]);
        if (!$existing) {
            $this->error('Data tidak ditemukan');
        }
        
        // Validate required fields
        $errors = $this->validateRequired($this->requiredFields);
        if (!empty($errors)) {
            $this->error('Validation failed: ' . implode(', ', $errors));
        }
        
        // Prepare data
        $data = $this->prepareData($_POST);
        
        // Update data
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $fields[] = "$field = ?";
            $values[] = $value;
        }
        $values[] = $id;
        
        $query = "UPDATE {$this->tableName} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = ?";
        
        try {
            updateQuery($query, $values);
            
            // Log activity
            $this->logActivity('update', $id, $data, $existing);
            
            // Update related stats
            $this->updateRelatedStats($data, $existing);
            
            $this->success("{$this->moduleName} berhasil diperbarui");
            
        } catch (Exception $e) {
            $this->error('Gagal memperbarui data: ' . $e->getMessage());
        }
    }
    
    /**
     * DELETE - Remove data
     * Impact: Removal affects all role dashboards
     */
    public function delete($id) {
        $this->requireRole($this->allowedRoles);
        
        // Check if data exists
        $existing = fetchRow("SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = ?", [$id]);
        if (!$existing) {
            $this->error('Data tidak ditemukan');
        }
        
        try {
            // Delete data
            deleteQuery("DELETE FROM {$this->tableName} WHERE {$this->primaryKey} = ?", [$id]);
            
            // Log activity
            $this->logActivity('delete', $id, [], $existing);
            
            // Update related stats
            $this->updateRelatedStats([], $existing);
            
            $this->success("{$this->moduleName} berhasil dihapus");
            
        } catch (Exception $e) {
            $this->error('Gagal menghapus data: ' . $e->getMessage());
        }
    }
    
    /**
     * Get module-specific statistics
     * Impact: Stats displayed in admin and role dashboards
     */
    protected function getModuleStats() {
        $stats = [
            'total' => (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName}") ?? [])['count'] ?? 0,
            'active' => 0,
            'inactive' => 0,
            'recent' => 0
        ];
        
        // Get status-based stats if status column exists
        if ($this->columnExists('status')) {
            $stats['active'] = (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = 'active'") ?? [])['count'] ?? 0;
            $stats['inactive'] = (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = 'inactive'") ?? [])['count'] ?? 0;
        }
        
        // Get recent additions (last 7 days)
        if ($this->columnExists('created_at')) {
            $stats['recent'] = (fetchRow("SELECT COUNT(*) as count FROM {$this->tableName} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)") ?? [])['count'] ?? 0;
        }
        
        return $stats;
    }
    
    /**
     * Prepare data for database
     * Impact: Data format affects all dashboard displays
     */
    protected function prepareData($data) {
        $prepared = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $this->requiredFields) || $this->columnExists($key)) {
                $prepared[$key] = $value;
            }
        }
        
        // Add timestamps
        if ($this->columnExists('updated_at')) {
            $prepared['updated_at'] = date('Y-m-d H:i:s');
        }
        
        if ($this->columnExists('updated_by')) {
            $prepared['updated_by'] = $this->user['id'] ?? null;
        }
        
        return $prepared;
    }
    
    /**
     * Get form data structure
     * Impact: Form structure affects admin dashboard UI
     */
    protected function getFormData($existingData = []) {
        return [
            'basic_info' => [
                'fields' => $this->getBasicFields(),
                'data' => $existingData
            ],
            'additional_info' => [
                'fields' => $this->getAdditionalFields(),
                'data' => $existingData
            ]
        ];
    }
    
    /**
     * Log CRUD activities
     * Impact: Activities visible in monitoring dashboard
     */
    protected function logActivity($action, $id, $newData = [], $oldData = []) {
        $logData = [
            'user_id' => $this->user['id'] ?? null,
            'module' => $this->moduleName,
            'action' => $action,
            'record_id' => $id,
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($newData),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert to activity log
        $fields = array_keys($logData);
        $values = array_values($logData);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $query = "INSERT INTO activity_logs (" . implode(',', $fields) . ") VALUES ($placeholders)";
        insertQuery($query, $values);
    }
    
    /**
     * Update related statistics
     * Impact: Stats changes affect all role dashboards
     */
    protected function updateRelatedStats($newData = [], $oldData = []) {
        // Update dashboard statistics
        // This will be implemented by child classes
    }
    
    /**
     * Check if column exists in table
     */
    protected function columnExists($column) {
        try {
            $result = fetchRow("SHOW COLUMNS FROM {$this->tableName} LIKE ?", [$column]);
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get basic fields (to be implemented by child classes)
     */
    protected function getBasicFields() {
        return [];
    }
    
    /**
     * Get additional fields (to be implemented by child classes)
     */
    protected function getAdditionalFields() {
        return [];
    }
}
?>
