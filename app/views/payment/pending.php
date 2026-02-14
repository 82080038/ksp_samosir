<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pending - KSP Samosir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="text-warning mb-3">
                            <i class="fas fa-clock fa-4x"></i>
                        </div>
                        <h4 class="card-title text-warning">Pembayaran Pending</h4>
                        <p class="card-text">
                            Pembayaran Anda sedang diproses. Kami akan mengirimkan notifikasi setelah pembayaran dikonfirmasi.
                        </p>
                        <?php if ($order_id): ?>
                            <p><strong>Order ID: #<?= htmlspecialchars($order_id) ?></strong></p>
                        <?php endif; ?>

                        <div class="mt-4">
                            <a href="<?= base_url('penjualan/detail/' . $order_id) ?>" class="btn btn-primary me-2">Lihat Detail Order</a>
                            <a href="<?= base_url('penjualan') ?>" class="btn btn-outline-secondary">Kembali ke Penjualan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
