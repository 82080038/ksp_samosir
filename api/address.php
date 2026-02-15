<?php
/**
 * Address API - Integration dengan alamat_db
 * Provides BPS-validated Indonesian address data
 */

require_once __DIR__ . '/../app/services/AddressService.php';

class AddressAPIController {
    
    /**
     * Get all provinces
     */
    public function getProvinces() {
        try {
            $provinces = AddressService::getProvinces();
            
            return [
                'success' => true,
                'data' => $provinces,
                'count' => count($provinces)
            ];
        } catch (Exception $e) {
            error_log("Error getting provinces: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load provinces'
            ];
        }
    }
    
    /**
     * Get regencies by province
     */
    public function getRegencies($provinceId) {
        try {
            if (!$provinceId) {
                return [
                    'success' => false,
                    'message' => 'Province ID is required'
                ];
            }
            
            $regencies = AddressService::getRegencies($provinceId);
            
            return [
                'success' => true,
                'data' => $regencies,
                'count' => count($regencies),
                'province_id' => $provinceId
            ];
        } catch (Exception $e) {
            error_log("Error getting regencies: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load regencies'
            ];
        }
    }
    
    /**
     * Get districts by regency
     */
    public function getDistricts($regencyId) {
        try {
            if (!$regencyId) {
                return [
                    'success' => false,
                    'message' => 'Regency ID is required'
                ];
            }
            
            $districts = AddressService::getDistricts($regencyId);
            
            return [
                'success' => true,
                'data' => $districts,
                'count' => count($districts),
                'regency_id' => $regencyId
            ];
        } catch (Exception $e) {
            error_log("Error getting districts: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load districts'
            ];
        }
    }
    
    /**
     * Get villages by district
     */
    public function getVillages($districtId) {
        try {
            if (!$districtId) {
                return [
                    'success' => false,
                    'message' => 'District ID is required'
                ];
            }
            
            $villages = AddressService::getVillages($districtId);
            
            return [
                'success' => true,
                'data' => $villages,
                'count' => count($villages),
                'district_id' => $districtId
            ];
        } catch (Exception $e) {
            error_log("Error getting villages: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load villages'
            ];
        }
    }
    
    /**
     * Get full address details
     */
    public function getFullAddress() {
        try {
            $provinceId = $_GET['province_id'] ?? null;
            $regencyId = $_GET['regency_id'] ?? null;
            $districtId = $_GET['district_id'] ?? null;
            $villageId = $_GET['village_id'] ?? null;
            
            if (!$provinceId || !$regencyId) {
                return [
                    'success' => false,
                    'message' => 'Province ID and Regency ID are required'
                ];
            }
            
            $address = AddressService::getFullAddress($provinceId, $regencyId, $districtId, $villageId);
            
            if (!$address) {
                return [
                    'success' => false,
                    'message' => 'Address not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $address
            ];
        } catch (Exception $e) {
            error_log("Error getting full address: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load address details'
            ];
        }
    }
    
    /**
     * Search addresses (autocomplete)
     */
    public function searchAddress() {
        try {
            $query = $_GET['q'] ?? '';
            $limit = intval($_GET['limit'] ?? 10);
            
            if (empty($query)) {
                return [
                    'success' => false,
                    'message' => 'Search query is required'
                ];
            }
            
            if (strlen($query) < 2) {
                return [
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters'
                ];
            }
            
            $results = AddressService::searchAddress($query, $limit);
            
            return [
                'success' => true,
                'data' => $results,
                'count' => count($results),
                'query' => $query
            ];
        } catch (Exception $e) {
            error_log("Error searching address: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to search addresses'
            ];
        }
    }
    
    /**
     * Get postal code
     */
    public function getPostalCode() {
        try {
            $provinceId = $_GET['province_id'] ?? null;
            $regencyId = $_GET['regency_id'] ?? null;
            $districtId = $_GET['district_id'] ?? null;
            $villageId = $_GET['village_id'] ?? null;
            
            if (!$provinceId || !$regencyId) {
                return [
                    'success' => false,
                    'message' => 'Province ID and Regency ID are required'
                ];
            }
            
            $postalCode = AddressService::getPostalCode($provinceId, $regencyId, $districtId, $villageId);
            
            return [
                'success' => true,
                'data' => [
                    'postal_code' => $postalCode
                ]
            ];
        } catch (Exception $e) {
            error_log("Error getting postal code: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get postal code'
            ];
        }
    }
    
    /**
     * Validate address
     */
    public function validateAddress() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                return [
                    'success' => false,
                    'message' => 'Invalid JSON input'
                ];
            }
            
            $validation = AddressService::validateAddressCompleteness($input);
            
            return [
                'success' => true,
                'data' => $validation
            ];
        } catch (Exception $e) {
            error_log("Error validating address: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to validate address'
            ];
        }
    }
    
    /**
     * Get address statistics
     */
    public function getStatistics() {
        try {
            $stats = AddressService::getAddressStatistics();
            
            if (!$stats) {
                return [
                    'success' => false,
                    'message' => 'Failed to load statistics'
                ];
            }
            
            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (Exception $e) {
            error_log("Error getting address statistics: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load statistics'
            ];
        }
    }
    
    /**
     * Sync member address
     */
    public function syncMemberAddress($memberId) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                return [
                    'success' => false,
                    'message' => 'Invalid JSON input'
                ];
            }
            
            $result = AddressService::syncMemberAddress($memberId, $input);
            
            return $result;
        } catch (Exception $e) {
            error_log("Error syncing member address: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to sync address'
            ];
        }
    }
}

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['REQUEST_URI'] ?? '';

// Extract endpoint from URL
$endpoint = preg_replace('/^.*\/api\/address\//', '', $endpoint);
$endpoint = explode('?', $endpoint)[0]; // Remove query string

// Fallback to POST parameter if URL extraction fails
if (empty($endpoint) && $method === 'POST') {
    $endpoint = $_POST['endpoint'] ?? '';
}

// Fallback to GET parameter if URL extraction fails
if (empty($endpoint) && $method === 'GET') {
    $endpoint = $_GET['endpoint'] ?? '';
}

$controller = new AddressAPIController();

// Route requests
switch ($endpoint) {
    case 'provinces':
        if ($method === 'GET') {
            $response = $controller->getProvinces();
        }
        break;
        
    case 'regencies':
        if ($method === 'GET') {
            $provinceId = $_GET['province_id'] ?? null;
            $response = $controller->getRegencies($provinceId);
        }
        break;
        
    case 'districts':
        if ($method === 'GET') {
            $regencyId = $_GET['regency_id'] ?? null;
            $response = $controller->getDistricts($regencyId);
        }
        break;
        
    case 'villages':
        if ($method === 'GET') {
            $districtId = $_GET['district_id'] ?? null;
            $response = $controller->getVillages($districtId);
        }
        break;
        
    case 'full':
        if ($method === 'GET') {
            $response = $controller->getFullAddress();
        }
        break;
        
    case 'search':
        if ($method === 'GET') {
            $response = $controller->searchAddress();
        }
        break;
        
    case 'postal-code':
        if ($method === 'GET') {
            $response = $controller->getPostalCode();
        }
        break;
        
    case 'validate':
        if ($method === 'POST') {
            $response = $controller->validateAddress();
        }
        break;
        
    case 'statistics':
        if ($method === 'GET') {
            $response = $controller->getStatistics();
        }
        break;
        
    default:
        // Handle member-specific endpoints
        if (preg_match('/^members\/(\d+)\/address$/', $endpoint, $matches)) {
            $memberId = $matches[1];
            if ($method === 'PUT') {
                $response = $controller->syncMemberAddress($memberId);
            }
        } else {
            $response = [
                'success' => false,
                'error' => 'Endpoint not found',
                'message' => 'Invalid endpoint: ' . $endpoint
            ];
        }
        break;
}

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
