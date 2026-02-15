<?php
require_once __DIR__ . '/BaseController.php';

/**
 * InvoiceController handles invoice generation, tracking, and payment management.
 * Supports both customer invoices (sales) and supplier invoices (purchases).
 */
class InvoiceController extends BaseController {
    /**
     * Display invoice management dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $stats = $this->getInvoiceStats();

        $this->render('invoice/index', [
            'stats' => $stats
        ]);
    }

    /**
     * Display customer invoices (sales invoices).
     */
    public function customerInvoices() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM customer_invoices") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $invoices = fetchAll("SELECT ci.*, p.no_faktur, u.full_name as customer_name FROM customer_invoices ci LEFT JOIN penjualan p ON ci.order_id = p.id LEFT JOIN users u ON ci.customer_id = u.id ORDER BY ci.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('invoice/customer_invoices', [
            'invoices' => $invoices,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Generate invoice for a sales order.
     */
    public function generateCustomerInvoice($order_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $order = fetchRow("SELECT p.*, u.full_name as customer_name, u.email FROM penjualan p LEFT JOIN users u ON p.pelanggan_id = u.id WHERE p.id = ?", [$order_id], 'i');

        if (!$order) {
            flashMessage('error', 'Order tidak ditemukan');
            redirect('penjualan');
        }

        if ($order['status_pembayaran'] !== 'lunas') {
            flashMessage('error', 'Order belum lunas, tidak dapat generate invoice');
            redirect('penjualan/detail/' . $order_id);
        }

        // Check if invoice already exists
        $existing = fetchRow("SELECT id FROM customer_invoices WHERE order_id = ?", [$order_id], 'i');
        if ($existing) {
            flashMessage('error', 'Invoice sudah ada untuk order ini');
            redirect('penjualan/detail/' . $order_id);
        }

        // Generate invoice number
        $invoice_number = $this->generateInvoiceNumber('customer');

        runInTransaction(function($conn) use ($order_id, $order, $invoice_number) {
            $stmt = $conn->prepare("INSERT INTO customer_invoices (invoice_number, order_id, customer_id, total_amount, status, due_date, created_by, created_at) VALUES (?, ?, ?, ?, 'unpaid', DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?, NOW())");
            $created_by = $_SESSION['user']['id'] ?? 1;
            $stmt->bind_param('siidi', $invoice_number, $order_id, $order['pelanggan_id'], $order['total_bayar'], $created_by);
            $stmt->execute();
            $invoice_id = $stmt->insert_id;
            $stmt->close();

            // Log invoice generation
            $this->logInvoiceActivity($invoice_id, 'generated', 'Invoice generated for order ' . $order_id);
        });

        flashMessage('success', 'Invoice berhasil dibuat: ' . $invoice_number);
        redirect('invoice/customerInvoices');
    }

    /**
     * Display supplier invoices (purchase invoices).
     */
    public function supplierInvoices() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM supplier_invoices") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $invoices = fetchAll("SELECT si.*, s.nama_perusahaan as supplier_name, po.nomor_po FROM supplier_invoices si LEFT JOIN suppliers s ON si.supplier_id = s.id LEFT JOIN purchase_orders po ON si.po_id = po.id ORDER BY si.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('invoice/supplier_invoices', [
            'invoices' => $invoices,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Record supplier invoice.
     */
    public function recordSupplierInvoice() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $po_id = intval($_POST['po_id']);
            $nomor_invoice = sanitize($_POST['nomor_invoice']);
            $tanggal_invoice = sanitize($_POST['tanggal_invoice']);
            $tanggal_jatuh_tempo = sanitize($_POST['tanggal_jatuh_tempo']);

            $po = fetchRow("SELECT * FROM purchase_orders WHERE id = ?", [$po_id], 'i');
            if (!$po) {
                flashMessage('error', 'Purchase Order tidak ditemukan');
                redirect('invoice/supplierInvoices');
            }

            // Check if invoice already exists for this PO
            $existing = fetchRow("SELECT id FROM supplier_invoices WHERE po_id = ?", [$po_id], 'i');
            if ($existing) {
                flashMessage('error', 'Invoice sudah ada untuk PO ini');
                redirect('invoice/supplierInvoices');
            }

            runInTransaction(function($conn) use ($po_id, $po, $nomor_invoice, $tanggal_invoice, $tanggal_jatuh_tempo) {
                $stmt = $conn->prepare("INSERT INTO supplier_invoices (po_id, supplier_id, nomor_invoice, tanggal_invoice, tanggal_jatuh_tempo, total_nilai, status_pembayaran, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, 'belum_lunas', ?, NOW())");
                $created_by = $_SESSION['user']['id'] ?? 1;
                $stmt->bind_param('iisssdi', $po_id, $po['supplier_id'], $nomor_invoice, $tanggal_invoice, $tanggal_jatuh_tempo, $po['total_nilai'], $created_by);
                $stmt->execute();
                $invoice_id = $stmt->insert_id;
                $stmt->close();

                // Update PO status
                $stmt2 = $conn->prepare("UPDATE purchase_orders SET status = 'invoiced' WHERE id = ?");
                $stmt2->bind_param('i', $po_id);
                $stmt2->execute();
                $stmt2->close();

                // Log invoice recording
                $this->logInvoiceActivity($invoice_id, 'recorded', 'Supplier invoice recorded for PO ' . $po['nomor_po'], 'supplier');
            });

            flashMessage('success', 'Invoice supplier berhasil dicatat');
            redirect('invoice/supplierInvoices');
        }

        $pos = fetchAll("SELECT po.*, s.nama_perusahaan FROM purchase_orders po LEFT JOIN suppliers s ON po.supplier_id = s.id WHERE po.status = 'completed' ORDER BY po.created_at DESC");

        $this->render('invoice/record_supplier_invoice', [
            'pos' => $pos
        ]);
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid($invoice_id, $type = 'customer') {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $table = $type === 'supplier' ? 'supplier_invoices' : 'customer_invoices';
        $status_field = $type === 'supplier' ? 'status_pembayaran' : 'status';

        runInTransaction(function($conn) use ($invoice_id, $table, $status_field, $type) {
            $stmt = $conn->prepare("UPDATE {$table} SET {$status_field} = ?, tanggal_bayar = CURDATE() WHERE id = ?");
            $paid_status = $type === 'supplier' ? 'lunas' : 'paid';
            $stmt->bind_param('si', $paid_status, $invoice_id);
            $stmt->execute();
            $stmt->close();

            // Log payment
            $this->logInvoiceActivity($invoice_id, 'paid', 'Invoice marked as paid', $type);
        });

        $message = $type === 'supplier' ? 'Invoice supplier' : 'Invoice customer';
        flashMessage('success', $message . ' berhasil ditandai sebagai lunas');
        
        $redirect = $type === 'supplier' ? 'supplierInvoices' : 'customerInvoices';
        redirect('invoice/' . $redirect);
    }

    /**
     * Download/view invoice PDF.
     */
    public function downloadInvoice($invoice_id, $type = 'customer') {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        // Placeholder for PDF generation
        flashMessage('info', 'Fitur download invoice PDF akan diimplementasikan dengan library FPDF/TCPDF');
        redirect('invoice/' . ($type === 'supplier' ? 'supplierInvoices' : 'customerInvoices'));
    }

    /**
     * Get invoice statistics.
     */
    private function getInvoiceStats() {
        try {
            $totalCust = (fetchRow("SELECT COUNT(*) as total FROM customer_invoices") ?? [])['total'] ?? 0;
            $paidCust = (fetchRow("SELECT COUNT(*) as total FROM customer_invoices WHERE status = 'paid'") ?? [])['total'] ?? 0;
            $totalSupp = (fetchRow("SELECT COUNT(*) as total FROM supplier_invoices") ?? [])['total'] ?? 0;
            $paidSupp = (fetchRow("SELECT COUNT(*) as total FROM supplier_invoices WHERE status_pembayaran = 'lunas'") ?? [])['total'] ?? 0;
            $stats = [
                'total_customer_invoices' => $totalCust,
                'paid_customer_invoices' => $paidCust,
                'unpaid_customer_invoices' => $totalCust - $paidCust,
                'total_supplier_invoices' => $totalSupp,
                'paid_supplier_invoices' => $paidSupp,
                'unpaid_supplier_invoices' => $totalSupp - $paidSupp,
                'overdue_customer' => (fetchRow("SELECT COUNT(*) as total FROM customer_invoices WHERE status = 'unpaid' AND due_date < CURDATE()") ?? [])['total'] ?? 0,
                'overdue_supplier' => (fetchRow("SELECT COUNT(*) as total FROM supplier_invoices WHERE status_pembayaran = 'belum_lunas' AND tanggal_jatuh_tempo < CURDATE()") ?? [])['total'] ?? 0,
            ];
        } catch (Exception $e) {
            $stats = ['total_customer_invoices' => 0, 'paid_customer_invoices' => 0, 'unpaid_customer_invoices' => 0, 'total_supplier_invoices' => 0, 'paid_supplier_invoices' => 0, 'unpaid_supplier_invoices' => 0, 'overdue_customer' => 0, 'overdue_supplier' => 0];
        }
        return $stats;
    }

    /**
     * Generate unique invoice number.
     */
    private function generateInvoiceNumber($type) {
        $prefix = $type === 'supplier' ? 'SUP' : 'CUS';
        $date = date('Ym');
        $last_invoice = fetchRow("SELECT invoice_number FROM " . ($type === 'supplier' ? 'supplier_invoices' : 'customer_invoices') . " WHERE invoice_number LIKE ? ORDER BY id DESC LIMIT 1", [$prefix . $date . '%'], 's');
        
        if ($last_invoice) {
            $last_number = intval(substr($last_invoice['invoice_number'], -4));
            $new_number = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $new_number = '0001';
        }

        return $prefix . $date . $new_number;
    }

    /**
     * Log invoice activity.
     */
    private function logInvoiceActivity($invoice_id, $action, $details, $type = 'customer') {
        runInTransaction(function($conn) use ($invoice_id, $action, $details, $type) {
            $stmt = $conn->prepare("INSERT INTO invoice_logs (invoice_id, invoice_type, action, details, performed_by, performed_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $performed_by = $_SESSION['user']['id'] ?? null;
            $stmt->bind_param('isssi', $invoice_id, $type, $action, $details, $performed_by);
            $stmt->execute();
            $stmt->close();
        });
    }
}
