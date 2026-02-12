<h2>Tambah Anggota</h2>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="post" action="<?= base_url('anggota/store') ?>">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">No Anggota *</label>
            <input type="text" name="no_anggota" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Nama Lengkap *</label>
            <input type="text" name="nama_lengkap" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">NIK *</label>
            <input type="text" name="nik" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tempat Lahir</label>
            <input type="text" name="tempat_lahir" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-select">
                <option value="">-</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" rows="2"></textarea>
        </div>
        <div class="col-md-3">
            <label class="form-label">No HP</label>
            <input type="text" name="no_hp" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Pekerjaan</label>
            <input type="text" name="pekerjaan" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Pendapatan Bulanan</label>
            <input type="number" step="0.01" name="pendapatan_bulanan" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Gabung</label>
            <input type="date" name="tanggal_gabung" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
                <option value="keluar">Keluar</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none;" id="loadingSpinner"></span>
            Simpan
        </button>
        <a href="<?= base_url('anggota') ?>" class="btn btn-secondary">Batal</a>
    </div>
</form>

<script>
document.getElementById('submitBtn').addEventListener('click', function() {
    document.getElementById('loadingSpinner').style.display = 'inline-block';
    this.disabled = true;
});
</script>
