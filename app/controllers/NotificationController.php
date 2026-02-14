<?php
require_once __DIR__ . '/BaseController.php';

/**
 * NotificationController handles SMS and WhatsApp notifications.
 * Integrates with external APIs for sending messages.
 */
class NotificationController extends BaseController {
    private $whatsapp_config = [
        'api_url' => 'https://api.fonnte.com/send',
        'api_key' => 'your-whatsapp-api-key' // Replace with actual key
    ];

    private $sms_config = [
        'api_url' => 'https://api.zenziva.net/v1/send',
        'user_key' => 'your-sms-user-key', // Replace with actual key
        'pass_key' => 'your-sms-pass-key'  // Replace with actual key
    ];

    /**
     * Send order confirmation notification.
     */
    public function sendOrderConfirmation($order_id) {
        $order = fetchRow("SELECT p.*, u.full_name as customer_name, u.email FROM penjualan p LEFT JOIN users u ON p.pelanggan_id = u.id WHERE p.id = ?", [$order_id], 'i');

        if (!$order) return false;

        $message = "Halo {$order['customer_name']},\n\n";
        $message .= "Pesanan Anda #{$order_id} telah diterima.\n";
        $message .= "Total: Rp " . formatCurrency($order['total_bayar']) . "\n";
        $message .= "Status: {$order['status_pembayaran']}\n\n";
        $message .= "Terima kasih telah berbelanja di KSP Samosir.";

        $this->sendWhatsApp($order['id'], $message); // Assuming phone number is available
        $this->logNotification($order_id, 'order_confirmation', 'whatsapp', $message);

        return true;
    }

    /**
     * Send payment success notification.
     */
    public function sendPaymentSuccess($order_id) {
        $order = fetchRow("SELECT p.*, u.full_name as customer_name FROM penjualan p LEFT JOIN users u ON p.pelanggan_id = u.id WHERE p.id = ?", [$order_id], 'i');

        if (!$order) return false;

        $message = "Halo {$order['customer_name']},\n\n";
        $message .= "Pembayaran untuk pesanan #{$order_id} telah berhasil.\n";
        $message .= "Total dibayar: Rp " . formatCurrency($order['total_bayar']) . "\n\n";
        $message .= "Pesanan Anda akan segera diproses.";

        $this->sendWhatsApp($order['id'], $message);
        $this->sendSMS($order['id'], $message);
        $this->logNotification($order_id, 'payment_success', 'whatsapp', $message);
        $this->logNotification($order_id, 'payment_success', 'sms', $message);

        return true;
    }

    /**
     * Send shipping notification.
     */
    public function sendShippingNotification($order_id) {
        $order = fetchRow("SELECT p.*, u.full_name as customer_name, s.courier, s.service, s.tracking_number FROM penjualan p LEFT JOIN users u ON p.pelanggan_id = u.id LEFT JOIN shipping_details s ON p.id = s.order_id WHERE p.id = ?", [$order_id], 'i');

        if (!$order) return false;

        $message = "Halo {$order['customer_name']},\n\n";
        $message .= "Pesanan #{$order_id} telah dikirim!\n";
        $message .= "Kurir: {$order['courier']} - {$order['service']}\n";
        if ($order['tracking_number']) {
            $message .= "No. Resi: {$order['tracking_number']}\n";
        }
        $message .= "\nTerima kasih telah berbelanja di KSP Samosir.";

        $this->sendWhatsApp($order['id'], $message);
        $this->logNotification($order_id, 'shipping', 'whatsapp', $message);

        return true;
    }

    /**
     * Send return status notification.
     */
    public function sendReturnNotification($return_id) {
        $return = fetchRow("SELECT r.*, u.full_name as customer_name, p.no_faktur FROM returns r LEFT JOIN users u ON r.customer_id = u.id LEFT JOIN penjualan p ON r.order_id = p.id WHERE r.id = ?", [$return_id], 'i');

        if (!$return) return false;

        $message = "Halo {$return['customer_name']},\n\n";
        $message .= "Status return untuk pesanan {$return['no_faktur']}:\n";
        $message .= "Status: {$return['status']}\n";
        if ($return['keputusan']) {
            $message .= "Keputusan: {$return['keputusan']}\n";
        }
        if ($return['jumlah_refund'] > 0) {
            $message .= "Refund: Rp " . formatCurrency($return['jumlah_refund']) . "\n";
        }

        $this->sendWhatsApp($return['customer_id'], $message);
        $this->logNotification($return_id, 'return_status', 'whatsapp', $message);

        return true;
    }

    /**
     * Send meeting notification.
     */
    public function sendMeetingNotification($meeting_id) {
        $meeting = fetchRow("SELECT * FROM rapat WHERE id = ?", [$meeting_id], 'i');
        $participants = fetchAll("SELECT u.full_name, u.id FROM rapat_peserta rp LEFT JOIN users u ON rp.user_id = u.id WHERE rp.rapat_id = ?", [$meeting_id], 'i');

        if (!$meeting) return false;

        $message = "Rapat: {$meeting['judul']}\n";
        $message .= "Tanggal: " . formatDate($meeting['tanggal'], 'd M Y') . "\n";
        $message .= "Waktu: {$meeting['waktu']}\n";
        $message .= "Lokasi: {$meeting['lokasi']}\n\n";
        $message .= "Agenda: {$meeting['agenda']}";

        foreach ($participants as $participant) {
            $this->sendWhatsApp($participant['id'], "Undangan " . $message);
            $this->logNotification($meeting_id, 'meeting_invitation', 'whatsapp', $message, $participant['id']);
        }

        return true;
    }

    /**
     * Send WhatsApp message.
     */
    private function sendWhatsApp($user_id, $message) {
        // Get user phone number (assuming it's stored in user profile)
        $user = fetchRow("SELECT phone FROM users WHERE id = ?", [$user_id], 'i');
        if (!$user || empty($user['phone'])) return false;

        // Placeholder for WhatsApp API call
        $postData = [
            'target' => $user['phone'],
            'message' => $message,
            'countryCode' => '62' // Indonesia
        ];

        // In production, uncomment and configure actual API call
        /*
        $ch = curl_init($this->whatsapp_config['api_url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->whatsapp_config['api_key']
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        */

        // For development, just log
        error_log("WhatsApp message to {$user['phone']}: {$message}");

        return true;
    }

    /**
     * Send SMS message.
     */
    private function sendSMS($user_id, $message) {
        // Get user phone number
        $user = fetchRow("SELECT phone FROM users WHERE id = ?", [$user_id], 'i');
        if (!$user || empty($user['phone'])) return false;

        // Placeholder for SMS API call
        $postData = [
            'userkey' => $this->sms_config['user_key'],
            'passkey' => $this->sms_config['pass_key'],
            'nohp' => $user['phone'],
            'pesan' => $message
        ];

        // In production, uncomment and configure actual API call
        /*
        $ch = curl_init($this->sms_config['api_url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        */

        // For development, just log
        error_log("SMS to {$user['phone']}: {$message}");

        return true;
    }

    /**
     * Log notification.
     */
    private function logNotification($reference_id, $type, $channel, $message, $user_id = null) {
        runInTransaction(function($conn) use ($reference_id, $type, $channel, $message, $user_id) {
            $stmt = $conn->prepare("INSERT INTO notification_logs (reference_id, type, channel, message, user_id, sent_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param('isssi', $reference_id, $type, $channel, $message, $user_id);
            $stmt->execute();
            $stmt->close();
        });
    }

    /**
     * Send bulk notifications for marketing.
     */
    public function sendBulkNotification() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render(__DIR__ . '/../views/notification/bulk.php');
            return;
        }

        $target_audience = sanitize($_POST['target_audience'] ?? 'all');
        $channel = sanitize($_POST['channel'] ?? 'whatsapp');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if (empty($message)) {
            flashMessage('error', 'Pesan tidak boleh kosong');
            redirect('notification/sendBulkNotification');
        }

        // Get target users
        $query = "SELECT id FROM users WHERE role = 'member' AND is_active = 1";
        if ($target_audience === 'investors') {
            $query .= " AND is_investor = 1";
        } elseif ($target_audience === 'agents') {
            $query .= " AND is_agen = 1";
        }

        $users = fetchAll($query);

        $success_count = 0;
        foreach ($users as $user) {
            if ($channel === 'whatsapp') {
                if ($this->sendWhatsApp($user['id'], $message)) $success_count++;
            } elseif ($channel === 'sms') {
                if ($this->sendSMS($user['id'], $message)) $success_count++;
            }
            $this->logNotification(null, 'bulk_' . $target_audience, $channel, $message, $user['id']);
        }

        flashMessage('success', "Notifikasi berhasil dikirim ke {$success_count} pengguna");
        redirect('notification/sendBulkNotification');
    }
}
