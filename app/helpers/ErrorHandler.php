<?php
/**
 * Enhanced Error Handling System
 * Comprehensive error logging and user-friendly messages
 */

class ErrorHandler {
    private static $errors = [];
    private static $logFile = null;
    
    public static function init() {
        self::$logFile = __DIR__ . '/../../logs/errors_' . date('Y-m-d') . '.log';
        
        // Set custom error handler
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    /**
     * Handle PHP errors
     */
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $error = [
            'type' => 'Error',
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'severity' => self::getSeverityName($severity),
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => self::getCurrentUserId(),
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        self::logError($error);
        self::showUserFriendlyError($error);
        
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public static function handleException($exception) {
        $error = [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => self::getCurrentUserId(),
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        self::logError($error);
        self::showUserFriendlyError($error);
    }
    
    /**
     * Handle fatal errors
     */
    public static function handleShutdown() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
    
    /**
     * Log error to file
     */
    private static function logError($error) {
        $logEntry = json_encode($error) . "\n";
        
        // Create log directory if not exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also store in memory for current request
        self::$errors[] = $error;
    }
    
    /**
     * Show user-friendly error message
     */
    private static function showUserFriendlyError($error) {
        // Don't show errors in production environment
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            self::showGenericError();
            return;
        }
        
        // In development, show detailed error
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => true,
                'message' => self::getUserFriendlyMessage($error),
                'debug' => $error
            ]);
        } else {
            echo '<div class="alert alert-danger m-3">';
            echo '<h5><i class="bi bi-exclamation-triangle"></i> Terjadi Kesalahan</h5>';
            echo '<p><strong>Pesan:</strong> ' . htmlspecialchars(self::getUserFriendlyMessage($error)) . '</p>';
            
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                echo '<details>';
                echo '<summary>Detail Error (Development Mode)</summary>';
                echo '<pre>' . htmlspecialchars(print_r($error, true)) . '</pre>';
                echo '</details>';
            }
            
            echo '<hr>';
            echo '<small class="text-muted">Error ID: ' . uniqid() . ' | ' . $error['timestamp'] . '</small>';
            echo '</div>';
        }
    }
    
    /**
     * Show generic error for production
     */
    private static function showGenericError() {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => true,
                'message' => 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti.'
            ]);
        } else {
            echo '<div class="alert alert-warning m-3">';
            echo '<h5><i class="bi bi-exclamation-triangle"></i> Sistem Sedang Dalam Perbaikan</h5>';
            echo '<p>Terjadi kesalahan pada sistem. Tim kami telah diberitahu dan sedang memperbaikinya.</p>';
            echo '<p>Silakan <a href="javascript:history.back()">kembali</a> atau coba lagi beberapa saat.</p>';
            echo '</div>';
        }
    }
    
    /**
     * Get user-friendly error message
     */
    private static function getUserFriendlyMessage($error) {
        $message = strtolower($error['message']);
        
        // Common database errors
        if (strpos($message, 'sql') !== false || strpos($message, 'mysql') !== false) {
            return 'Terjadi kesalahan pada database. Silakan coba lagi.';
        }
        
        // File upload errors
        if (strpos($message, 'upload') !== false || strpos($message, 'file') !== false) {
            return 'Terjadi kesalahan saat mengunggah file. Periksa ukuran dan tipe file.';
        }
        
        // Permission errors
        if (strpos($message, 'permission') !== false || strpos($message, 'access') !== false) {
            return 'Anda tidak memiliki izin untuk melakukan aksi ini.';
        }
        
        // Network errors
        if (strpos($message, 'connection') !== false || strpos($message, 'timeout') !== false) {
            return 'Terjadi masalah koneksi. Silakan periksa internet Anda.';
        }
        
        // Default to original message if no specific mapping
        return $error['message'];
    }
    
    /**
     * Get severity name
     */
    private static function getSeverityName($severity) {
        switch ($severity) {
            case E_ERROR: return 'E_ERROR';
            case E_WARNING: return 'E_WARNING';
            case E_PARSE: return 'E_PARSE';
            case E_NOTICE: return 'E_NOTICE';
            case E_CORE_ERROR: return 'E_CORE_ERROR';
            case E_CORE_WARNING: return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: return 'E_COMPILE_WARNING';
            case E_USER_ERROR: return 'E_USER_ERROR';
            case E_USER_WARNING: return 'E_USER_WARNING';
            case E_USER_NOTICE: return 'E_USER_NOTICE';
            default: return 'UNKNOWN';
        }
    }
    
    /**
     * Get current user ID
     */
    private static function getCurrentUserId() {
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }
        return null;
    }
    
    /**
     * Get recent errors for admin
     */
    public static function getRecentErrors($limit = 50) {
        if (!file_exists(self::$logFile)) {
            return [];
        }
        
        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $errors = [];
        
        // Get last $limit lines
        $lines = array_slice($lines, -$limit);
        
        foreach ($lines as $line) {
            $error = json_decode($line, true);
            if ($error) {
                $errors[] = $error;
            }
        }
        
        return array_reverse($errors);
    }
    
    /**
     * Log custom error
     */
    public static function logCustomError($message, $context = []) {
        $error = [
            'type' => 'Custom',
            'message' => $message,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => self::getCurrentUserId(),
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        self::logError($error);
    }
}

// Initialize error handler
ErrorHandler::init();

?>
