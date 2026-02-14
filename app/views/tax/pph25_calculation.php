<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">PPh 25 Calculation</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary">
            <i class="bi bi-calculator"></i> Calculate PPh 25
        </button>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    <strong>PPh 25 (Pajak Penghasilan Pasal 25)</strong> - Perhitungan angsuran pajak penghasilan untuk wajib pajak badan dalam negeri dan bentuk usaha tetap.
</div>

<!-- PPh 25 Calculator Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Perhitungan PPh 25</h5>
            </div>
            <div class="card-body">
                <form id="pph25-form">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="nama_wp" class="form-label">Nama Wajib Pajak</label>
                            <input type="text" class="form-control" id="nama_wp" required>
                        </div>
                        <div class="col-md-6">
                            <label for="npwp" class="form-label">NPWP</label>
                            <input type="text" class="form-control" id="npwp" placeholder="XX.XXX.XXX.X-XXX.XXX">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="tahun_pajak" class="form-label">Tahun Pajak</label>
                            <input type="number" class="form-control" id="tahun_pajak" value="2026" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status_spt" class="form-label">Status SPT Tahun Terakhir</label>
                            <select class="form-select" id="status_spt" required>
                                <option value="">Pilih Status</option>
                                <option value="kurang_bayar">Kurang Bayar</option>
                                <option value="lebih_bayar">Lebih Bayar</option>
                                <option value="nihil">Nihil</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="pph_terutang" class="form-label">PPh Terutang Tahun Terakhir</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="pph_terutang" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="pph_disetor" class="form-label">PPh yang Telah Disetor</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="pph_disetor">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="pendapatan_bruto" class="form-label">Pendapatan Bruto</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="pendapatan_bruto" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="pengurangan" class="form-label">Pengurangan Fiskal</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="pengurangan">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calculator me-1"></i>Hitung PPh 25
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
                    <p class="text-muted mt-2">Masukkan data dan klik "Hitung PPh 25"</p>
                </div>

                <div id="result-details" style="display: none;">
                    <div class="border rounded p-3 mb-3">
                        <h6>Detail Perhitungan:</h6>
                        <div class="row text-sm">
                            <div class="col-6">PPh Terutang:</div>
                            <div class="col-6 text-end fw-bold" id="pph-terutang">-</div>
                        </div>
                        <div class="row text-sm">
                            <div class="col-6">PPh Disetor:</div>
                            <div class="col-6 text-end fw-bold" id="pph-disetor">-</div>
                        </div>
                        <div class="row text-sm">
                            <div class="col-6">Selisih:</div>
                            <div class="col-6 text-end fw-bold" id="selisih-amount">-</div>
                        </div>
                        <div class="row text-sm border-top pt-2 mt-2">
                            <div class="col-6">Pendapatan Bruto:</div>
                            <div class="col-6 text-end fw-bold" id="bruto-display">-</div>
                        </div>
                        <div class="row text-sm">
                            <div class="col-6">Pengurangan:</div>
                            <div class="col-6 text-end fw-bold" id="pengurangan-display">-</div>
                        </div>
                        <div class="row text-sm">
                            <div class="col-6">Pendapatan Neto:</div>
                            <div class="col-6 text-end fw-bold" id="neto-display">-</div>
                        </div>
                        <div class="row text-sm border-top pt-2 mt-2">
                            <div class="col-6">Angsuran PPh 25:</div>
                            <div class="col-6 text-end fw-bold text-primary" id="pph25-amount">-</div>
                        </div>
                    </div>

                    <div class="alert alert-light">
                        <small>
                            <strong>Catatan:</strong> Angsuran PPh 25 dibayar setiap bulan sebesar 1/12 dari PPh 25 yang terutang.
                        </small>
                    </div>

                    <div class="text-center">
                        <button class="btn btn-outline-primary btn-sm" onclick="generateSSP()">
                            <i class="bi bi-file-earmark-text me-1"></i>Generate SSP
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- PPh 25 Information -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Informasi PPh 25</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-light">
                    <small>
                        <strong>PPh 25</strong> adalah angsuran pajak penghasilan yang harus dibayar oleh wajib pajak badan dalam negeri atau bentuk usaha tetap setiap bulan.
                    </small>
                </div>

                <h6>Rumus Perhitungan:</h6>
                <div class="bg-light p-2 rounded mb-3">
                    <small>
                        <strong>PPh 25 = (Pendapatan Bruto - Pengurangan) × 22%</strong><br>
                        <strong>Angsuran Bulanan = PPh 25 ÷ 12</strong>
                    </small>
                </div>

                <div class="text-center">
                    <small class="text-muted">Tarif PPh Badan: 22%</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pph25-form');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        calculatePPH25();
    });
});

function calculatePPH25() {
    const pphTerutang = parseFloat(document.getElementById('pph_terutang').value) || 0;
    const pphDisetor = parseFloat(document.getElementById('pph_disetor').value) || 0;
    const pendapatanBruto = parseFloat(document.getElementById('pendapatan_bruto').value) || 0;
    const pengurangan = parseFloat(document.getElementById('pengurangan').value) || 0;

    // Calculate net income
    const pendapatanNeto = pendapatanBruto - pengurangan;

    // Calculate PPh 25 (22% of net income)
    const pph25 = pendapatanNeto * 0.22;

    // Calculate difference
    const selisih = pphTerutang - pphDisetor;

    // Display results
    document.getElementById('pph-terutang').textContent = formatCurrency(pphTerutang);
    document.getElementById('pph-disetor').textContent = formatCurrency(pphDisetor);
    document.getElementById('selisih-amount').textContent = formatCurrency(selisih);
    document.getElementById('bruto-display').textContent = formatCurrency(pendapatanBruto);
    document.getElementById('pengurangan-display').textContent = formatCurrency(pengurangan);
    document.getElementById('neto-display').textContent = formatCurrency(pendapatanNeto);
    document.getElementById('pph25-amount').textContent = formatCurrency(pph25);

    document.getElementById('calculation-result').style.display = 'none';
    document.getElementById('result-details').style.display = 'block';
}

function formatCurrency(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
}

function generateSSP() {
    alert('Fitur generate SSP akan diimplementasikan');
}
</script>
