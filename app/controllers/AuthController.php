<?php
/**
 * Authentication Controller
 * Handles login, logout, and session management
 */

class AuthController {
    
    public function login() {
        if (isLoggedIn()) {
            redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->authenticate();
        } else {
            require_once __DIR__ . '/../views/auth/login.php';
        }
    }
    
    public function authenticate() {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            flashMessage('error', 'Username dan password wajib diisi');
            redirect('login');
        }
        
        // Check user credentials
        $user = fetchRow(
            "SELECT u.id, u.username, u.password, u.email, u.full_name, u.is_active, u.last_login,
                   r.name as role, r.description as role_description
            FROM users u 
            LEFT JOIN user_roles ur ON u.id = ur.user_id 
            LEFT JOIN roles r ON ur.role_id = r.id 
            WHERE u.username = ?",
            [$username],
            's'
        );
        
        if (!$user || !password_verify($password, $user['password'])) {
            flashMessage('error', 'Username atau password salah');
            redirect('login');
        }
        
        if (!$user['is_active']) {
            flashMessage('error', 'Akun tidak aktif');
            redirect('login');
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
        $_SESSION['logged_in'] = true;
        
        // Update last login
        executeNonQuery(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$user['id']],
            'i'
        );
        
        // Log activity
        logActivity('login', 'users', $user['id'], null, json_encode($user));
        
        flashMessage('success', 'Selamat datang, ' . $user['full_name'] . '!');
        redirect('dashboard');
    }
    
    public function register() {
        if (isLoggedIn()) {
            redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
        } else {
            require_once __DIR__ . '/../views/auth/register.php';
        }
    }

    public function store() {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $full_name = sanitize($_POST['full_name'] ?? '');
        
        // Validate input
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            flashMessage('error', 'Semua field wajib diisi');
            redirect('register');
        }
        
        if ($password !== $confirm_password) {
            flashMessage('error', 'Password tidak cocok');
            redirect('register');
        }
        
        if (strlen($password) < 6) {
            flashMessage('error', 'Password minimal 6 karakter');
            redirect('register');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flashMessage('error', 'Format email tidak valid');
            redirect('register');
        }
        
        // Check duplicates
        $existing = fetchRow("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email], 'ss');
        if ($existing) {
            flashMessage('error', 'Username atau email sudah terdaftar');
            redirect('register');
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        runInTransaction(function($conn) use ($username, $email, $hashed_password, $full_name) {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param('ssss', $username, $email, $hashed_password, $full_name);
            $stmt->execute();
            $user_id = $conn->insert_id;
            $stmt->close();
            
            // Assign member role
            $member_role_id = fetchRow("SELECT id FROM roles WHERE name = 'member'")['id'];
            if ($member_role_id) {
                $stmt = $conn->prepare("INSERT INTO user_roles (user_id, role_id, assigned_by) VALUES (?, ?, ?)");
                $stmt->bind_param('iii', $user_id, $member_role_id, $user_id);
                $stmt->execute();
                $stmt->close();
            }
        });
        
        flashMessage('success', 'Registrasi berhasil. Silakan login.');
        redirect('login');
    }
    
    public function logout() {
        session_destroy();
        redirect('login');
    }
}
