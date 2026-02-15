-- Inventory Management Tables for KSP Samosir
-- Inventory Items
CREATE TABLE IF NOT EXISTS inventory_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_item VARCHAR(20) NOT NULL UNIQUE,
    nama_item VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    kategori_id INT NULL,
    gudang_id INT NULL,
    satuan VARCHAR(20) DEFAULT 'pcs',
    harga_beli DECIMAL(15,2) DEFAULT 0,
    harga_jual DECIMAL(15,2) DEFAULT 0,
    stok_minimum INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori_produk(id) ON DELETE SET NULL,
    FOREIGN KEY (gudang_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_kode (kode_item),
    INDEX idx_kategori (kategori_id),
    INDEX idx_gudang (gudang_id),
    INDEX idx_active (is_active)
);

-- Warehouses
CREATE TABLE IF NOT EXISTS warehouses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_gudang VARCHAR(10) NOT NULL UNIQUE,
    nama_gudang VARCHAR(100) NOT NULL,
    alamat TEXT,
    kapasitas INT NULL, -- dalam satuan kubik atau jumlah item
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_kode (kode_gudang),
    INDEX idx_active (is_active)
);

-- Inventory Transactions (Stock Movements)
CREATE TABLE IF NOT EXISTS inventory_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    gudang_id INT NULL,
    tipe_transaksi ENUM('in', 'out', 'adjustment', 'transfer') NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(15,2) DEFAULT 0,
    total_nilai DECIMAL(15,2) DEFAULT 0,
    keterangan TEXT,
    referensi_type VARCHAR(50) NULL, -- 'sale', 'purchase', 'adjustment', 'transfer'
    referensi_id INT NULL,
    tanggal_transaksi DATE NOT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE RESTRICT,
    FOREIGN KEY (gudang_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_item (item_id),
    INDEX idx_gudang (gudang_id),
    INDEX idx_tipe (tipe_transaksi),
    INDEX idx_tanggal (tanggal_transaksi),
    INDEX idx_referensi (referensi_type, referensi_id)
);

-- Purchase Orders (for inventory replenishment)
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_po VARCHAR(50) NOT NULL UNIQUE,
    supplier_id INT NULL,
    tanggal_po DATE NOT NULL,
    tanggal_kirim DATE NULL,
    status_po ENUM('draft', 'approved', 'ordered', 'received', 'cancelled') DEFAULT 'draft',
    total_harga DECIMAL(15,2) DEFAULT 0,
    diskon DECIMAL(15,2) DEFAULT 0,
    pajak DECIMAL(15,2) DEFAULT 0,
    grand_total DECIMAL(15,2) DEFAULT 0,
    keterangan TEXT,
    approved_by INT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES vendors(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_nomor (nomor_po),
    INDEX idx_supplier (supplier_id),
    INDEX idx_status (status_po),
    INDEX idx_tanggal (tanggal_po)
);

-- Purchase Order Items
CREATE TABLE IF NOT EXISTS purchase_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    item_id INT NOT NULL,
    jumlah_pesan INT NOT NULL,
    jumlah_diterima INT DEFAULT 0,
    harga_satuan DECIMAL(15,2) NOT NULL,
    total_harga DECIMAL(15,2) NOT NULL,
    diskon DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE RESTRICT,
    INDEX idx_po (po_id),
    INDEX idx_item (item_id)
);

-- Stock Adjustments (for periodic inventory adjustments)
CREATE TABLE IF NOT EXISTS stock_adjustments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    gudang_id INT NULL,
    stok_sistem INT NOT NULL, -- stock according to system
    stok_fisik INT NOT NULL, -- physical count
    selisih INT NOT NULL, -- difference
    alasan TEXT,
    approved_by INT NULL,
    tanggal_adjustment DATE NOT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE RESTRICT,
    FOREIGN KEY (gudang_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_item (item_id),
    INDEX idx_gudang (gudang_id),
    INDEX idx_tanggal (tanggal_adjustment)
);

-- Insert Default Warehouse
INSERT INTO warehouses (kode_gudang, nama_gudang, alamat, kapasitas) VALUES
('MAIN', 'Gudang Utama', 'Jl. Raya Samosir No. 123', 10000),
('SECOND', 'Gudang Sekunder', 'Jl. Pendidikan No. 45', 5000);

-- Insert Sample Inventory Items
INSERT INTO inventory_items (kode_item, nama_item, deskripsi, kategori_id, gudang_id, satuan, harga_beli, harga_jual, stok_minimum) VALUES
('BRG001', 'Beras Premium 5kg', 'Beras premium kualitas terbaik', 1, 1, 'pcs', 50000, 55000, 10),
('BRG002', 'Gula Pasir 1kg', 'Gula pasir putih kristal', 2, 1, 'pcs', 12000, 13500, 20),
('BRG003', 'Minyak Goreng 2L', 'Minyak goreng kemasan 2 liter', 3, 1, 'pcs', 25000, 28000, 15);
