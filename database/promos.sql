-- Promos Table for Marketing Features
-- KSP Samosir - Modul Marketing & Promosi

CREATE TABLE promos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_promo VARCHAR(20) UNIQUE NOT NULL,
    jenis_diskon ENUM('persen', 'nominal') NOT NULL,
    nilai_diskon DECIMAL(10,2) NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_akhir DATE NOT NULL,
    deskripsi TEXT,
    status ENUM('aktif', 'nonaktif', 'kadaluarsa') DEFAULT 'aktif',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Indexes
CREATE INDEX idx_promos_kode ON promos(kode_promo);
CREATE INDEX idx_promos_status ON promos(status);
CREATE INDEX idx_promos_tanggal ON promos(tanggal_mulai, tanggal_akhir);
