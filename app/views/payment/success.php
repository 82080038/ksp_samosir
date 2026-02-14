<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - KSP Samosir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="text-success mb-3">
                            <i class="fas fa-check-circle fa-4x"></i>
                        </div>
                        <h4 class="card-title text-success">Pembayaran Berhasil!</h4>
                        <p class="card-text">
                            Terima kasih atas pembayaran Anda. Order Anda telah berhasil diproses.
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

    <script>
        // Auto redirect after 5 seconds
        setTimeout(function() {
            window.location.href = '<?= base_url('penjualan/detail/' . $order_id) ?>';
        }, 5000);
    </script>
</body>
</html>
