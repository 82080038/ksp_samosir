<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';
require_once __DIR__ . '/../../../app/helpers/FormatHelper.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="inventory">Manajemen Inventaris</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('inventory/items') ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-box-seam"></i> Items
            </a>
            <a href="<?= base_url('inventory/warehouses') ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-building"></i> Warehouses
            </a>
            <a href="<?= base_url('inventory/suppliers') ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-truck"></i> Suppliers
            </a>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#refreshModal">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<?php if (true): ?>
<!-- Development Mode Notice -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-gear me-2"></i>
    <strong>Mode Development:</strong> Semua fitur dapat diakses tanpa autentikasi.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Flash Messages -->
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Total Items</h5>
                        <span class="h2 font-weight-bold mb-0"><?= formatAngka($stats['total_items'] ?? 0) ?></span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-box-seam fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Warehouses</h5>
                        <span class="h2 font-weight-bold mb-0"><?= formatAngka($stats['total_warehouses'] ?? 0) ?></span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-building fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Suppliers</h5>
                        <span class="h2 font-weight-bold mb-0"><?= formatAngka($stats['total_suppliers'] ?? 0) ?></span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-truck fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Low Stock Items</h5>
                        <span class="h2 font-weight-bold mb-0 <?= ($stats['low_stock_items'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                            <?= formatAngka($stats['low_stock_items'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle fa-2x text-warning"></i>
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
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('inventory/addStockMovement') ?>" class="btn btn-success w-100">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add Stock Movement
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('inventory/stockMovements') ?>" class="btn btn-info w-100">
                            <i class="bi bi-arrow-left-right me-2"></i>
                            View Movements
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('inventory/items') ?>" class="btn btn-primary w-100">
                            <i class="bi bi-box-seam me-2"></i>
                            Manage Items
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-warning w-100" onclick="generateStockReport()">
                            <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                            Stock Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Alert -->
<?php if (!empty($lowStockItems)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Low Stock Alert (<?= count($lowStockItems) ?> items)
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Min Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockItems as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($item['kode_item']) ?></strong></td>
                                <td><?= htmlspecialchars($item['nama_item']) ?></td>
                                <td><?= htmlspecialchars($item['nama_kategori'] ?? '-') ?></td>
                                <td class="text-danger fw-bold"><?= formatAngka($item['stok_tersedia']) ?></td>
                                <td><?= formatAngka($item['stok_minimum']) ?></td>
                                <td>
                                    <span class="badge bg-danger">
                                        Low Stock
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Movements -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Stock Movements</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Item</th>
                                <th>Warehouse</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentMovements)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No recent movements found
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($recentMovements as $movement): ?>
                                <tr>
                                    <td><?= formatDate($movement['tanggal_transaksi']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($movement['kode_item']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($movement['nama_item']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($movement['nama_gudang'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $movement['tipe_transaksi'] === 'in' ? 'success' : 'danger' ?>">
                                            <?= $movement['tipe_transaksi'] === 'in' ? 'Stock In' : 'Stock Out' ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold">
                                        <?= formatAngka($movement['jumlah']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($movement['username'] ?? '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateStockReport() {
    window.open('<?= base_url('inventory/exportStockReport') ?>', '_blank');
}
</script>
