<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>


</div>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="anggota">Data Anggota</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="anggota/create" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah
                </a>
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

<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

        <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-members">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="anggota">Data Anggota</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
                <a href="anggota/create" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah
                </a>
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
                        
                        <span class="h2 font-weight-bold mb-0 metric-value"><?= formatAngka($stats['total_anggota'] ?? 0) ?></span>
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
                        
                        <span class="h2 font-weight-bold mb-0 metric-value text-success">
                            <?= formatAngka($stats['anggota_aktif'] ?? 0) ?>
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
                        
                        <span class="h2 font-weight-bold mb-0 metric-value text-secondary">
                            <?= formatAngka($stats['anggota_nonaktif'] ?? 0) ?>
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
                        
                        <span class="h2 font-weight-bold mb-0 metric-value text-info">
                            <?= formatAngka($stats['pendaftaran_bulan_ini'] ?? 0) ?>
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
    <!-- Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Daftar Anggota</h5>
    </div>
    <div class="card-body">
        <!-- Server-side table rendering (fallback) -->
        <div class="table-responsive" id="anggota-table-container">
            <table class="table table-striped table-hover" id="anggota-table">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Kode Anggota</th>
                        <th>Nama Lengkap</th>
                        <th>No. KTP</th>
                        <th>No. HP</th>
                        <th>Status</th>
                        <th>Tanggal Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data) && count($data) > 0): ?>
                        <?php foreach ($data as $index => $anggota): ?>
                            <tr>
                                <td><?= ($pagination['current_page'] - 1) * $pagination['per_page'] + $index + 1 ?></td>
                                <td>
                                    <span class="badge bg-primary"><?= htmlspecialchars($anggota['no_anggota'] ?? '') ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($anggota['nama_lengkap'] ?? '') ?></strong>
                                    <?php if (!empty($anggota['email'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($anggota['email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($anggota['no_ktp'] ?? '') ?></td>
                                <td><?= htmlspecialchars($anggota['no_hp'] ?? '') ?></td>
                                <td>
                                    <?php 
                                    $status = $anggota['status_anggota'] ?? 'Tidak Aktif';
                                    $badgeClass = $status === 'Aktif' ? 'bg-success' : 'bg-warning';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $status ?></span>
                                </td>
                                <td><?= formatTanggal($anggota['tanggal_bergabung'] ?? '', 'd M Y') ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('anggota/edit?data=' . urlencode(json_encode($anggota))) ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deleteAnggota('<?= $anggota['id'] ?>')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mb-0">Belum ada data anggota</p>
                                <a href="<?= base_url('anggota/create') ?>" class="btn btn-primary btn-sm mt-2">
                                    <i class="bi bi-plus-circle"></i> Tambah Anggota Pertama
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php if ($pagination['has_prev']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($pagination['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript will enhance this table -->
<script>
// Table will be enhanced by JavaScript if available
</script>

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

</div>

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


</div>

<!-- JavaScript for DOM Manipulation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page anggota - index initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Anggota', 'anggota-index');
    }
});

// Global functions
function saveAnggota() {
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