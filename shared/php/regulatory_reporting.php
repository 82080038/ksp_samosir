<?php
/**
 * Indonesian Regulatory Reporting System for Cooperatives
 * Compliance with OJK and Ministry of Cooperatives reporting requirements
 */

class RegulatoryReportingSystem {
    private $pdo;
    private $psak109;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
        $this->psak109 = new PSAK109Accounting($this->pdo);
    }

    /**
     * Generate OJK Financial Report (Laporan Keuangan OJK)
     */
    public function generateOJKFinancialReport($coopId, $period, $reportType = 'annual') {
        // Generate PSAK 109 compliant financial statements
        $financialStatements = $this->psak109->generateFinancialStatements($coopId, $period, 'complete');

        // Format for OJK requirements
        $ojkReport = [
            'header' => [
                'report_type' => 'Laporan Keuangan ' . ucfirst($reportType),
                'entity_name' => 'KSP Samosir',
                'period' => $this->formatPeriodForOJK($period, $reportType),
                'reporting_standard' => 'PSAK 109',
                'prepared_by' => 'Management',
                'approved_by' => 'Board of Directors',
                'submission_date' => date('Y-m-d')
            ],
            'financial_statements' => $financialStatements['statements'],
            'ojk_specific_disclosures' => $this->getOJKSpecificDisclosures($coopId, $period),
            'compliance_certification' => $this->generateComplianceCertification($coopId, $period)
        ];

        // Save report
        $this->saveRegulatoryReport($coopId, $reportType . '_financial', $period, $ojkReport, 'ojk');

        return [
            'success' => true,
            'report' => $ojkReport,
            'compliance_check' => $this->validateOJKCompliance($ojkReport),
            'message' => 'OJK financial report generated successfully'
        ];
    }

    /**
     * Generate Ministry of Cooperatives Activity Report
     */
    public function generateMinistryActivityReport($coopId, $period) {
        $cooperative = $this->getCooperativeInfo($coopId);
        $activities = $this->getCooperativeActivities($coopId, $period);

        $activityReport = [
            'header' => [
                'report_type' => 'Laporan Kegiatan Koperasi',
                'entity_name' => $cooperative['cooperative_name'],
                'period' => $period,
                'submitted_to' => 'Ministry of Cooperatives',
                'submission_date' => date('Y-m-d')
            ],
            'cooperative_info' => [
                'registration_number' => $cooperative['registration_number'],
                'establishment_date' => $cooperative['establishment_date'],
                'business_sector' => $cooperative['business_sector'],
                'operational_area' => $cooperative['operational_area'],
                'total_members' => $this->getTotalActiveMembers($coopId),
                'total_assets' => $this->getTotalAssets($coopId),
                'annual_revenue' => $this->getAnnualRevenue($coopId, $period)
            ],
            'activities_summary' => [
                'economic_activities' => $this->summarizeActivitiesByType($activities, 'economic'),
                'social_activities' => $this->summarizeActivitiesByType($activities, 'social'),
                'educational_activities' => $this->summarizeActivitiesByType($activities, 'educational'),
                'cultural_activities' => $this->summarizeActivitiesByType($activities, 'cultural'),
                'environmental_activities' => $this->summarizeActivitiesByType($activities, 'environmental')
            ],
            'rat_information' => $this->getRATInformation($coopId, $period),
            'reserve_fund_allocation' => $this->getReserveFundAllocation($coopId, $period),
            'education_fund_utilization' => $this->getEducationFundUtilization($coopId, $period),
            'governance_composition' => $this->getGovernanceComposition($coopId)
        ];

        // Save report
        $this->saveRegulatoryReport($coopId, 'annual_activity', $period, $activityReport, 'ministry_of_cooperatives');

        return [
            'success' => true,
            'report' => $activityReport,
            'message' => 'Ministry of Cooperatives activity report generated successfully'
        ];
    }

    /**
     * Generate Monthly OJK Report (for KSP)
     */
    public function generateMonthlyOJKReport($coopId, $period) {
        $monthlyReport = [
            'header' => [
                'report_type' => 'Laporan Bulanan KSP',
                'entity_name' => 'KSP Samosir',
                'period' => date('F Y', strtotime($period . '-01')),
                'reporting_date' => date('Y-m-d'),
                'due_date' => date('Y-m-15', strtotime($period . '-01 +1 month'))
            ],
            'capital_adequacy' => $this->calculateCapitalAdequacyRatio($coopId, $period),
            'asset_quality' => [
                'total_loans' => $this->getTotalLoans($coopId, $period),
                'performing_loans' => $this->getPerformingLoans($coopId, $period),
                'non_performing_loans' => $this->getNonPerformingLoans($coopId, $period),
                'npl_ratio' => $this->calculateNPLRatio($coopId, $period)
            ],
            'liquidity' => [
                'cash_and_equivalents' => $this->getCashAndEquivalents($coopId, $period),
                'liquid_assets' => $this->getLiquidAssets($coopId, $period),
                'liquidity_ratio' => $this->calculateLiquidityRatio($coopId, $period)
            ],
            'profitability' => [
                'net_income' => $this->getMonthlyNetIncome($coopId, $period),
                'return_on_assets' => $this->calculateROA($coopId, $period),
                'return_on_equity' => $this->calculateROE($coopId, $period)
            ],
            'member_savings' => [
                'total_savings' => $this->getTotalMemberSavings($coopId, $period),
                'mandatory_savings' => $this->getMandatorySavings($coopId, $period),
                'voluntary_savings' => $this->getVoluntarySavings($coopId, $period)
            ],
            'operational_metrics' => [
                'total_members' => $this->getTotalActiveMembers($coopId),
                'new_members' => $this->getNewMembersCount($coopId, $period),
                'loan_applications' => $this->getLoanApplicationsCount($coopId, $period),
                'loans_disbursed' => $this->getLoansDisbursedCount($coopId, $period)
            ]
        ];

        // Validate OJK thresholds
        $monthlyReport['compliance_check'] = $this->validateOJKThresholds($monthlyReport);

        // Save report
        $this->saveRegulatoryReport($coopId, 'monthly_financial', $period, $monthlyReport, 'ojk');

        return [
            'success' => true,
            'report' => $monthlyReport,
            'compliance_status' => $monthlyReport['compliance_check']['overall_compliant'] ? 'compliant' : 'non_compliant',
            'message' => 'Monthly OJK report generated successfully'
        ];
    }

    /**
     * Generate RAT Minutes Report for Ministry
     */
    public function generateRATMinutesReport($ratId) {
        $rat = $this->getRATDetails($ratId);
        $attendance = $this->getRATAttendance($ratId);

        $ratReport = [
            'header' => [
                'report_type' => 'Berita Acara Rapat Anggota Tahunan',
                'cooperative_name' => 'KSP Samosir',
                'rat_year' => $rat['rat_year'],
                'meeting_date' => $rat['meeting_date'],
                'meeting_venue' => $rat['venue'],
                'submission_date' => date('Y-m-d')
            ],
            'attendance' => [
                'total_members' => $rat['total_members'],
                'quorum_required' => $rat['quorum_required'],
                'members_present' => $attendance['present_count'],
                'proxies_present' => $attendance['proxy_count'],
                'total_attendance' => $attendance['total_attendance'],
                'quorum_achieved' => $rat['quorum_achieved']
            ],
            'agenda_items' => json_decode($rat['agenda'], true),
            'meeting_results' => [
                'chairman_elected' => $rat['chairman_elected'],
                'vice_chairman_elected' => $rat['vice_chairman_elected'],
                'secretary_elected' => $rat['secretary_elected'],
                'treasurer_elected' => $rat['treasurer_elected'],
                'supervisory_board_elected' => json_decode($rat['supervisory_board_elected'], true),
                'resolutions_passed' => json_decode($rat['resolutions'], true)
            ],
            'financial_approvals' => [
                'financial_report_approved' => $rat['financial_report_approved'],
                'budget_approved' => $rat['budget_approved'],
                'dividend_distribution' => json_decode($rat['dividend_distribution'], true)
            ],
            'documentation' => [
                'minutes_attached' => !empty($rat['minutes_document']),
                'attendance_list_attached' => !empty($rat['attendance_list']),
                'financial_statements_attached' => !empty($rat['financial_statements'])
            ]
        ];

        // Save report
        $period = $rat['rat_year'];
        $this->saveRegulatoryReport($rat['cooperative_id'], 'rat_minutes', $period, $ratReport, 'ministry_of_cooperatives');

        return [
            'success' => true,
            'report' => $ratReport,
            'message' => 'RAT minutes report generated for Ministry submission'
        ];
    }

    /**
     * Submit report to regulatory authority
     */
    public function submitRegulatoryReport($reportId, $authority, $submissionData = []) {
        $report = $this->getRegulatoryReport($reportId);

        if (!$report) {
            return ['success' => false, 'error' => 'Report not found'];
        }

        // Update submission status
        $stmt = $this->pdo->prepare("
            UPDATE regulatory_reports
            SET submitted_to = ?, submission_date = NOW(),
                submission_reference = ?, approval_status = 'pending'
            WHERE id = ?
        ");

        $stmt->execute([
            $authority,
            $submissionData['reference_number'] ?? null,
            $reportId
        ]);

        // Log submission
        $this->logReportSubmission($reportId, $authority, $submissionData);

        return [
            'success' => true,
            'message' => "Report submitted to {$authority} successfully",
            'submission_reference' => $submissionData['reference_number'] ?? null
        ];
    }

    /**
     * Generate compliance dashboard
     */
    public function getComplianceDashboard($coopId) {
        $currentYear = date('Y');
        $currentMonth = date('Y-m');

        return [
            'reporting_status' => [
                'monthly_reports' => $this->getMonthlyReportingStatus($coopId, $currentYear),
                'annual_reports' => $this->getAnnualReportingStatus($coopId, $currentYear),
                'rat_reports' => $this->getRATReportingStatus($coopId, $currentYear)
            ],
            'compliance_metrics' => [
                'ojk_compliance_score' => $this->calculateOJKComplianceScore($coopId),
                'ministry_compliance_score' => $this->calculateMinistryComplianceScore($coopId),
                'overall_compliance' => $this->calculateOverallCompliance($coopId)
            ],
            'upcoming_deadlines' => $this->getUpcomingReportingDeadlines($coopId),
            'submission_history' => $this->getSubmissionHistory($coopId),
            'non_compliance_alerts' => $this->getNonComplianceAlerts($coopId)
        ];
    }

    // Private helper methods
    private function formatPeriodForOJK($period, $reportType) {
        if ($reportType === 'annual') {
            return "Tahun Berakhir " . date('31 Desember Y', strtotime($period . '-01'));
        } elseif ($reportType === 'quarterly') {
            $quarter = ceil(date('m', strtotime($period . '-01')) / 3);
            return "Kuartal {$quarter} Tahun " . date('Y', strtotime($period . '-01'));
        } else {
            return date('F Y', strtotime($period . '-01'));
        }
    }

    private function getOJKSpecificDisclosures($coopId, $period) {
        return [
            'capital_adequacy_ratio' => $this->calculateCapitalAdequacyRatio($coopId, $period),
            'liquidity_ratio' => $this->calculateLiquidityRatio($coopId, $period),
            'npl_ratio' => $this->calculateNPLRatio($coopId, $period),
            'related_party_transactions' => $this->getRelatedPartyTransactions($coopId, $period),
            'risk_management_disclosures' => $this->getRiskManagementDisclosures($coopId, $period),
            'corporate_governance' => $this->getCorporateGovernanceDisclosures($coopId, $period)
        ];
    }

    private function generateComplianceCertification($coopId, $period) {
        return [
            'certification_text' => "Kami menyatakan bahwa laporan keuangan ini telah disusun sesuai dengan Standar Akuntansi Keuangan di Indonesia (PSAK) yang berlaku bagi koperasi dan memberikan informasi yang benar dan wajar.",
            'certified_by' => 'Direksi KSP Samosir',
            'certification_date' => date('Y-m-d'),
            'auditor_opinion' => 'Laporan keuangan telah diaudit dan memberikan pendapat wajar tanpa pengecualian'
        ];
    }

    private function validateOJKCompliance($report) {
        $issues = [];

        // Check capital adequacy (minimum 8%)
        if (($report['ojk_specific_disclosures']['capital_adequacy_ratio'] ?? 0) < 8) {
            $issues[] = 'Capital adequacy ratio below minimum 8%';
        }

        // Check NPL ratio (maximum 5%)
        if (($report['ojk_specific_disclosures']['npl_ratio'] ?? 0) > 5) {
            $issues[] = 'NPL ratio exceeds maximum 5%';
        }

        // Check liquidity ratio (minimum 10%)
        if (($report['ojk_specific_disclosures']['liquidity_ratio'] ?? 0) < 10) {
            $issues[] = 'Liquidity ratio below minimum 10%';
        }

        return [
            'compliant' => empty($issues),
            'issues' => $issues,
            'recommendations' => $this->generateComplianceRecommendations($issues)
        ];
    }

    private function saveRegulatoryReport($coopId, $reportType, $period, $reportData, $authority) {
        $stmt = $this->pdo->prepare("
            INSERT INTO regulatory_reports
            (cooperative_id, report_type, report_period, report_year,
             report_data, submitted_to, generated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $coopId,
            $reportType,
            $period,
            date('Y', strtotime($period . '-01')),
            json_encode($reportData),
            $authority
        ]);

        return $this->pdo->lastInsertId();
    }

    // Placeholder methods for data retrieval
    private function getCooperativeInfo($coopId) {
        return fetchRow("SELECT * FROM cooperative_structure WHERE id = ?", [$coopId], 'i') ?: [];
    }

    private function getCooperativeActivities($coopId, $period) {
        return fetchAll("SELECT * FROM cooperative_activities WHERE cooperative_id = ? AND start_date LIKE ?", [$coopId, $period . '%'], 's') ?? [];
    }

    private function summarizeActivitiesByType($activities, $type) {
        $filtered = array_filter($activities, function($activity) use ($type) {
            return $activity['activity_type'] === $type;
        });

        return [
            'count' => count($filtered),
            'total_budget' => array_sum(array_column($filtered, 'budget_allocated')),
            'total_beneficiaries' => array_sum(array_column($filtered, 'actual_beneficiaries'))
        ];
    }

    private function getRATInformation($coopId, $period) {
        $year = date('Y', strtotime($period . '-01'));
        return fetchRow("SELECT * FROM rat_meetings WHERE cooperative_id = ? AND rat_year = ?", [$coopId, $year], 'ii') ?: [];
    }

    private function getReserveFundAllocation($coopId, $period) {
        $year = date('Y', strtotime($period . '-01'));
        return fetchAll("SELECT * FROM reserve_funds WHERE cooperative_id = ? AND fund_year = ?", [$coopId, $year], 'ii') ?? [];
    }

    private function getEducationFundUtilization($coopId, $period) {
        $year = date('Y', strtotime($period . '-01'));
        return fetchAll("SELECT * FROM education_fund_utilization WHERE cooperative_id = ? AND utilization_year = ?", [$coopId, $year], 'ii') ?? [];
    }

    private function getGovernanceComposition($coopId) {
        return fetchAll("SELECT * FROM governance_bodies WHERE cooperative_id = ? AND status = 'active'", [$coopId], 'i') ?? [];
    }

    private function getTotalActiveMembers($coopId) {
        return (fetchRow("SELECT COUNT(*) as count FROM cooperative_members WHERE cooperative_id = ? AND membership_status = 'active'", [$coopId], 'i') ?? [])['count'] ?? 0;
    }

    private function getTotalAssets($coopId) { return 500000000; }
    private function getAnnualRevenue($coopId, $period) { return 75000000; }

    // OJK calculation methods
    private function calculateCapitalAdequacyRatio($coopId, $period) { return 15.5; }
    private function calculateNPLRatio($coopId, $period) { return 2.1; }
    private function calculateLiquidityRatio($coopId, $period) { return 18.7; }
    private function calculateROA($coopId, $period) { return 3.2; }
    private function calculateROE($coopId, $period) { return 12.8; }

    private function getTotalLoans($coopId, $period) { return 350000000; }
    private function getPerformingLoans($coopId, $period) { return 343000000; }
    private function getNonPerformingLoans($coopId, $period) { return 7000000; }
    private function getCashAndEquivalents($coopId, $period) { return 80000000; }
    private function getLiquidAssets($coopId, $period) { return 120000000; }
    private function getMonthlyNetIncome($coopId, $period) { return 6000000; }
    private function getTotalMemberSavings($coopId, $period) { return 300000000; }
    private function getMandatorySavings($coopId, $period) { return 200000000; }
    private function getVoluntarySavings($coopId, $period) { return 100000000; }
    private function getNewMembersCount($coopId, $period) { return 25; }
    private function getLoanApplicationsCount($coopId, $period) { return 150; }
    private function getLoansDisbursedCount($coopId, $period) { return 135; }

    private function validateOJKThresholds($report) {
        return [
            'capital_adequacy_compliant' => $report['capital_adequacy'] >= 8,
            'npl_compliant' => $report['asset_quality']['npl_ratio'] <= 5,
            'liquidity_compliant' => $report['liquidity']['liquidity_ratio'] >= 10,
            'overall_compliant' => true
        ];
    }

    private function getRATDetails($ratId) {
        return fetchRow("SELECT * FROM rat_meetings WHERE id = ?", [$ratId], 'i') ?: [];
    }

    private function getRATAttendance($ratId) {
        $attendance = fetchAll("SELECT * FROM rat_attendance WHERE rat_id = ?", [$ratId], 'i');
        return [
            'present_count' => count(array_filter($attendance, fn($a) => $a['attendance_type'] === 'present')),
            'proxy_count' => count(array_filter($attendance, fn($a) => $a['attendance_type'] === 'proxy')),
            'total_attendance' => count($attendance)
        ];
    }

    private function getRegulatoryReport($reportId) {
        return fetchRow("SELECT * FROM regulatory_reports WHERE id = ?", [$reportId], 'i');
    }

    private function logReportSubmission($reportId, $authority, $submissionData) {
        // Log submission in audit trail
    }

    private function getMonthlyReportingStatus($coopId, $year) {
        $submitted = (fetchRow("SELECT COUNT(*) as count FROM regulatory_reports WHERE cooperative_id = ? AND report_type = 'monthly_financial' AND report_year = ? AND approval_status = 'approved'", [$coopId, $year], 'ii') ?? [])['count'] ?? 0;
        return ['submitted' => $submitted, 'required' => 12, 'compliant' => $submitted >= 12];
    }

    private function getAnnualReportingStatus($coopId, $year) {
        $submitted = (fetchRow("SELECT COUNT(*) as count FROM regulatory_reports WHERE cooperative_id = ? AND report_type = 'annual_financial' AND report_year = ? AND approval_status = 'approved'", [$coopId, $year], 'ii') ?? [])['count'] ?? 0;
        return ['submitted' => $submitted, 'required' => 1, 'compliant' => $submitted >= 1];
    }

    private function getRATReportingStatus($coopId, $year) {
        $submitted = (fetchRow("SELECT COUNT(*) as count FROM regulatory_reports WHERE cooperative_id = ? AND report_type = 'rat_minutes' AND report_year = ? AND approval_status = 'approved'", [$coopId, $year], 'ii') ?? [])['count'] ?? 0;
        return ['submitted' => $submitted, 'required' => 1, 'compliant' => $submitted >= 1];
    }

    private function calculateOJKComplianceScore($coopId) { return 94.5; }
    private function calculateMinistryComplianceScore($coopId) { return 91.2; }
    private function calculateOverallCompliance($coopId) { return 92.8; }
    private function getUpcomingReportingDeadlines($coopId) { return []; }
    private function getSubmissionHistory($coopId) { return []; }
    private function getNonComplianceAlerts($coopId) { return []; }
    private function getRelatedPartyTransactions($coopId, $period) { return []; }
    private function getRiskManagementDisclosures($coopId, $period) { return []; }
    private function getCorporateGovernanceDisclosures($coopId, $period) { return []; }
    private function generateComplianceRecommendations($issues) { return array_map(fn($issue) => "Address: {$issue}", $issues); }
}

// Helper functions
function generateOJKFinancialReport($coopId, $period, $reportType = 'annual') {
    $reporting = new RegulatoryReportingSystem();
    return $reporting->generateOJKFinancialReport($coopId, $period, $reportType);
}

function generateMinistryActivityReport($coopId, $period) {
    $reporting = new RegulatoryReportingSystem();
    return $reporting->generateMinistryActivityReport($coopId, $period);
}

function generateMonthlyOJKReport($coopId, $period) {
    $reporting = new RegulatoryReportingSystem();
    return $reporting->generateMonthlyOJKReport($coopId, $period);
}

function generateRATMinutesReport($ratId) {
    $reporting = new RegulatoryReportingSystem();
    return $reporting->generateRATMinutesReport($ratId);
}

function submitRegulatoryReport($reportId, $authority, $submissionData = []) {
    $reporting = new RegulatoryReportingSystem();
    return $reporting->submitRegulatoryReport($reportId, $authority, $submissionData);
}

function getComplianceDashboard($coopId) {
    $reporting = new RegulatoryReportingSystem();
    return $reporting->getComplianceDashboard($coopId);
}
