<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Detail Rapat</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('rapat') ?>">Rapat</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>
    <div>
        <?php if ($rapat['status'] !== 'selesai' && $rapat['status'] !== 'dibatalkan'): ?>
            <a href="<?= base_url('rapat/edit/' . $rapat['id']) ?>" class="btn btn-outline-primary btn-sm">Edit Rapat</a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Meeting Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi Rapat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="text-primary mb-3"><?= htmlspecialchars($rapat['judul']) ?></h4>
                        <p><strong>Jenis:</strong>
                            <span class="badge bg-secondary ms-2">
                                <?= htmlspecialchars(str_replace(['rapat_anggota', 'rapat_pengurus', 'rapat_pengawas'], ['Anggota', 'Pengurus', 'Pengawas'], $rapat['jenis_rapat'])) ?>
                            </span>
                        </p>
                        <p><strong>Status:</strong>
                            <span class="badge bg-<?= $rapat['status'] === 'selesai' ? 'success' : ($rapat['status'] === 'dibatalkan' ? 'danger' : 'warning') ?> ms-2">
                                <?= htmlspecialchars($rapat['status']) ?>
                            </span>
                        </p>
                        <p><strong>Dibuat oleh:</strong> <?= htmlspecialchars($rapat['created_by_name'] ?? 'Unknown') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Tanggal:</strong> <?= htmlspecialchars(formatDate($rapat['tanggal'], 'l, d F Y')) ?></p>
                        <p><strong>Waktu:</strong> <?= htmlspecialchars($rapat['waktu'] ?: '-') ?></p>
                        <p><strong>Lokasi:</strong> <?= htmlspecialchars($rapat['lokasi'] ?: '-') ?></p>
                    </div>
                </div>
                <?php if (!empty($rapat['agenda'])): ?>
                    <div class="mt-3">
                        <h6>Agenda:</h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($rapat['agenda'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Minutes -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Notulen Rapat</h5>
                <?php if ($rapat['status'] === 'selesai'): ?>
                    <button class="btn btn-sm btn-outline-primary" onclick="addNotulen()">Tambah Notulen</button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($notulen)): ?>
                    <p class="text-muted">Belum ada notulen rapat.</p>
                <?php else: ?>
                    <?php foreach ($notulen as $note): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <small class="text-muted">
                                    Dibuat oleh: <?= htmlspecialchars($note['created_by_name'] ?? 'Unknown') ?> |
                                    <?= htmlspecialchars(formatDate($note['created_at'], 'd M Y H:i')) ?>
                                </small>
                            </div>
                            <p class="mt-2"><?= nl2br(htmlspecialchars($note['isi_notulen'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Decisions -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Keputusan Rapat</h5>
                <?php if ($rapat['status'] === 'selesai'): ?>
                    <button class="btn btn-sm btn-outline-success" onclick="addKeputusan()">Tambah Keputusan</button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($keputusan)): ?>
                    <p class="text-muted">Belum ada keputusan rapat.</p>
                <?php else: ?>
                    <?php foreach ($keputusan as $decision): ?>
                        <div class="border rounded p-3 mb-3">
                            <p class="mb-2"><strong>Keputusan:</strong> <?= nl2br(htmlspecialchars($decision['keputusan'])) ?></p>
                            <div class="row">
                                <div class="col-md-4">
                                    <small><strong>Status:</strong>
                                        <span class="badge bg-<?= $decision['status_pelaksanaan'] === 'selesai' ? 'success' : ($decision['status_pelaksanaan'] === 'dalam_proses' ? 'warning' : 'secondary') ?>">
                                            <?= htmlspecialchars($decision['status_pelaksanaan']) ?>
                                        </span>
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small><strong>PIC:</strong> <?= htmlspecialchars($decision['pic_name'] ?: '-') ?></small>
                                </div>
                                <div class="col-md-4">
                                    <small><strong>Deadline:</strong> <?= htmlspecialchars($decision['deadline'] ? formatDate($decision['deadline'], 'd M Y') : '-') ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Participants -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Peserta Rapat</h5>
                <?php if ($rapat['status'] !== 'selesai' && $rapat['status'] !== 'dibatalkan'): ?>
                    <button class="btn btn-sm btn-outline-secondary" onclick="managePeserta()">Kelola</button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($peserta)): ?>
                    <p class="text-muted">Belum ada peserta terdaftar.</p>
                <?php else: ?>
                    <?php foreach ($peserta as $participant): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= htmlspecialchars($participant['full_name']) ?></span>
                            <span class="badge bg-<?= $participant['status_kehadiran'] === 'hadir' ? 'success' : ($participant['status_kehadiran'] === 'izin' ? 'warning' : 'secondary') ?>">
                                <?= htmlspecialchars($participant['status_kehadiran']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modals for adding minutes and decisions (placeholders for future AJAX implementation) -->
<script>
function addNotulen() {
    alert('Fitur tambah notulen akan diimplementasikan dengan AJAX');
}

function addKeputusan() {
    alert('Fitur tambah keputusan akan diimplementasikan dengan AJAX');
}

function managePeserta() {
    alert('Fitur kelola peserta akan diimplementasikan dengan AJAX');
}
</script>
