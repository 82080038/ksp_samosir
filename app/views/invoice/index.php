<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Dashboard Invoice</h2>
        <p class="text-muted">Kelola invoice customer dan supplier</p>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['total_customer_invoices'] ?></h5>
                        <p class="card-text">Invoice Customer</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['paid_customer_invoices'] ?></h5>
                        <p class="card-text">Customer Lunas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['unpaid_customer_invoices'] ?></h5>
                        <p class="card-text">Customer Belum Bayar</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['overdue_customer'] ?></h5>
                        <p class="card-text">Customer Overdue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Supplier Invoice Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['total_supplier_invoices'] ?></h5>
                        <p class="card-text">Invoice Supplier</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-truck fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['paid_supplier_invoices'] ?></h5>
                        <p class="card-text">Supplier Lunas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['unpaid_supplier_invoices'] ?></h5>
                        <p class="card-text">Supplier Belum Bayar</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-hourglass-half fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['overdue_supplier'] ?></h5>
                        <p class="card-text">Supplier Overdue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="<?= base_url('invoice/customerInvoices') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-users"></i> Invoice Customer
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('invoice/supplierInvoices') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-truck"></i> Invoice Supplier
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('invoice/recordSupplierInvoice') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-plus"></i> Catat Invoice Supplier
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-warning btn-block" onclick="generateBulkInvoices()">
                            <i class="fas fa-file-export"></i> Generate Bulk Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Invoices -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Invoice Customer Terbaru</h5>
            </div>
            <div class="card-body">
                <?php
                $recent_customer = fetchAll("SELECT ci.*, u.full_name as customer_name FROM customer_invoices ci LEFT JOIN users u ON ci.customer_id = u.id ORDER BY ci.created_at DESC LIMIT 5");
                if (empty($recent_customer)): ?>
                    <p class="text-muted">Belum ada invoice customer.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_customer as $inv): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($inv['invoice_number']) ?></h6>
                                    <small class="text-muted">
                                        <span class="badge bg-<?= $inv['status'] === 'paid' ? 'success' : 'warning' ?>">
                                            <?= htmlspecialchars($inv['status']) ?>
                                        </span>
                                    </small>
                                </div>
                                <p class="mb-1">Customer: <?= htmlspecialchars($inv['customer_name']) ?></p>
                                <small class="text-muted">
                                    Rp <?= formatCurrency($inv['total_amount']) ?> |
                                    Dibuat: <?= htmlspecialchars(formatDate($inv['created_at'], 'd M Y')) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Invoice Supplier Terbaru</h5>
            </div>
            <div class="card-body">
                <?php
                $recent_supplier = fetchAll("SELECT si.*, s.nama_perusahaan as supplier_name FROM supplier_invoices si LEFT JOIN suppliers s ON si.supplier_id = s.id ORDER BY si.created_at DESC LIMIT 5");
                if (empty($recent_supplier)): ?>
                    <p class="text-muted">Belum ada invoice supplier.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_supplier as $inv): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($inv['nomor_invoice']) ?></h6>
                                    <small class="text-muted">
                                        <span class="badge bg-<?= $inv['status_pembayaran'] === 'lunas' ? 'success' : 'warning' ?>">
                                            <?= htmlspecialchars($inv['status_pembayaran']) ?>
                                        </span>
                                    </small>
                                </div>
                                <p class="mb-1">Supplier: <?= htmlspecialchars($inv['supplier_name']) ?></p>
                                <small class="text-muted">
                                    Rp <?= formatCurrency($inv['total_nilai']) ?> |
                                    Dibuat: <?= htmlspecialchars(formatDate($inv['created_at'], 'd M Y')) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function generateBulkInvoices() {
    alert('Fitur generate bulk invoice akan diimplementasikan untuk generate invoice dari multiple orders');
}
</script>
