<?php
/**
 * CRUD API Controller
 * Handles AJAX CRUD operations for all modules
 */

// Include required files
require_once __DIR__ . '/../app/helpers/ErrorHandler.php';
require_once __DIR__ . '/../app/helpers/DataExporter.php';

class CrudAPI {
    private $pdo;
    private $module;
    
    public function __construct() {
        // Database connection
        try {
            $this->pdo = new PDO('mysql:host=localhost;dbname=ksp_samosir', 'root', 'root');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Database connection failed'], 500);
        }
        
        // Get module from request
        $this->module = $_GET['module'] ?? $_POST['module'] ?? '';
        
        // Handle CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }
    
    /**
     * Main router
     */
    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? 'read';
        
        switch ($action) {
            case 'read':
                $this->read();
                break;
            case 'save':
                $this->save();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'bulk-delete':
                $this->bulkDelete();
                break;
            case 'export':
                $this->export();
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
    }
    
    /**
     * Read data
     */
    private function read() {
        try {
            $id = $_GET['id'] ?? null;
            $page = (int)($_GET['page'] ?? 1);
            $search = $_GET['search'] ?? '';
            $perPage = (int)($_GET['per_page'] ?? 10);
            
            if ($id) {
                // Read single record
                $data = $this->getSingleRecord($id);
                $this->jsonResponse(['success' => true, 'data' => $data]);
            } else {
                // Read multiple records with pagination
                $result = $this->getMultipleRecords($page, $search, $perPage);
                $this->jsonResponse(['success' => true, 'data' => $result['data'], 'pagination' => $result['pagination']]);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Save data (create or update)
     */
    private function save() {
        try {
            $id = $_POST['id'] ?? null;
            $data = $this->sanitizeInput($_POST);
            
            if ($id) {
                // Update existing record
                $this->updateRecord($id, $data);
                $message = 'Data berhasil diperbarui';
            } else {
                // Create new record
                $id = $this->createRecord($data);
                $message = 'Data berhasil ditambahkan';
            }
            
            $this->jsonResponse(['success' => true, 'message' => $message, 'id' => $id]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete record
     */
    private function delete() {
        try {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                throw new Exception('ID is required');
            }
            
            $this->deleteRecord($id);
            $this->jsonResponse(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Bulk delete
     */
    private function bulkDelete() {
        try {
            $ids = $_POST['ids'] ?? [];
            if (empty($ids)) {
                throw new Exception('No IDs provided');
            }
            
            $deleted = $this->bulkDeleteRecords($ids);
            $this->jsonResponse(['success' => true, 'message' => "$deleted data berhasil dihapus"]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Export data
     */
    private function export() {
        try {
            $format = $_GET['format'] ?? 'excel';
            $search = $_GET['search'] ?? '';
            
            $data = $this->getExportData($search);
            $headers = $this->getTableHeaders();
            
            $filename = $this->module . '_data_' . date('Y-m-d_H-i-s');
            $exporter = DataExporter::fromArray($data, $filename, $headers);
            
            switch ($format) {
                case 'csv':
                    $exporter->toCSV();
                    break;
                case 'excel':
                default:
                    $exporter->toExcel();
                    break;
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get single record
     */
    private function getSingleRecord($id) {
        $table = $this->getTableName();
        $sql = "SELECT * FROM $table WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get multiple records with pagination
     */
    private function getMultipleRecords($page, $search, $perPage) {
        $table = $this->getTableName();
        $offset = ($page - 1) * $perPage;
        
        // Build WHERE clause for search
        $where = '';
        $params = [];
        if (!empty($search)) {
            $where = "WHERE no_anggota LIKE :search OR nama_lengkap LIKE :search OR email LIKE :search";
            $params['search'] = "%$search%";
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM $table $where";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get data
        $sql = "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Build pagination
        $totalPages = ceil($total / $perPage);
        $pagination = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $total,
            'per_page' => $perPage,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ];
        
        return ['data' => $data, 'pagination' => $pagination];
    }
    
    /**
     * Create new record
     */
    private function createRecord($data) {
        $table = $this->getTableName();
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns, created_at) VALUES ($placeholders, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update record
     */
    private function updateRecord($id, $data) {
        $table = $this->getTableName();
        $setClause = [];
        
        foreach ($data as $key => $value) {
            $setClause[] = "$key = :$key";
        }
        
        $setClause = implode(', ', $setClause);
        $sql = "UPDATE $table SET $setClause, updated_at = NOW() WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }
    
    /**
     * Delete record
     */
    private function deleteRecord($id) {
        $table = $this->getTableName();
        $sql = "DELETE FROM $table WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
    }
    
    /**
     * Bulk delete records
     */
    private function bulkDeleteRecords($ids) {
        $table = $this->getTableName();
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "DELETE FROM $table WHERE id IN ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);
        return $stmt->rowCount();
    }
    
    /**
     * Get export data
     */
    private function getExportData($search) {
        $table = $this->getTableName();
        $where = '';
        $params = [];
        
        if (!empty($search)) {
            $where = "WHERE no_anggota LIKE :search OR nama_lengkap LIKE :search OR email LIKE :search";
            $params['search'] = "%$search%";
        }
        
        $sql = "SELECT * FROM $table $where ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get table headers for export
     */
    private function getTableHeaders() {
        switch ($this->module) {
            case 'anggota':
                return ['ID', 'No. Anggota', 'Nama Lengkap', 'Email', 'Telepon', 'Alamat', 'Status', 'Tanggal Bergabung'];
            case 'simpanan':
                return ['ID', 'No. Transaksi', 'ID Anggota', 'Jenis Simpanan', 'Jumlah', 'Tanggal'];
            case 'pinjaman':
                return ['ID', 'No. Pinjaman', 'ID Anggota', 'Jumlah', 'Bunga', 'Tenor', 'Status'];
            default:
                return [];
        }
    }
    
    /**
     * Get table name based on module
     */
    private function getTableName() {
        $tableMap = [
            'anggota' => 'anggota',
            'simpanan' => 'simpanan',
            'pinjaman' => 'pinjaman',
            'produk' => 'produk',
            'pemasok' => 'pemasok'
        ];
        
        if (!isset($tableMap[$this->module])) {
            throw new Exception("Unknown module: $this->module");
        }
        
        return $tableMap[$this->module];
    }
    
    /**
     * Sanitize input data
     */
    private function sanitizeInput($data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
    
    /**
     * Send JSON response
     */
    private function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}

// Handle request
try {
    $api = new CrudAPI();
    $api->handleRequest();
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
