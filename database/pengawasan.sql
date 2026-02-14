-- Pengawasan Module Tables
-- KSP Samosir - Modul Pengawasan & Sanksi

CREATE TABLE sanksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jenis_sanksi ENUM('teguran_lisan', 'teguran_tertulis', 'pemberhentian_sementara') NOT NULL,
    deskripsi TEXT,
    dasar_hukum TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pelanggaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    jenis_pelanggaran VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    tanggal_pelanggaran DATE NOT NULL,
    sanksi_id INT,
    status ENUM('investigasi', 'diputuskan', 'dieksekusi', 'ditutup') DEFAULT 'investigasi',
    decided_by INT,
    decided_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (sanksi_id) REFERENCES sanksi(id),
    FOREIGN KEY (decided_by) REFERENCES users(id)
);

CREATE TABLE laporan_pengawas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(255) NOT NULL,
    periode_mulai DATE,
    periode_akhir DATE,
    isi_laporan TEXT,
    rekomendasi TEXT,
    status ENUM('draft', 'final', 'disetujui') DEFAULT 'draft',
    created_by INT,
    approved_by INT,
    approved_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Indexes
CREATE INDEX idx_pelanggaran_user ON pelanggaran(user_id);
CREATE INDEX idx_pelanggaran_status ON pelanggaran(status);
CREATE INDEX idx_pelanggaran_tanggal ON pelanggaran(tanggal_pelanggaran);
CREATE INDEX idx_laporan_pengawas_status ON laporan_pengawas(status);
CREATE INDEX idx_laporan_pengawas_created_by ON laporan_pengawas(created_by);
