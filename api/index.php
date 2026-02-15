<?php
/**
 * REST API Controller for KSP Samosir
 * OpenAPI/Swagger compliant REST API endpoints
 * Based on fintech trends 2024 - API-first architecture
 */

require_once __DIR__ . '/../app/includes/MobileOptimizer.php';

class APIController {

    private $method;
    private $endpoint;
    private $params;
    private $headers;
    private $body;

    private function getBasePath() {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir === '/' || $scriptDir === '.') {
            return '';
        }
        return rtrim($scriptDir, '/');
    }

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->endpoint = $this->getEndpoint();
        $this->params = $this->getParams();
        $this->headers = $this->getHeaders();
        $this->body = $this->getBody();

        // Set JSON response headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
    }

    public function handleRequest() {
        // Handle preflight OPTIONS request
        if ($this->method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        try {
            // Authenticate API request
            if (!$this->authenticate()) {
                $this->sendResponse(401, ['error' => 'Unauthorized', 'message' => 'Invalid API credentials']);
                return;
            }

            // Route to appropriate handler
            $response = $this->routeRequest();

            if ($response !== null) {
                $this->sendResponse(200, $response);
            } else {
                $this->sendResponse(404, ['error' => 'Not Found', 'message' => 'Endpoint not found']);
            }

        } catch (Exception $e) {
            error_log("API Error: " . $e->getMessage());
            $this->sendResponse(500, ['error' => 'Internal Server Error', 'message' => 'An error occurred']);
        }
    }

    private function authenticate() {
        // Check if user is logged in via session (for web app)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user'])) {
            return true;
        }

        // DISABLED for development - allow API access without authentication
        return true;

        // Check for API key in header (for external API access)
        $apiKey = $this->headers['X-API-Key'] ?? $this->headers['Authorization'] ?? null;

        if (!$apiKey) {
            return false;
        }

        // Remove Bearer prefix if present
        $apiKey = str_replace('Bearer ', '', $apiKey);

        // Validate API key (simplified - in production, check against database)
        $validKeys = [
            'ksp_api_key_2024_production',
            'ksp_mobile_app_key',
            'ksp_web_app_key',
            'ksp_partner_key'
        ];

        return in_array($apiKey, $validKeys);
    }

    private function routeRequest() {
        switch ($this->endpoint) {
            // Members endpoints
            case 'members':
                return $this->handleMembers();
            case (preg_match('/^members\/(\d+)$/', $this->endpoint, $matches) ? true : false):
                $memberId = $matches[1];
                return $this->handleMember($memberId);

            // Loans endpoints
            case 'loans':
                return $this->handleLoans();
            case (preg_match('/^loans\/(\d+)$/', $this->endpoint, $matches) ? true : false):
                $loanId = $matches[1];
                return $this->handleLoan($loanId);

            // Savings endpoints
            case 'savings':
                return $this->handleSavings();
            case (preg_match('/^savings\/(\d+)$/', $this->endpoint, $matches) ? true : false):
                $savingId = $matches[1];
                return $this->handleSaving($savingId);

            // Transactions endpoints
            case 'transactions':
                return $this->handleTransactions();

            // Analytics endpoints
            case 'analytics/kpis':
                return $this->getKPIs();
            case 'analytics/trends':
                return $this->getTrends();

            // AI Credit Scoring endpoints
            case 'ai/credit-score':
                return $this->calculateCreditScore();

            // Inventory endpoints
            case 'inventory/items':
                return $this->handleInventory();
            case 'inventory/transactions':
                return $this->handleInventoryTransactions();

            default:
                return null;
        }
    }

    // Members API handlers
    private function handleMembers() {
        if ($this->method === 'GET') {
            $page = intval($this->params['page'] ?? 1);
            $limit = intval($this->params['limit'] ?? 20);
            $search = $this->params['search'] ?? null;

            // Get device info for optimization
            $device = MobileOptimizer::detectDevice();
            $optimizedLimit = MobileOptimizer::getOptimizedPagination($device);
            
            // Use device-optimized pagination if not explicitly set
            if (!isset($this->params['limit'])) {
                $limit = $optimizedLimit;
            }

            $whereClause = "WHERE status = 'aktif'";
            $params = [];

            if ($search) {
                $whereClause .= " AND (nama_lengkap LIKE ? OR no_anggota LIKE ? OR nik LIKE ?)";
                $params = ["%$search%", "%$search%", "%$search%"];
            }

            $offset = ($page - 1) * $limit;
            
            // Get optimized fields for device
            $fields = MobileOptimizer::getOptimizedFields('anggota', $device);
            $fieldList = implode(', ', $fields);
            
            $types = str_repeat('s', count($params)) . 'ii'; // search params are strings, limit/offset are integers
            $members = fetchAll(
                "SELECT {$fieldList}
                 FROM anggota $whereClause
                 ORDER BY tanggal_gabung DESC
                 LIMIT ? OFFSET ?",
                array_merge($params, [$limit, $offset]),
                $types
            );

            // Get total count
            $totalCount = (fetchRow(
                "SELECT COUNT(*) as count FROM anggota $whereClause",
                $params,
                str_repeat('s', count($params))
            ) ?? [])['count'] ?? 0;

            // Prepare response data
            $responseData = [
                'items' => $members,
                'total' => $totalCount,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalCount / $limit)
            ];

            // Compress data for mobile if needed
            if ($device['is_mobile']) {
                $responseData['items'] = MobileOptimizer::compressResponseData($members, $device);
            }

            return MobileOptimizer::formatApiResponse($responseData, $device);
        }

        if ($this->method === 'POST') {
            $data = $this->body;

            // Validate required fields
            $required = ['nama_lengkap', 'nik', 'tanggal_lahir', 'jenis_kelamin', 'alamat'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            // Generate member number
            $year = date('Y');
            $lastMember = fetchRow("SELECT no_anggota FROM anggota WHERE no_anggota LIKE ? ORDER BY id DESC LIMIT 1", [$year . '%'], 's');
            $newNum = $lastMember ? intval(substr($lastMember['no_anggota'], -4)) + 1 : 1;
            $noAnggota = $year . str_pad($newNum, 4, '0', STR_PAD_LEFT);

            $memberId = executeNonQuery(
                "INSERT INTO anggota (no_anggota, nama_lengkap, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, no_hp, email, pekerjaan, pendapatan_bulanan, tanggal_gabung, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $noAnggota,
                    $data['nama_lengkap'],
                    $data['nik'],
                    $data['tempat_lahir'] ?? '',
                    $data['tanggal_lahir'],
                    $data['jenis_kelamin'],
                    $data['alamat'],
                    $data['no_hp'] ?? '',
                    $data['email'] ?? '',
                    $data['pekerjaan'] ?? '',
                    $data['pendapatan_bulanan'] ?? 0,
                    date('Y-m-d'),
                    'aktif'
                ],
                'sssssssssisss'
            )['last_id'];

            return [
                'success' => true,
                'message' => 'Member created successfully',
                'data' => ['id' => $memberId, 'no_anggota' => $noAnggota]
            ];
        }

        $this->sendResponse(405, ['error' => 'Method Not Allowed']);
    }

    private function handleMember($memberId) {
        if ($this->method === 'GET') {
            $member = fetchRow(
                "SELECT id, no_anggota, nama_lengkap, nik, tanggal_lahir, jenis_kelamin,
                        alamat, no_hp, email, pekerjaan, pendapatan_bulanan, tanggal_gabung, status
                 FROM anggota WHERE id = ?",
                [$memberId],
                'i'
            );

            if (!$member) {
                $this->sendResponse(404, ['error' => 'Member not found']);
                return;
            }

            return ['data' => $member];
        }

        if ($this->method === 'PUT') {
            $data = $this->body;

            $updateFields = [];
            $params = [];
            $types = '';

            $allowedFields = ['nama_lengkap', 'alamat', 'no_hp', 'email', 'pekerjaan', 'pendapatan_bulanan'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                    $types .= 's';
                }
            }

            if (empty($updateFields)) {
                throw new Exception("No valid fields to update");
            }

            $params[] = $memberId;
            $types .= 'i';

            executeNonQuery(
                "UPDATE anggota SET " . implode(', ', $updateFields) . " WHERE id = ?",
                $params,
                $types
            );

            return ['success' => true, 'message' => 'Member updated successfully'];
        }

        $this->sendResponse(405, ['error' => 'Method Not Allowed']);
    }

    // Loans API handlers
    private function handleLoans() {
        if ($this->method === 'GET') {
            $status = $this->params['status'] ?? null;
            $memberId = $this->params['member_id'] ?? null;

            $whereClause = "";
            $params = [];

            if ($status) {
                $whereClause .= " AND status = ?";
                $params[] = $status;
            }

            if ($memberId) {
                $whereClause .= " AND anggota_id = ?";
                $params[] = $memberId;
            }

            $loans = fetchAll(
                "SELECT p.*, a.nama_lengkap as member_name, jp.nama_pinjaman as loan_type_name
                 FROM pinjaman p
                 LEFT JOIN anggota a ON p.anggota_id = a.id
                 LEFT JOIN jenis_pinjaman jp ON p.jenis_pinjaman_id = jp.id
                 WHERE 1=1 $whereClause
                 ORDER BY p.tanggal_pengajuan DESC",
                $params
            );

            return ['data' => $loans];
        }

        if ($this->method === 'POST') {
            $data = $this->body;

            // Validate required fields
            $required = ['anggota_id', 'jenis_pinjaman_id', 'jumlah_pinjaman', 'tenor_bulan'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            // Generate loan number
            $loanNumber = 'LN' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $loanId = executeNonQuery(
                "INSERT INTO pinjaman (anggota_id, jenis_pinjaman_id, no_pinjaman, jumlah_pinjaman, tenor_bulan, tanggal_pengajuan, status) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['anggota_id'],
                    $data['jenis_pinjaman_id'],
                    $loanNumber,
                    $data['jumlah_pinjaman'],
                    $data['tenor_bulan'],
                    date('Y-m-d'),
                    'pending'
                ],
                'iisiiis'
            )['last_id'];

            return [
                'success' => true,
                'message' => 'Loan application created successfully',
                'data' => ['id' => $loanId, 'no_pinjaman' => $loanNumber]
            ];
        }

        $this->sendResponse(405, ['error' => 'Method Not Allowed']);
    }

    // Savings API handlers
    private function handleSavings() {
        if ($this->method === 'GET') {
            $memberId = $this->params['member_id'] ?? null;

            $whereClause = "";
            $params = [];

            if ($memberId) {
                $whereClause = "WHERE s.anggota_id = ?";
                $params[] = $memberId;
            }

            $savings = fetchAll(
                "SELECT s.*, a.nama_lengkap as member_name, js.nama_simpanan as saving_type_name
                 FROM simpanan s
                 LEFT JOIN anggota a ON s.anggota_id = a.id
                 LEFT JOIN jenis_simpanan js ON s.jenis_simpanan_id = js.id
                 $whereClause
                 ORDER BY s.created_at DESC",
                $params
            );

            return ['data' => $savings];
        }

        if ($this->method === 'POST') {
            $data = $this->body;

            $required = ['anggota_id', 'jenis_simpanan_id', 'saldo'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            $savingId = executeNonQuery(
                "INSERT INTO simpanan (anggota_id, jenis_simpanan_id, no_rekening, saldo, status) VALUES (?, ?, ?, ?, ?)",
                [
                    $data['anggota_id'],
                    $data['jenis_simpanan_id'],
                    'SV' . date('Y') . rand(1000, 9999),
                    $data['saldo'],
                    'aktif'
                ],
                'iisds'
            )['last_id'];

            return [
                'success' => true,
                'message' => 'Saving account created successfully',
                'data' => ['id' => $savingId]
            ];
        }

        $this->sendResponse(405, ['error' => 'Method Not Allowed']);
    }

    // Analytics API handlers
    private function getKPIs() {
        return [
            'total_members' => (fetchRow("SELECT COUNT(*) as count FROM anggota WHERE status = 'aktif'") ?? [])['count'] ?? 0,
            'total_savings' => (fetchRow("SELECT COALESCE(SUM(saldo), 0) as total FROM simpanan WHERE status = 'aktif'") ?? [])['total'] ?? 0,
            'total_loans' => (fetchRow("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total FROM pinjaman WHERE status IN ('disetujui', 'dicairkan')") ?? [])['total'] ?? 0,
            'active_loans_count' => (fetchRow("SELECT COUNT(*) as count FROM pinjaman WHERE status IN ('disetujui', 'dicairkan')") ?? [])['count'] ?? 0
        ];
    }

    private function getTrends() {
        return [
            'monthly_members' => $this->getMonthlyMemberTrend(),
            'monthly_loans' => $this->getMonthlyLoanTrend(),
            'monthly_savings' => $this->getMonthlySavingsTrend()
        ];
    }

    private function getMonthlyMemberTrend() {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $count = (fetchRow("SELECT COUNT(*) as count FROM anggota WHERE DATE_FORMAT(tanggal_gabung, '%Y-%m') = ?", [$month], 's') ?? [])['count'] ?? 0;
            $data[] = ['month' => date('M Y', strtotime("-$i months")), 'count' => $count];
        }
        return $data;
    }

    private function getMonthlyLoanTrend() {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $total = (fetchRow("SELECT COALESCE(SUM(jumlah_pinjaman), 0) as total FROM pinjaman WHERE DATE_FORMAT(tanggal_pengajuan, '%Y-%m') = ?", [$month], 's') ?? [])['total'] ?? 0;
            $data[] = ['month' => date('M Y', strtotime("-$i months")), 'total' => $total];
        }
        return $data;
    }

    private function getMonthlySavingsTrend() {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $total = (fetchRow("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi_simpanan WHERE jenis_transaksi = 'setoran' AND DATE_FORMAT(tanggal_transaksi, '%Y-%m') = ?", [$month], 's') ?? [])['total'] ?? 0;
            $data[] = ['month' => date('M Y', strtotime("-$i months")), 'total' => $total];
        }
        return $data;
    }

    // AI Credit Scoring API
    private function calculateCreditScore() {
        if ($this->method !== 'POST') {
            $this->sendResponse(405, ['error' => 'Method Not Allowed']);
            return;
        }

        $data = $this->body;

        if (!isset($data['loan_id'])) {
            throw new Exception("loan_id is required");
        }

        // Import AICreditController for scoring logic
        require_once __DIR__ . '/AICreditController.php';
        $aiController = new AICreditController();

        // Get loan data
        $loanData = fetchRow(
            "SELECT p.*, a.nama_lengkap, a.nik, a.tanggal_lahir, a.pekerjaan, a.pendapatan_bulanan,
                    a.alamat, TIMESTAMPDIFF(YEAR, a.tanggal_lahir, CURDATE()) as usia
             FROM pinjaman p
             JOIN anggota a ON p.anggota_id = a.id
             WHERE p.id = ?",
            [$data['loan_id']],
            'i'
        );

        if (!$loanData) {
            throw new Exception("Loan not found");
        }

        // Calculate credit score using AI logic
        $reflection = new ReflectionClass($aiController);
        $method = $reflection->getMethod('calculateCreditScore');
        $method->setAccessible(true);
        $creditScore = $method->invokeArgs($aiController, [$loanData]);

        return [
            'loan_id' => $data['loan_id'],
            'credit_score' => $creditScore,
            'scored_at' => date('c')
        ];
    }

    // Inventory API handlers
    private function handleInventory() {
        if ($this->method === 'GET') {
            $items = fetchAll(
                "SELECT i.*, c.nama_kategori, w.nama_gudang,
                        (SELECT COALESCE(SUM(CASE WHEN tipe_transaksi = 'in' THEN jumlah ELSE -jumlah END), 0)
                         FROM inventory_transactions WHERE item_id = i.id) as stok_tersedia
                 FROM inventory_items i
                 LEFT JOIN kategori_produk c ON i.kategori_id = c.id
                 LEFT JOIN warehouses w ON i.gudang_id = w.id
                 WHERE i.is_active = 1
                 ORDER BY i.nama_item"
            );

            return ['data' => $items];
        }

        $this->sendResponse(405, ['error' => 'Method Not Allowed']);
    }

    private function handleInventoryTransactions() {
        if ($this->method === 'GET') {
            $transactions = fetchAll(
                "SELECT it.*, ii.nama_item, ii.kode_item, w.nama_gudang
                 FROM inventory_transactions it
                 LEFT JOIN inventory_items ii ON it.item_id = ii.id
                 LEFT JOIN warehouses w ON it.gudang_id = w.id
                 ORDER BY it.created_at DESC
                 LIMIT 100"
            );

            return ['data' => $transactions];
        }

        if ($this->method === 'POST') {
            $data = $this->body;

            $required = ['item_id', 'tipe_transaksi', 'jumlah'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            $transactionId = executeNonQuery(
                "INSERT INTO inventory_transactions (item_id, gudang_id, tipe_transaksi, jumlah, harga_satuan, keterangan, tanggal_transaksi)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['item_id'],
                    $data['gudang_id'] ?? 1,
                    $data['tipe_transaksi'],
                    $data['jumlah'],
                    $data['harga_satuan'] ?? 0,
                    $data['keterangan'] ?? '',
                    $data['tanggal_transaksi'] ?? date('Y-m-d')
                ],
                'iisidss'
            )['last_id'];

            return [
                'success' => true,
                'message' => 'Inventory transaction recorded successfully',
                'data' => ['id' => $transactionId]
            ];
        }

        $this->sendResponse(405, ['error' => 'Method Not Allowed']);
    }

    // Utility methods
    private function getEndpoint() {
        $requestUri = $_SERVER['REQUEST_URI'];
        // Remove query string
        $requestUri = explode('?', $requestUri)[0];
        // Remove dynamic base path (/[base]/api/)
        $apiPrefix = $this->getBasePath() . '/api/';
        $endpoint = strpos($requestUri, $apiPrefix) === 0
            ? substr($requestUri, strlen($apiPrefix))
            : ltrim($requestUri, '/');
        // Remove API version prefix (v1/)
        $endpoint = preg_replace('/^v\d+\//', '', $endpoint);
        // Remove leading/trailing slashes
        return trim($endpoint, '/');
    }

    private function getParams() {
        return array_merge($_GET, $_POST);
    }

    private function getHeaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerName = str_replace('HTTP_', '', $key);
                $headerName = str_replace('_', '-', $headerName);
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    private function getBody() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}

// Handle API requests
if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    $api = new APIController();
    $api->handleRequest();
}
?>
