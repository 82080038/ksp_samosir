<?php
/**
 * Multi-Database API Router for KSP Samosir
 * Updated untuk arsitektur 5 database dengan routing yang optimal
 */

require_once __DIR__ . '/../config/database_multi.php';
require_once __DIR__ . '/../app/controllers/DigitalRegistrationControllerMulti.php';

class MultiDatabaseAPIRouter {
    private $controllers = [];
    private $dbConnections = [];
    
    public function __construct() {
        $this->initializeControllers();
        $this->testDatabaseConnections();
    }
    
    /**
     * Initialize controllers dengan multi-database support
     */
    private function initializeControllers() {
        try {
            $this->controllers = [
                'registration' => new DigitalRegistrationController(),
                'members' => new MemberController(),
                'loans' => new LoanController(),
                'savings' => new SavingsController(),
                'analytics' => new AnalyticsController(),
                'system' => new SystemController()
            ];
        } catch (Exception $e) {
            error_log("Controller initialization failed: " . $e->getMessage());
            throw new Exception("Failed to initialize API controllers");
        }
    }
    
    /**
     * Test all database connections
     */
    private function testDatabaseConnections() {
        $testResults = DatabaseManager::testConnections();
        $hasErrors = false;
        
        foreach ($testResults as $dbType => $result) {
            if ($result['status'] === 'error') {
                error_log("Database connection failed ($dbType): " . $result['message']);
                $hasErrors = true;
            }
        }
        
        if ($hasErrors) {
            // Log error but don't fail completely - allow partial functionality
            error_log("Some database connections failed. API may have limited functionality.");
        }
    }
    
    /**
     * Route API request dengan multi-database support
     */
    public function route($endpoint, $method, $data = []) {
        try {
            // Set headers
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key, X-DB-Type');
            
            // Handle preflight OPTIONS request
            if ($method === 'OPTIONS') {
                http_response_code(200);
                exit;
            }
            
            // Parse endpoint
            $parts = explode('/', trim($endpoint, '/'));
            $resource = $parts[0] ?? '';
            $action = $parts[1] ?? '';
            $id = $parts[2] ?? null;
            
            // Get database type from header (for cross-database operations)
            $dbType = $_SERVER['HTTP_X_DB_TYPE'] ?? 'core';
            
            // Route to appropriate handler
            switch ($resource) {
                case 'health':
                    return $this->healthCheck();
                    
                case 'database':
                    return $this->databaseInfo();
                    
                case 'registration':
                    return $this->handleRegistration($action, $id, $method, $data);
                    
                case 'members':
                    return $this->handleMembers($action, $id, $method, $data, $dbType);
                    
                case 'loans':
                    return $this->handleLoans($action, $id, $method, $data);
                    
                case 'savings':
                    return $this->handleSavings($action, $id, $method, $data);
                    
                case 'analytics':
                    return $this->handleAnalytics($action, $method, $data);
                    
                case 'system':
                    return $this->handleSystem($action, $method, $data);
                    
                default:
                    return $this->errorResponse('Endpoint not found', 404);
            }
            
        } catch (Exception $e) {
            error_log("API Error: " . $e->getMessage());
            return $this->errorResponse('Internal server error', 500);
        }
    }
    
    /**
     * Health check endpoint
     */
    private function healthCheck() {
        $dbStatus = DatabaseManager::testConnections();
        $allHealthy = true;
        
        foreach ($dbStatus as $result) {
            if ($result['status'] === 'error') {
                $allHealthy = false;
                break;
            }
        }
        
        return [
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'timestamp' => date('c'),
            'databases' => $dbStatus,
            'version' => '2.0.0-multi-db'
        ];
    }
    
    /**
     * Database information endpoint
     */
    private function databaseInfo() {
        return [
            'success' => true,
            'data' => DatabaseManager::getDatabaseInfo()
        ];
    }
    
    /**
     * Handle registration endpoints (uses registration database)
     */
    private function handleRegistration($action, $id, $method, $data) {
        $controller = $this->controllers['registration'];
        
        switch ($action) {
            case 'form':
                if ($method === 'GET') {
                    return $controller->getForm();
                }
                break;
                
            case 'draft':
                if ($method === 'POST') {
                    return $controller->saveDraft($data);
                }
                break;
                
            case 'submit':
                if ($method === 'POST') {
                    return $controller->submitRegistration($data);
                }
                break;
                
            case 'statistics':
                if ($method === 'GET') {
                    return $controller->getStatistics();
                }
                break;
                
            default:
                if (!$action && $id && $method === 'GET') {
                    return $controller->getSubmission($id);
                }
                
                if ($id && $method === 'PUT') {
                    if (strpos($action, 'approve') !== false) {
                        return $controller->approveRegistration($id, $data['admin_id'], $data['notes'] ?? '');
                    }
                    if (strpos($action, 'reject') !== false) {
                        return $controller->rejectRegistration($id, $data['admin_id'], $data['reason'] ?? '');
                    }
                    if (strpos($action, 'print') !== false) {
                        return $controller->getPrintableForm($id);
                    }
                }
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle member endpoints (uses core database)
     */
    private function handleMembers($action, $id, $method, $data, $dbType) {
        // Force core database for members
        $db = new MultiDBQuery('core');
        
        switch ($method) {
            case 'GET':
                if (!$action) {
                    // List members
                    $page = intval($data['page'] ?? 1);
                    $limit = intval($data['limit'] ?? 20);
                    $search = $data['search'] ?? null;
                    
                    $whereClause = "WHERE status = 'aktif'";
                    $params = [];
                    
                    if ($search) {
                        $whereClause .= " AND (nama_lengkap LIKE ? OR no_anggota LIKE ? OR nik LIKE ?)";
                        $params = ["%$search%", "%$search%", "%$search%"];
                    }
                    
                    $offset = ($page - 1) * $limit;
                    
                    $members = $db->fetchAll(
                        "SELECT id, no_anggota, nama_lengkap, nik, no_hp, email, 
                                tanggal_gabung, status FROM anggota $whereClause
                         ORDER BY tanggal_gabung DESC LIMIT ? OFFSET ?",
                        array_merge($params, [$limit, $offset]),
                        str_repeat('s', count($params)) . 'ii'
                    );
                    
                    $totalCount = ($db->fetchRow(
                        "SELECT COUNT(*) as count FROM anggota $whereClause",
                        $params,
                        str_repeat('s', count($params))
                    ) ?? [])['count'] ?? 0;
                    
                    return [
                        'success' => true,
                        'data' => [
                            'items' => $members,
                            'total' => $totalCount,
                            'page' => $page,
                            'limit' => $limit,
                            'total_pages' => ceil($totalCount / $limit)
                        ]
                    ];
                }
                
                if ($id) {
                    // Get specific member
                    $member = $db->fetchRow(
                        "SELECT * FROM anggota WHERE id = ?",
                        [$id],
                        'i'
                    );
                    
                    if (!$member) {
                        return $this->errorResponse('Member not found', 404);
                    }
                    
                    return [
                        'success' => true,
                        'data' => $member
                    ];
                }
                break;
                
            case 'POST':
                // Create member
                $required = ['nama_lengkap', 'nik', 'tanggal_lahir', 'jenis_kelamin'];
                foreach ($required as $field) {
                    if (!isset($data[$field]) || empty($data[$field])) {
                        return $this->errorResponse("Missing required field: $field", 400);
                    }
                }
                
                $noAnggota = $this->generateMemberNumber();
                
                $result = $db->executeNonQuery(
                    "INSERT INTO anggota (no_anggota, nama_lengkap, nik, tempat_lahir, tanggal_lahir, 
                                      jenis_kelamin, alamat, no_hp, email, pekerjaan, tanggal_gabung, status) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')",
                    [
                        $noAnggota,
                        $data['nama_lengkap'],
                        $data['nik'],
                        $data['tempat_lahir'] ?? '',
                        $data['tanggal_lahir'],
                        $data['jenis_kelamin'],
                        $data['alamat'] ?? '',
                        $data['no_hp'] ?? '',
                        $data['email'] ?? '',
                        $data['pekerjaan'] ?? '',
                        date('Y-m-d')
                    ],
                    'sssssssssss'
                );
                
                // Log to system database
                systemDB()->executeNonQuery(
                    "INSERT INTO audit_logs (action, table_name, record_id, new_values, ip_address) 
                     VALUES ('create_member', 'anggota', ?, ?, ?)",
                    [$result['last_id'], json_encode($data), $_SERVER['REMOTE_ADDR'] ?? ''],
                    'iss'
                );
                
                return [
                    'success' => true,
                    'message' => 'Member created successfully',
                    'data' => ['id' => $result['last_id'], 'no_anggota' => $noAnggota]
                ];
                
            case 'PUT':
                if ($id) {
                    // Update member
                    $updateFields = [];
                    $params = [];
                    $types = '';
                    
                    $allowedFields = ['nama_lengkap', 'alamat', 'no_hp', 'email', 'pekerjaan'];
                    foreach ($allowedFields as $field) {
                        if (isset($data[$field])) {
                            $updateFields[] = "$field = ?";
                            $params[] = $data[$field];
                            $types .= 's';
                        }
                    }
                    
                    if (empty($updateFields)) {
                        return $this->errorResponse("No valid fields to update", 400);
                    }
                    
                    $params[] = $id;
                    $types .= 'i';
                    
                    $db->executeNonQuery(
                        "UPDATE anggota SET " . implode(', ', $updateFields) . " WHERE id = ?",
                        $params,
                        $types
                    );
                    
                    return [
                        'success' => true,
                        'message' => 'Member updated successfully'
                    ];
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle analytics endpoints (uses analytics database)
     */
    private function handleAnalytics($action, $method, $data) {
        $db = new MultiDBQuery('analytics');
        
        switch ($action) {
            case 'kpis':
                if ($method === 'GET') {
                    // Get KPIs from multiple databases
                    $coreDB = new MultiDBQuery('core');
                    $registrationDB = new MultiDBQuery('registration');
                    
                    $kpis = [
                        'total_members' => ($coreDB->fetchRow("SELECT COUNT(*) as count FROM anggota WHERE status = 'aktif'") ?? [])['count'] ?? 0,
                        'total_savings' => ($coreDB->fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM simpanan WHERE status = 'aktif'") ?? [])['total'] ?? 0,
                        'total_loans' => ($coreDB->fetchRow("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total FROM pinjaman WHERE status IN ('disetujui', 'dicairkan')") ?? [])['total'] ?? 0,
                        'registration_stats' => $registrationDB->fetchRow(
                            "SELECT COUNT(*) as total, 
                                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                                    COUNT(CASE WHEN DATE(submission_date) = CURDATE() THEN 1 END) as today
                             FROM registration_submissions"
                        )
                    ];
                    
                    return [
                        'success' => true,
                        'data' => $kpis
                    ];
                }
                break;
                
            case 'events':
                if ($method === 'POST') {
                    // Log analytics event
                    $required = ['event_type'];
                    foreach ($required as $field) {
                        if (!isset($data[$field])) {
                            return $this->errorResponse("Missing required field: $field", 400);
                        }
                    }
                    
                    $result = $db->executeNonQuery(
                        "INSERT INTO analytics_events (event_type, event_category, user_id, session_id, properties, ip_address, user_agent) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [
                            $data['event_type'],
                            $data['event_category'] ?? null,
                            $data['user_id'] ?? null,
                            $data['session_id'] ?? null,
                            json_encode($data['properties'] ?? []),
                            $_SERVER['REMOTE_ADDR'] ?? '',
                            $_SERVER['HTTP_USER_AGENT'] ?? ''
                        ],
                        'sssssss'
                    );
                    
                    return [
                        'success' => true,
                        'message' => 'Event logged successfully',
                        'event_id' => $result['last_id']
                    ];
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle system endpoints (uses system database)
     */
    private function handleSystem($action, $method, $data) {
        $db = new MultiDBQuery('system');
        
        switch ($action) {
            case 'config':
                if ($method === 'GET') {
                    $configs = $db->fetchAll("SELECT * FROM system_configs");
                    return [
                        'success' => true,
                        'data' => $configs
                    ];
                }
                
                if ($method === 'POST') {
                    $required = ['config_key', 'config_value'];
                    foreach ($required as $field) {
                        if (!isset($data[$field])) {
                            return $this->errorResponse("Missing required field: $field", 400);
                        }
                    }
                    
                    $db->executeNonQuery(
                        "INSERT INTO system_configs (config_key, config_value, config_type, description) 
                         VALUES (?, ?, ?, ?) 
                         ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), updated_at = CURRENT_TIMESTAMP",
                        [
                            $data['config_key'],
                            $data['config_value'],
                            $data['config_type'] ?? 'string',
                            $data['description'] ?? ''
                        ],
                        'ssss'
                    );
                    
                    return [
                        'success' => true,
                        'message' => 'Configuration saved successfully'
                    ];
                }
                break;
                
            case 'logs':
                if ($method === 'GET') {
                    $logs = $db->fetchAll(
                        "SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 100"
                    );
                    return [
                        'success' => true,
                        'data' => $logs
                    ];
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Helper methods
     */
    private function generateMemberNumber() {
        $db = new MultiDBQuery('core');
        $year = date('Y');
        $lastMember = $db->fetchRow(
            "SELECT no_anggota FROM anggota WHERE no_anggota LIKE ? ORDER BY id DESC LIMIT 1",
            [$year . '%'],
            's'
        );
        
        $newNum = $lastMember ? intval(substr($lastMember['no_anggota'], -4)) + 1 : 1;
        return $year . str_pad($newNum, 4, '0', STR_PAD_LEFT);
    }
    
    private function errorResponse($message, $statusCode = 400) {
        http_response_code($statusCode);
        return [
            'success' => false,
            'error' => $message,
            'status_code' => $statusCode
        ];
    }
}

// Handle API requests
if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    try {
        $method = $_SERVER['REQUEST_METHOD'];
        $endpoint = $_SERVER['REQUEST_URI'];
        
        // Extract endpoint from URL
        $endpoint = preg_replace('/^.*\/api\//', '', $endpoint);
        $endpoint = explode('?', $endpoint)[0]; // Remove query string
        
        // Get request data
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $data = array_merge($_GET, $_POST, $input);
        
        // Route request
        $router = new MultiDatabaseAPIRouter();
        $response = $router->route($endpoint, $method, $data);
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Internal server error',
            'message' => $e->getMessage()
        ]);
    }
}
?>
