<?php
require_once __DIR__ . '/BaseController.php';

class PinjamanController extends BaseController {
    public function index() {
        // // $this->ensureLoginAndRole([.*]); // DISABLED for development // DISABLED for development
        $pinjaman = fetchAll("SELECT p.id, p.no_pinjaman, p.jumlah_pinjaman, p.status, p.tanggal_pengajuan, a.nama_lengkap AS anggota FROM pinjaman p LEFT JOIN anggota a ON p.anggota_id=a.id ORDER BY p.created_at DESC");
        $this->render(__DIR__ . '/../views/pinjaman/index.php', ['pinjaman' => $pinjaman]);
    }

    public function create() {
        // // $this->ensureLoginAndRole([.*]); // DISABLED for development // DISABLED for development
        $anggota = fetchAll("SELECT id, nama_lengkap FROM anggota WHERE status='aktif' ORDER BY nama_lengkap");
        $jenis = fetchAll("SELECT id, nama_pinjaman FROM jenis_pinjaman WHERE is_active=1 ORDER BY nama_pinjaman");
        $this->render(__DIR__ . '/../views/pinjaman/create.php', ['anggota' => $anggota, 'jenis' => $jenis]);
    }

    public function store() {
        // // $this->ensureLoginAndRole([.*]); // DISABLED for development // DISABLED for development
        
        $data = $this->collectInput();
        $error = $this->validateInput($data);
        if ($error) {
            flashMessage('error', $error);
            redirect('pinjaman/create');
        }
        
        // Compliance validation (DISABLED for development)
        // try {
        //     validateTransactionCompliance($data['jenis_pinjaman_id'], $data['jumlah_pinjaman'], $data['anggota_id']);
        // } catch (Exception $e) {
        //     flashMessage('error', $e->getMessage());
        //     redirect('pinjaman/create');
        // }
        
        // Check duplicate
        $exists = fetchRow("SELECT id FROM pinjaman WHERE no_pinjaman = ? OR anggota_id = ?", [$data['no_pinjaman'], $data['anggota_id']], 'ss');
        if ($exists) {
            $error = 'No pinjaman atau anggota sudah terdaftar';
            flashMessage('error', $error);
            redirect('pinjaman/create');
        }
        
        runInTransaction(function($conn) use ($data) {
            $stmt = $conn->prepare("INSERT INTO pinjaman (anggota_id, jenis_pinjaman_id, no_pinjaman, jumlah_pinjaman, bunga_persen, tenor_bulan, angsuran_pokok, angsuran_bunga, total_angsuran, status, tanggal_pengajuan, tujuan_pinjaman, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('iissdddddssssssi',
                $data['anggota_id'],
                $data['jenis_pinjaman_id'],
                $data['no_pinjaman'],
                $data['jumlah_pinjaman'],
                $data['bunga_persen'],
                $data['tenor_bulan'],
                $data['angsuran_pokok'],
                $data['angsuran_bunga'],
                $data['total_angsuran'],
                $data['status'],
                $data['tanggal_pengajuan'],
                $data['tujuan_pinjaman'],
                $_SESSION['user']['id'] ?? null,
                $_SESSION['user']['id'] ?? null
            );
            $stmt->execute();
            $stmt->close();
            
            // Record koperasi transaction
            recordKoperasiTransaction(
                'PINJ_ANGGOTA',
                $data['anggota_id'],
                'debit',
                $data['jumlah_pinjaman'],
                "Pinjaman {$data['no_pinjaman']} untuk {$data['nama_lengkap']}",
                $data['no_pinjaman']
            );
            
            // Log compliance check
            logComplianceCheck('pinjaman_validation', [
                'amount' => $data['jumlah_pinjaman'],
                'member_id' => $data['anggota_id'],
                'jenis_pinjaman' => $data['jenis_pinjaman_id']
            ]);
        });

        flashMessage('success', 'Pengajuan pinjaman dibuat');
        redirect('pinjaman');
    }

    public function edit($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $row = fetchRow("SELECT * FROM pinjaman WHERE id = ?", [$id], 'i');
        if (!$row) {
            flashMessage('error', 'Data pinjaman tidak ditemukan');
            redirect('pinjaman');
        }
        $anggota = fetchAll("SELECT id, nama_lengkap FROM anggota WHERE status='aktif' ORDER BY nama_lengkap");
        $jenis = fetchAll("SELECT id, nama_pinjaman FROM jenis_pinjaman WHERE is_active=1 ORDER BY nama_pinjaman");
        $this->render(__DIR__ . '/../views/pinjaman/edit.php', ['pinjaman' => $row, 'anggota' => $anggota, 'jenis' => $jenis]);
    }

    public function update($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        
        $data = $this->collectInput();
        $error = $this->validateInput($data);
        if ($error) {
            flashMessage('error', $error);
            redirect('pinjaman/edit/' . $id);
        }
        runInTransaction(function($conn) use ($data, $id) {
            $stmt = $conn->prepare("UPDATE pinjaman SET anggota_id=?, jenis_pinjaman_id=?, no_pinjaman=?, jumlah_pinjaman=?, bunga_persen=?, tenor_bulan=?, angsuran_pokok=?, angsuran_bunga=?, total_angsuran=?, status=?, tanggal_pengajuan=?, tujuan_pinjaman=? WHERE id=?");
            $stmt->bind_param('iissdddddsssi',
                $data['anggota_id'],
                $data['jenis_pinjaman_id'],
                $data['no_pinjaman'],
                $data['jumlah_pinjaman'],
                $data['bunga_persen'],
                $data['tenor_bulan'],
                $data['angsuran_pokok'],
                $data['angsuran_bunga'],
                $data['total_angsuran'],
                $data['status'],
                $data['tanggal_pengajuan'],
                $data['tujuan_pinjaman'],
                $id
            );
            $stmt->execute();
            $stmt->close();
        });
        flashMessage('success', 'Pinjaman diperbarui');
        redirect('pinjaman');
    }

    public function delete($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        runInTransaction(function($conn) use ($id) {
            $stmt = $conn->prepare("UPDATE pinjaman SET status='ditolak' WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        });
        flashMessage('success', 'Pinjaman ditolak/dihapus');
        redirect('pinjaman');
    }

    public function approve($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        runInTransaction(function($conn) use ($id) {
            $stmt = $conn->prepare("UPDATE pinjaman SET status='disetujui', tanggal_disetujui=NOW(), approved_by=? WHERE id=?");
            $stmt->bind_param('ii', $_SESSION['user']['id'], $id);
            $stmt->execute();
            $stmt->close();
        });
        flashMessage('success', 'Pinjaman disetujui');
        redirect('pinjaman');
    }

    public function cairkan($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        runInTransaction(function($conn) use ($id) {
            $stmt = $conn->prepare("UPDATE pinjaman SET status='dicairkan', tanggal_pencairan=NOW() WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        });
        flashMessage('success', 'Pinjaman dicairkan');
        redirect('pinjaman');
    }

    public function angsuran() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $this->render(__DIR__ . '/../views/pinjaman/angsuran.php');
    }

    private function collectInput() {
        $jumlah = floatval($_POST['jumlah_pinjaman'] ?? 0);
        $bunga = floatval($_POST['bunga_persen'] ?? 0);
        $tenor = intval($_POST['tenor_bulan'] ?? 0);
        $angsuran_pokok = $tenor > 0 ? $jumlah / $tenor : 0;
        $angsuran_bunga = $tenor > 0 ? ($jumlah * ($bunga/100)) / $tenor : 0;
        $total_angsuran = $angsuran_pokok + $angsuran_bunga;
        return [
            'anggota_id' => intval($_POST['anggota_id'] ?? 0),
            'jenis_pinjaman_id' => intval($_POST['jenis_pinjaman_id'] ?? 0),
            'no_pinjaman' => sanitize($_POST['no_pinjaman'] ?? ''),
            'jumlah_pinjaman' => $jumlah,
            'bunga_persen' => $bunga,
            'tenor_bulan' => $tenor,
            'angsuran_pokok' => $angsuran_pokok,
            'angsuran_bunga' => $angsuran_bunga,
            'total_angsuran' => $total_angsuran,
            'status' => sanitize($_POST['status'] ?? 'pengajuan'),
            'tanggal_pengajuan' => sanitize($_POST['tanggal_pengajuan'] ?? date('Y-m-d')),
            'tujuan_pinjaman' => sanitize($_POST['tujuan_pinjaman'] ?? ''),
        ];
    }

    private function validateInput($data) {
        if (empty($data['anggota_id']) || empty($data['jenis_pinjaman_id']) || empty($data['no_pinjaman'])) {
            return 'Anggota, jenis pinjaman, dan no pinjaman wajib diisi';
        }
        if ($data['jumlah_pinjaman'] <= 0 || $data['tenor_bulan'] <= 0) {
            return 'Jumlah pinjaman dan tenor harus lebih dari 0';
        }
        return null;
    }
}
