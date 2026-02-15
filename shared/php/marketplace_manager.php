<?php
/**
 * Marketplace Platform Manager for KSP Samosir
 * Internal marketplace, B2B integration, digital products, and loyalty programs
 */

class MarketplaceManager {
    private $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
    }

    /**
     * Get marketplace dashboard data
     */
    public function getDashboard() {
        return [
            'stats' => $this->getMarketplaceStats(),
            'featured_products' => $this->getFeaturedProducts(),
            'recent_transactions' => $this->getRecentTransactions(),
            'top_sellers' => $this->getTopSellers(),
            'category_performance' => $this->getCategoryPerformance(),
            'loyalty_stats' => $this->getLoyaltyProgramStats()
        ];
    }

    /**
     * List marketplace products with filters
     */
    public function listProducts($filters = [], $page = 1, $perPage = 20) {
        $where = ['mp.status = "active"'];
        $params = [];
        $joins = '';

        // Apply filters
        if (!empty($filters['category_id'])) {
            $where[] = 'mp.category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['seller_type'])) {
            $where[] = 'mp.seller_type = ?';
            $params[] = $filters['seller_type'];
        }

        if (!empty($filters['price_min'])) {
            $where[] = 'mp.price >= ?';
            $params[] = $filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $where[] = 'mp.price <= ?';
            $params[] = $filters['price_max'];
        }

        if (!empty($filters['condition'])) {
            $where[] = 'mp.condition_status = ?';
            $params[] = $filters['condition'];
        }

        if (!empty($filters['location'])) {
            $where[] = 'mp.location LIKE ?';
            $params[] = '%' . $filters['location'] . '%';
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $where[] = '(mp.title LIKE ? OR mp.description LIKE ? OR JSON_SEARCH(mp.tags, "one", ?) IS NOT NULL)';
            $params = array_merge($params, [$searchTerm, $searchTerm, $filters['search']]);
        }

        // Sorting
        $sortBy = $filters['sort'] ?? 'created_at';
        $sortOrder = $filters['order'] ?? 'DESC';
        $allowedSorts = ['price', 'created_at', 'view_count', 'favorite_count'];

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        // Get products
        $products = fetchAll("
            SELECT
                mp.*,
                mc.name as category_name,
                CASE
                    WHEN mp.seller_type = 'member' THEN CONCAT('Member: ', a.nama_lengkap)
                    WHEN mp.seller_type = 'cooperative' THEN 'KSP Samosir'
                    WHEN mp.seller_type = 'vendor' THEN v.vendor_name
                END as seller_name,
                COALESCE(AVG(mr.rating), 0) as average_rating,
                COUNT(mr.id) as review_count
            FROM marketplace_products mp
            LEFT JOIN marketplace_categories mc ON mp.category_id = mc.id
            LEFT JOIN anggota a ON mp.seller_type = 'member' AND mp.seller_id = a.id
            LEFT JOIN vendors v ON mp.seller_type = 'vendor' AND mp.seller_id = v.id
            LEFT JOIN marketplace_reviews mr ON mp.id = mr.product_id AND mr.status = 'active'
            WHERE {$whereClause}
            GROUP BY mp.id
            ORDER BY mp.{$sortBy} {$sortOrder}
            LIMIT ? OFFSET ?
        ", array_merge($params, [$perPage, $offset]), str_repeat('s', count($params)) . 'ii');

        // Get total count for pagination
        $totalCount = (fetchRow("
            SELECT COUNT(*) as total
            FROM marketplace_products mp
            WHERE {$whereClause}
        ", $params, str_repeat('s', count($params))) ?? [])['total'] ?? 0;

        return [
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'total_pages' => ceil($totalCount / $perPage)
            ],
            'filters' => $filters
        ];
    }

    /**
     * Create new marketplace product
     */
    public function createProduct($productData) {
        // Validate product data
        $validation = $this->validateProductData($productData);
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        // Check if user can sell (member/cooperative/vendor status)
        if (!$this->canUserSell($productData['seller_id'], $productData['seller_type'])) {
            return ['success' => false, 'error' => 'User not authorized to sell products'];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO marketplace_products
            (product_id, seller_id, seller_type, title, description, category_id,
             price, original_price, quantity_available, condition_status, location,
             images, tags, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");

        $stmt->execute([
            $productData['product_id'] ?? null,
            $productData['seller_id'],
            $productData['seller_type'],
            $productData['title'],
            $productData['description'] ?? '',
            $productData['category_id'],
            $productData['price'],
            $productData['original_price'] ?? $productData['price'],
            $productData['quantity_available'],
            $productData['condition_status'] ?? 'new',
            $productData['location'] ?? '',
            json_encode($productData['images'] ?? []),
            json_encode($productData['tags'] ?? [])
        ]);

        $productId = $this->pdo->lastInsertId();

        // Award loyalty points for listing product
        if ($productData['seller_type'] === 'member') {
            $this->awardLoyaltyPoints($productData['seller_id'], 'product_listed', 10);
        }

        return ['success' => true, 'product_id' => $productId];
    }

    /**
     * Process marketplace purchase
     */
    public function processPurchase($buyerId, $cartItems, $paymentData) {
        $this->pdo->beginTransaction();

        try {
            $totalAmount = 0;
            $platformFee = 0;

            // Calculate totals and validate stock
            foreach ($cartItems as $item) {
                $product = $this->getProduct($item['product_id']);
                if (!$product || $product['quantity_available'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for product: {$product['title']}");
                }

                $itemTotal = $product['price'] * $item['quantity'];
                $totalAmount += $itemTotal;
                $platformFee += $itemTotal * 0.02; // 2% platform fee
            }

            // Generate transaction number
            $transactionNumber = 'MP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Create marketplace transaction
            $stmt = $this->pdo->prepare("
                INSERT INTO marketplace_transactions
                (transaction_number, buyer_id, seller_id, product_id, quantity,
                 unit_price, total_amount, platform_fee, payment_method, shipping_address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $transactionItems = [];
            foreach ($cartItems as $item) {
                $product = $this->getProduct($item['product_id']);
                $stmt->execute([
                    $transactionNumber . '-' . count($transactionItems),
                    $buyerId,
                    $product['seller_id'],
                    $item['product_id'],
                    $item['quantity'],
                    $product['price'],
                    $product['price'] * $item['quantity'],
                    ($product['price'] * $item['quantity']) * 0.02,
                    $paymentData['method'],
                    json_encode($paymentData['shipping_address'])
                ]);

                $transactionItems[] = $this->pdo->lastInsertId();

                // Update product stock
                $this->updateProductStock($item['product_id'], -$item['quantity']);
            }

            // Clear buyer's cart
            $this->clearUserCart($buyerId);

            // Award loyalty points
            $loyaltyPoints = floor($totalAmount / 10000); // 1 point per Rp 10,000
            $this->awardLoyaltyPoints($buyerId, 'marketplace_purchase', $loyaltyPoints);

            // Process payment (would integrate with payment gateway)
            $paymentResult = $this->processPayment($totalAmount + $platformFee, $paymentData);

            if (!$paymentResult['success']) {
                throw new Exception("Payment failed: " . $paymentResult['error']);
            }

            $this->pdo->commit();

            return [
                'success' => true,
                'transaction_number' => $transactionNumber,
                'total_amount' => $totalAmount,
                'platform_fee' => $platformFee,
                'loyalty_points_earned' => $loyaltyPoints
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Loyalty program management
     */
    public function getLoyaltyStatus($memberId) {
        $loyalty = fetchRow("
            SELECT * FROM loyalty_program
            WHERE member_id = ? AND status = 'active'
        ", [$memberId], 'i');

        if (!$loyalty) {
            // Create loyalty account if doesn't exist
            $stmt = $this->pdo->prepare("
                INSERT INTO loyalty_program (member_id)
                VALUES (?)
            ");
            $stmt->execute([$memberId]);
            $loyaltyId = $this->pdo->lastInsertId();

            $loyalty = fetchRow("SELECT * FROM loyalty_program WHERE id = ?", [$loyaltyId], 'i');
        }

        // Get recent transactions
        $recentTransactions = fetchAll("
            SELECT * FROM loyalty_transactions
            WHERE member_id = ?
            ORDER BY processed_at DESC
            LIMIT 10
        ", [$memberId], 'i');

        return array_merge($loyalty, ['recent_transactions' => $recentTransactions]);
    }

    /**
     * Redeem loyalty reward
     */
    public function redeemReward($memberId, $rewardId) {
        $loyalty = $this->getLoyaltyStatus($memberId);
        $reward = fetchRow("
            SELECT * FROM loyalty_rewards
            WHERE id = ? AND active = TRUE
        ", [$rewardId], 'i');

        if (!$reward) {
            return ['success' => false, 'error' => 'Reward not found'];
        }

        if ($loyalty['current_points'] < $reward['points_required']) {
            return ['success' => false, 'error' => 'Insufficient loyalty points'];
        }

        if ($reward['quantity_available'] !== null && $reward['quantity_available'] <= 0) {
            return ['success' => false, 'error' => 'Reward out of stock'];
        }

        $this->pdo->beginTransaction();

        try {
            // Deduct points
            $this->deductLoyaltyPoints($memberId, $reward['points_required'], 'Reward redemption: ' . $reward['reward_name']);

            // Update reward quantity if limited
            if ($reward['quantity_available'] !== null) {
                $stmt = $this->pdo->prepare("
                    UPDATE loyalty_rewards
                    SET quantity_available = quantity_available - 1
                    WHERE id = ?
                ");
                $stmt->execute([$rewardId]);
            }

            // Create redemption record
            $stmt = $this->pdo->prepare("
                INSERT INTO loyalty_transactions
                (member_id, transaction_type, points, reason, reference_type, reference_id)
                VALUES (?, 'redeemed', ?, ?, 'reward', ?)
            ");
            $stmt->execute([$memberId, $reward['points_required'], 'Reward redemption: ' . $reward['reward_name'], $rewardId]);

            $this->pdo->commit();

            return [
                'success' => true,
                'reward' => $reward,
                'points_deducted' => $reward['points_required']
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Add product review
     */
    public function addProductReview($transactionId, $reviewData) {
        // Verify transaction ownership
        $transaction = fetchRow("
            SELECT * FROM marketplace_transactions
            WHERE id = ? AND buyer_id = ?
        ", [$transactionId, $_SESSION['user']['id']], 'ii');

        if (!$transaction) {
            return ['success' => false, 'error' => 'Transaction not found or not authorized'];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO marketplace_reviews
            (transaction_id, reviewer_id, product_id, seller_id, rating, title, comment, verified_purchase)
            VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)
        ");

        $stmt->execute([
            $transactionId,
            $_SESSION['user']['id'],
            $transaction['product_id'],
            $transaction['seller_id'],
            $reviewData['rating'],
            $reviewData['title'] ?? '',
            $reviewData['comment'] ?? ''
        ]);

        return ['success' => true, 'review_id' => $this->pdo->lastInsertId()];
    }

    /**
     * Get product recommendations for user
     */
    public function getProductRecommendations($userId, $limit = 10) {
        // Get user's purchase history and preferences
        $userHistory = fetchAll("
            SELECT DISTINCT product_id, category_id
            FROM marketplace_transactions mt
            JOIN marketplace_products mp ON mt.product_id = mp.id
            WHERE mt.buyer_id = ?
            ORDER BY mt.created_at DESC
            LIMIT 20
        ", [$userId], 'i');

        if (empty($userHistory)) {
            // Return featured/popular products for new users
            return fetchAll("
                SELECT mp.*, mc.name as category_name
                FROM marketplace_products mp
                LEFT JOIN marketplace_categories mc ON mp.category_id = mc.id
                WHERE mp.featured = TRUE AND mp.status = 'active'
                ORDER BY mp.created_at DESC
                LIMIT ?
            ", [$limit], 'i') ?? [];
        }

        // Get categories user has purchased from
        $categories = array_unique(array_column($userHistory, 'category_id'));

        // Recommend products from same categories, excluding already purchased
        $placeholders = str_repeat('?,', count($categories) + count($userHistory)) . '?';
        $params = array_merge($categories, array_column($userHistory, 'product_id'), [$limit]);

        return fetchAll("
            SELECT DISTINCT mp.*, mc.name as category_name
            FROM marketplace_products mp
            LEFT JOIN marketplace_categories mc ON mp.category_id = mc.id
            WHERE mp.category_id IN (" . str_repeat('?,', count($categories) - 1) . "?)
            AND mp.id NOT IN (" . str_repeat('?,', count($userHistory) - 1) . "?)
            AND mp.status = 'active'
            ORDER BY mp.view_count DESC, mp.created_at DESC
            LIMIT ?
        ", $params, str_repeat('i', count($params))) ?? [];
    }

    // Private helper methods
    private function getMarketplaceStats() {
        return [
            'total_products' => (fetchRow("SELECT COUNT(*) as count FROM marketplace_products WHERE status = 'active'") ?? [])['count'] ?? 0,
            'total_transactions' => (fetchRow("SELECT COUNT(*) as count FROM marketplace_transactions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)") ?? [])['count'] ?? 0,
            'total_revenue' => (fetchRow("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM marketplace_transactions WHERE payment_status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)") ?? [])['revenue'] ?? 0,
            'active_sellers' => (fetchRow("SELECT COUNT(DISTINCT seller_id) as sellers FROM marketplace_products WHERE status = 'active' AND seller_type = 'member'") ?? [])['sellers'] ?? 0,
            'average_rating' => (fetchRow("SELECT COALESCE(AVG(rating), 0) as avg_rating FROM marketplace_reviews WHERE status = 'active'") ?? [])['avg_rating'] ?? null
        ];
    }

    private function getFeaturedProducts() {
        return fetchAll("
            SELECT mp.*, mc.name as category_name
            FROM marketplace_products mp
            LEFT JOIN marketplace_categories mc ON mp.category_id = mc.id
            WHERE mp.featured = TRUE AND mp.status = 'active'
            ORDER BY mp.created_at DESC
            LIMIT 8
        ", [], '') ?? [];
    }

    private function getRecentTransactions() {
        return fetchAll("
            SELECT mt.*, mp.title as product_title, a.nama_lengkap as buyer_name
            FROM marketplace_transactions mt
            JOIN marketplace_products mp ON mt.product_id = mp.id
            JOIN anggota a ON mt.buyer_id = a.id
            ORDER BY mt.created_at DESC
            LIMIT 10
        ", [], '') ?? [];
    }

    private function getTopSellers() {
        return fetchAll("
            SELECT
                mp.seller_id,
                mp.seller_type,
                CASE
                    WHEN mp.seller_type = 'member' THEN a.nama_lengkap
                    WHEN mp.seller_type = 'vendor' THEN v.vendor_name
                    ELSE 'KSP Samosir'
                END as seller_name,
                COUNT(mt.id) as total_sales,
                COALESCE(SUM(mt.total_amount), 0) as total_revenue
            FROM marketplace_products mp
            LEFT JOIN marketplace_transactions mt ON mp.id = mt.product_id AND mt.status = 'delivered'
            LEFT JOIN anggota a ON mp.seller_type = 'member' AND mp.seller_id = a.id
            LEFT JOIN vendors v ON mp.seller_type = 'vendor' AND mp.seller_id = v.id
            GROUP BY mp.seller_id, mp.seller_type, seller_name
            ORDER BY total_revenue DESC
            LIMIT 10
        ", [], '') ?? [];
    }

    private function getCategoryPerformance() {
        return fetchAll("
            SELECT
                mc.name as category_name,
                COUNT(mp.id) as product_count,
                COUNT(mt.id) as transaction_count,
                COALESCE(SUM(mt.total_amount), 0) as revenue
            FROM marketplace_categories mc
            LEFT JOIN marketplace_products mp ON mc.id = mp.category_id AND mp.status = 'active'
            LEFT JOIN marketplace_transactions mt ON mp.id = mt.product_id AND mt.status = 'delivered'
            GROUP BY mc.id, mc.name
            ORDER BY revenue DESC
        ", [], '') ?? [];
    }

    private function getLoyaltyProgramStats() {
        return [
            'total_members' => (fetchRow("SELECT COUNT(*) as count FROM loyalty_program WHERE status = 'active'") ?? [])['count'] ?? 0,
            'total_points_issued' => (fetchRow("SELECT COALESCE(SUM(points), 0) as points FROM loyalty_transactions WHERE transaction_type = 'earned'") ?? [])['points'] ?? 0,
            'total_points_redeemed' => (fetchRow("SELECT COALESCE(SUM(points), 0) as points FROM loyalty_transactions WHERE transaction_type = 'redeemed'") ?? [])['points'] ?? 0,
            'redemption_rate' => 0 // Calculate based on issued vs redeemed
        ];
    }

    private function validateProductData($data) {
        $errors = [];

        if (empty($data['title'])) $errors[] = 'Product title is required';
        if (empty($data['price']) || $data['price'] <= 0) $errors[] = 'Valid price is required';
        if (empty($data['quantity_available']) || $data['quantity_available'] < 0) $errors[] = 'Valid quantity is required';
        if (empty($data['category_id'])) $errors[] = 'Category is required';

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function canUserSell($userId, $userType) {
        if ($userType === 'member') {
            $member = fetchRow("SELECT status FROM anggota WHERE id = ?", [$userId], 'i');
            return $member && $member['status'] === 'aktif';
        } elseif ($userType === 'vendor') {
            $vendor = fetchRow("SELECT status FROM vendors WHERE id = ?", [$userId], 'i');
            return $vendor && $vendor['status'] === 'active';
        } elseif ($userType === 'cooperative') {
            return true; // Cooperative can always sell
        }

        return false;
    }

    private function getProduct($productId) {
        return fetchRow("SELECT * FROM marketplace_products WHERE id = ?", [$productId], 'i');
    }

    private function updateProductStock($productId, $quantityChange) {
        $stmt = $this->pdo->prepare("
            UPDATE marketplace_products
            SET quantity_available = quantity_available + ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$quantityChange, $productId]);
    }

    private function clearUserCart($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM marketplace_cart WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    private function processPayment($amount, $paymentData) {
        // Placeholder for payment gateway integration
        // Would integrate with Midtrans, GoPay, etc.
        return ['success' => true, 'transaction_id' => 'PAY-' . time()];
    }

    private function awardLoyaltyPoints($memberId, $reason, $points) {
        // Ensure loyalty account exists
        $loyalty = fetchRow("SELECT id FROM loyalty_program WHERE member_id = ?", [$memberId], 'i');
        if (!$loyalty) {
            $stmt = $this->pdo->prepare("INSERT INTO loyalty_program (member_id) VALUES (?)");
            $stmt->execute([$memberId]);
        }

        // Award points
        $stmt = $this->pdo->prepare("
            UPDATE loyalty_program
            SET current_points = current_points + ?,
                total_points_earned = total_points_earned + ?,
                updated_at = NOW()
            WHERE member_id = ?
        ");
        $stmt->execute([$points, $points, $memberId]);

        // Record transaction
        $stmt = $this->pdo->prepare("
            INSERT INTO loyalty_transactions
            (member_id, transaction_type, points, reason)
            VALUES (?, 'earned', ?, ?)
        ");
        $stmt->execute([$memberId, $points, $reason]);
    }

    private function deductLoyaltyPoints($memberId, $points, $reason) {
        $stmt = $this->pdo->prepare("
            UPDATE loyalty_program
            SET current_points = current_points - ?,
                total_points_redeemed = total_points_redeemed + ?,
                updated_at = NOW()
            WHERE member_id = ? AND current_points >= ?
        ");
        $stmt->execute([$points, $points, $memberId, $points]);
    }
}

// Helper functions
function getMarketplaceDashboard() {
    $marketplace = new MarketplaceManager();
    return $marketplace->getDashboard();
}

function listMarketplaceProducts($filters = [], $page = 1, $perPage = 20) {
    $marketplace = new MarketplaceManager();
    return $marketplace->listProducts($filters, $page, $perPage);
}

function createMarketplaceProduct($productData) {
    $marketplace = new MarketplaceManager();
    return $marketplace->createProduct($productData);
}

function processMarketplacePurchase($buyerId, $cartItems, $paymentData) {
    $marketplace = new MarketplaceManager();
    return $marketplace->processPurchase($buyerId, $cartItems, $paymentData);
}

function getLoyaltyStatus($memberId) {
    $marketplace = new MarketplaceManager();
    return $marketplace->getLoyaltyStatus($memberId);
}

function redeemLoyaltyReward($memberId, $rewardId) {
    $marketplace = new MarketplaceManager();
    return $marketplace->redeemReward($memberId, $rewardId);
}

function addProductReview($transactionId, $reviewData) {
    $marketplace = new MarketplaceManager();
    return $marketplace->addProductReview($transactionId, $reviewData);
}

function getProductRecommendations($userId, $limit = 10) {
    $marketplace = new MarketplaceManager();
    return $marketplace->getProductRecommendations($userId, $limit);
}
