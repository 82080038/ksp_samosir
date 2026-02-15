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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="payment-checkout">Checkout Pembayaran</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

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
