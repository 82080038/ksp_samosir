<?php
/**
 * Unit Switching Helper
 * Provides utilities for multi-unit operations
 */

class UnitHelper
{
    private $db;
    private static $currentUnit = null;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get current active unit
     */
    public static function getCurrentUnit()
    {
        if (self::$currentUnit === null) {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                $middleware = new MultiUnitMiddleware();
                self::$currentUnit = $middleware->getCurrentUnit($userId);
            }
        }
        return self::$currentUnit;
    }
    
    /**
     * Get current unit ID
     */
    public static function getCurrentUnitId()
    {
        $unit = self::getCurrentUnit();
        return $unit ? $unit['id'] : null;
    }
    
    /**
     * Get current unit name
     */
    public static function getCurrentUnitName()
    {
        $unit = self::getCurrentUnit();
        return $unit ? $unit['nama_unit'] : 'Unknown Unit';
    }
    
    /**
     * Check if user can access multiple units
     */
    public static function canAccessMultipleUnits($userId = null)
    {
        $userId = $userId ?? $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return false;
        }
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT role_id FROM users WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            return $user && in_array($user['role_id'], [1, 2]); // Admin and Manager
        } catch (Exception $e) {
            error_log("Error checking multi-unit access: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get available units for current user
     */
    public static function getAvailableUnits($userId = null)
    {
        $userId = $userId ?? $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return [];
        }
        
        try {
            $controller = new MultiUnitController();
            return $controller->getUserUnits($userId);
        } catch (Exception $e) {
            error_log("Error getting available units: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add unit filter to query
     */
    public static function addUnitFilter($query, $alias = '')
    {
        $unitId = self::getCurrentUnitId();
        if (!$unitId) {
            return $query;
        }
        
        // Check if user can see all units
        if (self::canAccessMultipleUnits()) {
            return $query;
        }
        
        $unitColumn = $alias ? $alias . '.unit_id' : 'unit_id';
        
        // Add unit filter
        if (stripos($query, 'WHERE') === false) {
            $query .= " WHERE {$unitColumn} = {$unitId}";
        } else {
            $query .= " AND {$unitColumn} = {$unitId}";
        }
        
        return $query;
    }
    
    /**
     * Get unit-specific setting
     */
    public static function getUnitSetting($key, $default = null)
    {
        $unitId = self::getCurrentUnitId();
        if (!$unitId) {
            return $default;
        }
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT setting_value FROM settings 
                WHERE setting_key = ? AND unit_id = ?
            ");
            $stmt->execute([$key, $unitId]);
            $result = $stmt->fetch();
            
            return $result ? $result['setting_value'] : $default;
        } catch (Exception $e) {
            error_log("Error getting unit setting: " . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Format unit display name
     */
    public static function formatUnitName($unit)
    {
        if (!$unit) {
            return 'Unknown Unit';
        }
        
        $levelLabels = [
            'pusat' => 'Pusat',
            'cabang' => 'Cabang',
            'unit' => 'Unit',
            'sub_unit' => 'Sub Unit'
        ];
        
        $level = $levelLabels[$unit['level_unit']] ?? $unit['level_unit'];
        return "{$unit['nama_unit']} ({$level})";
    }
    
    /**
     * Get unit statistics for dashboard
     */
    public static function getUnitStats()
    {
        $unitId = self::getCurrentUnitId();
        if (!$unitId) {
            return [];
        }
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT 
                    COUNT(DISTINCT a.id) as total_anggota,
                    COUNT(DISTINCT u.id) as total_users,
                    COALESCE(SUM(p.total_harga), 0) as total_penjualan,
                    COUNT(DISTINCT p.id) as total_transaksi
                FROM koperasi_unit ku
                LEFT JOIN anggota a ON ku.id = a.unit_id
                LEFT JOIN users u ON ku.id = u.unit_id
                LEFT JOIN penjualan p ON ku.id = p.unit_id AND p.status_pembayaran = 'lunas'
                    AND DATE(p.tanggal_penjualan) = CURDATE()
                WHERE ku.id = ?
            ");
            $stmt->execute([$unitId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting unit stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reset current unit cache
     */
    public static function resetCurrentUnit()
    {
        self::$currentUnit = null;
    }
    
    /**
     * Validate unit access for API endpoints
     */
    public static function validateUnitAccess($unitId, $userId = null)
    {
        $userId = $userId ?? $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return false;
        }
        
        $middleware = new MultiUnitMiddleware();
        return $middleware->checkUnitAccess($userId, $unitId);
    }
    
    /**
     * Get unit breadcrumb
     */
    public static function getUnitBreadcrumb($unitId = null)
    {
        $unitId = $unitId ?? self::getCurrentUnitId();
        if (!$unitId) {
            return [];
        }
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT 
                    ki.nama_induk,
                    ki.kode_induk,
                    ku.nama_unit,
                    ku.kode_unit,
                    ku.level_unit
                FROM koperasi_unit ku
                JOIN koperasi_induk ki ON ku.induk_id = ki.id
                WHERE ku.id = ?
            ");
            $stmt->execute([$unitId]);
            $unit = $stmt->fetch();
            
            if (!$unit) {
                return [];
            }
            
            return [
                'induk' => $unit['nama_induk'],
                'unit' => $unit['nama_unit'],
                'kode_induk' => $unit['kode_induk'],
                'kode_unit' => $unit['kode_unit'],
                'level' => $unit['level_unit']
            ];
        } catch (Exception $e) {
            error_log("Error getting unit breadcrumb: " . $e->getMessage());
            return [];
        }
    }
}
