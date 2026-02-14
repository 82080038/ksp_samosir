<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Hitung SHU</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('shu') ?>">SHU</a></li>
                <li class="breadcrumb-item active">Hitung</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>Form Perhitungan SHU</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= base_url('shu/calculate') ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="periode_start" class="form-label">Periode Mulai *</label>
                                <input type="date" class="form-control" id="periode_start" name="periode_start" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="periode_end" class="form-label">Periode Akhir *</label>
                                <input type="date" class="form-control" id="periode_end" name="periode_end" required>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6>Catatan:</h6>
                        <p>Perhitungan SHU akan menggunakan stored procedure yang menghitung berdasarkan:</p>
                        <ul class="mb-0">
                            <li>Modal anggota (simpanan pokok, wajib, sukarela)</li>
                            <li>Jasa anggota dari transaksi (penjualan, pinjaman, dll)</li>
                            <li>Persentase pembagian sesuai AD/ART</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Hitung SHU</button>
                        <a href="<?= base_url('shu') ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Informasi SHU</h5>
            </div>
            <div class="card-body">
                <h6>Komponen Perhitungan SHU:</h6>
                <ul>
                    <li><strong>SHU dari Modal:</strong> Berdasarkan simpanan anggota</li>
                    <li><strong>SHU dari Jasa:</strong> Berdasarkan transaksi dengan anggota</li>
                    <li><strong>Cadangan Koperasi:</strong> 20-30% dari laba</li>
                    <li><strong>Dividen Investor:</strong> Jika ada investor eksternal</li>
                </ul>

                <hr>

                <h6>Persentase Pembagian:</h6>
                <ul>
                    <li>Anggota: 70-80%</li>
                    <li>Cadangan: 10-20%</li>
                    <li>Investor: 5-10%</li>
                    <li>Pengurus: 5%</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Riwayat Perhitungan</h5>
            </div>
            <div class="card-body">
                <?php
                $recent_calculations = fetchAll("SELECT * FROM shu_periode ORDER BY calculated_at DESC LIMIT 3");
                if (empty($recent_calculations)): ?>
                    <p class="text-muted small">Belum ada perhitungan SHU.</p>
                <?php else: ?>
                    <?php foreach ($recent_calculations as $calc): ?>
                        <div class="border-bottom pb-2 mb-2">
                            <small class="text-muted">
                                <?= htmlspecialchars(formatDate($calc['periode_start'], 'M Y')) ?> - <?= htmlspecialchars(formatDate($calc['periode_end'], 'M Y')) ?><br>
                                Total SHU: Rp <?= formatCurrency($calc['total_shu']) ?><br>
                                <span class="badge bg-success">Dihitung</span>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('periode_start').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDate = new Date(startDate);
    endDate.setMonth(endDate.getMonth() + 11); // 12 months period
    document.getElementById('periode_end').value = endDate.toISOString().split('T')[0];
});
</script>
