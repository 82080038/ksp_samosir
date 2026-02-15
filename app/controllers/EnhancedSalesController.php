<?php
/**
 * Enhanced Sales System dengan AI Recommendations
 * Batch 5: Advanced Sales & Marketing
 */

class EnhancedSalesController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * AI Product Recommendations Engine
     */
    public function generateAIRecommendations($userId, $limit = 10)
    {
        try {
            $recommendations = [];
            
            // 1. Purchase History Based Recommendations
            $recommendations = array_merge($recommendations, $this->getPurchaseHistoryRecommendations($userId, $limit/4));
            
            // 2. Similar Products Recommendations
            $recommendations = array_merge($recommendations, $this->getSimilarProductRecommendations($userId, $limit/4));
            
            // 3. Trending Products Recommendations
            $recommendations = array_merge($recommendations, $this->getTrendingProductRecommendations($userId, $limit/4));
            
            // 4. Cross-sell Recommendations
            $recommendations = array_merge($recommendations, $this->getCrossSellRecommendations($userId, $limit/4));
            
            // Sort by confidence score and limit
            usort($recommendations, function($a, $b) {
                return $b['confidence_score'] <=> $a['confidence_score'];
            });
            
            return array_slice($recommendations, 0, $limit);
        } catch (Exception $e) {
            error_log("Error generating AI recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recommendations based on purchase history
     */
    private function getPurchaseHistoryRecommendations($userId, $limit)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p2.id as product_id,
                    p2.nama_produk,
                    p2.harga,
                    p2.gambar,
                    'purchase_history' as recommendation_type,
                    0.85 as confidence_score,
                    'Berdasarkan pembelian Anda sebelumnya' as reason_text
                FROM penjualan p
                JOIN detail_penjualan dp ON p.id = dp.id_penjualan
                JOIN produk p1 ON dp.id_produk = p1.id
                JOIN produk p2 ON p1.kategori_id = p2.kategori_id
                WHERE p.id_user = ? AND p.status = 'selesai'
                AND p2.id NOT IN (
                    SELECT dp2.id_produk FROM penjualan p2 
                    JOIN detail_penjualan dp2 ON p2.id = dp2.id_penjualan 
                    WHERE p2.id_user = ?
                )
                GROUP BY p2.id
                ORDER BY COUNT(dp.id) DESC, p2.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $userId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in purchase history recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get similar product recommendations
     */
    private function getSimilarProductRecommendations($userId, $limit)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p2.id as product_id,
                    p2.nama_produk,
                    p2.harga,
                    p2.gambar,
                    'similar_products' as recommendation_type,
                    ps.similarity_score as confidence_score,
                    CONCAT('Mirip dengan produk yang Anda lihat: ', p1.nama_produk) as reason_text
                FROM user_behavior_tracking ub
                JOIN produk p1 ON ub.product_id = p1.id
                JOIN product_similarity ps ON p1.id = ps.product_id_1
                JOIN produk p2 ON ps.product_id_2 = p2.id
                WHERE ub.user_id = ? AND ub.action_type = 'view'
                AND ub.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND p2.stok > 0
                ORDER BY ps.similarity_score DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in similar product recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get trending product recommendations
     */
    private function getTrendingProductRecommendations($userId, $limit)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.id as product_id,
                    p.nama_produk,
                    p.harga,
                    p.gambar,
                    'trending' as recommendation_type,
                    (COUNT(dp.id) * 0.1 + SUM(dp.jumlah) * 0.05) as confidence_score,
                    'Produk sedang populer minggu ini' as reason_text
                FROM penjualan pen
                JOIN detail_penjualan dp ON pen.id = dp.id_penjualan
                JOIN produk p ON dp.id_produk = p.id
                WHERE pen.status = 'selesai'
                AND pen.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND p.stok > 0
                AND p.id NOT IN (
                    SELECT dp2.id_produk FROM penjualan p2 
                    JOIN detail_penjualan dp2 ON p2.id = dp2.id_penjualan 
                    WHERE p2.id_user = ? AND p2.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                )
                GROUP BY p.id
                ORDER BY confidence_score DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in trending product recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get cross-sell recommendations
     */
    private function getCrossSellRecommendations($userId, $limit)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p2.id as product_id,
                    p2.nama_produk,
                    p2.harga,
                    p2.gambar,
                    'cross_sell' as recommendation_type,
                    0.75 as confidence_score,
                    'Sering dibeli bersama produk yang Anda lihat' as reason_text
                FROM penjualan p
                JOIN detail_penjualan dp1 ON p.id = dp1.id_penjualan
                JOIN detail_penjualan dp2 ON p.id = dp2.id_penjualan
                JOIN produk p1 ON dp1.id_produk = p1.id
                JOIN produk p2 ON dp2.id_produk = p2.id
                WHERE p.id_user = ? AND p.status = 'selesai'
                AND dp1.id_produk != dp2.id_produk
                AND p2.stok > 0
                AND p2.id NOT IN (
                    SELECT dp3.id_produk FROM penjualan p3 
                    JOIN detail_penjualan dp3 ON p3.id = dp3.id_penjualan 
                    WHERE p3.id_user = ?
                )
                GROUP BY p2.id
                ORDER BY COUNT(p.id) DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $userId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in cross-sell recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save AI recommendations to database
     */
    public function saveRecommendations($userId, $recommendations)
    {
        try {
            foreach ($recommendations as $rec) {
                $stmt = $this->db->prepare("
                    INSERT IGNORE INTO ai_recommendations 
                    (user_id, product_id, recommendation_type, confidence_score, reason_text, expires_at)
                    VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))
                ");
                $stmt->execute([
                    $userId,
                    $rec['product_id'],
                    $rec['recommendation_type'],
                    $rec['confidence_score'],
                    $rec['reason_text']
                ]);
            }
            return ['success' => true, 'message' => 'Recommendations saved successfully'];
        } catch (Exception $e) {
            error_log("Error saving recommendations: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to save recommendations'];
        }
    }
    
    /**
     * Track user behavior for AI learning
     */
    public function trackUserBehavior($userId, $actionType, $productId = null, $searchQuery = null)
    {
        try {
            $sessionId = session_id();
            $pageUrl = $_SERVER['REQUEST_URI'] ?? '';
            $referrer = $_SERVER['HTTP_REFERER'] ?? '';
            $deviceType = $this->detectDeviceType();
            
            $stmt = $this->db->prepare("
                INSERT INTO user_behavior_tracking 
                (user_id, session_id, action_type, product_id, search_query, page_url, referrer, device_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $sessionId,
                $actionType,
                $productId,
                $searchQuery,
                $pageUrl,
                $referrer,
                $deviceType
            ]);
            
            return ['success' => true];
        } catch (Exception $e) {
            error_log("Error tracking user behavior: " . $e->getMessage());
            return ['success' => false];
        }
    }
    
    /**
     * Calculate product similarity matrix
     */
    public function calculateProductSimilarity($productId1, $productId2)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p1.kategori_id,
                    p1.harga,
                    p2.kategori_id,
                    p2.harga,
                    ABS(p1.harga - p2.harga) as price_diff
                FROM produk p1, produk p2
                WHERE p1.id = ? AND p2.id = ?
            ");
            $stmt->execute([$productId1, $productId2]);
            $products = $stmt->fetch();
            
            if (!$products) return 0;
            
            $similarity = 0;
            
            // Category similarity (40% weight)
            if ($products['kategori_id'] == $products['kategori_id']) {
                $similarity += 0.4;
            }
            
            // Price similarity (30% weight)
            $avgPrice = ($products['harga'] + $products['harga']) / 2;
            $priceDiffPercent = $products['price_diff'] / $avgPrice;
            $priceSimilarity = max(0, 1 - $priceDiffPercent);
            $similarity += $priceSimilarity * 0.3;
            
            // Purchase pattern similarity (30% weight)
            $patternSimilarity = $this->calculatePurchasePatternSimilarity($productId1, $productId2);
            $similarity += $patternSimilarity * 0.3;
            
            return round($similarity, 4);
        } catch (Exception $e) {
            error_log("Error calculating product similarity: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calculate purchase pattern similarity
     */
    private function calculatePurchasePatternSimilarity($productId1, $productId2)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(CASE WHEN dp.id_produk = ? THEN 1 END) as count1,
                    COUNT(CASE WHEN dp.id_produk = ? THEN 1 END) as count2,
                    COUNT(DISTINCT p.id_user) as total_users
                FROM penjualan p
                JOIN detail_penjualan dp ON p.id = dp.id_penjualan
                WHERE p.status = 'selesai'
                AND dp.id_produk IN (?, ?)
            ");
            $stmt->execute([$productId1, $productId2, $productId1, $productId2]);
            $result = $stmt->fetch();
            
            if ($result['total_users'] == 0) return 0;
            
            // Jaccard similarity
            $intersection = min($result['count1'], $result['count2']);
            $union = max($result['count1'], $result['count2']);
            
            return $union > 0 ? $intersection / $union : 0;
        } catch (Exception $e) {
            error_log("Error calculating purchase pattern similarity: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Detect device type
     */
    private function detectDeviceType()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return preg_match('/iPad/', $userAgent) ? 'tablet' : 'mobile';
        }
        
        return 'desktop';
    }
    
    /**
     * Get recommendation performance analytics
     */
    public function getRecommendationAnalytics($startDate = null, $endDate = null)
    {
        try {
            $startDate = $startDate ?? date('Y-m-01');
            $endDate = $endDate ?? date('Y-m-t');
            
            $stmt = $this->db->prepare("
                SELECT 
                    recommendation_type,
                    COUNT(*) as total_recommendations,
                    SUM(CASE WHEN is_clicked = 1 THEN 1 ELSE 0 END) as total_clicks,
                    SUM(CASE WHEN is_purchased = 1 THEN 1 ELSE 0 END) as total_purchases,
                    AVG(confidence_score) as avg_confidence,
                    ROUND(SUM(CASE WHEN is_clicked = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as click_rate,
                    ROUND(SUM(CASE WHEN is_purchased = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as conversion_rate
                FROM ai_recommendations
                WHERE created_at BETWEEN ? AND ?
                GROUP BY recommendation_type
                ORDER BY conversion_rate DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting recommendation analytics: " . $e->getMessage());
            return [];
        }
    }
}
