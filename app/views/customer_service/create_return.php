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
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="cs-create-return">Buat Return Request</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('customer_service/returns') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Return
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('customer_service') ?>">Customer Service</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('customer_service/returns') ?>">Return</a></li>
        <li class="breadcrumb-item active">Buat</li>
    </ol>
</nav>

<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5>Form Return Request</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('customer_service/createReturn') ?>">
            <div class="mb-3">
                <label for="order_id" class="form-label">Pilih Order *</label>
                <select class="form-select" id="order_id" name="order_id" required>
                    <option value="">Pilih order</option>
                    <?php foreach ($orders as $order): ?>
                        <option value="<?= $order['id'] ?>">
                            <?= htmlspecialchars($order['no_faktur']) ?> - <?= htmlspecialchars($order['customer_name']) ?> (<?= htmlspecialchars(formatDate($order['tanggal_penjualan'], 'd M Y')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Hanya order yang sudah ada yang dapat dipilih untuk return</div>
            </div>

            <div class="mb-3">
                <label for="alasan_return" class="form-label">Alasan Return *</label>
                <textarea class="form-control" id="alasan_return" name="alasan_return" rows="4" placeholder="Jelaskan alasan return dengan detail..." required></textarea>
            </div>

            <div class="alert alert-info">
                <h6>Proses Return:</h6>
                <ol>
                    <li>Return request akan dibuat dengan status "Pending"</li>
                    <li>Tim customer service akan memverifikasi request</li>
                    <li>Jika disetujui, proses refund atau pengembalian barang akan dilakukan</li>
                    <li>Pelanggan akan mendapatkan notifikasi status return</li>
                </ol>
            </div>

            <div class="alert alert-warning">
                <h6>Kebijakan Return:</h6>
                <ul>
                    <li>Return dapat dilakukan maksimal 7 hari setelah pembelian</li>
                    <li>Barang harus dalam kondisi baik dan belum digunakan</li>
                    <li>Bukti pembelian asli diperlukan</li>
                    <li>Biaya pengiriman return ditanggung pembeli (kecuali kesalahan kami)</li>
                </ul>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Buat Return Request</button>
                <a href="<?= base_url('customer_service/returns') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
