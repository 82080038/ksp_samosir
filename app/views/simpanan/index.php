<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Simpanan</h2>
    <div class="btn-group">
        <a class="btn btn-primary btn-sm" href="<?= base_url('simpanan/create') ?>">Tambah Rekening</a>
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
                <th>No Rekening</th>
                <th>Anggota</th>
                <th>Jenis</th>
                <th>Saldo</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($simpanan as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['no_rekening']) ?></td>
                    <td><?= htmlspecialchars($row['anggota'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['nama_simpanan'] ?? '-') ?></td>
                    <td>Rp <?= number_format($row['saldo'], 2, ',', '.') ?></td>
                    <td><span class="badge bg-<?= $row['status']==='aktif'?'success':'secondary' ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('simpanan/edit/' . $row['id']) ?>">Edit</a>
                        <a class="btn btn-sm btn-outline-danger" href="<?= base_url('simpanan/delete/' . $row['id']) ?>" onclick="return confirm('Tutup rekening ini?')">Tutup</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
