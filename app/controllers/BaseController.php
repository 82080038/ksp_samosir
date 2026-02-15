<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../helpers/DependencyManager.php';

/**
 * KSP Samosir Standardized Base Controller
 * Consistent controller foundation with dependency management
 */
class BaseController {
    protected $user;
    protected $role;
    protected $pageInfo;
    
    public function __construct() {
        // Initialize dependencies
        $this->pageInfo = initView();
        
        // Get current user
        $this->user = getCurrentUser();
        $this->role = $this->user['role'] ?? null;
    }
    
    /**
     * Legacy auth guard (for backward compatibility)
     */
    protected function ensureLoginAndRole($roles = null) {
        // DISABLED for development
        // requireLogin();
        // if ($roles !== null && !hasRole($roles)) {
        //     flashMessage('error', 'Akses tidak diizinkan untuk peran Anda');
        //     redirect('dashboard');
        // }
    }

    /**
     * Standard render method with dependency management
     */
    public function render($viewPath, $data = [], $useLayout = true) {
        // Ensure all helpers are loaded
        loadHelper('FormatHelper');
        loadHelper('TitleHelper');
        loadHelper('AuthHelper');
        
        // Set common data
        $data['user'] = $this->user;
        $data['role'] = $this->role;
        $data['pageInfo'] = $this->pageInfo;
        
        extract($data);
        
        if ($useLayout && file_exists(APP_PATH . '/app/views/layouts/main.php')) {
            ob_start();
            require APP_PATH . '/app/views/' . $viewPath . '.php';
            $content = ob_get_clean();
            
            ob_start();
            require APP_PATH . '/app/views/layouts/main.php';
            echo ob_get_clean();
        } else {
            require APP_PATH . '/app/views/' . $viewPath . '.php';
        }
    }
    
    /**
     * SPA render method
     */
    protected function renderSPA($viewPath, $data = []) {
        // Ensure all helpers are loaded
        loadHelper('FormatHelper');
        loadHelper('TitleHelper');
        
        // Set common data
        $data['user'] = $this->user;
        $data['role'] = $this->role;
        $data['pageInfo'] = $this->pageInfo;
        
        extract($data);
        
        if (file_exists(APP_PATH . '/app/views/layouts/spa-main.php')) {
            ob_start();
            require APP_PATH . '/app/views/' . $viewPath . '.php';
            $content = ob_get_clean();
            
            ob_start();
            require APP_PATH . '/app/views/layouts/spa-main.php';
            echo ob_get_clean();
        } else {
            require APP_PATH . '/app/views/' . $viewPath . '.php';
        }
    }
    
    /**
     * JSON response method
     */
    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
    
    /**
     * Redirect method
     */
    protected function redirect($url) {
        header("Location: " . base_url($url));
        exit;
    }
    
    /**
     * Validate user role
     */
    protected function requireRole($roles) {
        // DISABLED for development
        // if (!is_array($roles)) {
        //     $roles = [$roles];
        // }
        
        // if (!in_array($this->role, $roles)) {
        //     $this->redirect('dashboard');
        // }
    }
    
    /**
     * Get POST data safely
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Get GET data safely
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($fields, $data = null) {
        if ($data === null) {
            $data = $_POST;
        }
        
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "Field {$field} is required";
            }
        }
        
        return $errors;
    }
    
    /**
     * Standard pagination
     */
    protected function paginate($query, $page = 1, $perPage = 10) {
        $page = max(1, (int)$page);
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM ({$query}) as subquery";
        $total = (fetchRow($countQuery) ?? [])['total'] ?? 0;
        
        // Get paginated data
        $dataQuery = "{$query} LIMIT {$perPage} OFFSET {$offset}";
        $data = fetchAll($dataQuery);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_next' => $page < ceil($total / $perPage),
                'has_prev' => $page > 1
            ]
        ];
    }
    
    /**
     * Standard error response
     */
    protected function error($message, $statusCode = 400) {
        if (requestIsAjax()) {
            $this->json([
                'success' => false,
                'message' => $message
            ], $statusCode);
        } else {
            $this->render('errors/error', [
                'message' => $message,
                'statusCode' => $statusCode
            ]);
        }
    }
    
    /**
     * Standard success response
     */
    protected function success($message, $data = []) {
        if (requestIsAjax()) {
            $this->json([
                'success' => true,
                'message' => $message,
                'data' => $data
            ]);
        } else {
            flashMessage('success', $message);
            $this->redirect($this->post('redirect', 'dashboard'));
        }
    }
}
