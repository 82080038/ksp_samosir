<?php
require_once __DIR__ . '/BaseController.php';

/**
 * CustomerServiceController handles customer service operations.
 * Manages helpdesk tickets, returns/refunds, and customer communication.
 */
class CustomerServiceController extends BaseController {
    /**
     * Display customer service dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $stats = $this->getCustomerServiceStats();
        $recent_tickets = $this->getRecentTickets();
        $pending_returns = $this->getPendingReturns();

        $this->render('customer_service/index', [
            'stats' => $stats,
            'recent_tickets' => $recent_tickets,
            'pending_returns' => $pending_returns
        ]);
    }

    /**
     * Display tickets management.
     */
    public function tickets() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM tickets") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $tickets = fetchAll("SELECT t.*, u.full_name as customer_name FROM tickets t LEFT JOIN users u ON t.customer_id = u.id ORDER BY t.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('customer_service/tickets', [
            'tickets' => $tickets,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Create new ticket.
     */
    public function createTicket() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customer_id = intval($_POST['customer_id']);
            $kategori = sanitize($_POST['kategori']);
            $prioritas = sanitize($_POST['prioritas']);
            $subjek = sanitize($_POST['subjek']);
            $deskripsi = sanitize($_POST['deskripsi']);

            if (empty($customer_id) || empty($subjek) || empty($deskripsi)) {
                flashMessage('error', 'Data tiket tidak lengkap');
                redirect('customer_service/createTicket');
            }

            runInTransaction(function($conn) use ($customer_id, $kategori, $prioritas, $subjek, $deskripsi) {
                $stmt = $conn->prepare("INSERT INTO tickets (customer_id, kategori, prioritas, subjek, deskripsi, status) VALUES (?, ?, ?, ?, ?, 'open')");
                $stmt->bind_param('issss', $customer_id, $kategori, $prioritas, $subjek, $deskripsi);
                $stmt->execute();
                $stmt->close();
            });

            flashMessage('success', 'Tiket berhasil dibuat');
            redirect('customer_service/tickets');
        }

        $customers = fetchAll("SELECT id, full_name FROM users WHERE role = 'member' ORDER BY full_name");

        $this->render('customer_service/create_ticket', [
            'customers' => $customers
        ]);
    }

    /**
     * Display returns/refunds management.
     */
    public function returns() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM returns") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $returns = fetchAll("SELECT r.*, p.no_faktur, u.full_name as customer_name FROM returns r LEFT JOIN penjualan p ON r.order_id = p.id LEFT JOIN users u ON r.customer_id = u.id ORDER BY r.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('customer_service/returns', [
            'returns' => $returns,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Create new return request.
     */
    public function createReturn() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $order_id = intval($_POST['order_id']);
            $alasan_return = sanitize($_POST['alasan_return']);

            if (empty($order_id) || empty($alasan_return)) {
                flashMessage('error', 'Data return tidak lengkap');
                redirect('customer_service/createReturn');
            }

            // Get customer from order
            $order = fetchRow("SELECT pelanggan_id FROM penjualan WHERE id = ?", [$order_id], 'i');
            if (!$order) {
                flashMessage('error', 'Order tidak ditemukan');
                redirect('customer_service/createReturn');
            }

            runInTransaction(function($conn) use ($order_id, $order, $alasan_return) {
                $stmt = $conn->prepare("INSERT INTO returns (order_id, customer_id, alasan_return, status) VALUES (?, ?, ?, 'pending')");
                $stmt->bind_param('iis', $order_id, $order['pelanggan_id'], $alasan_return);
                $stmt->execute();
                $stmt->close();
            });

            flashMessage('success', 'Return request berhasil dibuat');
            redirect('customer_service/returns');
        }

        $orders = fetchAll("
            SELECT
                p.id,
                p.no_faktur,
                COALESCE(u.full_name, 'Unknown Customer') as customer_name,
                p.tanggal_penjualan,
                COALESCE(addr.street_address, 'No address') as customer_address,
                COALESCE(c_phone.contact_value, 'No phone') as customer_phone
            FROM penjualan p
            LEFT JOIN users u ON p.pelanggan_id = u.id
            LEFT JOIN addresses addr ON addr.id = (SELECT address_id FROM entity_addresses WHERE entity_type = 'customer' AND entity_id = u.id LIMIT 1)
            LEFT JOIN contacts c_phone ON c_phone.id = (SELECT contact_id FROM entity_contacts WHERE entity_type = 'customer' AND entity_id = u.id AND contact_id = c_phone.id AND c_phone.contact_type = 'phone' LIMIT 1)
            WHERE p.status_pembayaran = 'lunas'
            ORDER BY p.tanggal_penjualan DESC
            LIMIT 100
        ", [], '');

        $this->render('customer_service/create_return', [
            'orders' => $orders
        ]);
    }

    /**
     * Process return/refund with enhanced workflow.
     */
    public function processReturn($id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $keputusan = sanitize($_POST['keputusan']);
            $alasan_keputusan = sanitize($_POST['alasan_keputusan']);
            $jumlah_refund = floatval($_POST['jumlah_refund'] ?? 0);
            $metode_refund = sanitize($_POST['metode_refund'] ?? '');
            $catatan = sanitize($_POST['catatan']);

            // Enhanced validation
            if (empty($keputusan)) {
                flashMessage('error', 'Keputusan harus dipilih');
                redirect('customer_service/processReturn/' . $id);
            }

            if ($keputusan === 'approved' && $jumlah_refund <= 0) {
                flashMessage('error', 'Jumlah refund harus diisi untuk return yang disetujui');
                redirect('customer_service/processReturn/' . $id);
            }

            runInTransaction(function($conn) use ($id, $keputusan, $alasan_keputusan, $jumlah_refund, $metode_refund, $catatan) {
                // Update return status
                $stmt = $conn->prepare("UPDATE returns SET keputusan = ?, jumlah_refund = ?, metode_refund = ?, status = 'processed', resolved_at = NOW(), processed_by = ?, alasan_keputusan = ?, catatan = ? WHERE id = ?");
                $processed_by = $_SESSION['user']['id'] ?? 1;
                $stmt->bind_param('sdssissi', $keputusan, $jumlah_refund, $metode_refund, $processed_by, $alasan_keputusan, $catatan, $id);
                $stmt->execute();
                $stmt->close();

                // If approved and refund needed, create refund record
                if ($keputusan === 'approved' && $jumlah_refund > 0) {
                    $stmt2 = $conn->prepare("INSERT INTO refunds (return_id, amount, method, status, created_by, created_at) VALUES (?, ?, ?, 'pending', ?, NOW())");
                    $stmt2->bind_param('idsi', $id, $jumlah_refund, $metode_refund, $processed_by);
                    $stmt2->execute();
                    $stmt2->close();
                }

                // Log return processing
                $this->logReturnActivity($id, 'processed', 'Return processed with decision: ' . $keputusan);
            });

            flashMessage('success', 'Return berhasil diproses');
            
            // Send notification
            $this->sendReturnNotification($id);
            
            redirect('customer_service/returns');
        }

        $return = fetchRow("SELECT r.*, p.no_faktur, u.full_name as customer_name, pr.full_name as processed_by_name FROM returns r LEFT JOIN penjualan p ON r.order_id = p.id LEFT JOIN users u ON r.customer_id = u.id LEFT JOIN users pr ON r.processed_by = pr.id WHERE r.id = ?", [$id], 'i');

        $this->render('customer_service/process_return', [
            'return' => $return
        ]);
    }

    /**
     * Approve return request (for multi-step approval).
     */
    public function approveReturn($id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        runInTransaction(function($conn) use ($id) {
            $approved_by = $_SESSION['user']['id'] ?? 1;
            $stmt = $conn->prepare("UPDATE returns SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmt->bind_param('ii', $approved_by, $id);
            $stmt->execute();
            $stmt->close();

            // Log approval
            $this->logReturnActivity($id, 'approved', 'Return request approved');
        });

        flashMessage('success', 'Return request berhasil diapprove');
        redirect('customer_service/returns');
    }

    /**
     * Process refund payment.
     */
    public function processRefund($refund_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        runInTransaction(function($conn) use ($refund_id) {
            $processed_by = $_SESSION['user']['id'] ?? 1;
            $stmt = $conn->prepare("UPDATE refunds SET status = 'completed', processed_by = ?, processed_at = NOW() WHERE id = ?");
            $stmt->bind_param('ii', $processed_by, $refund_id);
            $stmt->execute();
            $stmt->close();

            // Log refund processing
            $this->logRefundActivity($refund_id, 'completed', 'Refund payment processed');
        });

        flashMessage('success', 'Refund berhasil diproses');
        redirect('customer_service/refunds');
    }

    /**
     * Display refunds management.
     */
    public function refunds() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM refunds") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $refunds = fetchAll("SELECT r.*, ret.alasan_return, u.full_name as customer_name, pr.full_name as processed_by_name FROM refunds r LEFT JOIN returns ret ON r.return_id = ret.id LEFT JOIN users u ON ret.customer_id = u.id LEFT JOIN users pr ON r.processed_by = pr.id ORDER BY r.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('customer_service/refunds', [
            'refunds' => $refunds,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Log return activity.
     */
    private function logReturnActivity($return_id, $action, $details) {
        runInTransaction(function($conn) use ($return_id, $action, $details) {
            $stmt = $conn->prepare("INSERT INTO return_logs (return_id, action, details, performed_by, performed_at) VALUES (?, ?, ?, ?, NOW())");
            $performed_by = $_SESSION['user']['id'] ?? null;
            $stmt->bind_param('issi', $return_id, $action, $details, $performed_by);
            $stmt->execute();
            $stmt->close();
        });
    }

    /**
     * Log refund activity.
     */
    private function logRefundActivity($refund_id, $action, $details) {
        runInTransaction(function($conn) use ($refund_id, $action, $details) {
            $stmt = $conn->prepare("INSERT INTO refund_logs (refund_id, action, details, performed_by, performed_at) VALUES (?, ?, ?, ?, NOW())");
            $performed_by = $_SESSION['user']['id'] ?? null;
            $stmt->bind_param('issi', $refund_id, $action, $details, $performed_by);
            $stmt->execute();
            $stmt->close();
        });
    }

    /**
     * Send customer communication.
     */
    public function communication() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customer_id = intval($_POST['customer_id']);
            $tipe_komunikasi = sanitize($_POST['tipe_komunikasi']);
            $subjek = sanitize($_POST['subjek']);
            $pesan = sanitize($_POST['pesan']);

            // Placeholder for communication sending
            // In real implementation, integrate with WhatsApp/SMS API
            flashMessage('success', 'Pesan berhasil dikirim (placeholder)');
            redirect('customer_service/communication');
        }

        $customers = fetchAll("SELECT id, full_name FROM users WHERE role = 'member' ORDER BY full_name");

        $this->render('customer_service/communication', [
            'customers' => $customers
        ]);
    }

    /**
     * Get customer service statistics.
     */
    private function getCustomerServiceStats() {
        try {
            $stats = [
                'total_tickets' => (fetchRow("SELECT COUNT(*) as total FROM tickets") ?? [])['total'] ?? 0,
                'open_tickets' => (fetchRow("SELECT COUNT(*) as total FROM tickets WHERE status = 'open'") ?? [])['total'] ?? 0,
                'pending_returns' => (fetchRow("SELECT COUNT(*) as total FROM returns WHERE status = 'pending'") ?? [])['total'] ?? 0,
                'processed_returns_month' => (fetchRow("SELECT COUNT(*) as total FROM returns WHERE MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE) AND status = 'processed'") ?? [])['total'] ?? 0,
            ];
        } catch (Exception $e) {
            $stats = ['total_tickets' => 0, 'open_tickets' => 0, 'pending_returns' => 0, 'processed_returns_month' => 0];
        }
        return $stats;
    }

    /**
     * Get recent tickets.
     */
    private function getRecentTickets() {
        return fetchAll("SELECT t.*, u.full_name as customer_name FROM tickets t LEFT JOIN users u ON t.customer_id = u.id ORDER BY t.created_at DESC LIMIT 5") ?? [];
    }

    /**
     * Get pending returns.
     */
    private function getPendingReturns() {
        return fetchAll("SELECT r.*, p.no_faktur, u.full_name as customer_name FROM returns r LEFT JOIN penjualan p ON r.order_id = p.id LEFT JOIN users u ON r.customer_id = u.id WHERE r.status = 'pending' ORDER BY r.created_at DESC LIMIT 5") ?? [];
    }
}
