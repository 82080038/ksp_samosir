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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="tax-pph23">Perhitungan PPh 23</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">PPh 23 Calculation</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary">
            <i class="bi bi-calculator"></i> Calculate PPh 23
        </button>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    <strong>PPh 23 (Pajak Penghasilan Pasal 23)</strong> - Perhitungan pajak penghasilan dari dividen, bunga, royalti, sewa, dan hadiah.
</div>

<!-- PPh 23 Calculator Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Perhitungan PPh 23</h5>
            </div>
            <div class="card-body">
                <form id="pph23-form">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="jenis_pph23" class="form-label">Jenis Penghasilan</label>
                            <select class="form-select" id="jenis_pph23" required>
                                <option value="">Pilih Jenis Penghasilan</option>
                                <option value="dividen">Dividen</option>
                                <option value="bunga">Bunga</option>
                                <option value="royalti">Royalti</option>
                                <option value="sewa">Sewa</option>
                                <option value="hadiah">Hadiah/Doorprize</option>
                                <option value="jasa">Jasa Teknik/Konsultan/Management</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_penerima" class="form-label">Nama Penerima</label>
                            <input type="text" class="form-control" id="nama_penerima" required>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="npwp_penerima" class="form-label">NPWP Penerima</label>
                            <input type="text" class="form-control" id="npwp_penerima" placeholder="XX.XXX.XXX.X-XXX.XXX">
                        </div>
                        <div class="col-md-6">
                            <label for="periode" class="form-label">Periode</label>
                            <input type="month" class="form-control" id="periode" required>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="bruto_amount" class="form-label">Jumlah Bruto</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="bruto_amount" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="potongan" class="form-label">Potongan/Pengurangan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="potongan" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="tarif_pph23" class="form-label">Tarif PPh 23</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="tarif_pph23" step="0.01" readonly>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="dpp" class="form-label">Dasar Pengenaan Pajak (DPP)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="dpp" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calculator me-1"></i>Hitung PPh 23
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
                    <p class="text-muted mt-2">Pilih jenis penghasilan dan masukkan data</p>
                </div>

                <div id="result-details" style="display: none;">
                    <div class="border rounded p-3 mb-3">
                        <h6>Detail Perhitungan:</h6>
                        <div class="row text-sm">
                            <div class="col-6">Jumlah Bruto:</div>
                            <div class="col-6 text-end fw-bold" id="bruto-display">-</div>
                        </div>
                        <div class="row text-sm">
                            <div class="col-6">Potongan:</div>
                            <div class="col-6 text-end fw-bold" id="potongan-display">-</div>
                        </div>
                        <div class="row text-sm">
                            <div class="col-6">DPP:</div>
                            <div class="col-6 text-end fw-bold" id="dpp-display">-</div>
                        </div>
                        <div class="row text-sm border-top pt-2 mt-2">
                            <div class="col-6">Tarif PPh 23:</div>
                            <div class="col-6 text-end fw-bold" id="tarif-display">-</div>
                        </div>
                        <div class="row text-sm">
                            <div class="col-6">PPh 23 Terutang:</div>
                            <div class="col-6 text-end fw-bold text-primary" id="pph23-amount">-</div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button class="btn btn-outline-primary btn-sm" onclick="generateBuktiPotong()">
                            <i class="bi bi-file-earmark-text me-1"></i>Generate Bukti Potong
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- PPh 23 Rates Info -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Tarif PPh 23</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless">
                        <tbody>
                            <tr>
                                <td>Dividen</td>
                                <td class="text-end">15%</td>
                            </tr>
                            <tr>
                                <td>Bunga</td>
                                <td class="text-end">15%</td>
                            </tr>
                            <tr>
                                <td>Royalti</td>
                                <td class="text-end">15%</td>
                            </tr>
                            <tr>
                                <td>Sewa Tanah/Bangunan</td>
                                <td class="text-end">10%</td>
                            </tr>
                            <tr>
                                <td>Hadiah/Doorprize</td>
                                <td class="text-end">25%</td>
                            </tr>
                            <tr>
                                <td>Jasa Teknik/Konsultan</td>
                                <td class="text-end">2%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pph23-form');
    const jenisSelect = document.getElementById('jenis_pph23');

    // Set default rate when jenis changes
    jenisSelect.addEventListener('change', function() {
        updateTarifRate(this.value);
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        calculatePPH23();
    });
});

function updateTarifRate(jenis) {
    const tarifField = document.getElementById('tarif_pph23');
    const rates = {
        'dividen': 15,
        'bunga': 15,
        'royalti': 15,
        'sewa': 10,
        'hadiah': 25,
        'jasa': 2
    };

    tarifField.value = rates[jenis] || 0;
}

function calculatePPH23() {
    const bruto = parseFloat(document.getElementById('bruto_amount').value) || 0;
    const potongan = parseFloat(document.getElementById('potongan').value) || 0;
    const tarif = parseFloat(document.getElementById('tarif_pph23').value) || 0;

    // DPP = Bruto - Potongan
    const dpp = bruto - potongan;

    // PPh 23 = DPP * Tarif
    const pph23 = dpp * (tarif / 100);

    // Display results
    document.getElementById('bruto-display').textContent = formatCurrency(bruto);
    document.getElementById('potongan-display').textContent = formatCurrency(potongan);
    document.getElementById('dpp-display').textContent = formatCurrency(dpp);
    document.getElementById('tarif-display').textContent = tarif + '%';
    document.getElementById('pph23-amount').textContent = formatCurrency(pph23);

    document.getElementById('calculation-result').style.display = 'none';
    document.getElementById('result-details').style.display = 'block';
}

function formatCurrency(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
}

function generateBuktiPotong() {
    alert('Fitur generate bukti potong akan diimplementasikan');
}
</script>
