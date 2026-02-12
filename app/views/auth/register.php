<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
        }
        .register-card .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .register-card .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="text-center mb-4">
            <i class="bi bi-bank2 fa-3x text-primary mb-3"></i>
            <h2 class="h4 mb-1">Daftar Akun</h2>
            <p class="text-muted">Buat akun baru untuk akses aplikasi</p>
        </div>

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

        <form method="post" action="<?= base_url('register/store') ?>">
                <div class="row g-3">
                <div class="col-12">
                    <label for="username" class="form-label">Username *</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="col-12">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="col-12">
                    <label for="full_name" class="form-label">Nama Lengkap *</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                
                <div class="col-md-6">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="col-md-6">
                    <label for="confirm_password" class="form-label">Konfirmasi Password *</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">Daftar</button>
            </div>
        </form>
        
        <div class="text-center mt-3">
            <p class="mb-0">Sudah punya akun? <a href="<?= base_url('login') ?>" class="text-primary">Login di sini</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
