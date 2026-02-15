<?php
require_once __DIR__ . '/BaseController.php';

/**
 * Accounting Controller
 * Handles general journal, ledger, trial balance, and financial statements
 */

class AccountingController extends BaseController {
    
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'accountant']); // DISABLED for development
        
        $this->render('accounting/index', [
            'menu' => $this->getAccountingMenu(),
            'stats' => $this->getAccountingStats()
        ]);
    }
    
    public function jurnal() {
        // $this->ensureLoginAndRole(['admin', 'accountant']); // DISABLED for development
        
        $page = intval($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get journal entries with pagination
        $total = (fetchRow("SELECT COUNT(*) as count FROM jurnal") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);
        
        $jurnal = fetchAll(
            "SELECT j.*, dj.nama_perkiraan, dj.debet, dj.kredit, dj.keterangan as detail_keterangan
             FROM jurnal j 
             LEFT JOIN detail_jurnal dj ON j.id = dj.jurnal_id 
             ORDER BY j.tanggal_jurnal DESC, j.nomor_jurnal DESC 
             LIMIT ? OFFSET ?",
            [$perPage, $offset],
            'ii'
        );
        
        $this->render('accounting/jurnal', [
            'jurnal' => $jurnal,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }
    
    public function bukuBesar() {
        // $this->ensureLoginAndRole(['admin', 'accountant']); // DISABLED for development
        
        $akun_id = $_GET['akun_id'] ?? null;
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');
        
        if ($akun_id) {
            $bukuBesar = fetchAll(
                "SELECT dj.*, j.tanggal_jurnal, j.nomor_jurnal, j.keterangan
                 FROM detail_jurnal dj
                 JOIN jurnal j ON dj.jurnal_id = j.id
                 WHERE dj.nama_perkiraan_id = ? 
                 AND j.tanggal_jurnal BETWEEN ? AND ?
                 ORDER BY j.tanggal_jurnal, j.nomor_jurnal",
                [$akun_id, $start_date, $end_date],
                'iss'
            );
            
            $akunInfo = fetchRow("SELECT * FROM coa_new WHERE id = ?", [$akun_id], 'i');
        } else {
            $bukuBesar = [];
            $akunInfo = null;
        }
        
        $daftarAkun = fetchAll("SELECT * FROM coa_new ORDER BY kode_perkiraan");
        
        $this->render('accounting/buku_besar', [
            'bukuBesar' => $bukuBesar,
            'akunInfo' => $akunInfo,
            'daftarAkun' => $daftarAkun,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
    }
    
    public function neracaSaldo() {
        // $this->ensureLoginAndRole(['admin', 'accountant']); // DISABLED for development
        
        $as_of_date = $_GET['as_of_date'] ?? date('Y-m-d');
        
        // Get trial balance
        $neracaSaldo = fetchAll(
            "SELECT 
                coa.id,
                coa.kode_perkiraan,
                coa.nama_perkiraan,
                coa.tipe_akun,
                COALESCE(SUM(CASE WHEN dj.debet > 0 THEN dj.debet ELSE 0 END), 0) as total_debet,
                COALESCE(SUM(CASE WHEN dj.kredit > 0 THEN dj.kredit ELSE 0 END), 0) as total_kredit
             FROM coa
             LEFT JOIN detail_jurnal dj ON coa.id = dj.nama_perkiraan_id
             LEFT JOIN jurnal j ON dj.jurnal_id = j.id AND j.tanggal_jurnal <= ?
             GROUP BY coa.id, coa.kode_perkiraan, coa.nama_perkiraan, coa.tipe_akun
             ORDER BY coa.kode_perkiraan",
            [$as_of_date],
            's'
        );
        
        // Calculate balances
        foreach ($neracaSaldo as &$row) {
            if ($row['tipe_akun'] === 'Aktiva') {
                $row['saldo'] = $row['total_debet'] - $row['total_kredit'];
            } else {
                $row['saldo'] = $row['total_kredit'] - $row['total_debet'];
            }
        }
        
        $this->render('accounting/neraca_saldo', [
            'neracaSaldo' => $neracaSaldo,
            'as_of_date' => $as_of_date
        ]);
    }
    
    public function neraca() {
        // $this->ensureLoginAndRole(['admin', 'accountant']); // DISABLED for development
        
        $as_of_date = $_GET['as_of_date'] ?? date('Y-m-d');
        
        // Get trial balance first
        $trialBalance = $this->getTrialBalance($as_of_date);
        
        // Group by account type for balance sheet
        $aktiva = [];
        $passiva = [];
        $ekuitas = [];
        
        foreach ($trialBalance as $account) {
            $data = [
                'kode' => $account['kode_perkiraan'],
                'nama' => $account['nama_perkiraan'],
                'saldo' => $account['saldo']
            ];
            
            if ($account['tipe_akun'] === 'Aktiva') {
                $aktiva[] = $data;
            } elseif ($account['tipe_akun'] === 'Kewajiban') {
                $passiva[] = $data;
            } else {
                $ekuitas[] = $data;
            }
        }
        
        $total_aktiva = array_sum(array_column($aktiva, 'saldo'));
        $total_passiva = array_sum(array_column($passiva, 'saldo'));
        $total_ekuitas = array_sum(array_column($ekuitas, 'saldo'));
        
        $this->render('accounting/neraca', [
            'aktiva' => $aktiva,
            'passiva' => $passiva,
            'ekuitas' => $ekuitas,
            'total_aktiva' => $total_aktiva,
            'total_passiva' => $total_passiva,
            'total_ekuitas' => $total_ekuitas,
            'as_of_date' => $as_of_date
        ]);
    }
    
    public function labaRugi() {
        // $this->ensureLoginAndRole(['admin', 'accountant']); // DISABLED for development
        
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');
        
        // Get revenue and expenses
        $pendapatan = fetchAll(
            "SELECT coa.kode_perkiraan, coa.nama_perkiraan, 
                    COALESCE(SUM(dj.debet), 0) as total
                 FROM coa
                 LEFT JOIN detail_jurnal dj ON coa.id = dj.nama_perkiraan_id
                 LEFT JOIN jurnal j ON dj.jurnal_id = j.id
                 WHERE coa.tipe_akun = 'Pendapatan'
                 AND j.tanggal_jurnal BETWEEN ? AND ?
                 GROUP BY coa.id, coa.kode_perkiraan, coa.nama_perkiraan
                 ORDER BY coa.kode_perkiraan",
            [$start_date, $end_date],
            'ss'
        );
        
        $beban = fetchAll(
            "SELECT coa.kode_perkiraan, coa.nama_perkiraan, 
                    COALESCE(SUM(dj.kredit), 0) as total
                 FROM coa
                 LEFT JOIN detail_jurnal dj ON coa.id = dj.nama_perkiraan_id
                 LEFT JOIN jurnal j ON dj.jurnal_id = j.id
                 WHERE coa.tipe_akun = 'Beban'
                 AND j.tanggal_jurnal BETWEEN ? AND ?
                 GROUP BY coa.id, coa.kode_perkiraan, coa.nama_perkiraan
                 ORDER BY coa.kode_perkiraan",
            [$start_date, $end_date],
            'ss'
        );
        
        $total_pendapatan = array_sum(array_column($pendapatan, 'total'));
        $total_beban = array_sum(array_column($beban, 'total'));
        $laba_bersih = $total_pendapatan - $total_beban;
        
        $this->render('accounting/laba_rugi', [
            'pendapatan' => $pendapatan,
            'beban' => $beban,
            'total_pendapatan' => $total_pendapatan,
            'total_beban' => $total_beban,
            'laba_bersih' => $laba_bersih,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
    }
    
    public function createJournal() {
        // $this->ensureLoginAndRole(['admin', 'accountant']); // DISABLED for development
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeJournal();
            return;
        }
        
        $daftarAkun = fetchAll("SELECT * FROM coa ORDER BY kode_perkiraan");
        
        $this->render('accounting/create_journal', [
            'daftarAkun' => $daftarAkun,
            'nomor_jurnal' => $this->generateJournalNumber()
        ]);
    }
    
    public function storeJournal() {
        // $this->ensureLoginAndRole(['admin', 'accountant']); // DISABLED for development
        
        $tanggal_jurnal = $_POST['tanggal_jurnal'] ?? date('Y-m-d');
        $nomor_jurnal = $_POST['nomor_jurnal'] ?? '';
        $keterangan = $_POST['keterangan'] ?? '';
        $details = $_POST['detail'] ?? [];
        
        // Validate journal balance
        $total_debet = 0;
        $total_kredit = 0;
        
        foreach ($details as $detail) {
            $total_debet += floatval($detail['debet'] ?? 0);
            $total_kredit += floatval($detail['kredit'] ?? 0);
        }
        
        if (abs($total_debet - $total_kredit) > 0.01) {
            flashMessage('error', 'Jurnal tidak balance (Debet ≠ Kredit)');
            redirect('accounting/createJournal');
            return;
        }
        
        runInTransaction(function($conn) use ($tanggal_jurnal, $nomor_jurnal, $keterangan, $details) {
            // Insert journal header
            $stmt = $conn->prepare("INSERT INTO jurnal (tanggal_jurnal, nomor_jurnal, keterangan, created_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('sssi', $tanggal_jurnal, $nomor_jurnal, $keterangan, $_SESSION['user']['id'] ?? null);
            $stmt->execute();
            $jurnal_id = $stmt->insert_id;
            $stmt->close();
            
            // Insert journal details
            foreach ($details as $detail) {
                $stmt = $conn->prepare("INSERT INTO detail_jurnal (jurnal_id, nama_perkiraan_id, debet, kredit, keterangan) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('iidds', $jurnal_id, $detail['akun_id'], $detail['debet'], $detail['kredit'], $detail['keterangan'] ?? '');
                $stmt->execute();
                $stmt->close();
            }
        });
        
        flashMessage('success', 'Jurnal berhasil disimpan');
        redirect('accounting/jurnal');
    }
    
    private function getTrialBalance($as_of_date) {
        return fetchAll(
            "SELECT 
                coa.id,
                coa.kode_perkiraan,
                coa.nama_perkiraan,
                coa.tipe_akun,
                COALESCE(SUM(CASE WHEN dj.debet > 0 THEN dj.debet ELSE 0 END), 0) as total_debet,
                COALESCE(SUM(CASE WHEN dj.kredit > 0 THEN dj.kredit ELSE 0 END), 0) as total_kredit,
                CASE 
                    WHEN coa.tipe_akun = 'Aktiva' 
                    THEN COALESCE(SUM(CASE WHEN dj.debet > 0 THEN dj.debet ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN dj.kredit > 0 THEN dj.kredit ELSE 0 END), 0)
                    ELSE COALESCE(SUM(CASE WHEN dj.kredit > 0 THEN dj.kredit ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN dj.debet > 0 THEN dj.debet ELSE 0 END), 0)
                END as saldo
             FROM coa
             LEFT JOIN detail_jurnal dj ON coa.id = dj.nama_perkiraan_id
             LEFT JOIN jurnal j ON dj.jurnal_id = j.id AND j.tanggal_jurnal <= ?
             GROUP BY coa.id, coa.kode_perkiraan, coa.nama_perkiraan, coa.tipe_akun
             HAVING saldo != 0
             ORDER BY coa.kode_perkiraan",
            [$as_of_date],
            's'
        ) ?? [];
    }
    
    private function generateJournalNumber() {
        $lastJournal = fetchRow("SELECT nomor_jurnal FROM jurnal ORDER BY id DESC LIMIT 1");
        if ($lastJournal) {
            $lastNum = intval(substr($lastJournal['nomor_jurnal'], -4));
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }
        return 'JRN' . date('Ym') . str_pad($newNum, 4, '0', STR_PAD_LEFT);
    }
    
    private function getAccountingMenu() {
        return [
            ['url' => 'accounting/jurnal', 'name' => 'Jurnal Umum', 'icon' => 'bi-journal-text'],
            ['url' => 'accounting/bukuBesar', 'name' => 'Buku Besar', 'icon' => 'bi-journal-bookmark'],
            ['url' => 'accounting/neracaSaldo', 'name' => 'Neraca Saldo', 'icon' => 'bi-journal-check'],
            ['url' => 'accounting/neraca', 'name' => 'Neraca', 'icon' => 'bi-journal-medical'],
            ['url' => 'accounting/labaRugi', 'name' => 'Laba Rugi', 'icon' => 'bi-journal-plus'],
            ['url' => 'accounting/createJournal', 'name' => 'Buat Jurnal', 'icon' => 'bi-journal-plus']
        ];
    }
    
    private function getAccountingStats() {
        return [
            'total_jurnal' => (fetchRow("SELECT COUNT(*) as total FROM jurnal") ?? [])['total'] ?? 0,
            'jurnal_bulan_ini' => (fetchRow("SELECT COUNT(*) as total FROM jurnal WHERE MONTH(tanggal_jurnal) = MONTH(CURRENT_DATE) AND YEAR(tanggal_jurnal) = YEAR(CURRENT_DATE)") ?? [])['total'] ?? 0,
            'last_journal' => (fetchRow("SELECT tanggal_jurnal FROM jurnal ORDER BY tanggal_jurnal DESC LIMIT 1") ?? [])['tanggal_jurnal'] ?? null,
            'total_akun' => (fetchRow("SELECT COUNT(*) as total FROM coa") ?? [])['total'] ?? 0
        ];
    }
}
?>
