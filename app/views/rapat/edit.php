<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Edit Rapat</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('rapat') ?>">Rapat</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= base_url('rapat/update/' . $rapat['id']) ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Rapat *</label>
                        <input type="text" class="form-control" id="judul" name="judul" value="<?= htmlspecialchars($rapat['judul']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="jenis_rapat" class="form-label">Jenis Rapat *</label>
                        <select class="form-select" id="jenis_rapat" name="jenis_rapat" required>
                            <option value="">Pilih jenis rapat</option>
                            <option value="rapat_anggota" <?= $rapat['jenis_rapat'] === 'rapat_anggota' ? 'selected' : '' ?>>Rapat Anggota</option>
                            <option value="rapat_pengurus" <?= $rapat['jenis_rapat'] === 'rapat_pengurus' ? 'selected' : '' ?>>Rapat Pengurus</option>
                            <option value="rapat_pengawas" <?= $rapat['jenis_rapat'] === 'rapat_pengawas' ? 'selected' : '' ?>>Rapat Pengawas</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal" class="form-label">Tanggal *</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($rapat['tanggal']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="waktu" class="form-label">Waktu</label>
                                <input type="time" class="form-control" id="waktu" name="waktu" value="<?= htmlspecialchars($rapat['waktu']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="lokasi" class="form-label">Lokasi</label>
                        <textarea class="form-control" id="lokasi" name="lokasi" rows="2"><?= htmlspecialchars($rapat['lokasi']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="agenda" class="form-label">Agenda Rapat</label>
                        <textarea class="form-control" id="agenda" name="agenda" rows="4"><?= htmlspecialchars($rapat['agenda']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="terjadwal" <?= $rapat['status'] === 'terjadwal' ? 'selected' : '' ?>>Terjadwal</option>
                            <option value="berlangsung" <?= $rapat['status'] === 'berlangsung' ? 'selected' : '' ?>>Berlangsung</option>
                            <option value="selesai" <?= $rapat['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="dibatalkan" <?= $rapat['status'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Rapat</button>
                <a href="<?= base_url('rapat') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
