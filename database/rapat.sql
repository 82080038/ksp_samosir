-- Rapat Module Tables
-- KSP Samosir - Modul Manajemen Rapat

CREATE TABLE rapat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(255) NOT NULL,
    jenis_rapat ENUM('rapat_anggota', 'rapat_pengurus', 'rapat_pengawas') NOT NULL,
    tanggal DATE NOT NULL,
    waktu TIME,
    lokasi TEXT,
    agenda TEXT,
    status ENUM('terjadwal', 'berlangsung', 'selesai', 'dibatalkan') DEFAULT 'terjadwal',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE rapat_peserta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rapat_id INT NOT NULL,
    user_id INT NOT NULL,
    status_kehadiran ENUM('hadir', 'tidak_hadir', 'izin') DEFAULT 'tidak_hadir',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rapat_id) REFERENCES rapat(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE rapat_notulen (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rapat_id INT NOT NULL,
    isi_notulen TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rapat_id) REFERENCES rapat(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE rapat_keputusan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rapat_id INT NOT NULL,
    keputusan TEXT NOT NULL,
    status_pelaksanaan ENUM('belum_dilaksanakan', 'dalam_proses', 'selesai') DEFAULT 'belum_dilaksanakan',
    pic INT,
    deadline DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rapat_id) REFERENCES rapat(id) ON DELETE CASCADE,
    FOREIGN KEY (pic) REFERENCES users(id)
);

-- Indexes
CREATE INDEX idx_rapat_tanggal ON rapat(tanggal);
CREATE INDEX idx_rapat_status ON rapat(status);
CREATE INDEX idx_rapat_jenis ON rapat(jenis_rapat);
CREATE INDEX idx_rapat_peserta_rapat ON rapat_peserta(rapat_id);
CREATE INDEX idx_rapat_peserta_user ON rapat_peserta(user_id);
CREATE INDEX idx_rapat_notulen_rapat ON rapat_notulen(rapat_id);
CREATE INDEX idx_rapat_keputusan_rapat ON rapat_keputusan(rapat_id);
