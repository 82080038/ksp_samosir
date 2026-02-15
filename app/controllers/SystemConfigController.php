<?php
/**
 * System Configuration Controller
 * Based on KSP-PEB system-settings.php analysis
 */

class SystemConfigController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Get all system configurations grouped by category
     */
    public function getAllConfigs()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM system_configs 
                ORDER BY config_category, config_key
            ");
            $stmt->execute();
            $configs = $stmt->fetchAll();
            
            // Group by category
            $groupedConfigs = [];
            foreach ($configs as $config) {
                $groupedConfigs[$config['config_category']][] = $config;
            }
            
            return $groupedConfigs;
        } catch (Exception $e) {
            error_log("Error getting configs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get single configuration value
     */
    public function getConfig($key, $default = null)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT config_value FROM system_configs 
                WHERE config_key = ?
            ");
            $stmt->execute([$key]);
            $result = $stmt->fetch();
            
            return $result ? $result['config_value'] : $default;
        } catch (Exception $e) {
            error_log("Error getting config {$key}: " . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Update configuration value
     */
    public function updateConfig($key, $value, $userId = null)
    {
        try {
            // Get old value for logging
            $oldValue = $this->getConfig($key);
            
            // Update configuration
            $stmt = $this->db->prepare("
                UPDATE system_configs 
                SET config_value = ?, updated_by = ?, updated_at = NOW()
                WHERE config_key = ?
            ");
            $result = $stmt->execute([$value, $userId, $key]);
            
            if ($result) {
                // Log the change
                $this->logConfigChange($key, $oldValue, $value, $userId);
                return ['success' => true, 'message' => 'Configuration updated successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to update configuration'];
        } catch (Exception $e) {
            error_log("Error updating config {$key}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Get system information
     */
    public function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->getMySQLVersion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
            'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'php_memory_limit' => ini_get('memory_limit'),
            'php_max_execution_time' => ini_get('max_execution_time'),
            'php_upload_max_filesize' => ini_get('upload_max_filesize'),
            'disk_free_space' => $this->formatBytes(disk_free_space('/')),
            'disk_total_space' => $this->formatBytes(disk_total_space('/')),
            'timezone' => date_default_timezone_get(),
            'current_time' => date('Y-m-d H:i:s'),
            'uptime' => $this->getSystemUptime()
        ];
    }
    
    /**
     * Clear system cache
     */
    public function clearCache()
    {
        try {
            $cacheDir = __DIR__ . '/../storage/cache/';
            $cleared = 0;
            
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                        $cleared++;
                    }
                }
            }
            
            // Clear OPcache if enabled
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
            return ['success' => true, 'message' => "Cleared {$cleared} cache files"];
        } catch (Exception $e) {
            error_log("Error clearing cache: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to clear cache'];
        }
    }
    
    /**
     * Restart all active sessions
     */
    public function restartSessions()
    {
        try {
            // Get current session count
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM sessions WHERE expiry > ?");
            $stmt->execute([time()]);
            $result = $stmt->fetch();
            $count = $result['count'];
            
            // Delete all active sessions except current
            $sessionId = session_id();
            $stmt = $this->db->prepare("DELETE FROM sessions WHERE id != ? AND expiry > ?");
            $stmt->execute([$sessionId, time()]);
            
            return ['success' => true, 'message' => "Restarted {$count} sessions"];
        } catch (Exception $e) {
            error_log("Error restarting sessions: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to restart sessions'];
        }
    }
    
    /**
     * Get recent system logs
     */
    public function getSystemLogs($limit = 100)
    {
        try {
            $logFile = __DIR__ . '/../logs/error.log';
            $logs = [];
            
            if (file_exists($logFile)) {
                $lines = file($logFile);
                $lines = array_slice($lines, -$limit);
                
                foreach ($lines as $line) {
                    $logs[] = trim($line);
                }
            }
            
            return array_reverse($logs);
        } catch (Exception $e) {
            error_log("Error reading logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Log configuration changes
     */
    private function logConfigChange($key, $oldValue, $newValue, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO system_config_changes 
                (config_key, old_value, new_value, changed_by, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $key,
                $oldValue,
                $newValue,
                $userId,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Error logging config change: " . $e->getMessage());
        }
    }
    
    /**
     * Get MySQL version
     */
    private function getMySQLVersion()
    {
        try {
            $stmt = $this->db->query("SELECT VERSION() as version");
            $result = $stmt->fetch();
            return $result['version'] ?? 'Unknown';
        } catch (Exception $e) {
            return 'Unknown';
        }
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Get system uptime (Linux only)
     */
    private function getSystemUptime()
    {
        if (file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            $uptime = explode(' ', $uptime)[0];
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            
            return "{$days}d {$hours}h {$minutes}m";
        }
        
        return 'Unknown';
    }
}
