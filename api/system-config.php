<?php
/**
 * System Configuration API
 * Handles AJAX requests for system configuration
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../controllers/SystemConfigController.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Start session for authentication
session_start();

// Check authentication
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$systemConfig = new SystemConfigController();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $value = $_POST['value'] ?? '';
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid configuration ID']);
                exit;
            }
            
            // Get config key for validation
            $stmt = Database::getInstance()->prepare("SELECT config_key FROM system_configs WHERE id = ?");
            $stmt->execute([$id]);
            $config = $stmt->fetch();
            
            if (!$config) {
                echo json_encode(['success' => false, 'message' => 'Configuration not found']);
                exit;
            }
            
            // Validate value based on config type
            $stmt = Database::getInstance()->prepare("SELECT config_type FROM system_configs WHERE id = ?");
            $stmt->execute([$id]);
            $configType = $stmt->fetch()['config_type'];
            
            if ($configType === 'boolean' && !in_array($value, ['true', 'false'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid boolean value']);
                exit;
            }
            
            if ($configType === 'number' && !is_numeric($value)) {
                echo json_encode(['success' => false, 'message' => 'Invalid number value']);
                exit;
            }
            
            $result = $systemConfig->updateConfig($config['config_key'], $value, $_SESSION['user_id']);
            echo json_encode($result);
            break;
            
        case 'clear_cache':
            $result = $systemConfig->clearCache();
            echo json_encode($result);
            break;
            
        case 'restart_sessions':
            $result = $systemConfig->restartSessions();
            echo json_encode($result);
            break;
            
        case 'get_logs':
            $limit = intval($_POST['limit'] ?? 100);
            $logs = $systemConfig->getSystemLogs($limit);
            echo json_encode(['success' => true, 'logs' => $logs]);
            break;
            
        case 'get_system_info':
            $info = $systemConfig->getSystemInfo();
            echo json_encode(['success' => true, 'info' => $info]);
            break;
            
        case 'get_config':
            $key = $_POST['key'] ?? '';
            if (empty($key)) {
                echo json_encode(['success' => false, 'message' => 'Configuration key required']);
                exit;
            }
            
            $value = $systemConfig->getConfig($key);
            echo json_encode(['success' => true, 'value' => $value]);
            break;
            
        case 'get_all_configs':
            $configs = $systemConfig->getAllConfigs();
            echo json_encode(['success' => true, 'configs' => $configs]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("System Config API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
