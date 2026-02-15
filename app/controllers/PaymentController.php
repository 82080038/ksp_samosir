<?php
require_once __DIR__ . '/BaseController.php';

/**
 * PaymentController handles payment gateway integrations.
 * Supports Midtrans payment processing and webhooks.
 */
class PaymentController extends BaseController {
    private $midtrans_config = [
        'server_key' => 'SB-Mid-server-your-server-key', // Replace with actual key
        'client_key' => 'SB-Mid-client-your-client-key', // Replace with actual key
        'is_production' => false, // Set to true for production
        'is_sanitized' => true,
        'is_3ds' => true
    ];

    public function __construct() {
        // Initialize Midtrans
        \Midtrans\Config::$serverKey = $this->midtrans_config['server_key'];
        \Midtrans\Config::$clientKey = $this->midtrans_config['client_key'];
        \Midtrans\Config::$isProduction = $this->midtrans_config['is_production'];
        \Midtrans\Config::$isSanitized = $this->midtrans_config['is_sanitized'];
        \Midtrans\Config::$is3ds = $this->midtrans_config['is_3ds'];
    }

    /**
     * Create payment transaction for sales order.
     */
    public function createPayment($order_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff', 'member']); // DISABLED for development

        // Get order details
        $order = fetchRow("SELECT p.*, pl.nama_pelanggan, pl.email FROM penjualan p LEFT JOIN pelanggan pl ON p.pelanggan_id = pl.id WHERE p.id = ?", [$order_id], 'i');

        if (!$order) {
            flashMessage('error', 'Order tidak ditemukan');
            redirect('penjualan');
        }

        if ($order['status_pembayaran'] === 'lunas') {
            flashMessage('error', 'Order sudah dibayar');
            redirect('penjualan/detail/' . $order_id);
        }

        // Get order items
        $items = fetchAll("SELECT dp.*, pr.nama_produk FROM detail_penjualan dp LEFT JOIN produk pr ON dp.produk_id = pr.id WHERE dp.penjualan_id = ?", [$order_id], 'i');

        // Prepare Midtrans transaction data
        $transaction_details = [
            'order_id' => 'ORDER-' . $order_id,
            'gross_amount' => (int)$order['total_bayar']
        ];

        $item_details = [];
        foreach ($items as $item) {
            $item_details[] = [
                'id' => $item['produk_id'],
                'price' => (int)$item['harga_satuan'],
                'quantity' => (int)$item['jumlah'],
                'name' => substr($item['nama_produk'], 0, 50)
            ];
        }

        // Add shipping if applicable
        if ($order['total_bayar'] > $order['total_harga']) {
            $item_details[] = [
                'id' => 'shipping',
                'price' => (int)($order['total_bayar'] - $order['total_harga']),
                'quantity' => 1,
                'name' => 'Biaya Pengiriman'
            ];
        }

        $customer_details = [
            'first_name' => substr($order['nama_pelanggan'], 0, 50),
            'email' => $order['email'] ?: 'customer@example.com',
            'phone' => '08123456789' // Default, should be from customer data
        ];

        $transaction_data = [
            'transaction_details' => $transaction_details,
            'item_details' => $item_details,
            'customer_details' => $customer_details,
            'callbacks' => [
                'finish' => base_url('payment/finish'),
                'error' => base_url('payment/error'),
                'pending' => base_url('payment/pending')
            ]
        ];

        try {
            $snap_token = \Midtrans\Snap::getSnapToken($transaction_data);

            // Store payment attempt
            runInTransaction(function($conn) use ($order_id, $snap_token) {
                $stmt = $conn->prepare("INSERT INTO payment_attempts (order_id, payment_gateway, transaction_id, snap_token, status, created_at) VALUES (?, 'midtrans', ?, ?, 'pending', NOW())");
                $stmt->bind_param('iss', $order_id, 'ORDER-' . $order_id, $snap_token);
                $stmt->execute();
                $stmt->close();
            });

            $this->render('payment/checkout', [
                'snap_token' => $snap_token,
                'order' => $order,
                'items' => $items
            ]);

        } catch (Exception $e) {
            flashMessage('error', 'Gagal membuat transaksi pembayaran: ' . $e->getMessage());
            redirect('penjualan/detail/' . $order_id);
        }
    }

    /**
     * Handle payment success callback.
     */
    public function handleNotification() {
        $notification = new \Midtrans\Notification();

        $transaction_status = $notification->transaction_status;
        $order_id = str_replace('ORDER-', '', $notification->order_id);
        $payment_type = $notification->payment_type;
        $fraud_status = $notification->fraud_status;

        // Update payment status based on notification
        $status_mapping = [
            'capture' => 'lunas',
            'settlement' => 'lunas',
            'pending' => 'pending',
            'deny' => 'gagal',
            'cancel' => 'gagal',
            'expire' => 'gagal',
            'failure' => 'gagal'
        ];

        $payment_status = $status_mapping[$transaction_status] ?? 'pending';

        runInTransaction(function($conn) use ($order_id, $transaction_status, $payment_type, $payment_status, $fraud_status) {
            // Update penjualan status
            $stmt = $conn->prepare("UPDATE penjualan SET status_pembayaran = ?, metode_pembayaran = ? WHERE id = ?");
            $stmt->bind_param('ssi', $payment_status, $payment_type, $order_id);
            $stmt->execute();
            $stmt->close();

            // Update payment attempt
            $stmt2 = $conn->prepare("UPDATE payment_attempts SET status = ?, fraud_status = ?, updated_at = NOW() WHERE order_id = ? AND payment_gateway = 'midtrans'");
            $stmt2->bind_param('ssi', $transaction_status, $fraud_status, $order_id);
            $stmt2->execute();
            $stmt2->close();

            // Log payment notification
            $stmt3 = $conn->prepare("INSERT INTO payment_logs (order_id, gateway, notification_type, transaction_status, payment_type, fraud_status, raw_data, created_at) VALUES (?, 'midtrans', 'notification', ?, ?, ?, ?, NOW())");
            $raw_data = json_encode($_POST);
            $stmt3->bind_param('issss', $order_id, $transaction_status, $payment_type, $fraud_status, $raw_data);
            $stmt3->execute();
            $stmt3->close();

            // Send payment success notification if payment is successful
            if (in_array($transaction_status, ['capture', 'settlement'])) {
                require_once __DIR__ . '/NotificationController.php';
                $notification = new NotificationController();
                $notification->sendPaymentSuccess($order_id);
            }
        });

        // Return success response to Midtrans
        http_response_code(200);
        echo 'OK';
        exit;
    }

    /**
     * Payment success page.
     */
    public function success() {
        $order_id = str_replace('ORDER-', '', $_GET['order_id'] ?? '');
        $this->render('payment/success', [
            'order_id' => $order_id
        ], false); // Standalone page
    }

    /**
     * Payment pending page.
     */
    public function pending() {
        $order_id = str_replace('ORDER-', '', $_GET['order_id'] ?? '');
        $this->render('payment/pending', [
            'order_id' => $order_id
        ], false); // Standalone page
    }

    /**
     * Payment error page.
     */
    public function error() {
        $order_id = str_replace('ORDER-', '', $_GET['order_id'] ?? '');
        $this->render('payment/error', [
            'order_id' => $order_id
        ], false); // Standalone page
    }

    /**
     * Check payment status.
     */
    public function checkStatus($order_id) {
        $status = fetchRow("SELECT status_pembayaran FROM penjualan WHERE id = ?", [$order_id], 'i');
        header('Content-Type: application/json');
        echo json_encode(['status' => $status['status_pembayaran'] ?? 'unknown']);
        exit;
    }
}
