<?php
require_once __DIR__ . '/BaseController.php';

/**
 * PemasokController handles suppliers and procurement management.
 * Manages suppliers, purchase orders, and supplier invoices.
 */
class PemasokController extends BaseController {
    /**
     * Display procurement dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $stats = $this->getProcurementStats();
        $recent_pos = $this->getRecentPurchaseOrders();
        $pending_invoices = $this->getPendingInvoices();

        $this->render('pemasok/index', [
            'stats' => $stats,
            'recent_pos' => $recent_pos,
            'pending_invoices' => $pending_invoices
        ]);
    }

    /**
     * Display suppliers management.
     */
    public function suppliers() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM suppliers") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $suppliers = fetchAll("
            SELECT
                s.id,
                s.kode_pemasok,
                s.nama_perusahaan,
                s.npwp,
                COALESCE(c.category_name, 'Uncategorized') as category_name,
                COALESCE(st.status_name, 'Unknown') as status_name,
                COALESCE(addr.street_address, s.alamat) as alamat,
                COALESCE(c_phone.contact_value, s.no_telepon) as no_telepon,
                COALESCE(c_email.contact_value, s.email) as email,
                s.created_at
            FROM suppliers s
            LEFT JOIN categories c ON s.supplier_category_id = c.id
            LEFT JOIN status_types st ON st.category = 'supplier' AND st.status_code = s.status
            LEFT JOIN addresses addr ON s.address_id = addr.id AND addr.is_primary = 1
            LEFT JOIN contacts c_phone ON s.primary_contact_id = c_phone.id AND c_phone.contact_type = 'phone'
            LEFT JOIN contacts c_email ON c_email.id = (SELECT id FROM contacts WHERE contact_type = 'email' AND id IN (SELECT contact_id FROM entity_contacts WHERE entity_type = 'supplier' AND entity_id = s.id) LIMIT 1)
            ORDER BY s.created_at DESC
            LIMIT ? OFFSET ?
        ", [$perPage, $offset], 'ii');

        $this->render('pemasok/suppliers', [
            'suppliers' => $suppliers,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Create new supplier.
     */
    public function createSupplier() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->collectSupplierInput();
            $error = $this->validateSupplierInput($data);
            if ($error) {
                flashMessage('error', $error);
                redirect('pemasok/createSupplier');
            }

            runInTransaction(function($conn) use ($data) {
                $stmt = $conn->prepare("INSERT INTO suppliers (nama_perusahaan, npwp, alamat, telepon, email, kategori, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sssssss', $data['nama_perusahaan'], $data['npwp'], $data['alamat'], $data['telepon'], $data['email'], $data['kategori'], $data['status']);
                $stmt->execute();
                $stmt->close();
            });

            flashMessage('success', 'Pemasok berhasil ditambahkan');
            redirect('pemasok/suppliers');
        }

        $this->render('pemasok/create_supplier');
    }

    /**
     * Display purchase orders management.
     */
    public function purchaseOrders() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM purchase_orders") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $pos = fetchAll("SELECT po.*, s.nama_perusahaan as supplier_name FROM purchase_orders po LEFT JOIN suppliers s ON po.supplier_id = s.id ORDER BY po.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('pemasok/purchase_orders', [
            'pos' => $pos,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Create new purchase order.
     */
    public function createPO() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $supplier_id = intval($_POST['supplier_id']);
            $tanggal_po = sanitize($_POST['tanggal_po']);
            $tanggal_pengiriman = sanitize($_POST['tanggal_pengiriman']);
            $syarat_pembayaran = sanitize($_POST['syarat_pembayaran']);
            $products = $_POST['products'] ?? [];

            if (empty($supplier_id) || empty($tanggal_po) || empty($products)) {
                flashMessage('error', 'Data PO tidak lengkap');
                redirect('pemasok/createPO');
            }

            runInTransaction(function($conn) use ($supplier_id, $tanggal_po, $tanggal_pengiriman, $syarat_pembayaran, $products) {
                // Insert PO header
                $stmt = $conn->prepare("INSERT INTO purchase_orders (supplier_id, tanggal_po, tanggal_pengiriman, syarat_pembayaran, status) VALUES (?, ?, ?, ?, 'draft')");
                $stmt->bind_param('isss', $supplier_id, $tanggal_po, $tanggal_pengiriman, $syarat_pembayaran);
                $stmt->execute();
                $po_id = $stmt->insert_id;
                $stmt->close();

                // Insert PO details
                $total_nilai = 0;
                foreach ($products as $product) {
                    $produk_id = intval($product['produk_id']);
                    $qty = intval($product['qty']);
                    $harga_satuan = floatval($product['harga_satuan']);
                    $subtotal = $qty * $harga_satuan;

                    $stmt = $conn->prepare("INSERT INTO purchase_order_details (po_id, produk_id, qty, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param('iiidd', $po_id, $produk_id, $qty, $harga_satuan, $subtotal);
                    $stmt->execute();
                    $stmt->close();

                    $total_nilai += $subtotal;
                }

                // Update total nilai
                $stmt = $conn->prepare("UPDATE purchase_orders SET total_nilai = ? WHERE id = ?");
                $stmt->bind_param('di', $total_nilai, $po_id);
                $stmt->execute();
                $stmt->close();
            });

            flashMessage('success', 'Purchase Order berhasil dibuat');
            redirect('pemasok/purchaseOrders');
        }

        $suppliers = fetchAll("SELECT id, nama_perusahaan FROM suppliers WHERE status = 'aktif' ORDER BY nama_perusahaan");
        $products = fetchAll("SELECT id, nama_produk, kode_produk FROM produk WHERE is_active = 1 ORDER BY nama_produk");

        $this->render('pemasok/create_po', [
            'suppliers' => $suppliers,
            'products' => $products
        ]);
    }

    /**
     * Display supplier invoices management.
     */
    public function invoices() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM supplier_invoices") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $invoices = fetchAll("SELECT si.*, s.nama_perusahaan as supplier_name, po.nomor_po FROM supplier_invoices si LEFT JOIN suppliers s ON si.supplier_id = s.id LEFT JOIN purchase_orders po ON si.po_id = po.id ORDER BY si.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('pemasok/invoices', [
            'invoices' => $invoices,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Get procurement statistics.
     */
    private function getProcurementStats() {
        $stats = [];

        // Total suppliers
        $stats['total_suppliers'] = (fetchRow("SELECT COUNT(*) as total FROM suppliers WHERE status = 'aktif'") ?? [])['total'] ?? 0;

        // Total POs this month
        $stats['pos_this_month'] = (fetchRow("SELECT COUNT(*) as total FROM purchase_orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)") ?? [])['total'] ?? 0;

        // Total invoice value pending
        $stats['pending_invoice_value'] = (fetchRow("SELECT COALESCE(SUM(total_nilai), 0) as total FROM supplier_invoices WHERE status_pembayaran = 'belum_lunas'") ?? [])['total'] ?? 0;

        // Total POs pending
        $stats['pending_pos'] = (fetchRow("SELECT COUNT(*) as total FROM purchase_orders WHERE status = 'pending'") ?? [])['total'] ?? 0;

        return $stats;
    }

    /**
     * Get recent purchase orders.
     */
    private function getRecentPurchaseOrders() {
        return fetchAll("SELECT po.*, s.nama_perusahaan as supplier_name FROM purchase_orders po LEFT JOIN suppliers s ON po.supplier_id = s.id ORDER BY po.created_at DESC LIMIT 5") ?? [];
    }

    /**
     * Get pending invoices.
     */
    private function getPendingInvoices() {
        return fetchAll("SELECT si.*, s.nama_perusahaan as supplier_name FROM supplier_invoices si LEFT JOIN suppliers s ON si.supplier_id = s.id WHERE si.status_pembayaran = 'belum_lunas' ORDER BY si.tanggal_jatuh_tempo ASC LIMIT 5") ?? [];
    }

    /**
     * Collect supplier input data.
     */
    private function collectSupplierInput() {
        return [
            'nama_perusahaan' => sanitize($_POST['nama_perusahaan'] ?? ''),
            'npwp' => sanitize($_POST['npwp'] ?? ''),
            'alamat' => sanitize($_POST['alamat'] ?? ''),
            'telepon' => sanitize($_POST['telepon'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'kategori' => sanitize($_POST['kategori'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'aktif'),
        ];
    }

    /**
     * Validate supplier input.
     */
    private function validateSupplierInput($data) {
        if (empty($data['nama_perusahaan'])) {
            return 'Nama perusahaan wajib diisi';
        }
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return 'Format email tidak valid';
        }
        return null;
    }
}
