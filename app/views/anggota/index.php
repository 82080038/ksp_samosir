<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2" style="color: black;">Manajemen Anggota</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="export-members">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary" id="refresh-members">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<?php if (true): ?>
<!-- Development Mode Notice -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-gear me-2"></i>
    <strong>Mode Development:</strong> Semua fitur dapat diakses tanpa autentikasi.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

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

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card dashboard-metric">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Total Anggota</h5>
                        <span class="h2 font-weight-bold mb-0 metric-value"><?= number_format($stats['total_anggota'] ?? 0) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="bg-primary bg-opacity-25 rounded p-3">
                            <i class="bi bi-people text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card dashboard-metric">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Anggota Aktif</h5>
                        <span class="h2 font-weight-bold mb-0 metric-value text-success">
                            <?= number_format($stats['anggota_aktif'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="bg-success bg-opacity-25 rounded p-3">
                            <i class="bi bi-person-check text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card dashboard-metric">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Anggota Nonaktif</h5>
                        <span class="h2 font-weight-bold mb-0 metric-value text-secondary">
                            <?= number_format($stats['anggota_nonaktif'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="bg-secondary bg-opacity-25 rounded p-3">
                            <i class="bi bi-person-x text-secondary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card dashboard-metric">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Pendaftaran Bulan Ini</h5>
                        <span class="h2 font-weight-bold mb-0 metric-value text-info">
                            <?= number_format($stats['pendaftaran_bulan_ini'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="bg-info bg-opacity-25 rounded p-3">
                            <i class="bi bi-calendar-plus text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Members Data Table Container -->
<div id="members-container" class="fade-in">
    <!-- Data table will be rendered here by JavaScript -->
</div>

<!-- Hidden Templates (for JavaScript use) -->
<div id="templates-container" style="display: none;">
    <!-- Templates are loaded dynamically by JavaScript -->
</div>

<!-- JavaScript for Member Management -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize KSP components if not already loaded
    if (typeof window.KSP === 'undefined') {
        console.error('KSP components not loaded');
        return;
    }

    // The MemberManager will auto-initialize when it detects #members-container
    console.log('Member management page loaded');

    // Additional page-specific functionality
    const refreshBtn = document.getElementById('refresh-members');
    const exportBtn = document.getElementById('export-members');

    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            // Trigger table refresh
            const event = new CustomEvent('refresh-members-table');
            document.dispatchEvent(event);
        });
    }

    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            // Implement export functionality
            KSP.showNotification('Export functionality coming soon', 'info');
        });
    }
});
</script>

<style>
/* Page-specific styles */
.dashboard-metric {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.dashboard-metric:hover {
    transform: translateY(-2px);
}

.metric-value {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
}

@media (max-width: 768px) {
    .metric-value {
        font-size: 2rem;
    }
}
</style>
