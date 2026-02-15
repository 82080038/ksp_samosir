<?php
/**
 * Multi-Database Configuration for KSP Samosir
 * Arsitektur 5 Database untuk optimal performance & scalability
 */

// =====================================================
// DATABASE CONFIGURATIONS
// =====================================================

// Database Configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'root');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// Database 1: Core Koperasi
define('DB_CORE_HOST', 'localhost');
define('DB_CORE_USER', 'root');
define('DB_CORE_PASS', 'root');
define('DB_CORE_NAME', 'ksp_samosir_core');

// Database 2: Registration System
define('DB_REGISTRATION_HOST', 'localhost');
define('DB_REGISTRATION_USER', 'root');
define('DB_REGISTRATION_PASS', 'root');
define('DB_REGISTRATION_NAME', 'ksp_samosir_registration');

// Database 3: Analytics & Reporting
define('DB_ANALYTICS_HOST', 'localhost');
define('DB_ANALYTICS_USER', 'root');
define('DB_ANALYTICS_PASS', 'root');
define('DB_ANALYTICS_NAME', 'ksp_samosir_analytics');

// Database 4: Business Modules
define('DB_BUSINESS_HOST', 'localhost');
define('DB_BUSINESS_USER', 'root');
define('DB_BUSINESS_PASS', 'root');
define('DB_BUSINESS_NAME', 'ksp_samosir_business');

// Database 5: System & Infrastructure
define('DB_SYSTEM_HOST', 'localhost');
define('DB_SYSTEM_USER', 'root');
define('DB_SYSTEM_PASS', 'root');
define('DB_SYSTEM_NAME', 'ksp_samosir_system');

// Database 6: External Address Database (BPS Data)
define('DB_ADDRESS_HOST', 'localhost');
define('DB_ADDRESS_USER', 'root');
define('DB_ADDRESS_PASS', 'root');
define('DB_ADDRESS_NAME', 'alamat_db');

// Database 7: External People Database
define('DB_PEOPLE_HOST', 'localhost');
define('DB_PEOPLE_USER', 'root');
define('DB_PEOPLE_PASS', 'root');
define('DB_PEOPLE_NAME', 'people_db');

// =====================================================
// DATABASE CONNECTION MANAGER
// =====================================================

class DatabaseManager {
    private static $connections = [];
    private static $config = [
        'core' => [
            'host' => DB_CORE_HOST,
            'user' => DB_CORE_USER,
            'pass' => DB_CORE_PASS,
            'name' => DB_CORE_NAME
        ],
        'registration' => [
            'host' => DB_REGISTRATION_HOST,
            'user' => DB_REGISTRATION_USER,
            'pass' => DB_REGISTRATION_PASS,
            'name' => DB_REGISTRATION_NAME
        ],
        'analytics' => [
            'host' => DB_ANALYTICS_HOST,
            'user' => DB_ANALYTICS_USER,
            'pass' => DB_ANALYTICS_PASS,
            'name' => DB_ANALYTICS_NAME
        ],
        'business' => [
            'host' => DB_BUSINESS_HOST,
            'user' => DB_BUSINESS_USER,
            'pass' => DB_BUSINESS_PASS,
            'name' => DB_BUSINESS_NAME
        ],
        'system' => [
            'host' => DB_SYSTEM_HOST,
            'user' => DB_SYSTEM_USER,
            'pass' => DB_SYSTEM_PASS,
            'name' => DB_SYSTEM_NAME
        ],
        'address' => [
            'host' => DB_ADDRESS_HOST,
            'user' => DB_ADDRESS_USER,
            'pass' => DB_ADDRESS_PASS,
            'name' => DB_ADDRESS_NAME
        ],
        'people' => [
            'host' => DB_PEOPLE_HOST,
            'user' => DB_PEOPLE_USER,
            'pass' => DB_PEOPLE_PASS,
            'name' => DB_PEOPLE_NAME
        ]
    ];

    /**
     * Get database connection by type
     * @param string $type core|registration|analytics|business|system
     * @return mysqli
     */
    public static function getConnection($type = 'core') {
        if (!isset(self::$config[$type])) {
            throw new Exception("Invalid database type: $type");
        }

        $key = $type;
        
        // Reuse existing connection if available
        if (isset(self::$connections[$key]) && 
            self::$connections[$key] instanceof mysqli && 
            self::$connections[$key]->ping()) {
            return self::$connections[$key];
        }

        try {
            $config = self::$config[$type];
            $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['name']);
            
            if ($conn->connect_error) {
                error_log("Database connection failed ($type): " . $conn->connect_error);
                throw new Exception("Database connection error for $type. Please check configuration.");
            }
            
            $conn->set_charset(DB_CHARSET);
            $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
            
            self::$connections[$key] = $conn;
            return $conn;
            
        } catch (Exception $e) {
            error_log("Database connection error ($type): " . $e->getMessage());
            throw new Exception("Unable to connect to $type database. Please try again later.");
        }
    }

    /**
     * Close all connections
     */
    public static function closeAll() {
        foreach (self::$connections as $conn) {
            if ($conn instanceof mysqli) {
                $conn->close();
            }
        }
        self::$connections = [];
    }

    /**
     * Test all database connections
     */
    public static function testConnections() {
        $results = [];
        foreach (array_keys(self::$config) as $type) {
            try {
                $conn = self::getConnection($type);
                $results[$type] = [
                    'status' => 'success',
                    'database' => self::$config[$type]['name'],
                    'message' => 'Connection successful'
                ];
            } catch (Exception $e) {
                $results[$type] = [
                    'status' => 'error',
                    'database' => self::$config[$type]['name'],
                    'message' => $e->getMessage()
                ];
            }
        }
        return $results;
    }

    /**
     * Get database info for all connections
     */
    public static function getDatabaseInfo() {
        $info = [];
        foreach (array_keys(self::$config) as $type) {
            try {
                $conn = self::getConnection($type);
                $result = $conn->query("SELECT DATABASE() as db_name, VERSION() as version");
                $data = $result->fetch_assoc();
                
                $tables = $conn->query("SHOW TABLES")->num_rows;
                
                $info[$type] = [
                    'database' => $data['db_name'],
                    'version' => $data['version'],
                    'tables_count' => $tables,
                    'status' => 'connected'
                ];
            } catch (Exception $e) {
                $info[$type] = [
                    'database' => self::$config[$type]['name'],
                    'version' => 'N/A',
                    'tables_count' => 0,
                    'status' => 'error: ' . $e->getMessage()
                ];
            }
        }
        return $info;
    }
}

// =====================================================
// LEGACY COMPATIBILITY FUNCTIONS
// =====================================================

// For backward compatibility with existing code
function getConnection() {
    return DatabaseManager::getConnection('core');
}

// Enhanced functions with database type parameter
function getConnectionByType($type) {
    return DatabaseManager::getConnection($type);
}

// =====================================================
// DATABASE QUERY HELPERS FOR MULTI-DB
// =====================================================

class MultiDBQuery {
    private $dbType;

    public function __construct($dbType = 'core') {
        $this->dbType = $dbType;
    }

    public function fetchRow($sql, $params = [], $types = "") {
        $conn = DatabaseManager::getConnection($this->dbType);
        
        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $conn->error);
            }
            
            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            } else {
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } else {
            $result = $conn->query($sql);
            return $result ? $result->fetch_assoc() : null;
        }
    }

    public function fetchAll($sql, $params = [], $types = "") {
        $conn = DatabaseManager::getConnection($this->dbType);
        
        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $conn->error);
            }
            
            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            } else {
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $result = $conn->query($sql);
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }
    }

    public function executeNonQuery($sql, $params = [], $types = "") {
        $conn = DatabaseManager::getConnection($this->dbType);
        
        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $conn->error);
            }
            
            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            } else {
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            }
            
            $stmt->execute();
            return [
                'affected_rows' => $stmt->affected_rows,
                'last_id' => $stmt->insert_id
            ];
        } else {
            $conn->query($sql);
            return [
                'affected_rows' => $conn->affected_rows,
                'last_id' => $conn->insert_id
            ];
        }
    }
}

// =====================================================
// CONVENIENCE FUNCTIONS
// =====================================================

function coreDB() {
    return new MultiDBQuery('core');
}

function registrationDB() {
    return new MultiDBQuery('registration');
}

function analyticsDB() {
    return new MultiDBQuery('analytics');
}

function businessDB() {
    return new MultiDBQuery('business');
}

function systemDB() {
    return new MultiDBQuery('system');
}

function addressDB() {
    return new MultiDBQuery('address');
}

function peopleDB() {
    return new MultiDBQuery('people');
}

// Auto-close connections on script end
register_shutdown_function(function() {
    DatabaseManager::closeAll();
});

?>
