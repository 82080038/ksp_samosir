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
    <title>Login - KSP Samosir</title>
    
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
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 20px;
        }
        
        .login-sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .login-form {
            padding: 40px;
        }
        
        .brand-logo {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .brand-text {
            font-size: 1.2rem;
            opacity: 0.9;
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
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .feature-item {
            margin: 15px 0;
            font-size: 0.9rem;
        }
        
        .feature-item i {
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .login-sidebar {
                display: none;
            }
            
            .login-form {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="row g-0 h-100">
            <!-- Sidebar -->
            <div class="col-md-5">
                <div class="login-sidebar h-100">
                    <div class="brand-logo">
                        <i class="bi bi-bank"></i> KSP Samosir
                    </div>
                    <div class="brand-text mb-4">
                        Koperasi Simpan Pinjam<br>Sistem Manajemen Terintegrasi
                    </div>
                    
                    <div class="features">
                        <div class="feature-item">
                            <i class="bi bi-shield-check"></i>
                            Keamanan Data Terjamin
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-graph-up"></i>
                            Laporan Real-time
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-people"></i>
                            Manajemen Anggota
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-cash-stack"></i>
                            Simpan Pinjam Digital
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Login Form -->
            <div class="col-md-7">
                <div class="login-form">
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
                        <h2 class="mb-3">Selamat Datang Kembali</h2>
                        <p class="text-muted">Silakan login untuk mengakses sistem</p>
                    </div>
                    
                    <form method="post" action="<?= base_url('login') ?>" id="login-form">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person me-2"></i>Username
                            </label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Masukkan username" required autocomplete="username">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-2"></i>Password
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Masukkan password" required autocomplete="current-password">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Ingat saya
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login" id="btn-login">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                <span id="btn-text">Login</span>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Development Testing Info -->
                    <div class="alert alert-info mt-4" id="dev-info">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle me-2"></i>Informasi Akun Testing (Development)
                        </h6>
                        <small class="text-muted">Gunakan akun berikut untuk testing aplikasi:</small>
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="py-2">Role</th>
                                        <th class="py-2">Username</th>
                                        <th class="py-2">Password</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="py-2">
                                            <span class="badge bg-danger">Admin</span>
                                        </td>
                                        <td class="py-2"><code>admin</code></td>
                                        <td class="py-2"><code>admin123</code></td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <span class="badge bg-primary">Manager</span>
                                        </td>
                                        <td class="py-2"><code>manager</code></td>
                                        <td class="py-2"><code>manager123</code></td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <span class="badge bg-success">Staff</span>
                                        </td>
                                        <td class="py-2"><code>staff</code></td>
                                        <td class="py-2"><code>staff123</code></td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <span class="badge bg-warning">Anggota</span>
                                        </td>
                                        <td class="py-2"><code>anggota</code></td>
                                        <td class="py-2"><code>anggota123</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillAdminCreds()">
                                <i class="bi bi-person-fill me-1"></i>Isi Admin
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillStaffCreds()">
                                <i class="bi bi-person me-1"></i>Isi Staff
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="clearForm()">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </button>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            &copy; <?= date('Y') ?> KSP Samosir. All rights reserved.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('login-form');
        const btnLogin = document.getElementById('btn-login');
        const btnText = document.getElementById('btn-text');
        
        form.addEventListener('submit', function(e) {
            // Show loading state
            btnLogin.disabled = true;
            btnText.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Login...';
            
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
        
        // Auto-focus username field
        document.getElementById('username').focus();
        
        console.log('Login page initialized');
    });
    
    // Auto-fill functions for testing
    function fillAdminCreds() {
        document.getElementById('username').value = 'admin';
        document.getElementById('password').value = 'admin123';
        document.getElementById('username').focus();
    }
    
    function fillStaffCreds() {
        document.getElementById('username').value = 'staff';
        document.getElementById('password').value = 'staff123';
        document.getElementById('username').focus();
    }
    
    function clearForm() {
        document.getElementById('username').value = '';
        document.getElementById('password').value = '';
        document.getElementById('username').focus();
    }
    </script>
</body>
</html>
