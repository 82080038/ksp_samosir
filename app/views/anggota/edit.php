<h2>Edit Anggota #<?= htmlspecialchars($anggota['id']) ?></h2>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="post" action="<?= base_url('anggota/update/' . $anggota['id']) ?>">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">No Anggota *</label>
            <input type="text" name="no_anggota" class="form-control" value="<?= htmlspecialchars($anggota['no_anggota']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Nama Lengkap *</label>
            <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($anggota['nama_lengkap']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">NIK *</label>
            <input type="text" name="nik" class="form-control" value="<?= htmlspecialchars($anggota['nik']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tempat Lahir</label>
            <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($anggota['tempat_lahir']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($anggota['tanggal_lahir']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-select">
                <option value="" <?= $anggota['jenis_kelamin']==''?'selected':'' ?>>-</option>
                <option value="L" <?= $anggota['jenis_kelamin']=='L'?'selected':'' ?>>Laki-laki</option>
                <option value="P" <?= $anggota['jenis_kelamin']=='P'?'selected':'' ?>>Perempuan</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($anggota['alamat']) ?></textarea>
        </div>
        <div class="col-md-3">
            <label class="form-label">No HP</label>
            <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($anggota['no_hp']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($anggota['email']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Pekerjaan</label>
            <input type="text" name="pekerjaan" class="form-control" value="<?= htmlspecialchars($anggota['pekerjaan']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Pendapatan Bulanan</label>
            <input type="number" step="0.01" name="pendapatan_bulanan" class="form-control" value="<?= htmlspecialchars($anggota['pendapatan_bulanan']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Gabung</label>
            <input type="date" name="tanggal_gabung" class="form-control" value="<?= htmlspecialchars($anggota['tanggal_gabung']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="aktif" <?= $anggota['status']=='aktif'?'selected':'' ?>>Aktif</option>
                <option value="nonaktif" <?= $anggota['status']=='nonaktif'?'selected':'' ?>>Nonaktif</option>
                <option value="keluar" <?= $anggota['status']=='keluar'?'selected':'' ?>>Keluar</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?= base_url('anggota') ?>" class="btn btn-secondary">Batal</a>
    </div>
</form>
