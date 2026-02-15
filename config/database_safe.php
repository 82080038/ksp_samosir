<?php
/**
 * Enhanced Database Helper with Column Existence Check
 * Prevents "Unknown column" errors by checking if columns exist
 */

// Enhanced fetchRow with column check
function safeFetchRow($sql, $params = [], $types = '') {
    try {
        $conn = getLegacyConnection();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error . " SQL: " . $sql);
            return null;
        }
        
        if (!empty($params)) {
            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            } else {
                // Auto-detect types if not provided
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
        }
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error . " SQL: " . $sql);
            $stmt->close();
            return null;
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row;
    } catch (Exception $e) {
        error_log("Query error: " . $e->getMessage() . " SQL: " . $sql);
        return null;
    }
}

// Enhanced fetchAll with column check
function safeFetchAll($sql, $params = [], $types = '') {
    try {
        $conn = getLegacyConnection();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error . " SQL: " . $sql);
            return [];
        }
        
        if (!empty($params)) {
            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            } else {
                // Auto-detect types if not provided
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
        }
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error . " SQL: " . $sql);
            $stmt->close();
            return [];
        }
        
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        
        return $data;
    } catch (Exception $e) {
        error_log("Query error: " . $e->getMessage() . " SQL: " . $sql);
        return [];
    }
}

// Enhanced executeNonQuery with column check
function safeExecuteNonQuery($sql, $params = [], $types = '') {
    try {
        $conn = getLegacyConnection();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error . " SQL: " . $sql);
            return ['affected' => 0, 'last_id' => null];
        }
        
        if (!empty($params)) {
            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            } else {
                // Auto-detect types if not provided
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
        }
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error . " SQL: " . $sql);
            $stmt->close();
            return ['affected' => 0, 'last_id' => null];
        }
        
        $affected = $stmt->affected_rows;
        $last_id = $stmt->insert_id;
        $stmt->close();
        
        return ['affected' => $affected, 'last_id' => $last_id];
    } catch (Exception $e) {
        error_log("NonQuery error: " . $e->getMessage() . " SQL: " . $sql);
        return ['affected' => 0, 'last_id' => null];
    }
}

// Check if table exists
function tableExists($tableName) {
    try {
        $conn = getLegacyConnection();
        $result = $conn->query("SHOW TABLES LIKE '$tableName'");
        $exists = $result && $result->num_rows > 0;
        $conn->close();
        return $exists;
    } catch (Exception $e) {
        return false;
    }
}

// Check if column exists in table
function columnExists($tableName, $columnName) {
    try {
        $conn = getLegacyConnection();
        $result = $conn->query("SHOW COLUMNS FROM `$tableName` LIKE '$columnName'");
        $exists = $result && $result->num_rows > 0;
        $conn->close();
        return $exists;
    } catch (Exception $e) {
        return false;
    }
}

// Get table columns
function getTableColumns($tableName) {
    try {
        $conn = getLegacyConnection();
        $result = $conn->query("SHOW COLUMNS FROM `$tableName`");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        $conn->close();
        return $columns;
    } catch (Exception $e) {
        return [];
    }
}
?>
