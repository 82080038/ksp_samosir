<?php
/**
 * Koperasi Accounting System
 * KSP Samosir - Cooperative Accounting Management
 */

/**
 * Create journal entry with double-entry bookkeeping
 */
function createJournalEntry($entry_date, $description, $entries, $reference_number = null) {
    runInTransaction(function($conn) use ($entry_date, $description, $entries, $reference_number) {
        // Insert journal header
        $stmt = $conn->prepare("INSERT INTO jurnal (entry_date, description, reference_number, status, posted_by) VALUES (?, ?, ?, 'draft', ?)");
        $stmt->bind_param('sssi', $entry_date, $description, $reference_number, $_SESSION['user']['id']);
        $stmt->execute();
        $jurnal_id = $conn->insert_id;
        $stmt->close();
        
        // Insert journal details
        $total_debit = 0;
        $total_credit = 0;
        
        foreach ($entries as $entry) {
            $stmt = $conn->prepare("INSERT INTO jurnal_detail (jurnal_id, coa_id, debit, credit, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('iidss', $jurnal_id, $entry['coa_id'], $entry['debit'], $entry['credit'], $entry['description']);
            $stmt->execute();
            $stmt->close();
            
            $total_debit += $entry['debit'];
            $total_credit += $entry['credit'];
        }
        
        // Validate balance
        if (abs($total_debit - $total_credit) > 0.01) {
            throw new Exception("Journal entry must balance. Debit: $total_debit, Credit: $total_credit");
        }
        
        return $jurnal_id;
    });
}

/**
 * Post journal entry to general ledger
 */
function postJournal($jurnal_id) {
    runInTransaction(function($conn) use ($jurnal_id) {
        // Update journal status
        $stmt = $conn->prepare("UPDATE jurnal SET status = 'posted', posted_by = ?, posted_at = NOW() WHERE id = ?");
        $stmt->bind_param('ii', $_SESSION['user']['id'], $jurnal_id);
        $stmt->execute();
        $stmt->close();
        
        // Trigger will automatically update buku_besar via trigger
        return true;
    });
}

/**
 * Calculate trial balance
 */
function getTrialBalance($date_from, $date_to) {
    $sql = "SELECT 
                coa.code,
                coa.name,
                coa.type,
                SUM(CASE WHEN bb.debit > 0 THEN bb.debit ELSE 0 END) as debit,
                SUM(CASE WHEN bb.credit > 0 THEN bb.credit ELSE 0 END) as credit,
                SUM(bb.saldo) as saldo
            FROM coa 
            LEFT JOIN buku_besar bb ON coa.id = bb.coa_id
            WHERE coa.is_active = 1 
            AND bb.tanggal BETWEEN ? AND ?
            GROUP BY coa.id, coa.code, coa.name, coa.type
            ORDER BY coa.code";
    
    return fetchAll($sql, [$date_from, $date_to], 'ss') ?? [];
}

/**
 * Get income statement
 */
function getIncomeStatement($date_from, $date_to) {
    $sql = "SELECT 
                coa.name,
                SUM(CASE WHEN coa.type = 'revenue' THEN bb.credit ELSE 0 END) as pendapatan,
                SUM(CASE WHEN coa.type = 'expense' THEN bb.debit ELSE 0 END) as beban
            FROM coa 
            JOIN buku_besar bb ON coa.id = bb.coa_id
            WHERE coa.type IN ('revenue', 'expense') 
            AND coa.is_active = 1
            AND bb.tanggal BETWEEN ? AND ?
            GROUP BY coa.id, coa.name
            ORDER BY coa.code";
    
    $results = fetchAll($sql, [$date_from, $date_to], 'ss');
    
    $total_pendapatan = 0;
    $total_beban = 0;
    
    foreach ($results as $row) {
        $total_pendapatan += $row['pendapatan'];
        $total_beban += $row['beban'];
    }
    
    return [
        'details' => $results,
        'total_pendapatan' => $total_pendapatan,
        'total_beban' => $total_beban,
        'laba_rugi' => $total_pendapatan - $total_beban
    ];
}

/**
 * Get balance sheet
 */
function getBalanceSheet($as_of_date) {
    $sql = "SELECT 
                coa.code,
                coa.name,
                coa.type,
                SUM(bb.saldo) as saldo
            FROM coa 
            JOIN buku_besar bb ON coa.id = bb.coa_id
            WHERE coa.is_active = 1 
            AND bb.tanggal <= ?
            GROUP BY coa.id, coa.code, coa.name, coa.type
            ORDER BY coa.code";
    
    return fetchAll($sql, [$as_of_date], 's') ?? [];
}

/**
 * Calculate SHU (Sisa Hasil Usaha)
 */
function calculateSHU($periode_start, $periode_end) {
    // Get total SHU for the period
    $sql = "SELECT 
                SUM(CASE WHEN coa.type = 'revenue' THEN bb.credit ELSE 0 END) - 
                SUM(CASE WHEN coa.type = 'expense' THEN bb.debit ELSE 0 END) as total_shu
            FROM coa 
            JOIN buku_besar bb ON coa.id = bb.coa_id
            WHERE coa.type IN ('revenue', 'expense') 
            AND coa.is_active = 1
            AND bb.tanggal BETWEEN ? AND ?";
    
    $result = fetchRow($sql, [$periode_start, $periode_end], 'ss');
    return $result['total_shu'] ?? 0;
}

/**
 * Distribute SHU to members
 */
function distributeSHU($shu_periode_id) {
    runInTransaction(function($conn) use ($shu_periode_id) {
        // Get SHU periode info
        $shu_info = fetchRow("SELECT * FROM shu_periode WHERE id = ?", [$shu_periode_id], 'i');
        if (!$shu_info) return false;
        
        // Get all active members with their savings
        $members = fetchAll("SELECT a.id, a.nama_lengkap, 
                                COALESCE(SUM(s.saldo), 0) as total_simpanan
                                FROM anggota a
                                LEFT JOIN simpanan s ON a.id = s.anggota_id AND s.status = 'aktif'
                                WHERE a.status = 'aktif'
                                GROUP BY a.id, a.nama_lengkap");
        
        $total_simpanan = array_sum(array_column($members, 'total_simpanan'));
        
        foreach ($members as $member) {
            $persentase_shu = ($member['total_simpanan'] / $total_simpanan) * 100;
            $jumlah_shu = $shu_info['total_shu'] * ($persentase_shu / 100);
            
            // Insert or update SHU anggota
            $sql = "INSERT INTO shu_anggota (anggota_id, shu_periode_id, jumlah_simpanan, total_shu, persentase_shu, status)
                      VALUES (?, ?, ?, ?, ?, 'calculated')
                      ON DUPLICATE KEY UPDATE 
                      total_shu = VALUES(total_shu), persentase_shu = VALUES(persentase_shu)";
            
            executeNonQuery($sql, [$member['id'], $shu_periode_id, $member['total_simpanan'], $jumlah_shu, $persentase_shu], 'iiidd');
        }
        
        // Update SHU periode status
        executeNonQuery("UPDATE shu_periode SET status = 'distributed', distributed_at = NOW() WHERE id = ?", [$shu_periode_id], 'i');
        
        return true;
    });
}

/**
 * Record modal pokok changes
 */
function recordModalPokok($anggota_id, $jumlah, $jenis, $tanggal, $description, $bukti = null) {
    runInTransaction(function($conn) use ($anggota_id, $jumlah, $jenis, $tanggal, $description, $bukti) {
        // Create journal entry for modal pokok
        $coa_id = (fetchRow("SELECT id FROM coa WHERE code = '3000'") ?? [])['id'] ?? 0;
        
        $entries = [];
        if ($jenis === 'masuk') {
            $entries[] = ['coa_id' => $coa_id, 'debit' => $jumlah, 'credit' => 0, 'description' => "Modal pokok $anggota_id - $description"];
            $entries[] = ['coa_id' => (fetchRow("SELECT id FROM coa WHERE code = '1000'") ?? [])['id'] ?? 0, 'debit' => 0, 'credit' => $jumlah, 'description' => "Modal pokok $anggota_id - $description"];
        } else {
            $entries[] = ['coa_id' => (fetchRow("SELECT id FROM coa WHERE code = '1000'") ?? [])['id'] ?? 0, 'debit' => $jumlah, 'credit' => 0, 'description' => "Penarikan modal pokok $anggota_id - $description"];
            $entries[] = ['coa_id' => $coa_id, 'debit' => 0, 'credit' => $jumlah, 'description' => "Penarikan modal pokok $anggota_id - $description"];
        }
        
        $jurnal_id = createJournalEntry($tanggal, $description, $entries, "MP-$anggota_id");
        
        // Record modal pokok transaction
        $stmt = $conn->prepare("INSERT INTO modal_pokok (anggota_id, jumlah, jenis, tanggal, description, bukti, status) VALUES (?, ?, ?, ?, ?, ?, 'draft')");
        $stmt->bind_param('idsssss', $anggota_id, $jumlah, $jenis, $tanggal, $description, $bukti);
        $stmt->execute();
        $stmt->close();
        
        return $jurnal_id;
    });
}

/**
 * Get cooperative settings
 */
function getPengaturan($key = null) {
    if ($key) {
        return fetchRow("SELECT value FROM pengaturan_koperasi WHERE setting_key = ? AND is_active = 1", [$key], 's');
    }
    return fetchAll("SELECT * FROM pengaturan_koperasi WHERE is_active = 1 ORDER BY setting_key") ?? [];
}

/**
 * Update cooperative setting
 */
function updatePengaturan($key, $value) {
    return executeNonQuery(
        "UPDATE pengaturan_koperasi SET value = ?, updated_at = NOW() WHERE setting_key = ?",
        [$value, $key],
        'ss'
    );
}

/**
 * Generate financial reports
 */
function generateLaporanKeuangan($periode, $jenis) {
    switch ($jenis) {
        case 'neraca':
            return getBalanceSheet($periode);
        case 'laba_rugi':
            return getIncomeStatement(date('Y-m-01', strtotime($periode)), $periode);
        case 'arus_kas':
            return getTrialBalance(date('Y-m-01', strtotime($periode)), $periode);
        default:
            return [];
    }
}

/**
 * Helper to format currency in accounting format
 */
function formatAccounting($amount) {
    return number_format($amount, 2, ',', '.');
}

/**
 * Validate journal entry balance
 */
function validateJournalBalance($entries) {
    $total_debit = array_sum(array_column($entries, 'debit'));
    $total_credit = array_sum(array_column($entries, 'credit'));
    return abs($total_debit - $total_credit) < 0.01;
}
?>
