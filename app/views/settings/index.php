<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="settings">Pengaturan</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="settings/create" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah
                </a>
            </div>
    </div>
</div>



    </div>
</div>

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

<!-- Settings Content -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Pengaturan Sistem</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Manajemen User</h6>
                        <p class="text-muted">Kelola user dan hak akses sistem</p>
                        <a href="<?= base_url('settings/users') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-people"></i> Kelola User
                        </a>
                    </div>
                    <div class="col-md-6">
                        <h6>Role & Permissions</h6>
                        <p class="text-muted">Atur role dan permission user</p>
                        <a href="<?= base_url('settings/roles') ?>" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-shield-check"></i> Kelola Role
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<!-- JavaScript for Settings -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Settings page initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Pengaturan', 'settings');
    }
});

// Global functions
function saveSettings() {
    const form = document.querySelector('form');
    if (form) {
        form.dispatchEvent(new Event('submit'));
    }
}
</script>

<style>
/* Settings-specific styles */
.page-title {
    font-weight: 700;
}

.main-content {
    min-height: 400px;
}
</style>
