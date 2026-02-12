-- Koperasi Activity Management System
-- Based on UU No. 25 Tahun 1992 and Koperasi Polres Samosir Documentation

-- Koperasi Activity Types Table
CREATE TABLE IF NOT EXISTS koperasi_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    activity_code VARCHAR(50) UNIQUE NOT NULL,
    activity_name VARCHAR(255) NOT NULL,
    activity_type ENUM('simpanan', 'pinjaman', 'jual_beli', 'investasi', 'jasa_lain') NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert standard koperasi activities based on UU 25/1992
INSERT INTO koperasi_activities (activity_code, activity_name, activity_type, description) VALUES
-- Simpanan Activities (Pasal 23 UU 25/1992)
('SIM_POKOK', 'Simpanan Pokok', 'simpanan', 'Simpanan wajib sekali saat pendaftaran anggota'),
('SIM_WAJIB', 'Simpanan Wajib', 'simpanan', 'Simpanan berkala sesuai ketentuan koperasi'),
('SIM_SUKARELA', 'Simpanan Sukarela', 'simpanan', 'Simpanan sukarela dari anggota'),
('SIM_BERJANGKA', 'Simpanan Berjangka', 'simpanan', 'Simpanan dengan periode tertentu'),

-- Pinjaman Activities (Pasal 24 UU 25/1992)
('PINJ_ANGGOTA', 'Pinjaman Anggota', 'pinjaman', 'Pinjaman kepada anggota untuk kebutuhan produktif'),
('PINJ_INVESTASI', 'Pinjaman Investasi', 'pinjaman', 'Pinjaman untuk investasi usaha anggota'),
('PINJ_KONSUMTIF', 'Pinjaman Konsumtif', 'pinjaman', 'Pinjaman untuk kebutuhan konsumtif anggota'),
('PINJ_KEPEMILIK', 'Pinjaman Kepemilik', 'pinjaman', 'Pinjaman kepada kepemilik koperasi'),

-- Jual Beli Activities (Pasal 25 UU 25/1992)
('JUAL_PRODUK_ANGGOTA', 'Jual Produk Anggota', 'jual_beli', 'Penjualan produk dari anggota'),
('JUAL_PRODUK_KOPERASI', 'Jual Produk Koperasi', 'jual_beli', 'Penjualan produk hasil usaha koperasi'),
('BELI_KEBUTUHAN', 'Beli Kebutuhan Operasional', 'jual_beli', 'Pembelian barang untuk operasional koperasi'),
('JUAL_EKSTERNAL', 'Jual ke Non-Anggota', 'jual_beli', 'Penjualan produk ke masyarakat umum'),

-- Investment Activities
('INVEST_MODAL_POKOK', 'Investasi Modal Pokok', 'investasi', 'Investasi modal pokok dari anggota'),
('INVEST_MODAL_PENYERTA', 'Investasi Modal Penyerta', 'investasi', 'Investasi modal penyertaan anggota'),
('INVEST_REKSA', 'Investasi Dana Cadangan', 'investasi', 'Investasi dana cadangan resiko'),

-- Jasa Lain Activities
('JASA_ADMINISTRASI', 'Jasa Administrasi', 'jasa_lain', 'Biaya administrasi dan jasa pengelolaan'),
('JASA_PENYIMPANAN', 'Jasa Penyimpanan', 'jasa_lain', 'Jasa penyimpanan barang/produk'),
('JASA_TRANSPORTASI', 'Jasa Transportasi', 'jasa_lain', 'Jasa pengiriman dan distribusi'),
('JASA_KONSULTASI', 'Jasa Konsultasi', 'jasa_lain', 'Jasa konsultasi bisnis dan manajemen')
ON DUPLICATE KEY UPDATE activity_name=VALUES(activity_name), description=VALUES(description);

-- Koperasi Transaction Log
CREATE TABLE IF NOT EXISTS koperasi_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_date DATE NOT NULL,
    activity_code VARCHAR(50) NOT NULL,
    member_id INT,
    transaction_type ENUM('debit', 'credit') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT,
    reference_number VARCHAR(100),
    status ENUM('draft', 'posted', 'cancelled') DEFAULT 'draft',
    created_by INT,
    approved_by INT,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_code) REFERENCES koperasi_activities(activity_code),
    FOREIGN KEY (member_id) REFERENCES anggota(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- SHU Calculation Components (Pasal 26 UU 25/1992)
CREATE TABLE IF NOT EXISTS shu_components (
    id INT PRIMARY KEY AUTO_INCREMENT,
    component_code VARCHAR(50) UNIQUE NOT NULL,
    component_name VARCHAR(255) NOT NULL,
    component_type ENUM('jasa_modal', 'jasa_usaha', 'pendidikan_sosial', 'honorarium', 'lainnya') NOT NULL,
    percentage_weight DECIMAL(5,2) DEFAULT 0.00,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert SHU components based on UU 25/1992 and common practices
INSERT INTO shu_components (component_code, component_name, component_type, percentage_weight, description) VALUES
('JASA_MODAL_ANGGOTA', 'Jasa Modal Anggota', 'jasa_modal', 40.00, 'Bagian SHU untuk anggota berdasarkan simpanan'),
('JASA_MODAL_PENGURUS', 'Jasa Modal Pengurus', 'jasa_modal', 5.00, 'Bagian SHU untuk modal pengurus koperasi'),
('JASA_USHA_ANGGOTA', 'Jasa Usaha Anggota', 'jasa_usaha', 35.00, 'Bagian SHU dari transaksi dengan anggota'),
('JASA_USHA_KOPERASI', 'Jasa Usaha Koperasi', 'jasa_usaha', 15.00, 'Bagian SHU dari usaha langsung koperasi'),
('PENDIDIKAN', 'Pendidikan Sosial', 'pendidikan_sosial', 3.00, 'Dana pendidikan anggota dan masyarakat'),
('HONORARIUM_PENGURUS', 'Honorarium Pengurus', 'honorarium', 2.00, 'Honorarium untuk pengurus aktif'),
('DANA_CADANGAN', 'Dana Cadangan', 'lainnya', 5.00, 'Dana cadangan resiko dan pengembangan')
ON DUPLICATE KEY UPDATE component_name=VALUES(component_name), percentage_weight=VALUES(percentage_weight), description=VALUES(description);

-- SHU Calculation Period
CREATE TABLE IF NOT EXISTS shu_periods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_shu DECIMAL(15,2) DEFAULT 0.00,
    calculation_method ENUM('standard', 'custom') DEFAULT 'standard',
    status ENUM('draft', 'calculated', 'approved', 'distributed') DEFAULT 'draft',
    calculated_by INT,
    calculated_at TIMESTAMP NULL,
    approved_by INT,
    approved_at TIMESTAMP NULL,
    distributed_by INT,
    distributed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (calculated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (distributed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- SHU Distribution to Members
CREATE TABLE IF NOT EXISTS shu_member_distribution (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shu_period_id INT NOT NULL,
    member_id INT NOT NULL,
    component_code VARCHAR(50) NOT NULL,
    base_amount DECIMAL(15,2) DEFAULT 0.00,
    calculated_shu DECIMAL(15,2) DEFAULT 0.00,
    percentage_share DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('calculated', 'approved', 'paid', 'reserved') DEFAULT 'calculated',
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    paid_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shu_period_id) REFERENCES shu_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES anggota(id) ON DELETE CASCADE,
    FOREIGN KEY (component_code) REFERENCES shu_components(component_code) ON DELETE RESTRICT
);

-- Koperasi Meeting Management
CREATE TABLE IF NOT EXISTS koperasi_meetings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    meeting_type ENUM('rapat_anggota', 'rapat_pengurus', 'rapat_pengawas', 'rapat_kombinasi') NOT NULL,
    meeting_title VARCHAR(255) NOT NULL,
    meeting_date DATETIME NOT NULL,
    meeting_location VARCHAR(255),
    agenda TEXT,
    description TEXT,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Meeting Attendance
CREATE TABLE IF NOT EXISTS meeting_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT NOT NULL,
    member_id INT NOT NULL,
    attendance_status ENUM('hadir', 'izin', 'tanpa_keterangan', 'tidak_hadir') DEFAULT 'tidak_hadir',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES koperasi_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES anggota(id) ON DELETE CASCADE
);

-- Meeting Decisions
CREATE TABLE IF NOT EXISTS meeting_decisions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT NOT NULL,
    decision_number INT NOT NULL,
    decision_title VARCHAR(255) NOT NULL,
    decision_content TEXT NOT NULL,
    decision_type ENUM('kebijakan', 'program', 'anggaran', 'personalia', 'lainnya') DEFAULT 'kebijakan',
    implementation_status ENUM('belum_direalisasi', 'sedang_direalisasi', 'selesai', 'dibatalkan') DEFAULT 'belum_direalisasi',
    responsible_person INT,
    target_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES koperasi_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (responsible_person) REFERENCES anggota(id) ON DELETE SET NULL
);

-- Koperasi Supervision (Pengawas)
CREATE TABLE IF NOT EXISTS supervision_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    supervision_date DATE NOT NULL,
    supervisor_id INT NOT NULL,
    supervised_person_id INT NOT NULL,
    supervision_type ENUM('audit', 'monitoring', 'evaluation', 'investigation') NOT NULL,
    findings TEXT,
    recommendations TEXT,
    action_required ENUM('none', 'minor', 'major', 'critical') DEFAULT 'none',
    follow_up_date DATE,
    status ENUM('open', 'in_progress', 'closed', 'escalated') DEFAULT 'open',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supervisor_id) REFERENCES anggota(id) ON DELETE CASCADE,
    FOREIGN KEY (supervised_person_id) REFERENCES anggota(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Sanctions and Warnings
CREATE TABLE IF NOT EXISTS koperasi_sanctions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sanction_date DATE NOT NULL,
    member_id INT NOT NULL,
    sanction_type ENUM('teguran_lisan', 'teguran_tertulis', 'suspensi', 'pemberhentian', 'pemecatan') NOT NULL,
    sanction_reason TEXT NOT NULL,
    sanction_period_days INT DEFAULT 0,
    issued_by INT NOT NULL,
    approved_by INT,
    status ENUM('issued', 'serving', 'completed', 'appealed', 'cancelled') DEFAULT 'issued',
    start_date DATE,
    end_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Views for reporting
CREATE OR REPLACE VIEW v_koperasi_activity_summary AS
SELECT 
    ka.activity_type,
    COUNT(*) as total_transactions,
    SUM(CASE WHEN kt.transaction_type = 'debit' THEN kt.amount ELSE 0 END) as total_debit,
    SUM(CASE WHEN kt.transaction_type = 'credit' THEN kt.amount ELSE 0 END) as total_credit,
    SUM(kt.amount) as net_amount,
    COUNT(DISTINCT kt.member_id) as unique_members
FROM koperasi_activities ka
LEFT JOIN koperasi_transactions kt ON ka.activity_code = kt.activity_code
WHERE ka.is_active = 1
GROUP BY ka.activity_type;

CREATE OR REPLACE VIEW v_shu_calculation_summary AS
SELECT 
    sp.period_start,
    sp.period_end,
    sp.total_shu,
    COUNT(smd.id) as total_distributions,
    SUM(smd.calculated_shu) as distributed_amount,
    sp.status
FROM shu_periods sp
LEFT JOIN shu_member_distribution smd ON sp.id = smd.shu_period_id
GROUP BY sp.id, sp.period_start, sp.period_end, sp.total_shu, sp.status;

-- Stored Procedures for SHU Calculation
DELIMITER //
CREATE PROCEDURE calculate_shu_period(IN period_start DATE, IN period_end DATE, IN calculation_method VARCHAR(20))
BEGIN
    DECLARE total_shu_amount DECIMAL(15,2) DEFAULT 0;
    DECLARE period_id INT;
    
    -- Create SHU period record
    INSERT INTO shu_periods (period_start, period_end, calculation_method, status, calculated_by, calculated_at)
    VALUES (period_start, period_end, calculation_method, 'calculated', @user_id, NOW());
    
    SET period_id = LAST_INSERT_ID();
    
    -- Calculate total SHU from koperasi activities
    SELECT SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END)
    INTO total_shu_amount
    FROM koperasi_transactions 
    WHERE transaction_date BETWEEN period_start AND period_end 
    AND status = 'posted';
    
    -- Update total SHU
    UPDATE shu_periods SET total_shu = total_shu_amount WHERE id = period_id;
    
    -- Calculate SHU for each member based on components
    -- This is a simplified calculation - actual implementation would be more complex
    INSERT INTO shu_member_distribution (shu_period_id, member_id, component_code, base_amount, calculated_shu, percentage_share, status)
    SELECT 
        period_id,
        kt.member_id,
        sc.component_code,
        COALESCE(SUM(CASE WHEN kt.transaction_type = 'credit' THEN kt.amount ELSE 0 END), 0) as base_amount,
        (COALESCE(SUM(CASE WHEN kt.transaction_type = 'credit' THEN kt.amount ELSE 0 END), 0) * sc.percentage_weight / 100) as calculated_shu,
        sc.percentage_weight as percentage_share,
        'calculated'
    FROM koperasi_transactions kt
    CROSS JOIN shu_components sc
    WHERE kt.transaction_date BETWEEN period_start AND period_end 
    AND kt.status = 'posted'
    AND kt.member_id IS NOT NULL
    AND sc.is_active = 1
    GROUP BY kt.member_id, sc.component_code, sc.percentage_weight;
    
    SELECT period_id as result;
END//
DELIMITER ;

-- Triggers for audit logging
DELIMITER //
CREATE TRIGGER log_koperasi_transaction
AFTER INSERT ON koperasi_transactions
FOR EACH ROW
BEGIN
    INSERT INTO logs (user_id, action, table_name, record_id, old_values, new_values, created_at)
    VALUES (
        NEW.created_by,
        'CREATE',
        'koperasi_transactions',
        NEW.id,
        NULL,
        JSON_OBJECT(
            'activity_code', NEW.activity_code,
            'amount', NEW.amount,
            'transaction_type', NEW.transaction_type,
            'member_id', NEW.member_id
        ),
        NOW()
    );
END//
DELIMITER ;
