<?php
/**
 * Application Configuration
 * KSP Samosir - Aplikasi Koperasi Cepat
 */

// Application Settings
define('APP_NAME', 'KSP Samosir');
define('APP_VERSION', '1.0.0');

if (!function_exists('detect_app_base_url')) {
    function detect_app_base_url() {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir === '/' || $scriptDir === '.') {
            $scriptDir = '';
        }
        return rtrim($scheme . '://' . $host . $scriptDir, '/');
    }
}

define('APP_URL', detect_app_base_url());
define('APP_PATH', __DIR__ . '/..');
define('ENCRYPTION_KEY', 'ksp_samosir_secure_key_2025_change_in_production');
define('MAX_ITEMS_PER_PAGE', 100);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); // Enable for development
ini_set('log_errors', 1);
ini_set('error_log', APP_PATH . '/logs/error.log');

// Ensure logs directory exists
if (!is_dir(APP_PATH . '/logs')) {
    mkdir(APP_PATH . '/logs', 0755, true);
}

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
require_once __DIR__ . '/error_handler.php';
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

function getActivePage() {
    $request = $_SERVER['REQUEST_URI'] ?? '';
    $basePath = parse_url(APP_URL, PHP_URL_PATH) ?: '';
    if ($basePath !== '' && strpos($request, $basePath) === 0) {
        $request = substr($request, strlen($basePath));
    }
    $request = rtrim($request, '/');
    $segments = explode('/', $request);
    
    // Handle URL patterns with or without leading slash
    $page = '';
    if (empty($segments[0])) {
        $page = $segments[1] ?? '';
    } else {
        $page = $segments[0];
    }
    
    return preg_replace('/[^a-zA-Z0-9_\-]/', '', $page);
}

function isActivePage($pageName) {
    $currentPage = getActivePage();
    if ($currentPage === $pageName) return true;

    // Support multi-segment paths (e.g. koperasi_modul/kpn_lahan)
    if (strpos($pageName, '/') !== false) {
        $request = $_SERVER['REQUEST_URI'] ?? '';
        $basePath = parse_url(APP_URL, PHP_URL_PATH) ?: '';
        if ($basePath !== '' && strpos($request, $basePath) === 0) {
            $request = substr($request, strlen($basePath));
        }
        $fullPath = trim(strtok($request, '?'), '/');
        return $fullPath === $pageName;
    }
    return false;
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
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        // Return default user for development
        return [
            'id' => 1,
            'username' => 'admin',
            'name' => 'Administrator',
            'role' => 'admin'
        ];
    }
    
    // Return user from session
    return $_SESSION['user'];
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login');
    }
}

function hasRole($roles) {
    $user = getCurrentUser();
    if (!$user) return false;
    $userRole = $user['role'] ?? null;

    // During development: super_admin = admin
    if ($userRole === 'super_admin') {
        $userRole = 'admin';
    }

    $roles = (array)$roles;
    return in_array($userRole, $roles, true);
}

function getCurrentUserRole() {
    $user = getCurrentUser();
    return $user['role'] ?? null;
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
    $user_id = null;
    if (isset($_SESSION['user']['id']) && !empty($_SESSION['user']['id'])) {
        $user_id = $_SESSION['user']['id'];
    }
    
    executeNonQuery(
        "INSERT INTO logs (user_id, action, table_name, record_id, old_data, new_data, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $user_id,
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
    // Cache dimatikan untuk pengembangan
    return null;
}

function setCache($key, $data) {
    // Cache dimatikan untuk pengembangan
    return true;
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
