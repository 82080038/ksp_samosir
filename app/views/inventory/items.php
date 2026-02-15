<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="inventory-items">Daftar Item</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2" style="color: black;">Inventory Items</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('inventory/addItem') ?>" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add Item
            </a>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportItems()">
            <i class="bi bi-download"></i> Export
        </button>
    </div>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <label for="search" class="form-label">Search Items</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Search by item name or code..." 
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="<?= base_url('inventory/items') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Items Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Items List (<?= formatAngka($total) ?> items)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Code</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Warehouse</th>
                        <th>Current Stock</th>
                        <th>Min Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="py-4">
                                <i class="bi bi-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No items found</h5>
                                <p class="text-muted mb-3">
                                    <?= $search ? 'No items match your search criteria.' : 'Start by adding your first inventory item.' ?>
                                </p>
                                <a href="<?= base_url('inventory/addItem') ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Add First Item
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): 
                            $stockStatus = 'normal';
                            if (($item['stok_tersedia'] ?? 0) <= 0) {
                                $stockStatus = 'out';
                            } elseif (($item['stok_tersedia'] ?? 0) <= ($item['stok_minimum'] ?? 0)) {
                                $stockStatus = 'low';
                            }
                        ?>
                        <tr>
                            <td>
                                <strong class="text-primary"><?= htmlspecialchars($item['kode_item']) ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($item['nama_item']) ?></strong>
                                    <?php if ($item['deskripsi']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars(substr($item['deskripsi'], 0, 50)) ?>...</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($item['nama_kategori'] ?? 'Uncategorized') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?= htmlspecialchars($item['nama_gudang'] ?? 'No Warehouse') ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold 
                                    <?php 
                                    if ($stockStatus === 'out') echo 'text-danger';
                                    elseif ($stockStatus === 'low') echo 'text-warning';
                                    else echo 'text-success';
                                    ?>">
                                    <?= formatAngka($item['stok_tersedia'] ?? 0) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?= formatAngka($item['stok_minimum'] ?? 0) ?>
                            </td>
                            <td>
                                <?php if ($stockStatus === 'out'): ?>
                                    <span class="badge bg-danger">Out of Stock</span>
                                <?php elseif ($stockStatus === 'low'): ?>
                                    <span class="badge bg-warning">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge bg-success">In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewItem(<?= $item['id'] ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editItem(<?= $item['id'] ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(<?= $item['id'] ?>, '<?= htmlspecialchars($item['nama_item']) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Items pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Sebelumnya</a>
                    </li>
                <?php endif; ?>
                
                <?php 
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                if ($startPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>">1</a>
                    </li>
                    <?php if ($startPage > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>"><?= $totalPages ?></a>
                    </li>
                <?php endif; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Selanjutnya</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete item <strong id="deleteItemName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewItem(id) {
    window.location.href = `<?= base_url('inventory/viewItem') ?>?id=${id}`;
}

function editItem(id) {
    window.location.href = `<?= base_url('inventory/editItem') ?>?id=${id}`;
}

function deleteItem(id, name) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('confirmDeleteBtn').onclick = () => {
        window.location.href = `<?= base_url('inventory/deleteItem') ?>?id=${id}`;
    };
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function exportItems() {
    const search = document.getElementById('search').value;
    window.open(`<?= base_url('inventory/exportItems') ?>?search=${encodeURIComponent(search)}`, '_blank');
}
</script>
