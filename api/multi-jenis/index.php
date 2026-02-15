<?php
/**
 * KSP Samosir - Multi Jenis Koperasi API Routes
 * API endpoints untuk management jenis koperasi
 */

// Include required files
require_once __DIR__ . '/../../app/controllers/MultiJenisKoperasiController.php';

class MultiJenisKoperasiAPI
{
    private $controller;
    
    public function __construct()
    {
        $this->controller = new MultiJenisKoperasiController();
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
                case 'jenis':
                    return $this->handleJenis($method, $action, $id, $data);
                    
                case 'detail':
                    return $this->handleDetail($method, $id, $data);
                    
                case 'setup':
                    return $this->handleSetup($method, $data);
                    
                case 'unit-setup':
                    return $this->handleUnitSetup($method, $data);
                    
                case 'update':
                    return $this->handleUpdate($method, $data);
                    
                case 'statistik':
                    return $this->handleStatistik($method);
                    
                default:
                    return $this->errorResponse('Endpoint not found', 404);
            }
        } catch (Exception $e) {
            error_log("Multi Jenis Koperasi API Error: " . $e->getMessage());
            return $this->errorResponse('Internal server error', 500);
        }
    }
    
    /**
     * Handle jenis koperasi endpoints
     */
    private function handleJenis($method, $action, $id, $data)
    {
        switch ($method) {
            case 'GET':
                if ($action === 'koperasi') {
                    // GET /jenis/koperasi - Get all jenis koperasi
                    return $this->controller->getJenisKoperasi();
                } elseif ($action === 'modul' && $id) {
                    // GET /jenis/modul/{id} - Get modul for jenis koperasi
                    return $this->controller->getDetailJenisKoperasi($id);
                }
                break;
                
            case 'POST':
                if ($action === 'koperasi') {
                    // POST /jenis/koperasi - Create new jenis koperasi
                    return $this->createJenisKoperasi($data);
                }
                break;
                
            case 'PUT':
                if ($action === 'koperasi' && $id) {
                    // PUT /jenis/koperasi/{id} - Update jenis koperasi
                    return $this->updateJenisKoperasi($id, $data);
                }
                break;
                
            case 'DELETE':
                if ($action === 'koperasi' && $id) {
                    // DELETE /jenis/koperasi/{id} - Delete jenis koperasi
                    return $this->deleteJenisKoperasi($id);
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle detail endpoints
     */
    private function handleDetail($method, $id, $data)
    {
        if ($method === 'GET' && $id) {
            // GET /detail/{id} - Get detail jenis koperasi
            return $this->controller->getDetailJenisKoperasi($id);
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle setup endpoints
     */
    private function handleSetup($method, $data)
    {
        switch ($method) {
            case 'POST':
                // POST /setup - Setup unit dengan jenis koperasi
                return $this->controller->setupUnitJenisKoperasi(
                    $data['unit_id'] ?? null,
                    $data['jenis_koperasi_id'] ?? null,
                    $data['modul_config'] ?? []
                );
                
            case 'PUT':
                // PUT /setup - Update setup unit
                return $this->controller->updateUnitKonfigurasi(
                    $data['unit_id'] ?? null,
                    $data['konfigurasi'] ?? []
                );
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle unit setup endpoints
     */
    private function handleUnitSetup($method, $data)
    {
        if ($method === 'GET') {
            // GET /unit-setup - Get all unit setup
            return $this->controller->getUnitSetup();
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle update endpoints
     */
    private function handleUpdate($method, $data)
    {
        if ($method === 'POST') {
            // POST /update - Update konfigurasi unit
            return $this->controller->updateUnitKonfigurasi(
                $data['unit_id'] ?? null,
                $data['konfigurasi'] ?? []
            );
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle statistik endpoints
     */
    private function handleStatistik($method)
    {
        if ($method === 'GET') {
            // GET /statistik - Get statistik setup
            return $this->controller->getStatistikSetup();
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Create new jenis koperasi
     */
    private function createJenisKoperasi($data)
    {
        // Validation
        $required = ['kode_jenis', 'nama_jenis', 'deskripsi'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->errorResponse("Field {$field} is required", 400);
            }
        }
        
        // Insert to database
        $conn = new mysqli('localhost', 'root', 'root', 'ksp_samosir');
        
        $sql = "INSERT INTO koperasi_jenis (kode_jenis, nama_jenis, deskripsi, icon, warna_tema, urutan_tampil) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", 
            $data['kode_jenis'],
            $data['nama_jenis'],
            $data['deskripsi'],
            $data['icon'] ?? 'fas fa-circle',
            $data['warna_tema'] ?? '#3b82f6',
            $data['urutan_tampil'] ?? 0
        );
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Jenis koperasi berhasil dibuat',
                'id' => $conn->insert_id
            ];
        } else {
            return $this->errorResponse('Gagal membuat jenis koperasi: ' . $stmt->error, 500);
        }
    }
    
    /**
     * Update jenis koperasi
     */
    private function updateJenisKoperasi($id, $data)
    {
        $conn = new mysqli('localhost', 'root', 'root', 'ksp_samosir');
        
        $sql = "UPDATE koperasi_jenis SET 
                nama_jenis = ?, 
                deskripsi = ?, 
                icon = ?, 
                warna_tema = ?, 
                urutan_tampil = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", 
            $data['nama_jenis'],
            $data['deskripsi'],
            $data['icon'] ?? 'fas fa-circle',
            $data['warna_tema'] ?? '#3b82f6',
            $data['urutan_tampil'] ?? 0,
            $id
        );
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Jenis koperasi berhasil diupdate'
            ];
        } else {
            return $this->errorResponse('Gagal update jenis koperasi: ' . $stmt->error, 500);
        }
    }
    
    /**
     * Delete jenis koperasi
     */
    private function deleteJenisKoperasi($id)
    {
        $conn = new mysqli('localhost', 'root', 'root', 'ksp_samosir');
        
        // Check if used by any unit
        $sql = "SELECT COUNT(*) as count FROM koperasi_unit_extended WHERE jenis_koperasi_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];
        
        if ($count > 0) {
            return $this->errorResponse('Tidak dapat menghapus jenis koperasi yang sudah digunakan', 400);
        }
        
        // Soft delete
        $sql = "UPDATE koperasi_jenis SET is_active = 0, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Jenis koperasi berhasil dihapus'
            ];
        } else {
            return $this->errorResponse('Gagal menghapus jenis koperasi: ' . $stmt->error, 500);
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
$api = new MultiJenisKoperasiAPI();

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['REQUEST_URI'];
$endpoint = str_replace('/api/multi-jenis/', '', $endpoint);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Route and return response
$response = $api->route($method, $endpoint, $input);
echo json_encode($response, JSON_PRETTY_PRINT);
?>
