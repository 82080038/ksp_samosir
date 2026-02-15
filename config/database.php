<?php
/**
 * Database Configuration
 * KSP Samosir - Aplikasi Koperasi Cepat
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'ksp_samosir');
define('DB_CHARSET', 'utf8mb4');

// Include safe database helpers
require_once __DIR__ . '/database_safe.php';

// Create database connection
function getLegacyConnection() {
    static $conn = null;
    
    // Reuse existing connection if available
    if ($conn instanceof mysqli && $conn->ping()) {
        return $conn;
    }
    
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            throw new Exception("Database connection error. Please check configuration.");
        }
        
        $conn->set_charset(DB_CHARSET);
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw new Exception("Unable to connect to database. Please try again later.");
    }
}

// Replace old functions with safe versions
function fetchRow($sql, $params = [], $types = "") {
    return safeFetchRow($sql, $params, $types);
}

function fetchAll($sql, $params = [], $types = "") {
    return safeFetchAll($sql, $params, $types);
}

function executeNonQuery($sql, $params = [], $types = "") {
    return safeExecuteNonQuery($sql, $params, $types);
}

function runInTransaction($callback) {
    $conn = getConnection();
    $conn->begin_transaction();
    try {
        $result = $callback($conn);
        $conn->commit();
        return $result;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}
?>
