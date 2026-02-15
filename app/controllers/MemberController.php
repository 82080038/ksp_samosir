<?php
require_once __DIR__ . '/BaseController.php';

/**
 * Member Portal Controller
 * Handles member self-service features: registration, profile, statements, applications
 */

class MemberController extends BaseController {

    public function index() {
        // $this->ensureLoginAndRole(['member']); // DISABLED for development
        // For development, simulate logged in member
        $member_id = $_SESSION['user']['id'] ?? 1;

        $member = $this->getMemberData($member_id);
        $stats = $this->getMemberStats($member_id);
        $recentActivities = $this->getRecentActivities($member_id);

        $this->render('member/index', [
            'member' => $member,
            'stats' => $stats,
            'recent_activities' => $recentActivities
        ]);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeRegistration();
            return;
        }

        $this->render('member/register');
    }

    public function storeRegistration() {
        // Collect form data
        $data = [
            'no_anggota' => $this->generateMemberNumber(),
            'nama_lengkap' => sanitize($_POST['nama_lengkap'] ?? ''),
            'nik' => sanitize($_POST['nik'] ?? ''),
            'tempat_lahir' => sanitize($_POST['tempat_lahir'] ?? ''),
            'tanggal_lahir' => $_POST['tanggal_lahir'] ?? '',
            'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
            'alamat' => sanitize($_POST['alamat'] ?? ''),
            'no_hp' => sanitize($_POST['no_hp'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'pekerjaan' => sanitize($_POST['pekerjaan'] ?? ''),
            'pendapatan_bulanan' => floatval($_POST['pendapatan_bulanan'] ?? 0),
            'tanggal_gabung' => date('Y-m-d'),
            'status' => 'pending' // Waiting for approval
        ];

        // Validation
        $errors = $this->validateRegistration($data);
        if (!empty($errors)) {
            $_SESSION['registration_errors'] = $errors;
            $_SESSION['registration_data'] = $data;
            redirect('member/register');
            return;
        }

        // Store registration
        $member_id = $this->saveMemberRegistration($data);

        // Send notification
        $this->sendRegistrationNotification($member_id, $data);

        flashMessage('success', 'Pendaftaran berhasil! Silakan tunggu persetujuan dari pengurus koperasi.');
        redirect('member/registrationSuccess?ref=' . $data['no_anggota']);
    }

    public function profile() {
        // $this->ensureLoginAndRole(['member']); // DISABLED for development
        $member_id = $_SESSION['user']['id'] ?? 1;

        $member = $this->getMemberData($member_id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateProfile($member_id);
            return;
        }

        $this->render('member/profile', [
            'member' => $member
        ]);
    }

    public function updateProfile($member_id) {
        $data = [
            'nama_lengkap' => sanitize($_POST['nama_lengkap'] ?? ''),
            'alamat' => sanitize($_POST['alamat'] ?? ''),
            'no_hp' => sanitize($_POST['no_hp'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'pekerjaan' => sanitize($_POST['pekerjaan'] ?? ''),
            'pendapatan_bulanan' => floatval($_POST['pendapatan_bulanan'] ?? 0)
        ];

        // Update member data
        executeNonQuery(
            "UPDATE anggota SET nama_lengkap = ?, alamat = ?, no_hp = ?, email = ?, pekerjaan = ?, pendapatan_bulanan = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$data['nama_lengkap'], $data['alamat'], $data['no_hp'], $data['email'], $data['pekerjaan'], $data['pendapatan_bulanan'], $member_id],
            'sssssdi'
        );

        logActivity('UPDATE', 'anggota', $member_id, null, $data);

        flashMessage('success', 'Profile berhasil diperbarui');
        redirect('member/profile');
    }

    public function loanApplication() {
        // $this->ensureLoginAndRole(['member']); // DISABLED for development
        $member_id = $_SESSION['user']['id'] ?? 1;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->submitLoanApplication($member_id);
            return;
        }

        $member = $this->getMemberData($member_id);
        $jenis_pinjaman = fetchAll("SELECT * FROM jenis_pinjaman WHERE is_active = 1");

        $this->render('member/loan_application', [
            'member' => $member,
            'jenis_pinjaman' => $jenis_pinjaman
        ]);
    }

    public function submitLoanApplication($member_id) {
        $data = [
            'anggota_id' => $member_id,
            'jenis_pinjaman_id' => intval($_POST['jenis_pinjaman_id'] ?? 0),
            'jumlah_pinjaman' => floatval($_POST['jumlah_pinjaman'] ?? 0),
            'tenor_bulan' => intval($_POST['tenor_bulan'] ?? 0),
            'tujuan_pinjaman' => sanitize($_POST['tujuan_pinjaman'] ?? ''),
            'tanggal_pengajuan' => date('Y-m-d'),
            'status' => 'pending'
        ];

        // Generate loan number
        $data['no_pinjaman'] = $this->generateLoanNumber();

        // Calculate installments
        $bunga_per_tahun = 12; // 12% per year
        $bunga_bulanan = $bunga_per_tahun / 100 / 12;
        $pokok_bulanan = $data['jumlah_pinjaman'] / $data['tenor_bulan'];
        $bunga_bulanan = $data['jumlah_pinjaman'] * $bunga_bulanan;

        $data['angsuran_pokok'] = $pokok_bulanan;
        $data['angsuran_bunga'] = $bunga_bulanan;
        $data['total_angsuran'] = $pokok_bulanan + $bunga_bulanan;

        // Save loan application
        $loan_id = executeNonQuery(
            "INSERT INTO pinjaman (anggota_id, jenis_pinjaman_id, no_pinjaman, jumlah_pinjaman, bunga_persen, tenor_bulan, angsuran_pokok, angsuran_bunga, total_angsuran, tujuan_pinjaman, tanggal_pengajuan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$data['anggota_id'], $data['jenis_pinjaman_id'], $data['no_pinjaman'], $data['jumlah_pinjaman'], $bunga_per_tahun, $data['tenor_bulan'], $data['angsuran_pokok'], $data['angsuran_bunga'], $data['total_angsuran'], $data['tujuan_pinjaman'], $data['tanggal_pengajuan'], $data['status']],
            'iisdiidddsss'
        )['last_id'];

        // Send notification
        $this->sendLoanApplicationNotification($loan_id, $data);

        logActivity('CREATE', 'pinjaman', $loan_id, null, $data);

        flashMessage('success', 'Pengajuan pinjaman berhasil dikirim. Silakan tunggu persetujuan.');
        redirect('member/loanHistory');
    }

    public function loanHistory() {
        // $this->ensureLoginAndRole(['member']); // DISABLED for development
        $member_id = $_SESSION['user']['id'] ?? 1;

        $loans = fetchAll(
            "SELECT p.*, jp.nama_pinjaman FROM pinjaman p
             LEFT JOIN jenis_pinjaman jp ON p.jenis_pinjaman_id = jp.id
             WHERE p.anggota_id = ? ORDER BY p.tanggal_pengajuan DESC",
            [$member_id],
            'i'
        );

        $this->render('member/loan_history', [
            'loans' => $loans
        ]);
    }

    public function savingsStatement() {
        // $this->ensureLoginAndRole(['member']); // DISABLED for development
        $member_id = $_SESSION['user']['id'] ?? 1;

        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');

        $transactions = fetchAll(
            "SELECT ts.*, js.nama_simpanan FROM transaksi_simpanan ts
             LEFT JOIN simpanan s ON ts.simpanan_id = s.id
             LEFT JOIN jenis_simpanan js ON s.jenis_simpanan_id = js.id
             WHERE s.anggota_id = ? AND ts.tanggal_transaksi BETWEEN ? AND ?
             ORDER BY ts.tanggal_transaksi DESC",
            [$member_id, $start_date, $end_date],
            'iss'
        );

        $current_balance = $this->getCurrentSavingsBalance($member_id);

        $this->render('member/savings_statement', [
            'transactions' => $transactions,
            'current_balance' => $current_balance,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
    }

    public function downloadStatement() {
        // $this->ensureLoginAndRole(['member']); // DISABLED for development
        $member_id = $_SESSION['user']['id'] ?? 1;
        $type = $_GET['type'] ?? 'savings';

        // Generate PDF statement (stub implementation)
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="statement_' . $type . '_' . date('Y-m-d') . '.pdf"');

        echo 'PDF Statement Generation - ' . $type . ' for member ID: ' . $member_id;
        exit;
    }

    private function getMemberData($member_id) {
        return fetchRow(
            "SELECT a.*, u.username, u.email as user_email FROM anggota a
             LEFT JOIN users u ON a.id = u.id
             WHERE a.id = ?",
            [$member_id],
            'i'
        );
    }

    private function getMemberStats($member_id) {
        return [
            'total_savings' => (fetchRow(
                "SELECT COALESCE(SUM(saldo), 0) as total FROM simpanan WHERE anggota_id = ? AND status = 'aktif'",
                [$member_id], 'i'
            ) ?? [])['total'] ?? 0,
            'active_loans' => (fetchRow(
                "SELECT COUNT(*) as total FROM pinjaman WHERE anggota_id = ? AND status IN ('disetujui', 'dicairkan')",
                [$member_id], 'i'
            ) ?? [])['total'] ?? 0,
            'pending_applications' => (fetchRow(
                "SELECT COUNT(*) as total FROM pinjaman WHERE anggota_id = ? AND status = 'pending'",
                [$member_id], 'i'
            ) ?? [])['total'] ?? 0,
            'member_since' => (fetchRow(
                "SELECT tanggal_gabung FROM anggota WHERE id = ?",
                [$member_id], 'i'
            ) ?? [])['tanggal_gabung'] ?? 0
        ];
    }

    private function getRecentActivities($member_id) {
        $activities = [];

        // Recent savings transactions
        $savings = fetchAll(
            "SELECT 'Savings Transaction' as type, CONCAT('Transaction: ', ts.jenis_transaksi) as description, ts.tanggal_transaksi as date
             FROM transaksi_simpanan ts
             LEFT JOIN simpanan s ON ts.simpanan_id = s.id
             WHERE s.anggota_id = ?
             ORDER BY ts.tanggal_transaksi DESC LIMIT 3",
            [$member_id], 'i'
        );

        // Recent loan activities
        $loans = fetchAll(
            "SELECT CONCAT('Loan ', status) as type, CONCAT('Loan application status: ', status) as description, tanggal_pengajuan as date
             FROM pinjaman
             WHERE anggota_id = ?
             ORDER BY tanggal_pengajuan DESC LIMIT 2",
            [$member_id], 'i'
        );

        $activities = array_merge($savings, $loans);
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 5);
    }

    private function validateRegistration($data) {
        $errors = [];

        if (empty($data['nama_lengkap'])) $errors[] = 'Nama lengkap wajib diisi';
        if (empty($data['nik']) || strlen($data['nik']) != 16) $errors[] = 'NIK harus 16 digit';
        if (empty($data['tanggal_lahir'])) $errors[] = 'Tanggal lahir wajib diisi';
        if (empty($data['jenis_kelamin'])) $errors[] = 'Jenis kelamin wajib dipilih';
        if (empty($data['alamat'])) $errors[] = 'Alamat wajib diisi';
        if (empty($data['no_hp'])) $errors[] = 'Nomor HP wajib diisi';
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email valid wajib diisi';

        // Check if NIK already exists
        $existing = fetchRow("SELECT id FROM anggota WHERE nik = ?", [$data['nik']], 's');
        if ($existing) $errors[] = 'NIK sudah terdaftar';

        return $errors;
    }

    private function saveMemberRegistration($data) {
        return executeNonQuery(
            "INSERT INTO anggota (no_anggota, nama_lengkap, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, no_hp, email, pekerjaan, pendapatan_bulanan, tanggal_gabung, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$data['no_anggota'], $data['nama_lengkap'], $data['nik'], $data['tempat_lahir'], $data['tanggal_lahir'], $data['jenis_kelamin'], $data['alamat'], $data['no_hp'], $data['email'], $data['pekerjaan'], $data['pendapatan_bulanan'], $data['tanggal_gabung'], $data['status']],
            'ssssssssssds'
        )['last_id'];
    }

    private function generateMemberNumber() {
        $year = date('Y');
        $lastMember = fetchRow("SELECT no_anggota FROM anggota WHERE no_anggota LIKE ? ORDER BY id DESC LIMIT 1", [$year . '%'], 's');

        if ($lastMember) {
            $lastNum = intval(substr($lastMember['no_anggota'], -4));
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        return $year . str_pad($newNum, 4, '0', STR_PAD_LEFT);
    }

    private function generateLoanNumber() {
        $year = date('Y');
        $lastLoan = fetchRow("SELECT no_pinjaman FROM pinjaman WHERE no_pinjaman LIKE ? ORDER BY id DESC LIMIT 1", ['LN' . $year . '%'], 's');

        if ($lastLoan) {
            $lastNum = intval(substr($lastLoan['no_pinjaman'], -4));
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        return 'LN' . $year . str_pad($newNum, 4, '0', STR_PAD_LEFT);
    }

    private function getCurrentSavingsBalance($member_id) {
        return (fetchRow(
            "SELECT COALESCE(SUM(s.saldo), 0) as total FROM simpanan s WHERE s.anggota_id = ? AND s.status = 'aktif'",
            [$member_id], 'i'
        ) ?? [])['total'] ?? 0;
    }

    private function sendRegistrationNotification($member_id, $data) {
        // Send email/SMS notification (stub)
        error_log("Member registration notification sent for: " . $data['nama_lengkap']);
    }

    private function sendLoanApplicationNotification($loan_id, $data) {
        // Send email/SMS notification (stub)
        error_log("Loan application notification sent for loan ID: " . $loan_id);
    }
}
?>
