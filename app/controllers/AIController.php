<?php
require_once __DIR__ . '/BaseController.php';

/**
 * AIController handles basic AI features for recommendations and fraud detection.
 * Provides product recommendations and fraud detection algorithms.
 */
class AIController extends BaseController {
    /**
     * Display AI features dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $recommendation_stats = $this->getRecommendationStats();
        $fraud_stats = $this->getFraudDetectionStats();
        $recent_recommendations = $this->getRecentRecommendations();

        $this->render('ai/index', [
            'recommendation_stats' => $recommendation_stats,
            'fraud_stats' => $fraud_stats,
            'recent_recommendations' => $recent_recommendations
        ]);
    }

    /**
     * Generate product recommendations for a user.
     */
    public function getRecommendations($user_id = null) {
        $user_id = $user_id ?? intval($_GET['user_id'] ?? 0);

        if (!$user_id) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'User ID required']);
            exit;
        }

        $recommendations = $this->generateProductRecommendations($user_id);

        header('Content-Type: application/json');
        echo json_encode(['recommendations' => $recommendations]);
        exit;
    }

    /**
     * Run fraud detection on recent transactions.
     */
    public function runFraudDetection() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $suspicious_transactions = $this->detectFraudulentTransactions();
        $risky_users = $this->identifyRiskyUsers();
        $unusual_patterns = $this->detectUnusualPatterns();

        // Store fraud alerts
        $this->storeFraudAlerts($suspicious_transactions, $risky_users, $unusual_patterns);

        flashMessage('success', 'Deteksi fraud berhasil dijalankan. ' . count($suspicious_transactions) . ' transaksi mencurigakan terdeteksi.');
        redirect('ai/index');
    }

    /**
     * Get personalized product recommendations.
     */
    public function personalizedRecommendations() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $user_id = intval($_GET['user_id'] ?? 0);
        $limit = intval($_GET['limit'] ?? 5);

        if (!$user_id) {
            $this->render('ai/personalized_recommendations', [
                'error' => 'User ID required'
            ]);
            return;
        }

        $recommendations = $this->generateProductRecommendations($user_id, $limit);
        $user_purchase_history = $this->getUserPurchaseHistory($user_id);

        $this->render('ai/personalized_recommendations', [
            'recommendations' => $recommendations,
            'user_purchase_history' => $user_purchase_history,
            'user_id' => $user_id
        ]);
    }

    /**
     * Generate product recommendations based on collaborative filtering.
     */
    private function generateProductRecommendations($user_id, $limit = 10) {
        // Get user's purchase history
        $user_purchases = fetchAll("SELECT produk_id, COUNT(*) as purchase_count FROM detail_penjualan dp LEFT JOIN penjualan p ON dp.penjualan_id = p.id WHERE p.pelanggan_id = ? GROUP BY produk_id ORDER BY purchase_count DESC LIMIT 10", [$user_id], 'i');

        if (empty($user_purchases)) {
            // New user - recommend popular products
            return $this->getPopularProducts($limit);
        }

        $recommendations = [];
        $user_product_ids = array_column($user_purchases, 'produk_id');

        // Find users who bought similar products (collaborative filtering)
        $similar_users = fetchAll("SELECT DISTINCT p.pelanggan_id FROM detail_penjualan dp LEFT JOIN penjualan p ON dp.penjualan_id = p.id WHERE dp.produk_id IN (" . str_repeat('?,', count($user_product_ids) - 1) . "?) AND p.pelanggan_id != ? GROUP BY p.pelanggan_id HAVING COUNT(DISTINCT dp.produk_id) >= 2 ORDER BY COUNT(DISTINCT dp.produk_id) DESC LIMIT 10", array_merge($user_product_ids, [$user_id]), str_repeat('i', count($user_product_ids)) . 'i');

        if (!empty($similar_users)) {
            $similar_user_ids = array_column($similar_users, 'pelanggan_id');

            // Get products bought by similar users but not by current user
            $recommended_products = fetchAll("SELECT pr.id, pr.nama_produk, pr.harga_jual, COUNT(*) as frequency FROM detail_penjualan dp LEFT JOIN penjualan p ON dp.penjualan_id = p.id LEFT JOIN produk pr ON dp.produk_id = pr.id WHERE p.pelanggan_id IN (" . str_repeat('?,', count($similar_user_ids) - 1) . "?) AND dp.produk_id NOT IN (" . str_repeat('?,', count($user_product_ids) - 1) . "?) AND pr.is_active = 1 GROUP BY dp.produk_id ORDER BY frequency DESC LIMIT ?", array_merge($similar_user_ids, $user_product_ids, [$limit]), str_repeat('i', count($similar_user_ids) + count($user_product_ids)) . 'i');

            foreach ($recommended_products as $product) {
                $recommendations[] = [
                    'product_id' => $product['id'],
                    'name' => $product['nama_produk'],
                    'price' => $product['harga_jual'],
                    'reason' => 'Berdasarkan pembelian pengguna serupa',
                    'confidence' => min(100, $product['frequency'] * 10)
                ];
            }
        }

        // If not enough recommendations, add popular products
        if (count($recommendations) < $limit) {
            $popular_products = $this->getPopularProducts($limit - count($recommendations));
            foreach ($popular_products as $product) {
                // Check if not already recommended
                $already_recommended = array_filter($recommendations, function($rec) use ($product) {
                    return $rec['product_id'] == $product['id'];
                });

                if (empty($already_recommended)) {
                    $recommendations[] = [
                        'product_id' => $product['id'],
                        'name' => $product['nama_produk'],
                        'price' => $product['harga_jual'],
                        'reason' => 'Produk populer',
                        'confidence' => 50
                    ];
                }
            }
        }

        return array_slice($recommendations, 0, $limit);
    }

    /**
     * Get popular products for recommendations.
     */
    private function getPopularProducts($limit = 10) {
        return fetchAll("SELECT pr.id, pr.nama_produk, pr.harga_jual, COUNT(dp.jumlah) as total_sold FROM produk pr LEFT JOIN detail_penjualan dp ON pr.id = dp.produk_id WHERE pr.is_active = 1 GROUP BY pr.id ORDER BY total_sold DESC LIMIT ?", [$limit], 'i') ?? [];
    }

    /**
     * Get user purchase history.
     */
    private function getUserPurchaseHistory($user_id) {
        return fetchAll("SELECT pr.nama_produk, SUM(dp.jumlah) as quantity, SUM(dp.subtotal) as total_spent, MAX(p.tanggal_penjualan) as last_purchase FROM detail_penjualan dp LEFT JOIN penjualan p ON dp.penjualan_id = p.id LEFT JOIN produk pr ON dp.produk_id = pr.id WHERE p.pelanggan_id = ? GROUP BY dp.produk_id ORDER BY last_purchase DESC LIMIT 10", [$user_id], 'i') ?? [];
    }

    /**
     * Detect fraudulent transactions.
     */
    private function detectFraudulentTransactions() {
        $fraudulent_transactions = [];

        // Rule 1: Transactions with very high amounts
        $high_value_transactions = fetchAll("SELECT p.id, p.total_harga, u.full_name as customer_name, p.tanggal_penjualan FROM penjualan p LEFT JOIN users u ON p.pelanggan_id = u.id WHERE p.total_harga > 10000000 AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY p.total_harga DESC");

        foreach ($high_value_transactions as $transaction) {
            $fraudulent_transactions[] = [
                'transaction_id' => $transaction['id'],
                'type' => 'high_value',
                'amount' => $transaction['total_harga'],
                'customer' => $transaction['customer_name'],
                'date' => $transaction['tanggal_penjualan'],
                'risk_level' => 'medium',
                'reason' => 'Nilai transaksi sangat tinggi'
            ];
        }

        // Rule 2: Multiple transactions from same customer in short time
        $rapid_transactions = fetchAll("SELECT p.pelanggan_id, u.full_name, COUNT(*) as transaction_count, SUM(p.total_harga) as total_amount FROM penjualan p LEFT JOIN users u ON p.pelanggan_id = u.id WHERE p.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 HOUR) GROUP BY p.pelanggan_id HAVING transaction_count > 5");

        foreach ($rapid_transactions as $customer) {
            $fraudulent_transactions[] = [
                'customer_id' => $customer['pelanggan_id'],
                'type' => 'rapid_transactions',
                'customer' => $customer['full_name'],
                'transaction_count' => $customer['transaction_count'],
                'total_amount' => $customer['total_amount'],
                'risk_level' => 'high',
                'reason' => 'Banyak transaksi dalam waktu singkat'
            ];
        }

        // Rule 3: Unusual payment method patterns
        $unusual_payments = fetchAll("SELECT p.metode_pembayaran, COUNT(*) as count FROM penjualan p WHERE p.created_at >= DATE_SUB(CURDATE(), INTERVAL 24 HOUR) GROUP BY p.metode_pembayaran HAVING count > 20");

        foreach ($unusual_payments as $payment) {
            $fraudulent_transactions[] = [
                'type' => 'unusual_payment_pattern',
                'payment_method' => $payment['metode_pembayaran'],
                'count' => $payment['count'],
                'risk_level' => 'low',
                'reason' => 'Pola pembayaran tidak biasa'
            ];
        }

        return $fraudulent_transactions;
    }

    /**
     * Identify risky users based on behavior patterns.
     */
    private function identifyRiskyUsers() {
        $risky_users = [];

        // Users with high return rates
        $high_return_users = fetchAll("SELECT u.id, u.full_name, COUNT(r.id) as return_count, COUNT(p.id) as purchase_count FROM users u LEFT JOIN returns r ON u.id = r.customer_id LEFT JOIN penjualan p ON u.id = p.pelanggan_id WHERE u.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY u.id HAVING return_count > 0 AND (return_count / GREATEST(purchase_count, 1)) > 0.5");

        foreach ($high_return_users as $user) {
            $risky_users[] = [
                'user_id' => $user['id'],
                'name' => $user['full_name'],
                'return_count' => $user['return_count'],
                'purchase_count' => $user['purchase_count'],
                'return_rate' => round(($user['return_count'] / max($user['purchase_count'], 1)) * 100, 1),
                'risk_level' => 'medium',
                'reason' => 'Tingkat return yang tinggi'
            ];
        }

        // Users with failed payments
        $failed_payment_users = fetchAll("SELECT u.id, u.full_name, COUNT(*) as failed_count FROM users u LEFT JOIN penjualan p ON u.id = p.pelanggan_id WHERE p.status_pembayaran = 'gagal' AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY u.id HAVING failed_count >= 3");

        foreach ($failed_payment_users as $user) {
            $risky_users[] = [
                'user_id' => $user['id'],
                'name' => $user['full_name'],
                'failed_payments' => $user['failed_count'],
                'risk_level' => 'high',
                'reason' => 'Banyak pembayaran gagal'
            ];
        }

        return $risky_users;
    }

    /**
     * Detect unusual patterns in system usage.
     */
    private function detectUnusualPatterns() {
        $unusual_patterns = [];

        // Unusual login times
        $unusual_logins = fetchAll("SELECT u.full_name, COUNT(*) as login_count FROM users u LEFT JOIN logs l ON u.id = l.user_id WHERE l.action = 'login' AND HOUR(l.created_at) BETWEEN 2 AND 5 AND l.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY u.id HAVING login_count > 5");

        foreach ($unusual_logins as $user) {
            $unusual_patterns[] = [
                'user' => $user['full_name'],
                'pattern' => 'unusual_login_time',
                'count' => $user['login_count'],
                'risk_level' => 'low',
                'reason' => 'Login pada jam tidak biasa'
            ];
        }

        // High frequency data access
        $high_access_users = fetchAll("SELECT u.full_name, COUNT(*) as access_count FROM users u LEFT JOIN logs l ON u.id = l.user_id WHERE l.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 HOUR) GROUP BY u.id HAVING access_count > 100");

        foreach ($high_access_users as $user) {
            $unusual_patterns[] = [
                'user' => $user['full_name'],
                'pattern' => 'high_frequency_access',
                'count' => $user['access_count'],
                'risk_level' => 'medium',
                'reason' => 'Akses sistem yang sangat tinggi'
            ];
        }

        return $unusual_patterns;
    }

    /**
     * Store fraud alerts in database.
     */
    private function storeFraudAlerts($transactions, $users, $patterns) {
        runInTransaction(function($conn) use ($transactions, $users, $patterns) {
            $created_by = $_SESSION['user']['id'] ?? 1;

            // Store transaction alerts
            foreach ($transactions as $alert) {
                $stmt = $conn->prepare("INSERT INTO ai_fraud_alerts (alert_type, reference_id, risk_level, description, alert_data, created_by) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE risk_level = VALUES(risk_level), description = VALUES(description), alert_data = VALUES(alert_data)");
                $stmt->bind_param('sisssi', $alert['type'], $alert['transaction_id'] ?? $alert['customer_id'] ?? null, $alert['risk_level'], $alert['reason'], json_encode($alert), $created_by);
                $stmt->execute();
                $stmt->close();
            }

            // Store user risk alerts
            foreach ($users as $alert) {
                $stmt = $conn->prepare("INSERT INTO ai_fraud_alerts (alert_type, reference_id, risk_level, description, alert_data, created_by) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE risk_level = VALUES(risk_level), description = VALUES(description), alert_data = VALUES(alert_data)");
                $stmt->bind_param('sisssi', 'risky_user', $alert['user_id'], $alert['risk_level'], $alert['reason'], json_encode($alert), $created_by);
                $stmt->execute();
                $stmt->close();
            }

            // Store pattern alerts
            foreach ($patterns as $alert) {
                $stmt = $conn->prepare("INSERT INTO ai_fraud_alerts (alert_type, risk_level, description, alert_data, created_by) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE risk_level = VALUES(risk_level), description = VALUES(description), alert_data = VALUES(alert_data)");
                $stmt->bind_param('sssss', 'unusual_pattern', $alert['risk_level'], $alert['reason'], json_encode($alert), $created_by);
                $stmt->execute();
                $stmt->close();
            }
        });
    }

    /**
     * Get recommendation statistics.
     */
    private function getRecommendationStats() {
        $stats = [];

        $stats['total_recommendations_generated'] = (fetchRow("SELECT COUNT(*) as total FROM ai_recommendations WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)") ?? [])['total'] ?? 0;
        $stats['unique_users_recommended'] = (fetchRow("SELECT COUNT(DISTINCT user_id) as total FROM ai_recommendations WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)") ?? [])['total'] ?? 0;
        $stats['conversion_rate'] = $this->calculateRecommendationConversionRate();

        return $stats;
    }

    /**
     * Get fraud detection statistics.
     */
    private function getFraudDetectionStats() {
        $stats = [];

        $stats['total_alerts_today'] = (fetchRow("SELECT COUNT(*) as total FROM ai_fraud_alerts WHERE DATE(created_at) = CURDATE()") ?? [])['total'] ?? 0;
        $stats['high_risk_alerts'] = (fetchRow("SELECT COUNT(*) as total FROM ai_fraud_alerts WHERE risk_level = 'high' AND status = 'active'") ?? [])['total'] ?? 0;
        $stats['resolved_alerts'] = (fetchRow("SELECT COUNT(*) as total FROM ai_fraud_alerts WHERE status = 'resolved'") ?? [])['total'] ?? 0;

        return $stats;
    }

    /**
     * Get recent recommendations.
     */
    private function getRecentRecommendations() {
        return fetchAll("SELECT ar.*, u.full_name as user_name FROM ai_recommendations ar LEFT JOIN users u ON ar.user_id = u.id ORDER BY ar.created_at DESC LIMIT 10") ?? [];
    }

    /**
     * Calculate recommendation conversion rate.
     */
    private function calculateRecommendationConversionRate() {
        // This would require tracking which recommendations led to purchases
        // For now, return a placeholder
        return 15.5; // 15.5% conversion rate
    }
}
