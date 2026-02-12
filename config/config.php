<?php
/**
 * Application Configuration
 * KSP Samosir - Aplikasi Koperasi Cepat
 */

// Application Settings
define('APP_NAME', 'KSP Samosir');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/ksp_samosir');
define('APP_PATH', __DIR__ . '/..');
define('ENCRYPTION_KEY', 'your_secret_encryption_key_here_change_in_production');
define('MAX_ITEMS_PER_PAGE', 100);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', APP_PATH . '/logs/error.log');

// Session Configuration (SIMPLE for development)
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'ksp_samosir_dev');
}
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 86400); // 24 hours for development
}

// Require local shared utilities
require_once APP_PATH . '/shared/php/formatters.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/role_management.php';
require_once __DIR__ . '/koperasi_accounting.php';
require_once __DIR__ . '/alamat_management.php';
require_once __DIR__ . '/koperasi_activities.php';
require_once __DIR__ . '/koperasi_compliance.php';
require_once __DIR__ . '/responsive_system.php';

// Alias for consistency
function formatCurrency($amount) {
    return format_rupiah($amount, 2);
}

// Date/Time Settings
define('TIMEZONE', 'Asia/Jakarta');
date_default_timezone_set(TIMEZONE);

// Currency Settings
define('CURRENCY', 'IDR');
define('DECIMAL_PLACES', 2);

// Security Settings
define('HASH_ALGO', PASSWORD_DEFAULT);
define('TOKEN_EXPIRY', 3600); // 1 hour

// File Upload Settings
define('UPLOAD_PATH', APP_PATH . '/public/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Pagination Settings
define('ITEMS_PER_PAGE', 10);

// Helper Functions
function base_url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

function redirect($url) {
    header("Location: " . base_url($url));
    exit;
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function verifyToken($token, $session_token) {
    return hash_equals($session_token, $token);
}

function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    if (!$user_id) {
        return null;
    }
    
    // Get user with role information
    $sql = "SELECT u.id, u.username, u.email, u.full_name, u.is_active, u.last_login,
                   r.name as role, r.description as role_description
            FROM users u 
            LEFT JOIN user_roles ur ON u.id = ur.user_id 
            LEFT JOIN roles r ON ur.role_id = r.id 
            WHERE u.id = ?";
    
    $user = fetchRow($sql, [$user_id], 'i');
    
    if ($user) {
        $_SESSION['user'] = $user;
        return $user;
    }
    
    return $_SESSION['user'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login');
    }
}

function hasRole($roles) {
    $user = getCurrentUser();
    if (!$user) return false;
    $roles = (array)$roles;
    return in_array($user['role'] ?? null, $roles, true);
}

function flashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlashMessage($type) {
    $message = $_SESSION['flash'][$type] ?? '';
    unset($_SESSION['flash'][$type]);
    return $message;
}

function logActivity($action, $table, $record_id = null, $old_data = null, $new_data = null) {
    executeNonQuery(
        "INSERT INTO logs (user_id, action, table_name, record_id, old_data, new_data, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $_SESSION['user']['id'] ?? null,
            $action,
            $table,
            $record_id,
            $old_data,
            $new_data,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ],
        'isssssss'
    );
}

function rateLimit($key, $limit, $windowSeconds) {
    $now = time();
    $attempts = $_SESSION['rate_limit'][$key] ?? [];
    $attempts = array_filter($attempts, function($t) use ($now, $windowSeconds) {
        return $t > $now - $windowSeconds;
    });
    if (count($attempts) >= $limit) {
        return false; // blocked
    }
    $attempts[] = $now;
    $_SESSION['rate_limit'][$key] = $attempts;
    return true;
}

function encryptData($data) {
    $key = ENCRYPTION_KEY;
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decryptData($encryptedData) {
    $key = ENCRYPTION_KEY;
    $data = base64_decode($encryptedData);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
}

function getCache($key) {
    $cacheFile = APP_PATH . '/cache/' . md5($key) . '.cache';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 3600)) { // 1 hour
        return unserialize(file_get_contents($cacheFile));
    }
    return null;
}

function setCache($key, $data) {
    $cacheDir = APP_PATH . '/cache';
    if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
    $cacheFile = $cacheDir . '/' . md5($key) . '.cache';
    file_put_contents($cacheFile, serialize($data));
}

function sendEmail($to, $subject, $body) {
    // Stub: Implement with PHPMailer later
    // require 'vendor/autoload.php'; $mail = new PHPMailer(); etc.
    error_log("Email stub: To: $to, Subject: $subject, Body: $body");
    return true; // Assume success for now
}

function renderTable($headers, $rows, $classes = 'table table-striped') {
    echo '<table class="' . htmlspecialchars($classes) . '">';
    echo '<thead><tr>';
    foreach ($headers as $h) echo '<th>' . htmlspecialchars($h) . '</th>';
    echo '</tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($row as $cell) echo '<td>' . htmlspecialchars($cell) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

// Auto-load helpers
spl_autoload_register(function ($class) {
    $file = APP_PATH . '/app/helpers/' . strtolower($class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}
?>
