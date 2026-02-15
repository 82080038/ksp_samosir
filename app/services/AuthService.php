<?php
/**
 * Microservices Architecture - Authentication Service
 * Batch 8: Microservices Implementation
 */

require_once __DIR__ . '/../../config/database_multi.php';

class AuthService
{
    private $db;
    private $jwtSecret;
    private $tokenExpiry;
    
    public function __construct()
    {
        $this->db = DatabaseManager::getConnection('core');
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'ksp_samosir_secret_key_2026';
        $this->tokenExpiry = 3600; // 1 hour
    }
    
    /**
     * Authenticate user and generate JWT token
     */
    public function authenticate($credentials)
    {
        try {
            $username = $credentials['username'] ?? '';
            $password = $credentials['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                return ['success' => false, 'message' => 'Username and password required'];
            }
            
            // Get user from database
            $stmt = $this->db->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.username = ? AND u.is_active = 1
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password'])) {
                $this->logAuthAttempt($username, false, 'Invalid credentials');
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
            
            // Check MFA if enabled
            if ($user['mfa_enabled']) {
                return $this->handleMFA($user, $credentials);
            }
            
            // Generate JWT token
            $token = $this->generateJWT($user);
            
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Log successful authentication
            $this->logAuthAttempt($username, true, 'Successful login');
            
            return [
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role_name'],
                    'mfa_enabled' => $user['mfa_enabled']
                ],
                'expires_in' => $this->tokenExpiry
            ];
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Authentication failed'];
        }
    }
    
    /**
     * Handle Multi-Factor Authentication
     */
    private function handleMFA($user, $credentials)
    {
        $mfaCode = $credentials['mfa_code'] ?? '';
        
        if (empty($mfaCode)) {
            return [
                'success' => false,
                'message' => 'MFA code required',
                'mfa_required' => true,
                'mfa_method' => 'totp'
            ];
        }
        
        // Verify TOTP code (simplified - would use actual TOTP library)
        if (!$this->verifyTOTP($user['mfa_secret'], $mfaCode)) {
            $this->logAuthAttempt($user['username'], false, 'Invalid MFA code');
            return ['success' => false, 'message' => 'Invalid MFA code'];
        }
        
        // Generate JWT token after MFA verification
        $token = $this->generateJWT($user);
        $this->updateLastLogin($user['id']);
        $this->logAuthAttempt($user['username'], true, 'Successful MFA login');
        
        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'role' => $user['role_name'],
                'mfa_enabled' => $user['mfa_enabled']
            ],
            'expires_in' => $this->tokenExpiry
        ];
    }
    
    /**
     * Verify TOTP code (simplified implementation)
     */
    private function verifyTOTP($secret, $code)
    {
        // In production, use a proper TOTP library like spomky-labs/otphp
        // This is a simplified verification
        return strlen($code) === 6 && is_numeric($code);
    }
    
    /**
     * Generate JWT token
     */
    private function generateJWT($user)
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role_name'],
            'iat' => time(),
            'exp' => time() + $this->tokenExpiry,
            'iss' => 'ksp-samosir-auth'
        ];
        
        // Encode header and payload
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        // Create signature
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->jwtSecret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }
    
    /**
     * Verify JWT token
     */
    public function verifyToken($token)
    {
        try {
            if (empty($token)) {
                return ['success' => false, 'message' => 'Token required'];
            }
            
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return ['success' => false, 'message' => 'Invalid token format'];
            }
            
            list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
            
            // Verify signature
            $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->jwtSecret, true);
            $expectedSignature = $this->base64UrlEncode($signature);
            
            if (!hash_equals($expectedSignature, $signatureEncoded)) {
                return ['success' => false, 'message' => 'Invalid token signature'];
            }
            
            // Decode payload
            $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
            
            // Check expiration
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return ['success' => false, 'message' => 'Token expired'];
            }
            
            return [
                'success' => true,
                'user' => [
                    'id' => $payload['user_id'],
                    'username' => $payload['username'],
                    'role' => $payload['role']
                ]
            ];
        } catch (Exception $e) {
            error_log("Token verification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Token verification failed'];
        }
    }
    
    /**
     * Refresh JWT token
     */
    public function refreshToken($token)
    {
        try {
            $verification = $this->verifyToken($token);
            if (!$verification['success']) {
                return $verification;
            }
            
            // Get fresh user data
            $stmt = $this->db->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ? AND u.is_active = 1
            ");
            $stmt->execute([$verification['user']['id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found or inactive'];
            }
            
            // Generate new token
            $newToken = $this->generateJWT($user);
            
            return [
                'success' => true,
                'token' => $newToken,
                'expires_in' => $this->tokenExpiry
            ];
        } catch (Exception $e) {
            error_log("Token refresh error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Token refresh failed'];
        }
    }
    
    /**
     * Logout user
     */
    public function logout($token)
    {
        try {
            // Add token to blacklist (simplified - in production use Redis)
            $this->blacklistToken($token);
            
            return ['success' => true, 'message' => 'Logged out successfully'];
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Logout failed'];
        }
    }
    
    /**
     * Blacklist token
     */
    private function blacklistToken($token)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO token_blacklist (token, expires_at) 
                VALUES (?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
            ");
            return $stmt->execute([$token]);
        } catch (Exception $e) {
            error_log("Token blacklist error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if token is blacklisted
     */
    public function isTokenBlacklisted($token)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM token_blacklist 
                WHERE token = ? AND expires_at > NOW()
            ");
            $stmt->execute([$token]);
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (Exception $e) {
            error_log("Token blacklist check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Register new user
     */
    public function register($userData)
    {
        try {
            // Validate required fields
            $required = ['username', 'email', 'password', 'full_name'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return ['success' => false, 'message' => "{$field} is required"];
                }
            }
            
            // Check if username exists
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
            $stmt->execute([$userData['username']]);
            if ($stmt->fetch()['count'] > 0) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
            
            // Check if email exists
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
            $stmt->execute([$userData['email']]);
            if ($stmt->fetch()['count'] > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            // Create user
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, full_name, role_id, is_active) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $userData['username'],
                $userData['email'],
                $hashedPassword,
                $userData['full_name'],
                $userData['role_id'] ?? 2, // Default role
                1 // Active
            ]);
            
            if ($result) {
                $userId = $this->db->lastInsertId();
                $this->logAuthAttempt($userData['username'], true, 'User registration');
                
                return [
                    'success' => true,
                    'user_id' => $userId,
                    'message' => 'User registered successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Registration failed'];
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    /**
     * Update user password
     */
    public function updatePassword($userId, $currentPassword, $newPassword)
    {
        try {
            // Get current password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$hashedPassword, $userId]);
            
            if ($result) {
                // Invalidate all user tokens
                $this->invalidateUserTokens($userId);
                
                return ['success' => true, 'message' => 'Password updated successfully'];
            }
            
            return ['success' => false, 'message' => 'Password update failed'];
        } catch (Exception $e) {
            error_log("Password update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Password update failed'];
        }
    }
    
    /**
     * Enable MFA for user
     */
    public function enableMFA($userId)
    {
        try {
            $secret = $this->generateTOTPSecret();
            
            $stmt = $this->db->prepare("
                UPDATE users SET mfa_secret = ?, mfa_enabled = 1, updated_at = NOW() 
                WHERE id = ?
            ");
            $result = $stmt->execute([$secret, $userId]);
            
            if ($result) {
                return [
                    'success' => true,
                    'secret' => $secret,
                    'qr_code' => $this->generateTOTPQRCode($secret), // Would generate actual QR code
                    'message' => 'MFA enabled successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to enable MFA'];
        } catch (Exception $e) {
            error_log("MFA enable error: " . $e->getMessage());
            return ['success' => false, 'message' => 'MFA enable failed'];
        }
    }
    
    /**
     * Disable MFA for user
     */
    public function disableMFA($userId, $password)
    {
        try {
            // Verify password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Password is incorrect'];
            }
            
            $stmt = $this->db->prepare("
                UPDATE users SET mfa_enabled = 0, mfa_secret = NULL, updated_at = NOW() 
                WHERE id = ?
            ");
            $result = $stmt->execute([$userId]);
            
            return $result ? 
                ['success' => true, 'message' => 'MFA disabled successfully'] : 
                ['success' => false, 'message' => 'Failed to disable MFA'];
        } catch (Exception $e) {
            error_log("MFA disable error: " . $e->getMessage());
            return ['success' => false, 'message' => 'MFA disable failed'];
        }
    }
    
    /**
     * Generate TOTP secret
     */
    private function generateTOTPSecret()
    {
        // In production, use a proper TOTP library
        return 'JBSWY3DPEHPK3PXP'; // Example secret
    }
    
    /**
     * Generate TOTP QR code
     */
    private function generateTOTPQRCode($secret)
    {
        // In production, generate actual QR code
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';
    }
    
    /**
     * Update last login
     */
    private function updateLastLogin($userId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE users SET last_login = NOW() WHERE id = ?
            ");
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Last login update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Invalidate user tokens
     */
    private function invalidateUserTokens($userId)
    {
        try {
            // In production, this would add all user tokens to blacklist
            // For now, we'll just log the action
            error_log("Tokens invalidated for user ID: {$userId}");
            return true;
        } catch (Exception $e) {
            error_log("Token invalidation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log authentication attempt
     */
    private function logAuthAttempt($username, $success, $details)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO auth_logs (username, success, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $username,
                $success,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Auth log error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get authentication logs
     */
    public function getAuthLogs($userId = null, $limit = 50)
    {
        try {
            $sql = "
                SELECT al.*, u.full_name 
                FROM auth_logs al
                LEFT JOIN users u ON al.username = u.username
            ";
            
            $params = [];
            
            if ($userId) {
                $sql .= " WHERE u.id = ?";
                $params[] = $userId;
            }
            
            $sql .= " ORDER BY al.created_at DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Auth logs error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Base64 URL encode
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
    
    /**
     * Get user permissions
     */
    public function getUserPermissions($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.permission_code, p.name 
                FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                JOIN user_roles ur ON rp.role_id = ur.role_id
                WHERE ur.user_id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get permissions error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check user permission
     */
    public function hasPermission($userId, $permission)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                JOIN user_roles ur ON rp.role_id = ur.role_id
                WHERE ur.user_id = ? AND p.permission_code = ?
            ");
            $stmt->execute([$userId, $permission]);
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (Exception $e) {
            error_log("Permission check error: " . $e->getMessage());
            return false;
        }
    }
}
