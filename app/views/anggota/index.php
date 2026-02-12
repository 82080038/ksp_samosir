<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Anggota</h2>
    <div class="btn-group">
        <a class="btn btn-primary btn-sm" href="<?= base_url('anggota/create') ?>">Tambah Anggota</a>
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
                <th>No Anggota</th>
                <th>Nama</th>
                <th class="d-none d-md-table-cell">NIK</th>
                <th class="d-none d-md-table-cell">Kontak</th>
                <th>Status</th>
                <th class="d-none d-md-table-cell">Bergabung</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($anggota as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['no_anggota']) ?></td>
                    <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                    <td class="d-none d-md-table-cell"><?= htmlspecialchars($row['nik']) ?></td>
                    <td class="d-none d-md-table-cell"><?= htmlspecialchars($row['no_hp']) ?><br><small><?= htmlspecialchars($row['email']) ?></small></td>
                    <td><span class="badge bg-<?= $row['status']==='aktif'?'success':'secondary' ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td class="d-none d-md-table-cell"><?= htmlspecialchars($row['tanggal_gabung']) ?></td>
                    <td>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('anggota/edit/' . $row['id']) ?>" aria-label="Edit Anggota">Edit</a>
                        <a class="btn btn-sm btn-outline-danger" href="<?= base_url('anggota/delete/' . $row['id']) ?>" onclick="return confirm('Nonaktifkan anggota ini?')" aria-label="Hapus Anggota">Hapus</a>
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
