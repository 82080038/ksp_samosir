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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="accounting-jurnal">Jurnal Umum</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('accounting/createJournal') ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Buat Jurnal
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportJournal()">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $_GET['start_date'] ?? date('Y-m-01') ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Nomor jurnal atau keterangan..." value="<?= $_GET['search'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Journal Entries -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Journal Entries</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Jurnal</th>
                        <th>Keterangan</th>
                        <th>Akun</th>
                        <th>Debet</th>
                        <th>Kredit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jurnal)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data jurnal</td>
                    </tr>
                    <?php else: ?>
                        <?php $currentJournal = null; ?>
                        <?php foreach ($jurnal as $entry): ?>
                            <?php if ($currentJournal !== $entry['nomor_jurnal']): ?>
                                <?php if ($currentJournal !== null): ?>
                            <tr class="table-secondary">
                                <td colspan="7" class="text-center fw-bold">
                                    <hr>
                                </td>
                            </tr>
                                <?php endif; ?>
                                <tr class="table-info">
                                    <td><?= formatDate($entry['tanggal_jurnal']) ?></td>
                                    <td><strong><?= htmlspecialchars($entry['nomor_jurnal']) ?></strong></td>
                                    <td colspan="4"><?= htmlspecialchars($entry['keterangan']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editJournal('<?= $entry['id'] ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php $currentJournal = $entry['nomor_jurnal']; ?>
                            <?php endif; ?>
                            
                            <tr>
                                <td></td>
                                <td></td>
                                <td><?= htmlspecialchars($entry['detail_keterangan'] ?? '') ?></td>
                                <td><?= htmlspecialchars($entry['nama_perkiraan']) ?></td>
                                <td class="text-end"><?= formatCurrency($entry['debet']) ?></td>
                                <td class="text-end"><?= formatCurrency($entry['kredit']) ?></td>
                                <td></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <td colspan="4" class="text-end fw-bold">TOTAL:</td>
                        <td class="text-end fw-bold">
                            <?= formatCurrency(array_sum(array_column($jurnal, 'debet'))) ?>
                        </td>
                        <td class="text-end fw-bold">
                            <?= formatCurrency(array_sum(array_column($jurnal, 'kredit'))) ?>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Journal pagination">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
function exportJournal() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    window.open(`<?= base_url('accounting/exportJournal') ?>?start_date=${startDate}&end_date=${endDate}`, '_blank');
}

function editJournal(id) {
    window.location.href = `<?= base_url('accounting/editJournal') ?>?id=${id}`;
}
</script>
