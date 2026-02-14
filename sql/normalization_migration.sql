-- DATABASE NORMALIZATION - KSP Samosir
-- This script normalizes the database to 3NF while maintaining application functionality

-- ================================
-- 1. CREATE LOOKUP/REFERENCE TABLES
-- ================================

-- Status reference table
CREATE TABLE status_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(50) NOT NULL, -- 'member', 'employee', 'supplier', 'asset', etc.
    status_code VARCHAR(50) NOT NULL,
    status_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Category reference table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_type VARCHAR(50) NOT NULL, -- 'product', 'service', 'asset', 'expense', etc.
    category_code VARCHAR(50) NOT NULL,
    category_name VARCHAR(100) NOT NULL,
    parent_category_id INT,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_category_id) REFERENCES categories(id)
);

-- Departments table
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_code VARCHAR(20) UNIQUE NOT NULL,
    department_name VARCHAR(100) NOT NULL,
    description TEXT,
    manager_id INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Positions table
CREATE TABLE positions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    position_code VARCHAR(20) UNIQUE NOT NULL,
    position_name VARCHAR(100) NOT NULL,
    department_id INT,
    level INT DEFAULT 1,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- ================================
-- 2. ADDRESS & CONTACT NORMALIZATION
-- ================================

-- Addresses table (normalized)
CREATE TABLE addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    address_type ENUM('home', 'office', 'billing', 'shipping', 'other') DEFAULT 'home',
    street_address VARCHAR(255) NOT NULL,
    address_line_2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Indonesia',
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    is_primary TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contacts table (normalized)
CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contact_type ENUM('phone', 'mobile', 'email', 'fax', 'website') NOT NULL,
    contact_value VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Entity-Address relationship table
CREATE TABLE entity_addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entity_type ENUM('member', 'supplier', 'employee', 'customer', 'other') NOT NULL,
    entity_id INT NOT NULL,
    address_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE CASCADE
);

-- Entity-Contact relationship table
CREATE TABLE entity_contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entity_type ENUM('member', 'supplier', 'employee', 'customer', 'other') NOT NULL,
    entity_id INT NOT NULL,
    contact_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
);

-- ================================
-- 3. MEMBER DATA NORMALIZATION
-- ================================

-- Update anggota table to remove redundant address/contact fields
-- (We'll keep the current structure but add proper relationships)

-- Add relationship columns to anggota table
ALTER TABLE anggota
ADD COLUMN address_id INT,
ADD COLUMN primary_contact_id INT,
ADD FOREIGN KEY (address_id) REFERENCES addresses(id),
ADD FOREIGN KEY (primary_contact_id) REFERENCES contacts(id);

-- Update employees table to use normalized address/contact
ALTER TABLE employees
ADD COLUMN address_id INT,
ADD COLUMN primary_contact_id INT,
ADD COLUMN department_id INT,
ADD COLUMN position_id INT,
ADD FOREIGN KEY (address_id) REFERENCES addresses(id),
ADD FOREIGN KEY (primary_contact_id) REFERENCES contacts(id),
ADD FOREIGN KEY (department_id) REFERENCES departments(id),
ADD FOREIGN KEY (position_id) REFERENCES positions(id);

-- Update suppliers table to use normalized address/contact
ALTER TABLE suppliers
ADD COLUMN address_id INT,
ADD COLUMN primary_contact_id INT,
ADD COLUMN supplier_category_id INT,
ADD FOREIGN KEY (address_id) REFERENCES addresses(id),
ADD FOREIGN KEY (primary_contact_id) REFERENCES contacts(id),
ADD FOREIGN KEY (supplier_category_id) REFERENCES categories(id);

-- ================================
-- 4. PRODUCT/SERVICE NORMALIZATION
-- ================================

-- Update produk table to use category reference
ALTER TABLE produk
ADD COLUMN category_id INT,
ADD FOREIGN KEY (category_id) REFERENCES categories(id);

-- ================================
-- 5. TRANSACTION NORMALIZATION
-- ================================

-- Add transaction type reference
CREATE TABLE transaction_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_code VARCHAR(50) UNIQUE NOT NULL,
    type_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Update relevant transaction tables to reference transaction types
-- (This is a design improvement - current tables are already fairly normalized)

-- ================================
-- 6. POPULATE REFERENCE DATA
-- ================================

-- Insert initial status types
INSERT INTO status_types (category, status_code, status_name, description) VALUES
('member', 'active', 'Aktif', 'Anggota aktif'),
('member', 'inactive', 'Tidak Aktif', 'Anggota tidak aktif'),
('member', 'suspended', 'Ditangguhkan', 'Anggota ditangguhkan'),
('employee', 'active', 'Aktif', 'Karyawan aktif'),
('employee', 'inactive', 'Tidak Aktif', 'Karyawan tidak aktif'),
('employee', 'terminated', 'Dipecat', 'Karyawan dipecat'),
('supplier', 'active', 'Aktif', 'Supplier aktif'),
('supplier', 'inactive', 'Tidak Aktif', 'Supplier tidak aktif'),
('supplier', 'blacklisted', 'Blacklist', 'Supplier diblacklist'),
('asset', 'excellent', 'Sangat Baik', 'Kondisi asset sangat baik'),
('asset', 'good', 'Baik', 'Kondisi asset baik'),
('asset', 'fair', 'Cukup', 'Kondisi asset cukup'),
('asset', 'poor', 'Buruk', 'Kondisi asset buruk'),
('asset', 'critical', 'Kritis', 'Kondisi asset kritis'),
('asset', 'disposed', 'Sudah Disposed', 'Asset sudah disposed');

-- Insert initial categories
INSERT INTO categories (category_type, category_code, category_name, description) VALUES
('product', 'food', 'Makanan', 'Produk makanan'),
('product', 'beverage', 'Minuman', 'Produk minuman'),
('product', 'household', 'Keperluan Rumah Tangga', 'Produk keperluan rumah tangga'),
('product', 'electronics', 'Elektronik', 'Produk elektronik'),
('service', 'consultation', 'Konsultasi', 'Layanan konsultasi'),
('service', 'maintenance', 'Maintenance', 'Layanan maintenance'),
('asset', 'building', 'Bangunan', 'Asset berupa bangunan'),
('asset', 'vehicle', 'Kendaraan', 'Asset berupa kendaraan'),
('asset', 'equipment', 'Peralatan', 'Asset berupa peralatan'),
('asset', 'furniture', 'Furniture', 'Asset berupa furniture'),
('supplier', 'food', 'Supplier Makanan', 'Supplier produk makanan'),
('supplier', 'service', 'Service Provider', 'Penyedia jasa'),
('supplier', 'equipment', 'Supplier Peralatan', 'Supplier peralatan');

-- Insert initial departments
INSERT INTO departments (department_code, department_name, description) VALUES
('HR', 'Human Resources', 'Departemen Sumber Daya Manusia'),
('FIN', 'Finance', 'Departemen Keuangan'),
('OPS', 'Operations', 'Departemen Operasional'),
('IT', 'Information Technology', 'Departemen Teknologi Informasi'),
('MKT', 'Marketing', 'Departemen Pemasaran'),
('SUP', 'Supervision', 'Departemen Pengawasan');

-- Insert initial positions
INSERT INTO positions (position_code, position_name, department_id, level, description) VALUES
('CEO', 'Chief Executive Officer', NULL, 5, 'Direktur Utama'),
('MGR_HR', 'Manager HR', 1, 4, 'Manager Human Resources'),
('MGR_FIN', 'Manager Finance', 2, 4, 'Manager Finance'),
('MGR_OPS', 'Manager Operations', 3, 4, 'Manager Operations'),
('STAFF_HR', 'Staff HR', 1, 2, 'Staff Human Resources'),
('STAFF_FIN', 'Staff Finance', 2, 2, 'Staff Finance'),
('STAFF_OPS', 'Staff Operations', 3, 2, 'Staff Operations');

-- ================================
-- 7. DATA MIGRATION SCRIPT
-- ================================

-- Migrate existing address data to normalized structure
-- (This is a placeholder - actual migration would depend on current data)

-- Example migration for anggota table:
-- INSERT INTO addresses (address_type, street_address, city, state, postal_code, country)
-- SELECT 'home', alamat, 'Unknown', 'Unknown', '00000', 'Indonesia' FROM anggota WHERE alamat IS NOT NULL;

-- Then update relationship:
-- UPDATE anggota a JOIN addresses addr ON a.alamat = addr.street_address SET a.address_id = addr.id;

-- ================================
-- 8. CREATE INDEXES
-- ================================

CREATE INDEX idx_entity_addresses_entity ON entity_addresses(entity_type, entity_id);
CREATE INDEX idx_entity_contacts_entity ON entity_contacts(entity_type, entity_id);
CREATE INDEX idx_status_types_category ON status_types(category, status_code);
CREATE INDEX idx_categories_type ON categories(category_type, category_code);
CREATE INDEX idx_departments_code ON departments(department_code);
CREATE INDEX idx_positions_code ON positions(position_code);
CREATE INDEX idx_transaction_types_code ON transaction_types(type_code);
