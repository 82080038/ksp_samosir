<?php
require_once __DIR__ . '/BaseController.php';

class SimpananController extends BaseController {
    public function index() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        
        // Calculate statistics
        $stats = [
            'total_rekening' => (fetchRow("SELECT COUNT(*) as count FROM simpanan") ?? [])['count'] ?? 0,
            'total_saldo' => (fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM simpanan WHERE status = 'aktif'") ?? [])['total'] ?? 0,
            'rekening_aktif' => (fetchRow("SELECT COUNT(*) as count FROM simpanan WHERE status = 'aktif'") ?? [])['count'] ?? 0,
            'rekening_nonaktif' => (fetchRow("SELECT COUNT(*) as count FROM simpanan WHERE status != 'aktif'") ?? [])['count'] ?? 0,
            'setoran_bulan_ini' => (fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi_simpanan WHERE jenis_transaksi = 'setoran' AND MONTH(tanggal_transaksi) = MONTH(CURRENT_DATE) AND YEAR(tanggal_transaksi) = YEAR(CURRENT_DATE)") ?? [])['total'] ?? 0
        ];
        
        $simpanan = fetchAll("SELECT s.id, s.no_rekening, s.saldo, s.status, s.tanggal_buka, a.nama_lengkap AS anggota, js.nama_simpanan FROM simpanan s LEFT JOIN anggota a ON s.anggota_id=a.id LEFT JOIN jenis_simpanan js ON s.jenis_simpanan_id=js.id ORDER BY s.created_at DESC");
        $this->render('simpanan/index', ['simpanan' => $simpanan, 'stats' => $stats]);
    }

    public function create() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $anggota = fetchAll("SELECT id, nama_lengkap FROM anggota WHERE status='aktif' ORDER BY nama_lengkap");
        $jenis = fetchAll("SELECT id, nama_simpanan FROM jenis_simpanan WHERE is_active=1 ORDER BY nama_simpanan");
        $this->render('simpanan/create', ['anggota' => $anggota, 'jenis' => $jenis]);
    }

    public function store() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        
        $data = $this->collectInput();
        $error = $this->validateInput($data);
        if ($error) {
            flashMessage('error', $error);
            redirect('simpanan/create');
        }

        $exists = fetchRow("SELECT id FROM simpanan WHERE no_rekening = ?", [$data['no_rekening']], 's');
        if ($exists) {
            flashMessage('error', 'No rekening sudah terdaftar');
            redirect('simpanan/create');
        }

        runInTransaction(function($conn) use ($data) {
            $stmt = $conn->prepare("INSERT INTO simpanan (anggota_id, jenis_simpanan_id, no_rekening, saldo, status, tanggal_buka, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('iisdssi', $data['anggota_id'], $data['jenis_simpanan_id'], $data['no_rekening'], $data['saldo'], $data['status'], $data['tanggal_buka'], $_SESSION['user']['id'] ?? null);
            $stmt->execute();
            $stmt->close();
        });

        flashMessage('success', 'Rekening simpanan ditambahkan');
        redirect('simpanan');
    }

    public function edit($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $row = fetchRow("SELECT * FROM simpanan WHERE id = ?", [$id], 'i');
        if (!$row) {
            flashMessage('error', 'Data simpanan tidak ditemukan');
            redirect('simpanan');
        }
        $anggota = fetchAll("SELECT id, nama_lengkap FROM anggota WHERE status='aktif' ORDER BY nama_lengkap");
        $jenis = fetchAll("SELECT id, nama_simpanan FROM jenis_simpanan WHERE is_active=1 ORDER BY nama_simpanan");
        $this->render('simpanan/edit', ['simpanan' => $row, 'anggota' => $anggota, 'jenis' => $jenis]);
    }

    public function update($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        
        $data = $this->collectInput();
        $error = $this->validateInput($data);
        if ($error) {
            flashMessage('error', $error);
            redirect('simpanan/edit/' . $id);
        }
        $exists = fetchRow("SELECT id FROM simpanan WHERE no_rekening = ? AND id <> ?", [$data['no_rekening'], $id], 'si');
        if ($exists) {
            flashMessage('error', 'No rekening sudah terdaftar');
            redirect('simpanan/edit/' . $id);
        }
        runInTransaction(function($conn) use ($data, $id) {
            $stmt = $conn->prepare("UPDATE simpanan SET anggota_id=?, jenis_simpanan_id=?, no_rekening=?, saldo=?, status=?, tanggal_buka=? WHERE id=?");
            $stmt->bind_param('iisdssi', $data['anggota_id'], $data['jenis_simpanan_id'], $data['no_rekening'], $data['saldo'], $data['status'], $data['tanggal_buka'], $id);
            $stmt->execute();
            $stmt->close();
        });
        flashMessage('success', 'Rekening simpanan diperbarui');
        redirect('simpanan');
    }

    public function delete($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        runInTransaction(function($conn) use ($id) {
            $stmt = $conn->prepare("UPDATE simpanan SET status='ditutup', tanggal_tutup=NOW() WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        });
        flashMessage('success', 'Rekening simpanan ditutup');
        redirect('simpanan');
    }

    public function transaksi() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $this->render('simpanan/transaksi');
    }

    private function collectInput() {
        return [
            'anggota_id' => intval($_POST['anggota_id'] ?? 0),
            'jenis_simpanan_id' => intval($_POST['jenis_simpanan_id'] ?? 0),
            'no_rekening' => sanitize($_POST['no_rekening'] ?? ''),
            'saldo' => isset($_POST['saldo']) ? floatval($_POST['saldo']) : 0,
            'status' => sanitize($_POST['status'] ?? 'aktif'),
            'tanggal_buka' => sanitize($_POST['tanggal_buka'] ?? ''),
        ];
    }

    private function validateInput($data) {
        if (empty($data['anggota_id']) || empty($data['jenis_simpanan_id']) || empty($data['no_rekening'])) {
            return 'Anggota, jenis simpanan, dan no rekening wajib diisi';
        }
        return null;
    }
}
