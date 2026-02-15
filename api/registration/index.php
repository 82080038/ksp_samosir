<?php
/**
 * KSP Samosir - Digital Registration API Routes
 * API endpoints untuk pendaftaran anggota digital
 */

// Include required files
require_once __DIR__ . '/../../app/controllers/DigitalRegistrationController.php';

class DigitalRegistrationAPI
{
    private $controller;
    
    public function __construct()
    {
        $this->controller = new DigitalRegistrationController();
    }
    
    /**
     * Route API requests
     */
    public function route($method, $endpoint, $data = null)
    {
        // Parse endpoint
        $parts = explode('/', trim($endpoint, '/'));
        $resource = $parts[0] ?? '';
        $action = $parts[1] ?? '';
        $id = $parts[2] ?? null;
        
        // Add CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');
        
        // Handle preflight requests
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        try {
            switch ($resource) {
                case 'form':
                    return $this->handleForm($method, $id);
                    
                case 'draft':
                    return $this->handleDraft($method, $data);
                    
                case 'submit':
                    return $this->handleSubmit($method, $data);
                    
                case 'submission':
                    return $this->handleSubmission($method, $id, $data);
                    
                case 'submissions':
                    return $this->handleSubmissions($method, $data);
                    
                case 'approve':
                    return $this->handleApprove($method, $id, $data);
                    
                case 'reject':
                    return $this->handleReject($method, $id, $data);
                    
                case 'print':
                    return $this->handlePrint($method, $id);
                    
                case 'statistics':
                    return $this->handleStatistics($method);
                    
                default:
                    return $this->errorResponse('Endpoint not found', 404);
            }
        } catch (Exception $e) {
            error_log("Digital Registration API Error: " . $e->getMessage());
            return $this->errorResponse('Internal server error', 500);
        }
    }
    
    /**
     * Handle form endpoints
     */
    private function handleForm($method, $id)
    {
        if ($method === 'GET') {
            if ($id) {
                // GET /form/{id} - Get specific form
                return $this->controller->getRegistrationForm($id);
            } else {
                // GET /form - Get default form
                return $this->controller->getRegistrationForm();
            }
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle draft endpoints
     */
    private function handleDraft($method, $data)
    {
        switch ($method) {
            case 'POST':
                // POST /draft - Save new draft
                return $this->controller->saveDraft($data);
                
            case 'PUT':
                // PUT /draft - Update existing draft
                if (isset($data['submission_id']) && isset($data['token'])) {
                    return $this->controller->updateDraft(
                        $data['submission_id'], 
                        $data, 
                        $data['token']
                    );
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed or missing parameters', 405);
    }
    
    /**
     * Handle submit endpoints
     */
    private function handleSubmit($method, $data)
    {
        if ($method === 'POST') {
            if (isset($data['submission_id']) && isset($data['token'])) {
                return $this->controller->submitRegistration(
                    $data['submission_id'], 
                    $data, 
                    $data['token']
                );
            } else {
                return $this->errorResponse('Missing submission_id or token', 400);
            }
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle submission endpoints
     */
    private function handleSubmission($method, $id, $data)
    {
        if ($method === 'GET' && $id) {
            // GET /submission/{id} - Get specific submission
            return $this->controller->getSubmission($id);
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle submissions endpoints
     */
    private function handleSubmissions($method, $data)
    {
        if ($method === 'GET') {
            // GET /submissions?status=x&limit=y&offset=z
            $status = $data['status'] ?? null;
            $limit = $data['limit'] ?? 20;
            $offset = $data['offset'] ?? 0;
            
            return $this->controller->getSubmissions($status, $limit, $offset);
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle approve endpoints
     */
    private function handleApprove($method, $id, $data)
    {
        if ($method === 'POST' && $id) {
            if (isset($data['admin_id'])) {
                return $this->controller->approveRegistration($id, $data['admin_id']);
            } else {
                return $this->errorResponse('Missing admin_id', 400);
            }
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle reject endpoints
     */
    private function handleReject($method, $id, $data)
    {
        if ($method === 'POST' && $id) {
            if (isset($data['admin_id']) && isset($data['reason'])) {
                return $this->controller->rejectRegistration(
                    $id, 
                    $data['admin_id'], 
                    $data['reason']
                );
            } else {
                return $this->errorResponse('Missing admin_id or reason', 400);
            }
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle print endpoints
     */
    private function handlePrint($method, $id)
    {
        if ($method === 'GET' && $id) {
            // GET /print/{id} - Generate printable form
            return $this->controller->generatePrintableForm($id);
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle statistics endpoints
     */
    private function handleStatistics($method)
    {
        if ($method === 'GET') {
            return $this->controller->getStatistics();
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Error response helper
     */
    private function errorResponse($message, $code = 400)
    {
        http_response_code($code);
        return [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Success response helper
     */
    private function successResponse($data = null, $message = 'Success')
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// Route the request
$api = new DigitalRegistrationAPI();

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['REQUEST_URI'];
$endpoint = str_replace('/api/registration/', '', $endpoint);

// Parse query parameters for GET requests
if ($method === 'GET' && strpos($endpoint, '?') !== false) {
    list($endpoint, $queryString) = explode('?', $endpoint, 2);
    parse_str($queryString, $getData);
} else {
    $getData = [];
}

// Get JSON input for POST/PUT requests
$input = json_decode(file_get_contents('php://input'), true);

// Merge GET data for filtering
$data = array_merge($getData, $input ?? []);

// Route and return response
$response = $api->route($method, $endpoint, $data);
echo json_encode($response, JSON_PRETTY_PRINT);
?>
