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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="invoice-record">Catat Invoice Supplier</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('invoice/supplierInvoices') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Supplier
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('invoice') ?>">Invoice</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('invoice/supplierInvoices') ?>">Supplier</a></li>
        <li class="breadcrumb-item active">Catat</li>
    </ol>
</nav>

<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5>Form Pencatatan Invoice Supplier</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('invoice/recordSupplierInvoice') ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="po_id" class="form-label">Purchase Order *</label>
                        <select class="form-select" id="po_id" name="po_id" required>
                            <option value="">Pilih Purchase Order</option>
                            <?php foreach ($pos as $po): ?>
                                <option value="<?= $po['id'] ?>" data-supplier="<?= htmlspecialchars($po['nama_perusahaan']) ?>" data-total="<?= $po['total_nilai'] ?>">
                                    PO-<?= $po['id'] ?> - <?= htmlspecialchars($po['nama_perusahaan']) ?> (Rp <?= formatCurrency($po['total_nilai']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hanya PO yang sudah completed yang dapat dicatat invoice-nya</div>
                    </div>

                    <div class="mb-3">
                        <label for="nomor_invoice" class="form-label">Nomor Invoice *</label>
                        <input type="text" class="form-control" id="nomor_invoice" name="nomor_invoice" placeholder="INV-2024-001" required>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_invoice" class="form-label">Tanggal Invoice *</label>
                        <input type="date" class="form-control" id="tanggal_invoice" name="tanggal_invoice" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tanggal_jatuh_tempo" class="form-label">Tanggal Jatuh Tempo</label>
                        <input type="date" class="form-control" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo">
                    </div>

                    <div class="mb-3">
                        <label for="syarat_pembayaran" class="form-label">Syarat Pembayaran</label>
                        <select class="form-select" id="syarat_pembayaran" name="syarat_pembayaran">
                            <option value="30_hari">30 Hari</option>
                            <option value="60_hari">60 Hari</option>
                            <option value="90_hari">90 Hari</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>

                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Informasi PO Terpilih:</h6>
                            <p id="supplierInfo">Pilih PO untuk melihat detail supplier</p>
                            <p id="totalInfo">Total PO: -</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <h6>Catatan:</h6>
                <p>Invoice supplier akan dicatat dengan status "Belum Lunas". Status akan diupdate ketika pembayaran dilakukan.</p>
                <p>Nomor invoice harus unik dan sesuai dengan dokumen asli dari supplier.</p>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Catat Invoice</button>
                <a href="<?= base_url('invoice/supplierInvoices') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('po_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const supplier = selectedOption.getAttribute('data-supplier');
    const total = selectedOption.getAttribute('data-total');
    
    if (supplier && total) {
        document.getElementById('supplierInfo').textContent = 'Supplier: ' + supplier;
        document.getElementById('totalInfo').textContent = 'Total PO: Rp ' + parseFloat(total).toLocaleString('id-ID');
    } else {
        document.getElementById('supplierInfo').textContent = 'Pilih PO untuk melihat detail supplier';
        document.getElementById('totalInfo').textContent = 'Total PO: -';
    }
});

// Auto-set due date based on payment terms
document.getElementById('syarat_pembayaran').addEventListener('change', function() {
    const terms = this.value;
    const invoiceDate = document.getElementById('tanggal_invoice').value;
    
    if (invoiceDate) {
        const date = new Date(invoiceDate);
        
        switch (terms) {
            case '30_hari':
                date.setDate(date.getDate() + 30);
                break;
            case '60_hari':
                date.setDate(date.getDate() + 60);
                break;
            case '90_hari':
                date.setDate(date.getDate() + 90);
                break;
            case 'cash':
                date.setDate(date.getDate() + 7); // 7 days for cash
                break;
        }
        
        document.getElementById('tanggal_jatuh_tempo').value = date.toISOString().split('T')[0];
    }
});

// Update due date when invoice date changes
document.getElementById('tanggal_invoice').addEventListener('change', function() {
    document.getElementById('syarat_pembayaran').dispatchEvent(new Event('change'));
});
</script>
