<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Buat Penjualan Agen</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('penjualan') ?>">Penjualan</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('penjualan/agentSales') ?>">Agen</a></li>
                <li class="breadcrumb-item active">Buat</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5>Form Penjualan Agen</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('penjualan/storeAgentSale') ?>" id="agentSaleForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="agent_id" class="form-label">Agen *</label>
                        <select class="form-select" id="agent_id" name="agent_id" required>
                            <option value="">Pilih agen</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['id'] ?>" data-commission="<?= $agent['komisi_persen'] ?>">
                                    <?= htmlspecialchars($agent['nama']) ?> (<?= $agent['komisi_persen'] ?>%)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="pelanggan_nama" class="form-label">Nama Pelanggan *</label>
                        <input type="text" class="form-control" id="pelanggan_nama" name="pelanggan_nama" required>
                    </div>

                    <div class="mb-3">
                        <label for="pelanggan_telp" class="form-label">Telepon Pelanggan</label>
                        <input type="tel" class="form-control" id="pelanggan_telp" name="pelanggan_telp">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="pelanggan_alamat" class="form-label">Alamat Pelanggan</label>
                        <textarea class="form-control" id="pelanggan_alamat" name="pelanggan_alamat" rows="3"></textarea>
                    </div>

                    <div class="alert alert-info">
                        <h6>Informasi Komisi:</h6>
                        <p id="commissionInfo">Pilih agen untuk melihat persentase komisi</p>
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
                                <div class="col-md-5">
                                    <label class="form-label">Produk *</label>
                                    <select class="form-select product-select" name="produk[0][produk_id]" required>
                                        <option value="">Pilih produk</option>
                                        <?php foreach ($produk as $prod): ?>
                                            <option value="<?= $prod['id'] ?>" data-price="<?= $prod['harga_jual'] ?>" data-stock="<?= $prod['stok'] ?>">
                                                <?= htmlspecialchars($prod['nama_produk']) ?> (<?= htmlspecialchars($prod['kode_produk']) ?>) - Stok: <?= $prod['stok'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Qty *</label>
                                    <input type="number" class="form-control qty-input" name="produk[0][qty]" min="1" max="999" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Harga Jual *</label>
                                    <input type="number" step="0.01" class="form-control price-input" name="produk[0][harga_jual]" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Subtotal</label>
                                    <input type="number" step="0.01" class="form-control subtotal-input" readonly>
                                </div>
                                <div class="col-md-0 d-none">
                                    <button type="button" class="btn btn-danger btn-sm remove-product" style="display: none;">X</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm" id="addProductBtn">Tambah Produk</button>

                    <div class="mt-3">
                        <strong>Total Penjualan: Rp <span id="totalAmount">0.00</span></strong><br>
                        <strong>Komisi Agen: Rp <span id="commissionAmount">0.00</span></strong>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Buat Penjualan Agen</button>
                <a href="<?= base_url('penjualan/agentSales') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
let productIndex = 1;
let currentCommissionRate = 0;

document.getElementById('agent_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    currentCommissionRate = parseFloat(selectedOption.getAttribute('data-commission')) || 0;
    document.getElementById('commissionInfo').textContent = `Persentase komisi: ${currentCommissionRate}%`;
    updateTotal();
});

document.getElementById('addProductBtn').addEventListener('click', function() {
    addProductRow();
});

function addProductRow() {
    const container = document.getElementById('productsContainer');
    const newRow = document.createElement('div');
    newRow.className = 'product-row mb-3';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-5">
                <label class="form-label">Produk *</label>
                <select class="form-select product-select" name="produk[${productIndex}][produk_id]" required>
                    <option value="">Pilih produk</option>
                    <?php foreach ($produk as $prod): ?>
                        <option value="<?= $prod['id'] ?>" data-price="<?= $prod['harga_jual'] ?>" data-stock="<?= $prod['stok'] ?>">
                            <?= htmlspecialchars($prod['nama_produk']) ?> (<?= htmlspecialchars($prod['kode_produk']) ?>) - Stok: <?= $prod['stok'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Qty *</label>
                <input type="number" class="form-control qty-input" name="produk[${productIndex}][qty]" min="1" max="999" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Harga Jual *</label>
                <input type="number" step="0.01" class="form-control price-input" name="produk[${productIndex}][harga_jual]" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Subtotal</label>
                <input type="number" step="0.01" class="form-control subtotal-input" readonly>
            </div>
        </div>
    `;
    container.appendChild(newRow);
    productIndex++;

    updateEventListeners();
}

function updateEventListeners() {
    document.querySelectorAll('.product-select').forEach(select => {
        select.addEventListener('change', function() {
            const row = this.closest('.product-row');
            const selectedOption = this.options[this.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;

            const priceInput = row.querySelector('.price-input');
            priceInput.value = price.toFixed(2);

            const qtyInput = row.querySelector('.qty-input');
            qtyInput.max = stock;

            updateSubtotal(row);
        });
    });

    document.querySelectorAll('.qty-input, .price-input').forEach(input => {
        input.addEventListener('input', function() {
            updateSubtotal(this.closest('.product-row'));
        });
    });
}

function updateSubtotal(row) {
    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const subtotal = qty * price;
    row.querySelector('.subtotal-input').value = subtotal.toFixed(2);
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal-input').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('totalAmount').textContent = total.toLocaleString('id-ID');

    const commission = total * (currentCommissionRate / 100);
    document.getElementById('commissionAmount').textContent = commission.toLocaleString('id-ID');
}

// Initialize event listeners
updateEventListeners();
</script>
