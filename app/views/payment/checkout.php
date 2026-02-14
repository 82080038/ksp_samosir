<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - KSP Samosir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-your-client-key"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Pembayaran Order #<?= htmlspecialchars($order['id']) ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Detail Order</h5>
                                <p><strong>Pelanggan:</strong> <?= htmlspecialchars($order['nama_pelanggan']) ?></p>
                                <p><strong>Total:</strong> Rp <?= formatCurrency($order['total_bayar']) ?></p>
                                <p><strong>Tanggal:</strong> <?= htmlspecialchars(formatDate($order['created_at'], 'd M Y H:i')) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Produk</h5>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($items as $item): ?>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span><?= htmlspecialchars($item['nama_produk']) ?> (<?= $item['jumlah'] ?>x)</span>
                                            <strong>Rp <?= formatCurrency($item['harga_satuan'] * $item['jumlah']) ?></strong>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <hr>

                        <div class="alert alert-info">
                            <h6>Pilih Metode Pembayaran</h6>
                            <p>Klik tombol "Bayar Sekarang" untuk melanjutkan ke halaman pembayaran aman.</p>
                        </div>

                        <div class="d-grid">
                            <button id="pay-button" class="btn btn-success btn-lg">Bayar Sekarang</button>
                        </div>

                        <div class="mt-3 text-center">
                            <a href="<?= base_url('penjualan/detail/' . $order['id']) ?>" class="btn btn-outline-secondary">Kembali ke Detail Order</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const payButton = document.getElementById('pay-button');
        const snapToken = '<?= $snap_token ?>';

        payButton.addEventListener('click', function() {
            snap.pay(snapToken, {
                onSuccess: function(result) {
                    console.log('Payment success:', result);
                    window.location.href = '<?= base_url('payment/success?order_id=' . $order['id']) ?>';
                },
                onPending: function(result) {
                    console.log('Payment pending:', result);
                    window.location.href = '<?= base_url('payment/pending?order_id=' . $order['id']) ?>';
                },
                onError: function(result) {
                    console.log('Payment error:', result);
                    window.location.href = '<?= base_url('payment/error?order_id=' . $order['id']) ?>';
                },
                onClose: function() {
                    console.log('Payment popup closed');
                    // Redirect back to order detail
                    window.location.href = '<?= base_url('penjualan/detail/' . $order['id']) ?>';
                }
            });
        });
    </script>
</body>
</html>
