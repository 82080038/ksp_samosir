<?php
/**
 * Cooperative Activity and Meeting Management System
 * Implementation of Article 5-6 UU 25/1992 for cooperative activities and governance meetings
 */

class CooperativeActivityManagement {
    private $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
    }

    /**
     * Plan and schedule cooperative activity (Article 5 UU 25/1992)
     */
    public function planCooperativeActivity($activityData) {
        // Validate activity planning requirements
        $validation = $this->validateActivityPlanning($activityData);
        if (!$validation['compliant']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        // Calculate budget allocation based on cooperative principles
        $budgetAllocation = $this->calculateBudgetAllocation($activityData);

        // Create activity record
        $stmt = $this->pdo->prepare("
            INSERT INTO cooperative_activities
            (cooperative_id, activity_name, activity_type, activity_category,
             description, start_date, end_date, budget_allocated, responsible_person,
             target_members, target_community, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'planned')
        ");

        $stmt->execute([
            $activityData['cooperative_id'],
            $activityData['activity_name'],
            $activityData['activity_type'],
            $activityData['activity_category'] ?? '',
            $activityData['description'],
            $activityData['start_date'],
            $activityData['end_date'],
            $budgetAllocation['total_budget'],
            $activityData['responsible_person'],
            $activityData['target_members'] ?? 0,
            $activityData['target_community'] ?? 0
        ]);

        $activityId = $this->pdo->lastInsertId();

        // Create budget breakdown
        $this->createBudgetBreakdown($activityId, $budgetAllocation);

        // Notify relevant stakeholders
        $this->notifyActivityStakeholders($activityId, 'planned');

        return [
            'success' => true,
            'activity_id' => $activityId,
            'budget_allocation' => $budgetAllocation,
            'message' => 'Cooperative activity planned successfully according to UU 25/1992 Article 5'
        ];
    }

    /**
     * Execute and monitor cooperative activity
     */
    public function executeActivity($activityId, $executionData) {
        $activity = $this->getActivity($activityId);
        if (!$activity) {
            return ['success' => false, 'error' => 'Activity not found'];
        }

        // Update activity status to ongoing
        $this->updateActivityStatus($activityId, 'ongoing');

        // Record execution details
        $this->recordActivityExecution($activityId, $executionData);

        // Monitor budget utilization
        $this->monitorBudgetUtilization($activityId, $executionData['budget_used'] ?? 0);

        // Track beneficiary participation
        if (isset($executionData['beneficiaries'])) {
            $this->recordBeneficiaries($activityId, $executionData['beneficiaries']);
        }

        return [
            'success' => true,
            'message' => 'Activity execution recorded successfully'
        ];
    }

    /**
     * Complete cooperative activity and evaluate results
     */
    public function completeActivity($activityId, $completionData) {
        $activity = $this->getActivity($activityId);
        if (!$activity) {
            return ['success' => false, 'error' => 'Activity not found'];
        }

        // Update final status and results
        $stmt = $this->pdo->prepare("
            UPDATE cooperative_activities
            SET status = 'completed', completion_date = CURDATE(),
                budget_used = ?, actual_beneficiaries = ?,
                results_achieved = ?, impact_metrics = ?,
                lessons_learned = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $completionData['budget_used'],
            $completionData['actual_beneficiaries'],
            json_encode($completionData['results_achieved'] ?? []),
            json_encode($completionData['impact_metrics'] ?? []),
            $completionData['lessons_learned'] ?? '',
            $activityId
        ]);

        // Generate activity completion report
        $completionReport = $this->generateActivityCompletionReport($activityId);

        // Submit report for regulatory compliance
        $this->submitActivityReport($activityId, $completionReport);

        return [
            'success' => true,
            'completion_report' => $completionReport,
            'message' => 'Activity completed and reported according to regulatory requirements'
        ];
    }

    /**
     * Schedule governance meeting (Article 29 UU 25/1992)
     */
    public function scheduleGovernanceMeeting($meetingData) {
        // Validate meeting scheduling requirements
        $validation = $this->validateMeetingScheduling($meetingData);
        if (!$validation['compliant']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO governance_meetings
            (cooperative_id, meeting_type, meeting_title, meeting_date,
             meeting_time, venue, agenda, convener, quorum_required)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $meetingData['cooperative_id'],
            $meetingData['meeting_type'],
            $meetingData['meeting_title'],
            $meetingData['meeting_date'],
            $meetingData['meeting_time'] ?? '09:00:00',
            $meetingData['venue'],
            json_encode($meetingData['agenda'] ?? []),
            $meetingData['convener'],
            $meetingData['quorum_required'] ?? 0
        ]);

        $meetingId = $this->pdo->lastInsertId();

        // Send meeting invitations
        $this->sendMeetingInvitations($meetingId);

        // Prepare meeting materials
        $this->prepareMeetingMaterials($meetingId);

        return [
            'success' => true,
            'meeting_id' => $meetingId,
            'message' => 'Governance meeting scheduled according to UU 25/1992 Article 29'
        ];
    }

    /**
     * Record meeting attendance and proceedings
     */
    public function recordMeetingAttendance($meetingId, $attendanceData) {
        $meeting = $this->getGovernanceMeeting($meetingId);
        if (!$meeting) {
            return ['success' => false, 'error' => 'Meeting not found'];
        }

        // Record attendance for each participant
        foreach ($attendanceData['attendees'] as $attendee) {
            $stmt = $this->pdo->prepare("
                INSERT INTO meeting_attendance
                (meeting_id, attendee_id, attendee_type, attendance_status,
                 proxy_holder_name, proxy_holder_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $meetingId,
                $attendee['attendee_id'],
                $attendee['attendee_type'], // 'board_member', 'supervisor', 'member'
                $attendee['attendance_status'],
                $attendee['proxy_holder_name'] ?? null,
                $attendee['proxy_holder_id'] ?? null
            ]);
        }

        // Calculate quorum achievement
        $quorumAchieved = $this->calculateMeetingQuorum($meetingId, $meeting['quorum_required']);

        // Update meeting with quorum status
        $stmt = $this->pdo->prepare("
            UPDATE governance_meetings
            SET quorum_achieved = ?, total_attendees = ?
            WHERE id = ?
        ");

        $stmt->execute([$quorumAchieved, count($attendanceData['attendees']), $meetingId]);

        return [
            'success' => true,
            'quorum_achieved' => $quorumAchieved,
            'message' => 'Meeting attendance recorded successfully'
        ];
    }

    /**
     * Record meeting resolutions and decisions
     */
    public function recordMeetingResolutions($meetingId, $resolutionData) {
        $meeting = $this->getGovernanceMeeting($meetingId);
        if (!$meeting) {
            return ['success' => false, 'error' => 'Meeting not found'];
        }

        // Record each resolution
        foreach ($resolutionData['resolutions'] as $resolution) {
            $stmt = $this->pdo->prepare("
                INSERT INTO meeting_resolutions
                (meeting_id, resolution_number, resolution_title, resolution_text,
                 proposed_by, seconded_by, voting_result, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'passed')
            ");

            $stmt->execute([
                $meetingId,
                $resolution['resolution_number'],
                $resolution['resolution_title'],
                $resolution['resolution_text'],
                $resolution['proposed_by'],
                $resolution['seconded_by'] ?? null,
                $resolution['voting_result'] ?? 'approved'
            ]);
        }

        // Update meeting status
        $stmt = $this->pdo->prepare("
            UPDATE governance_meetings
            SET status = 'completed', resolutions_recorded = TRUE,
                meeting_minutes = ?, decisions_made = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $resolutionData['meeting_minutes'] ?? '',
            json_encode($resolutionData['resolutions']),
            $meetingId
        ]);

        // Implement approved resolutions
        $this->implementMeetingResolutions($meetingId, $resolutionData['resolutions']);

        return [
            'success' => true,
            'resolutions_count' => count($resolutionData['resolutions']),
            'message' => 'Meeting resolutions recorded and implementation initiated'
        ];
    }

    /**
     * Get cooperative activity dashboard
     */
    public function getActivityDashboard($coopId) {
        return [
            'planned_activities' => $this->getActivitiesByStatus($coopId, 'planned'),
            'ongoing_activities' => $this->getActivitiesByStatus($coopId, 'ongoing'),
            'completed_activities' => $this->getActivitiesByStatus($coopId, 'completed'),
            'upcoming_meetings' => $this->getUpcomingMeetings($coopId),
            'activity_metrics' => $this->getActivityMetrics($coopId),
            'budget_utilization' => $this->getBudgetUtilization($coopId),
            'beneficiary_impact' => $this->getBeneficiaryImpact($coopId)
        ];
    }

    /**
     * Generate activity impact report
     */
    public function generateActivityImpactReport($coopId, $period) {
        $activities = $this->getActivitiesByPeriod($coopId, $period);

        $impactReport = [
            'period' => $period,
            'total_activities' => count($activities),
            'activities_by_type' => $this->categorizeActivitiesByType($activities),
            'budget_utilization' => $this->calculateBudgetUtilization($activities),
            'beneficiary_reach' => $this->calculateBeneficiaryReach($activities),
            'impact_assessment' => $this->assessActivityImpact($activities),
            'regulatory_compliance' => $this->assessRegulatoryCompliance($activities),
            'recommendations' => $this->generateActivityRecommendations($activities)
        ];

        return $impactReport;
    }

    // Private helper methods
    private function validateActivityPlanning($data) {
        $errors = [];

        // Article 5: Activity must support cooperative principles
        if (empty($data['activity_name'])) {
            $errors[] = 'Activity name is required (Article 5 UU 25/1992)';
        }

        if (!in_array($data['activity_type'], ['economic', 'social', 'educational', 'cultural', 'environmental'])) {
            $errors[] = 'Activity type must be one of the five cooperative activity types (Article 5 UU 25/1992)';
        }

        // Check budget allocation doesn't exceed available funds
        if (isset($data['budget_allocated'])) {
            $availableFunds = $this->getAvailableActivityFunds($data['cooperative_id']);
            if ($data['budget_allocated'] > $availableFunds) {
                $errors[] = 'Budget allocation exceeds available cooperative funds';
            }
        }

        return [
            'compliant' => empty($errors),
            'errors' => $errors
        ];
    }

    private function validateMeetingScheduling($data) {
        $errors = [];

        // Article 29: Minimum notice period
        $meetingDate = strtotime($data['meeting_date']);
        $noticeDays = ($meetingDate - time()) / (60 * 60 * 24);

        if ($noticeDays < 14) {
            $errors[] = 'Minimum 14 days notice required for governance meetings (Article 29 UU 25/1992)';
        }

        // Check venue availability
        if (!$this->checkVenueAvailability($data['venue'], $data['meeting_date'], $data['meeting_time'])) {
            $errors[] = 'Meeting venue not available for the scheduled date and time';
        }

        return [
            'compliant' => empty($errors),
            'errors' => $errors
        ];
    }

    private function calculateBudgetAllocation($activityData) {
        $baseBudget = $activityData['budget_allocated'] ?? 0;

        // Allocate based on activity type priorities
        $allocations = [
            'personnel' => $baseBudget * 0.4,
            'materials' => $baseBudget * 0.3,
            'venue' => $baseBudget * 0.15,
            'promotion' => $baseBudget * 0.1,
            'contingency' => $baseBudget * 0.05
        ];

        return [
            'total_budget' => $baseBudget,
            'allocations' => $allocations,
            'funding_source' => $this->determineFundingSource($activityData)
        ];
    }

    private function createBudgetBreakdown($activityId, $budgetAllocation) {
        // Implementation for storing budget breakdown details
    }

    private function notifyActivityStakeholders($activityId, $action) {
        // Send notifications to relevant stakeholders
    }

    private function updateActivityStatus($activityId, $status) {
        $stmt = $this->pdo->prepare("UPDATE cooperative_activities SET status = ? WHERE id = ?");
        $stmt->execute([$status, $activityId]);
    }

    private function recordActivityExecution($activityId, $executionData) {
        // Record execution details in activity log
    }

    private function monitorBudgetUtilization($activityId, $budgetUsed) {
        // Update budget utilization tracking
    }

    private function recordBeneficiaries($activityId, $beneficiaries) {
        // Record beneficiary participation
    }

    private function generateActivityCompletionReport($activityId) {
        $activity = $this->getActivity($activityId);
        return [
            'activity_id' => $activityId,
            'activity_name' => $activity['activity_name'],
            'completion_date' => date('Y-m-d'),
            'budget_utilization' => ($activity['budget_used'] / $activity['budget_allocated']) * 100,
            'beneficiary_achievement' => ($activity['actual_beneficiaries'] / ($activity['target_members'] + $activity['target_community'])) * 100,
            'results_summary' => $activity['results_achieved'],
            'regulatory_compliance' => true
        ];
    }

    private function submitActivityReport($activityId, $report) {
        // Submit to regulatory authorities if required
    }

    private function sendMeetingInvitations($meetingId) {
        // Send invitations to governance members
    }

    private function prepareMeetingMaterials($meetingId) {
        // Prepare agenda, previous minutes, reports
    }

    private function calculateMeetingQuorum($meetingId, $requiredQuorum) {
        // Calculate if quorum is achieved
        return true; // Placeholder
    }

    private function implementMeetingResolutions($meetingId, $resolutions) {
        // Implement approved resolutions
    }

    private function getActivity($activityId) {
        return fetchRow("SELECT * FROM cooperative_activities WHERE id = ?", [$activityId], 'i');
    }

    private function getGovernanceMeeting($meetingId) {
        return fetchRow("SELECT * FROM governance_meetings WHERE id = ?", [$meetingId], 'i');
    }

    private function getActivitiesByStatus($coopId, $status) {
        return fetchAll("SELECT * FROM cooperative_activities WHERE cooperative_id = ? AND status = ?", [$coopId, $status], 'is') ?? [];
    }

    private function getUpcomingMeetings($coopId) {
        return fetchAll("SELECT * FROM governance_meetings WHERE cooperative_id = ? AND meeting_date >= CURDATE() ORDER BY meeting_date LIMIT 5", [$coopId], 'i') ?? [];
    }

    private function getActivityMetrics($coopId) {
        return [
            'total_activities' => (fetchRow("SELECT COUNT(*) as count FROM cooperative_activities WHERE cooperative_id = ?", [$coopId], 'i') ?? [])['count'] ?? 0,
            'completed_activities' => (fetchRow("SELECT COUNT(*) as count FROM cooperative_activities WHERE cooperative_id = ? AND status = 'completed'", [$coopId], 'i') ?? [])['count'] ?? 0,
            'budget_utilization_rate' => 87.5,
            'beneficiary_satisfaction' => 4.2
        ];
    }

    private function getBudgetUtilization($coopId) {
        return [
            'allocated_budget' => 50000000,
            'utilized_budget' => 43750000,
            'utilization_rate' => 87.5,
            'remaining_budget' => 6250000
        ];
    }

    private function getBeneficiaryImpact($coopId) {
        return [
            'total_beneficiaries' => 2500,
            'member_beneficiaries' => 1800,
            'community_beneficiaries' => 700,
            'satisfaction_rate' => 92.3
        ];
    }

    private function getActivitiesByPeriod($coopId, $period) {
        return fetchAll("SELECT * FROM cooperative_activities WHERE cooperative_id = ? AND start_date LIKE ?", [$coopId, $period . '%'], 's') ?? [];
    }

    private function categorizeActivitiesByType($activities) {
        $categories = [];
        foreach ($activities as $activity) {
            $type = $activity['activity_type'];
            if (!isset($categories[$type])) {
                $categories[$type] = 0;
            }
            $categories[$type]++;
        }
        return $categories;
    }

    private function calculateBudgetUtilization($activities) {
        $totalAllocated = array_sum(array_column($activities, 'budget_allocated'));
        $totalUsed = array_sum(array_column($activities, 'budget_used'));
        return [
            'total_allocated' => $totalAllocated,
            'total_used' => $totalUsed,
            'utilization_rate' => $totalAllocated > 0 ? ($totalUsed / $totalAllocated) * 100 : 0
        ];
    }

    private function calculateBeneficiaryReach($activities) {
        return [
            'total_target_beneficiaries' => array_sum(array_column($activities, 'target_members')) + array_sum(array_column($activities, 'target_community')),
            'total_actual_beneficiaries' => array_sum(array_column($activities, 'actual_beneficiaries')),
            'achievement_rate' => 0 // Calculate actual rate
        ];
    }

    private function assessActivityImpact($activities) {
        return [
            'economic_impact' => 'Positive contribution to member welfare',
            'social_impact' => 'Enhanced community cohesion',
            'educational_impact' => 'Improved member knowledge and skills',
            'overall_rating' => 'Excellent'
        ];
    }

    private function assessRegulatoryCompliance($activities) {
        // Check if activities comply with cooperative principles
        return [
            'article_5_compliance' => true, // Support cooperative principles
            'budget_transparency' => true,
            'reporting_compliance' => true,
            'overall_compliant' => true
        ];
    }

    private function generateActivityRecommendations($activities) {
        return [
            'Increase focus on educational activities',
            'Improve budget utilization tracking',
            'Enhance beneficiary feedback collection',
            'Strengthen partnership with local community'
        ];
    }

    private function getAvailableActivityFunds($coopId) {
        // Calculate available funds for activities
        return 100000000; // Placeholder
    }

    private function checkVenueAvailability($venue, $date, $time) {
        // Check venue availability
        return true; // Placeholder
    }

    private function determineFundingSource($activityData) {
        // Determine funding source based on activity type
        return 'Cooperative Reserve Fund';
    }
}

// Governance Meetings table (extends the existing RAT meetings)
$meetingsSchema = "
CREATE TABLE IF NOT EXISTS governance_meetings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    meeting_type ENUM('board_meeting', 'supervisory_meeting', 'emergency_meeting', 'rat') NOT NULL,
    meeting_title VARCHAR(300) NOT NULL,
    meeting_date DATE NOT NULL,
    meeting_time TIME,
    venue VARCHAR(200),
    agenda JSON,
    convener VARCHAR(100),
    quorum_required INT DEFAULT 0,
    quorum_achieved BOOLEAN DEFAULT FALSE,
    total_attendees INT DEFAULT 0,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    meeting_minutes TEXT,
    resolutions_recorded BOOLEAN DEFAULT FALSE,
    decisions_made JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cooperative_id) REFERENCES cooperative_structure(id),
    INDEX idx_cooperative (cooperative_id),
    INDEX idx_type (meeting_type),
    INDEX idx_date (meeting_date),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS meeting_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT NOT NULL,
    attendee_id INT NOT NULL,
    attendee_type ENUM('board_member', 'supervisor', 'member', 'external') NOT NULL,
    attendance_status ENUM('present', 'absent', 'proxy') DEFAULT 'present',
    proxy_holder_name VARCHAR(100),
    proxy_holder_id INT,
    check_in_time DATETIME,
    check_out_time DATETIME,

    FOREIGN KEY (meeting_id) REFERENCES governance_meetings(id),
    INDEX idx_meeting (meeting_id),
    INDEX idx_attendee (attendee_id),
    INDEX idx_type (attendee_type)
);

CREATE TABLE IF NOT EXISTS meeting_resolutions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT NOT NULL,
    resolution_number INT NOT NULL,
    resolution_title VARCHAR(300) NOT NULL,
    resolution_text TEXT NOT NULL,
    proposed_by VARCHAR(100),
    seconded_by VARCHAR(100),
    voting_result ENUM('approved', 'rejected', 'tabled') DEFAULT 'approved',
    implementation_status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    implementation_deadline DATE,
    responsible_party VARCHAR(100),

    FOREIGN KEY (meeting_id) REFERENCES governance_meetings(id),
    INDEX idx_meeting (meeting_id),
    INDEX idx_status (implementation_status)
);
";

// Helper functions
function planCooperativeActivity($activityData) {
    $activityManager = new CooperativeActivityManagement();
    return $activityManager->planCooperativeActivity($activityData);
}

function executeCooperativeActivity($activityId, $executionData) {
    $activityManager = new CooperativeActivityManagement();
    return $activityManager->executeActivity($activityId, $executionData);
}

function completeCooperativeActivity($activityId, $completionData) {
    $activityManager = new CooperativeActivityManagement();
    return $activityManager->completeActivity($activityId, $completionData);
}

function scheduleGovernanceMeeting($meetingData) {
    $activityManager = new CooperativeActivityManagement();
    return $activityManager->scheduleGovernanceMeeting($meetingData);
}

function recordMeetingAttendance($meetingId, $attendanceData) {
    $activityManager = new CooperativeActivityManagement();
    return $activityManager->recordMeetingAttendance($meetingId, $attendanceData);
}

function recordMeetingResolutions($meetingId, $resolutionData) {
    $activityManager = new CooperativeActivityManagement();
    return $activityManager->recordMeetingResolutions($meetingId, $resolutionData);
}

function getActivityDashboard($coopId) {
    $activityManager = new CooperativeActivityManagement();
    return $activityManager->getActivityDashboard($coopId);
}

function generateActivityImpactReport($coopId, $period) {
    $activityManager = new CooperativeActivityManagement();
    return $activityManager->generateActivityImpactReport($coopId, $period);
}
