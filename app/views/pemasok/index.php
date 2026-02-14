<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Dashboard Procurement</h2>
        <p class="text-muted">Kelola pemasok, purchase order, dan faktur supplier</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['total_suppliers'] ?></h5>
                        <p class="card-text">Total Pemasok Aktif</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-truck fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $stats['pos_this_month'] ?></h5>
                        <p class="card-text">PO Bulan Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0">Rp <?= formatCurrency($stats['pending_invoice_value']) ?></h5>
                        <p class="card-text">Invoice Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['pending_pos'] ?></h5>
                        <p class="card-text">PO Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
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
                        <a href="<?= base_url('pemasok/suppliers') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-users"></i> Kelola Pemasok
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('pemasok/createPO') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-plus"></i> Buat Purchase Order
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('pemasok/purchaseOrders') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-list"></i> Lihat PO
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('pemasok/invoices') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-file-invoice-dollar"></i> Kelola Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Purchase Orders -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>PO Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_pos)): ?>
                    <p class="text-muted">Belum ada purchase order.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_pos as $po): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">PO-<?= htmlspecialchars($po['id']) ?></h6>
                                    <small class="text-muted">
                                        <span class="badge bg-<?= $po['status'] === 'completed' ? 'success' : ($po['status'] === 'pending' ? 'warning' : 'info') ?>">
                                            <?= htmlspecialchars($po['status']) ?>
                                        </span>
                                    </small>
                                </div>
                                <p class="mb-1">Supplier: <?= htmlspecialchars($po['supplier_name']) ?></p>
                                <small class="text-muted">
                                    Total: Rp <?= formatCurrency($po['total_nilai']) ?> |
                                    Dibuat: <?= htmlspecialchars(formatDate($po['created_at'], 'd M Y')) ?>
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
                <h5>Invoice Pending</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pending_invoices)): ?>
                    <p class="text-muted">Tidak ada invoice pending.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pending_invoices as $invoice): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">INV-<?= htmlspecialchars($invoice['nomor_invoice']) ?></h6>
                                    <small class="text-muted">
                                        <span class="badge bg-warning">Belum Lunas</span>
                                    </small>
                                </div>
                                <p class="mb-1">Supplier: <?= htmlspecialchars($invoice['supplier_name']) ?></p>
                                <small class="text-muted">
                                    Nilai: Rp <?= formatCurrency($invoice['total_nilai']) ?> |
                                    Jatuh Tempo: <?= htmlspecialchars(formatDate($invoice['tanggal_jatuh_tempo'], 'd M Y')) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
