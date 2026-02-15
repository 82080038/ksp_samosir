<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!-- Main Content Container -->
<div class="main-content" id="main-content" data-page="{module}">

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="{module}-{page}">{title}</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        {action_buttons}
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

<!-- Page Content -->
{content}

</div>

<!-- JavaScript for Page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page {module}-{page} initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('{title}', '{module}-{page}');
    }
});

// Global functions
function save{module_capitalize}() {
    const form = document.querySelector('form');
    if (form) {
        form.dispatchEvent(new Event('submit'));
    }
}
</script>

<style>
/* Page-specific styles */
.page-title {
    font-weight: 700;
}

.main-content {
    min-height: 400px;
}
</style>