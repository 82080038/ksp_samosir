<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Buat Penjualan Baru</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('penjualan') ?>">Penjualan</a></li>
                <li class="breadcrumb-item active">Buat</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= base_url('penjualan/store') ?>" id="penjualanForm">
            <div class="row">
                <div class="col-md-6">
                    <!-- Customer Selection -->
                    <div class="mb-3">
                        <label for="pelanggan_id" class="form-label">Pelanggan *</label>
                        <select class="form-select" id="pelanggan_id" name="pelanggan_id" required>
                            <option value="">Pilih pelanggan</option>
                            <?php foreach ($pelanggan as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_pelanggan']) ?> (<?= htmlspecialchars($p['kode_pelanggan']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-3">
                        <label for="metode_pembayaran" class="form-label">Metode Pembayaran *</label>
                        <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="debit">Kartu Debit</option>
                            <option value="kredit">Kartu Kredit</option>
                        </select>
                    </div>

                    <!-- Promo Selection -->
                    <div class="mb-3">
                        <label for="promo_id" class="form-label">Promo (Opsional)</label>
                        <select class="form-select" id="promo_id" name="promo_id">
                            <option value="">Tidak ada promo</option>
                            <?php foreach ($promos as $promo): ?>
                                <option value="<?= $promo['id'] ?>" data-type="<?= $promo['jenis_diskon'] ?>" data-value="<?= $promo['nilai_diskon'] ?>">
                                    <?= htmlspecialchars($promo['kode_promo']) ?> - 
                                    <?php if ($promo['jenis_diskon'] === 'persen'): ?>
                                        Diskon <?= $promo['nilai_diskon'] ?>%
                                    <?php else: ?>
                                        Diskon Rp <?= formatCurrency($promo['nilai_diskon']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- Shipping Information -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6>Pengiriman</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="shipping_needed" class="form-label">Perlu Pengiriman?</label>
                                <select class="form-select" id="shipping_needed">
                                    <option value="no">Tidak (Ambil di Toko)</option>
                                    <option value="yes">Ya (Dikirim)</option>
                                </select>
                            </div>

                            <div id="shippingOptions" style="display: none;">
                                <div class="mb-3">
                                    <label for="destination" class="form-label">Kota Tujuan</label>
                                    <select class="form-select" id="destination">
                                        <option value="">Pilih kota tujuan</option>
                                        <!-- Cities will be loaded via AJAX -->
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="courier" class="form-label">Kurir</label>
                                    <select class="form-select" id="courier">
                                        <option value="jne">JNE</option>
                                        <option value="pos">POS Indonesia</option>
                                        <option value="tiki">TIKI</option>
                                    </select>
                                </div>

                                <button type="button" class="btn btn-outline-primary btn-sm" id="calculateShipping">Hitung Ongkir</button>

                                <div id="shippingResults" class="mt-3" style="display: none;">
                                    <h6>Pilihan Pengiriman:</h6>
                                    <div id="shippingOptionsList"></div>
                                </div>
                            </div>
                        </div>
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
                                    <label class="form-label">Harga Satuan *</label>
                                    <input type="number" step="0.01" class="form-control price-input" name="produk[0][harga_jual]" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Subtotal</label>
                                    <input type="number" step="0.01" class="form-control subtotal-input" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm" id="addProductBtn">Tambah Produk</button>

                    <div class="mt-3">
                        <strong>Subtotal Produk: Rp <span id="subtotalAmount">0.00</span></strong><br>
                        <strong>Diskon Promo: Rp <span id="discountAmount">0.00</span></strong><br>
                        <strong>Ongkir: Rp <span id="shippingAmount">0.00</span></strong><br>
                        <strong><hr></strong>
                        <strong>Total Bayar: Rp <span id="totalAmount">0.00</span></strong>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Buat Penjualan</button>
                <a href="<?= base_url('penjualan') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
// Product management
let productIndex = 1;
let selectedShippingCost = 0;
let selectedPromoDiscount = 0;

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
                <label class="form-label">Harga Satuan *</label>
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
    let subtotal = 0;
    document.querySelectorAll('.subtotal-input').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });

    // Apply promo discount
    let discount = 0;
    if (selectedPromoDiscount > 0) {
        const promoSelect = document.getElementById('promo_id');
        const selectedOption = promoSelect.options[promoSelect.selectedIndex];
        const discountType = selectedOption.getAttribute('data-type');
        const discountValue = parseFloat(selectedOption.getAttribute('data-value')) || 0;

        if (discountType === 'persen') {
            discount = subtotal * (discountValue / 100);
        } else {
            discount = Math.min(discountValue, subtotal);
        }
    }

    const total = subtotal - discount + selectedShippingCost;

    document.getElementById('subtotalAmount').textContent = subtotal.toLocaleString('id-ID');
    document.getElementById('discountAmount').textContent = discount.toLocaleString('id-ID');
    document.getElementById('shippingAmount').textContent = selectedShippingCost.toLocaleString('id-ID');
    document.getElementById('totalAmount').textContent = total.toLocaleString('id-ID');
}

// Promo handling
document.getElementById('promo_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    selectedPromoDiscount = parseFloat(selectedOption.getAttribute('data-value')) || 0;
    updateTotal();
});

// Shipping handling
document.getElementById('shipping_needed').addEventListener('change', function() {
    const shippingOptions = document.getElementById('shippingOptions');
    if (this.value === 'yes') {
        shippingOptions.style.display = 'block';
        loadCities();
    } else {
        shippingOptions.style.display = 'none';
        selectedShippingCost = 0;
        updateTotal();
    }
});

function loadCities() {
    // Load cities via AJAX (placeholder - implement with actual RajaOngkir API)
    const destinationSelect = document.getElementById('destination');
    destinationSelect.innerHTML = '<option value="">Pilih kota tujuan</option>';
    
    // Add some sample cities
    const cities = [
        {id: 114, name: 'Bandung'},
        {id: 501, name: 'Yogyakarta'},
        {id: 151, name: 'Jakarta'},
        {id: 427, name: 'Surabaya'}
    ];
    
    cities.forEach(city => {
        const option = document.createElement('option');
        option.value = city.id;
        option.textContent = city.name;
        destinationSelect.appendChild(option);
    });
}

document.getElementById('calculateShipping').addEventListener('click', function() {
    const destination = document.getElementById('destination').value;
    const courier = document.getElementById('courier').value;
    
    if (!destination) {
        alert('Pilih kota tujuan terlebih dahulu');
        return;
    }

    // Calculate total weight (simplified - 1kg per product)
    const productRows = document.querySelectorAll('.product-row');
    let totalWeight = 0;
    productRows.forEach(row => {
        const qty = parseInt(row.querySelector('.qty-input').value) || 0;
        totalWeight += qty * 1000; // 1kg per product
    });

    // AJAX call to shipping API (placeholder)
    fetch(`<?= base_url('shipping/getShippingOptions') ?>?origin=501&destination=${destination}&weight=${totalWeight}&courier=${courier}`)
        .then(response => response.json())
        .then(data => {
            displayShippingOptions(data.shipping_options);
        })
        .catch(error => {
            console.error('Error calculating shipping:', error);
            alert('Gagal menghitung ongkir');
        });
});

function displayShippingOptions(options) {
    const resultsDiv = document.getElementById('shippingResults');
    const optionsList = document.getElementById('shippingOptionsList');
    
    optionsList.innerHTML = '';
    
    if (options && options.length > 0) {
        options[0].costs.forEach(cost => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'form-check';
            optionDiv.innerHTML = `
                <input class="form-check-input" type="radio" name="shipping_option" value="${cost.value}" data-service="${cost.service}" data-etd="${cost.etd}">
                <label class="form-check-label">
                    ${cost.service} - Rp ${cost.value.toLocaleString('id-ID')} (${cost.etd} hari)
                </label>
            `;
            optionsList.appendChild(optionDiv);
        });
        
        // Add event listeners for shipping options
        document.querySelectorAll('input[name="shipping_option"]').forEach(radio => {
            radio.addEventListener('change', function() {
                selectedShippingCost = parseInt(this.value) || 0;
                updateTotal();
            });
        });
        
        resultsDiv.style.display = 'block';
    } else {
        optionsList.innerHTML = '<p class="text-muted">Tidak ada opsi pengiriman tersedia</p>';
        resultsDiv.style.display = 'block';
    }
}

// Initialize
updateEventListeners();
updateTotal();
</script>
