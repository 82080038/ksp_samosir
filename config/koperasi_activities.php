<?php
/**
 * Koperasi Activity Management System
 * Based on UU No. 25 Tahun 1992 and Koperasi Polres Samosir Requirements
 */

/**
 * Get all koperasi activities
 */
function getKoperasiActivities($type = null) {
    $sql = "SELECT * FROM koperasi_activities WHERE is_active = 1";
    $params = [];
    
    if ($type) {
        $sql .= " AND activity_type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY activity_type, activity_name";
    
    return fetchAll($sql, $params) ?? [];
}

/**
 * Record koperasi transaction
 */
function recordKoperasiTransaction($activity_code, $member_id, $transaction_type, $amount, $description, $reference_number = null) {
    $sql = "INSERT INTO koperasi_transactions 
              (transaction_date, activity_code, member_id, transaction_type, amount, description, reference_number, created_by)
              VALUES (CURDATE(), ?, ?, ?, ?, ?, ?, ?)";
    
    return executeNonQuery($sql, [$activity_code, $member_id, $transaction_type, $amount, $description, $reference_number, $_SESSION['user']['id']], 'sssdsss');
}

/**
 * Post koperasi transaction
 */
function postKoperasiTransaction($transaction_id) {
    return executeNonQuery(
        "UPDATE koperasi_transactions SET status = 'posted', approved_by = ?, approved_at = NOW() WHERE id = ?",
        [$_SESSION['user']['id'], $transaction_id],
        'ii'
    );
}

/**
 * Get SHU components
 */
function getSHUComponents() {
    return fetchAll("SELECT * FROM shu_components WHERE is_active = 1 ORDER BY component_type, percentage_weight DESC") ?? [];
}

/**
 * Create SHU calculation period
 */
function createSHUPeriod($period_start, $period_end, $calculation_method = 'standard') {
    $sql = "INSERT INTO shu_periods (period_start, period_end, calculation_method, status, calculated_by, calculated_at)
              VALUES (?, ?, ?, 'draft', ?, NOW())";
    
    return executeNonQuery($sql, [$period_start, $period_end, $calculation_method, $_SESSION['user']['id']], 'ssssi');
}

/**
 * Calculate SHU for a period
 */
function calculateSHUForPeriod($period_id) {
    // Get period details
    $period = fetchRow("SELECT * FROM shu_periods WHERE id = ?", [$period_id], 'i');
    if (!$period) return false;
    
    // Calculate total SHU from transactions
    $sql = "SELECT 
                SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END) as total_shu
            FROM koperasi_transactions 
            WHERE transaction_date BETWEEN ? AND ? 
            AND status = 'posted'";
    
    $result = fetchRow($sql, [$period['period_start'], $period['period_end']], 'ss');
    $total_shu = $result['total_shu'] ?? 0;
    
    // Update total SHU
    executeNonQuery("UPDATE shu_periods SET total_shu = ? WHERE id = ?", [$total_shu, $period_id], 'di');
    
    // Distribute SHU to members based on components
    $components = getSHUComponents();
    
    foreach ($components as $component) {
        $component_shu = $total_shu * ($component['percentage_weight'] / 100);
        
        // Get members who participated in this component
        $member_sql = "SELECT DISTINCT kt.member_id 
                       FROM koperasi_transactions kt
                       JOIN koperasi_activities ka ON kt.activity_code = ka.activity_code
                       WHERE kt.transaction_date BETWEEN ? AND ? 
                       AND kt.status = 'posted'
                       AND kt.member_id IS NOT NULL";
        
        $members = fetchAll($member_sql, [$period['period_start'], $period['period_end']], 'ss');
        
        foreach ($members as $member) {
            // Calculate member's share based on their participation
            $member_base = getMemberSHUBase($member['member_id'], $component['component_code'], $period['period_start'], $period['period_end']);
            $member_shu = $component_shu * ($member_base / getTotalMemberBase($component['component_code'], $period['period_start'], $period['period_end']));
            
            executeNonQuery(
                "INSERT INTO shu_member_distribution 
                 (shu_period_id, member_id, component_code, base_amount, calculated_shu, percentage_share, status)
                 VALUES (?, ?, ?, ?, ?, ?, 'calculated')
                 ON DUPLICATE KEY UPDATE 
                 base_amount = VALUES(base_amount), 
                 calculated_shu = VALUES(calculated_shu), 
                 percentage_share = VALUES(percentage_share)",
                [$period_id, $member['member_id'], $component['component_code'], $member_base, $member_shu, 0],
                'iiiddsd'
            );
        }
    }
    
    // Update period status
    executeNonQuery("UPDATE shu_periods SET status = 'calculated', calculated_at = NOW() WHERE id = ?", [$period_id], 'i');
    
    return true;
}

/**
 * Get member's SHU base amount for a component
 */
function getMemberSHUBase($member_id, $component_code, $period_start, $period_end) {
    $sql = "SELECT COALESCE(SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END), 0) as base_amount
            FROM koperasi_transactions kt
            WHERE kt.member_id = ? 
            AND kt.transaction_date BETWEEN ? AND ?
            AND kt.status = 'posted'
            AND kt.activity_code IN (
                SELECT activity_code FROM koperasi_activities 
                WHERE activity_type = (
                    CASE 
                        WHEN ? IN ('jasa_modal', 'jasa_usaha') THEN (
                            CASE 
                                WHEN ? = 'jasa_modal' THEN 'simpanan'
                                WHEN ? = 'jasa_usaha' THEN 'jual_beli'
                            END
                        )
                        ELSE ?
                    END
                )
            )";
    
    $component_type = '';
    switch ($component_code) {
        case 'JASA_MODAL_ANGGOTA':
        case 'JASA_MODAL_PENGURUS':
            $component_type = 'jasa_modal';
            break;
        case 'JASA_USA_ANGGOTA':
        case 'JASA_USA_KOPERASI':
            $component_type = 'jasa_usaha';
            break;
        default:
            $component_type = substr($component_code, 0, 4);
    }
    
    $result = fetchRow($sql, [$member_id, $period_start, $period_end, $component_type, $component_type, $component_type], 'issss');
    return $result['base_amount'] ?? 0;
}

/**
 * Get total member base for a component
 */
function getTotalMemberBase($component_code, $period_start, $period_end) {
    $sql = "SELECT COALESCE(SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END), 0) as total_base
            FROM koperasi_transactions kt
            WHERE kt.transaction_date BETWEEN ? AND ?
            AND kt.status = 'posted'
            AND kt.member_id IS NOT NULL
            AND kt.activity_code IN (
                SELECT activity_code FROM koperasi_activities 
                WHERE activity_type = ?
            )";
    
    $activity_type = '';
    switch ($component_code) {
        case 'JASA_MODAL_ANGGOTA':
        case 'JASA_MODAL_PENGURUS':
            $activity_type = 'simpanan';
            break;
        case 'JASA_USA_ANGGOTA':
        case 'JASA_USA_KOPERASI':
            $activity_type = 'jual_beli';
            break;
        default:
            $activity_type = substr($component_code, 0, 4);
    }
    
    $result = fetchRow($sql, [$period_start, $period_end, $activity_type], 'sss');
    return $result['total_base'] ?? 1;
}

/**
 * Get SHU periods
 */
function getSHUPeriods($status = null) {
    $sql = "SELECT * FROM shu_periods";
    $params = [];
    
    if ($status) {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY period_start DESC";
    
    return fetchAll($sql, $params) ?? [];
}

/**
 * Get SHU distribution for a period
 */
function getSHUDistribution($period_id) {
    return fetchAll("
        SELECT smd.*, a.nama_lengkap, sc.component_name, sc.component_type
        FROM shu_member_distribution smd
        JOIN anggota a ON smd.member_id = a.id
        JOIN shu_components sc ON smd.component_code = sc.component_code
        WHERE smd.shu_period_id = ?
        ORDER BY a.nama_lengkap, sc.component_type
    ", [$period_id], 'i') ?? [];
}

/**
 * Create koperasi meeting
 */
function createKoperasiMeeting($meeting_type, $title, $meeting_date, $location, $agenda, $description) {
    $sql = "INSERT INTO koperasi_meetings 
              (meeting_type, meeting_title, meeting_date, meeting_location, agenda, description, created_by)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    return executeNonQuery($sql, [$meeting_type, $title, $meeting_date, $location, $agenda, $description, $_SESSION['user']['id']], 'sssssss');
}

/**
 * Get koperasi meetings
 */
function getKoperasiMeetings($type = null, $status = null) {
    $sql = "SELECT * FROM koperasi_meetings";
    $params = [];
    
    if ($type) {
        $sql .= " WHERE meeting_type = ?";
        $params[] = $type;
    }
    
    if ($status) {
        $sql .= $type ? " AND status = ?" : " WHERE status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY meeting_date DESC";
    
    return fetchAll($sql, $params) ?? [];
}

/**
 * Record meeting attendance
 */
function recordMeetingAttendance($meeting_id, $member_id, $attendance_status, $notes = null) {
    $sql = "INSERT INTO meeting_attendance (meeting_id, member_id, attendance_status, notes)
              VALUES (?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE 
              attendance_status = VALUES(attendance_status), 
              notes = VALUES(notes)";
    
    return executeNonQuery($sql, [$meeting_id, $member_id, $attendance_status, $notes], 'isss');
}

/**
 * Create meeting decision
 */
function createMeetingDecision($meeting_id, $decision_number, $title, $content, $type, $responsible_person, $target_date) {
    $sql = "INSERT INTO meeting_decisions 
              (meeting_id, decision_number, decision_title, decision_content, decision_type, responsible_person, target_date)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    return executeNonQuery($sql, [$meeting_id, $decision_number, $title, $content, $type, $responsible_person, $target_date], 'iissssss');
}

/**
 * Record supervision
 */
function recordSupervision($supervisor_id, $supervised_person_id, $supervision_type, $findings, $recommendations, $action_required, $follow_up_date) {
    $sql = "INSERT INTO supervision_records 
              (supervision_date, supervisor_id, supervised_person_id, supervision_type, findings, recommendations, action_required, follow_up_date, created_by)
              VALUES (CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?)";
    
    return executeNonQuery($sql, [$supervisor_id, $supervised_person_id, $supervision_type, $findings, $recommendations, $action_required, $follow_up_date, $_SESSION['user']['id']], 'iisssss');
}

/**
 * Issue sanction
 */
function issueSanction($member_id, $sanction_type, $reason, $period_days, $notes) {
    $sql = "INSERT INTO koperasi_sanctions 
              (sanction_date, member_id, sanction_type, sanction_reason, sanction_period_days, issued_by, start_date, end_date, notes)
              VALUES (CURDATE(), ?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY), ?)";
    
    return executeNonQuery($sql, [$member_id, $sanction_type, $reason, $period_days, $_SESSION['user']['id'], $period_days, $notes], 'isssss');
}

/**
 * Get activity summary
 */
function getActivitySummary($date_from, $date_to) {
    return fetchRow("
        SELECT 
            COUNT(*) as total_transactions,
            SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END) as total_debit,
            SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END) as total_credit,
            COUNT(DISTINCT member_id) as active_members,
            COUNT(DISTINCT activity_code) as active_activities
        FROM koperasi_transactions 
        WHERE transaction_date BETWEEN ? AND ? 
        AND status = 'posted'
    ", [$date_from, $date_to], 'ss');
}

/**
 * Get SHU summary
 */
function getSHUSummary() {
    return fetchRow("
        SELECT 
            COUNT(*) as total_periods,
            SUM(total_shu) as total_shu_distributed,
            COUNT(CASE WHEN status = 'distributed' THEN 1 END) as distributed_periods,
            AVG(total_shu) as average_shu_per_period
        FROM shu_periods
    ");
}
?>
