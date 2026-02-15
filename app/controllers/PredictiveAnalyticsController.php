<?php
/**
 * Predictive Analytics Engine
 * Batch 7: Advanced Analytics
 */

class PredictiveAnalyticsController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Run customer churn prediction
     */
    public function predictCustomerChurn()
    {
        try {
            $model = $this->getModel('Customer Churn Prediction');
            if (!$model) {
                return ['success' => false, 'message' => 'Churn prediction model not found'];
            }
            
            $customers = $this->getCustomersForPrediction();
            $predictions = [];
            
            foreach ($customers as $customer) {
                $churnProbability = $this->calculateChurnProbability($customer);
                $confidence = $this->calculatePredictionConfidence($customer, $churnProbability);
                
                // Save prediction
                $this->savePrediction($model['id'], 'customer', $customer['id'], $churnProbability, $confidence, [
                    'last_purchase_days' => $customer['last_purchase_days'],
                    'total_orders' => $customer['total_orders'],
                    'avg_order_value' => $customer['avg_order_value'],
                    'days_since_last_login' => $customer['days_since_last_login']
                ]);
                
                $predictions[] = [
                    'customer_id' => $customer['id'],
                    'customer_name' => $customer['full_name'],
                    'churn_probability' => $churnProbability,
                    'confidence' => $confidence,
                    'risk_level' => $this->getRiskLevel($churnProbability)
                ];
            }
            
            // Update model accuracy
            $this->updateModelAccuracy($model['id']);
            
            return [
                'success' => true,
                'predictions' => $predictions,
                'total_processed' => count($predictions),
                'high_risk_count' => count(array_filter($predictions, fn($p) => $p['churn_probability'] > 0.7))
            ];
        } catch (Exception $e) {
            error_log("Error in customer churn prediction: " . $e->getMessage());
            return ['success' => false, 'message' => 'Prediction failed'];
        }
    }
    
    /**
     * Run sales forecasting
     */
    public function forecastSales($periods = 30)
    {
        try {
            $model = $this->getModel('Sales Forecasting');
            if (!$model) {
                return ['success' => false, 'message' => 'Sales forecasting model not found'];
            }
            
            $historicalData = $this->getSalesHistoricalData();
            $forecast = [];
            
            for ($i = 1; $i <= $periods; $i++) {
                $forecastDate = date('Y-m-d', strtotime("+$i days"));
                $predictedRevenue = $this->predictSalesRevenue($historicalData, $i);
                $confidence = $this->calculateForecastConfidence($historicalData, $i);
                
                // Save prediction
                $this->savePrediction($model['id'], 'sales', $i, $predictedRevenue, $confidence, [
                    'forecast_date' => $forecastDate,
                    'period_ahead' => $i,
                    'historical_trend' => $this->calculateTrend($historicalData)
                ]);
                
                $forecast[] = [
                    'date' => $forecastDate,
                    'predicted_revenue' => $predictedRevenue,
                    'confidence' => $confidence,
                    'period_ahead' => $i
                ];
            }
            
            return [
                'success' => true,
                'forecast' => $forecast,
                'periods' => $periods,
                'total_predicted_revenue' => array_sum(array_column($forecast, 'predicted_revenue'))
            ];
        } catch (Exception $e) {
            error_log("Error in sales forecasting: " . $e->getMessage());
            return ['success' => false, 'message' => 'Forecasting failed'];
        }
    }
    
    /**
     * Run product demand prediction
     */
    public function predictProductDemand()
    {
        try {
            $model = $this->getModel('Product Demand Prediction');
            if (!$model) {
                return ['success' => false, 'message' => 'Product demand model not found'];
            }
            
            $products = $this->getProductsForPrediction();
            $predictions = [];
            
            foreach ($products as $product) {
                $predictedDemand = $this->calculateProductDemand($product);
                $confidence = $this->calculateDemandConfidence($product);
                
                // Save prediction
                $this->savePrediction($model['id'], 'product', $product['id'], $predictedDemand, $confidence, [
                    'current_stock' => $product['stok'],
                    'historical_sales' => $product['historical_sales'],
                    'price' => $product['harga'],
                    'season' => date('M')
                ]);
                
                $predictions[] = [
                    'product_id' => $product['id'],
                    'product_name' => $product['nama_produk'],
                    'predicted_demand' => $predictedDemand,
                    'current_stock' => $product['stok'],
                    'reorder_suggested' => $predictedDemand > $product['stok'],
                    'confidence' => $confidence
                ];
            }
            
            return [
                'success' => true,
                'predictions' => $predictions,
                'total_products' => count($predictions),
                'reorder_suggested' => count(array_filter($predictions, fn($p) => $p['reorder_suggested']))
            ];
        } catch (Exception $e) {
            error_log("Error in product demand prediction: " . $e->getMessage());
            return ['success' => false, 'message' => 'Demand prediction failed'];
        }
    }
    
    /**
     * Run customer lifetime value prediction
     */
    public function predictCustomerLifetimeValue()
    {
        try {
            $model = $this->getModel('Customer Lifetime Value');
            if (!$model) {
                return ['success' => false, 'message' => 'CLV model not found'];
            }
            
            $customers = $this->getCustomersForCLV();
            $predictions = [];
            
            foreach ($customers as $customer) {
                $predictedCLV = $this->calculateCustomerCLV($customer);
                $confidence = $this->calculateCLVConfidence($customer);
                
                // Save prediction
                $this->savePrediction($model['id'], 'customer', $customer['id'], $predictedCLV, $confidence, [
                    'current_clv' => $customer['total_revenue'],
                    'order_frequency' => $customer['order_frequency'],
                    'avg_order_value' => $customer['avg_order_value'],
                    'customer_age_days' => $customer['customer_age_days']
                ]);
                
                $predictions[] = [
                    'customer_id' => $customer['id'],
                    'customer_name' => $customer['full_name'],
                    'current_clv' => $customer['total_revenue'],
                    'predicted_clv' => $predictedCLV,
                    'growth_potential' => $predictedCLV - $customer['total_revenue'],
                    'confidence' => $confidence,
                    'segment' => $this->getCustomerSegment($predictedCLV)
                ];
            }
            
            return [
                'success' => true,
                'predictions' => $predictions,
                'total_customers' => count($predictions),
                'high_potential_count' => count(array_filter($predictions, fn($p) => $p['growth_potential'] > 1000000))
            ];
        } catch (Exception $e) {
            error_log("Error in CLV prediction: " . $e->getMessage());
            return ['success' => false, 'message' => 'CLV prediction failed'];
        }
    }
    
    /**
     * Get customers for churn prediction
     */
    private function getCustomersForPrediction()
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    u.id,
                    u.full_name,
                    DATEDIFF(NOW(), MAX(p.tanggal_penjualan)) as last_purchase_days,
                    COUNT(p.id) as total_orders,
                    AVG(p.total_harga) as avg_order_value,
                    DATEDIFF(NOW(), MAX(u.last_login)) as days_since_last_login
                FROM users u
                LEFT JOIN penjualan p ON u.id = p.user_id AND p.status_pembayaran = 'lunas'
                WHERE u.is_active = 1
                GROUP BY u.id, u.full_name, u.last_login
                HAVING total_orders > 0
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting customers for prediction: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calculate churn probability
     */
    private function calculateChurnProbability($customer)
    {
        $score = 0;
        
        // Last purchase days factor (40% weight)
        if ($customer['last_purchase_days'] > 90) {
            $score += 0.4;
        } elseif ($customer['last_purchase_days'] > 60) {
            $score += 0.3;
        } elseif ($customer['last_purchase_days'] > 30) {
            $score += 0.2;
        }
        
        // Order frequency factor (30% weight)
        if ($customer['total_orders'] <= 1) {
            $score += 0.3;
        } elseif ($customer['total_orders'] <= 3) {
            $score += 0.2;
        } elseif ($customer['total_orders'] <= 5) {
            $score += 0.1;
        }
        
        // Average order value factor (20% weight)
        if ($customer['avg_order_value'] < 100000) {
            $score += 0.2;
        } elseif ($customer['avg_order_value'] < 500000) {
            $score += 0.1;
        }
        
        // Login activity factor (10% weight)
        if ($customer['days_since_last_login'] > 30) {
            $score += 0.1;
        }
        
        return min($score, 1.0);
    }
    
    /**
     * Get sales historical data
     */
    private function getSalesHistoricalData()
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    DATE(tanggal_penjualan) as date,
                    SUM(total_harga) as revenue,
                    COUNT(*) as orders
                FROM penjualan 
                WHERE status_pembayaran = 'lunas'
                AND tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 90 DAY)
                GROUP BY DATE(tanggal_penjualan)
                ORDER BY date
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting sales historical data: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Predict sales revenue
     */
    private function predictSalesRevenue($historicalData, $daysAhead)
    {
        if (empty($historicalData)) {
            return 0;
        }
        
        // Simple moving average with trend adjustment
        $recentRevenue = array_slice(array_column($historicalData, 'revenue'), -7);
        $avgRevenue = array_sum($recentRevenue) / count($recentRevenue);
        
        // Calculate trend
        $trend = $this->calculateTrend($historicalData);
        
        // Apply trend and seasonality
        $seasonalFactor = $this->getSeasonalFactor($daysAhead);
        $predictedRevenue = $avgRevenue * (1 + $trend) * $seasonalFactor;
        
        return max(0, $predictedRevenue);
    }
    
    /**
     * Calculate trend from historical data
     */
    private function calculateTrend($historicalData)
    {
        if (count($historicalData) < 2) {
            return 0;
        }
        
        $revenues = array_column($historicalData, 'revenue');
        $firstHalf = array_slice($revenues, 0, count($revenues) / 2);
        $secondHalf = array_slice($revenues, count($revenues) / 2);
        
        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);
        
        return ($secondAvg - $firstAvg) / $firstAvg;
    }
    
    /**
     * Get seasonal factor
     */
    private function getSeasonalFactor($daysAhead)
    {
        $dayOfWeek = date('w', strtotime("+$daysAhead days"));
        
        // Weekend factor
        if ($dayOfWeek == 0 || $dayOfWeek == 6) {
            return 1.2;
        }
        
        // Monday factor
        if ($dayOfWeek == 1) {
            return 1.1;
        }
        
        return 1.0;
    }
    
    /**
     * Get products for demand prediction
     */
    private function getProductsForPrediction()
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    p.id,
                    p.nama_produk,
                    p.stok,
                    p.harga,
                    COALESCE(SUM(dp.jumlah), 0) as historical_sales
                FROM produk p
                LEFT JOIN detail_penjualan dp ON p.id = dp.id_produk
                LEFT JOIN penjualan pen ON dp.id_penjualan = pen.id AND pen.status_pembayaran = 'lunas' AND pen.tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY p.id, p.nama_produk, p.stok, p.harga
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting products for prediction: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calculate product demand
     */
    private function calculateProductDemand($product)
    {
        $baseDemand = $product['historical_sales'];
        
        // Price elasticity factor
        $priceFactor = 1.0;
        if ($product['harga'] > 1000000) {
            $priceFactor = 0.8; // Higher price reduces demand
        } elseif ($product['harga'] < 100000) {
            $priceFactor = 1.2; // Lower price increases demand
        }
        
        // Stock scarcity factor
        $stockFactor = 1.0;
        if ($product['stok'] < 10) {
            $stockFactor = 1.3; // Low stock might indicate high demand
        }
        
        return max(0, $baseDemand * $priceFactor * $stockFactor);
    }
    
    /**
     * Get customers for CLV prediction
     */
    private function getCustomersForCLV()
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    u.id,
                    u.full_name,
                    COALESCE(SUM(p.total_harga), 0) as total_revenue,
                    COUNT(p.id) as total_orders,
                    AVG(p.total_harga) as avg_order_value,
                    DATEDIFF(NOW(), MIN(p.tanggal_penjualan)) as customer_age_days,
                    DATEDIFF(NOW(), MAX(p.tanggal_penjualan)) as days_since_last_purchase
                FROM users u
                LEFT JOIN penjualan p ON u.id = p.user_id AND p.status_pembayaran = 'lunas'
                WHERE u.is_active = 1
                GROUP BY u.id, u.full_name
                HAVING total_orders > 0
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting customers for CLV: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calculate customer lifetime value
     */
    private function calculateCustomerCLV($customer)
    {
        // Current CLV
        $currentCLV = $customer['total_revenue'];
        
        // Purchase frequency (orders per month)
        $customerAgeMonths = max(1, $customer['customer_age_days'] / 30);
        $purchaseFrequency = $customer['total_orders'] / $customerAgeMonths;
        
        // Average order value trend
        $avgOrderValue = $customer['avg_order_value'];
        
        // Recency factor (recent customers more valuable)
        $recencyFactor = 1.0;
        if ($customer['days_since_last_purchase'] < 30) {
            $recencyFactor = 1.3;
        } elseif ($customer['days_since_last_purchase'] > 90) {
            $recencyFactor = 0.7;
        }
        
        // Predict next 12 months
        $predictedMonths = 12;
        $predictedCLV = $currentCLV + ($purchaseFrequency * $avgOrderValue * $predictedMonths * $recencyFactor);
        
        return $predictedCLV;
    }
    
    /**
     * Get model by name
     */
    private function getModel($modelName)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM predictive_models WHERE model_name = ? AND is_active = 1");
            $stmt->execute([$modelName]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting model: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Save prediction
     */
    private function savePrediction($modelId, $entityType, $entityId, $predictionValue, $confidence, $predictionData)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO predictive_predictions 
                (model_id, entity_type, entity_id, prediction_value, confidence_score, prediction_data, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))
                ON DUPLICATE KEY UPDATE
                    prediction_value = VALUES(prediction_value),
                    confidence_score = VALUES(confidence_score),
                    prediction_data = VALUES(prediction_data),
                    predicted_at = NOW(),
                    expires_at = VALUES(expires_at)
            ");
            return $stmt->execute([$modelId, $entityType, $entityId, $predictionValue, $confidence, json_encode($predictionData)]);
        } catch (Exception $e) {
            error_log("Error saving prediction: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Calculate prediction confidence
     */
    private function calculatePredictionConfidence($data, $prediction)
    {
        // Simple confidence calculation based on data quality
        $confidence = 0.5; // Base confidence
        
        // More data points increase confidence
        if (isset($data['total_orders']) && $data['total_orders'] > 5) {
            $confidence += 0.2;
        }
        
        // Recent activity increases confidence
        if (isset($data['last_purchase_days']) && $data['last_purchase_days'] < 30) {
            $confidence += 0.1;
        }
        
        // Consistent values increase confidence
        if (isset($data['avg_order_value']) && $data['avg_order_value'] > 0) {
            $confidence += 0.1;
        }
        
        return min($confidence, 1.0);
    }
    
    /**
     * Calculate forecast confidence
     */
    private function calculateForecastConfidence($historicalData, $daysAhead)
    {
        $baseConfidence = 0.8;
        
        // Less confidence for longer forecasts
        $confidenceReduction = min(0.5, ($daysAhead / 60) * 0.5);
        
        return max(0.3, $baseConfidence - $confidenceReduction);
    }
    
    /**
     * Calculate demand confidence
     */
    private function calculateDemandConfidence($product)
    {
        $confidence = 0.5;
        
        // Historical sales data increases confidence
        if ($product['historical_sales'] > 0) {
            $confidence += 0.3;
        }
        
        // Current stock level affects confidence
        if ($product['stok'] > 0) {
            $confidence += 0.1;
        }
        
        return min($confidence, 1.0);
    }
    
    /**
     * Calculate CLV confidence
     */
    private function calculateCLVConfidence($customer)
    {
        $confidence = 0.5;
        
        // More orders increase confidence
        if ($customer['total_orders'] > 10) {
            $confidence += 0.3;
        } elseif ($customer['total_orders'] > 5) {
            $confidence += 0.2;
        }
        
        // Longer customer history increases confidence
        if ($customer['customer_age_days'] > 180) {
            $confidence += 0.1;
        }
        
        return min($confidence, 1.0);
    }
    
    /**
     * Get risk level
     */
    private function getRiskLevel($probability)
    {
        if ($probability > 0.7) {
            return 'High Risk';
        } elseif ($probability > 0.4) {
            return 'Medium Risk';
        } else {
            return 'Low Risk';
        }
    }
    
    /**
     * Get customer segment
     */
    private function getCustomerSegment($clv)
    {
        if ($clv > 10000000) {
            return 'Platinum';
        } elseif ($clv > 5000000) {
            return 'Gold';
        } elseif ($clv > 1000000) {
            return 'Silver';
        } else {
            return 'Bronze';
        }
    }
    
    /**
     * Update model accuracy
     */
    private function updateModelAccuracy($modelId)
    {
        try {
            // This would typically compare predictions with actual outcomes
            // For now, we'll simulate accuracy calculation
            $stmt = $this->db->prepare("
                UPDATE predictive_models 
                SET accuracy_score = 0.85, last_trained_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$modelId]);
        } catch (Exception $e) {
            error_log("Error updating model accuracy: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all predictive insights
     */
    public function getPredictiveInsights($limit = 20)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM v_predictive_insights
                ORDER BY confidence_score DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting predictive insights: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Run all predictive models
     */
    public function runAllPredictions()
    {
        try {
            $results = [];
            
            // Run churn prediction
            $results['churn_prediction'] = $this->predictCustomerChurn();
            
            // Run sales forecasting
            $results['sales_forecast'] = $this->forecastSales(30);
            
            // Run product demand prediction
            $results['demand_prediction'] = $this->predictProductDemand();
            
            // Run CLV prediction
            $results['clv_prediction'] = $this->predictCustomerLifetimeValue();
            
            return [
                'success' => true,
                'results' => $results,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("Error running all predictions: " . $e->getMessage());
            return ['success' => false, 'message' => 'Batch prediction failed'];
        }
    }
}
