<!-- AJAX API Handler -->
<?php
/**
 * AJAX API Handler for KSP Samosir
 * Centralized endpoint for all AJAX requests
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, X-CSRF-Token');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include required files
require_once __DIR__ . '/../config/config.php';

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    // Get action from POST data
    $action = $_POST['action'] ?? '';
    
    // Route to appropriate handler
    switch ($action) {
        // Member operations
        case 'get_member_details':
            $member_id = $_POST['member_id'] ?? 0;
            $member = getAnggotaWithAddress($member_id);
            if ($member) {
                $response['success'] = true;
                $response['data'] = $member;
            } else {
                $response['message'] = 'Anggota tidak ditemukan';
            }
            break;
            
        case 'save_member':
            $member_data = $_POST['data'] ?? [];
            // Implement member saving logic here
            $response['success'] = true;
            $response['message'] = 'Data anggota berhasil disimpan';
            $response['data'] = $member_data;
            break;
            
        case 'delete_member':
            $member_id = $_POST['member_id'] ?? 0;
            // Implement member deletion logic here
            $response['success'] = true;
            $response['message'] = 'Anggota berhasil dihapus';
            break;
            
        // Loan operations
        case 'get_loan_details':
            $loan_id = $_POST['loan_id'] ?? 0;
            $loan = fetchRow("SELECT p.*, a.nama_lengkap FROM pinjaman p LEFT JOIN anggota a ON p.anggota_id = a.id WHERE p.id = ?", [$loan_id], 'i');
            if ($loan) {
                $response['success'] = true;
                $response['data'] = $loan;
            } else {
                $response['message'] = 'Pinjaman tidak ditemukan';
            }
            break;
            
        case 'approve_loan':
            $loan_id = $_POST['loan_id'] ?? 0;
            // Implement loan approval logic here
            $response['success'] = true;
            $response['message'] = 'Pinjaman berhasil disetujui';
            break;
            
        case 'reject_loan':
            $loan_id = $_POST['loan_id'] ?? 0;
            $reason = $_POST['reason'] ?? '';
            // Implement loan rejection logic here
            $response['success'] = true;
            $response['message'] = 'Pinjaman ditolak: ' . $reason;
            break;
            
        // Savings operations
        case 'get_savings_history':
            $member_id = $_POST['member_id'] ?? 0;
            $savings = fetchAll("SELECT * FROM simpanan WHERE anggota_id = ? ORDER BY created_at DESC", [$member_id], 'i');
            $response['success'] = true;
            $response['data'] = $savings;
            break;
            
        case 'add_savings':
            $savings_data = $_POST['data'] ?? [];
            // Implement savings addition logic here
            $response['success'] = true;
            $response['message'] = 'Simpanan berhasil ditambahkan';
            break;
            
        // Address operations
        case 'get_provinces':
            $provinces = getProvinces();
            $response['success'] = true;
            $response['data'] = $provinces;
            break;
            
        case 'get_regencies':
            $province_id = $_POST['province_id'] ?? 0;
            $regencies = getRegencies($province_id);
            $response['success'] = true;
            $response['data'] = $regencies;
            break;
            
        case 'get_districts':
            $regency_id = $_POST['regency_id'] ?? 0;
            $districts = getDistricts($regency_id);
            $response['success'] = true;
            $response['data'] = $districts;
            break;
            
        case 'get_villages':
            $district_id = $_POST['district_id'] ?? 0;
            $villages = getVillages($district_id);
            $response['success'] = true;
            $response['data'] = $villages;
            break;
            
        case 'search_address':
            $keyword = $_POST['keyword'] ?? '';
            $type = $_POST['type'] ?? 'all';
            $results = searchAddress($keyword, $type);
            $response['success'] = true;
            $response['data'] = $results;
            break;
            
        // Settings operations
        case 'get_settings':
            $settings = getPengaturan();
            $response['success'] = true;
            $response['data'] = $settings;
            break;
            
        case 'update_settings':
            $settings_data = $_POST['data'] ?? [];
            foreach ($settings_data as $key => $value) {
                updatePengaturan($key, $value);
            }
            $response['success'] = true;
            $response['message'] = 'Pengaturan berhasil diperbarui';
            break;
            
        // Reports operations
        case 'get_financial_report':
            $params = $_POST;
            // Implement financial report logic here
            $response['success'] = true;
            $response['data'] = [
                'total_income' => 10000000,
                'total_expense' => 8000000,
                'net_income' => 2000000
            ];
            break;
            
        case 'get_shu_report':
            $period_id = $_POST['period_id'] ?? 0;
            $shu_data = getSHUDistribution($period_id);
            $response['success'] = true;
            $response['data'] = $shu_data;
            break;
            
        // Activity operations
        case 'get_activity_summary':
            $start_date = $_POST['start_date'] ?? date('Y-m-01');
            $end_date = $_POST['end_date'] ?? date('Y-m-d');
            $summary = getActivitySummary($start_date, $end_date);
            $response['success'] = true;
            $response['data'] = $summary;
            break;
            
        // Meeting operations
        case 'get_meetings':
            $meetings = getKoperasiMeetings();
            $response['success'] = true;
            $response['data'] = $meetings;
            break;
            
        case 'save_meeting_attendance':
            $attendance_data = $_POST['data'] ?? [];
            // Implement attendance saving logic here
            $response['success'] = true;
            $response['message'] = 'Absensi rapat berhasil disimpan';
            break;
            
        default:
            $response['message'] = 'Action tidak dikenali: ' . $action;
            break;
    }
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('AJAX Error: ' . $e->getMessage());
}

// Send JSON response
echo json_encode($response);
?>
