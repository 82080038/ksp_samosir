<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="data:," >
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .login-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
        }
        .login-body {
            padding: 2rem;
        }
        .form-floating label {
            color: #6c757d;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem;
            font-weight: 500;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="bi bi-bank2 fa-3x mb-3"></i>
            <h1><?= APP_NAME ?></h1>
            <p>Sistem Manajemen Koperasi</p>
        </div>
        
        <div class="login-body">
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
            
            <?php if ($info = getFlashMessage('info')): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <?= $info ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Development Test Accounts -->
            <div class="alert alert-warning" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Mode Development:</strong> Gunakan akun test di bawah ini
            </div>

            <div class="accordion mb-3" id="testAccountsAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#testAccounts" aria-expanded="false" aria-controls="testAccounts">
                            <i class="bi bi-key me-2"></i>Klik untuk lihat Test Accounts
                        </button>
                    </h2>
                    <div id="testAccounts" class="accordion-collapse collapse" data-bs-parent="#testAccountsAccordion">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="py-2">Role</th>
                                            <th class="py-2">Username</th>
                                            <th class="py-2">Password</th>
                                            <th class="py-2">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="py-2">
                                                <span class="badge bg-danger">Admin</span>
                                            </td>
                                            <td class="py-2"><code>admin</code></td>
                                            <td class="py-2"><code>admin123</code></td>
                                            <td class="py-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillLogin('admin', 'admin123')">
                                                    <i class="bi bi-arrow-right-circle"></i> Use
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="py-2">
                                                <span class="badge bg-warning">Staff</span>
                                            </td>
                                            <td class="py-2"><code>staff</code></td>
                                            <td class="py-2"><code>staff123</code></td>
                                            <td class="py-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillLogin('staff', 'staff123')">
                                                    <i class="bi bi-arrow-right-circle"></i> Use
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="py-2">
                                                <span class="badge bg-success">Member</span>
                                            </td>
                                            <td class="py-2"><code>member</code></td>
                                            <td class="py-2"><code>member123</code></td>
                                            <td class="py-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillLogin('member', 'member123')">
                                                    <i class="bi bi-arrow-right-circle"></i> Use
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="http://localhost/ksp_samosir/login/authenticate">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    <label for="username">Username</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Ingat saya
                    </label>
                </div>
                
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-login">Login</button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="bi bi-shield-check me-1"></i>
                    Development Environment - Test Credentials Only
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-fill login form function
        function fillLogin(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
            
            // Add visual feedback
            const button = event.target.closest('button');
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check-circle"></i> Filled';
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-success');
            
            // Reset after 2 seconds
            setTimeout(() => {
                button.innerHTML = originalHtml;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-primary');
            }, 2000);
            
            // Focus on submit button
            document.querySelector('button[type="submit"]').focus();
        }
        
        // Auto-expand test accounts on page load if no error
        document.addEventListener('DOMContentLoaded', function() {
            const errorAlert = document.querySelector('.alert-danger');
            if (!errorAlert) {
                // No error, show test accounts for convenience
                const accordionButton = document.querySelector('#testAccountsAccordion .accordion-button');
                if (accordionButton) {
                    setTimeout(() => {
                        accordionButton.click();
                    }, 1000);
                }
            }
        });
    </script>
</body>
</html>
