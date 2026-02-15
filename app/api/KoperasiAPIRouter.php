<?php
/**
 * Koperasi API Router
 * Handles all API requests for koperasi management
 */

class KoperasiAPIRouter
{
    private $controllers = [];
    
    public function __construct()
    {
        $this->initializeControllers();
    }
    
    /**
     * Initialize controllers
     */
    private function initializeControllers()
    {
        $this->controllers = [
            'koperasi' => new KoperasiController(),
            'auth' => new AuthService(),
            'multi_unit' => new MultiUnitController(),
            'ai' => new AIService()
        ];
    }
    
    /**
     * Route API request
     */
    public function route($endpoint, $method, $data = [])
    {
        try {
            // Authentication check
            if (!$this->isAuthenticated()) {
                return $this->errorResponse('Authentication required', 401);
            }
            
            // Permission check
            if (!$this->hasPermission($endpoint, $method)) {
                return $this->errorResponse('Permission denied', 403);
            }
            
            // Parse endpoint
            $parts = explode('/', trim($endpoint, '/'));
            $resource = $parts[0] ?? '';
            $action = $parts[1] ?? '';
            $id = $parts[2] ?? null;
            
            switch ($resource) {
                case 'dashboard':
                    return $this->handleDashboard($action, $method, $data);
                    
                case 'anggota':
                    return $this->handleAnggota($action, $id, $method, $data);
                    
                case 'simpanan':
                    return $this->handleSimpanan($action, $id, $method, $data);
                    
                case 'pinjaman':
                    return $this->handlePinjaman($action, $id, $method, $data);
                    
                case 'angsuran':
                    return $this->handleAngsuran($action, $id, $method, $data);
                    
                case 'units':
                    return $this->handleUnits($method, $data);
                    
                case 'jenis-simpanan':
                    return $this->handleJenisSimpanan($method, $data);
                    
                case 'jenis-pinjaman':
                    return $this->handleJenisPinjaman($method, $data);
                    
                case 'reports':
                    return $this->handleReports($action, $method, $data);
                    
                case 'settings':
                    return $this->handleSettings($method, $data);
                    
                default:
                    return $this->errorResponse('Endpoint not found', 404);
            }
        } catch (Exception $e) {
            error_log("API Error: " . $e->getMessage());
            return $this->errorResponse('Internal server error', 500);
        }
    }
    
    /**
     * Handle dashboard endpoints
     */
    private function handleDashboard($action, $method, $data)
    {
        switch ($action) {
            case 'overview':
                if ($method === 'GET') {
                    return $this->controllers['koperasi']->getDashboardOverview();
                }
                break;
                
            case 'stats':
                if ($method === 'GET') {
                    return $this->controllers['koperasi']->getDashboardStats($data);
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle anggota endpoints
     */
    private function handleAnggota($action, $id, $method, $data)
    {
        switch ($method) {
            case 'GET':
                if ($id) {
                    return $this->controllers['koperasi']->getAnggotaById($id);
                } else {
                    return $this->controllers['koperasi']->getAnggota($data);
                }
                
            case 'POST':
                if ($action === 'create') {
                    return $this->controllers['koperasi']->createAnggota($data);
                }
                break;
                
            case 'PUT':
                if ($id) {
                    return $this->controllers['koperasi']->updateAnggota($id, $data);
                }
                break;
                
            case 'DELETE':
                if ($id) {
                    return $this->controllers['koperasi']->deleteAnggota($id);
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle simpanan endpoints
     */
    private function handleSimpanan($action, $id, $method, $data)
    {
        switch ($method) {
            case 'GET':
                if ($id) {
                    return $this->controllers['koperasi']->getSimpananById($id);
                } else {
                    return $this->controllers['koperasi']->getSimpanan($data);
                }
                
            case 'POST':
                if ($action === 'create') {
                    return $this->controllers['koperasi']->createSimpanan($data);
                }
                break;
                
            case 'PUT':
                if ($id) {
                    return $this->controllers['koperasi']->updateSimpanan($id, $data);
                }
                break;
                
            case 'DELETE':
                if ($id) {
                    return $this->controllers['koperasi']->deleteSimpanan($id);
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle pinjaman endpoints
     */
    private function handlePinjaman($action, $id, $method, $data)
    {
        switch ($method) {
            case 'GET':
                if ($id) {
                    if ($action === 'approve') {
                        return $this->controllers['koperasi']->approvePinjaman($id, $data);
                    } else {
                        return $this->controllers['koperasi']->getPinjamanById($id);
                    }
                } elseif ($action === 'jatuh-tempo') {
                    return $this->controllers['koperasi']->getPinjamanJatuhTempo($data);
                } else {
                    return $this->controllers['koperasi']->getPinjaman($data);
                }
                
            case 'POST':
                if ($action === 'create') {
                    return $this->controllers['koperasi']->createPinjaman($data);
                }
                break;
                
            case 'PUT':
                if ($id) {
                    return $this->controllers['koperasi']->updatePinjaman($id, $data);
                }
                break;
                
            case 'DELETE':
                if ($id) {
                    return $this->controllers['koperasi']->deletePinjaman($id);
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle angsuran endpoints
     */
    private function handleAngsuran($action, $id, $method, $data)
    {
        switch ($method) {
            case 'GET':
                if ($id) {
                    return $this->controllers['koperasi']->getAngsuranById($id);
                } else {
                    return $this->controllers['koperasi']->getAngsuran($data);
                }
                
            case 'POST':
                if ($action === 'create') {
                    return $this->controllers['koperasi']->createAngsuran($data);
                }
                break;
                
            case 'PUT':
                if ($id) {
                    return $this->controllers['koperasi']->updateAngsuran($id, $data);
                }
                break;
                
            case 'DELETE':
                if ($id) {
                    return $this->controllers['koperasi']->deleteAngsuran($id);
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle units endpoints
     */
    private function handleUnits($method, $data)
    {
        switch ($method) {
            case 'GET':
                return $this->controllers['multi_unit']->getAllUnits();
                
            case 'POST':
                return $this->controllers['multi_unit']->createUnit($data);
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle jenis simpanan endpoints
     */
    private function handleJenisSimpanan($method, $data)
    {
        switch ($method) {
            case 'GET':
                return $this->controllers['koperasi']->getJenisSimpanan();
                
            case 'POST':
                return $this->controllers['koperasi']->createJenisSimpanan($data);
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle jenis pinjaman endpoints
     */
    private function handleJenisPinjaman($method, $data)
    {
        switch ($method) {
            case 'GET':
                return $this->controllers['koperasi']->getJenisPinjaman();
                
            case 'POST':
                return $this->controllers['koperasi']->createJenisPinjaman($data);
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle reports endpoints
     */
    private function handleReports($action, $method, $data)
    {
        switch ($action) {
            case 'simpanan':
                if ($method === 'GET') {
                    return $this->controllers['koperasi']->getLaporanSimpanan($data);
                }
                break;
                
            case 'pinjaman':
                if ($method === 'GET') {
                    return $this->controllers['koperasi']->getLaporanPinjaman($data);
                }
                break;
                
            case 'shu':
                if ($method === 'GET') {
                    return $this->controllers['koperasi']->getLaporanSHU($data);
                }
                break;
                
            case 'neraca':
                if ($method === 'GET') {
                    return $this->controllers['koperasi']->getNeraca($data);
                }
                break;
                
            case 'laba-rugi':
                if ($method === 'GET') {
                    return $this->controllers['koperasi']->getLabaRugi($data);
                }
                break;
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Handle settings endpoints
     */
    private function handleSettings($method, $data)
    {
        switch ($method) {
            case 'GET':
                return $this->controllers['koperasi']->getSettings($data);
                
            case 'POST':
                return $this->controllers['koperasi']->updateSettings($data);
        }
        
        return $this->errorResponse('Method not allowed', 405);
    }
    
    /**
     * Check if user is authenticated
     */
    private function isAuthenticated()
    {
        session_start();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Check if user has permission
     */
    private function hasPermission($endpoint, $method)
    {
        // For now, allow all authenticated users
        // In production, implement proper permission checking
        return true;
    }
    
    /**
     * Success response
     */
    private function successResponse($data = [], $message = 'Success')
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Error response
     */
    private function errorResponse($message, $code = 400)
    {
        http_response_code($code);
        return [
            'success' => false,
            'message' => $message,
            'error_code' => $code,
            'timestamp' => date('c')
        ];
    }
}
