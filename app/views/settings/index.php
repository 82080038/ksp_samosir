<div class="container-fluid px-3 px-md-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1">Pengaturan Aplikasi</h2>
            <p class="text-muted mb-0">Koperasi Pemasaran Kepolisian Polres Samosir</p>
        </div>
        <span class="badge bg-primary">Mode Development</span>
    </div>

    <div class="row g-3">
        <!-- Info Koperasi -->
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-building me-2"></i>
                    <strong>Identitas Koperasi</strong>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('settings/update') ?>" method="post" class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Koperasi</label>
                            <input type="text" name="nama_koperasi" class="form-control" placeholder="KSP Samosir" value="<?= getPengaturan('nama_koperasi')['value'] ?? 'KSP Samosir' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= getPengaturan('email')['value'] ?? 'info@ksp_samosir.co.id' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="no_telp" class="form-control" value="<?= getPengaturan('no_telp')['value'] ?? '(021) 1234567' ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"><?= getPengaturan('alamat')['value'] ?? 'Jl. Contoh No.123' ?></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-save me-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Parameter Keuangan -->
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-graph-up-arrow me-2"></i>
                    <strong>Parameter Keuangan</strong>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('settings/update') ?>" method="post" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bunga Simpanan Wajib (%)</label>
                            <input type="number" step="0.01" name="bunga_simpanan_wajib" class="form-control" value="<?= getPengaturan('bunga_simpanan_wajib')['value'] ?? '3.00' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bunga Simpanan Sukarela (%)</label>
                            <input type="number" step="0.01" name="bunga_simpanan_sukarela" class="form-control" value="<?= getPengaturan('bunga_simpanan_sukarela')['value'] ?? '4.00' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bunga Pinjaman (%)</label>
                            <input type="number" step="0.01" name="bunga_pinjaman" class="form-control" value="<?= getPengaturan('bunga_pinjaman')['value'] ?? '12.00' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Batas Pinjaman Maksimum (Rp)</label>
                            <input type="number" name="max_loan_limit" class="form-control" value="<?= getPengaturan('max_loan_limit')['value'] ?? '20000000' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Batas Transaksi Harian (Rp)</label>
                            <input type="number" name="daily_transaction_limit" class="form-control" value="<?= getPengaturan('daily_transaction_limit')['value'] ?? '50000000' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Simpanan Pokok Minimum (Rp)</label>
                            <input type="number" name="simpanan_pokok_minimum" class="form-control" value="<?= getPengaturan('simpanan_pokok_minimum')['value'] ?? '1000000' ?>">
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-save me-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sumber Alamat -->
        <div class="col-12 col-xl-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-geo-alt me-2"></i>
                    <strong>Sumber Alamat</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">Menggunakan database <code>alamat_db</code> langsung (provinces, regencies, districts, villages).</p>
                    <ul class="list-unstyled small mb-3">
                        <li><i class="bi bi-check-circle text-success me-1"></i> Tidak menyimpan salinan di DB aplikasi</li>
                        <li><i class="bi bi-check-circle text-success me-1"></i> Lookup langsung ke alamat_db</li>
                        <li><i class="bi bi-check-circle text-success me-1"></i> Search dan dropdown dinamis</li>
                    </ul>
                    <div class="alert alert-info py-2 mb-0">
                        <div class="fw-semibold">Catatan</div>
                        Pastikan database <code>alamat_db</code> tersedia dan kredensial MySQL sesuai.
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance & Mode -->
        <div class="col-12 col-xl-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-shield-check me-2"></i>
                    <strong>Compliance / Mode</strong>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="authToggle" checked disabled>
                        <label class="form-check-label" for="authToggle">Auth/Role sedang <strong>DISABLED</strong> (dev)</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="complianceToggle" checked disabled>
                        <label class="form-check-label" for="complianceToggle">Compliance pinjaman <strong>DISABLED</strong> (dev)</label>
                    </div>
                    <p class="small text-muted mb-2">Lihat catatan <code>docs/DISABLED_FEATURES_DEV.md</code> untuk mengaktifkan kembali di produksi.</p>
                    <a class="btn btn-outline-primary btn-sm" href="<?= base_url('docs/DISABLED_FEATURES_DEV.md') ?>" target="_blank"><i class="bi bi-journal-text me-1"></i>Lihat catatan</a>
                </div>
            </div>
        </div>

        <!-- Ringkasan Aktivitas -->
        <div class="col-12 col-xl-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-speedometer me-2"></i>
                    <strong>Ringkasan Aktivitas</strong>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted">Total Aktivitas</small>
                            <div class="h5 mb-0">-</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">SHU Terdistribusi</small>
                            <div class="h5 mb-0">-</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Catatan</small>
                            <p class="mb-0 small text-muted">Data real akan tampil setelah compliance dan auth diaktifkan kembali.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
