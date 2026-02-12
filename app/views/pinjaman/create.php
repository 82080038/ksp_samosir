<h2>Ajukan Pinjaman</h2>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="post" action="<?= base_url('pinjaman/store') ?>">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Anggota *</label>
            <select name="anggota_id" class="form-select" required>
                <option value="">- Pilih Anggota -</option>
                <?php foreach ($anggota as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama_lengkap']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Jenis Pinjaman *</label>
            <select name="jenis_pinjaman_id" class="form-select" required>
                <option value="">- Pilih Jenis -</option>
                <?php foreach ($jenis as $j): ?>
                    <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nama_pinjaman']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">No Pinjaman *</label>
            <input type="text" name="no_pinjaman" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Jumlah Pinjaman *</label>
            <input type="number" step="0.01" name="jumlah_pinjaman" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Bunga (% per tahun)</label>
            <input type="number" step="0.01" name="bunga_persen" class="form-control" value="0">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tenor (bulan)</label>
            <input type="number" name="tenor_bulan" class="form-control" value="12">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Pengajuan</label>
            <input type="date" name="tanggal_pengajuan" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-8">
            <label class="form-label">Tujuan Pinjaman</label>
            <textarea name="tujuan_pinjaman" class="form-control" rows="2"></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="pengajuan">Pengajuan</option>
                <option value="ditolak">Ditolak</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?= base_url('pinjaman') ?>" class="btn btn-secondary">Batal</a>
    </div>
</form>
