<?php
/**
 * Koperasi API Entry Point
 * Handles all API requests for koperasi management
 */

// Include required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/KoperasiController.php';
require_once __DIR__ . '/../../app/controllers/MultiUnitController.php';
require_once __DIR__ . '/../../app/services/AuthService.php';
require_once __DIR__ . '/../../app/services/AIService.php';
require_once __DIR__ . '/../../app/api/KoperasiAPIRouter.php';

// Start session
session_start();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['REQUEST_URI'];
$endpoint = str_replace('/api/koperasi', '', $endpoint);
$endpoint = trim($endpoint, '/');

// Get request data
$data = [];
if ($method === 'POST' || $method === 'PUT') {
    $input = file_get_contents('php://input');
    if ($input) {
        $data = json_decode($input, true) ?? [];
    }
} elseif ($method === 'GET') {
    $data = $_GET;
}

// Initialize router and route request
try {
    $router = new KoperasiAPIRouter();
    $response = $router->route($endpoint, $method, $data);
    
    // Send response
    echo json_encode($response);
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error_code' => 500,
        'timestamp' => date('c')
    ]);
}
?>
