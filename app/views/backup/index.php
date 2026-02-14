<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Manajemen Backup & Recovery</h2>
        <p class="text-muted">Kelola backup database dan disaster recovery</p>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $backup_stats['total_backups'] ?></h5>
                        <p class="card-text">Total Backup</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-database fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= round($backup_stats['total_size'] / 1024 / 1024, 2) ?> MB</h5>
                        <p class="card-text">Total Ukuran</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-hdd fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title mb-0">
                            <?= $backup_stats['last_backup'] ? formatDate($backup_stats['last_backup'], 'd M Y H:i') : 'Belum ada' ?>
                        </h6>
                        <p class="card-text">Backup Terakhir</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= count($scheduled_backups) ?></h5>
                        <p class="card-text">Jadwal Backup</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <button class="btn btn-outline-primary btn-block" onclick="createBackup()">
                            <i class="fas fa-plus"></i> Buat Backup Baru
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('backup/scheduleBackup') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-calendar-plus"></i> Atur Jadwal
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-info btn-block" onclick="viewLogs()">
                            <i class="fas fa-list"></i> Lihat Log
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-warning btn-block" onclick="cleanupOldBackups()">
                            <i class="fas fa-broom"></i> Bersihkan Backup Lama
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backup Files List -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>File Backup</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nama File</th>
                                <th>Tipe</th>
                                <th>Ukuran</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($backup['filename']) ?></strong>
                                        <?php if (!empty($backup['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($backup['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($backup['type']) ?></span>
                                    </td>
                                    <td>
                                        <?= round($backup['file_size'] / 1024 / 1024, 2) ?> MB
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(formatDate($backup['created_at'], 'd M Y H:i')) ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= base_url('backup/downloadBackup/' . $backup['filename']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= $backup['filename'] ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="confirmRestore('<?= $backup['filename'] ?>')">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($backups)): ?>
                    <div class="alert alert-info">
                        <h6>Belum ada file backup</h6>
                        <p>Klik tombol "Buat Backup Baru" untuk membuat backup pertama Anda.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Jadwal Backup</h5>
            </div>
            <div class="card-body">
                <?php if (empty($scheduled_backups)): ?>
                    <p class="text-muted">Belum ada jadwal backup otomatis.</p>
                    <a href="<?= base_url('backup/scheduleBackup') ?>" class="btn btn-primary btn-sm">Atur Jadwal</a>
                <?php else: ?>
                    <?php foreach ($scheduled_backups as $schedule): ?>
                        <div class="border rounded p-3 mb-2">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1"><?= htmlspecialchars(ucfirst($schedule['frequency'])) ?></h6>
                                <span class="badge bg-success">Aktif</span>
                            </div>
                            <p class="mb-1">Waktu: <?= htmlspecialchars($schedule['scheduled_time']) ?></p>
                            <small class="text-muted">
                                Next run: <?= htmlspecialchars($schedule['next_run'] ? formatDate($schedule['next_run'], 'd M Y H:i') : 'Not scheduled') ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Panduan Backup</h5>
            </div>
            <div class="card-body">
                <h6>Tipe Backup:</h6>
                <ul>
                    <li><strong>Full:</strong> Backup seluruh database</li>
                    <li><strong>Incremental:</strong> Hanya data yang berubah</li>
                    <li><strong>Partial:</strong> Backup tabel tertentu</li>
                </ul>

                <h6>Frekuensi:</h6>
                <ul>
                    <li><strong>Daily:</strong> Setiap hari</li>
                    <li><strong>Weekly:</strong> Setiap minggu</li>
                    <li><strong>Monthly:</strong> Setiap bulan</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="createBackupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Backup Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?= base_url('backup/createBackup') ?>">
                    <div class="mb-3">
                        <label for="backup_type" class="form-label">Tipe Backup</label>
                        <select class="form-select" id="backup_type" name="backup_type" required>
                            <option value="full">Full Backup</option>
                            <option value="incremental">Incremental Backup</option>
                            <option value="partial">Partial Backup</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Buat Backup</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function createBackup() {
    new bootstrap.Modal(document.getElementById('createBackupModal')).show();
}

function confirmDelete(filename) {
    if (confirm('Hapus backup "' + filename + '"?')) {
        window.location.href = '<?= base_url('backup/deleteBackup/') ?>' + filename;
    }
}

function confirmRestore(filename) {
    if (confirm('Restore database dari backup "' + filename + '"? Ini akan menggantikan data saat ini.')) {
        window.location.href = '<?= base_url('backup/restoreBackup/') ?>' + filename;
    }
}

function viewLogs() {
    alert('Fitur lihat log backup akan diimplementasikan');
}

function cleanupOldBackups() {
    alert('Fitur bersihkan backup lama akan diimplementasikan');
}
</script>
