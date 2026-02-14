-- Payroll System Tables
-- KSP Samosir - Employee management and payroll processing

CREATE TABLE employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    basic_salary DECIMAL(15,2) NOT NULL,
    allowance DECIMAL(15,2) DEFAULT 0,
    deduction DECIMAL(15,2) DEFAULT 0,
    supervisor_id INT,
    hire_date DATE NOT NULL,
    termination_date DATE,
    status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supervisor_id) REFERENCES users(id)
);

CREATE TABLE payrolls (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    period VARCHAR(7) NOT NULL, -- YYYY-MM format
    basic_salary DECIMAL(15,2) NOT NULL,
    allowance DECIMAL(15,2) DEFAULT 0,
    deduction DECIMAL(15,2) DEFAULT 0,
    gross_salary DECIMAL(15,2) NOT NULL,
    tax DECIMAL(15,2) DEFAULT 0,
    net_salary DECIMAL(15,2) NOT NULL,
    status ENUM('draft', 'processed', 'paid') DEFAULT 'processed',
    payment_date DATE,
    payment_method VARCHAR(50),
    processed_by INT,
    processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (processed_by) REFERENCES users(id)
);

CREATE TABLE employee_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    check_in TIME,
    check_out TIME,
    hours_worked DECIMAL(5,2),
    status ENUM('present', 'absent', 'late', 'half_day', 'overtime') DEFAULT 'present',
    notes TEXT,
    recorded_by INT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

CREATE TABLE payroll_components (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    component_type ENUM('allowance', 'deduction', 'bonus', 'overtime') NOT NULL,
    component_name VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    is_recurring TINYINT(1) DEFAULT 1,
    effective_date DATE NOT NULL,
    end_date DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Indexes
CREATE INDEX idx_employees_employee_id ON employees(employee_id);
CREATE INDEX idx_employees_status ON employees(status);
CREATE INDEX idx_employees_department ON employees(department);
CREATE INDEX idx_payrolls_employee_id ON payrolls(employee_id);
CREATE INDEX idx_payrolls_period ON payrolls(period);
CREATE INDEX idx_payrolls_status ON payrolls(status);
CREATE INDEX idx_employee_attendance_employee_id ON employee_attendance(employee_id);
CREATE INDEX idx_employee_attendance_date ON employee_attendance(attendance_date);
CREATE INDEX idx_payroll_components_employee_id ON payroll_components(employee_id);
CREATE INDEX idx_payroll_components_type ON payroll_components(component_type);
