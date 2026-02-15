<?php
/**
 * Admin API Entry Point
 * Handles all admin API requests
 */

// Include required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/api/AdminAPIRouter.php';
require_once __DIR__ . '/../../app/controllers/AdminDashboardController.php';
require_once __DIR__ . '/../../app/controllers/SuperAdminDashboardController.php';
require_once __DIR__ . '/../../app/controllers/MultiUnitController.php';
require_once __DIR__ . '/../../app/controllers/KoperasiController.php';
require_once __DIR__ . '/../../app/services/AIService.php';
require_once __DIR__ . '/../../app/services/AuthService.php';
require_once __DIR__ . '/../../app/controllers/RealtimeDashboardController.php';
require_once __DIR__ . '/../../app/controllers/PredictiveAnalyticsController.php';
require_once __DIR__ . '/../../app/controllers/CustomReportController.php';
require_once __DIR__ . '/../../app/controllers/DataVisualizationController.php';
require_once __DIR__ . '/../../app/controllers/EnhancedSalesController.php';
require_once __DIR__ . '/../../app/controllers/CommissionController.php';
require_once __DIR__ . '/../../app/controllers/MarketingAnalyticsController.php';
require_once __DIR__ . '/../../app/controllers/KnowledgeBaseController.php';

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

// Get request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$endpoint = str_replace('/api/admin/', '', $requestUri);
$endpoint = explode('?', $endpoint)[0]; // Remove query string

// Parse JSON input for POST/PUT requests
$inputData = null;
if (in_array($method, ['POST', 'PUT'])) {
    $inputData = json_decode(file_get_contents('php://input'), true);
}

// Initialize API Router
$router = new AdminAPIRouter();

// Route the request
$response = $router->route($method, $endpoint, $inputData);

// Send response
echo json_encode($response);
