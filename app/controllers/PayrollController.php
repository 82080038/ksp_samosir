<?php
require_once __DIR__ . '/BaseController.php';

/**
 * PayrollController handles employee management and payroll processing.
 * Manages employee records, salary calculations, and payroll distributions.
 */
class PayrollController extends BaseController {
    /**
     * Display payroll management dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $stats = $this->getPayrollStats();
        $recent_payrolls = $this->getRecentPayrolls();

        $this->render('payroll/index', [
            'stats' => $stats,
            'recent_payrolls' => $recent_payrolls
        ]);
    }

    /**
     * Display employee management.
     */
    public function employees() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $totalResult = fetchRow("SELECT COUNT(*) as count FROM employees");
        $total = $totalResult['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $employees = fetchAll("SELECT e.*, u.full_name as supervisor_name FROM employees e LEFT JOIN users u ON e.supervisor_id = u.id ORDER BY e.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('payroll/employees', [
            'employees' => $employees,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Create new employee.
     */
    public function createEmployee() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->collectEmployeeInput();
            $error = $this->validateEmployeeInput($data);
            if ($error) {
                flashMessage('error', $error);
                redirect('payroll/createEmployee');
            }

            runInTransaction(function($conn) use ($data) {
                $stmt = $conn->prepare("INSERT INTO employees (employee_id, full_name, email, phone, address, position, department, basic_salary, allowance, deduction, supervisor_id, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssssddddssss', 
                    $data['employee_id'], $data['full_name'], $data['email'], $data['phone'], 
                    $data['address'], $data['position'], $data['department'], $data['basic_salary'], 
                    $data['allowance'], $data['deduction'], $data['supervisor_id'], $data['hire_date'], $data['status']);
                $stmt->execute();
                $stmt->close();
            });

            flashMessage('success', 'Karyawan berhasil ditambahkan');
            redirect('payroll/employees');
        }

        $supervisors = fetchAll("SELECT id, full_name FROM users WHERE role IN ('admin', 'pengurus') ORDER BY full_name");

        $this->render('payroll/create_employee', [
            'supervisors' => $supervisors
        ]);
    }

    /**
     * Process payroll for employees.
     */
    public function processPayroll() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $period = sanitize($_POST['period']);
            $employee_ids = $_POST['employee_ids'] ?? [];

            if (empty($employee_ids)) {
                flashMessage('error', 'Pilih minimal satu karyawan');
                redirect('payroll/processPayroll');
            }

            $payroll_data = [];
            foreach ($employee_ids as $employee_id) {
                $employee = fetchRow("SELECT * FROM employees WHERE id = ?", [$employee_id], 'i');
                if ($employee) {
                    $gross_salary = $employee['basic_salary'] + $employee['allowance'] - $employee['deduction'];
                    $tax = $this->calculateTax($gross_salary);
                    $net_salary = $gross_salary - $tax;

                    $payroll_data[] = [
                        'employee_id' => $employee_id,
                        'basic_salary' => $employee['basic_salary'],
                        'allowance' => $employee['allowance'],
                        'deduction' => $employee['deduction'],
                        'gross_salary' => $gross_salary,
                        'tax' => $tax,
                        'net_salary' => $net_salary
                    ];
                }
            }

            runInTransaction(function($conn) use ($period, $payroll_data) {
                $processed_by = $_SESSION['user']['id'] ?? 1;
                
                foreach ($payroll_data as $data) {
                    $stmt = $conn->prepare("INSERT INTO payrolls (employee_id, period, basic_salary, allowance, deduction, gross_salary, tax, net_salary, status, processed_by, processed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'processed', ?, NOW())");
                    $stmt->bind_param('isddddddd', 
                        $data['employee_id'], $period, $data['basic_salary'], $data['allowance'], 
                        $data['deduction'], $data['gross_salary'], $data['tax'], $data['net_salary'], $processed_by);
                    $stmt->execute();
                    $stmt->close();
                }
            });

            flashMessage('success', 'Payroll berhasil diproses untuk ' . count($payroll_data) . ' karyawan');
            redirect('payroll/payrollHistory');
        }

        require_once __DIR__ . '/../shared/php/mobile_optimizer.php';

        $device = getDeviceInfo();
        $perPage = getMobilePagination();

        $employees = fetchAll("
            SELECT
                e.id,
                e.employee_id,
                e.full_name,
                e.basic_salary,
                e.allowance,
                e.deduction,
                COALESCE(d.department_name, 'Not Assigned') as department_name,
                COALESCE(p.position_name, 'Not Assigned') as position_name,
                COALESCE(addr.street_address, e.address) as address,
                COALESCE(c_phone.contact_value, e.phone) as phone,
                COALESCE(c_email.contact_value, e.email) as email_address
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            LEFT JOIN addresses addr ON e.address_id = addr.id AND addr.is_primary = 1
            LEFT JOIN contacts c_phone ON e.primary_contact_id = c_phone.id AND c_phone.contact_type = 'phone'
            LEFT JOIN contacts c_email ON c_email.id = (SELECT id FROM contacts WHERE contact_type = 'email' AND id IN (SELECT contact_id FROM entity_contacts WHERE entity_type = 'employee' AND entity_id = e.id) LIMIT 1)
            WHERE e.status = 'active'
            ORDER BY e.full_name
            LIMIT {$perPage}
        ", [], '');

        // Compress data for mobile devices
        $employees = compressMobileData($employees);

        $this->render('payroll/process_payroll', [
            'employees' => $employees
        ]);
    }

    /**
     * Display payroll history.
     */
    public function payrollHistory() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $totalResult = fetchRow("SELECT COUNT(*) as count FROM payrolls");
        $total = $totalResult['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $payrolls = fetchAll("SELECT p.*, e.employee_id, e.full_name FROM payrolls p LEFT JOIN employees e ON p.employee_id = e.id ORDER BY p.processed_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('payroll/payroll_history', [
            'payrolls' => $payrolls,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Generate payroll report.
     */
    public function payrollReport() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $period = $_GET['period'] ?? date('Y-m');
        $department = $_GET['department'] ?? 'all';

        $query = "SELECT p.*, e.employee_id, e.full_name, COALESCE(pos.position_name, 'Not Assigned') as position, COALESCE(dept.department_name, 'Not Assigned') as department FROM payrolls p LEFT JOIN employees e ON p.employee_id = e.id LEFT JOIN positions pos ON e.position_id = pos.id LEFT JOIN departments dept ON e.department_id = dept.id WHERE p.period = ?";
        
        if ($department !== 'all') {
            $query .= " AND dept.id = ?";
        }
        
        $query .= " ORDER BY e.full_name";

        $payrolls = fetchAll($query, $department !== 'all' ? [$period, $department] : [$period]);

        // Calculate totals
        $totals = [
            'total_employees' => count($payrolls),
            'total_basic_salary' => array_sum(array_column($payrolls, 'basic_salary')),
            'total_allowance' => array_sum(array_column($payrolls, 'allowance')),
            'total_deduction' => array_sum(array_column($payrolls, 'deduction')),
            'total_gross' => array_sum(array_column($payrolls, 'gross_salary')),
            'total_tax' => array_sum(array_column($payrolls, 'tax')),
            'total_net' => array_sum(array_column($payrolls, 'net_salary'))
        ];

        $this->render('payroll/payroll_report', [
            'payrolls' => $payrolls,
            'totals' => $totals,
            'period' => $period,
            'department' => $department
        ]);
    }

    /**
     * Calculate tax based on Indonesian tax rules.
     */
    private function calculateTax($gross_salary) {
        // Simplified tax calculation for Indonesia
        // This is a basic implementation - in production, use proper tax calculation
        $annual_salary = $gross_salary * 12;
        $tax = 0;

        if ($annual_salary <= 50000000) {
            $tax = $annual_salary * 0.05;
        } elseif ($annual_salary <= 250000000) {
            $tax = 50000000 * 0.05 + ($annual_salary - 50000000) * 0.15;
        } elseif ($annual_salary <= 500000000) {
            $tax = 50000000 * 0.05 + 200000000 * 0.15 + ($annual_salary - 250000000) * 0.25;
        } else {
            $tax = 50000000 * 0.05 + 200000000 * 0.15 + 250000000 * 0.25 + ($annual_salary - 500000000) * 0.30;
        }

        return $tax / 12; // Monthly tax
    }

    /**
     * Get payroll statistics.
     */
    private function getPayrollStats() {
        $stats = [];

        $stats['total_employees'] = fetchRow("SELECT COUNT(*) as total FROM employees WHERE status = 'active'")['total'] ?? 0;
        $stats['total_payrolls_this_month'] = fetchRow("SELECT COUNT(*) as total FROM payrolls WHERE MONTH(processed_at) = MONTH(CURRENT_DATE) AND YEAR(processed_at) = YEAR(CURRENT_DATE)")['total'] ?? 0;
        $stats['total_salary_paid_month'] = fetchRow("SELECT COALESCE(SUM(net_salary), 0) as total FROM payrolls WHERE MONTH(processed_at) = MONTH(CURRENT_DATE) AND YEAR(processed_at) = YEAR(CURRENT_DATE)")['total'] ?? 0;
        $stats['pending_payrolls'] = fetchRow("SELECT COUNT(*) as total FROM employees WHERE status = 'active' AND id NOT IN (SELECT employee_id FROM payrolls WHERE period = DATE_FORMAT(CURRENT_DATE, '%Y-%m'))")['total'] ?? 0;

        return $stats;
    }

    /**
     * Get recent payrolls.
     */
    private function getRecentPayrolls() {
        return fetchAll("SELECT p.*, e.employee_id, e.full_name FROM payrolls p LEFT JOIN employees e ON p.employee_id = e.id ORDER BY p.processed_at DESC LIMIT 10") ?? [];
    }

    /**
     * Collect employee input data.
     */
    private function collectEmployeeInput() {
        return [
            'employee_id' => sanitize($_POST['employee_id']),
            'full_name' => sanitize($_POST['full_name']),
            'email' => sanitize($_POST['email']),
            'phone' => sanitize($_POST['phone']),
            'address' => sanitize($_POST['address']),
            'position' => sanitize($_POST['position']),
            'department' => sanitize($_POST['department']),
            'basic_salary' => floatval($_POST['basic_salary']),
            'allowance' => floatval($_POST['allowance'] ?? 0),
            'deduction' => floatval($_POST['deduction'] ?? 0),
            'supervisor_id' => intval($_POST['supervisor_id'] ?? 0),
            'hire_date' => sanitize($_POST['hire_date']),
            'status' => sanitize($_POST['status'] ?? 'active')
        ];
    }

    /**
     * Validate employee input.
     */
    private function validateEmployeeInput($data) {
        if (empty($data['employee_id'])) return 'ID Karyawan wajib diisi';
        if (empty($data['full_name'])) return 'Nama lengkap wajib diisi';
        if (empty($data['position'])) return 'Jabatan wajib diisi';
        if ($data['basic_salary'] <= 0) return 'Gaji pokok harus lebih dari 0';
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) return 'Format email tidak valid';
        
        // Check if employee_id already exists
        $existing = fetchRow("SELECT id FROM employees WHERE employee_id = ?", [$data['employee_id']], 's');
        if ($existing) return 'ID Karyawan sudah ada';
        
        return null;
    }
}
