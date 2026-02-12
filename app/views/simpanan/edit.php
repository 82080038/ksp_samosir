<h2>Edit Rekening Simpanan #<?= htmlspecialchars($simpanan['id']) ?></h2>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="post" action="<?= base_url('simpanan/update/' . $simpanan['id']) ?>">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Anggota *</label>
            <select name="anggota_id" class="form-select" required>
                <option value="">- Pilih Anggota -</option>
                <?php foreach ($anggota as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $simpanan['anggota_id']==$a['id']?'selected':'' ?>><?= htmlspecialchars($a['nama_lengkap']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Jenis Simpanan *</label>
            <select name="jenis_simpanan_id" class="form-select" required>
                <option value="">- Pilih Jenis -</option>
                <?php foreach ($jenis as $j): ?>
                    <option value="<?= $j['id'] ?>" <?= $simpanan['jenis_simpanan_id']==$j['id']?'selected':'' ?>><?= htmlspecialchars($j['nama_simpanan']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">No Rekening *</label>
            <input type="text" name="no_rekening" class="form-control" value="<?= htmlspecialchars($simpanan['no_rekening']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Saldo</label>
            <input type="number" step="0.01" name="saldo" class="form-control" value="<?= htmlspecialchars($simpanan['saldo']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Buka</label>
            <input type="date" name="tanggal_buka" class="form-control" value="<?= htmlspecialchars($simpanan['tanggal_buka']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="aktif" <?= $simpanan['status']=='aktif'?'selected':'' ?>>Aktif</option>
                <option value="dibekukan" <?= $simpanan['status']=='dibekukan'?'selected':'' ?>>Dibekukan</option>
                <option value="ditutup" <?= $simpanan['status']=='ditutup'?'selected':'' ?>>Ditutup</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?= base_url('simpanan') ?>" class="btn btn-secondary">Batal</a>
    </div>
</form>
