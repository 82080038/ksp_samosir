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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="inventory-movement">Tambah Pergerakan Stok</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2" style="color: black;">Add Stock Movement</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('inventory/stockMovements') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Movements
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Stock Movement Form</h5>
    </div>
    <div class="card-body">
        <form method="POST" id="movementForm">
            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="tanggal_transaksi" class="form-label">Transaction Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal_transaksi" name="tanggal_transaksi" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="tipe_transaksi" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="tipe_transaksi" name="tipe_transaksi" required>
                        <option value="">Select Type</option>
                        <option value="in">Stock In (+)</option>
                        <option value="out">Stock Out (-)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="item_id" class="form-label">Item <span class="text-danger">*</span></label>
                    <select class="form-select" id="item_id" name="item_id" required>
                        <option value="">Select Item</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item['id'] ?>" data-current-stock="<?= $item['stok_tersedia'] ?? 0 ?>">
                                <?= htmlspecialchars($item['kode_item']) ?> - <?= htmlspecialchars($item['nama_item']) ?>
                                (Stock: <?= number_format($item['stok_tersedia'] ?? 0) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="gudang_id" class="form-label">Warehouse</label>
                    <select class="form-select" id="gudang_id" name="gudang_id">
                        <option value="">Select Warehouse</option>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <option value="<?= $warehouse['id'] ?>">
                                <?= htmlspecialchars($warehouse['nama_gudang']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="jumlah" class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" required>
                    <div class="form-text" id="stockWarning" style="display: none;"></div>
                </div>
                <div class="col-md-4">
                    <label for="harga_satuan" class="form-label">Unit Price</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" id="harga_satuan" name="harga_satuan" step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-12">
                    <label for="keterangan" class="form-label">Description</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Enter transaction description..."></textarea>
                </div>
            </div>
            
            <!-- Transaction Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Transaction Summary</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Total Value:</strong><br>
                                    <span id="totalValue" class="text-primary fw-bold">Rp 0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Stock After:</strong><br>
                                    <span id="stockAfter" class="fw-bold">-</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Transaction Type:</strong><br>
                                    <span id="transactionType" class="fw-bold">-</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Status:</strong><br>
                                    <span id="transactionStatus" class="badge bg-secondary">Pending</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Movement
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
// Form variables
let selectedItemStock = 0;

// Update form when item is selected
document.getElementById('item_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    selectedItemStock = parseInt(selectedOption.getAttribute('data-current-stock') || 0);
    updateSummary();
});

// Update form when quantity or price changes
document.getElementById('jumlah').addEventListener('input', updateSummary);
document.getElementById('harga_satuan').addEventListener('input', updateSummary);
document.getElementById('tipe_transaksi').addEventListener('change', function() {
    updateSummary();
    validateStock();
});

function updateSummary() {
    const tipeTransaksi = document.getElementById('tipe_transaksi').value;
    const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
    const hargaSatuan = parseFloat(document.getElementById('harga_satuan').value) || 0;
    const totalValue = jumlah * hargaSatuan;
    
    // Update total value
    document.getElementById('totalValue').textContent = 'Rp ' + totalValue.toLocaleString('id-ID', {minimumFractionDigits: 2});
    
    // Update stock after
    let stockAfter = selectedItemStock;
    if (tipeTransaksi === 'in') {
        stockAfter += jumlah;
    } else if (tipeTransaksi === 'out') {
        stockAfter -= jumlah;
    }
    
    document.getElementById('stockAfter').textContent = stockAfter.toLocaleString();
    document.getElementById('stockAfter').className = stockAfter < 0 ? 'text-danger fw-bold' : 'text-success fw-bold';
    
    // Update transaction type
    const typeText = tipeTransaksi === 'in' ? 'Stock In (+)' : tipeTransaksi === 'out' ? 'Stock Out (-)' : '-';
    document.getElementById('transactionType').textContent = typeText;
    
    // Update status
    const statusElement = document.getElementById('transactionStatus');
    if (tipeTransaksi && jumlah > 0) {
        if (tipeTransaksi === 'out' && stockAfter < 0) {
            statusElement.textContent = 'Insufficient Stock';
            statusElement.className = 'badge bg-danger';
        } else {
            statusElement.textContent = 'Ready to Save';
            statusElement.className = 'badge bg-success';
        }
    } else {
        statusElement.textContent = 'Incomplete';
        statusElement.className = 'badge bg-warning';
    }
}

function validateStock() {
    const tipeTransaksi = document.getElementById('tipe_transaksi').value;
    const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
    const warningElement = document.getElementById('stockWarning');
    
    if (tipeTransaksi === 'out' && jumlah > selectedItemStock) {
        warningElement.textContent = `Warning: Requested quantity (${jumlah}) exceeds available stock (${selectedItemStock})`;
        warningElement.style.display = 'block';
        warningElement.className = 'form-text text-danger fw-bold';
    } else {
        warningElement.style.display = 'none';
    }
}

function resetForm() {
    document.getElementById('movementForm').reset();
    selectedItemStock = 0;
    updateSummary();
    
    // Reset warnings
    document.getElementById('stockWarning').style.display = 'none';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
});

// Form validation
document.getElementById('movementForm').addEventListener('submit', function(e) {
    const tipeTransaksi = document.getElementById('tipe_transaksi').value;
    const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
    const itemId = document.getElementById('item_id').value;
    
    if (!tipeTransaksi || !jumlah || !itemId) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return false;
    }
    
    if (tipeTransaksi === 'out' && (selectedItemStock - jumlah) < 0) {
        e.preventDefault();
        if (!confirm(`Warning: This transaction will result in negative stock (${selectedItemStock - jumlah}). Continue?`)) {
            return false;
        }
    }
});
</script>
