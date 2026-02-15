<?php
/**
 * Multi-Unit Middleware
 * Handles unit switching and access control
 */

class MultiUnitMiddleware
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Check if user has access to current unit
     */
    public function checkUnitAccess($userId, $unitId)
    {
        try {
            // Get user role and assigned unit
            $stmt = $this->db->prepare("
                SELECT u.role_id, u.unit_id, r.name as role_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.id = ? AND u.is_active = 1
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return false;
            }
            
            // Admin and Manager can access all units
            if (in_array($user['role_id'], [1, 2])) {
                return true;
            }
            
            // Other roles can only access assigned unit
            return $user['unit_id'] == $unitId;
        } catch (Exception $e) {
            error_log("Error checking unit access: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get current active unit for user
     */
    public function getCurrentUnit($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ku.*, ki.nama_induk, ki.kode_induk
                FROM users u
                JOIN koperasi_unit ku ON u.unit_id = ku.id
                JOIN koperasi_induk ki ON ku.induk_id = ki.id
                WHERE u.id = ? AND u.is_active = 1
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting current unit: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Set unit context for database queries
     */
    public function setUnitContext($unitId)
    {
        // Set session variable for current unit
        $_SESSION['current_unit_id'] = $unitId;
        
        // Set global variable for database queries
        if (!defined('CURRENT_UNIT_ID')) {
            define('CURRENT_UNIT_ID', $unitId);
        }
    }
    
    /**
     * Filter data by current unit
     */
    public function filterByUnit($query, $userId = null)
    {
        $userId = $userId ?? $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $query;
        }
        
        $user = $this->getUserRole($userId);
        if (!$user) {
            return $query;
        }
        
        // Admin and Manager see all data
        if (in_array($user['role_id'], [1, 2])) {
            return $query;
        }
        
        // Add unit filter to query
        $unitId = $_SESSION['current_unit_id'] ?? $user['unit_id'];
        if ($unitId) {
            // Add WHERE clause for unit filtering
            if (stripos($query, 'WHERE') === false) {
                $query .= " WHERE unit_id = {$unitId}";
            } else {
                $query .= " AND unit_id = {$unitId}";
            }
        }
        
        return $query;
    }
    
    /**
     * Get user role information
     */
    private function getUserRole($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT role_id, unit_id FROM users WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting user role: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Log unit switching
     */
    public function logUnitSwitch($userId, $fromUnitId, $toUnitId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO logs (user_id, action, details, created_at)
                VALUES (?, 'unit_switch', ?, NOW())
            ");
            return $stmt->execute([
                $userId,
                json_encode([
                    'from_unit' => $fromUnitId,
                    'to_unit' => $toUnitId,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ])
            ]);
        } catch (Exception $e) {
            error_log("Error logging unit switch: " . $e->getMessage());
            return false;
        }
    }
}
