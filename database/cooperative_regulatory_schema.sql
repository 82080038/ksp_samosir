-- Indonesian Cooperative Regulatory Framework Database Schema for KSP Samosir
-- Implementation of UU No. 25 Tahun 1992, OJK regulations, and cooperative compliance requirements

-- Cooperative Organizational Structure (UU No. 25 Tahun 1992)
CREATE TABLE IF NOT EXISTS cooperative_structure (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_name VARCHAR(200) NOT NULL,
    cooperative_code VARCHAR(20) UNIQUE NOT NULL,
    legal_entity_number VARCHAR(50),
    establishment_date DATE NOT NULL,
    business_sector VARCHAR(100) NOT NULL,
    cooperative_type ENUM('primary', 'secondary', 'tertiary') DEFAULT 'primary',
    membership_type ENUM('open', 'closed') DEFAULT 'open',
    operational_area VARCHAR(100),
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    postal_code VARCHAR(10),
    phone VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(200),

    -- Governance Structure (Article 18 UU 25/1992)
    chairman_name VARCHAR(100),
    vice_chairman_name VARCHAR(100),
    secretary_name VARCHAR(100),
    treasurer_name VARCHAR(100),
    supervisory_board_chairman VARCHAR(100),

    -- Capital Structure (Article 25-28 UU 25/1992)
    authorized_capital DECIMAL(15,2),
    issued_capital DECIMAL(15,2),
    paid_up_capital DECIMAL(15,2),
    reserve_fund DECIMAL(15,2) DEFAULT 0,
    education_fund DECIMAL(15,2) DEFAULT 0,

    -- Regulatory Compliance
    registration_number VARCHAR(50),
    ministry_registration_date DATE,
    ojk_registration_number VARCHAR(50),
    ojk_registration_date DATE,
    tax_id VARCHAR(50),
    business_license_number VARCHAR(50),

    -- Operational Status
    operational_status ENUM('active', 'inactive', 'dissolved', 'under_supervision') DEFAULT 'active',
    last_audit_date DATE,
    next_audit_date DATE,
    compliance_status ENUM('compliant', 'warning', 'non_compliant') DEFAULT 'compliant',

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (operational_status),
    INDEX idx_compliance (compliance_status),
    INDEX idx_sector (business_sector),
    INDEX idx_type (cooperative_type)
);

-- Member Management (Article 15-17 UU 25/1992)
CREATE TABLE IF NOT EXISTS cooperative_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_number VARCHAR(20) UNIQUE NOT NULL,
    cooperative_id INT NOT NULL,

    -- Personal Information
    full_name VARCHAR(200) NOT NULL,
    nik VARCHAR(20) UNIQUE,
    birth_date DATE,
    birth_place VARCHAR(100),
    gender ENUM('L', 'P'),
    marital_status ENUM('single', 'married', 'divorced', 'widowed'),
    religion VARCHAR(50),
    nationality VARCHAR(50) DEFAULT 'Indonesia',

    -- Contact Information
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(10),
    phone VARCHAR(20),
    email VARCHAR(100),

    -- Membership Details
    membership_date DATE NOT NULL,
    membership_type ENUM('regular', 'honorary', 'collective') DEFAULT 'regular',
    membership_status ENUM('active', 'inactive', 'suspended', 'resigned', 'expelled') DEFAULT 'active',

    -- Share Capital (Article 26 UU 25/1992)
    share_value DECIMAL(10,2) DEFAULT 0,
    total_shares INT DEFAULT 0,
    share_certificate_number VARCHAR(50),

    -- Savings & Deposits
    mandatory_savings DECIMAL(12,2) DEFAULT 0,
    voluntary_savings DECIMAL(12,2) DEFAULT 0,
    special_savings DECIMAL(12,2) DEFAULT 0,

    -- Employment (if applicable)
    employment_status ENUM('employed', 'self_employed', 'unemployed', 'student', 'retired'),
    occupation VARCHAR(100),
    employer VARCHAR(200),

    -- RAT Participation (Article 29 UU 25/1992)
    rat_participation_years INT DEFAULT 0,
    last_rat_attendance DATE,

    -- Regulatory Compliance
    kyc_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    aml_risk_level ENUM('low', 'medium', 'high') DEFAULT 'low',

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_status (membership_status),
    INDEX idx_membership_date (membership_date),
    INDEX idx_kyc (kyc_status)
);

-- RAT (Rapat Anggota Tahunan) Management (Article 29-32 UU 25/1992)
CREATE TABLE IF NOT EXISTS rat_meetings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    rat_year YEAR NOT NULL,
    rat_type ENUM('annual', 'extraordinary') DEFAULT 'annual',

    -- Meeting Details
    meeting_date DATE NOT NULL,
    meeting_time TIME,
    venue VARCHAR(200),
    agenda TEXT,

    -- Attendance Requirements (Minimum 3/4 of members)
    total_members INT NOT NULL,
    quorum_required INT NOT NULL,
    members_present INT DEFAULT 0,
    proxies_present INT DEFAULT 0,
    total_attendance INT DEFAULT 0,
    quorum_achieved BOOLEAN DEFAULT FALSE,

    -- Meeting Results
    chairman_elected VARCHAR(100),
    vice_chairman_elected VARCHAR(100),
    secretary_elected VARCHAR(100),
    treasurer_elected VARCHAR(100),
    supervisory_board_elected TEXT, -- JSON array of names

    -- Financial Approvals
    financial_report_approved BOOLEAN DEFAULT FALSE,
    budget_approved BOOLEAN DEFAULT FALSE,
    dividend_distribution TEXT, -- JSON with distribution details

    -- Resolutions
    resolutions TEXT, -- JSON array of resolutions passed

    -- Documentation
    minutes_document VARCHAR(500),
    attendance_list VARCHAR(500),
    financial_statements VARCHAR(500),

    -- Regulatory Compliance
    ministry_notification_date DATE,
    ministry_approval_date DATE,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_year (rat_year),
    INDEX idx_date (meeting_date),
    INDEX idx_quorum (quorum_achieved)
);

-- RAT Attendance Tracking
CREATE TABLE IF NOT EXISTS rat_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rat_id INT NOT NULL,
    member_id INT NOT NULL,
    attendance_type ENUM('present', 'proxy', 'absent') DEFAULT 'present',
    proxy_holder_name VARCHAR(100),
    proxy_holder_nik VARCHAR(20),

    FOREIGN KEY (rat_id) REFERENCES rat_meetings(id),
    FOREIGN KEY (member_id) REFERENCES cooperative_members(id),
    UNIQUE KEY unique_attendance (rat_id, member_id)
);

-- Cooperative Governance Bodies (Article 18-24 UU 25/1992)
CREATE TABLE IF NOT EXISTS governance_bodies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    body_type ENUM('board_of_directors', 'supervisory_board', 'executive_committee') NOT NULL,
    position_title VARCHAR(100) NOT NULL,
    member_id INT,
    external_member_name VARCHAR(200),
    is_external BOOLEAN DEFAULT FALSE,

    -- Term of Office (Article 20 UU 25/1992)
    term_start_date DATE NOT NULL,
    term_end_date DATE NOT NULL,
    term_years INT DEFAULT 4,

    -- Authority and Responsibilities
    authorities TEXT, -- JSON array of authorities
    responsibilities TEXT, -- JSON array of responsibilities

    -- Status
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    appointment_date DATE,
    resignation_date DATE,

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    FOREIGN KEY (member_id) REFERENCES cooperative_members(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_type (body_type),
    INDEX idx_member (member_id),
    INDEX idx_status (status)
);

-- Financial Accounting Standards (PSAK 109 for Cooperatives)
CREATE TABLE IF NOT EXISTS cooperative_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    account_code VARCHAR(20) NOT NULL,
    account_name VARCHAR(200) NOT NULL,
    account_type ENUM('asset', 'liability', 'equity', 'income', 'expense') NOT NULL,
    account_category VARCHAR(100),
    parent_account_id INT,

    -- PSAK 109 Classification
    balance_sheet_classification ENUM('current_asset', 'noncurrent_asset', 'current_liability', 'noncurrent_liability', 'equity'),
    income_statement_classification ENUM('operating_income', 'other_income', 'operating_expense', 'other_expense'),

    -- Accounting Rules
    normal_balance ENUM('debit', 'credit'),
    allow_manual_entry BOOLEAN DEFAULT TRUE,
    requires_approval BOOLEAN DEFAULT FALSE,

    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    FOREIGN KEY (parent_account_id) REFERENCES cooperative_accounts(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_type (account_type),
    INDEX idx_code (account_code),
    INDEX idx_active (is_active)
);

-- Journal Entries (Double-entry bookkeeping)
CREATE TABLE IF NOT EXISTS journal_entries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    entry_number VARCHAR(20) UNIQUE NOT NULL,
    entry_date DATE NOT NULL,
    reference_type VARCHAR(50), -- 'transaction', 'adjustment', 'closing'
    reference_id INT,
    description TEXT,

    -- Entry Status
    status ENUM('draft', 'posted', 'reversed') DEFAULT 'draft',
    posted_by INT,
    posted_at DATETIME,

    -- Audit Trail
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_by INT,
    approved_at DATETIME,

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_date (entry_date),
    INDEX idx_status (status),
    INDEX idx_reference (reference_type, reference_id)
);

-- Journal Entry Lines
CREATE TABLE IF NOT EXISTS journal_lines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    journal_entry_id INT NOT NULL,
    account_id INT NOT NULL,
    debit DECIMAL(15,2) DEFAULT 0,
    credit DECIMAL(15,2) DEFAULT 0,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id INT,

    FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id),
    FOREIGN KEY (account_id) REFERENCES cooperative_accounts(id),
    INDEX idx_journal (journal_entry_id),
    INDEX idx_account (account_id)
);

-- Financial Periods (Article 33 UU 25/1992)
CREATE TABLE IF NOT EXISTS financial_periods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    period_year YEAR NOT NULL,
    period_type ENUM('annual', 'quarterly', 'monthly') DEFAULT 'annual',
    period_number INT DEFAULT 1, -- 1-4 for quarterly, 1-12 for monthly
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,

    -- Period Status
    status ENUM('open', 'closed', 'locked') DEFAULT 'open',
    closed_by INT,
    closed_at DATETIME,

    -- Financial Results
    total_assets DECIMAL(15,2),
    total_liabilities DECIMAL(15,2),
    total_equity DECIMAL(15,2),
    net_income DECIMAL(15,2),

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_year (period_year),
    INDEX idx_period (period_type, period_number),
    INDEX idx_status (status)
);

-- Regulatory Reporting Requirements (OJK & Ministry of Cooperatives)
CREATE TABLE IF NOT EXISTS regulatory_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    report_type ENUM('monthly_financial', 'quarterly_financial', 'annual_financial', 'annual_activity', 'rat_minutes', 'membership_changes') NOT NULL,
    report_period VARCHAR(20) NOT NULL, -- '2024-01', '2024-Q1', '2024'
    report_year YEAR NOT NULL,
    report_quarter INT, -- 1-4
    report_month INT, -- 1-12

    -- Report Content
    report_data JSON,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- Submission Status
    submitted_to ENUM('ministry_of_cooperatives', 'ojk', 'province', 'district'),
    submission_date DATE,
    submission_reference VARCHAR(100),
    approval_status ENUM('pending', 'approved', 'rejected', 'revision_required') DEFAULT 'pending',
    approval_date DATE,
    approval_notes TEXT,

    -- Document References
    report_file VARCHAR(500),
    supporting_documents JSON,

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_type (report_type),
    INDEX idx_period (report_year, report_quarter, report_month),
    INDEX idx_submission (submitted_to, submission_date),
    INDEX idx_approval (approval_status)
);

-- Cooperative Activities & Programs (Article 5 UU 25/1992)
CREATE TABLE IF NOT EXISTS cooperative_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    activity_name VARCHAR(200) NOT NULL,
    activity_type ENUM('economic', 'social', 'educational', 'cultural', 'environmental') NOT NULL,
    activity_category VARCHAR(100),

    -- Activity Details
    description TEXT,
    start_date DATE,
    end_date DATE,
    budget_allocated DECIMAL(12,2),
    budget_used DECIMAL(12,2),

    -- Target Beneficiaries
    target_members INT,
    target_community INT,
    actual_beneficiaries INT,

    -- Implementation
    responsible_person VARCHAR(100),
    partners TEXT, -- JSON array of partners
    status ENUM('planned', 'ongoing', 'completed', 'cancelled') DEFAULT 'planned',

    -- Results & Impact
    results_achieved TEXT,
    impact_metrics JSON,
    lessons_learned TEXT,

    -- Documentation
    activity_reports TEXT, -- JSON array of report files
    photos_videos TEXT, -- JSON array of media files

    -- Regulatory Compliance
    ministry_approval_required BOOLEAN DEFAULT FALSE,
    ministry_approval_date DATE,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_type (activity_type),
    INDEX idx_status (status),
    INDEX idx_date (start_date, end_date)
);

-- Audit Trail & Compliance Monitoring
CREATE TABLE IF NOT EXISTS compliance_audits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    audit_type ENUM('internal', 'external', 'regulatory', 'financial') NOT NULL,
    audit_period VARCHAR(20),
    audit_firm VARCHAR(200),
    auditor_name VARCHAR(100),

    -- Audit Scope
    audit_scope TEXT,
    audit_objectives TEXT,

    -- Findings
    findings TEXT, -- JSON array of findings
    recommendations TEXT, -- JSON array of recommendations
    corrective_actions TEXT, -- JSON array of required actions

    -- Results
    audit_result ENUM('passed', 'conditional_pass', 'failed') DEFAULT 'passed',
    compliance_score DECIMAL(5,2), -- 0-100
    critical_findings INT DEFAULT 0,

    -- Follow-up
    action_deadline DATE,
    action_status ENUM('pending', 'in_progress', 'completed', 'overdue') DEFAULT 'pending',
    follow_up_date DATE,

    -- Documentation
    audit_report_file VARCHAR(500),
    management_response TEXT,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_type (audit_type),
    INDEX idx_result (audit_result),
    INDEX idx_status (action_status)
);

-- Legal Document Management
CREATE TABLE IF NOT EXISTS legal_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    document_type ENUM('bylaws', 'articles_of_association', 'registration_certificate', 'business_license', 'tax_certificate', 'rat_minutes', 'board_decisions', 'contracts', 'regulatory_approvals') NOT NULL,
    document_title VARCHAR(300) NOT NULL,
    document_number VARCHAR(100),
    issue_date DATE,
    expiry_date DATE,
    issuing_authority VARCHAR(200),

    -- Document Content
    document_file VARCHAR(500),
    document_content TEXT, -- For storing key clauses
    digital_signature TEXT,

    -- Status & Compliance
    status ENUM('active', 'expired', 'revoked', 'superseded') DEFAULT 'active',
    compliance_required BOOLEAN DEFAULT TRUE,
    renewal_required BOOLEAN DEFAULT FALSE,
    renewal_date DATE,

    -- Access Control
    access_level ENUM('public', 'members_only', 'board_only', 'management_only') DEFAULT 'members_only',

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_type (document_type),
    INDEX idx_status (status),
    INDEX idx_expiry (expiry_date),
    INDEX idx_access (access_level)
);

-- Reserve Fund Management (Article 44 UU 25/1992)
CREATE TABLE IF NOT EXISTS reserve_funds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    fund_type ENUM('reserve_fund', 'education_fund', 'welfare_fund', 'development_fund') NOT NULL,
    fund_year YEAR NOT NULL,

    -- Fund Allocation (Minimum 25% of annual profit)
    allocated_amount DECIMAL(12,2) NOT NULL,
    allocation_percentage DECIMAL(5,2),
    allocation_source VARCHAR(200), -- Annual profit, donations, etc.

    -- Fund Utilization
    utilized_amount DECIMAL(12,2) DEFAULT 0,
    utilization_details TEXT,

    -- Balance
    opening_balance DECIMAL(12,2) DEFAULT 0,
    closing_balance DECIMAL(12,2) DEFAULT 0,

    -- Compliance
    ministry_approval_required BOOLEAN DEFAULT TRUE,
    ministry_approval_date DATE,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_type (fund_type),
    INDEX idx_year (fund_year)
);

-- Education Fund Utilization (Article 45 UU 25/1992)
CREATE TABLE IF NOT EXISTS education_fund_utilization (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    utilization_year YEAR NOT NULL,
    program_name VARCHAR(300) NOT NULL,
    program_type ENUM('training', 'education', 'seminar', 'workshop', 'study_tour', 'research') NOT NULL,

    -- Program Details
    participants INT,
    target_audience VARCHAR(200), -- Members, management, community
    program_provider VARCHAR(200),
    program_duration_days INT,

    -- Budget
    allocated_budget DECIMAL(10,2),
    actual_cost DECIMAL(10,2),

    -- Results
    completion_status ENUM('completed', 'ongoing', 'cancelled') DEFAULT 'completed',
    participants_satisfied INT,
    satisfaction_rating DECIMAL(3,1),
    learning_outcomes TEXT,

    -- Documentation
    program_report VARCHAR(500),
    certificates_issued INT,
    photos_evidence TEXT,

    -- Ministry Approval
    ministry_approval_required BOOLEAN DEFAULT FALSE,
    ministry_approval_date DATE,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_year (utilization_year),
    INDEX idx_type (program_type),
    INDEX idx_status (completion_status)
);

-- Create indexes for performance
CREATE INDEX idx_members_cooperative ON cooperative_members(cooperative_id, membership_status);
CREATE INDEX idx_rat_cooperative_year ON rat_meetings(cooperative_id, rat_year);
CREATE INDEX idx_governance_cooperative_type ON governance_bodies(cooperative_id, body_type, status);
CREATE INDEX idx_accounts_cooperative_type ON cooperative_accounts(cooperative_id, account_type, is_active);
CREATE INDEX idx_journal_cooperative_date ON journal_entries(cooperative_id, entry_date, status);
CREATE INDEX idx_reports_cooperative_type ON regulatory_reports(cooperative_id, report_type, report_year);
CREATE INDEX idx_activities_cooperative_type ON cooperative_activities(cooperative_id, activity_type, status);
CREATE INDEX idx_audits_cooperative_type ON compliance_audits(cooperative_id, audit_type);
CREATE INDEX idx_documents_cooperative_type ON legal_documents(cooperative_id, document_type, status);
