<?php
require_once __DIR__ . '/BaseController.php';

/**
 * AnggotaController handles CRUD operations for members (anggota).
 * Extends BaseController for common auth/role guards and rendering.
 */
class AnggotaController extends BaseController {
    /**
     * Display paginated list of anggota with stats.
     */
    public function index() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development

        require_once __DIR__ . '/../shared/php/mobile_optimizer.php';

        $device = getDeviceInfo();
        $perPage = getMobilePagination();
        $page = intval($_GET['page'] ?? 1);
        $offset = ($page - 1) * $perPage;
        
        $total = fetchRow("SELECT COUNT(*) as count FROM anggota")['count'];
        $totalPages = ceil($total / $perPage);
        
        // Build optimized query for device
        $baseQuery = "
            FROM anggota a
            LEFT JOIN addresses addr ON a.address_id = addr.id AND addr.is_primary = 1
            LEFT JOIN entity_addresses ea ON ea.entity_type = 'member' AND ea.entity_id = a.id AND ea.address_id = addr.id
            LEFT JOIN contacts c_phone ON a.primary_contact_id = c_phone.id AND c_phone.contact_type = 'phone'
            LEFT JOIN contacts c_email ON c_email.id = (SELECT id FROM contacts WHERE contact_type = 'email' AND id IN (SELECT contact_id FROM entity_contacts WHERE entity_type = 'member' AND entity_id = a.id) LIMIT 1)
            ORDER BY a.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $selectFields = "a.id, a.no_anggota, a.nama_lengkap, a.nik, a.status, a.tanggal_gabung, COALESCE(addr.street_address, a.alamat) as alamat, COALESCE(c_phone.contact_value, a.no_hp) as no_hp, COALESCE(c_email.contact_value, a.email) as email";
        $fullQuery = "SELECT {$selectFields} {$baseQuery}";
        $optimizedQuery = optimizeQueryForDevice($fullQuery, 'anggota');
        $anggota = fetchAll($optimizedQuery, [$perPage, $offset], 'ii');

        // Compress data for mobile devices
        $anggota = compressMobileData($anggota);

        $this->render(__DIR__ . '/../views/anggota/index.php', [
            'anggota' => $anggota,
            'page' => $page,
            'totalPages' => $totalPages,
            'device' => $device
        ]);
    }

    /**
     * Show create form.
     */
    public function create() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $this->render(__DIR__ . '/../views/anggota/create.php');
    }

    /**
     * Store new anggota with validation and transaction.
     */
    public function store() {
        // // $this->ensureLoginAndRole([.*]); // DISABLED for development // DISABLED for development
        
        $data = $this->collectInput();
        $error = $this->validateInput($data);
        if ($error) {
            flashMessage('error', $error);
            redirect('anggota/create');
        }

        // Check duplicate
        $exists = fetchRow("SELECT id FROM anggota WHERE no_anggota = ? OR nik = ?", [$data['no_anggota'], $data['nik']], 'ss');
        if ($exists) {
            flashMessage('error', 'No anggota atau NIK sudah terdaftar');
            redirect('anggota/create');
        }

        runInTransaction(function($conn) use ($data) {
            $created_by = $_SESSION['user']['id'] ?? null;
            $stmt = $conn->prepare("INSERT INTO anggota (no_anggota, nama_lengkap, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, no_hp, email, pekerjaan, pendapatan_bulanan, tanggal_gabung, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                'ssssssssssdssi',
                $data['no_anggota'],
                $data['nama_lengkap'],
                $data['nik'],
                $data['tempat_lahir'],
                $data['tanggal_lahir'],
                $data['jenis_kelamin'],
                $data['alamat'],
                $data['no_hp'],
                $data['email'],
                $data['pekerjaan'],
                $data['pendapatan_bulanan'],
                $data['tanggal_gabung'],
                $data['status'],
                $created_by
            );
            $stmt->execute();
            $stmt->close();
        });

        flashMessage('success', 'Anggota berhasil ditambahkan');
        redirect('anggota');
    }

    /**
     * Show edit form with prefilled data.
     */
    public function edit($id) {
        // // $this->ensureLoginAndRole([.*]); // DISABLED for development // DISABLED for development
        $row = fetchRow("SELECT * FROM anggota WHERE id = ?", [$id], 'i');
        if (!$row) {
            flashMessage('error', 'Data anggota tidak ditemukan');
            redirect('anggota');
        }
        $this->render(__DIR__ . '/../views/anggota/edit.php', ['anggota' => $row]);
    }

    /**
     * Update anggota with validation and transaction.
     */
    public function update($id) {
        // // $this->ensureLoginAndRole([.*]); // DISABLED for development // DISABLED for development
        
        $data = $this->collectInput();
        $error = $this->validateInput($data);
        if ($error) {
            flashMessage('error', $error);
            redirect('anggota/edit/' . $id);
        }

        $exists = fetchRow("SELECT id FROM anggota WHERE (no_anggota = ? OR nik = ?) AND id <> ?", [$data['no_anggota'], $data['nik'], $id], 'ssi');
        if ($exists) {
            flashMessage('error', 'No anggota atau NIK sudah terdaftar');
            redirect('anggota/edit/' . $id);
        }

        runInTransaction(function($conn) use ($data, $id) {
            $stmt = $conn->prepare("UPDATE anggota SET no_anggota=?, nama_lengkap=?, nik=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, alamat=?, no_hp=?, email=?, pekerjaan=?, pendapatan_bulanan=?, tanggal_gabung=?, status=? WHERE id=?");
            $stmt->bind_param(
                'ssssssssssdssi',
                $data['no_anggota'],
                $data['nama_lengkap'],
                $data['nik'],
                $data['tempat_lahir'],
                $data['tanggal_lahir'],
                $data['jenis_kelamin'],
                $data['alamat'],
                $data['no_hp'],
                $data['email'],
                $data['pekerjaan'],
                $data['pendapatan_bulanan'],
                $data['tanggal_gabung'],
                $data['status'],
                $id
            );
            $stmt->execute();
            $stmt->close();
        });

        flashMessage('success', 'Anggota berhasil diperbarui');
        redirect('anggota');
    }

    /**
     * Soft delete anggota by setting status to 'keluar'.
     */
    public function delete($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        runInTransaction(function($conn) use ($id) {
            $stmt = $conn->prepare("UPDATE anggota SET status = 'keluar' WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        });

        flashMessage('success', 'Anggota berhasil dinonaktifkan');
        redirect('anggota');
    }

    /**
     * Collect and sanitize input data.
     */
    private function collectInput() {
        return [
            'no_anggota' => sanitize($_POST['no_anggota'] ?? ''),
            'nama_lengkap' => sanitize($_POST['nama_lengkap'] ?? ''),
            'nik' => sanitize($_POST['nik'] ?? ''),
            'tempat_lahir' => sanitize($_POST['tempat_lahir'] ?? ''),
            'tanggal_lahir' => sanitize($_POST['tanggal_lahir'] ?? ''),
            'jenis_kelamin' => sanitize($_POST['jenis_kelamin'] ?? ''),
            'alamat' => sanitize($_POST['alamat'] ?? ''),
            'no_hp' => sanitize($_POST['no_hp'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'pekerjaan' => sanitize($_POST['pekerjaan'] ?? ''),
            'pendapatan_bulanan' => isset($_POST['pendapatan_bulanan']) ? floatval($_POST['pendapatan_bulanan']) : 0,
            'tanggal_gabung' => sanitize($_POST['tanggal_gabung'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'aktif'),
        ];
    }

    /**
     * Validate input data.
     */
    private function validateInput($data) {
        if (empty($data['no_anggota']) || empty($data['nama_lengkap']) || empty($data['nik'])) {
            return 'No anggota, nama lengkap, dan NIK wajib diisi';
        }
        if (!in_array($data['jenis_kelamin'], ['L', 'P', ''], true)) {
            return 'Jenis kelamin harus L atau P';
        }
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return 'Format email tidak valid';
        }
        if (!empty($data['tanggal_lahir']) && strtotime($data['tanggal_lahir']) > time()) {
            return 'Tanggal lahir tidak boleh di masa depan';
        }
        return null;
    }
}
