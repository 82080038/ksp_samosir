<?php
/**
 * Base Controller - INDUK UNTUK SEMUA CONTROLLER
 * 
 * Base class yang menyediakan fungsi-fungsi umum
 * untuk semua controller di aplikasi
 * 
 * @author KSP Samosir Development Team
 * @version 2.0.0
 */

abstract class BaseController {
    
    protected $database;
    protected $user;
    protected $pageData;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->database = $this->connectDatabase();
        $this->user = $this->getCurrentUser();
        $this->pageData = [];
    }
    
    /**
     * Connect ke database
     */
    protected function connectDatabase() {
        try {
            $pdo = new PDO(
                'mysql:host=localhost;dbname=ksp_samosir;charset=utf8mb4',
                'root',
                'root',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            return $pdo;
        } catch (PDOException $e) {
            if (getenv('APP_ENV') === 'development') {
                throw new Exception('Database connection failed: ' . $e->getMessage());
            }
            return null;
        }
    }
    
    /**
     * Get current user
     */
    protected function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }
    
    /**
     * Check if user has specific role
     */
    protected function hasRole($role) {
        if (!$this->user) return false;
        return $this->user['role'] === $role;
    }
    
    /**
     * Check if user has any of the specified roles
     */
    protected function hasAnyRole($roles) {
        if (!$this->user) return false;
        return in_array($this->user['role'], $roles);
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth() {
        if (!$this->user) {
            $this->redirect(base_url('login'));
        }
    }
    
    /**
     * Require specific role
     */
    protected function requireRole($role) {
        $this->requireAuth();
        if (!$this->hasRole($role)) {
            $this->showError('Access denied', 'You do not have permission to access this page.');
        }
    }
    
    /**
     * Require any of specified roles
     */
    protected function requireAnyRole($roles) {
        $this->requireAuth();
        if (!$this->hasAnyRole($roles)) {
            $this->showError('Access denied', 'You do not have permission to access this page.');
        }
    }
    
    /**
     * Render view
     */
    protected function renderView($view, $data = []) {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$view}");
        }
        
        // Extract data ke variables
        extract($data);
        
        // Start output buffering
        ob_start();
        include $viewFile;
        return ob_get_clean();
    }
    
    /**
     * Render page with layout
     */
    protected function renderPage($view, $data = []) {
        $content = $this->renderView($view, $data);
        return $content;
    }
    
    /**
     * JSON response
     */
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Success JSON response
     */
    protected function successResponse($data = null, $message = 'Success') {
        $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Error JSON response
     */
    protected function errorResponse($message = 'Error', $statusCode = 400, $data = null) {
        $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    
    /**
     * Redirect
     */
    protected function redirect($url, $statusCode = 302) {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
    
    /**
     * Get POST data
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Get GET data
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Get input data (POST or GET)
     */
    protected function input($key = null, $default = null) {
        if ($key === null) {
            return array_merge($_GET, $_POST);
        }
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($fields, $data = null) {
        if ($data === null) {
            $data = $this->input();
        }
        
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        return $errors;
    }
    
    /**
     * Show error page
     */
    protected function showError($title, $message, $statusCode = 500) {
        http_response_code($statusCode);
        
        $content = '
            <div class="container-fluid py-5">
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center">
                        <div class="error-code display-1 text-danger">' . $statusCode . '</div>
                        <h2 class="mb-4">' . htmlspecialchars($title) . '</h2>
                        <p class="text-muted mb-4">' . htmlspecialchars($message) . '</p>
                        <a href="' . base_url('dashboard') . '" class="btn btn-primary">
                            <i class="bi bi-house"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        ';
        
        echo $content;
        exit;
    }
    
    /**
     * Set flash message
     */
    protected function setFlash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get flash message
     */
    protected function getFlash($type) {
        $message = $_SESSION['flash'][$type] ?? null;
        if ($message) {
            unset($_SESSION['flash'][$type]);
        }
        return $message;
    }
    
    /**
     * Get pagination info
     */
    protected function getPagination($totalItems, $currentPage = 1, $itemsPerPage = 20) {
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        return [
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage,
            'total_pages' => $totalPages,
            'current_page' => $currentPage,
            'offset' => $offset,
            'has_prev' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'prev_page' => $currentPage > 1 ? $currentPage - 1 : null,
            'next_page' => $currentPage < $totalPages ? $currentPage + 1 : null
        ];
    }
    
    /**
     * Log activity
     */
    protected function logActivity($action, $description = '', $data = []) {
        if (!$this->database) return;
        
        try {
            $stmt = $this->database->prepare("
                INSERT INTO activity_logs (user_id, action, description, data, ip_address, user_agent, created_at)
                VALUES (:user_id, :action, :description, :data, :ip_address, :user_agent, NOW())
            ");
            
            $stmt->execute([
                ':user_id' => $this->user['id'] ?? null,
                ':action' => $action,
                ':description' => $description,
                ':data' => json_encode($data),
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log('Failed to log activity: ' . $e->getMessage());
        }
    }
    
    /**
     * Upload file
     */
    protected function uploadFile($fileInput, $uploadDir = 'uploads/', $allowedTypes = []) {
        if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload failed'];
        }
        
        $file = $_FILES[$fileInput];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];
        
        // Validate file type
        if (!empty($allowedTypes) && !in_array($fileType, $allowedTypes)) {
            return ['success' => false, 'message' => 'File type not allowed'];
        }
        
        // Generate unique filename
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = uniqid() . '.' . $fileExtension;
        
        // Create upload directory if not exists
        $fullUploadDir = __DIR__ . '/../../public/' . $uploadDir;
        if (!is_dir($fullUploadDir)) {
            mkdir($fullUploadDir, 0755, true);
        }
        
        // Move file
        $uploadPath = $fullUploadDir . $newFileName;
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            return [
                'success' => true,
                'filename' => $newFileName,
                'original_name' => $fileName,
                'size' => $fileSize,
                'type' => $fileType,
                'path' => $uploadDir . $newFileName
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to move uploaded file'];
        }
    }
    
    /**
     * Format date
     */
    protected function formatDate($date, $format = 'd/m/Y') {
        if (!$date) return '';
        $datetime = new DateTime($date);
        return $datetime->format($format);
    }
    
    /**
     * Format currency
     */
    protected function formatCurrency($amount, $currency = 'IDR') {
        if ($currency === 'IDR') {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }
        return number_format($amount, 2);
    }
    
    /**
     * Method yang harus diimplementasikan oleh child class
     */
    abstract public function index();
}
?>
