-- Integration Alamat Database from alamat_db
-- KSP Samosir - Address Management System

-- Create address tables in ksp_samosir database
CREATE TABLE IF NOT EXISTS ref_provinces (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ref_regencies (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    province_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (province_id) REFERENCES ref_provinces(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ref_districts (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    regency_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (regency_id) REFERENCES ref_regencies(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ref_villages (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    district_id INT NOT NULL,
    kodepos VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (district_id) REFERENCES ref_districts(id) ON DELETE CASCADE
);

-- Import data from alamat_db
INSERT INTO ref_provinces (id, name)
SELECT id, name FROM alamat_db.provinces;

INSERT INTO ref_regencies (id, name, province_id)
SELECT id, name, province_id FROM alamat_db.regencies;

INSERT INTO ref_districts (id, name, regency_id)
SELECT id, name, regency_id FROM alamat_db.districts;

INSERT INTO ref_villages (id, name, district_id, kodepos)
SELECT id, name, district_id, kodepos FROM alamat_db.villages;

-- Add address columns to anggota table if not exists
ALTER TABLE anggota 
ADD COLUMN IF NOT EXISTS province_id INT NULL,
ADD COLUMN IF NOT EXISTS regency_id INT NULL,
ADD COLUMN IF NOT EXISTS district_id INT NULL,
ADD COLUMN IF NOT EXISTS village_id INT NULL;

-- Add foreign key constraints for anggota addresses
ALTER TABLE anggota 
ADD CONSTRAINT fk_anggota_province FOREIGN KEY (province_id) REFERENCES ref_provinces(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_anggota_regency FOREIGN KEY (regency_id) REFERENCES ref_regencies(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_anggota_district FOREIGN KEY (district_id) REFERENCES ref_districts(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_anggota_village FOREIGN KEY (village_id) REFERENCES ref_villages(id) ON DELETE SET NULL;

-- Create address helper view
CREATE OR REPLACE VIEW v_anggota_alamat AS
SELECT 
    a.id,
    a.nama_lengkap,
    a.alamat,
    p.name as province_name,
    r.name as regency_name,
    d.name as district_name,
    v.name as village_name,
    v.kodepos,
    CONCAT(a.alamat, ', ', v.name, ', ', d.name, ', ', r.name, ', ', p.name) as alamat_lengkap
FROM anggota a
LEFT JOIN ref_provinces p ON a.province_id = p.id
LEFT JOIN ref_regencies r ON a.regency_id = r.id
LEFT JOIN ref_districts d ON a.district_id = d.id
LEFT JOIN ref_villages v ON a.village_id = v.id;

-- Create address lookup functions
DELIMITER //
CREATE PROCEDURE get_full_address(IN province_id INT, IN regency_id INT, IN district_id INT, IN village_id INT)
BEGIN
    SELECT 
        CONCAT(
            IFNULL(v.name, ''), ', ',
            IFNULL(d.name, ''), ', ',
            IFNULL(r.name, ''), ', ',
            IFNULL(p.name, '')
        ) as alamat_lengkap,
        v.kodepos
    FROM ref_provinces p
    LEFT JOIN ref_regencies r ON r.province_id = p.id AND r.id = regency_id
    LEFT JOIN ref_districts d ON d.regency_id = r.id AND d.id = district_id
    LEFT JOIN ref_villages v ON v.district_id = d.id AND v.id = village_id
    WHERE p.id = province_id
    LIMIT 1;
END//

CREATE PROCEDURE get_address_options(IN parent_type VARCHAR(20), IN parent_id INT)
BEGIN
    CASE parent_type
        WHEN 'province' THEN
            SELECT id, name FROM ref_provinces ORDER BY name;
        WHEN 'regency' THEN
            SELECT id, name FROM ref_regencies WHERE province_id = parent_id ORDER BY name;
        WHEN 'district' THEN
            SELECT id, name FROM ref_districts WHERE regency_id = parent_id ORDER BY name;
        WHEN 'village' THEN
            SELECT id, name, kodepos FROM ref_villages WHERE district_id = parent_id ORDER BY name;
        ELSE
            SELECT '' as id, '' as name;
    END CASE;
END//
DELIMITER ;

-- Create address validation function
DELIMITER //
CREATE FUNCTION validate_address_id(table_name VARCHAR(20), id_value INT) 
RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE result BOOLEAN DEFAULT FALSE;
    
    CASE table_name
        WHEN 'province' THEN
            SET result = (SELECT COUNT(*) > 0 FROM ref_provinces WHERE id = id_value);
        WHEN 'regency' THEN
            SET result = (SELECT COUNT(*) > 0 FROM ref_regencies WHERE id = id_value);
        WHEN 'district' THEN
            SET result = (SELECT COUNT(*) > 0 FROM ref_districts WHERE id = id_value);
        WHEN 'village' THEN
            SET result = (SELECT COUNT(*) > 0 FROM ref_villages WHERE id = id_value);
        ELSE
            SET result = FALSE;
    END CASE;
    
    RETURN result;
END//
DELIMITER ;

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_regencies_province ON ref_regencies(province_id);
CREATE INDEX IF NOT EXISTS idx_districts_regency ON ref_districts(regency_id);
CREATE INDEX IF NOT EXISTS idx_villages_district ON ref_villages(district_id);
CREATE INDEX IF NOT EXISTS idx_anggota_province ON anggota(province_id);
CREATE INDEX IF NOT EXISTS idx_anggota_regency ON anggota(regency_id);
CREATE INDEX IF NOT EXISTS idx_anggota_district ON anggota(district_id);
CREATE INDEX IF NOT EXISTS idx_anggota_village ON anggota(village_id);

-- Add address statistics
CREATE TABLE IF NOT EXISTS address_stats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    total_provinces INT DEFAULT 0,
    total_regencies INT DEFAULT 0,
    total_districts INT DEFAULT 0,
    total_villages INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO address_stats (total_provinces, total_regencies, total_districts, total_villages)
SELECT 
    (SELECT COUNT(*) FROM ref_provinces),
    (SELECT COUNT(*) FROM ref_regencies),
    (SELECT COUNT(*) FROM ref_districts),
    (SELECT COUNT(*) FROM ref_villages)
ON DUPLICATE KEY UPDATE 
    total_provinces = VALUES(total_provinces),
    total_regencies = VALUES(total_regencies),
    total_districts = VALUES(total_districts),
    total_villages = VALUES(total_villages),
    last_updated = NOW();
