<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Proses Return</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('customer_service') ?>">Customer Service</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('customer_service/returns') ?>">Return</a></li>
                <li class="breadcrumb-item active">Proses</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Detail Return #<?= htmlspecialchars($return['id']) ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Order:</strong> <?= htmlspecialchars($return['no_faktur']) ?></p>
                        <p><strong>Pelanggan:</strong> <?= htmlspecialchars($return['customer_name']) ?></p>
                        <p><strong>Status Saat Ini:</strong>
                            <span class="badge bg-<?= $return['status'] === 'processed' ? 'success' : 'warning' ?>">
                                <?= htmlspecialchars($return['status']) ?>
                            </span>
                        </p>
                        <p><strong>Dibuat:</strong> <?= htmlspecialchars(formatDate($return['created_at'], 'd M Y H:i')) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Alasan Return:</strong></p>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($return['alasan_return'])) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($return['status'] === 'pending'): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Proses Return</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= base_url('customer_service/processReturn/' . $return['id']) ?>">
                        <div class="mb-3">
                            <label for="keputusan" class="form-label">Keputusan *</label>
                            <select class="form-select" id="keputusan" name="keputusan" required>
                                <option value="">Pilih keputusan</option>
                                <option value="approved">Setujui Return</option>
                                <option value="rejected">Tolak Return</option>
                                <option value="partial">Return Sebagian</option>
                            </select>
                        </div>

                        <div class="mb-3" id="refundSection" style="display: none;">
                            <label for="jumlah_refund" class="form-label">Jumlah Refund (Rp)</label>
                            <input type="number" step="0.01" class="form-control" id="jumlah_refund" name="jumlah_refund" placeholder="0.00">
                            <div class="form-text">Kosongkan jika tidak ada refund</div>
                        </div>

                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Catatan tambahan..."></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Proses Return</button>
                            <a href="<?= base_url('customer_service/returns') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Status Proses</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h6>Return telah diproses</h6>
                        <p><strong>Keputusan:</strong> <?= htmlspecialchars($return['keputusan']) ?></p>
                        <?php if ($return['jumlah_refund'] > 0): ?>
                            <p><strong>Jumlah Refund:</strong> Rp <?= formatCurrency($return['jumlah_refund']) ?></p>
                        <?php endif; ?>
                        <p><strong>Diproses pada:</strong> <?= htmlspecialchars(formatDate($return['resolved_at'], 'd M Y H:i')) ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Informasi Order</h5>
            </div>
            <div class="card-body">
                <?php
                // Get order details
                $order_details = fetchAll("SELECT dp.*, pr.nama_produk FROM detail_penjualan dp LEFT JOIN produk pr ON dp.produk_id = pr.id WHERE dp.penjualan_id = ?", [$return['order_id']], 'i');
                ?>
                <p><strong>Produk yang dibeli:</strong></p>
                <ul class="list-group list-group-flush">
                    <?php foreach ($order_details as $detail): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= htmlspecialchars($detail['nama_produk']) ?> (<?= $detail['jumlah'] ?>x)</span>
                            <strong>Rp <?= formatCurrency($detail['harga_satuan'] * $detail['jumlah']) ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Panduan Proses</h5>
            </div>
            <div class="card-body">
                <h6>Ketika Menyetujui:</h6>
                <ul>
                    <li>Verifikasi kondisi barang</li>
                    <li>Proses refund jika diperlukan</li>
                    <li>Update stok inventaris</li>
                    <li>Kirim notifikasi ke pelanggan</li>
                </ul>

                <h6>Ketika Menolak:</h6>
                <ul>
                    <li>Jelaskan alasan penolakan</li>
                    <li>Berikan alternatif solusi</li>
                    <li>Kirim notifikasi ke pelanggan</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('keputusan').addEventListener('change', function() {
    const refundSection = document.getElementById('refundSection');
    if (this.value === 'approved' || this.value === 'partial') {
        refundSection.style.display = 'block';
    } else {
        refundSection.style.display = 'none';
        document.getElementById('jumlah_refund').value = '';
    }
});
</script>
