<?php
// Dependency management
if (!function_exists('initView')) {
    require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';
}
if (!function_exists('getCurrentUser')) {
    require_once __DIR__ . '/../../../config/config.php';
}
$pageInfo = $pageInfo ?? (function_exists('initView') ? initView() : []);
$user = $user ?? (function_exists('getCurrentUser') ? getCurrentUser() : []);
$role = $role ?? ($user['role'] ?? 'admin');
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="tax-pph21">Perhitungan PPh 21</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">PPh 21 Calculation</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary">
            <i class="bi bi-calculator"></i> Calculate PPh 21
        </button>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    <strong>PPh 21 (Pajak Penghasilan Pasal 21)</strong> - Perhitungan pajak penghasilan dari pegawai atau peserta kegiatan.
</div>

<!-- PPh 21 Calculator Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Perhitungan PPh 21</h5>
            </div>
            <div class="card-body">
                <form id="pph21-form">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="nama_pegawai" class="form-label">Nama Pegawai/Peserta</label>
                            <input type="text" class="form-control" id="nama_pegawai" required>
                        </div>
                        <div class="col-md-6">
                            <label for="npwp" class="form-label">NPWP</label>
                            <input type="text" class="form-control" id="npwp" placeholder="XX.XXX.XXX.X-XXX.XXX">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="periode" class="form-label">Periode</label>
                            <select class="form-select" id="periode" required>
                                <option value="">Pilih Periode</option>
                                <option value="bulanan">Bulanan</option>
                                <option value="tahunan">Tahunan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tahun_pajak" class="form-label">Tahun Pajak</label>
                            <input type="number" class="form-control" id="tahun_pajak" value="2026" required>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="gaji_pokok" class="form-label">Gaji Pokok</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="gaji_pokok" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="tunjangan" class="form-label">Tunjangan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="tunjangan">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="bonus" class="form-label">Bonus/THR</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="bonus">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="potongan" class="form-label">Potongan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="potongan">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calculator me-1"></i>Hitung PPh 21
                        </button>
                        <button type="reset" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Hasil Perhitungan</h5>
            </div>
            <div class="card-body">
                <div id="calculation-result" class="text-center">
                    <i class="bi bi-calculator text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Masukkan data dan klik "Hitung PPh 21"</p>
                </div>

                <div id="result-details" style="display: none;">
                    <div class="border rounded p-3 mb-3">
                        <h6>Detail Perhitungan:</h6>
                        <div class="row text-sm">
                            <div class="col-6">Penghasilan Bruto:</div>
                            <div class="col-6 text-end fw-bold" id="bruto-amount">-</div>
                        </div>
                        <div class="row text-sm">
                            <div class="col-6">Biaya Jabatan:</div>
                            <div class="col-6 text-end fw-bold" id="biaya-jabatan">-</div>
                        </div>
                        <div class="row text-sm">
                            <div class="col-6">Penghasilan Neto:</div>
                            <div class="col-6 text-end fw-bold" id="neto-amount">-</div>
                        </div>
                        <div class="row text-sm">
                            <div class="col-6">PTKP:</div>
                            <div class="col-6 text-end fw-bold" id="ptkp-amount">-</div>
                        </div>
                        <div class="row text-sm border-top pt-2 mt-2">
                            <div class="col-6">PKP:</div>
                            <div class="col-6 text-end fw-bold" id="pkp-amount">-</div>
                        </div>
                        <div class="row text-sm">
                            <div class="col-6">PPh 21 Terutang:</div>
                            <div class="col-6 text-end fw-bold text-primary" id="pph21-amount">-</div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button class="btn btn-outline-primary btn-sm" onclick="printResult()">
                            <i class="bi bi-printer me-1"></i>Cetak Bukti Potong
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pph21-form');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        calculatePPH21();
    });
});

function calculatePPH21() {
    // Get form values
    const gajiPokok = parseFloat(document.getElementById('gaji_pokok').value) || 0;
    const tunjangan = parseFloat(document.getElementById('tunjangan').value) || 0;
    const bonus = parseFloat(document.getElementById('bonus').value) || 0;
    const potongan = parseFloat(document.getElementById('potongan').value) || 0;

    // Calculate bruto income
    const bruto = gajiPokok + tunjangan + bonus - potongan;

    // Biaya jabatan (5% dari bruto, max 6jt per bulan)
    const biayaJabatan = Math.min(bruto * 0.05, 6000000);

    // Penghasilan neto
    const neto = bruto - biayaJabatan;

    // PTKP (simplified - TK/0 for single)
    const ptkp = 54000000; // PTKP 2026 for TK/0

    // PKP
    const pkp = Math.max(0, neto - ptkp);

    // Calculate PPh 21 (simplified progressive rates)
    let pph21 = 0;
    if (pkp > 0) {
        if (pkp <= 60000000) {
            pph21 = pkp * 0.05;
        } else if (pkp <= 250000000) {
            pph21 = (60000000 * 0.05) + ((pkp - 60000000) * 0.15);
        } else if (pkp <= 500000000) {
            pph21 = (60000000 * 0.05) + (190000000 * 0.15) + ((pkp - 250000000) * 0.25);
        } else {
            pph21 = (60000000 * 0.05) + (190000000 * 0.15) + (250000000 * 0.25) + ((pkp - 500000000) * 0.30);
        }
    }

    // Display results
    document.getElementById('bruto-amount').textContent = formatCurrency(bruto);
    document.getElementById('biaya-jabatan').textContent = formatCurrency(biayaJabatan);
    document.getElementById('neto-amount').textContent = formatCurrency(neto);
    document.getElementById('ptkp-amount').textContent = formatCurrency(ptkp);
    document.getElementById('pkp-amount').textContent = formatCurrency(pkp);
    document.getElementById('pph21-amount').textContent = formatCurrency(pph21);

    document.getElementById('calculation-result').style.display = 'none';
    document.getElementById('result-details').style.display = 'block';
}

function formatCurrency(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
}

function printResult() {
    window.print();
}
</script>
