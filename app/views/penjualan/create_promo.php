<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Buat Promo Baru</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('penjualan') ?>">Penjualan</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('penjualan/promos') ?>">Promo</a></li>
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
        <h5>Form Promo</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('penjualan/storePromo') ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="kode_promo" class="form-label">Kode Promo *</label>
                        <input type="text" class="form-control text-uppercase" id="kode_promo" name="kode_promo" placeholder="DISKON10" required>
                        <div class="form-text">Kode unik untuk promo ini</div>
                    </div>

                    <div class="mb-3">
                        <label for="jenis_diskon" class="form-label">Jenis Diskon *</label>
                        <select class="form-select" id="jenis_diskon" name="jenis_diskon" required>
                            <option value="">Pilih jenis diskon</option>
                            <option value="persen">Persentase (%)</option>
                            <option value="nominal">Nominal (Rp)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nilai_diskon" class="form-label">Nilai Diskon *</label>
                        <input type="number" step="0.01" class="form-control" id="nilai_diskon" name="nilai_diskon" placeholder="10.00" required>
                        <div class="form-text" id="nilai_help">Masukkan nilai diskon</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai *</label>
                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_akhir" class="form-label">Tanggal Akhir *</label>
                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi Promo</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Jelaskan promo ini..."></textarea>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <h6>Contoh Penggunaan:</h6>
                <ul>
                    <li><strong>Diskon Persen:</strong> Kode "HEMAT20" memberikan diskon 20% dari total belanja</li>
                    <li><strong>Diskon Nominal:</strong> Kode "POTONG5000" memberikan potongan Rp 5.000 dari total belanja</li>
                </ul>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Buat Promo</button>
                <a href="<?= base_url('penjualan/promos') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('jenis_diskon').addEventListener('change', function() {
    const jenis = this.value;
    const nilaiInput = document.getElementById('nilai_diskon');
    const helpText = document.getElementById('nilai_help');

    if (jenis === 'persen') {
        nilaiInput.step = '0.01';
        nilaiInput.max = '100';
        nilaiInput.placeholder = '20.00';
        helpText.textContent = 'Masukkan persentase diskon (0-100%)';
    } else if (jenis === 'nominal') {
        nilaiInput.step = '0.01';
        nilaiInput.removeAttribute('max');
        nilaiInput.placeholder = '50000.00';
        helpText.textContent = 'Masukkan nilai diskon dalam Rupiah';
    } else {
        nilaiInput.placeholder = '0.00';
        helpText.textContent = 'Masukkan nilai diskon';
    }
});

// Set minimum date for start date
document.getElementById('tanggal_mulai').min = new Date().toISOString().split('T')[0];

// Auto-set end date when start date changes
document.getElementById('tanggal_mulai').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDate = new Date(startDate);
    endDate.setMonth(endDate.getMonth() + 1); // Default 1 month duration
    document.getElementById('tanggal_akhir').value = endDate.toISOString().split('T')[0];
    document.getElementById('tanggal_akhir').min = this.value;
});

// Generate promo code suggestion
document.getElementById('jenis_diskon').addEventListener('change', function() {
    const jenis = this.value;
    const kodeInput = document.getElementById('kode_promo');

    if (jenis === 'persen') {
        kodeInput.placeholder = 'HEMAT20';
    } else if (jenis === 'nominal') {
        kodeInput.placeholder = 'POTONG5000';
    } else {
        kodeInput.placeholder = 'PROMO2024';
    }
});

// Initialize
document.getElementById('jenis_diskon').dispatchEvent(new Event('change'));
</script>
