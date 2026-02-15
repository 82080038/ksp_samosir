<?php
require_once __DIR__ . '/BaseController.php';

/**
 * Inventory Controller
 * Handles inventory management, stock tracking, warehouse management, and supplier management
 */

class InventoryController extends BaseController {
    
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'staff', 'inventory_manager']); // DISABLED for development
        
        $stats = $this->getInventoryStats();
        $lowStockItems = $this->getLowStockItems();
        $recentMovements = $this->getRecentMovements();
        
        $this->render('inventory/index', [
            'stats' => $stats,
            'lowStockItems' => $lowStockItems,
            'recentMovements' => $recentMovements
        ]);
    }
    
    public function items() {
        // $this->ensureLoginAndRole(['admin', 'staff', 'inventory_manager']); // DISABLED for development
        
        $page = intval($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $search = $_GET['search'] ?? '';
        
        $total = (fetchRow("SELECT COUNT(*) as count FROM inventory_items WHERE is_active = 1" . 
                         ($search ? " AND (nama_item LIKE ? OR kode_item LIKE ?)" : ""), 
                         $search ? ["%$search%", "%$search%"] : []) ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);
        
        $items = fetchAll(
            "SELECT i.*, c.nama_kategori, w.nama_gudang, 
                    (SELECT SUM(CASE WHEN tipe_transaksi = 'in' THEN jumlah ELSE -jumlah END) 
                     FROM inventory_transactions WHERE item_id = i.id) as stok_tersedia
             FROM inventory_items i
             LEFT JOIN kategori_produk c ON i.kategori_id = c.id
             LEFT JOIN warehouses w ON i.gudang_id = w.id
             WHERE i.is_active = 1" . 
             ($search ? " AND (i.nama_item LIKE ? OR i.kode_item LIKE ?)" : "") . 
             " ORDER BY i.created_at DESC LIMIT ? OFFSET ?",
            array_merge($search ? ["%$search%", "%$search%"] : [], [$perPage, $offset]),
            $search ? 'ssii' : 'ii'
        );
        
        $this->render('inventory/items', [
            'items' => $items,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'total' => $total
        ]);
    }
    
    public function warehouses() {
        // $this->ensureLoginAndRole(['admin', 'inventory_manager']); // DISABLED for development
        
        $warehouses = fetchAll(
            "SELECT w.*, 
                    (SELECT COUNT(*) FROM inventory_items WHERE gudang_id = w.id AND is_active = 1) as jumlah_item,
                    (SELECT SUM((SELECT SUM(CASE WHEN tipe_transaksi = 'in' THEN jumlah ELSE -jumlah END) 
                                 FROM inventory_transactions WHERE item_id = i.id)) 
                     FROM inventory_items i WHERE i.gudang_id = w.id AND i.is_active = 1) as total_stok
             FROM warehouses w
             WHERE w.is_active = 1
             ORDER BY w.nama_gudang"
        );
        
        $this->render('inventory/warehouses', [
            'warehouses' => $warehouses
        ]);
    }
    
    public function suppliers() {
        // $this->ensureLoginAndRole(['admin', 'inventory_manager']); // DISABLED for development
        
        $suppliers = fetchAll(
            "SELECT s.*, 
                    (SELECT COUNT(*) FROM purchase_orders WHERE supplier_id = s.id) as jumlah_po,
                    (SELECT SUM(total_harga) FROM purchase_orders WHERE supplier_id = s.id) as total_nilai_po,
                    s.created_at as tanggal_daftar
             FROM vendors s
             WHERE s.is_active = 1
             ORDER BY s.nama_vendor"
        );
        
        $this->render('inventory/suppliers', [
            'suppliers' => $suppliers
        ]);
    }
    
    public function stockMovements() {
        // $this->ensureLoginAndRole(['admin', 'staff', 'inventory_manager']); // DISABLED for development
        
        $page = intval($_GET['page'] ?? 1);
        $perPage = 25;
        $offset = ($page - 1) * $perPage;
        $item_id = $_GET['item_id'] ?? null;
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        
        $whereClause = "WHERE it.tanggal_transaksi BETWEEN ? AND ?";
        $params = [$start_date, $end_date];
        $types = 'ss';
        
        if ($item_id) {
            $whereClause .= " AND it.item_id = ?";
            $params[] = $item_id;
            $types .= 'i';
        }
        
        $total = (fetchRow("SELECT COUNT(*) as count FROM inventory_transactions it $whereClause", $params, $types) ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);
        
        $movements = fetchAll(
            "SELECT it.*, ii.nama_item, ii.kode_item, w.nama_gudang, u.username
             FROM inventory_transactions it
             LEFT JOIN inventory_items ii ON it.item_id = ii.id
             LEFT JOIN warehouses w ON it.gudang_id = w.id
             LEFT JOIN users u ON it.created_by = u.id
             $whereClause
             ORDER BY it.tanggal_transaksi DESC, it.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset]),
            $types . 'ii'
        );
        
        $items = fetchAll("SELECT id, nama_item, kode_item FROM inventory_items WHERE is_active = 1 ORDER BY nama_item");
        
        $this->render('inventory/stock_movements', [
            'movements' => $movements,
            'items' => $items,
            'page' => $page,
            'totalPages' => $totalPages,
            'item_id' => $item_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total' => $total
        ]);
    }
    
    public function addStockMovement() {
        // $this->ensureLoginAndRole(['admin', 'inventory_manager']); // DISABLED for development
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeStockMovement();
            return;
        }
        
        $items = fetchAll("SELECT id, nama_item, kode_item FROM inventory_items WHERE is_active = 1 ORDER BY nama_item");
        $warehouses = fetchAll("SELECT id, nama_gudang FROM warehouses WHERE is_active = 1 ORDER BY nama_gudang");
        
        $this->render('inventory/add_movement', [
            'items' => $items,
            'warehouses' => $warehouses
        ]);
    }
    
    public function storeStockMovement() {
        // $this->ensureLoginAndRole(['admin', 'inventory_manager']); // DISABLED for development
        
        $item_id = intval($_POST['item_id'] ?? 0);
        $gudang_id = intval($_POST['gudang_id'] ?? 0);
        $tipe_transaksi = $_POST['tipe_transaksi'] ?? '';
        $jumlah = intval($_POST['jumlah'] ?? 0);
        $harga_satuan = floatval($_POST['harga_satuan'] ?? 0);
        $keterangan = sanitize($_POST['keterangan'] ?? '');
        $tanggal_transaksi = $_POST['tanggal_transaksi'] ?? date('Y-m-d');
        
        // Validation
        if (!$item_id || !$gudang_id || !in_array($tipe_transaksi, ['in', 'out']) || $jumlah <= 0) {
            flashMessage('error', 'Data tidak valid');
            redirect('inventory/addStockMovement');
            return;
        }
        
        // Check stock availability for 'out' transactions
        if ($tipe_transaksi === 'out') {
            $currentStock = $this->getCurrentStock($item_id);
            if ($currentStock < $jumlah) {
                flashMessage('error', 'Stok tidak mencukupi. Stok tersedia: ' . $currentStock);
                redirect('inventory/addStockMovement');
                return;
            }
        }
        
        runInTransaction(function($conn) use ($item_id, $gudang_id, $tipe_transaksi, $jumlah, $harga_satuan, $keterangan, $tanggal_transaksi) {
            // Insert stock movement
            $stmt = $conn->prepare("INSERT INTO inventory_transactions 
                                   (item_id, gudang_id, tipe_transaksi, jumlah, harga_satuan, total_nilai, keterangan, tanggal_transaksi, created_by) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $total_nilai = $jumlah * $harga_satuan;
            $stmt->bind_param('iisiddssi', $item_id, $gudang_id, $tipe_transaksi, $jumlah, $harga_satuan, $total_nilai, $keterangan, $tanggal_transaksi, $_SESSION['user']['id'] ?? null);
            $stmt->execute();
            $stmt->close();
            
            // Update inventory item stock (if needed)
            // This could be handled by triggers or scheduled updates
        });
        
        $message = $tipe_transaksi === 'in' ? 'Barang masuk berhasil dicatat' : 'Barang keluar berhasil dicatat';
        flashMessage('success', $message);
        redirect('inventory/stockMovements');
    }
    
    private function getCurrentStock($item_id) {
        $result = fetchRow(
            "SELECT COALESCE(SUM(CASE WHEN tipe_transaksi = 'in' THEN jumlah ELSE -jumlah END), 0) as stok 
             FROM inventory_transactions 
             WHERE item_id = ?",
            [$item_id],
            'i'
        );
        return $result['stok'] ?? 0;
    }
    
    private function getInventoryStats() {
        $totalItems = fetchRow("SELECT COUNT(*) as total FROM inventory_items WHERE is_active = 1");
        $totalWarehouses = fetchRow("SELECT COUNT(*) as total FROM warehouses WHERE is_active = 1");
        $totalSuppliers = fetchRow("SELECT COUNT(*) as total FROM vendors WHERE is_active = 1");
        $lowStockItems = fetchRow("SELECT COUNT(*) as total FROM inventory_items WHERE is_active = 1 AND stok_minimum > (SELECT COALESCE(SUM(CASE WHEN tipe_transaksi = 'in' THEN jumlah ELSE -jumlah END), 0) FROM inventory_transactions WHERE item_id = inventory_items.id)");
        $totalStockValue = fetchRow("SELECT COALESCE(SUM((SELECT COALESCE(SUM(CASE WHEN tipe_transaksi = 'in' THEN jumlah ELSE -jumlah END), 0) FROM inventory_transactions WHERE item_id = i.id) * i.harga_beli), 0) as total FROM inventory_items i WHERE i.is_active = 1");
        
        return [
            'total_items' => $totalItems['total'] ?? 0,
            'total_warehouses' => $totalWarehouses['total'] ?? 0,
            'total_suppliers' => $totalSuppliers['total'] ?? 0,
            'low_stock_items' => $lowStockItems['total'] ?? 0,
            'total_stock_value' => $totalStockValue['total'] ?? 0
        ];
    }
    
    private function getLowStockItems() {
        return fetchAll(
            "SELECT i.*, 
                    COALESCE((SELECT SUM(CASE WHEN tipe_transaksi = 'in' THEN jumlah ELSE -jumlah END) 
                             FROM inventory_transactions WHERE item_id = i.id), 0) as stok_tersedia,
                    c.nama_kategori
             FROM inventory_items i
             LEFT JOIN kategori_produk c ON i.kategori_id = c.id
             WHERE i.is_active = 1 
             AND i.stok_minimum > COALESCE((SELECT SUM(CASE WHEN tipe_transaksi = 'in' THEN jumlah ELSE -jumlah END) 
                                          FROM inventory_transactions WHERE item_id = i.id), 0)
             ORDER BY (i.stok_minimum - COALESCE((SELECT SUM(CASE WHEN tipe_transaksi = 'in' THEN jumlah ELSE -jumlah END) 
                                                FROM inventory_transactions WHERE item_id = i.id), 0)) DESC
             LIMIT 10"
        ) ?? [];
    }
    
    private function getRecentMovements() {
        return fetchAll(
            "SELECT it.*, ii.nama_item, ii.kode_item, w.nama_gudang, u.username
             FROM inventory_transactions it
             LEFT JOIN inventory_items ii ON it.item_id = ii.id
             LEFT JOIN warehouses w ON it.gudang_id = w.id
             LEFT JOIN users u ON it.created_by = u.id
             ORDER BY it.created_at DESC
             LIMIT 10"
        ) ?? [];
    }
}
?>
