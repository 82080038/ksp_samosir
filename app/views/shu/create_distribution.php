<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Buat Distribusi Keuntungan</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('shu') ?>">SHU</a></li>
                <li class="breadcrumb-item active">Buat Distribusi</li>
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

<div class="card">
    <div class="card-header">
        <h5>Form Distribusi Keuntungan</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('shu/createDistribution') ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="periode" class="form-label">Periode *</label>
                        <input type="text" class="form-control" id="periode" name="periode" placeholder="Contoh: 2024" required>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_distribusi" class="form-label">Tanggal Distribusi *</label>
                        <input type="date" class="form-control" id="tanggal_distribusi" name="tanggal_distribusi" required>
                    </div>

                    <div class="mb-3">
                        <label for="total_keuntungan" class="form-label">Total Keuntungan (Rp) *</label>
                        <input type="number" step="0.01" class="form-control" id="total_keuntungan" name="total_keuntungan" placeholder="0.00" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="shu_anggota" class="form-label">SHU untuk Anggota (Rp)</label>
                        <input type="number" step="0.01" class="form-control" id="shu_anggota" name="shu_anggota" placeholder="0.00">
                    </div>

                    <div class="mb-3">
                        <label for="dividen_investor" class="form-label">Dividen untuk Investor (Rp)</label>
                        <input type="number" step="0.01" class="form-control" id="dividen_investor" name="dividen_investor" placeholder="0.00">
                    </div>

                    <div class="mb-3">
                        <label for="cadangan_koperasi" class="form-label">Cadangan Koperasi (Rp)</label>
                        <input type="number" step="0.01" class="form-control" id="cadangan_koperasi" name="cadangan_koperasi" placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <h6>Informasi Pembagian:</h6>
                <div id="distribution_summary">
                    <p>Total: Rp <span id="total_display">0.00</span></p>
                    <p>Selisih: Rp <span id="difference_display">0.00</span></p>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Buat Distribusi</button>
                <a href="<?= base_url('shu') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function updateSummary() {
    const total = parseFloat(document.getElementById('total_keuntungan').value) || 0;
    const shu = parseFloat(document.getElementById('shu_anggota').value) || 0;
    const dividen = parseFloat(document.getElementById('dividen_investor').value) || 0;
    const cadangan = parseFloat(document.getElementById('cadangan_koperasi').value) || 0;

    const allocated = shu + dividen + cadangan;
    const difference = total - allocated;

    document.getElementById('total_display').textContent = total.toLocaleString('id-ID');
    document.getElementById('difference_display').textContent = difference.toLocaleString('id-ID');

    // Change color based on difference
    const diffElement = document.getElementById('difference_display');
    if (difference === 0) {
        diffElement.style.color = 'green';
    } else if (difference > 0) {
        diffElement.style.color = 'orange';
    } else {
        diffElement.style.color = 'red';
    }
}

// Auto-calculate suggested distribution
document.getElementById('total_keuntungan').addEventListener('input', function() {
    const total = parseFloat(this.value) || 0;
    if (total > 0) {
        // Suggested distribution: 70% SHU, 10% Dividen, 20% Cadangan
        const shu = total * 0.7;
        const dividen = total * 0.1;
        const cadangan = total * 0.2;

        document.getElementById('shu_anggota').value = shu.toFixed(2);
        document.getElementById('dividen_investor').value = dividen.toFixed(2);
        document.getElementById('cadangan_koperasi').value = cadangan.toFixed(2);
    }
    updateSummary();
});

// Update summary on any input change
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', updateSummary);
});

// Initial update
updateSummary();
</script>
