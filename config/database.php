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

// Create database connection
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset(DB_CHARSET);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        return $conn;
    } catch (Exception $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

// Helper function for prepared statements
function executeQuery($sql, $params = [], $types = "") {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $stmt->close();
    $conn->close();
    
    return $result;
}

// Helper function for single row
function fetchRow($sql, $params = [], $types = "") {
    $result = executeQuery($sql, $params, $types);
    return $result->fetch_assoc();
}

// Helper function for multiple rows
function fetchAll($sql, $params = [], $types = "") {
    $result = executeQuery($sql, $params, $types);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Helper function for insert/update/delete
function executeNonQuery($sql, $params = [], $types = "") {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $last_id = $stmt->insert_id;
    
    $stmt->close();
    $conn->close();
    
    return ['affected' => $affected, 'last_id' => $last_id];
}

// Transaction helpers
function beginTransaction() {
    $conn = getConnection();
    $conn->begin_transaction();
    return $conn;
}

function commitTransaction($conn) {
    if ($conn instanceof mysqli) {
        $conn->commit();
        $conn->close();
    }
}

function rollbackTransaction($conn) {
    if ($conn instanceof mysqli) {
        $conn->rollback();
        $conn->close();
    }
}

// Execute callable within a transaction
function runInTransaction(callable $callback) {
    $conn = beginTransaction();
    try {
        $callback($conn);
        commitTransaction($conn);
        return true;
    } catch (Exception $e) {
        rollbackTransaction($conn);
        throw $e;
    }
}
?>
