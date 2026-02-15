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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="payment-pending">Pembayaran Pending</h1>
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
