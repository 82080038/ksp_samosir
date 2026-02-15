<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../shared/php/cooperative_regulatory_framework.php';

/**
 * Cooperative Regulatory Compliance Controller
 * Handles all regulatory compliance requirements for Indonesian cooperatives
 */
class CooperativeComplianceController extends BaseController {

    private $regulatory;

    public function __construct() {
        parent::__construct();
        $this->regulatory = new CooperativeRegulatoryFramework();
    }

    /**
     * Main compliance dashboard
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        $coopId = $this->getCurrentCooperativeId();

        // Get compliance status
        $complianceStatus = $this->getComplianceStatus($coopId);
        $upcomingDeadlines = $this->getUpcomingDeadlines($coopId);
        $recentAudits = $this->getRecentAudits($coopId);
        $regulatoryAlerts = $this->getRegulatoryAlerts($coopId);

        $this->render('compliance/dashboard', [
            'compliance_status' => $complianceStatus,
            'upcoming_deadlines' => $upcomingDeadlines,
            'recent_audits' => $recentAudits,
            'regulatory_alerts' => $regulatoryAlerts
        ]);
    }

    /**
     * RAT (Annual Member Meeting) management
     */
    public function ratManagement() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        $coopId = $this->getCurrentCooperativeId();
        $currentYear = date('Y');

        // Get current RAT
        $currentRAT = $this->getCurrentRAT($coopId, $currentYear);

        // Get historical RATs
        $historicalRATs = $this->getHistoricalRATs($coopId);

        // Calculate quorum status
        $quorumStatus = $this->calculateQuorumStatus($currentRAT);

        $this->render('compliance/rat_management', [
            'current_rat' => $currentRAT,
            'historical_rats' => $historicalRATs,
            'quorum_status' => $quorumStatus,
            'current_year' => $currentYear
        ]);
    }

    /**
     * Schedule new RAT
     */
    public function scheduleRAT() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ratData = [
                'cooperative_id' => $this->getCurrentCooperativeId(),
                'rat_year' => intval($_POST['rat_year']),
                'rat_type' => $_POST['rat_type'] ?? 'annual',
                'meeting_date' => $_POST['meeting_date'],
                'meeting_time' => $_POST['meeting_time'] ?? '09:00:00',
                'venue' => $_POST['venue'],
                'agenda' => json_decode($_POST['agenda'] ?? '[]', true)
            ];

            $result = $this->regulatory->scheduleRAT($ratData);

            if ($result['success']) {
                flashMessage('success', 'RAT berhasil dijadwalkan sesuai UU 25/1992');
                redirect('cooperative-compliance/rat-management');
            } else {
                flashMessage('error', 'Gagal menjadwalkan RAT: ' . implode(', ', $result['errors']));
            }
        }

        $this->render('compliance/schedule_rat', [
            'current_year' => date('Y'),
            'next_year' => date('Y') + 1
        ]);
    }

    /**
     * RAT attendance management
     */
    public function ratAttendance($ratId) {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        $coopId = $this->getCurrentCooperativeId();

        // Get RAT details
        $rat = $this->getRAT($ratId);
        if (!$rat || $rat['cooperative_id'] != $coopId) {
            flashMessage('error', 'RAT tidak ditemukan');
            redirect('cooperative-compliance/rat-management');
        }

        // Get member attendance
        $attendance = $this->getRATAttendance($ratId);
        $memberStats = $this->getMemberAttendanceStats($coopId);

        $this->render('compliance/rat_attendance', [
            'rat' => $rat,
            'attendance' => $attendance,
            'member_stats' => $memberStats
        ]);
    }

    /**
     * Record RAT attendance
     */
    public function recordRATAttendance($ratId) {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $memberId = intval($_POST['member_id']);
            $attendanceType = $_POST['attendance_type'];
            $proxyName = $_POST['proxy_name'] ?? null;
            $proxyNik = $_POST['proxy_nik'] ?? null;

            $result = $this->recordMemberAttendance($ratId, $memberId, $attendanceType, $proxyName, $proxyNik);

            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Kehadiran berhasil dicatat']);
            } else {
                echo json_encode(['success' => false, 'error' => $result['error']]);
            }
            exit;
        }
    }

    /**
     * RAT results and resolutions
     */
    public function ratResults($ratId) {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        $coopId = $this->getCurrentCooperativeId();

        // Get RAT details
        $rat = $this->getRAT($ratId);
        if (!$rat || $rat['cooperative_id'] != $coopId) {
            flashMessage('error', 'RAT tidak ditemukan');
            redirect('cooperative-compliance/rat-management');
        }

        // Get RAT results
        $results = $this->getRATResults($ratId);

        $this->render('compliance/rat_results', [
            'rat' => $rat,
            'results' => $results
        ]);
    }

    /**
     * Record RAT results
     */
    public function recordRATResults($ratId) {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $results = [
                'chairman_elected' => $_POST['chairman_elected'],
                'vice_chairman_elected' => $_POST['vice_chairman_elected'],
                'secretary_elected' => $_POST['secretary_elected'],
                'treasurer_elected' => $_POST['treasurer_elected'],
                'supervisory_board_elected' => json_decode($_POST['supervisory_board_elected'] ?? '[]', true),
                'financial_report_approved' => isset($_POST['financial_report_approved']),
                'budget_approved' => isset($_POST['budget_approved']),
                'resolutions' => json_decode($_POST['resolutions'] ?? '[]', true)
            ];

            $result = $this->saveRATResults($ratId, $results);

            if ($result['success']) {
                flashMessage('success', 'Hasil RAT berhasil disimpan');
                redirect('cooperative-compliance/rat-results/' . $ratId);
            } else {
                flashMessage('error', 'Gagal menyimpan hasil RAT: ' . $result['error']);
            }
        }
    }

    /**
     * Regulatory reporting management
     */
    public function regulatoryReports() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        $coopId = $this->getCurrentCooperativeId();

        // Get report types and status
        $reportTypes = $this->getRequiredReportTypes();
        $reports = $this->getRegulatoryReports($coopId);
        $complianceStatus = $this->getReportingComplianceStatus($coopId);

        $this->render('compliance/regulatory_reports', [
            'report_types' => $reportTypes,
            'reports' => $reports,
            'compliance_status' => $complianceStatus
        ]);
    }

    /**
     * Generate regulatory report
     */
    public function generateReport() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        $coopId = $this->getCurrentCooperativeId();
        $reportType = $_GET['type'] ?? 'monthly_financial';
        $period = $_GET['period'] ?? date('Y-m');

        $result = $this->regulatory->generateFinancialReport($coopId, $period, $reportType);

        if ($result['success']) {
            // Store report file
            $reportFile = $this->saveReportFile($result['report_data'], $reportType, $period);

            // Update report record
            $this->pdo->prepare("
                UPDATE regulatory_reports
                SET report_file = ?, generated_at = NOW()
                WHERE id = ?
            ")->execute([$reportFile, $result['report_id']]);

            flashMessage('success', 'Laporan berhasil dibuat sesuai standar PSAK 109 dan regulasi OJK');
        } else {
            flashMessage('error', 'Gagal membuat laporan: ' . $result['error']);
        }

        redirect('cooperative-compliance/regulatory-reports');
    }

    /**
     * Governance body management
     */
    public function governanceManagement() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        $coopId = $this->getCurrentCooperativeId();

        // Get governance bodies
        $governanceBodies = $this->getGovernanceBodies($coopId);
        $vacantPositions = $this->getVacantGovernancePositions($coopId);
        $termExpirations = $this->getUpcomingTermExpirations($coopId);

        $this->render('compliance/governance_management', [
            'governance_bodies' => $governanceBodies,
            'vacant_positions' => $vacantPositions,
            'term_expirations' => $termExpirations
        ]);
    }

    /**
     * Appoint governance member
     */
    public function appointGovernanceMember() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $appointmentData = [
                'cooperative_id' => $this->getCurrentCooperativeId(),
                'body_type' => $_POST['body_type'],
                'position_title' => $_POST['position_title'],
                'member_id' => intval($_POST['member_id']) ?: null,
                'external_member_name' => $_POST['external_member_name'] ?? null,
                'term_start_date' => $_POST['term_start_date'],
                'term_end_date' => $_POST['term_end_date'],
                'authorities' => json_decode($_POST['authorities'] ?? '[]', true),
                'responsibilities' => json_decode($_POST['responsibilities'] ?? '[]', true)
            ];

            $result = $this->appointGovernanceMemberData($appointmentData);

            if ($result['success']) {
                flashMessage('success', 'Anggota pengurus berhasil diangkat sesuai UU 25/1992');
                redirect('cooperative-compliance/governance-management');
            } else {
                flashMessage('error', 'Gagal mengangkat anggota pengurus: ' . $result['error']);
            }
        }

        $coopId = $this->getCurrentCooperativeId();
        $activeMembers = $this->getActiveMembers($coopId);

        $this->render('compliance/appoint_governance', [
            'active_members' => $activeMembers
        ]);
    }

    /**
     * Reserve fund management
     */
    public function reserveFundManagement() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        $coopId = $this->getCurrentCooperativeId();
        $currentYear = date('Y');

        // Get current reserve funds
        $reserveFunds = $this->getReserveFunds($coopId, $currentYear);
        $annualProfit = $this->getAnnualProfit($coopId, $currentYear);
        $minimumAllocation = $annualProfit * 0.25; // 25% of profit

        $this->render('compliance/reserve_fund_management', [
            'reserve_funds' => $reserveFunds,
            'annual_profit' => $annualProfit,
            'minimum_allocation' => $minimumAllocation,
            'current_year' => $currentYear
        ]);
    }

    /**
     * Allocate reserve funds
     */
    public function allocateReserveFunds() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $allocationData = [
                'year' => intval($_POST['allocation_year']),
                'source' => $_POST['allocation_source'],
                'allocations' => []
            ];

            // Parse allocations
            $fundTypes = ['reserve_fund', 'education_fund', 'welfare_fund', 'development_fund'];
            foreach ($fundTypes as $fundType) {
                if (isset($_POST[$fundType . '_amount']) && floatval($_POST[$fundType . '_amount']) > 0) {
                    $allocationData['allocations'][] = [
                        'fund_type' => $fundType,
                        'amount' => floatval($_POST[$fundType . '_amount']),
                        'percentage' => floatval($_POST[$fundType . '_percentage'] ?? 0)
                    ];
                }
            }

            $result = $this->regulatory->allocateReserveFunds($this->getCurrentCooperativeId(), $allocationData);

            if ($result['success']) {
                flashMessage('success', 'Dana cadangan berhasil dialokasikan sesuai UU 25/1992 Pasal 44');
                redirect('cooperative-compliance/reserve-fund-management');
            } else {
                flashMessage('error', 'Gagal mengalokasikan dana cadangan: ' . implode(', ', $result['errors']));
            }
        }
    }

    /**
     * Compliance audit management
     */
    public function complianceAudits() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        $coopId = $this->getCurrentCooperativeId();

        // Get audit history
        $audits = $this->getComplianceAudits($coopId);
        $upcomingAudits = $this->getUpcomingAudits($coopId);
        $auditRecommendations = $this->getAuditRecommendations($coopId);

        $this->render('compliance/compliance_audits', [
            'audits' => $audits,
            'upcoming_audits' => $upcomingAudits,
            'audit_recommendations' => $auditRecommendations
        ]);
    }

    /**
     * Perform compliance audit
     */
    public function performAudit() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        $coopId = $this->getCurrentCooperativeId();
        $auditType = $_GET['type'] ?? 'internal';

        $result = $this->regulatory->performComplianceAudit($coopId, $auditType);

        if ($result['success']) {
            flashMessage('success', 'Audit kepatuhan berhasil dilakukan. Skor: ' . $result['overall_score'] . '%');
        } else {
            flashMessage('error', 'Gagal melakukan audit: ' . $result['error']);
        }

        redirect('cooperative-compliance/compliance-audits');
    }

    /**
     * Legal document management
     */
    public function legalDocuments() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        $coopId = $this->getCurrentCooperativeId();

        // Get documents by category
        $documents = $this->getLegalDocuments($coopId);
        $expiringDocuments = $this->getExpiringDocuments($coopId);
        $missingDocuments = $this->getMissingRequiredDocuments($coopId);

        $this->render('compliance/legal_documents', [
            'documents' => $documents,
            'expiring_documents' => $expiringDocuments,
            'missing_documents' => $missingDocuments
        ]);
    }

    /**
     * Upload legal document
     */
    public function uploadLegalDocument() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // Disabled for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
            $coopId = $this->getCurrentCooperativeId();

            $documentData = [
                'cooperative_id' => $coopId,
                'document_type' => $_POST['document_type'],
                'document_title' => $_POST['document_title'],
                'document_number' => $_POST['document_number'] ?? null,
                'issue_date' => $_POST['issue_date'] ?? null,
                'expiry_date' => $_POST['expiry_date'] ?? null,
                'issuing_authority' => $_POST['issuing_authority'] ?? null
            ];

            // Upload file
            $uploadResult = $this->uploadDocumentFile($_FILES['document_file']);
            if (!$uploadResult['success']) {
                flashMessage('error', 'Gagal mengupload file: ' . $uploadResult['error']);
                redirect('cooperative-compliance/legal-documents');
            }

            $documentData['document_file'] = $uploadResult['file_path'];

            $result = $this->saveLegalDocument($documentData);

            if ($result['success']) {
                flashMessage('success', 'Dokumen legal berhasil diupload');
            } else {
                flashMessage('error', 'Gagal menyimpan dokumen: ' . $result['error']);
            }
        }

        redirect('cooperative-compliance/legal-documents');
    }

    // Private helper methods
    private function getCurrentCooperativeId() {
        // For now, return default cooperative ID
        // In production, this would get from session or configuration
        return 1;
    }

    private function getComplianceStatus($coopId) {
        // Calculate overall compliance status
        $coop = fetchRow("SELECT * FROM cooperative_structure WHERE id = ?", [$coopId], 'i');

        $status = [
            'overall_compliance' => $coop['compliance_status'] ?? 'unknown',
            'governance_compliance' => $this->checkGovernanceCompliance($coopId),
            'financial_compliance' => $this->checkFinancialCompliance($coopId),
            'regulatory_compliance' => $this->checkRegulatoryCompliance($coopId),
            'operational_compliance' => $this->checkOperationalCompliance($coopId)
        ];

        $status['compliance_score'] = $this->calculateComplianceScore($status);

        return $status;
    }

    private function checkGovernanceCompliance($coopId) {
        $governanceCount = (fetchRow("SELECT COUNT(*) as count FROM governance_bodies WHERE cooperative_id = ? AND status = 'active'", [$coopId], 'i') ?? [])['count'] ?? 0;
        return $governanceCount >= 5 ? 'compliant' : 'non_compliant';
    }

    private function checkFinancialCompliance($coopId) {
        $reportCount = (fetchRow("SELECT COUNT(*) as count FROM regulatory_reports WHERE cooperative_id = ? AND approval_status = 'approved'", [$coopId], 'i') ?? [])['count'] ?? 0;
        return $reportCount >= 4 ? 'compliant' : 'non_compliant';
    }

    private function checkRegulatoryCompliance($coopId) {
        $coop = fetchRow("SELECT * FROM cooperative_structure WHERE id = ?", [$coopId], 'i');
        $compliant = !empty($coop['registration_number']) && !empty($coop['ojk_registration_number']);
        return $compliant ? 'compliant' : 'non_compliant';
    }

    private function checkOperationalCompliance($coopId) {
        $ratCount = (fetchRow("SELECT COUNT(*) as count FROM rat_meetings WHERE cooperative_id = ? AND quorum_achieved = 1", [$coopId], 'i') ?? [])['count'] ?? 0;
        return $ratCount >= 2 ? 'compliant' : 'non_compliant';
    }

    private function calculateComplianceScore($status) {
        $scores = [
            'compliant' => 100,
            'warning' => 75,
            'non_compliant' => 25,
            'unknown' => 0
        ];

        $totalScore = 0;
        $count = 0;

        foreach ($status as $key => $value) {
            if ($key !== 'overall_compliance' && $key !== 'compliance_score' && isset($scores[$value])) {
                $totalScore += $scores[$value];
                $count++;
            }
        }

        return $count > 0 ? round($totalScore / $count, 1) : 0;
    }

    private function getUpcomingDeadlines($coopId) {
        $deadlines = [];

        // RAT deadline
        $currentYear = date('Y');
        $ratDeadline = date('Y-03-31'); // March 31st for annual RAT
        if (strtotime($ratDeadline) > time()) {
            $deadlines[] = [
                'type' => 'RAT',
                'description' => 'Rapat Anggota Tahunan ' . $currentYear,
                'deadline' => $ratDeadline,
                'urgency' => 'high'
            ];
        }

        // Financial reporting deadlines
        $deadlines[] = [
            'type' => 'Financial Report',
            'description' => 'Laporan Keuangan Tahunan ' . $currentYear,
            'deadline' => date('Y-04-30'),
            'urgency' => 'high'
        ];

        // Audit deadlines
        $deadlines[] = [
            'type' => 'Audit',
            'description' => 'Audit Kepatuhan Tahunan',
            'deadline' => date('Y-05-31'),
            'urgency' => 'medium'
        ];

        return $deadlines;
    }

    private function getRecentAudits($coopId) {
        return fetchAll("
            SELECT * FROM compliance_audits
            WHERE cooperative_id = ?
            ORDER BY created_at DESC
            LIMIT 5
        ", [$coopId], 'i') ?? [];
    }

    private function getRegulatoryAlerts($coopId) {
        $alerts = [];

        // Check for expiring documents
        $expiringDocs = $this->getExpiringDocuments($coopId);
        foreach ($expiringDocs as $doc) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Dokumen {$doc['document_type']} akan kedaluwarsa pada " . date('d M Y', strtotime($doc['expiry_date'])),
                'priority' => 'medium'
            ];
        }

        // Check for upcoming deadlines
        $deadlines = $this->getUpcomingDeadlines($coopId);
        foreach ($deadlines as $deadline) {
            $daysUntil = (strtotime($deadline['deadline']) - time()) / (60 * 60 * 24);
            if ($daysUntil <= 30) {
                $alerts[] = [
                    'type' => 'urgent',
                    'message' => "Deadline {$deadline['description']} pada " . date('d M Y', strtotime($deadline['deadline'])),
                    'priority' => 'high'
                ];
            }
        }

        return array_slice($alerts, 0, 5);
    }

    // Additional helper methods would be implemented...
    private function getCurrentRAT($coopId, $year) { return null; }
    private function getHistoricalRATs($coopId) { return []; }
    private function calculateQuorumStatus($rat) { return ['achieved' => false, 'percentage' => 0]; }
    private function getRAT($ratId) { return fetchRow("SELECT * FROM rat_meetings WHERE id = ?", [$ratId], 'i'); }
    private function getRATAttendance($ratId) { return fetchAll("SELECT * FROM rat_attendance WHERE rat_id = ?", [$ratId], 'i') ?? []; }
    private function getMemberAttendanceStats($coopId) { return ['total_members' => 0, 'attended_last_rat' => 0]; }
    private function recordMemberAttendance($ratId, $memberId, $type, $proxyName, $proxyNik) { return ['success' => true]; }
    private function getRATResults($ratId) { return []; }
    private function saveRATResults($ratId, $results) { return ['success' => true]; }
    private function getRequiredReportTypes() { return ['monthly_financial', 'quarterly_financial', 'annual_financial']; }
    private function getRegulatoryReports($coopId) { return fetchAll("SELECT * FROM regulatory_reports WHERE cooperative_id = ?", [$coopId], 'i') ?? []; }
    private function getReportingComplianceStatus($coopId) { return ['compliant' => true, 'score' => 95]; }
    private function saveReportFile($reportData, $type, $period) { return '/reports/' . $type . '_' . $period . '.pdf'; }
    private function getGovernanceBodies($coopId) { return fetchAll("SELECT * FROM governance_bodies WHERE cooperative_id = ?", [$coopId], 'i') ?? []; }
    private function getVacantGovernancePositions($coopId) { return []; }
    private function getUpcomingTermExpirations($coopId) { return []; }
    private function appointGovernanceMemberData($data) { return ['success' => true]; }
    private function getActiveMembers($coopId) { return fetchAll("SELECT * FROM cooperative_members WHERE cooperative_id = ? AND membership_status = 'active'", [$coopId], 'i') ?? []; }
    private function getReserveFunds($coopId, $year) { return fetchAll("SELECT * FROM reserve_funds WHERE cooperative_id = ? AND fund_year = ?", [$coopId, $year], 'ii') ?? []; }
    private function getAnnualProfit($coopId, $year) { return 100000000; }
    private function getComplianceAudits($coopId) { return fetchAll("SELECT * FROM compliance_audits WHERE cooperative_id = ?", [$coopId], 'i') ?? []; }
    private function getUpcomingAudits($coopId) { return []; }
    private function getAuditRecommendations($coopId) { return []; }
    private function getLegalDocuments($coopId) { return fetchAll("SELECT * FROM legal_documents WHERE cooperative_id = ?", [$coopId], 'i') ?? []; }
    private function getExpiringDocuments($coopId) { return []; }
    private function getMissingRequiredDocuments($coopId) { return []; }
    private function uploadDocumentFile($file) { return ['success' => true, 'file_path' => '/uploads/documents/' . $file['name']]; }
    private function saveLegalDocument($data) { return ['success' => true, 'document_id' => 1]; }
}
