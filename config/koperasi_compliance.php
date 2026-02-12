<?php
/**
 * Koperasi Compliance Validator
 * Ensures all activities comply with UU No. 25 Tahun 1992 and AD/ART
 */

/**
 * Validate loan amount against maximum allowed
 */
function validateLoanAmount($amount, $member_id = null) {
    // Get maximum loan limit from settings
    $max_loan_limit = getPengaturan('max_loan_limit');
    if ($max_loan_limit && $amount > $max_loan_limit) {
        throw new Exception("Pinjaman melebihi batas maksimal yang ditetapkan: Rp " . formatCurrency($max_loan_limit));
    }
    
    // Check if member has outstanding loans
    if ($member_id) {
        $outstanding = fetchRow(
            "SELECT COALESCECE(SUM(jumlah_pinjaman - COALESCE(SUM(total_dibayar), 0)), 0) as outstanding 
             FROM pinjaman 
             WHERE anggota_id = ? AND status IN ('disetujui', 'dicairkan')",
            [$member_id],
            'i'
        );
        
        if ($outstanding['outstanding'] > 0) {
            throw new Exception("Anggota memiliki pinjaman belum lunas sebesar Rp " . formatCurrency($outstanding['outstanding']));
        }
    }
    
    return true;
}

/**
 * Validate SHU percentage distribution
 */
function validateSHUPercentages($components) {
    $total_percentage = array_sum(array_column($components, 'percentage_weight'));
    
    if ($total_percentage > 100) {
        throw new Exception("Total persentase SHU tidak boleh melebihi 100%. Saat ini: " . $total_percentage . "%");
    }
    
    // Check individual component limits
    $limits = [
        'jasa_modal' => 40, // Maksimal 40% untuk jasa modal
        'jasa_usaha' => 35, // Maksimal 35% untuk jasa usaha
        'pendidikan_sosial' => 3, // Minimal 3% untuk pendidikan sosial
    ];
    
    foreach ($components as $component) {
        $component_type = substr($component['component_code'], 0, 4);
        
        if (isset($limits[$component_type])) {
            if ($component['percentage_weight'] > $limits[$component_type]) {
                throw new Exception("Persentase {$component['component_name']} melebihi batas maksimal {$limits[$component_type]}%");
            }
        }
    }
    
    return true;
}

/**
 * Validate meeting quorum
 */
function validateMeetingQuorum($meeting_type, $total_members, $attendees) {
    $quorum_required = 0;
    
    switch ($meeting_type) {
        case 'rapat_anggota':
            $quorum_required = ceil($total_members / 2); // Minimal 50%
            break;
        case 'rapat_pengurus':
            $quorum_required = count(getAllPengurus()); // Semua pengurus harus hadir
            break;
        case 'rapat_pengawas':
            $quorum_required = count(getAllPengawas()); // Semua pengawas harus hadir
            break;
        default:
            $quorum_required = ceil($total_members / 2);
    }
    
    if ($attendees < $quorum_required) {
        throw new Exception("Kuorum tidak terpenuhi. Diperlukan minimal {$quorum} dari {$total_members} anggota");
    }
    
    return true;
}

/**
 * Validate sanction authority
 */
function validateSanctionAuthority($member_id, $sanction_type, $issued_by) {
    // Check if issuer has authority
    $issuer_role = getCurrentUser()['role'];
    
    // Only pengawas can issue serious sanctions
    if (in_array($sanction_type, ['pemberhentian', 'pemecatan']) && $issuer_role !== 'admin') {
        throw new Exception("Hanya admin yang dapat memberikan sanksi berat: {$sanction_type}");
    }
    
    // Check if member being sanctioned is not pengurus (unless by higher authority)
    $member_role = getMemberRole($member_id);
    if ($member_role === 'admin' && $issued_by !== $member_id && $sanction_type !== 'teguran_lisan') {
        throw new Exception("Tidak dapat memberikan sanksi kepada admin kecuali teguran lisan");
    }
    
    return true;
}

/**
 * Validate interest rates
 */
function validateInterestRates($interest_type, $rate) {
    $settings = [
        'bunga_simpanan_wajib' => getPengaturan('bunga_simpanan_wajib'),
        'bunga_simpanan_sukarela' => getPengaturan('bunga_simpanan_sukarela'),
        'bunga_pinjaman' => getPengaturan('bunga_pinjaman'),
    ];
    
    if (isset($settings[$interest_type])) {
        $max_rate = $settings[$interest_type]['value'];
        if ($rate > $max_rate) {
            throw new Exception("Bunga {$interest_type} melebihi batas yang ditetapkan: " . $max_rate . "%");
        }
    }
    
    return true;
}

/**
 * Check if member is eligible for loan
 */
function validateMemberEligibility($member_id, $loan_amount) {
    $member = getAnggotaWithAddress($member_id);
    
    if (!$member) {
        throw new Exception("Anggota tidak ditemukan");
    }
    
    if ($member['status'] !== 'aktif') {
        throw new Exception("Anggota tidak aktif. Status: " . $member['status']);
    }
    
    // Check if member has required savings
    $required_savings = getPengaturan('simpanan_pokok_minimum');
    $current_savings = getMemberTotalSavings($member_id);
    
    if ($required_savings && $current_savings < $required_savings) {
        throw new Exception("Anggota harus memiliki simpanan minimal Rp " . formatCurrency($required_savings));
    }
    
    return validateLoanAmount($loan_amount, $member_id);
}

/**
 * Validate transaction compliance
 */
function validateTransactionCompliance($activity_code, $amount, $member_id = null) {
    $activity = fetchRow("SELECT * FROM koperasi_activities WHERE activity_code = ?", [$activity_code], 's');
    
    if (!$activity) {
        throw new Exception("Aktivitas koperasi tidak dikenali: {$activity_code}");
    }
    
    // Check if activity is allowed for member
    if ($member_id && $activity['activity_type'] === 'pinjaman') {
        validateMemberEligibility($member_id, $amount);
    }
    
    // Check daily/transaction limits
    $daily_limit = getPengaturan('daily_transaction_limit');
    if ($daily_limit && $amount > $daily_limit) {
        throw new Exception("Transaksi melebihi batas harian: Rp " . formatCurrency($daily_limit));
    }
    
    return true;
}

/**
 * Generate compliance report
 */
function generateComplianceReport($date_from, $date_to) {
    $report = [
        'period' => "{$date_from} s/d {$date_to}",
        'violations' => [],
        'warnings' => [],
        'compliance_score' => 0
    ];
    
    // Check loan compliance
    $loans = fetchAll("
        SELECT kt.*, a.nama_lengkap 
        FROM koperasi_transactions kt
        JOIN anggota a ON kt.member_id = a.id
        WHERE kt.activity_code LIKE 'PINJ_%' 
        AND kt.transaction_date BETWEEN ? AND ?
        AND kt.status = 'posted'
    ", [$date_from, $date_to], 'ss');
    
    foreach ($loans as $loan) {
        try {
            validateLoanAmount($loan['amount'], $loan['member_id']);
        } catch (Exception $e) {
            $report['violations'][] = [
                'type' => 'Pinjaman',
                'member' => $loan['nama_lengkap'],
                'amount' => $loan['amount'],
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Check SHU compliance
    $shu_periods = getSHUPeriods('distributed');
    foreach ($shu_periods as $period) {
        $distributions = getSHUDistribution($period['id']);
        try {
            validateSHUPercentages($distributions);
        } catch (Exception $e) {
            $report['violations'][] = [
                'type' => 'SHU',
                'period' => $period['period_start'] . ' - ' . $period['period_end'],
                'error' => $e->getMessage()
            ];
        }
    }
    
    return $report;
}

/**
 * Check if activity requires approval
 */
function requiresApproval($activity_code) {
    $approval_required = [
        'PINJ_ANGGOTA' => true,
        'PINJ_INVESTASI' => true,
        'PINJ_KONSUMTIF' => true,
        'JUAL_EKSTERNAL_LARGE' => true
    ];
    
    return isset($approval_required[$activity_code]) ? $approval_required[$activity_code] : false;
}

/**
 * Get all pengurus (management team)
 */
function getAllPengurus() {
    return fetchAll("
        SELECT a.id, a.nama_lengkap, a.jabatan 
        FROM anggota a
        WHERE a.jabatan IN ('Ketua', 'Wakil Ketua', 'Sekretaris', 'Wakil Sekretaris', 'Bendahara')
        AND a.status = 'aktif'
        ORDER BY 
            CASE a.jabatan
                WHEN 'Ketua' THEN 1
                WHEN 'Wakil Ketua' THEN 2
                WHEN 'Sekretaris' THEN 3
                WHEN 'Wakil Sekretaris' THEN 4
                WHEN 'Bendahara' THEN 5
                ELSE 6
            END
    ");
}

/**
 * Get all pengawas (supervisory team)
 */
function getAllPengawas() {
    return fetchAll("
        SELECT a.id, a.nama_lengkap 
        FROM anggota a
        WHERE a.jabatan IN ('Ketua Pengawas', 'Anggota Pengawas')
        AND a.status = 'aktif'
        ORDER BY a.nama_lengkap
    ");
}

/**
 * Get member role
 */
function getMemberRole($member_id) {
    $member = fetchRow("SELECT jabatan FROM anggota WHERE id = ?", [$member_id], 'i');
    return $member ? $member['jabatan'] : null;
}

/**
 * Get member total savings
 */
function getMemberTotalSavings($member_id) {
    $result = fetchRow("
        SELECT COALESCE(SUM(saldo), 0) as total_savings
        FROM simpanan 
        WHERE anggota_id = ? AND status = 'aktif'
    ", [$member_id], 'i');
    
    return $result['total_savings'] ?? 0;
}

/**
 * Check if action requires documentation
 */
function requiresDocumentation($action_type) {
    $documentation_required = [
        'pinjaman_approval' => true,
        'shu_calculation' => true,
        'major_sanction' => true,
        'meeting_decision' => true,
        'perubahan_art' => true
    ];
    
    return isset($documentation_required[$action_type]) ? $documentation_required[$action_type] : false;
}

/**
 * Log compliance check
 */
function logComplianceCheck($check_type, $details, $status = 'passed') {
    $sql = "INSERT INTO compliance_logs (check_type, details, status, checked_by, checked_at) VALUES (?, ?, ?, ?, NOW())";
    executeNonQuery($sql, [$check_type, json_encode($details), $status, $_SESSION['user']['id']], 'ssss');
}
?>
