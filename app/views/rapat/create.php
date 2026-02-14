<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Tambah Rapat</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('rapat') ?>">Rapat</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= base_url('rapat/store') ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Rapat *</label>
                        <input type="text" class="form-control" id="judul" name="judul" required>
                    </div>

                    <div class="mb-3">
                        <label for="jenis_rapat" class="form-label">Jenis Rapat *</label>
                        <select class="form-select" id="jenis_rapat" name="jenis_rapat" required>
                            <option value="">Pilih jenis rapat</option>
                            <option value="rapat_anggota">Rapat Anggota</option>
                            <option value="rapat_pengurus">Rapat Pengurus</option>
                            <option value="rapat_pengawas">Rapat Pengawas</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal" class="form-label">Tanggal *</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="waktu" class="form-label">Waktu</label>
                                <input type="time" class="form-control" id="waktu" name="waktu">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="lokasi" class="form-label">Lokasi</label>
                        <textarea class="form-control" id="lokasi" name="lokasi" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="agenda" class="form-label">Agenda Rapat</label>
                        <textarea class="form-control" id="agenda" name="agenda" rows="4" placeholder="Jelaskan agenda rapat..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="terjadwal">Terjadwal</option>
                            <option value="berlangsung">Berlangsung</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan Rapat</button>
                <a href="<?= base_url('rapat') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
