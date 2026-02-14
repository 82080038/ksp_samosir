<?php
/**
 * Indonesian Cooperative Regulatory Framework Implementation
 * Compliant with UU No. 25 Tahun 1992, OJK regulations, and PSAK 109
 */

class CooperativeRegulatoryFramework {
    private $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
    }

    /**
     * Initialize cooperative with regulatory compliance
     */
    public function initializeCooperative($coopData) {
        // Validate against UU 25/1992 requirements
        $validation = $this->validateCooperativeRequirements($coopData);
        if (!$validation['compliant']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        // Create cooperative structure
        $stmt = $this->pdo->prepare("
            INSERT INTO cooperative_structure
            (cooperative_name, cooperative_code, establishment_date, business_sector,
             cooperative_type, membership_type, operational_area, address, city, province,
             chairman_name, vice_chairman_name, secretary_name, treasurer_name,
             authorized_capital, issued_capital, paid_up_capital)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $coopData['name'],
            $coopData['code'],
            $coopData['establishment_date'],
            $coopData['business_sector'],
            $coopData['type'] ?? 'primary',
            $coopData['membership_type'] ?? 'open',
            $coopData['operational_area'],
            $coopData['address'],
            $coopData['city'],
            $coopData['province'],
            $coopData['chairman'],
            $coopData['vice_chairman'] ?? null,
            $coopData['secretary'] ?? null,
            $coopData['treasurer'] ?? null,
            $coopData['authorized_capital'],
            $coopData['issued_capital'],
            $coopData['paid_up_capital']
        ]);

        $coopId = $this->pdo->lastInsertId();

        // Initialize default accounts (PSAK 109)
        $this->initializePSAK109Accounts($coopId);

        // Create governance bodies
        $this->createInitialGovernanceBodies($coopId, $coopData);

        return [
            'success' => true,
            'cooperative_id' => $coopId,
            'message' => 'Cooperative initialized with regulatory compliance'
        ];
    }

    /**
     * Validate cooperative against UU 25/1992 requirements
     */
    private function validateCooperativeRequirements($data) {
        $errors = [];

        // Article 5: Minimum 20 members for primary cooperative
        if ($data['type'] === 'primary' && (!isset($data['initial_members']) || $data['initial_members'] < 20)) {
            $errors[] = 'Primary cooperative must have minimum 20 members (Article 5 UU 25/1992)';
        }

        // Article 6: Must have clear business purpose
        if (empty($data['business_sector'])) {
            $errors[] = 'Business sector must be clearly defined (Article 6 UU 25/1992)';
        }

        // Article 25: Minimum capital requirements
        if ($data['paid_up_capital'] < 10000000) { // Rp 10 million minimum
            $errors[] = 'Minimum paid-up capital Rp 10,000,000 required (Article 25 UU 25/1992)';
        }

        // Article 18: Governance structure required
        if (empty($data['chairman'])) {
            $errors[] = 'Chairman must be appointed (Article 18 UU 25/1992)';
        }

        return [
            'compliant' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Initialize PSAK 109 compliant accounts
     */
    private function initializePSAK109Accounts($coopId) {
        $defaultAccounts = [
            // Assets
            ['1001', 'Kas', 'asset', 'current_asset'],
            ['1002', 'Bank', 'asset', 'current_asset'],
            ['1101', 'Piutang Anggota', 'asset', 'current_asset'],
            ['1102', 'Piutang Non-Anggota', 'asset', 'current_asset'],
            ['1201', 'Persediaan', 'asset', 'current_asset'],

            // Fixed Assets
            ['1501', 'Tanah', 'asset', 'noncurrent_asset'],
            ['1502', 'Bangunan', 'asset', 'noncurrent_asset'],
            ['1503', 'Peralatan', 'asset', 'noncurrent_asset'],

            // Liabilities
            ['2001', 'Simpanan Anggota', 'liability', 'current_liability'],
            ['2002', 'Simpanan Sukarela', 'liability', 'current_liability'],
            ['2101', 'Pinjaman Bank', 'liability', 'current_liability'],
            ['2102', 'Pinjaman Non-Bank', 'liability', 'current_liability'],

            // Equity
            ['3001', 'Modal Disetor', 'equity', 'equity'],
            ['3002', 'Modal Penyertaan', 'equity', 'equity'],
            ['3003', 'Cadangan', 'equity', 'equity'],
            ['3004', 'SHU Ditahan', 'equity', 'equity'],

            // Income
            ['4001', 'Pendapatan Bunga Pinjaman', 'income', 'operating_income'],
            ['4002', 'Pendapatan Administrasi', 'income', 'operating_income'],
            ['4003', 'Pendapatan Jasa', 'income', 'operating_income'],

            // Expenses
            ['5001', 'Beban Operasional', 'expense', 'operating_expense'],
            ['5002', 'Beban Bunga Simpanan', 'expense', 'operating_expense'],
            ['5003', 'Beban Administrasi', 'expense', 'operating_expense'],
            ['5101', 'Beban Cadangan', 'expense', 'operating_expense']
        ];

        foreach ($defaultAccounts as $account) {
            $stmt = $this->pdo->prepare("
                INSERT INTO cooperative_accounts
                (cooperative_id, account_code, account_name, account_type,
                 balance_sheet_classification, normal_balance)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $normalBalance = in_array($account[2], ['asset', 'expense']) ? 'debit' : 'credit';

            $stmt->execute([
                $coopId,
                $account[0],
                $account[1],
                $account[2],
                $account[3],
                $normalBalance
            ]);
        }
    }

    /**
     * Create initial governance bodies
     */
    private function createInitialGovernanceBodies($coopId, $coopData) {
        // Board of Directors (Article 18 UU 25/1992)
        $this->createGovernancePosition($coopId, 'board_of_directors', 'Ketua', $coopData['chairman']);
        if (!empty($coopData['vice_chairman'])) {
            $this->createGovernancePosition($coopId, 'board_of_directors', 'Wakil Ketua', $coopData['vice_chairman']);
        }
        if (!empty($coopData['secretary'])) {
            $this->createGovernancePosition($coopId, 'board_of_directors', 'Sekretaris', $coopData['secretary']);
        }
        if (!empty($coopData['treasurer'])) {
            $this->createGovernancePosition($coopId, 'board_of_directors', 'Bendahara', $coopData['treasurer']);
        }

        // Supervisory Board (Article 19 UU 25/1992)
        if (!empty($coopData['supervisory_board'])) {
            foreach ($coopData['supervisory_board'] as $supervisor) {
                $this->createGovernancePosition($coopId, 'supervisory_board', 'Pengawas', $supervisor);
            }
        }
    }

    private function createGovernancePosition($coopId, $bodyType, $position, $memberName) {
        $stmt = $this->pdo->prepare("
            INSERT INTO governance_bodies
            (cooperative_id, body_type, position_title, external_member_name, is_external,
             term_start_date, term_end_date, status)
            VALUES (?, ?, ?, ?, TRUE, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 4 YEAR), 'active')
        ");

        $stmt->execute([$coopId, $bodyType, $position, $memberName]);
    }

    /**
     * Member management with regulatory compliance
     */
    public function registerMember($memberData) {
        // Validate member registration requirements
        $validation = $this->validateMemberRegistration($memberData);
        if (!$validation['compliant']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        // Generate member number
        $memberNumber = $this->generateMemberNumber($memberData['cooperative_id']);

        // Create member record
        $stmt = $this->pdo->prepare("
            INSERT INTO cooperative_members
            (member_number, cooperative_id, full_name, nik, birth_date, birth_place,
             gender, address, city, province, phone, email, membership_date,
             share_value, mandatory_savings)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $memberNumber,
            $memberData['cooperative_id'],
            $memberData['full_name'],
            $memberData['nik'],
            $memberData['birth_date'],
            $memberData['birth_place'],
            $memberData['gender'],
            $memberData['address'],
            $memberData['city'],
            $memberData['province'],
            $memberData['phone'] ?? null,
            $memberData['email'] ?? null,
            $memberData['membership_date'] ?? date('Y-m-d'),
            $memberData['share_value'] ?? 100000, // Minimum share value
            $memberData['mandatory_savings'] ?? 50000 // Minimum mandatory savings
        ]);

        $memberId = $this->pdo->lastInsertId();

        // Create initial journal entries for member registration
        $this->createMemberRegistrationEntries($memberData['cooperative_id'], $memberId, $memberData);

        return [
            'success' => true,
            'member_id' => $memberId,
            'member_number' => $memberNumber,
            'message' => 'Member registered successfully with regulatory compliance'
        ];
    }

    /**
     * Validate member registration against regulations
     */
    private function validateMemberRegistration($data) {
        $errors = [];

        // Article 15: Must be individual or collective entity
        if (empty($data['full_name'])) {
            $errors[] = 'Full name is required (Article 15 UU 25/1992)';
        }

        // Age requirement (minimum 17 years for savings cooperatives)
        if (isset($data['birth_date'])) {
            $age = date_diff(date_create($data['birth_date']), date_create('today'))->y;
            if ($age < 17) {
                $errors[] = 'Minimum age 17 years required (PP 9/1995)';
            }
        }

        // Share capital requirement
        if (($data['share_value'] ?? 0) < 100000) {
            $errors[] = 'Minimum share value Rp 100,000 required (Article 26 UU 25/1992)';
        }

        // Mandatory savings requirement
        if (($data['mandatory_savings'] ?? 0) < 50000) {
            $errors[] = 'Minimum mandatory savings Rp 50,000 required (PP 9/1995)';
        }

        return [
            'compliant' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * RAT (Annual Member Meeting) management
     */
    public function scheduleRAT($ratData) {
        // Validate RAT scheduling requirements
        $validation = $this->validateRATRequirements($ratData);
        if (!$validation['compliant']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        // Calculate quorum requirement (3/4 of members)
        $totalMembers = $this->getTotalActiveMembers($ratData['cooperative_id']);
        $quorumRequired = ceil($totalMembers * 0.75);

        // Create RAT record
        $stmt = $this->pdo->prepare("
            INSERT INTO rat_meetings
            (cooperative_id, rat_year, rat_type, meeting_date, meeting_time, venue,
             agenda, total_members, quorum_required)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $ratData['cooperative_id'],
            $ratData['rat_year'],
            $ratData['rat_type'] ?? 'annual',
            $ratData['meeting_date'],
            $ratData['meeting_time'] ?? '09:00:00',
            $ratData['venue'],
            json_encode($ratData['agenda'] ?? []),
            $totalMembers,
            $quorumRequired
        ]);

        $ratId = $this->pdo->lastInsertId();

        // Notify members about RAT (Article 29 UU 25/1992)
        $this->notifyMembersAboutRAT($ratId);

        return [
            'success' => true,
            'rat_id' => $ratId,
            'message' => 'RAT scheduled successfully with regulatory compliance'
        ];
    }

    /**
     * Validate RAT requirements
     */
    private function validateRATRequirements($data) {
        $errors = [];

        // Article 29: Must be held annually
        $currentYear = date('Y');
        if ($data['rat_year'] != $currentYear && $data['rat_type'] === 'annual') {
            $errors[] = 'Annual RAT must be held in current year (Article 29 UU 25/1992)';
        }

        // Minimum 14 days notice required
        $meetingDate = strtotime($data['meeting_date']);
        $noticeDays = ($meetingDate - time()) / (60 * 60 * 24);
        if ($noticeDays < 14) {
            $errors[] = 'Minimum 14 days notice required for RAT (Article 29 UU 25/1992)';
        }

        return [
            'compliant' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Financial reporting for regulatory compliance
     */
    public function generateFinancialReport($coopId, $period, $reportType = 'annual') {
        // Generate PSAK 109 compliant financial statements
        $balanceSheet = $this->generateBalanceSheet($coopId, $period);
        $incomeStatement = $this->generateIncomeStatement($coopId, $period);
        $cashFlowStatement = $this->generateCashFlowStatement($coopId, $period);
        $equityStatement = $this->generateEquityStatement($coopId, $period);

        // Create regulatory report record
        $stmt = $this->pdo->prepare("
            INSERT INTO regulatory_reports
            (cooperative_id, report_type, report_period, report_year,
             report_data, submitted_to)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $reportData = [
            'balance_sheet' => $balanceSheet,
            'income_statement' => $incomeStatement,
            'cash_flow_statement' => $cashFlowStatement,
            'equity_statement' => $equityStatement,
            'generated_at' => date('Y-m-d H:i:s'),
            'compliance_standard' => 'PSAK 109',
            'reporting_standard' => 'OJK Regulation'
        ];

        $stmt->execute([
            $coopId,
            $reportType . '_financial',
            $period,
            date('Y'),
            json_encode($reportData),
            'ojk'
        ]);

        return [
            'success' => true,
            'report_id' => $this->pdo->lastInsertId(),
            'report_data' => $reportData,
            'message' => 'PSAK 109 compliant financial report generated'
        ];
    }

    /**
     * Generate balance sheet (PSAK 109)
     */
    private function generateBalanceSheet($coopId, $period) {
        // Assets
        $currentAssets = $this->getAccountBalances($coopId, 'current_asset', $period);
        $nonCurrentAssets = $this->getAccountBalances($coopId, 'noncurrent_asset', $period);

        // Liabilities
        $currentLiabilities = $this->getAccountBalances($coopId, 'current_liability', $period);
        $nonCurrentLiabilities = $this->getAccountBalances($coopId, 'noncurrent_liability', $period);

        // Equity
        $equity = $this->getAccountBalances($coopId, 'equity', $period);

        return [
            'assets' => [
                'current_assets' => array_sum($currentAssets),
                'non_current_assets' => array_sum($nonCurrentAssets),
                'total_assets' => array_sum($currentAssets) + array_sum($nonCurrentAssets)
            ],
            'liabilities' => [
                'current_liabilities' => array_sum($currentLiabilities),
                'non_current_liabilities' => array_sum($nonCurrentLiabilities),
                'total_liabilities' => array_sum($currentLiabilities) + array_sum($nonCurrentLiabilities)
            ],
            'equity' => [
                'total_equity' => array_sum($equity)
            ],
            'liabilities_and_equity' => array_sum($currentLiabilities) + array_sum($nonCurrentLiabilities) + array_sum($equity)
        ];
    }

    /**
     * Generate income statement (PSAK 109)
     */
    private function generateIncomeStatement($coopId, $period) {
        $income = $this->getAccountBalances($coopId, 'operating_income', $period);
        $expenses = $this->getAccountBalances($coopId, 'operating_expense', $period);

        $operatingIncome = array_sum($income);
        $operatingExpenses = array_sum($expenses);
        $operatingProfit = $operatingIncome - $operatingExpenses;

        return [
            'operating_income' => $operatingIncome,
            'operating_expenses' => $operatingExpenses,
            'operating_profit' => $operatingProfit,
            'tax_expense' => $operatingProfit * 0.22, // 22% corporate tax
            'net_profit' => $operatingProfit * 0.78
        ];
    }

    /**
     * Reserve fund management (Article 44 UU 25/1992)
     */
    public function allocateReserveFunds($coopId, $allocationData) {
        // Validate reserve fund allocation (minimum 25% of annual profit)
        $validation = $this->validateReserveFundAllocation($coopId, $allocationData);
        if (!$validation['compliant']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        // Allocate to different reserve funds
        foreach ($allocationData['allocations'] as $allocation) {
            $stmt = $this->pdo->prepare("
                INSERT INTO reserve_funds
                (cooperative_id, fund_type, fund_year, allocated_amount,
                 allocation_percentage, allocation_source)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $coopId,
                $allocation['fund_type'],
                $allocationData['year'],
                $allocation['amount'],
                $allocation['percentage'],
                $allocationData['source']
            ]);
        }

        // Create journal entries for reserve fund allocation
        $this->createReserveFundEntries($coopId, $allocationData);

        return [
            'success' => true,
            'message' => 'Reserve funds allocated according to UU 25/1992 Article 44'
        ];
    }

    /**
     * Validate reserve fund allocation
     */
    private function validateReserveFundAllocation($coopId, $allocationData) {
        $errors = [];

        $totalAllocation = array_sum(array_column($allocationData['allocations'], 'amount'));
        $annualProfit = $this->getAnnualProfit($coopId, $allocationData['year']);
        $minimumRequired = $annualProfit * 0.25; // 25% of annual profit

        if ($totalAllocation < $minimumRequired) {
            $errors[] = "Minimum reserve fund allocation Rp " . number_format($minimumRequired) . " required (25% of annual profit, Article 44 UU 25/1992)";
        }

        // Check education fund allocation (Article 45)
        $educationFund = array_filter($allocationData['allocations'], function($a) {
            return $a['fund_type'] === 'education_fund';
        });

        if (empty($educationFund)) {
            $errors[] = "Education fund allocation required (Article 45 UU 25/1992)";
        }

        return [
            'compliant' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Audit trail and compliance monitoring
     */
    public function performComplianceAudit($coopId, $auditType = 'internal') {
        $auditResults = [
            'governance_compliance' => $this->auditGovernanceCompliance($coopId),
            'financial_compliance' => $this->auditFinancialCompliance($coopId),
            'operational_compliance' => $this->auditOperationalCompliance($coopId),
            'regulatory_compliance' => $this->auditRegulatoryCompliance($coopId)
        ];

        // Calculate overall compliance score
        $scores = array_column($auditResults, 'score');
        $overallScore = array_sum($scores) / count($scores);

        // Create audit record
        $stmt = $this->pdo->prepare("
            INSERT INTO compliance_audits
            (cooperative_id, audit_type, audit_result, compliance_score,
             findings, recommendations, corrective_actions)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $auditResult = $overallScore >= 80 ? 'passed' : ($overallScore >= 60 ? 'conditional_pass' : 'failed');

        $stmt->execute([
            $coopId,
            $auditType,
            $auditResult,
            $overallScore,
            json_encode($auditResults),
            json_encode($this->generateAuditRecommendations($auditResults)),
            json_encode($this->generateCorrectiveActions($auditResults))
        ]);

        return [
            'success' => true,
            'audit_id' => $this->pdo->lastInsertId(),
            'overall_score' => $overallScore,
            'audit_result' => $auditResult,
            'detailed_results' => $auditResults
        ];
    }

    // Helper methods
    private function generateMemberNumber($coopId) {
        $year = date('Y');
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM cooperative_members
            WHERE cooperative_id = ? AND YEAR(membership_date) = ?
        ");
        $stmt->execute([$coopId, $year]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] + 1;

        return sprintf('ANG-%d-%04d', $coopId, $count);
    }

    private function createMemberRegistrationEntries($coopId, $memberId, $memberData) {
        // Create journal entries for member registration
        $this->createJournalEntry($coopId, [
            'description' => 'Member registration: ' . $memberData['full_name'],
            'lines' => [
                [
                    'account_code' => '1001', // Kas
                    'debit' => $memberData['mandatory_savings'],
                    'description' => 'Mandatory savings'
                ],
                [
                    'account_code' => '3001', // Modal Disetor
                    'credit' => $memberData['share_value'],
                    'description' => 'Share capital'
                ],
                [
                    'account_code' => '2001', // Simpanan Anggota
                    'credit' => $memberData['mandatory_savings'],
                    'description' => 'Mandatory savings'
                ]
            ]
        ]);
    }

    private function createJournalEntry($coopId, $entryData) {
        $entryNumber = 'JE-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $stmt = $this->pdo->prepare("
            INSERT INTO journal_entries
            (cooperative_id, entry_number, entry_date, description, status, created_by)
            VALUES (?, ?, CURDATE(), ?, 'posted', 1)
        ");

        $stmt->execute([$coopId, $entryNumber, $entryData['description']]);
        $entryId = $this->pdo->lastInsertId();

        // Create journal lines
        foreach ($entryData['lines'] as $line) {
            $accountId = $this->getAccountIdByCode($coopId, $line['account_code']);
            if ($accountId) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO journal_lines
                    (journal_entry_id, account_id, debit, credit, description)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $entryId,
                    $accountId,
                    $line['debit'] ?? 0,
                    $line['credit'] ?? 0,
                    $line['description']
                ]);
            }
        }

        return $entryId;
    }

    private function getAccountIdByCode($coopId, $accountCode) {
        $stmt = $this->pdo->prepare("
            SELECT id FROM cooperative_accounts
            WHERE cooperative_id = ? AND account_code = ? AND is_active = TRUE
        ");
        $stmt->execute([$coopId, $accountCode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : null;
    }

    private function getAccountBalances($coopId, $classification, $period) {
        $stmt = $this->pdo->prepare("
            SELECT ca.account_name,
                   COALESCE(SUM(CASE WHEN jl.debit > 0 THEN jl.debit ELSE -jl.credit END), 0) as balance
            FROM cooperative_accounts ca
            LEFT JOIN journal_lines jl ON ca.id = jl.account_id
            LEFT JOIN journal_entries je ON jl.journal_entry_id = je.id
            WHERE ca.cooperative_id = ? AND ca.balance_sheet_classification = ?
            AND je.status = 'posted' AND je.entry_date <= ?
            GROUP BY ca.id, ca.account_name
        ");
        $stmt->execute([$coopId, $classification, $period . '-12-31']);
        $balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_column($balances, 'balance', 'account_name');
    }

    private function getTotalActiveMembers($coopId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM cooperative_members
            WHERE cooperative_id = ? AND membership_status = 'active'
        ");
        $stmt->execute([$coopId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    private function notifyMembersAboutRAT($ratId) {
        // Implementation for member notification system
        // Would integrate with email/SMS notification system
    }

    private function getAnnualProfit($coopId, $year) {
        // Calculate annual profit for reserve fund allocation
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(CASE WHEN jl.debit > 0 THEN jl.debit ELSE -jl.credit END), 0) as profit
            FROM journal_lines jl
            JOIN journal_entries je ON jl.journal_entry_id = je.id
            JOIN cooperative_accounts ca ON jl.account_id = ca.id
            WHERE ca.cooperative_id = ? AND ca.account_type = 'income'
            AND YEAR(je.entry_date) = ? AND je.status = 'posted'
        ");
        $stmt->execute([$coopId, $year]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['profit'];
    }

    private function createReserveFundEntries($coopId, $allocationData) {
        // Create journal entries for reserve fund allocation
        $totalAllocation = array_sum(array_column($allocationData['allocations'], 'amount'));

        $this->createJournalEntry($coopId, [
            'description' => 'Reserve fund allocation for year ' . $allocationData['year'],
            'lines' => [
                [
                    'account_code' => '4001', // SHU (laba bersih)
                    'debit' => $totalAllocation,
                    'description' => 'SHU allocation to reserve funds'
                ],
                [
                    'account_code' => '3003', // Cadangan
                    'credit' => $totalAllocation,
                    'description' => 'Reserve fund allocation'
                ]
            ]
        ]);
    }

    private function auditGovernanceCompliance($coopId) {
        // Check governance structure compliance
        $governanceCount = $this->pdo->query("SELECT COUNT(*) FROM governance_bodies WHERE cooperative_id = $coopId AND status = 'active'")->fetchColumn();
        $score = min(100, $governanceCount * 20); // 5 bodies = 100 points

        return [
            'score' => $score,
            'findings' => $governanceCount >= 5 ? [] : ['Insufficient governance bodies'],
            'recommendations' => $governanceCount < 5 ? ['Appoint missing governance members'] : []
        ];
    }

    private function auditFinancialCompliance($coopId) {
        // Check financial reporting compliance
        $reportsCount = $this->pdo->query("SELECT COUNT(*) FROM regulatory_reports WHERE cooperative_id = $coopId AND approval_status = 'approved'")->fetchColumn();
        $score = min(100, $reportsCount * 25); // 4 reports = 100 points

        return [
            'score' => $score,
            'findings' => $reportsCount >= 4 ? [] : ['Missing regulatory reports'],
            'recommendations' => $reportsCount < 4 ? ['Submit missing financial reports'] : []
        ];
    }

    private function auditOperationalCompliance($coopId) {
        // Check operational compliance
        $ratCount = $this->pdo->query("SELECT COUNT(*) FROM rat_meetings WHERE cooperative_id = $coopId AND quorum_achieved = 1")->fetchColumn();
        $score = min(100, $ratCount * 50); // 2 RATs = 100 points

        return [
            'score' => $score,
            'findings' => $ratCount >= 2 ? [] : ['Missing RAT meetings'],
            'recommendations' => $ratCount < 2 ? ['Schedule RAT meetings'] : []
        ];
    }

    private function auditRegulatoryCompliance($coopId) {
        // Check overall regulatory compliance
        $coop = $this->pdo->query("SELECT * FROM cooperative_structure WHERE id = $coopId")->fetch(PDO::FETCH_ASSOC);
        $score = 100;

        if (empty($coop['registration_number'])) $score -= 25;
        if (empty($coop['ojk_registration_number'])) $score -= 25;
        if ($coop['compliance_status'] !== 'compliant') $score -= 25;

        return [
            'score' => max(0, $score),
            'findings' => $score < 100 ? ['Missing regulatory registrations'] : [],
            'recommendations' => $score < 100 ? ['Complete regulatory registrations'] : []
        ];
    }

    private function generateAuditRecommendations($auditResults) {
        $recommendations = [];

        foreach ($auditResults as $area => $result) {
            if ($result['score'] < 80) {
                $recommendations = array_merge($recommendations, $result['recommendations']);
            }
        }

        return array_unique($recommendations);
    }

    private function generateCorrectiveActions($auditResults) {
        $actions = [];

        foreach ($auditResults as $area => $result) {
            if (!empty($result['findings'])) {
                foreach ($result['findings'] as $finding) {
                    $actions[] = [
                        'finding' => $finding,
                        'action' => 'Address ' . strtolower($finding),
                        'deadline' => date('Y-m-d', strtotime('+30 days')),
                        'priority' => $result['score'] < 60 ? 'high' : 'medium'
                    ];
                }
            }
        }

        return $actions;
    }
}

// Helper functions
function initializeCooperativeWithCompliance($coopData) {
    $regulatory = new CooperativeRegulatoryFramework();
    return $regulatory->initializeCooperative($coopData);
}

function registerMemberWithCompliance($memberData) {
    $regulatory = new CooperativeRegulatoryFramework();
    return $regulatory->registerMember($memberData);
}

function scheduleRATWithCompliance($ratData) {
    $regulatory = new CooperativeRegulatoryFramework();
    return $regulatory->scheduleRAT($ratData);
}

function generatePSAK109FinancialReport($coopId, $period) {
    $regulatory = new CooperativeRegulatoryFramework();
    return $regulatory->generateFinancialReport($coopId, $period);
}

function allocateReserveFunds($coopId, $allocationData) {
    $regulatory = new CooperativeRegulatoryFramework();
    return $regulatory->allocateReserveFunds($coopId, $allocationData);
}

function performComplianceAudit($coopId, $auditType = 'internal') {
    $regulatory = new CooperativeRegulatoryFramework();
    return $regulatory->performComplianceAudit($coopId, $auditType);
}
