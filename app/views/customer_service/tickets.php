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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="cs-tickets">Kelola Tiket</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('customer_service/createTicket') ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Buat Tiket Baru
            </a>
            <a href="<?= base_url('customer_service') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID Tiket</th>
                        <th>Pelanggan</th>
                        <th>Subjek</th>
                        <th>Kategori</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td>
                                <strong>#<?= htmlspecialchars($ticket['id']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($ticket['customer_name']) ?></strong>
                            </td>
                            <td>
                                <div style="max-width: 200px;" title="<?= htmlspecialchars($ticket['deskripsi']) ?>">
                                    <?= htmlspecialchars(substr($ticket['subjek'], 0, 30)) ?>...
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($ticket['kategori']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $ticket['prioritas'] === 'high' ? 'danger' : ($ticket['prioritas'] === 'medium' ? 'warning' : 'info') ?>">
                                    <?= htmlspecialchars($ticket['prioritas']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $ticket['status'] === 'resolved' ? 'success' : ($ticket['status'] === 'open' ? 'warning' : 'info') ?>">
                                    <?= htmlspecialchars($ticket['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($ticket['created_at'], 'd M Y')) ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewTicket(<?= $ticket['id'] ?>)">
                                        Lihat
                                    </button>
                                    <?php if ($ticket['status'] === 'open'): ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="resolveTicket(<?= $ticket['id'] ?>)">
                                            Resolve
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Pagination" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal for ticket details -->
<div class="modal fade" id="ticketModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Tiket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="ticketContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewTicket(id) {
    // Placeholder for AJAX detail loading
    alert('Detail tiket akan dimuat dengan AJAX');
}

function resolveTicket(id) {
    if (confirm('Resolve tiket ini?')) {
        // Placeholder for resolution
        alert('Tiket diresolve');
    }
}
</script>
