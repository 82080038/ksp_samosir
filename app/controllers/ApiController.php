<?php

/**
 * RESTful API Controller for KSP Samosir
 * Handles all API endpoints with proper HTTP methods
 */
class ApiController {

    private $pdo;
    private $baseUrl = '/ksp_samosir/api/v1';

    public function __construct() {
        $this->pdo = getConnection();
    }

    /**
     * Handle API requests based on HTTP method and path
     */
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['REQUEST_URI'];

        // Remove base path
        $path = str_replace('/ksp_samosir/api/v1', '', $path);
        $path = str_replace('/ksp_samosir/api', '', $path);

        // Parse path segments
        $segments = array_filter(explode('/', trim($path, '/')));
        $resource = $segments[0] ?? null;
        $id = $segments[1] ?? null;
        $action = $segments[2] ?? null;

        // Authentication check
        if (!$this->authenticateRequest()) {
            $this->sendError('Unauthorized', 401);
            return;
        }

        // Route to appropriate handler
        switch ($resource) {
            case 'members':
                $this->handleMembers($method, $id, $action);
                break;
            case 'loans':
                $this->handleLoans($method, $id, $action);
                break;
            case 'savings':
                $this->handleSavings($method, $id, $action);
                break;
            case 'addresses':
                $this->handleAddresses($method, $id);
                break;
            default:
                $this->sendError('Resource not found', 404);
        }
    }

    /**
     * Authenticate API request
     */
    private function authenticateRequest() {
        // Check session for web requests
        if (isset($_SESSION['user'])) {
            return true;
        }

        // Check API key for external requests
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
        if ($apiKey) {
            // Validate API key against database
            $stmt = $this->pdo->prepare("SELECT id FROM api_keys WHERE key_value = ? AND active = 1");
            $stmt->execute([$apiKey]);
            return $stmt->fetch() !== false;
        }

        return false;
    }

    /**
     * Handle Members resource
     */
    private function handleMembers($method, $id = null, $action = null) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getMember($id);
                } else {
                    $this->getMembers();
                }
                break;
            case 'POST':
                $this->createMember();
                break;
            case 'PUT':
                if ($id) {
                    $this->updateMember($id);
                } else {
                    $this->sendError('Member ID required', 400);
                }
                break;
            case 'DELETE':
                if ($id) {
                    $this->deleteMember($id);
                } else {
                    $this->sendError('Member ID required', 400);
                }
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle Loans resource
     */
    private function handleLoans($method, $id = null, $action = null) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getLoan($id);
                } else {
                    $this->getLoans();
                }
                break;
            case 'POST':
                $this->createLoan();
                break;
            case 'PUT':
                if ($id) {
                    if ($action === 'approve') {
                        $this->approveLoan($id);
                    } elseif ($action === 'reject') {
                        $this->rejectLoan($id);
                    } else {
                        $this->updateLoan($id);
                    }
                } else {
                    $this->sendError('Loan ID required', 400);
                }
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle Savings resource
     */
    private function handleSavings($method, $id = null, $action = null) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getSaving($id);
                } else {
                    $this->getSavings();
                }
                break;
            case 'POST':
                $this->createSaving();
                break;
            case 'PUT':
                if ($id) {
                    $this->updateSaving($id);
                } else {
                    $this->sendError('Saving ID required', 400);
                }
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle Addresses resource
     */
    private function handleAddresses($method, $id = null) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getAddress($id);
                } else {
                    $this->getProvinces(); // Default to provinces
                }
                break;
            case 'POST':
                $this->searchAddresses();
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    // ==================== MEMBERS METHODS ====================

    private function getMembers() {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 15);
            $search = $_GET['search'] ?? null;
            $status = $_GET['status'] ?? 'aktif';

            $offset = ($page - 1) * $perPage;

            $whereClause = "WHERE status = ?";
            $params = [$status];

            if ($search) {
                $whereClause .= " AND (nama_lengkap LIKE ? OR no_anggota LIKE ? OR no_ktp LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM anggota $whereClause";
            $stmt = $this->pdo->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];

            // Get paginated data
            $sql = "SELECT id, no_anggota, nama_lengkap, no_ktp, tanggal_lahir,
                           alamat, no_telepon, email, status, tanggal_daftar
                    FROM anggota $whereClause
                    ORDER BY tanggal_daftar DESC
                    LIMIT ? OFFSET ?";

            $params[] = $perPage;
            $params[] = $offset;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->sendSuccess([
                'data' => $members,
                'meta' => [
                    'pagination' => [
                        'total' => (int)$total,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => ceil($total / $perPage),
                        'from' => $offset + 1,
                        'to' => min($offset + $perPage, $total)
                    ]
                ]
            ]);

        } catch (Exception $e) {
            $this->sendError('Failed to fetch members: ' . $e->getMessage(), 500);
        }
    }

    private function getMember($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, ea.address_id, addr.full_address
                FROM anggota a
                LEFT JOIN entity_addresses ea ON a.id = ea.entity_id AND ea.entity_type = 'anggota'
                LEFT JOIN addresses addr ON ea.address_id = addr.id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$member) {
                $this->sendError('Member not found', 404);
                return;
            }

            $this->sendSuccess(['data' => $member]);

        } catch (Exception $e) {
            $this->sendError('Failed to fetch member: ' . $e->getMessage(), 500);
        }
    }

    private function createMember() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                $this->sendError('Invalid JSON data', 400);
                return;
            }

            // Validate required fields
            $required = ['nama_lengkap', 'no_ktp', 'alamat'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $this->sendError("Field '$field' is required", 400);
                    return;
                }
            }

            // Generate member number
            $memberNumber = $this->generateMemberNumber();

            $stmt = $this->pdo->prepare("
                INSERT INTO anggota (no_anggota, nama_lengkap, no_ktp, tanggal_lahir,
                                   alamat, no_telepon, email, status, tanggal_daftar)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'aktif', NOW())
            ");

            $stmt->execute([
                $memberNumber,
                $data['nama_lengkap'],
                $data['no_ktp'],
                $data['tanggal_lahir'] ?? null,
                $data['alamat'],
                $data['no_telepon'] ?? null,
                $data['email'] ?? null
            ]);

            $memberId = $this->pdo->lastInsertId();

            $this->sendSuccess([
                'data' => ['id' => $memberId, 'no_anggota' => $memberNumber],
                'message' => 'Member created successfully'
            ], 201);

        } catch (Exception $e) {
            $this->sendError('Failed to create member: ' . $e->getMessage(), 500);
        }
    }

    private function updateMember($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                $this->sendError('Invalid JSON data', 400);
                return;
            }

            $updateFields = [];
            $params = [];

            $allowedFields = ['nama_lengkap', 'no_ktp', 'tanggal_lahir', 'alamat',
                            'no_telepon', 'email', 'status'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }

            if (empty($updateFields)) {
                $this->sendError('No fields to update', 400);
                return;
            }

            $params[] = $id;
            $sql = "UPDATE anggota SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                $this->sendError('Member not found or no changes made', 404);
                return;
            }

            $this->sendSuccess(['message' => 'Member updated successfully']);

        } catch (Exception $e) {
            $this->sendError('Failed to update member: ' . $e->getMessage(), 500);
        }
    }

    private function deleteMember($id) {
        try {
            $stmt = $this->pdo->prepare("UPDATE anggota SET status = 'nonaktif' WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                $this->sendError('Member not found', 404);
                return;
            }

            $this->sendSuccess(['message' => 'Member deactivated successfully']);

        } catch (Exception $e) {
            $this->sendError('Failed to delete member: ' . $e->getMessage(), 500);
        }
    }

    // ==================== LOANS METHODS ====================

    private function getLoans() {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 15);
            $status = $_GET['status'] ?? null;
            $memberId = $_GET['member_id'] ?? null;

            $offset = ($page - 1) * $perPage;
            $whereClause = "";
            $params = [];

            if ($status) {
                $whereClause .= " AND p.status = ?";
                $params[] = $status;
            }

            if ($memberId) {
                $whereClause .= " AND p.anggota_id = ?";
                $params[] = $memberId;
            }

            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM pinjaman p WHERE 1=1 $whereClause";
            $stmt = $this->pdo->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];

            // Get paginated data
            $sql = "
                SELECT p.*, a.nama_lengkap as member_name, a.no_anggota
                FROM pinjaman p
                JOIN anggota a ON p.anggota_id = a.id
                WHERE 1=1 $whereClause
                ORDER BY p.tanggal_pengajuan DESC
                LIMIT ? OFFSET ?
            ";

            $params[] = $perPage;
            $params[] = $offset;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->sendSuccess([
                'data' => $loans,
                'meta' => [
                    'pagination' => [
                        'total' => (int)$total,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => ceil($total / $perPage)
                    ]
                ]
            ]);

        } catch (Exception $e) {
            $this->sendError('Failed to fetch loans: ' . $e->getMessage(), 500);
        }
    }

    private function getLoan($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, a.nama_lengkap, a.no_anggota,
                       jp.nama_jenis, jp.bunga_per_tahun, jp.maksimal_pinjaman
                FROM pinjaman p
                JOIN anggota a ON p.anggota_id = a.id
                JOIN jenis_pinjaman jp ON p.jenis_pinjaman_id = jp.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $loan = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$loan) {
                $this->sendError('Loan not found', 404);
                return;
            }

            $this->sendSuccess(['data' => $loan]);

        } catch (Exception $e) {
            $this->sendError('Failed to fetch loan: ' . $e->getMessage(), 500);
        }
    }

    private function createLoan() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                $this->sendError('Invalid JSON data', 400);
                return;
            }

            $required = ['anggota_id', 'jenis_pinjaman_id', 'jumlah_pinjaman', 'jangka_waktu'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    $this->sendError("Field '$field' is required", 400);
                    return;
                }
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO pinjaman (anggota_id, jenis_pinjaman_id, jumlah_pinjaman,
                                   jangka_waktu, bunga_per_tahun, tanggal_pengajuan, status)
                VALUES (?, ?, ?, ?, ?, NOW(), 'pending')
            ");

            $stmt->execute([
                $data['anggota_id'],
                $data['jenis_pinjaman_id'],
                $data['jumlah_pinjaman'],
                $data['jangka_waktu'],
                $data['bunga_per_tahun'] ?? 12.0
            ]);

            $loanId = $this->pdo->lastInsertId();

            $this->sendSuccess([
                'data' => ['id' => $loanId],
                'message' => 'Loan application created successfully'
            ], 201);

        } catch (Exception $e) {
            $this->sendError('Failed to create loan: ' . $e->getMessage(), 500);
        }
    }

    private function approveLoan($id) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE pinjaman
                SET status = 'approved', tanggal_pencairan = NOW()
                WHERE id = ? AND status = 'pending'
            ");
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                $this->sendError('Loan not found or cannot be approved', 400);
                return;
            }

            $this->sendSuccess(['message' => 'Loan approved successfully']);

        } catch (Exception $e) {
            $this->sendError('Failed to approve loan: ' . $e->getMessage(), 500);
        }
    }

    private function rejectLoan($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $reason = $data['reason'] ?? 'No reason provided';

            $stmt = $this->pdo->prepare("
                UPDATE pinjaman
                SET status = 'rejected', catatan = ?
                WHERE id = ? AND status = 'pending'
            ");
            $stmt->execute([$reason, $id]);

            if ($stmt->rowCount() === 0) {
                $this->sendError('Loan not found or cannot be rejected', 400);
                return;
            }

            $this->sendSuccess(['message' => 'Loan rejected successfully']);

        } catch (Exception $e) {
            $this->sendError('Failed to reject loan: ' . $e->getMessage(), 500);
        }
    }

    // ==================== SAVINGS METHODS ====================

    private function getSavings() {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 15);
            $memberId = $_GET['member_id'] ?? null;
            $type = $_GET['type'] ?? null;

            $offset = ($page - 1) * $perPage;
            $whereClause = "";
            $params = [];

            if ($memberId) {
                $whereClause .= " AND s.anggota_id = ?";
                $params[] = $memberId;
            }

            if ($type) {
                $whereClause .= " AND js.nama_jenis = ?";
                $params[] = $type;
            }

            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM simpanan s
                        JOIN jenis_simpanan js ON s.jenis_simpanan_id = js.id
                        WHERE 1=1 $whereClause";
            $stmt = $this->pdo->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];

            // Get paginated data
            $sql = "
                SELECT s.*, a.nama_lengkap, a.no_anggota, js.nama_jenis
                FROM simpanan s
                JOIN anggota a ON s.anggota_id = a.id
                JOIN jenis_simpanan js ON s.jenis_simpanan_id = js.id
                WHERE 1=1 $whereClause
                ORDER BY s.tanggal_transaksi DESC
                LIMIT ? OFFSET ?
            ";

            $params[] = $perPage;
            $params[] = $offset;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $savings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->sendSuccess([
                'data' => $savings,
                'meta' => [
                    'pagination' => [
                        'total' => (int)$total,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => ceil($total / $perPage)
                    ]
                ]
            ]);

        } catch (Exception $e) {
            $this->sendError('Failed to fetch savings: ' . $e->getMessage(), 500);
        }
    }

    private function getSaving($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT s.*, a.nama_lengkap, a.no_anggota, js.nama_jenis, js.bunga_per_tahun
                FROM simpanan s
                JOIN anggota a ON s.anggota_id = a.id
                JOIN jenis_simpanan js ON s.jenis_simpanan_id = js.id
                WHERE s.id = ?
            ");
            $stmt->execute([$id]);
            $saving = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$saving) {
                $this->sendError('Saving record not found', 404);
                return;
            }

            $this->sendSuccess(['data' => $saving]);

        } catch (Exception $e) {
            $this->sendError('Failed to fetch saving: ' . $e->getMessage(), 500);
        }
    }

    private function createSaving() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                $this->sendError('Invalid JSON data', 400);
                return;
            }

            $required = ['anggota_id', 'jenis_simpanan_id', 'jumlah'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    $this->sendError("Field '$field' is required", 400);
                    return;
                }
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO simpanan (anggota_id, jenis_simpanan_id, jumlah,
                                    tanggal_transaksi, keterangan)
                VALUES (?, ?, ?, NOW(), ?)
            ");

            $stmt->execute([
                $data['anggota_id'],
                $data['jenis_simpanan_id'],
                $data['jumlah'],
                $data['keterangan'] ?? null
            ]);

            $savingId = $this->pdo->lastInsertId();

            $this->sendSuccess([
                'data' => ['id' => $savingId],
                'message' => 'Saving record created successfully'
            ], 201);

        } catch (Exception $e) {
            $this->sendError('Failed to create saving: ' . $e->getMessage(), 500);
        }
    }

    // ==================== ADDRESS METHODS ====================

    private function getProvinces() {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name FROM ref_provinces ORDER BY name");
            $stmt->execute();
            $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->sendSuccess(['data' => $provinces]);

        } catch (Exception $e) {
            $this->sendError('Failed to fetch provinces: ' . $e->getMessage(), 500);
        }
    }

    private function searchAddresses() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $keyword = $data['keyword'] ?? '';
            $type = $data['type'] ?? 'village';

            if (empty($keyword)) {
                $this->sendError('Keyword is required', 400);
                return;
            }

            $table = '';
            switch ($type) {
                case 'province':
                    $table = 'ref_provinces';
                    break;
                case 'regency':
                    $table = 'ref_regencies';
                    break;
                case 'district':
                    $table = 'ref_districts';
                    break;
                case 'village':
                default:
                    $table = 'ref_villages';
                    break;
            }

            $stmt = $this->pdo->prepare("
                SELECT id, name FROM $table
                WHERE name LIKE ?
                ORDER BY name
                LIMIT 20
            ");
            $stmt->execute(["%$keyword%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->sendSuccess(['data' => $results]);

        } catch (Exception $e) {
            $this->sendError('Failed to search addresses: ' . $e->getMessage(), 500);
        }
    }

    // ==================== HELPER METHODS ====================

    private function generateMemberNumber() {
        $year = date('Y');
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM anggota
            WHERE YEAR(tanggal_daftar) = ?
        ");
        $stmt->execute([$year]);
        $count = $stmt->fetch()['count'] + 1;

        return sprintf('KSP-%s-%04d', $year, $count);
    }

    private function sendSuccess($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
    }

    private function sendError($message, $statusCode = 400) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $statusCode
            ]
        ]);
    }
}
