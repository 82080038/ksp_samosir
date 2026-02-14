<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Tambah Pemasok</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('pemasok') ?>">Procurement</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('pemasok/suppliers') ?>">Pemasok</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5>Form Data Pemasok</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('pemasok/createSupplier') ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nama_perusahaan" class="form-label">Nama Perusahaan *</label>
                        <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" required>
                    </div>

                    <div class="mb-3">
                        <label for="npwp" class="form-label">NPWP</label>
                        <input type="text" class="form-control" id="npwp" name="npwp" placeholder="15 digit NPWP">
                    </div>

                    <div class="mb-3">
                        <label for="telepon" class="form-label">Telepon</label>
                        <input type="tel" class="form-control" id="telepon" name="telepon">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <select class="form-select" id="kategori" name="kategori">
                            <option value="">Pilih kategori</option>
                            <option value="makanan">Makanan</option>
                            <option value="minuman">Minuman</option>
                            <option value="bahan_baku">Bahan Baku</option>
                            <option value="peralatan">Peralatan</option>
                            <option value="jasa">Jasa</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan Pemasok</button>
                <a href="<?= base_url('pemasok/suppliers') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
