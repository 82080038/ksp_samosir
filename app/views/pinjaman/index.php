<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Pinjaman</h2>
    <div class="btn-group">
        <a class="btn btn-primary btn-sm" href="<?= base_url('pinjaman/create') ?>">Ajukan Pinjaman</a>
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
                <th>No Pinjaman</th>
                <th>Anggota</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Tgl Pengajuan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pinjaman as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['no_pinjaman']) ?></td>
                    <td><?= htmlspecialchars($row['anggota'] ?? '-') ?></td>
                    <td>Rp <?= number_format($row['jumlah_pinjaman'], 2, ',', '.') ?></td>
                    <td><span class="badge bg-<?= $row['status']==='disetujui'?'success':($row['status']==='pengajuan'?'warning':'secondary') ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td><?= htmlspecialchars($row['tanggal_pengajuan']) ?></td>
                    <td>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('pinjaman/edit/' . $row['id']) ?>">Edit</a>
                        <a class="btn btn-sm btn-outline-success" href="<?= base_url('pinjaman/approve/' . $row['id']) ?>">Approve</a>
                        <a class="btn btn-sm btn-outline-primary" href="<?= base_url('pinjaman/cairkan/' . $row['id']) ?>">Cairkan</a>
                        <a class="btn btn-sm btn-outline-danger" href="<?= base_url('pinjaman/delete/' . $row['id']) ?>" onclick="return confirm('Tolak/hapus pinjaman ini?')">Tolak</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
