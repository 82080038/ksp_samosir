<?php
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
$pinjaman = $pinjaman ?? [];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="pinjaman">Pinjaman</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('pinjaman/create') ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah
            </a>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
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
            <?php if (empty($pinjaman)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data pinjaman</td></tr>
            <?php else: ?>
                <?php foreach ($pinjaman as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['no_pinjaman'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['anggota'] ?? '-') ?></td>
                    <td><?= function_exists('formatUang') ? formatUang($row['jumlah_pinjaman'] ?? 0, '', false) : number_format($row['jumlah_pinjaman'] ?? 0) ?></td>
                    <td><span class="badge bg-<?= ($row['status'] ?? '') === 'disetujui' ? 'success' : (($row['status'] ?? '') === 'pengajuan' ? 'warning' : 'secondary') ?>"><?= htmlspecialchars($row['status'] ?? '-') ?></span></td>
                    <td><?= htmlspecialchars($row['tanggal_pengajuan'] ?? '-') ?></td>
                    <td>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('pinjaman/edit/' . ($row['id'] ?? '')) ?>">Edit</a>
                        <a class="btn btn-sm btn-outline-success" href="<?= base_url('pinjaman/approve/' . ($row['id'] ?? '')) ?>">Approve</a>
                        <a class="btn btn-sm btn-outline-danger" href="<?= base_url('pinjaman/delete/' . ($row['id'] ?? '')) ?>" onclick="return confirm('Tolak/hapus pinjaman ini?')">Tolak</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>