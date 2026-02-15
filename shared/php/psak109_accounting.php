<?php
/**
 * PSAK 109 Financial Accounting Standards for Cooperatives
 * Implementation of Indonesian Financial Accounting Standards for Cooperatives
 */

class PSAK109Accounting {
    private $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
    }

    /**
     * Initialize PSAK 109 compliant chart of accounts for cooperative
     */
    public function initializeCooperativeChartOfAccounts($coopId) {
        $accounts = $this->getPSAK109ChartOfAccounts();

        foreach ($accounts as $account) {
            $stmt = $this->pdo->prepare("
                INSERT INTO cooperative_accounts
                (cooperative_id, account_code, account_name, account_type,
                 balance_sheet_classification, income_statement_classification,
                 normal_balance, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)
            ");

            $stmt->execute([
                $coopId,
                $account['code'],
                $account['name'],
                $account['type'],
                $account['bs_classification'],
                $account['is_classification'],
                $account['normal_balance']
            ]);
        }

        return ['success' => true, 'accounts_created' => count($accounts)];
    }

    /**
     * Generate PSAK 109 compliant financial statements
     */
    public function generateFinancialStatements($coopId, $period, $statementType = 'complete') {
        $statements = [];

        // Balance Sheet (Neraca)
        if ($statementType === 'complete' || $statementType === 'balance_sheet') {
            $statements['balance_sheet'] = $this->generateBalanceSheet($coopId, $period);
        }

        // Income Statement (Laporan Laba Rugi)
        if ($statementType === 'complete' || $statementType === 'income_statement') {
            $statements['income_statement'] = $this->generateIncomeStatement($coopId, $period);
        }

        // Statement of Changes in Equity (Laporan Perubahan Ekuitas)
        if ($statementType === 'complete' || $statementType === 'equity_statement') {
            $statements['equity_statement'] = $this->generateEquityStatement($coopId, $period);
        }

        // Cash Flow Statement (Laporan Arus Kas)
        if ($statementType === 'complete' || $statementType === 'cash_flow') {
            $statements['cash_flow_statement'] = $this->generateCashFlowStatement($coopId, $period);
        }

        // Notes to Financial Statements
        $statements['notes'] = $this->generateFinancialStatementNotes($coopId, $period);

        return [
            'success' => true,
            'period' => $period,
            'generated_at' => date('Y-m-d H:i:s'),
            'compliance_standard' => 'PSAK 109',
            'statements' => $statements
        ];
    }

    /**
     * Generate Balance Sheet (Neraca) - PSAK 109 compliant
     */
    private function generateBalanceSheet($coopId, $period) {
        // Assets
        $currentAssets = $this->getAccountBalancesByClassification($coopId, 'current_asset', $period);
        $nonCurrentAssets = $this->getAccountBalancesByClassification($coopId, 'noncurrent_asset', $period);

        // Liabilities
        $currentLiabilities = $this->getAccountBalancesByClassification($coopId, 'current_liability', $period);
        $nonCurrentLiabilities = $this->getAccountBalancesByClassification($coopId, 'noncurrent_liability', $period);

        // Equity
        $equity = $this->getAccountBalancesByClassification($coopId, 'equity', $period);

        // Calculate totals
        $totalAssets = array_sum($currentAssets) + array_sum($nonCurrentAssets);
        $totalLiabilities = array_sum($currentLiabilities) + array_sum($nonCurrentLiabilities);
        $totalEquity = array_sum($equity);
        $liabilitiesAndEquity = $totalLiabilities + $totalEquity;

        return [
            'header' => [
                'title' => 'NERACA',
                'entity' => 'KSP Samosir',
                'period' => 'Per ' . date('t F Y', strtotime($period . '-01')),
                'currency' => 'Rupiah'
            ],
            'assets' => [
                'current_assets' => [
                    'total' => array_sum($currentAssets),
                    'accounts' => $currentAssets
                ],
                'non_current_assets' => [
                    'total' => array_sum($nonCurrentAssets),
                    'accounts' => $nonCurrentAssets
                ],
                'total_assets' => $totalAssets
            ],
            'liabilities_and_equity' => [
                'liabilities' => [
                    'current_liabilities' => [
                        'total' => array_sum($currentLiabilities),
                        'accounts' => $currentLiabilities
                    ],
                    'non_current_liabilities' => [
                        'total' => array_sum($nonCurrentLiabilities),
                        'accounts' => $nonCurrentLiabilities
                    ],
                    'total_liabilities' => $totalLiabilities
                ],
                'equity' => [
                    'total' => $totalEquity,
                    'accounts' => $equity
                ],
                'total_liabilities_and_equity' => $liabilitiesAndEquity
            ],
            'balance_check' => ($totalAssets === $liabilitiesAndEquity) ? 'Balanced' : 'Out of Balance'
        ];
    }

    /**
     * Generate Income Statement (Laporan Laba Rugi) - PSAK 109 compliant
     */
    private function generateIncomeStatement($coopId, $period) {
        // Operating Income
        $operatingIncome = $this->getAccountBalancesByClassification($coopId, 'operating_income', $period);

        // Operating Expenses
        $operatingExpenses = $this->getAccountBalancesByClassification($coopId, 'operating_expense', $period);

        // Calculate operating profit
        $totalOperatingIncome = array_sum($operatingIncome);
        $totalOperatingExpenses = array_sum($operatingExpenses);
        $operatingProfit = $totalOperatingIncome - $totalOperatingExpenses;

        // Other Income/Expenses
        $otherIncome = $this->getAccountBalancesByClassification($coopId, 'other_income', $period);
        $otherExpenses = $this->getAccountBalancesByClassification($coopId, 'other_expense', $period);

        $totalOtherIncome = array_sum($otherIncome);
        $totalOtherExpenses = array_sum($otherExpenses);
        $otherProfit = $totalOtherIncome - $totalOtherExpenses;

        // Profit before tax
        $profitBeforeTax = $operatingProfit + $otherProfit;

        // Tax expense (22% corporate tax rate)
        $taxExpense = $profitBeforeTax * 0.22;

        // Net profit
        $netProfit = $profitBeforeTax - $taxExpense;

        return [
            'header' => [
                'title' => 'LAPORAN LABA RUGI',
                'entity' => 'KSP Samosir',
                'period' => 'Untuk periode yang berakhir ' . date('t F Y', strtotime($period . '-01')),
                'currency' => 'Rupiah'
            ],
            'operating_income' => [
                'total' => $totalOperatingIncome,
                'accounts' => $operatingIncome
            ],
            'operating_expenses' => [
                'total' => $totalOperatingExpenses,
                'accounts' => $operatingExpenses
            ],
            'operating_profit' => $operatingProfit,
            'other_income' => [
                'total' => $totalOtherIncome,
                'accounts' => $otherIncome
            ],
            'other_expenses' => [
                'total' => $totalOtherExpenses,
                'accounts' => $otherExpenses
            ],
            'other_profit' => $otherProfit,
            'profit_before_tax' => $profitBeforeTax,
            'tax_expense' => $taxExpense,
            'net_profit' => $netProfit
        ];
    }

    /**
     * Generate Statement of Changes in Equity - PSAK 109 compliant
     */
    private function generateEquityStatement($coopId, $period) {
        // Get equity accounts balances
        $equityAccounts = $this->getEquityAccountMovements($coopId, $period);

        // Calculate changes
        $openingBalance = $this->getEquityOpeningBalance($coopId, $period);
        $netProfit = $this->getNetProfit($coopId, $period);
        $dividends = $this->getDividendsPaid($coopId, $period);
        $capitalContributions = $this->getCapitalContributions($coopId, $period);

        $closingBalance = $openingBalance + $netProfit - $dividends + $capitalContributions;

        return [
            'header' => [
                'title' => 'LAPORAN PERUBAHAN EKUITAS',
                'entity' => 'KSP Samosir',
                'period' => 'Untuk periode yang berakhir ' . date('t F Y', strtotime($period . '-01')),
                'currency' => 'Rupiah'
            ],
            'opening_balance' => $openingBalance,
            'changes' => [
                'net_profit' => $netProfit,
                'dividends_paid' => -$dividends,
                'capital_contributions' => $capitalContributions
            ],
            'closing_balance' => $closingBalance,
            'equity_accounts' => $equityAccounts
        ];
    }

    /**
     * Generate Cash Flow Statement - PSAK 109 compliant
     */
    private function generateCashFlowStatement($coopId, $period) {
        // Operating activities
        $operatingCashFlows = $this->calculateOperatingCashFlows($coopId, $period);

        // Investing activities
        $investingCashFlows = $this->calculateInvestingCashFlows($coopId, $period);

        // Financing activities
        $financingCashFlows = $this->calculateFinancingCashFlows($coopId, $period);

        $netCashFlow = $operatingCashFlows['net'] + $investingCashFlows['net'] + $financingCashFlows['net'];

        $openingCash = $this->getOpeningCashBalance($coopId, $period);
        $closingCash = $openingCash + $netCashFlow;

        return [
            'header' => [
                'title' => 'LAPORAN ARUS KAS',
                'entity' => 'KSP Samosir',
                'period' => 'Untuk periode yang berakhir ' . date('t F Y', strtotime($period . '-01')),
                'currency' => 'Rupiah'
            ],
            'operating_activities' => $operatingCashFlows,
            'investing_activities' => $investingCashFlows,
            'financing_activities' => $financingCashFlows,
            'net_cash_flow' => $netCashFlow,
            'opening_cash_balance' => $openingCash,
            'closing_cash_balance' => $closingCash
        ];
    }

    /**
     * Generate Notes to Financial Statements
     */
    private function generateFinancialStatementNotes($coopId, $period) {
        $cooperative = $this->getCooperativeInfo($coopId);

        return [
            'note_1' => [
                'title' => 'Informasi Umum',
                'content' => [
                    'Nama entitas' => $cooperative['cooperative_name'],
                    'Alamat' => $cooperative['address'],
                    'Bidang usaha' => $cooperative['business_sector'],
                    'Tahun berdiri' => $cooperative['establishment_date']
                ]
            ],
            'note_2' => [
                'title' => 'Kebijakan Akuntansi',
                'content' => [
                    'Standar akuntansi' => 'PSAK 109 - Akuntansi Koperasi',
                    'Mata uang pelaporan' => 'Rupiah Indonesia',
                    'Basis akuntansi' => 'Akrual',
                    'Kebijakan penyusutan' => 'Garis lurus (Straight Line)'
                ]
            ],
            'note_3' => [
                'title' => 'Penjelasan atas Pos-pos Neraca',
                'content' => $this->getBalanceSheetNotes($coopId, $period)
            ],
            'note_4' => [
                'title' => 'Penjelasan atas Laba Rugi',
                'content' => $this->getIncomeStatementNotes($coopId, $period)
            ],
            'note_5' => [
                'title' => 'Komitmen dan Kontinjensi',
                'content' => $this->getCommitmentsAndContingencies($coopId, $period)
            ]
        ];
    }

    /**
     * Record journal entry with PSAK 109 compliance
     */
    public function recordJournalEntry($coopId, $entryData) {
        // Validate entry follows double-entry bookkeeping
        if (!$this->validateJournalEntry($entryData)) {
            return ['success' => false, 'error' => 'Invalid journal entry - debits and credits must balance'];
        }

        // Create journal entry header
        $entryNumber = $this->generateEntryNumber($coopId, $entryData['date']);

        $stmt = $this->pdo->prepare("
            INSERT INTO journal_entries
            (cooperative_id, entry_number, entry_date, description, status, created_by)
            VALUES (?, ?, ?, ?, 'draft', ?)
        ");

        $stmt->execute([
            $coopId,
            $entryNumber,
            $entryData['date'],
            $entryData['description'],
            $entryData['created_by'] ?? 1
        ]);

        $entryId = $this->pdo->lastInsertId();

        // Create journal entry lines
        foreach ($entryData['lines'] as $line) {
            $accountId = $this->getAccountIdByCode($coopId, $line['account_code']);
            if (!$accountId) {
                // Create account if it doesn't exist
                $accountId = $this->createAccountFromCode($coopId, $line['account_code']);
            }

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
                $line['description'] ?? ''
            ]);
        }

        return [
            'success' => true,
            'entry_id' => $entryId,
            'entry_number' => $entryNumber,
            'message' => 'Journal entry recorded with PSAK 109 compliance'
        ];
    }

    /**
     * Post journal entry (make it final)
     */
    public function postJournalEntry($entryId) {
        // Validate entry can be posted
        if (!$this->canPostEntry($entryId)) {
            return ['success' => false, 'error' => 'Entry cannot be posted'];
        }

        // Update status to posted
        $stmt = $this->pdo->prepare("
            UPDATE journal_entries
            SET status = 'posted', posted_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$entryId]);

        // Update account balances
        $this->updateAccountBalances($entryId);

        return ['success' => true, 'message' => 'Journal entry posted successfully'];
    }

    /**
     * Generate trial balance
     */
    public function generateTrialBalance($coopId, $period) {
        $stmt = $this->pdo->prepare("
            SELECT
                ca.account_code,
                ca.account_name,
                COALESCE(SUM(jl.debit), 0) as total_debit,
                COALESCE(SUM(jl.credit), 0) as total_credit,
                (COALESCE(SUM(jl.debit), 0) - COALESCE(SUM(jl.credit), 0)) as balance
            FROM cooperative_accounts ca
            LEFT JOIN journal_lines jl ON ca.id = jl.account_id
            LEFT JOIN journal_entries je ON jl.journal_entry_id = je.id
            WHERE ca.cooperative_id = ? AND je.status = 'posted'
            AND je.entry_date <= ?
            GROUP BY ca.id, ca.account_code, ca.account_name
            ORDER BY ca.account_code
        ");

        $stmt->execute([$coopId, $period . '-12-31']);
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalDebits = array_sum(array_column($accounts, 'total_debit'));
        $totalCredits = array_sum(array_column($accounts, 'total_credit'));

        return [
            'period' => $period,
            'accounts' => $accounts,
            'totals' => [
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'difference' => $totalDebits - $totalCredits
            ],
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01
        ];
    }

    /**
     * Close accounting period
     */
    public function closeAccountingPeriod($coopId, $period) {
        // Validate all entries are posted
        $unpostedCount = (fetchRow("
            SELECT COUNT(*) as count FROM journal_entries
            WHERE cooperative_id = ? AND entry_date LIKE ? AND status != 'posted'
        ", [$coopId, $period . '%'], 's') ?? [])['count'] ?? 0;

        if ($unpostedCount > 0) {
            return ['success' => false, 'error' => 'Cannot close period with unposted entries'];
        }

        // Generate closing entries
        $closingEntries = $this->generateClosingEntries($coopId, $period);

        // Execute closing entries
        foreach ($closingEntries as $entry) {
            $this->recordJournalEntry($coopId, $entry);
        }

        // Mark period as closed
        $stmt = $this->pdo->prepare("
            INSERT INTO financial_periods
            (cooperative_id, period_year, period_type, period_number,
             start_date, end_date, status, closed_at)
            VALUES (?, ?, 'annual', 1, ?, ?, 'closed', NOW())
            ON DUPLICATE KEY UPDATE
            status = 'closed', closed_at = NOW()
        ");

        $stmt->execute([
            $coopId,
            date('Y', strtotime($period . '-01')),
            $period . '-01-01',
            $period . '-12-31'
        ]);

        return [
            'success' => true,
            'message' => 'Accounting period closed successfully',
            'closing_entries' => count($closingEntries)
        ];
    }

    // Private helper methods
    private function getPSAK109ChartOfAccounts() {
        return [
            // Assets
            ['code' => '1001', 'name' => 'Kas', 'type' => 'asset', 'bs_classification' => 'current_asset', 'is_classification' => null, 'normal_balance' => 'debit'],
            ['code' => '1002', 'name' => 'Bank', 'type' => 'asset', 'bs_classification' => 'current_asset', 'is_classification' => null, 'normal_balance' => 'debit'],
            ['code' => '1101', 'name' => 'Piutang Anggota', 'type' => 'asset', 'bs_classification' => 'current_asset', 'is_classification' => null, 'normal_balance' => 'debit'],
            ['code' => '1102', 'name' => 'Piutang Non-Anggota', 'type' => 'asset', 'bs_classification' => 'current_asset', 'is_classification' => null, 'normal_balance' => 'debit'],
            ['code' => '1201', 'name' => 'Persediaan', 'type' => 'asset', 'bs_classification' => 'current_asset', 'is_classification' => null, 'normal_balance' => 'debit'],

            // Fixed Assets
            ['code' => '1501', 'name' => 'Tanah', 'type' => 'asset', 'bs_classification' => 'noncurrent_asset', 'is_classification' => null, 'normal_balance' => 'debit'],
            ['code' => '1502', 'name' => 'Bangunan', 'type' => 'asset', 'bs_classification' => 'noncurrent_asset', 'is_classification' => null, 'normal_balance' => 'debit'],
            ['code' => '1503', 'name' => 'Peralatan', 'type' => 'asset', 'bs_classification' => 'noncurrent_asset', 'is_classification' => null, 'normal_balance' => 'debit'],
            ['code' => '1504', 'name' => 'Akumulasi Penyusutan', 'type' => 'asset', 'bs_classification' => 'noncurrent_asset', 'is_classification' => null, 'normal_balance' => 'credit'],

            // Liabilities
            ['code' => '2001', 'name' => 'Simpanan Anggota', 'type' => 'liability', 'bs_classification' => 'current_liability', 'is_classification' => null, 'normal_balance' => 'credit'],
            ['code' => '2002', 'name' => 'Simpanan Sukarela', 'type' => 'liability', 'bs_classification' => 'current_liability', 'is_classification' => null, 'normal_balance' => 'credit'],
            ['code' => '2101', 'name' => 'Pinjaman Bank', 'type' => 'liability', 'bs_classification' => 'current_liability', 'is_classification' => null, 'normal_balance' => 'credit'],
            ['code' => '2102', 'name' => 'Pinjaman Non-Bank', 'type' => 'liability', 'bs_classification' => 'current_liability', 'is_classification' => null, 'normal_balance' => 'credit'],

            // Equity
            ['code' => '3001', 'name' => 'Modal Disetor', 'type' => 'equity', 'bs_classification' => 'equity', 'is_classification' => null, 'normal_balance' => 'credit'],
            ['code' => '3002', 'name' => 'Modal Penyertaan', 'type' => 'equity', 'bs_classification' => 'equity', 'is_classification' => null, 'normal_balance' => 'credit'],
            ['code' => '3003', 'name' => 'Cadangan', 'type' => 'equity', 'bs_classification' => 'equity', 'is_classification' => null, 'normal_balance' => 'credit'],
            ['code' => '3004', 'name' => 'SHU Ditahan', 'type' => 'equity', 'bs_classification' => 'equity', 'is_classification' => null, 'normal_balance' => 'credit'],
            ['code' => '3005', 'name' => 'SHU Tahun Berjalan', 'type' => 'equity', 'bs_classification' => 'equity', 'is_classification' => null, 'normal_balance' => 'credit'],

            // Income
            ['code' => '4001', 'name' => 'Pendapatan Bunga Pinjaman', 'type' => 'income', 'bs_classification' => null, 'is_classification' => 'operating_income', 'normal_balance' => 'credit'],
            ['code' => '4002', 'name' => 'Pendapatan Administrasi', 'type' => 'income', 'bs_classification' => null, 'is_classification' => 'operating_income', 'normal_balance' => 'credit'],
            ['code' => '4003', 'name' => 'Pendapatan Jasa', 'type' => 'income', 'bs_classification' => null, 'is_classification' => 'operating_income', 'normal_balance' => 'credit'],
            ['code' => '4101', 'name' => 'Pendapatan Lain-lain', 'type' => 'income', 'bs_classification' => null, 'is_classification' => 'other_income', 'normal_balance' => 'credit'],

            // Expenses
            ['code' => '5001', 'name' => 'Beban Operasional', 'type' => 'expense', 'bs_classification' => null, 'is_classification' => 'operating_expense', 'normal_balance' => 'debit'],
            ['code' => '5002', 'name' => 'Beban Bunga Simpanan', 'type' => 'expense', 'bs_classification' => null, 'is_classification' => 'operating_expense', 'normal_balance' => 'debit'],
            ['code' => '5003', 'name' => 'Beban Administrasi', 'type' => 'expense', 'bs_classification' => null, 'is_classification' => 'operating_expense', 'normal_balance' => 'debit'],
            ['code' => '5101', 'name' => 'Beban Cadangan', 'type' => 'expense', 'bs_classification' => null, 'is_classification' => 'operating_expense', 'normal_balance' => 'debit'],
            ['code' => '5102', 'name' => 'Beban Penyusutan', 'type' => 'expense', 'bs_classification' => null, 'is_classification' => 'operating_expense', 'normal_balance' => 'debit'],
            ['code' => '5201', 'name' => 'Beban Lain-lain', 'type' => 'expense', 'bs_classification' => null, 'is_classification' => 'other_expense', 'normal_balance' => 'debit'],
            ['code' => '5301', 'name' => 'Beban Pajak', 'type' => 'expense', 'bs_classification' => null, 'is_classification' => 'other_expense', 'normal_balance' => 'debit']
        ];
    }

    private function getAccountBalancesByClassification($coopId, $classification, $period) {
        $stmt = $this->pdo->prepare("
            SELECT ca.account_name,
                   COALESCE(SUM(CASE WHEN ca.normal_balance = 'debit' THEN jl.debit - jl.credit ELSE jl.credit - jl.debit END), 0) as balance
            FROM cooperative_accounts ca
            LEFT JOIN journal_lines jl ON ca.id = jl.account_id
            LEFT JOIN journal_entries je ON jl.journal_entry_id = je.id
            WHERE ca.cooperative_id = ?
            AND (ca.balance_sheet_classification = ? OR ca.income_statement_classification = ?)
            AND je.status = 'posted' AND je.entry_date <= ?
            GROUP BY ca.id, ca.account_name
            HAVING balance != 0
            ORDER BY ca.account_code
        ");

        $stmt->execute([$coopId, $classification, $classification, $period . '-12-31']);
        $balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_column($balances, 'balance', 'account_name');
    }

    private function validateJournalEntry($entryData) {
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($entryData['lines'] as $line) {
            $totalDebits += $line['debit'] ?? 0;
            $totalCredits += $line['credit'] ?? 0;
        }

        return abs($totalDebits - $totalCredits) < 0.01;
    }

    private function generateEntryNumber($coopId, $date) {
        $year = date('Y', strtotime($date));
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM journal_entries
            WHERE cooperative_id = ? AND YEAR(entry_date) = ?
        ");
        $stmt->execute([$coopId, $year]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] + 1;

        return sprintf('JE-%d-%04d', $year, $count);
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

    private function createAccountFromCode($coopId, $accountCode) {
        // Create account with default settings based on code
        $accountInfo = $this->getAccountInfoFromCode($accountCode);

        $stmt = $this->pdo->prepare("
            INSERT INTO cooperative_accounts
            (cooperative_id, account_code, account_name, account_type,
             balance_sheet_classification, income_statement_classification,
             normal_balance, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)
        ");

        $stmt->execute([
            $coopId,
            $accountCode,
            $accountInfo['name'],
            $accountInfo['type'],
            $accountInfo['bs_classification'],
            $accountInfo['is_classification'],
            $accountInfo['normal_balance']
        ]);

        return $this->pdo->lastInsertId();
    }

    private function getAccountInfoFromCode($accountCode) {
        $accounts = $this->getPSAK109ChartOfAccounts();
        foreach ($accounts as $account) {
            if ($account['code'] === $accountCode) {
                return $account;
            }
        }

        // Default account info
        return [
            'name' => 'Account ' . $accountCode,
            'type' => 'asset',
            'bs_classification' => 'current_asset',
            'is_classification' => null,
            'normal_balance' => 'debit'
        ];
    }

    private function canPostEntry($entryId) {
        $entry = fetchRow("SELECT * FROM journal_entries WHERE id = ?", [$entryId], 'i');
        return $entry && $entry['status'] === 'draft';
    }

    private function updateAccountBalances($entryId) {
        // This would update running balances - simplified implementation
        // In production, would maintain account balance tables
    }

    // Placeholder methods for financial statement generation
    private function getEquityAccountMovements($coopId, $period) { return []; }
    private function getEquityOpeningBalance($coopId, $period) { return 100000000; }
    private function getNetProfit($coopId, $period) { return 25000000; }
    private function getDividendsPaid($coopId, $period) { return 5000000; }
    private function getCapitalContributions($coopId, $period) { return 10000000; }
    private function calculateOperatingCashFlows($coopId, $period) { return ['net' => 30000000, 'details' => []]; }
    private function calculateInvestingCashFlows($coopId, $period) { return ['net' => -10000000, 'details' => []]; }
    private function calculateFinancingCashFlows($coopId, $period) { return ['net' => -5000000, 'details' => []]; }
    private function getOpeningCashBalance($coopId, $period) { return 50000000; }
    private function getCooperativeInfo($coopId) { return ['cooperative_name' => 'KSP Samosir', 'address' => 'Jl. Sudirman No. 1', 'business_sector' => 'Simpan Pinjam', 'establishment_date' => '2020']; }
    private function getBalanceSheetNotes($coopId, $period) { return ['Significant accounting policies and estimates used in preparation of financial statements.']; }
    private function getIncomeStatementNotes($coopId, $period) { return ['Revenue recognition and expense classification details.']; }
    private function getCommitmentsAndContingencies($coopId, $period) { return ['Legal commitments and contingent liabilities disclosure.']; }
}

// Helper functions
function initializePSAK109ChartOfAccounts($coopId) {
    $psak109 = new PSAK109Accounting();
    return $psak109->initializeCooperativeChartOfAccounts($coopId);
}

function generatePSAK109FinancialStatements($coopId, $period, $statementType = 'complete') {
    $psak109 = new PSAK109Accounting();
    return $psak109->generateFinancialStatements($coopId, $period, $statementType);
}

function recordPSAK109JournalEntry($coopId, $entryData) {
    $psak109 = new PSAK109Accounting();
    return $psak109->recordJournalEntry($coopId, $entryData);
}

function postPSAK109JournalEntry($entryId) {
    $psak109 = new PSAK109Accounting();
    return $psak109->postJournalEntry($entryId);
}

function generatePSAK109TrialBalance($coopId, $period) {
    $psak109 = new PSAK109Accounting();
    return $psak109->generateTrialBalance($coopId, $period);
}

function closePSAK109AccountingPeriod($coopId, $period) {
    $psak109 = new PSAK109Accounting();
    return $psak109->closeAccountingPeriod($coopId, $period);
}
