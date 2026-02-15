-- Digital Document Management & E-signature Tables for KSP Samosir
-- Based on fintech trends 2024 - Digital transformation and e-signature adoption

-- Document Templates
CREATE TABLE IF NOT EXISTS document_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_template VARCHAR(100) NOT NULL,
    jenis_dokumen ENUM('loan_agreement', 'membership_form', 'savings_contract', 'guarantee_letter', 'general') NOT NULL,
    template_content LONGTEXT NOT NULL,
    variables JSON, -- Available variables for template
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_jenis (jenis_dokumen),
    INDEX idx_active (is_active)
);

-- Digital Documents
CREATE TABLE IF NOT EXISTS digital_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_dokumen VARCHAR(50) NOT NULL UNIQUE,
    judul_dokumen VARCHAR(200) NOT NULL,
    jenis_dokumen ENUM('loan_agreement', 'membership_form', 'savings_contract', 'guarantee_letter', 'invoice', 'report', 'general') NOT NULL,
    template_id INT NULL,
    konten_dokumen LONGTEXT NOT NULL,
    file_path VARCHAR(500) NULL, -- For uploaded files
    file_size INT NULL,
    mime_type VARCHAR(100) NULL,

    -- Related entities
    member_id INT NULL,
    loan_id INT NULL,
    saving_id INT NULL,
    transaction_id INT NULL,

    status ENUM('draft', 'pending_signature', 'signed', 'completed', 'expired', 'rejected') DEFAULT 'draft',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',

    -- Metadata
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    signed_at TIMESTAMP NULL,

    FOREIGN KEY (template_id) REFERENCES document_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (member_id) REFERENCES anggota(id) ON DELETE SET NULL,
    FOREIGN KEY (loan_id) REFERENCES pinjaman(id) ON DELETE SET NULL,
    FOREIGN KEY (saving_id) REFERENCES simpanan(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_nomor (nomor_dokumen),
    INDEX idx_jenis (jenis_dokumen),
    INDEX idx_member (member_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_created (created_at)
);

-- Document Signatures (E-signature)
CREATE TABLE IF NOT EXISTS document_signatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    signer_id INT NULL, -- User ID if internal signer
    signer_type ENUM('member', 'staff', 'admin', 'external') DEFAULT 'member',
    signer_name VARCHAR(100) NOT NULL,
    signer_email VARCHAR(100) NULL,
    signer_phone VARCHAR(20) NULL,

    -- Signature data
    signature_type ENUM('digital', 'electronic', 'wet_signature') DEFAULT 'electronic',
    signature_data LONGTEXT NULL, -- Base64 encoded signature image
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    location_data JSON NULL, -- GPS coordinates, city, country

    -- Legal compliance
    consent_given BOOLEAN DEFAULT FALSE,
    consent_timestamp TIMESTAMP NULL,
    legal_agreement_accepted BOOLEAN DEFAULT FALSE,

    -- Signature status
    status ENUM('pending', 'signed', 'rejected', 'expired') DEFAULT 'pending',
    signed_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    reminder_sent BOOLEAN DEFAULT FALSE,

    -- Audit trail
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (document_id) REFERENCES digital_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (signer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_document (document_id),
    INDEX idx_signer (signer_id),
    INDEX idx_status (status),
    INDEX idx_signed (signed_at)
);

-- Document Workflow
CREATE TABLE IF NOT EXISTS document_workflows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    workflow_name VARCHAR(100) NOT NULL,
    current_step INT DEFAULT 1,
    total_steps INT NOT NULL,
    status ENUM('active', 'completed', 'paused', 'cancelled') DEFAULT 'active',

    -- Step configuration (JSON)
    steps_config JSON NOT NULL, -- Define each step: signer, deadline, requirements

    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,

    FOREIGN KEY (document_id) REFERENCES digital_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_document (document_id),
    INDEX idx_status (status),
    INDEX idx_current_step (current_step)
);

-- Document Audit Log
CREATE TABLE IF NOT EXISTS document_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NULL,
    signature_id INT NULL,
    action ENUM('created', 'viewed', 'signed', 'rejected', 'downloaded', 'shared', 'modified', 'deleted') NOT NULL,
    description TEXT,
    old_value TEXT NULL,
    new_value TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    performed_by INT NULL,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (document_id) REFERENCES digital_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (signature_id) REFERENCES document_signatures(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_document (document_id),
    INDEX idx_signature (signature_id),
    INDEX idx_action (action),
    INDEX idx_performed (performed_by, performed_at)
);

-- Document Sharing/Access Control
CREATE TABLE IF NOT EXISTS document_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    user_id INT NULL,
    access_type ENUM('view', 'sign', 'edit', 'admin') DEFAULT 'view',
    access_code VARCHAR(100) NULL, -- For external sharing
    expires_at TIMESTAMP NULL,
    access_count INT DEFAULT 0,
    last_accessed TIMESTAMP NULL,
    granted_by INT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (document_id) REFERENCES digital_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_document (document_id),
    INDEX idx_user (user_id),
    INDEX idx_code (access_code),
    INDEX idx_expires (expires_at)
);

-- Insert default document templates
INSERT INTO document_templates (nama_template, jenis_dokumen, template_content, variables) VALUES
('Loan Agreement Template', 'loan_agreement',
'<h1>KOPERASI SIMPAN PINJAM - PERJANJIAN PINJAMAN</h1>

<p>Nomor: {{nomor_pinjaman}}</p>
<p>Tanggal: {{tanggal_perjanjian}}</p>

<p>Pihak Pertama (Pemberi Pinjaman): KSP Samosir</p>
<p>Pihak Kedua (Penerima Pinjaman): {{nama_anggota}}</p>

<h2>KETENTUAN PINJAMAN</h2>
<ul>
<li>Jumlah Pinjaman: Rp {{jumlah_pinjaman}}</li>
<li>Tenor: {{tenor_bulan}} bulan</li>
<li>Bunga: {{bunga_persen}}% per tahun</li>
<li>Angsuran per bulan: Rp {{angsuran_per_bulan}}</li>
<li>Tanggal Jatuh Tempo: {{tanggal_jatuh_tempo}}</li>
</ul>

<h2>PERNYATAAN</h2>
<p>Saya yang bertanda tangan di bawah ini menyatakan telah menerima pinjaman dan akan mematuhi semua ketentuan yang berlaku.</p>

<p>Tanggal: {{tanggal_tanda_tangan}}</p>
<br><br>
<p>___________________________</p>
<p>{{nama_anggota}}</p>
<p>Penerima Pinjaman</p>

<p>Dicetak oleh sistem KSP Samosir pada {{tanggal_cetak}}</p>',
JSON_OBJECT(
    'nomor_pinjaman', 'Loan number',
    'tanggal_perjanjian', 'Agreement date',
    'nama_anggota', 'Member name',
    'jumlah_pinjaman', 'Loan amount',
    'tenor_bulan', 'Loan term in months',
    'bunga_persen', 'Interest rate percentage',
    'angsuran_per_bulan', 'Monthly installment',
    'tanggal_jatuh_tempo', 'Due date',
    'tanggal_tanda_tangan', 'Signature date',
    'tanggal_cetak', 'Print date'
)),

('Membership Form Template', 'membership_form',
'<h1>FORMULIR PENDAFTARAN ANGGOTA KSP SAMOSIR</h1>

<h2>DATA PRIBADI</h2>
<table border="1" style="width: 100%; border-collapse: collapse;">
<tr><td>Nama Lengkap</td><td>{{nama_lengkap}}</td></tr>
<tr><td>NIK</td><td>{{nik}}</td></tr>
<tr><td>Tempat/Tanggal Lahir</td><td>{{tempat_lahir}}, {{tanggal_lahir}}</td></tr>
<tr><td>Jenis Kelamin</td><td>{{jenis_kelamin}}</td></tr>
<tr><td>Alamat</td><td>{{alamat}}</td></tr>
<tr><td>No. HP</td><td>{{no_hp}}</td></tr>
<tr><td>Email</td><td>{{email}}</td></tr>
<tr><td>Pekerjaan</td><td>{{pekerjaan}}</td></tr>
<tr><td>Pendapatan Bulanan</td><td>Rp {{pendapatan_bulanan}}</td></tr>
</table>

<h2>KETENTUAN KEANGGOTAAN</h2>
<ol>
<li>Membayar simpanan pokok sesuai ketentuan</li>
<li>Membayar simpanan wajib setiap bulan</li>
<li>Mematuhi AD/ART dan keputusan rapat anggota</li>
<li>Menggunakan jasa koperasi untuk kebutuhan ekonomi</li>
</ol>

<h2>PERNYATAAN</h2>
<p>Saya menyatakan bahwa data yang saya berikan adalah benar dan saya bersedia mematuhi semua ketentuan keanggotaan KSP Samosir.</p>

<p>Tanggal: {{tanggal_pendaftaran}}</p>
<br><br>
<p>___________________________</p>
<p>{{nama_lengkap}}</p>
<p>Calon Anggota</p>

<p>Dicetak oleh sistem KSP Samosir pada {{tanggal_cetak}}</p>',
JSON_OBJECT(
    'nama_lengkap', 'Full name',
    'nik', 'National ID number',
    'tempat_lahir', 'Place of birth',
    'tanggal_lahir', 'Date of birth',
    'jenis_kelamin', 'Gender',
    'alamat', 'Address',
    'no_hp', 'Phone number',
    'email', 'Email address',
    'pekerjaan', 'Occupation',
    'pendapatan_bulanan', 'Monthly income',
    'tanggal_pendaftaran', 'Registration date',
    'tanggal_cetak', 'Print date'
));

-- Insert sample documents
INSERT INTO digital_documents (nomor_dokumen, judul_dokumen, jenis_dokumen, konten_dokumen, status) VALUES
('DOC-2024-001', 'Sistem Dokumen Digital KSP Samosir', 'general',
'<h1>Sistem Dokumen Digital KSP Samosir</h1>
<p>Sistem ini memungkinkan pengelolaan dokumen secara digital dengan fitur e-signature untuk kemudahan dan keamanan transaksi.</p>
<p>Dokumen ini dibuat secara otomatis oleh sistem pada tanggal ' . date('Y-m-d') . '.</p>',
'completed'),

('DOC-2024-002', 'Panduan E-Signature', 'general',
'<h1>Panduan Penggunaan Tanda Tangan Digital</h1>
<p>Tanda tangan digital memberikan keamanan dan kemudahan dalam proses persetujuan dokumen secara elektronik.</p>
<ul>
<li>Klik tombol "Tanda Tangan" pada dokumen</li>
<li>Verifikasi identitas Anda</li>
<li>Setujui persyaratan hukum</li>
<li>Dokumen akan ditandai sebagai sah</li>
</ul>',
'completed');
