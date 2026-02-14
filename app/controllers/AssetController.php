<?php
require_once __DIR__ . '/BaseController.php';

/**
 * AssetController handles fixed asset management and depreciation.
 * Manages company assets, depreciation calculations, and maintenance.
 */
class AssetController extends BaseController {
    /**
     * Display asset management dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $stats = $this->getAssetStats();
        $recent_assets = $this->getRecentAssets();

        $this->render(__DIR__ . '/../views/asset/index.php', [
            'stats' => $stats,
            'recent_assets' => $recent_assets
        ]);
    }

    /**
     * Display assets list.
     */
    public function assets() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = fetchRow("SELECT COUNT(*) as count FROM fixed_assets")['count'];
        $totalPages = ceil($total / $perPage);

        $assets = fetchAll("
            SELECT
                fa.id,
                fa.asset_code,
                fa.asset_name,
                COALESCE(c.category_name, 'Uncategorized') as category_name,
                fa.acquisition_cost,
                COALESCE(st.status_name, fa.condition_status) as condition_status_name,
                fa.location,
                fa.created_at
            FROM fixed_assets fa
            LEFT JOIN categories c ON fa.category_id = c.id
            LEFT JOIN status_types st ON st.category = 'asset' AND st.status_code = fa.condition_status
            ORDER BY fa.created_at DESC
            LIMIT ? OFFSET ?
        ", [$perPage, $offset], 'ii');

        $this->render(__DIR__ . '/../views/asset/assets.php', [
            'assets' => $assets,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Add new asset.
     */
    public function addAsset() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->collectAssetInput();
            $error = $this->validateAssetInput($data);
            if ($error) {
                flashMessage('error', $error);
                redirect('asset/addAsset');
            }

            runInTransaction(function($conn) use ($data) {
                $stmt = $conn->prepare("INSERT INTO fixed_assets (asset_code, asset_name, category, acquisition_date, acquisition_cost, useful_life_years, salvage_value, location, condition_status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $created_by = $_SESSION['user']['id'] ?? 1;
                $stmt->bind_param('ssssddissi', 
                    $data['asset_code'], $data['asset_name'], $data['category'], 
                    $data['acquisition_date'], $data['acquisition_cost'], $data['useful_life_years'], 
                    $data['salvage_value'], $data['location'], $data['condition_status'], $created_by);
                $stmt->execute();
                $asset_id = $stmt->insert_id;
                $stmt->close();

                // Calculate depreciation schedule
                $this->calculateDepreciationSchedule($conn, $asset_id, $data);
            });

            flashMessage('success', 'Asset berhasil ditambahkan');
            redirect('asset/assets');
        }

        $this->render(__DIR__ . '/../views/asset/add_asset.php');
    }

    /**
     * View asset details and depreciation schedule.
     */
    public function assetDetail($asset_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $asset = fetchRow("SELECT * FROM fixed_assets WHERE id = ?", [$asset_id], 'i');
        if (!$asset) {
            flashMessage('error', 'Asset tidak ditemukan');
            redirect('asset/assets');
        }

        $depreciation_schedule = fetchAll("SELECT * FROM asset_depreciation WHERE asset_id = ? ORDER BY depreciation_date", [$asset_id], 'i');
        $maintenance_history = fetchAll("SELECT * FROM asset_maintenance WHERE asset_id = ? ORDER BY maintenance_date DESC", [$asset_id], 'i');

        $this->render(__DIR__ . '/../views/asset/asset_detail.php', [
            'asset' => $asset,
            'depreciation_schedule' => $depreciation_schedule,
            'maintenance_history' => $maintenance_history
        ]);
    }

    /**
     * Record asset maintenance.
     */
    public function recordMaintenance($asset_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maintenance_date = sanitize($_POST['maintenance_date']);
            $maintenance_type = sanitize($_POST['maintenance_type']);
            $description = sanitize($_POST['description']);
            $cost = floatval($_POST['cost'] ?? 0);
            $performed_by = sanitize($_POST['performed_by']);

            runInTransaction(function($conn) use ($asset_id, $maintenance_date, $maintenance_type, $description, $cost, $performed_by) {
                $stmt = $conn->prepare("INSERT INTO asset_maintenance (asset_id, maintenance_date, maintenance_type, description, cost, performed_by, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $recorded_by = $_SESSION['user']['id'] ?? 1;
                $stmt->bind_param('isssdsi', $asset_id, $maintenance_date, $maintenance_type, $description, $cost, $performed_by, $recorded_by);
                $stmt->execute();
                $stmt->close();

                // Update asset condition if major maintenance
                if ($maintenance_type === 'major_repair') {
                    $stmt2 = $conn->prepare("UPDATE fixed_assets SET condition_status = 'good', last_maintenance_date = ? WHERE id = ?");
                    $stmt2->bind_param('si', $maintenance_date, $asset_id);
                    $stmt2->execute();
                    $stmt2->close();
                }
            });

            flashMessage('success', 'Maintenance berhasil dicatat');
            redirect('asset/assetDetail/' . $asset_id);
        }

        $asset = fetchRow("SELECT asset_name FROM fixed_assets WHERE id = ?", [$asset_id], 'i');

        $this->render(__DIR__ . '/../views/asset/record_maintenance.php', [
            'asset' => $asset,
            'asset_id' => $asset_id
        ]);
    }

    /**
     * Generate depreciation report.
     */
    public function depreciationReport() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $period = $_GET['period'] ?? date('Y-m');

        // Get depreciation for the period
        $depreciation_data = fetchAll("SELECT fa.asset_name, fa.asset_code, ad.depreciation_date, ad.depreciation_amount, ad.accumulated_depreciation, fa.acquisition_cost - ad.accumulated_depreciation as net_book_value FROM asset_depreciation ad LEFT JOIN fixed_assets fa ON ad.asset_id = fa.id WHERE DATE_FORMAT(ad.depreciation_date, '%Y-%m') = ? ORDER BY fa.asset_name, ad.depreciation_date", [$period], 's');

        // Calculate totals
        $total_depreciation = array_sum(array_column($depreciation_data, 'depreciation_amount'));

        $this->render(__DIR__ . '/../views/asset/depreciation_report.php', [
            'depreciation_data' => $depreciation_data,
            'total_depreciation' => $total_depreciation,
            'period' => $period
        ]);
    }

    /**
     * Calculate depreciation schedule for an asset.
     */
    private function calculateDepreciationSchedule($conn, $asset_id, $asset_data) {
        $acquisition_date = new DateTime($asset_data['acquisition_date']);
        $useful_life_years = $asset_data['useful_life_years'];
        $acquisition_cost = $asset_data['acquisition_cost'];
        $salvage_value = $asset_data['salvage_value'];

        // Straight-line depreciation
        $depreciable_amount = $acquisition_cost - $salvage_value;
        $annual_depreciation = $depreciable_amount / $useful_life_years;
        $monthly_depreciation = $annual_depreciation / 12;

        $accumulated_depreciation = 0;
        $current_date = clone $acquisition_date;

        // Calculate depreciation for each month until end of useful life
        for ($year = 0; $year < $useful_life_years; $year++) {
            for ($month = 0; $month < 12; $month++) {
                $depreciation_date = $current_date->format('Y-m-d');
                $accumulated_depreciation += $monthly_depreciation;

                // Don't exceed depreciable amount
                if ($accumulated_depreciation > $depreciable_amount) {
                    $monthly_depreciation = $depreciable_amount - ($accumulated_depreciation - $monthly_depreciation);
                    $accumulated_depreciation = $depreciable_amount;
                }

                $stmt = $conn->prepare("INSERT INTO asset_depreciation (asset_id, depreciation_date, depreciation_amount, accumulated_depreciation) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('isdd', $asset_id, $depreciation_date, $monthly_depreciation, $accumulated_depreciation);
                $stmt->execute();
                $stmt->close();

                $current_date->modify('+1 month');

                // Stop if fully depreciated
                if ($accumulated_depreciation >= $depreciable_amount) {
                    break 2;
                }
            }
        }
    }

    /**
     * Get asset statistics.
     */
    private function getAssetStats() {
        $stats = [];

        $stats['total_assets'] = fetchRow("SELECT COUNT(*) as total FROM fixed_assets")['total'];
        $stats['total_asset_value'] = fetchRow("SELECT COALESCE(SUM(acquisition_cost), 0) as total FROM fixed_assets")['total'];
        $stats['total_depreciation'] = fetchRow("SELECT COALESCE(SUM(accumulated_depreciation), 0) as total FROM asset_depreciation ad LEFT JOIN fixed_assets fa ON ad.asset_id = fa.id WHERE fa.condition_status != 'disposed'")['total'];
        $stats['net_book_value'] = $stats['total_asset_value'] - $stats['total_depreciation'];
        $stats['assets_needing_maintenance'] = fetchRow("SELECT COUNT(*) as total FROM fixed_assets WHERE condition_status IN ('poor', 'critical') OR last_maintenance_date < DATE_SUB(CURDATE(), INTERVAL 6 MONTH)")['total'];

        return $stats;
    }

    /**
     * Get recent assets.
     */
    private function getRecentAssets() {
        return fetchAll("SELECT fa.*, u.full_name as created_by_name FROM fixed_assets fa LEFT JOIN users u ON fa.created_by = u.id ORDER BY fa.created_at DESC LIMIT 5");
    }

    /**
     * Collect asset input data.
     */
    private function collectAssetInput() {
        return [
            'asset_code' => sanitize($_POST['asset_code']),
            'asset_name' => sanitize($_POST['asset_name']),
            'category' => sanitize($_POST['category']),
            'acquisition_date' => sanitize($_POST['acquisition_date']),
            'acquisition_cost' => floatval($_POST['acquisition_cost']),
            'useful_life_years' => intval($_POST['useful_life_years']),
            'salvage_value' => floatval($_POST['salvage_value'] ?? 0),
            'location' => sanitize($_POST['location']),
            'condition_status' => sanitize($_POST['condition_status'] ?? 'excellent')
        ];
    }

    /**
     * Validate asset input.
     */
    private function validateAssetInput($data) {
        if (empty($data['asset_code'])) return 'Kode asset wajib diisi';
        if (empty($data['asset_name'])) return 'Nama asset wajib diisi';
        if ($data['acquisition_cost'] <= 0) return 'Harga perolehan harus lebih dari 0';
        if ($data['useful_life_years'] <= 0) return 'Masa manfaat harus lebih dari 0';
        if ($data['salvage_value'] < 0) return 'Nilai sisa tidak boleh negatif';
        if ($data['salvage_value'] >= $data['acquisition_cost']) return 'Nilai sisa harus kurang dari harga perolehan';

        // Check if asset_code already exists
        $existing = fetchRow("SELECT id FROM fixed_assets WHERE asset_code = ?", [$data['asset_code']], 's');
        if ($existing) return 'Kode asset sudah ada';

        return null;
    }
}
