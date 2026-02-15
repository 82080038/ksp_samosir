<?php
/**
 * Registration API - Multi-Database Version
 * Updated untuk arsitektur 5 database KSP Samosir
 */

require_once __DIR__ . '/../app/controllers/DigitalRegistrationControllerMulti.php';

class RegistrationAPIRouter {
    private $controller;
    
    public function __construct() {
        $this->controller = new DigitalRegistrationController();
    }
    
    public function route($endpoint, $method, $data = []) {
        try {
            // Set headers
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
            
            // Handle preflight OPTIONS request
            if ($method === 'OPTIONS') {
                http_response_code(200);
                exit;
            }
            
            // Parse endpoint
            $parts = explode('/', trim($endpoint, '/'));
            $action = $parts[0] ?? '';
            $id = $parts[1] ?? null;
            
            switch ($action) {
                case 'form':
                    if ($method === 'GET') {
                        return $this->controller->getForm();
                    }
                    break;
                    
                case 'draft':
                    if ($method === 'POST') {
                        return $this->controller->saveDraft($data);
                    }
                    break;
                    
                case 'submit':
                    if ($method === 'POST') {
                        return $this->controller->submitRegistration($data);
                    }
                    break;
                    
                case 'statistics':
                    if ($method === 'GET') {
                        return $this->controller->getStatistics();
                    }
                    break;
                    
                default:
                    if (!$action && $id && $method === 'GET') {
                        return $this->controller->getSubmission($id);
                    }
                    
                    if ($id && $method === 'PUT') {
                        if (strpos($action, 'approve') !== false) {
                            return $this->controller->approveRegistration($id, $data['admin_id'], $data['notes'] ?? '');
                        }
                        if (strpos($action, 'reject') !== false) {
                            return $this->controller->rejectRegistration($id, $data['admin_id'], $data['reason'] ?? '');
                        }
                        if (strpos($action, 'print') !== false) {
                            return $this->controller->getPrintableForm($id);
                        }
                    }
            }
            
            return [
                'success' => false,
                'error' => 'Endpoint not found',
                'message' => 'Invalid endpoint or method'
            ];
            
        } catch (Exception $e) {
            error_log("Registration API Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ];
        }
    }
}

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['REQUEST_URI'] ?? '';

// Extract endpoint from URL
$endpoint = preg_replace('/^.*\/api\/registration\//', '', $endpoint);
$endpoint = explode('?', $endpoint)[0]; // Remove query string

// Get request data
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$data = array_merge($_GET, $_POST, $input);

// Route request
$router = new RegistrationAPIRouter();
$response = $router->route($endpoint, $method, $data);

echo json_encode($response, JSON_PRETTY_PRINT);
?>
