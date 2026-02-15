<?php
/**
 * KSP Samosir - Agricultural API Routes
 * API endpoints untuk koperasi pertanian
 */

// Include required files
require_once __DIR__ . '/../../app/controllers/AgriculturalController.php';

class AgriculturalAPI
{
    private $controller;
    
    public function __construct()
    {
        $this->controller = new AgriculturalController();
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
                case 'dashboard':
                    return $this->handleDashboard($method);
                    
                case 'lahan':
                    return $this->handleLahan($method, $id, $data);
                    
                case 'tanaman':
                    return $this->handleTanaman($method);
                    
                case 'planning':
                    return $this->handlePlanning($method, $id, $data);
                    
                case 'inventory':
                    return $this->handleInventory($method, $data);
                    
                case 'statistics':
                    return $this->handleStatistics($method);
                    
                default:
                    return $this->errorResponse('Endpoint not found', 404);
            }
        } catch (Exception $e) {
            error_log("Agricultural API Error: " . $e->getMessage());
            return $this->errorResponse('Internal server error', 500);
        }
    }
    
    /**
     * Handle dashboard endpoints
     */
    private function handleDashboard($method)
    {
        if ($method === 'GET') {
            return $this->controller->getDashboard();
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle lahan endpoints
     */
    private function handleLahan($method, $id, $data)
    {
        switch ($method) {
            case 'GET':
                if ($id) {
                    // GET /lahan/{id} - Get lahan for specific anggota
                    return $this->controller->getLahan($id);
                } else {
                    // GET /lahan - Get all lahan
                    return $this->controller->getLahan();
                }
                
            case 'POST':
                // POST /lahan - Create new lahan
                return $this->createLahan($data);
                
            case 'PUT':
                if ($id) {
                    // PUT /lahan/{id} - Update lahan
                    return $this->updateLahan($id, $data);
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle tanaman endpoints
     */
    private function handleTanaman($method)
    {
        if ($method === 'GET') {
            return $this->controller->getTanaman();
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle planning endpoints
     */
    private function handlePlanning($method, $id, $data)
    {
        switch ($method) {
            case 'GET':
                if ($id) {
                    // GET /planning/{status} - Get planning by status
                    return $this->controller->getPlanning($id);
                } else {
                    // GET /planning - Get all planning
                    return $this->controller->getPlanning();
                }
                
            case 'POST':
                // POST /planning - Create new planning
                return $this->controller->createPlanning($data);
                
            case 'PUT':
                if ($id) {
                    // PUT /planning/{id} - Update planning
                    return $this->updatePlanning($id, $data);
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle inventory endpoints
     */
    private function handleInventory($method, $data)
    {
        switch ($method) {
            case 'GET':
                if (isset($data['jenis_barang'])) {
                    // GET /inventory?jenis_barang=pupuk
                    return $this->controller->getInventory($data['jenis_barang']);
                } else {
                    // GET /inventory - Get all inventory
                    return $this->controller->getInventory();
                }
                
            case 'POST':
                // POST /inventory - Add inventory transaction
                return $this->controller->addInventoryTransaction($data);
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
     * Create new lahan
     */
    private function createLahan($data)
    {
        // Validation
        $required = ['anggota_id', 'kode_lahan', 'nama_lahan', 'luas_lahan', 'jenis_tanah', 'kualitas_tanah', 'status_lahan'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->errorResponse("Field {$field} is required", 400);
            }
        }
        
        // Insert to database
        $conn = new mysqli('localhost', 'root', 'root', 'ksp_samosir');
        
        $sql = "INSERT INTO ksp_agricultural_lahan 
                (anggota_id, kode_lahan, nama_lahan, luas_lahan, jenis_tanah, kualitas_tanah, 
                 koordinat_lat, koordinat_lng, sertifikat_nomor, sertifikat_tanggal, status_lahan, 
                 masa_sewa_mulai, masa_sewa_akhir, catatan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issdsssssssss", 
            $data['anggota_id'],
            $data['kode_lahan'],
            $data['nama_lahan'],
            $data['luas_lahan'],
            $data['jenis_tanah'],
            $data['kualitas_tanah'],
            $data['koordinat_lat'] ?? null,
            $data['koordinat_lng'] ?? null,
            $data['sertifikat_nomor'] ?? null,
            $data['sertifikat_tanggal'] ?? null,
            $data['status_lahan'],
            $data['masa_sewa_mulai'] ?? null,
            $data['masa_sewa_akhir'] ?? null,
            $data['catatan'] ?? ''
        );
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Lahan berhasil ditambahkan',
                'lahan_id' => $conn->insert_id
            ];
        } else {
            return $this->errorResponse('Gagal menambah lahan: ' . $stmt->error, 500);
        }
    }
    
    /**
     * Update lahan
     */
    private function updateLahan($id, $data)
    {
        $conn = new mysqli('localhost', 'root', 'root', 'ksp_samosir');
        
        $sql = "UPDATE ksp_agricultural_lahan SET 
                nama_lahan = ?, 
                luas_lahan = ?, 
                jenis_tanah = ?, 
                kualitas_tanah = ?,
                koordinat_lat = ?,
                koordinat_lng = ?,
                sertifikat_nomor = ?,
                sertifikat_tanggal = ?,
                status_lahan = ?,
                masa_sewa_mulai = ?,
                masa_sewa_akhir = ?,
                catatan = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssssssssssi", 
            $data['nama_lahan'],
            $data['luas_lahan'],
            $data['jenis_tanah'],
            $data['kualitas_tanah'],
            $data['koordinat_lat'] ?? null,
            $data['koordinat_lng'] ?? null,
            $data['sertifikat_nomor'] ?? null,
            $data['sertifikat_tanggal'] ?? null,
            $data['status_lahan'],
            $data['masa_sewa_mulai'] ?? null,
            $data['masa_sewa_akhir'] ?? null,
            $data['catatan'] ?? '',
            $id
        );
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Lahan berhasil diupdate'
            ];
        } else {
            return $this->errorResponse('Gagal update lahan: ' . $stmt->error, 500);
        }
    }
    
    /**
     * Update planning
     */
    private function updatePlanning($id, $data)
    {
        if (isset($data['status'])) {
            return $this->controller->updatePlanningStatus($id, $data['status'], $data);
        } else {
            return $this->errorResponse('Status is required for update', 400);
        }
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
$api = new AgriculturalAPI();

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['REQUEST_URI'];
$endpoint = str_replace('/api/agricultural/', '', $endpoint);

// Parse query parameters for GET requests
if ($method === 'GET' && strpos($endpoint, '?') !== false) {
    list($endpoint, $queryString) = explode('?', $endpoint, 2);
    parse_str($queryString, $getData);
} else {
    $getData = [];
}

// Get JSON input for POST/PUT requests
$input = json_decode(file_get_contents('php://input'), true);

// Merge GET data for inventory filtering
$data = array_merge($getData, $input ?? []);

// Route and return response
$response = $api->route($method, $endpoint, $data);
echo json_encode($response, JSON_PRETTY_PRINT);
?>
