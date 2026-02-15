<?php
/**
 * KSP Samosir - Middleware System
 * Provides authentication, logging, CORS, and request handling middleware
 */

// Base Middleware Interface
interface MiddlewareInterface {
    public function handle($request, $next);
}

function middleware_app_url($path = '') {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($scriptDir === '/' || $scriptDir === '.') {
        $scriptDir = '';
    }
    $path = ltrim($path, '/');
    if ($path === '') {
        return $scriptDir === '' ? '/' : $scriptDir . '/';
    }
    return ($scriptDir === '' ? '' : $scriptDir) . '/' . $path;
}

// Request/Response wrapper
class Request {
    private $data;

    public function __construct($data = []) {
        $this->data = $data;
    }

    public function get($key, $default = null) {
        return $this->data[$key] ?? $default;
    }

    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    public function all() {
        return $this->data;
    }

    public function has($key) {
        return isset($this->data[$key]);
    }

    public function method() {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function uri() {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public function header($key, $default = null) {
        $headers = getallheaders();
        return $headers[$key] ?? $default;
    }

    public function ip() {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ??
               $_SERVER['HTTP_X_REAL_IP'] ??
               $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function isAjax() {
        return $this->header('X-Requested-With') === 'XMLHttpRequest' ||
               $this->header('Content-Type') === 'application/json';
    }
}

class Response {
    private $status = 200;
    private $headers = [];
    private $content = '';

    public function status($code) {
        $this->status = $code;
        http_response_code($code);
        return $this;
    }

    public function header($key, $value) {
        $this->headers[$key] = $value;
        return $this;
    }

    public function json($data, $status = 200) {
        $this->status($status);
        $this->header('Content-Type', 'application/json');
        $this->content = json_encode($data);
        return $this;
    }

    public function html($content, $status = 200) {
        $this->status($status);
        $this->header('Content-Type', 'text/html');
        $this->content = $content;
        return $this;
    }

    public function redirect($url, $status = 302) {
        $this->status($status);
        $this->header('Location', $url);
        return $this;
    }

    public function send() {
        // Send headers
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        // Send content
        echo $this->content;
        exit;
    }
}

// Authentication Middleware
class AuthMiddleware implements MiddlewareInterface {
    private $requiredRoles = [];

    public function __construct($roles = []) {
        $this->requiredRoles = (array) $roles;
    }

    public function handle($request, $next) {
        // DISABLED for development
        // Check if user is logged in
        // if (!isset($_SESSION['user'])) {
        //     if ($request->isAjax()) {
        //         $response = new Response();
        //         return $response->json([
        //             'success' => false,
        //             'error' => ['message' => 'Unauthorized']
        //         ], 401);
        //     } else {
        //         header('Location: ' . middleware_app_url('login'));
        //         exit;
        //     }
        // }

        // Check role requirements (DISABLED for development)
        // if (!empty($this->requiredRoles)) {
        //     $userRole = $_SESSION['user']['role'] ?? null;
        //     if (!$userRole || !in_array($userRole, $this->requiredRoles)) {
        //         if ($request->isAjax()) {
        //             $response = new Response();
        //             return $response->json([
        //                 'success' => false,
        //                 'error' => ['message' => 'Forbidden']
        //             ], 403);
        //         } else {
        //             http_response_code(403);
        //             require_once __DIR__ . '/../app/views/errors/403.php';
        //             exit;
        //         }
        //     }
        // }

        // Check cooperative session (DISABLED for development)
        // if (empty($_SESSION['koperasi_id'])) {
        //     session_destroy();
        //     if ($request->isAjax()) {
        //         $response = new Response();
        //         return $response->json([
        //             'success' => false,
        //             'error' => ['message' => 'Invalid cooperative session']
        //         ], 401);
        //     } else {
        //         header('Location: ' . middleware_app_url('login'));
        //         exit;
        //     }
        // }

        return $next($request);
    }
}

// CSRF Protection Middleware
class CSRFMiddleware implements MiddlewareInterface {
    public function handle($request, $next) {
        // Skip CSRF check for GET requests and AJAX requests
        if ($request->method() === 'GET' || $request->isAjax()) {
            return $next($request);
        }

        // Check CSRF token
        $token = $request->get('_csrf') ?? $request->header('X-CSRF-Token');

        if (!$this->validateToken($token)) {
            if ($request->isAjax()) {
                $response = new Response();
                return $response->json([
                    'success' => false,
                    'error' => ['message' => 'CSRF token validation failed']
                ], 403);
            } else {
                http_response_code(403);
                die('CSRF token validation failed');
            }
        }

        return $next($request);
    }

    private function validateToken($token) {
        if (!$token) return false;

        $sessionToken = $_SESSION['csrf_token'] ?? null;
        if (!$sessionToken) return false;

        return hash_equals($sessionToken, $token);
    }

    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// CORS Middleware
class CORSMiddleware implements MiddlewareInterface {
    private $allowedOrigins = ['http://localhost:3000', 'http://127.0.0.1:3000'];
    private $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
    private $allowedHeaders = ['Content-Type', 'X-Requested-With', 'X-CSRF-Token', 'Authorization'];
    private $allowCredentials = true;

    public function handle($request, $next) {
        $origin = $request->header('Origin');

        // Set CORS headers
        header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
        header('Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $this->allowedHeaders));

        if ($this->allowCredentials) {
            header('Access-Control-Allow-Credentials: true');
        }

        header('Access-Control-Max-Age: 86400'); // 24 hours

        // Handle preflight requests
        if ($request->method() === 'OPTIONS') {
            exit(0);
        }

        return $next($request);
    }
}

// Logging Middleware
class LoggingMiddleware implements MiddlewareInterface {
    private $logFile;
    private $logLevel;

    public function __construct($logFile = null, $logLevel = 'info') {
        $this->logFile = $logFile ?: __DIR__ . '/../logs/middleware_' . date('Y-m-d') . '.log';
        $this->logLevel = $logLevel;

        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    public function handle($request, $next) {
        $startTime = microtime(true);

        // Log incoming request
        $this->logRequest($request);

        try {
            $response = $next($request);

            // Log successful response
            $duration = microtime(true) - $startTime;
            $this->logResponse($request, 'success', $duration);

            return $response;

        } catch (Exception $e) {
            // Log error response
            $duration = microtime(true) - $startTime;
            $this->logResponse($request, 'error', $duration, $e->getMessage());

            throw $e;
        }
    }

    private function logRequest($request) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => 'info',
            'type' => 'request',
            'method' => $request->method(),
            'uri' => $request->uri(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $_SESSION['user_id'] ?? null,
            'session_id' => session_id()
        ];

        $this->writeLog($logData);
    }

    private function logResponse($request, $status, $duration, $error = null) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $status === 'error' ? 'error' : 'info',
            'type' => 'response',
            'method' => $request->method(),
            'uri' => $request->uri(),
            'status' => $status,
            'duration' => round($duration * 1000, 2) . 'ms',
            'error' => $error
        ];

        $this->writeLog($logData);
    }

    private function writeLog($data) {
        $logLine = json_encode($data) . PHP_EOL;
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
}

// Rate Limiting Middleware
class RateLimitMiddleware implements MiddlewareInterface {
    private $maxRequests = 100; // requests per window
    private $windowSeconds = 60; // 1 minute window
    private $redis = null;

    public function __construct($maxRequests = 100, $windowSeconds = 60) {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;

        // Try to connect to Redis (optional)
        try {
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1', 6379);
        } catch (Exception $e) {
            // Redis not available, use file-based rate limiting
            $this->redis = null;
        }
    }

    public function handle($request, $next) {
        $identifier = $this->getIdentifier($request);

        if ($this->isRateLimited($identifier)) {
            if ($request->isAjax()) {
                $response = new Response();
                return $response->json([
                    'success' => false,
                    'error' => ['message' => 'Rate limit exceeded']
                ], 429);
            } else {
                http_response_code(429);
                die('Rate limit exceeded. Please try again later.');
            }
        }

        $this->recordRequest($identifier);
        return $next($request);
    }

    private function getIdentifier($request) {
        // Use IP + User ID combination
        $ip = $request->ip();
        $userId = $_SESSION['user_id'] ?? 'anonymous';
        return md5($ip . '_' . $userId);
    }

    private function isRateLimited($identifier) {
        $key = "ratelimit:{$identifier}";
        $current = $this->getCurrentRequests($key);

        return $current >= $this->maxRequests;
    }

    private function recordRequest($identifier) {
        $key = "ratelimit:{$identifier}";
        $this->incrementRequests($key);
    }

    private function getCurrentRequests($key) {
        if ($this->redis) {
            return (int) $this->redis->get($key);
        } else {
            // File-based fallback
            $file = sys_get_temp_dir() . '/' . $key . '.count';
            if (file_exists($file) && (time() - filemtime($file)) < $this->windowSeconds) {
                return (int) file_get_contents($file);
            }
            return 0;
        }
    }

    private function incrementRequests($key) {
        if ($this->redis) {
            $this->redis->incr($key);
            $this->redis->expire($key, $this->windowSeconds);
        } else {
            // File-based fallback
            $file = sys_get_temp_dir() . '/' . $key . '.count';
            $current = $this->getCurrentRequests($key);
            file_put_contents($file, $current + 1);
        }
    }
}

// Security Headers Middleware
class SecurityHeadersMiddleware implements MiddlewareInterface {
    public function handle($request, $next) {
        // Security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // Content Security Policy (basic)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://ajax.googleapis.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com;");

        // HSTS (only for HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        return $next($request);
    }
}

// Middleware Pipeline
class MiddlewarePipeline {
    private $middlewares = [];
    private $request;

    public function __construct($request = null) {
        $this->request = $request ?: new Request(array_merge($_GET, $_POST));
    }

    public function add($middleware) {
        if (is_string($middleware)) {
            $middleware = new $middleware();
        }
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function run($finalHandler) {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            function ($next, $middleware) {
                return function ($request) use ($middleware, $next) {
                    return $middleware->handle($request, $next);
                };
            },
            $finalHandler
        );

        return $pipeline($this->request);
    }
}

// Utility functions for backward compatibility
function require_login() {
    $middleware = new MiddlewarePipeline();
    $middleware->add(new AuthMiddleware());
    return $middleware;
}

function require_role($roles) {
    $middleware = new MiddlewarePipeline();
    $middleware->add(new AuthMiddleware($roles));
    return $middleware;
}

// CSRF token generation for templates
function csrf_token() {
    return CSRFMiddleware::generateToken();
}

// Example usage:
/*
// Basic authentication
$pipeline = new MiddlewarePipeline();
$pipeline->add(new SecurityHeadersMiddleware());
$pipeline->add(new CORSMiddleware());
$pipeline->add(new LoggingMiddleware());
$pipeline->add(new RateLimitMiddleware());
$pipeline->add(new CSRFMiddleware());
$pipeline->add(new AuthMiddleware(['admin', 'pengurus']));

$result = $pipeline->run(function($request) {
    // Your application logic here
    return new Response('Success');
});

$result->send();
*/
?>
