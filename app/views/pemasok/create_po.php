<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Buat Purchase Order</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('pemasok') ?>">Procurement</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('pemasok/purchaseOrders') ?>">PO</a></li>
                <li class="breadcrumb-item active">Buat PO</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5>Form Purchase Order</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('pemasok/createPO') ?>" id="poForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier *</label>
                        <select class="form-select" id="supplier_id" name="supplier_id" required>
                            <option value="">Pilih supplier</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= $supplier['id'] ?>"><?= htmlspecialchars($supplier['nama_perusahaan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_po" class="form-label">Tanggal PO *</label>
                        <input type="date" class="form-control" id="tanggal_po" name="tanggal_po" required>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_pengiriman" class="form-label">Tanggal Pengiriman</label>
                        <input type="date" class="form-control" id="tanggal_pengiriman" name="tanggal_pengiriman">
                    </div>

                    <div class="mb-3">
                        <label for="syarat_pembayaran" class="form-label">Syarat Pembayaran</label>
                        <select class="form-select" id="syarat_pembayaran" name="syarat_pembayaran">
                            <option value="cash">Cash</option>
                            <option value="30_hari">30 Hari</option>
                            <option value="60_hari">60 Hari</option>
                            <option value="90_hari">90 Hari</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6>Detail Produk</h6>
                </div>
                <div class="card-body">
                    <div id="productsContainer">
                        <div class="product-row mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Produk *</label>
                                    <select class="form-select product-select" name="products[0][produk_id]" required>
                                        <option value="">Pilih produk</option>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?= $product['id'] ?>" data-price="0">
                                                <?= htmlspecialchars($product['nama_produk']) ?> (<?= htmlspecialchars($product['kode_produk']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Qty *</label>
                                    <input type="number" class="form-control qty-input" name="products[0][qty]" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Harga Satuan *</label>
                                    <input type="number" step="0.01" class="form-control price-input" name="products[0][harga_satuan]" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Subtotal</label>
                                    <input type="number" step="0.01" class="form-control subtotal-input" readonly>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-sm remove-product" style="display: none;">X</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm" id="addProductBtn">Tambah Produk</button>

                    <div class="mt-3">
                        <strong>Total PO: Rp <span id="totalAmount">0.00</span></strong>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Buat PO</button>
                <a href="<?= base_url('pemasok/purchaseOrders') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
let productIndex = 1;

document.getElementById('addProductBtn').addEventListener('click', function() {
    addProductRow();
});

function addProductRow() {
    const container = document.getElementById('productsContainer');
    const newRow = document.createElement('div');
    newRow.className = 'product-row mb-3';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Produk *</label>
                <select class="form-select product-select" name="products[${productIndex}][produk_id]" required>
                    <option value="">Pilih produk</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= $product['id'] ?>" data-price="0">
                            <?= htmlspecialchars($product['nama_produk']) ?> (<?= htmlspecialchars($product['kode_produk']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Qty *</label>
                <input type="number" class="form-control qty-input" name="products[${productIndex}][qty]" min="1" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Harga Satuan *</label>
                <input type="number" step="0.01" class="form-control price-input" name="products[${productIndex}][harga_satuan]" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Subtotal</label>
                <input type="number" step="0.01" class="form-control subtotal-input" readonly>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm remove-product">X</button>
            </div>
        </div>
    `;
    container.appendChild(newRow);
    productIndex++;

    // Show remove buttons if more than one row
    document.querySelectorAll('.remove-product').forEach(btn => btn.style.display = 'block');
    updateEventListeners();
}

function updateEventListeners() {
    document.querySelectorAll('.product-select, .qty-input, .price-input').forEach(input => {
        input.addEventListener('input', updateSubtotal);
    });

    document.querySelectorAll('.remove-product').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.product-row').remove();
            updateTotal();
            // Hide remove buttons if only one row left
            if (document.querySelectorAll('.product-row').length === 1) {
                document.querySelector('.remove-product').style.display = 'none';
            }
        });
    });
}

function updateSubtotal() {
    document.querySelectorAll('.product-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const subtotal = qty * price;
        row.querySelector('.subtotal-input').value = subtotal.toFixed(2);
    });
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal-input').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('totalAmount').textContent = total.toLocaleString('id-ID');
}

// Initialize event listeners
updateEventListeners();
updateSubtotal();
</script>
