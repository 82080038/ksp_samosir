<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Kelola Invoice Supplier</h2>
    <div class="btn-group">
        <a href="<?= base_url('pemasok') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Dashboard</a>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No Invoice</th>
                        <th>Supplier</th>
                        <th>PO</th>
                        <th>Tanggal Invoice</th>
                        <th>Total Nilai</th>
                        <th>Jatuh Tempo</th>
                        <th>Status Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($invoice['nomor_invoice']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($invoice['supplier_name']) ?></strong>
                            </td>
                            <td>
                                PO-<?= htmlspecialchars($invoice['po_id']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($invoice['nomor_po']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($invoice['tanggal_invoice'], 'd M Y')) ?>
                            </td>
                            <td>
                                <strong class="text-danger">Rp <?= formatCurrency($invoice['total_nilai']) ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars(formatDate($invoice['tanggal_jatuh_tempo'], 'd M Y')) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $invoice['status_pembayaran'] === 'lunas' ? 'success' : 'warning' ?>">
                                    <?= htmlspecialchars($invoice['status_pembayaran']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewInvoice(<?= $invoice['id'] ?>)">
                                        Detail
                                    </button>
                                    <?php if ($invoice['status_pembayaran'] === 'belum_lunas'): ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="payInvoice(<?= $invoice['id'] ?>)">
                                            Bayar
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary" onclick="printInvoice(<?= $invoice['id'] ?>)">
                                        Print
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Pagination" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Summary Card -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6>Total Invoice</h6>
                <h4 class="text-primary">
                    <?php
                    $total_invoices = count($invoices);
                    echo $total_invoices;
                    ?>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6>Total Nilai Invoice</h6>
                <h4 class="text-danger">
                    Rp <?php
                    $total_value = array_sum(array_column($invoices, 'total_nilai'));
                    echo formatCurrency($total_value);
                    ?>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6>Invoice Belum Lunas</h6>
                <h4 class="text-warning">
                    <?php
                    $unpaid = count(array_filter($invoices, function($inv) { return $inv['status_pembayaran'] === 'belum_lunas'; }));
                    echo $unpaid;
                    ?>
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Modal for invoice details -->
<div class="modal fade" id="invoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="invoiceContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewInvoice(id) {
    // Placeholder for AJAX detail loading
    alert('Detail invoice akan dimuat dengan AJAX');
}

function payInvoice(id) {
    if (confirm('Tandai invoice ini sebagai lunas?')) {
        // Placeholder for payment processing
        alert('Invoice ditandai sebagai lunas');
    }
}

function printInvoice(id) {
    // Placeholder for print functionality
    alert('Fitur print invoice akan diimplementasikan');
}
</script>
