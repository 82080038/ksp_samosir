<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Buat Tiket Baru</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('customer_service') ?>">Customer Service</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('customer_service/tickets') ?>">Tiket</a></li>
                <li class="breadcrumb-item active">Buat</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5>Form Tiket Customer Service</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('customer_service/createTicket') ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Pelanggan *</label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">Pilih pelanggan</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>"><?= htmlspecialchars($customer['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori *</label>
                        <select class="form-select" id="kategori" name="kategori" required>
                            <option value="">Pilih kategori</option>
                            <option value="produk">Produk</option>
                            <option value="pengiriman">Pengiriman</option>
                            <option value="pembayaran">Pembayaran</option>
                            <option value="pengembalian">Pengembalian</option>
                            <option value="komplain">Komplain</option>
                            <option value="informasi">Informasi</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="prioritas" class="form-label">Prioritas</label>
                        <select class="form-select" id="prioritas" name="prioritas">
                            <option value="low">Rendah</option>
                            <option value="medium" selected>Sedang</option>
                            <option value="high">Tinggi</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="subjek" class="form-label">Subjek *</label>
                        <input type="text" class="form-control" id="subjek" name="subjek" placeholder="Judul singkat masalah" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi *</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" placeholder="Jelaskan detail masalah..." required></textarea>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <h6>Informasi:</h6>
                <p>Tiket akan dibuat dengan status "Open" dan akan diproses oleh tim customer service.</p>
                <p>Pelanggan akan mendapatkan notifikasi melalui sistem internal.</p>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Buat Tiket</button>
                <a href="<?= base_url('customer_service/tickets') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
