<?php
/**
 * People API Endpoints
 * KSP Samosir - People Database API
 * Microservice for people data management
 */

require_once __DIR__ . '/../app/services/EnhancedPeopleService.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request details
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? '';
$startTime = microtime(true);

// Log API access
$userId = $_SESSION['user_id'] ?? null;
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

try {
    switch ($endpoint) {
        case 'profile':
            handleProfile($method);
            break;
            
        case 'search':
            handleSearch($method);
            break;
            
        case 'statistics':
            handleStatistics($method);
            break;
            
        case 'sync':
            handleSync($method);
            break;
            
        case 'members':
            handleMembers($method);
            break;
            
        default:
            json_response(['success' => false, 'message' => 'Endpoint not found'], 404);
    }
} catch (Exception $e) {
    json_response(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()], 500);
} finally {
    // Log API access
    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    EnhancedPeopleService::logApiAccess(
        "/api/people.php?endpoint=$endpoint",
        $method,
        $userId,
        $ipAddress,
        $_REQUEST,
        http_response_code(),
        $responseTime
    );
}

/**
 * Handle profile endpoints
 */
function handleProfile($method) {
    switch ($method) {
        case 'GET':
            $userId = $_GET['user_id'] ?? null;
            $type = $_GET['type'] ?? 'ksp_anggota_id';
            
            if (!$userId) {
                json_response(['success' => false, 'message' => 'User ID required'], 400);
            }
            
            $result = EnhancedPeopleService::getPeopleProfile($userId, $type);
            json_response($result, $result['success'] ? 200 : 404);
            break;
            
        default:
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

/**
 * Handle search endpoints
 */
function handleSearch($method) {
    switch ($method) {
        case 'GET':
            $query = $_GET['q'] ?? '';
            $limit = intval($_GET['limit'] ?? 20);
            $offset = intval($_GET['offset'] ?? 0);
            
            if (empty($query)) {
                json_response(['success' => false, 'message' => 'Search query required'], 400);
            }
            
            $filters = [];
            if (!empty($_GET['member_type'])) {
                $filters['member_type'] = $_GET['member_type'];
            }
            if (!empty($_GET['ksp_status'])) {
                $filters['ksp_status'] = $_GET['ksp_status'];
            }
            if (!empty($_GET['age_min'])) {
                $filters['age_min'] = intval($_GET['age_min']);
            }
            if (!empty($_GET['age_max'])) {
                $filters['age_max'] = intval($_GET['age_max']);
            }
            
            $result = EnhancedPeopleService::searchPeople($query, $filters, $limit, $offset);
            json_response($result, $result['success'] ? 200 : 400);
            break;
            
        default:
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

/**
 * Handle statistics endpoints
 */
function handleStatistics($method) {
    switch ($method) {
        case 'GET':
            $result = EnhancedPeopleService::getPeopleStatistics();
            json_response($result, $result['success'] ? 200 : 500);
            break;
            
        default:
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

/**
 * Handle sync endpoints
 */
function handleSync($method) {
    switch ($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                json_response(['success' => false, 'message' => 'Invalid JSON input'], 400);
            }
            
            // Validate required fields
            $required = ['id', 'nama_lengkap'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    json_response(['success' => false, 'message' => "Field '$field' is required"], 400);
                }
            }
            
            $result = EnhancedPeopleService::syncAnggotaToPeopleEnhanced($input);
            json_response($result, $result['success'] ? 201 : 400);
            break;
            
        default:
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

/**
 * Handle members endpoints
 */
function handleMembers($method) {
    switch ($method) {
        case 'GET':
            $limit = intval($_GET['limit'] ?? 20);
            $offset = intval($_GET['offset'] ?? 0);
            $memberType = $_GET['member_type'] ?? null;
            $status = $_GET['status'] ?? null;
            
            // Build query
            $whereConditions = ["ksp_anggota_id IS NOT NULL"];
            $params = [];
            $types = "";
            
            if ($memberType) {
                $whereConditions[] = "ksp_member_type = ?";
                $params[] = $memberType;
                $types .= "s";
            }
            
            if ($status) {
                $whereConditions[] = "ksp_status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            $whereClause = "WHERE " . implode(" AND ", $whereConditions);
            
            try {
                require_once __DIR__ . '/../../config/database_multi.php';
                
                $sql = "
                    SELECT * FROM v_ksp_members 
                    $whereClause
                    ORDER BY ksp_join_date DESC, nama ASC
                    LIMIT ? OFFSET ?
                ";
                
                $params[] = $limit;
                $params[] = $offset;
                $types .= "ii";
                
                $members = peopleDB()->fetchAll($sql, $params, $types);
                
                // Get total count
                $countSql = "SELECT COUNT(*) as total FROM v_ksp_members $whereClause";
                $countParams = array_slice($params, 0, -2);
                $countTypes = substr($types, 0, -2);
                
                $total = (peopleDB()->fetchRow($countSql, $countParams, $countTypes) ?? [])['total'] ?? 0;
                
                json_response([
                    'success' => true,
                    'data' => $members,
                    'pagination' => [
                        'total' => $total,
                        'limit' => $limit,
                        'offset' => $offset,
                        'has_more' => ($offset + $limit) < $total
                    ]
                ], 200);
                
            } catch (Exception $e) {
                json_response(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;
            
        default:
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

/**
 * Send JSON response
 */
function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

?>
