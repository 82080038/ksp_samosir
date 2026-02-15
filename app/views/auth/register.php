<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KSP Samosir</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            margin: 20px;
        }
        
        .register-form {
            padding: 40px;
        }
        
        .brand-logo {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .login-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-form">
            <!-- Flash Messages -->
            <?php if ($error = getFlashMessage('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success = getFlashMessage('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="text-center mb-4">
                <div class="brand-logo">
                    <i class="bi bi-bank"></i> KSP Samosir
                </div>
                <h4 class="mb-3">Daftar Akun Baru</h4>
                <p class="text-muted">Bergabung dengan Koperasi Simpan Pinjam kami</p>
            </div>
            
            <form method="post" action="<?= base_url('register') ?>" id="register-form">
                <div class="mb-3">
                    <label for="full_name" class="form-label">
                        <i class="bi bi-person me-2"></i>Nama Lengkap
                    </label>
                    <input type="text" class="form-control" id="full_name" name="full_name" 
                           placeholder="Masukkan nama lengkap" required>
                </div>
                
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="bi bi-person-badge me-2"></i>Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Pilih username" required autocomplete="username">
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-2"></i>Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="Masukkan email" required autocomplete="email">
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-2"></i>Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Buat password" required autocomplete="new-password">
                    <small class="form-text text-muted">Minimal 6 karakter</small>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">
                        <i class="bi bi-lock-fill me-2"></i>Konfirmasi Password
                    </label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Ulangi password" required autocomplete="new-password">
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">
                        Saya setuju dengan <a href="#" class="text-decoration-none">syarat dan ketentuan</a>
                    </label>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-register" id="btn-register">
                        <i class="bi bi-person-plus me-2"></i>
                        <span id="btn-text">Daftar</span>
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    Sudah punya akun? 
                    <a href="<?= base_url('login') ?>" class="login-link">Login di sini</a>
                </small>
            </div>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    &copy; <?= date('Y') ?> KSP Samosir. All rights reserved.
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('register-form');
        const btnRegister = document.getElementById('btn-register');
        const btnText = document.getElementById('btn-text');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        // Password confirmation validation
        function validatePasswords() {
            if (password.value && confirmPassword.value) {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Password tidak cocok');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
        }
        
        password.addEventListener('change', validatePasswords);
        confirmPassword.addEventListener('keyup', validatePasswords);
        
        form.addEventListener('submit', function(e) {
            // Show loading state
            btnRegister.disabled = true;
            btnText.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Mendaftar...';
            
            // Add spinning animation
            const style = document.createElement('style');
            style.textContent = `
                .spin {
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        });
        
        // Auto-focus first field
        document.getElementById('full_name').focus();
        
        console.log('Register page initialized');
    });
    </script>
</body>
</html>
