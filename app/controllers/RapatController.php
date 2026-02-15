<?php
require_once __DIR__ . '/BaseController.php';

/**
 * RapatController handles CRUD operations for rapat (meetings).
 * Extends BaseController for common auth/role guards and rendering.
 */
class RapatController extends BaseController {
    /**
     * Display paginated list of rapat with stats.
     */
    public function index() {
        // $this->ensureLoginAndRole(['pengurus', 'pengawas']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM rapat") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $rapat = fetchAll("SELECT r.*, u.full_name as created_by_name FROM rapat r LEFT JOIN users u ON r.created_by = u.id ORDER BY r.tanggal DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('rapat/index', [
            'rapat' => $rapat,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Show create form.
     */
    public function create() {
        // $this->ensureLoginAndRole(['pengurus', 'pengawas']); // DISABLED for development
        $this->render('rapat/create');
    }

    /**
     * Store new rapat with validation and transaction.
     */
    public function store() {
        // $this->ensureLoginAndRole(['pengurus', 'pengawas']); // DISABLED for development

        $data = $this->collectInput();
        $error = $this->validateInput($data);
        if ($error) {
            flashMessage('error', $error);
            redirect('rapat/create');
        }

        $created_by = $_SESSION['user']['id'] ?? null;
        runInTransaction(function($conn) use ($data, $created_by) {
            $stmt = $conn->prepare("INSERT INTO rapat (judul, jenis_rapat, tanggal, waktu, lokasi, agenda, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                'sssssssi',
                $data['judul'],
                $data['jenis_rapat'],
                $data['tanggal'],
                $data['waktu'],
                $data['lokasi'],
                $data['agenda'],
                $data['status'],
                $created_by
            );
            $stmt->execute();
            $rapat_id = $stmt->insert_id;
            $stmt->close();

            // Add creator as participant
            if ($created_by) {
                $stmt2 = $conn->prepare("INSERT INTO rapat_peserta (rapat_id, user_id) VALUES (?, ?)");
                $stmt2->bind_param('ii', $rapat_id, $created_by);
                $stmt2->execute();
                $stmt2->close();
            }
        });

        flashMessage('success', 'Rapat berhasil ditambahkan');
        redirect('rapat');
    }

    /**
     * Show edit form with prefilled data.
     */
    public function edit($id) {
        // $this->ensureLoginAndRole(['pengurus', 'pengawas']); // DISABLED for development
        $row = fetchRow("SELECT * FROM rapat WHERE id = ?", [$id], 'i');
        if (!$row) {
            flashMessage('error', 'Data rapat tidak ditemukan');
            redirect('rapat');
        }
        $this->render('rapat/edit', ['rapat' => $row]);
    }

    /**
     * Update rapat with validation and transaction.
     */
    public function update($id) {
        // $this->ensureLoginAndRole(['pengurus', 'pengawas']); // DISABLED for development

        $data = $this->collectInput();
        $error = $this->validateInput($data);
        if ($error) {
            flashMessage('error', $error);
            redirect('rapat/edit/' . $id);
        }

        runInTransaction(function($conn) use ($data, $id) {
            $stmt = $conn->prepare("UPDATE rapat SET judul=?, jenis_rapat=?, tanggal=?, waktu=?, lokasi=?, agenda=?, status=? WHERE id=?");
            $stmt->bind_param(
                'sssssssi',
                $data['judul'],
                $data['jenis_rapat'],
                $data['tanggal'],
                $data['waktu'],
                $data['lokasi'],
                $data['agenda'],
                $data['status'],
                $id
            );
            $stmt->execute();
            $stmt->close();
        });

        flashMessage('success', 'Rapat berhasil diperbarui');
        redirect('rapat');
    }

    /**
     * Send meeting invitations/notifications.
     */
    public function sendInvitations($id) {
        // $this->ensureLoginAndRole(['pengurus', 'pengawas']); // DISABLED for development
        
        require_once __DIR__ . '/NotificationController.php';
        $notification = new NotificationController();
        
        if ($notification->sendMeetingNotification($id)) {
            flashMessage('success', 'Undangan rapat berhasil dikirim');
        } else {
            flashMessage('error', 'Gagal mengirim undangan rapat');
        }
        
        redirect('rapat/detail/' . $id);
    }

    /**
     * Soft delete rapat by setting status to 'dibatalkan'.
     */
    public function delete($id) {
        // $this->ensureLoginAndRole(['pengurus', 'pengawas']); // DISABLED for development
        runInTransaction(function($conn) use ($id) {
            $stmt = $conn->prepare("UPDATE rapat SET status = 'dibatalkan' WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        });

        flashMessage('success', 'Rapat berhasil dibatalkan');
        redirect('rapat');
    }

    /**
     * Show detail view with participants, minutes, and decisions.
     */
    public function detail($id) {
        // $this->ensureLoginAndRole(['pengurus', 'pengawas']); // DISABLED for development

        $rapat = fetchRow("SELECT r.*, u.full_name as created_by_name FROM rapat r LEFT JOIN users u ON r.created_by = u.id WHERE r.id = ?", [$id], 'i');
        if (!$rapat) {
            flashMessage('error', 'Data rapat tidak ditemukan');
            redirect('rapat');
        }

        $peserta = fetchAll("SELECT rp.*, u.full_name FROM rapat_peserta rp JOIN users u ON rp.user_id = u.id WHERE rp.rapat_id = ?", [$id], 'i');
        $notulen = fetchAll("SELECT rn.*, u.full_name as created_by_name FROM rapat_notulen rn LEFT JOIN users u ON rn.created_by = u.id WHERE rn.rapat_id = ? ORDER BY rn.created_at DESC", [$id], 'i');
        $keputusan = fetchAll("SELECT rk.*, u.full_name as pic_name FROM rapat_keputusan rk LEFT JOIN users u ON rk.pic = u.id WHERE rk.rapat_id = ?", [$id], 'i');

        $this->render('rapat/detail', [
            'rapat' => $rapat,
            'peserta' => $peserta,
            'notulen' => $notulen,
            'keputusan' => $keputusan
        ]);
    }

    /**
     * Collect and sanitize input data.
     */
    private function collectInput() {
        return [
            'judul' => sanitize($_POST['judul'] ?? ''),
            'jenis_rapat' => sanitize($_POST['jenis_rapat'] ?? ''),
            'tanggal' => sanitize($_POST['tanggal'] ?? ''),
            'waktu' => sanitize($_POST['waktu'] ?? ''),
            'lokasi' => sanitize($_POST['lokasi'] ?? ''),
            'agenda' => sanitize($_POST['agenda'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'terjadwal'),
        ];
    }

    /**
     * Validate input data.
     */
    private function validateInput($data) {
        if (empty($data['judul']) || empty($data['jenis_rapat']) || empty($data['tanggal'])) {
            return 'Judul rapat, jenis rapat, dan tanggal wajib diisi';
        }
        if (!in_array($data['jenis_rapat'], ['rapat_anggota', 'rapat_pengurus', 'rapat_pengawas'])) {
            return 'Jenis rapat tidak valid';
        }
        if (!in_array($data['status'], ['terjadwal', 'berlangsung', 'selesai', 'dibatalkan'])) {
            return 'Status rapat tidak valid';
        }
        if (!empty($data['tanggal']) && strtotime($data['tanggal']) < time() - 86400) {
            return 'Tanggal rapat tidak boleh di masa lalu';
        }
        return null;
    }
}
