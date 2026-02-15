<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="rapat">Rapat</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Jenis</th>
                <th class="d-none d-md-table-cell">Tanggal</th>
                <th class="d-none d-md-table-cell">Lokasi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rapat as $row): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($row['judul']) ?></strong><br>
                        <small class="text-muted">Oleh: <?= htmlspecialchars($row['created_by_name'] ?? 'Unknown') ?></small>
                    </td>
                    <td>
                        <span class="badge bg-secondary">
                            <?= htmlspecialchars(str_replace(['rapat_anggota', 'rapat_pengurus', 'rapat_pengawas'], ['Anggota', 'Pengurus', 'Pengawas'], $row['jenis_rapat'])) ?>
                        </span>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <?= htmlspecialchars(formatDate($row['tanggal'], 'd M Y')) ?><br>
                        <small><?= htmlspecialchars($row['waktu']) ?></small>
                    </td>
                    <td class="d-none d-md-table-cell"><?= htmlspecialchars($row['lokasi']) ?></td>
                    <td>
                        <span class="badge bg-<?= $row['status'] === 'selesai' ? 'success' : ($row['status'] === 'dibatalkan' ? 'danger' : 'warning') ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a class="btn btn-sm btn-outline-primary" href="<?= base_url('rapat/detail/' . $row['id']) ?>" aria-label="Detail Rapat">Detail</a>
                        <?php if ($row['status'] !== 'selesai' && $row['status'] !== 'dibatalkan'): ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('rapat/edit/' . $row['id']) ?>" aria-label="Edit Rapat">Edit</a>
                            <a class="btn btn-sm btn-outline-danger" href="<?= base_url('rapat/delete/' . $row['id']) ?>" onclick="return confirm('Batalkan rapat ini?')" aria-label="Hapus Rapat">Batal</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <nav aria-label="Pagination">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Sebelumnya</a></li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Selanjutnya</a></li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
