<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="accounting-create-journal">Buat Jurnal Baru</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('accounting/jurnal') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Form Jurnal</h5>
    </div>
    <div class="card-body">
        <form method="POST" id="journalForm">
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="tanggal_jurnal" class="form-label">Tanggal Jurnal <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal_jurnal" name="tanggal_jurnal" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="nomor_jurnal" class="form-label">Nomor Jurnal <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nomor_jurnal" name="nomor_jurnal" value="<?= htmlspecialchars($nomor_jurnal) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="keterangan" class="form-label">Keterangan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Masukkan keterangan jurnal..." required>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-12">
                    <h6>Detail Jurnal</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="detailTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Akun <span class="text-danger">*</span></th>
                                    <th>Keterangan</th>
                                    <th>Debet</th>
                                    <th>Kredit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select class="form-select akun-select" name="detail[0][akun_id]" required>
                                            <option value="">Pilih Akun</option>
                                            <?php foreach ($daftarAkun as $akun): ?>
                                                <option value="<?= $akun['id'] ?>"><?= $akun['kode_perkiraan'] ?> - <?= htmlspecialchars($akun['nama_perkiraan']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="detail[0][keterangan]" placeholder="Keterangan detail...">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control debet-input" name="detail[0][debet]" step="0.01" min="0" onchange="calculateBalance()">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control kredit-input" name="detail[0][kredit]" step="0.01" min="0" onchange="calculateBalance()">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary">
                                    <th colspan="2">TOTAL</th>
                                    <th class="text-end" id="totalDebet">0.00</th>
                                    <th class="text-end" id="totalKredit">0.00</th>
                                    <th>
                                        <button type="button" class="btn btn-sm btn-success" onclick="addRow()">
                                            <i class="bi bi-plus"></i> Tambah
                                        </button>
                                    </th>
                                </tr>
                                <tr class="table-warning">
                                    <th colspan="2">BALANCE</th>
                                    <th class="text-end" id="balance">0.00</th>
                                    <th class="text-end" id="balanceStatus">-</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Jurnal
                    </button>
                    <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let rowCounter = 1;

function addRow() {
    const tbody = document.querySelector('#detailTable tbody');
    const newRow = document.createElement('tr');
    
    const akunOptions = `<?= json_encode(array_map(function($akun) { 
        return ['value' => $akun['id'], 'text' => $akun['kode_perkiraan'] . ' - ' . $akun['nama_perkiraan']]; 
    }, $daftarAkun)) ?>`;
    
    newRow.innerHTML = `
        <td>
            <select class="form-select akun-select" name="detail[${rowCounter}][akun_id]" required>
                <option value="">Pilih Akun</option>
                ${akunOptions.map(opt => `<option value="${opt.value}">${opt.text}</option>`).join('')}
            </select>
        </td>
        <td>
            <input type="text" class="form-control" name="detail[${rowCounter}][keterangan]" placeholder="Keterangan detail...">
        </td>
        <td>
            <input type="number" class="form-control debet-input" name="detail[${rowCounter}][debet]" step="0.01" min="0" onchange="calculateBalance()">
        </td>
        <td>
            <input type="number" class="form-control kredit-input" name="detail[${rowCounter}][kredit]" step="0.01" min="0" onchange="calculateBalance()">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    rowCounter++;
}

function removeRow(button) {
    const row = button.closest('tr');
    const tbody = row.parentElement;
    
    if (tbody.children.length > 1) {
        row.remove();
        calculateBalance();
    }
}

function calculateBalance() {
    let totalDebet = 0;
    let totalKredit = 0;
    
    document.querySelectorAll('.debet-input').forEach(input => {
        totalDebet += parseFloat(input.value) || 0;
    });
    
    document.querySelectorAll('.kredit-input').forEach(input => {
        totalKredit += parseFloat(input.value) || 0;
    });
    
    const balance = totalDebet - totalKredit;
    
    document.getElementById('totalDebet').textContent = totalDebet.toFixed(2);
    document.getElementById('totalKredit').textContent = totalKredit.toFixed(2);
    document.getElementById('balance').textContent = Math.abs(balance).toFixed(2);
    
    const balanceElement = document.getElementById('balanceStatus');
    if (Math.abs(balance) < 0.01) {
        balanceElement.textContent = 'BALANCE';
        balanceElement.className = 'text-success fw-bold';
    } else {
        balanceElement.textContent = 'NOT BALANCE';
        balanceElement.className = 'text-danger fw-bold';
    }
}

function resetForm() {
    document.getElementById('journalForm').reset();
    // Keep only first row
    const tbody = document.querySelector('#detailTable tbody');
    while (tbody.children.length > 1) {
        tbody.removeChild(tbody.lastChild);
    }
    calculateBalance();
}

// Auto-calculate when page loads
document.addEventListener('DOMContentLoaded', function() {
    calculateBalance();
});

// Form validation
document.getElementById('journalForm').addEventListener('submit', function(e) {
    const balance = parseFloat(document.getElementById('balance').textContent);
    if (balance > 0.01) {
        e.preventDefault();
        alert('Jurnal tidak balance! Total debet harus sama dengan total kredit.');
        return false;
    }
});
</script>
