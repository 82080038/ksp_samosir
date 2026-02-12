-- Role Management System for KSP Samosir
-- Based on koperasi_db structure but simplified for KSP needs

-- Roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1
);

-- Permissions table
CREATE TABLE IF NOT EXISTS permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    module VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1
);

-- Role-Permissions junction table
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- User-Roles junction table (replace current role column)
CREATE TABLE IF NOT EXISTS user_roles (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default roles
INSERT INTO roles (name, description) VALUES
('super_admin', 'Super administrator with full system access'),
('admin', 'Administrator with management access'),
('supervisor', 'Supervisor with approval access'),
('staff', 'Staff member with operational access'),
('member', 'Regular member with limited access')
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- Insert default permissions
INSERT INTO permissions (name, description, module) VALUES
-- Dashboard permissions
('view_dashboard', 'View dashboard', 'dashboard'),

-- Anggota permissions
('view_anggota', 'View member list', 'anggota'),
('create_anggota', 'Create new member', 'anggota'),
('edit_anggota', 'Edit member information', 'anggota'),
('delete_anggota', 'Delete member', 'anggota'),

-- Simpanan permissions
('view_simpanan', 'View savings', 'simpanan'),
('create_simpanan', 'Create savings account', 'simpanan'),
('edit_simpanan', 'Edit savings', 'simpanan'),
('delete_simpanan', 'Delete savings', 'simpanan'),
('transaksi_simpanan', 'Process savings transactions', 'simpanan'),

-- Pinjaman permissions
('view_pinjaman', 'View loan applications', 'pinjaman'),
('create_pinjaman', 'Create loan application', 'pinjaman'),
('edit_pinjaman', 'Edit loan details', 'pinjaman'),
('delete_pinjaman', 'Delete loan', 'pinjaman'),
('approve_pinjaman', 'Approve loan applications', 'pinjaman'),
('cairkan_pinjaman', 'Disburse approved loans', 'pinjaman'),

-- Produk permissions
('view_produk', 'View products', 'produk'),
('create_produk', 'Create new product', 'produk'),
('edit_produk', 'Edit product information', 'produk'),
('delete_produk', 'Delete product', 'produk'),

-- Penjualan permissions
('view_penjualan', 'View sales', 'penjualan'),
('create_penjualan', 'Create sale transaction', 'penjualan'),
('edit_penjualan', 'Edit sale', 'penjualan'),
('delete_penjualan', 'Delete sale', 'penjualan'),

-- Laporan permissions
('view_laporan', 'View reports', 'laporan'),
('generate_laporan', 'Generate reports', 'laporan'),
('export_laporan', 'Export reports', 'laporan'),

-- Settings permissions
('view_settings', 'View settings', 'settings'),
('edit_settings', 'Edit settings', 'settings'),
('manage_users', 'Manage user accounts', 'settings'),

-- System permissions
('view_logs', 'View audit logs', 'system'),
('manage_permissions', 'Manage role permissions', 'system'),
('admin_access', 'Full administrative access', 'system')
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- Assign permissions to roles
-- Super Admin: All permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'super_admin'
ON DUPLICATE KEY UPDATE assigned_at=NOW();

-- Admin: Most permissions except system admin
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'admin' AND p.name NOT IN ('manage_permissions', 'admin_access')
ON DUPLICATE KEY UPDATE assigned_at=NOW();

-- Supervisor: View and approve permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'supervisor' AND p.name IN (
    'view_dashboard', 'view_anggota', 'view_simpanan', 'view_pinjaman', 
    'view_produk', 'view_penjualan', 'view_laporan', 'approve_pinjaman'
)
ON DUPLICATE KEY UPDATE assigned_at=NOW();

-- Staff: Operational permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'staff' AND p.name IN (
    'view_dashboard', 'view_anggota', 'create_anggota', 'edit_anggota',
    'view_simpanan', 'create_simpanan', 'transaksi_simpanan',
    'view_pinjaman', 'create_pinjaman', 'view_produk', 'create_produk',
    'view_penjualan', 'create_penjualan', 'view_laporan'
)
ON DUPLICATE KEY UPDATE assigned_at=NOW();

-- Member: Limited permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'member' AND p.name IN (
    'view_dashboard', 'view_simpanan', 'transaksi_simpanan', 'view_laporan'
)
ON DUPLICATE KEY UPDATE assigned_at=NOW();

-- Update existing users to use new role system
-- First, extend role column to accommodate longer role names
ALTER TABLE users MODIFY COLUMN role VARCHAR(20) NOT NULL DEFAULT 'member';

-- Map current roles to new roles
UPDATE users SET role = 'super_admin' WHERE role = 'admin';
UPDATE users SET role = 'staff' WHERE role = 'staff';
UPDATE users SET role = 'member' WHERE role = 'member';

-- Insert user_roles for existing users
INSERT INTO user_roles (user_id, role_id, assigned_by)
SELECT u.id, r.id, u.id FROM users u, roles r 
WHERE u.role = r.name
ON DUPLICATE KEY UPDATE assigned_at=NOW();

-- Add role_id column to users table (temporary for migration)
ALTER TABLE users ADD COLUMN role_id INT NULL AFTER role;
UPDATE users u SET role_id = (SELECT id FROM roles WHERE name = u.role);
ALTER TABLE users DROP COLUMN role;
