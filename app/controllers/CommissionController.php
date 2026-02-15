<?php
/**
 * Automated Commission Calculation System
 * Batch 5: Advanced Sales & Marketing
 */

class CommissionController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Calculate commission for a sale
     */
    public function calculateCommission($saleId, $userId, $saleAmount)
    {
        try {
            $totalCommission = 0;
            $calculations = [];
            
            // Get applicable commission rules
            $rules = $this->getApplicableRules($userId, $saleAmount);
            
            foreach ($rules as $rule) {
                $commissionAmount = $this->calculateRuleCommission($rule, $saleAmount, $userId);
                
                if ($commissionAmount > 0) {
                    // Save calculation
                    $calculationId = $this->saveCommissionCalculation([
                        'user_id' => $userId,
                        'sale_id' => $saleId,
                        'rule_id' => $rule['id'],
                        'commission_amount' => $commissionAmount,
                        'commission_rate' => $this->getCommissionRate($rule, $saleAmount),
                        'sale_amount' => $saleAmount,
                        'calculation_date' => date('Y-m-d'),
                        'status' => 'calculated'
                    ]);
                    
                    $calculations[] = [
                        'rule_name' => $rule['rule_name'],
                        'commission_amount' => $commissionAmount,
                        'calculation_id' => $calculationId
                    ];
                    
                    $totalCommission += $commissionAmount;
                }
            }
            
            return [
                'success' => true,
                'total_commission' => $totalCommission,
                'calculations' => $calculations
            ];
        } catch (Exception $e) {
            error_log("Error calculating commission: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to calculate commission'];
        }
    }
    
    /**
     * Get applicable commission rules for user
     */
    private function getApplicableRules($userId, $saleAmount)
    {
        try {
            // Get user role
            $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) return [];
            
            $userRole = $user['role'];
            
            // Get applicable rules
            $stmt = $this->db->prepare("
                SELECT cr.*, ct.tier_level, ct.min_sales, ct.max_sales, 
                       ct.commission_rate as tier_rate, ct.bonus_amount
                FROM commission_rules cr
                LEFT JOIN commission_tiers ct ON cr.id = ct.rule_id
                WHERE cr.target_role = ? 
                AND cr.is_active = 1
                AND cr.effective_date <= CURDATE()
                AND (cr.expiry_date IS NULL OR cr.expiry_date >= CURDATE())
                AND (cr.min_amount IS NULL OR ? >= cr.min_amount)
                AND (cr.max_amount IS NULL OR ? <= cr.max_amount)
                ORDER BY cr.rule_type, ct.tier_level
            ");
            $stmt->execute([$userRole, $saleAmount, $saleAmount]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting applicable rules: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calculate commission based on rule
     */
    private function calculateRuleCommission($rule, $saleAmount, $userId)
    {
        try {
            switch ($rule['rule_type']) {
                case 'percentage':
                    return $saleAmount * ($rule['commission_value'] / 100);
                    
                case 'fixed':
                    return $rule['commission_value'];
                    
                case 'tiered':
                    return $this->calculateTieredCommission($rule, $saleAmount, $userId);
                    
                case 'performance':
                    return $this->calculatePerformanceCommission($rule, $saleAmount, $userId);
                    
                default:
                    return 0;
            }
        } catch (Exception $e) {
            error_log("Error calculating rule commission: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calculate tiered commission
     */
    private function calculateTieredCommission($rule, $saleAmount, $userId)
    {
        try {
            // Get user's total sales for current month
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(total_harga), 0) as monthly_sales
                FROM penjualan 
                WHERE id_user = ? 
                AND status = 'selesai'
                AND MONTH(created_at) = MONTH(CURRENT_DATE)
                AND YEAR(created_at) = YEAR(CURRENT_DATE)
            ");
            $stmt->execute([$userId]);
            $monthlySales = $stmt->fetch()['monthly_sales'];
            
            // Find applicable tier
            $stmt = $this->db->prepare("
                SELECT commission_rate, bonus_amount
                FROM commission_tiers
                WHERE rule_id = ?
                AND ? >= min_sales
                AND (max_sales IS NULL OR ? <= max_sales)
                ORDER BY tier_level DESC
                LIMIT 1
            ");
            $stmt->execute([$rule['id'], $monthlySales, $monthlySales]);
            $tier = $stmt->fetch();
            
            if (!$tier) {
                return $saleAmount * ($rule['commission_value'] / 100);
            }
            
            $commission = $saleAmount * ($tier['commission_rate'] / 100);
            $commission += $tier['bonus_amount'];
            
            return $commission;
        } catch (Exception $e) {
            error_log("Error calculating tiered commission: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calculate performance-based commission
     */
    private function calculatePerformanceCommission($rule, $saleAmount, $userId)
    {
        try {
            // Get performance metric
            $performanceValue = $this->getPerformanceMetric($rule['performance_metric'], $userId);
            
            if ($performanceValue >= ($rule['performance_target'] ?? 0)) {
                return $saleAmount * ($rule['commission_value'] / 100);
            }
            
            return 0;
        } catch (Exception $e) {
            error_log("Error calculating performance commission: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get performance metric value
     */
    private function getPerformanceMetric($metric, $userId)
    {
        try {
            switch ($metric) {
                case 'monthly_sales':
                    $stmt = $this->db->prepare("
                        SELECT COALESCE(SUM(total_harga), 0) as value
                        FROM penjualan 
                        WHERE id_user = ? 
                        AND status = 'selesai'
                        AND MONTH(created_at) = MONTH(CURRENT_DATE)
                        AND YEAR(created_at) = YEAR(CURRENT_DATE)
                    ");
                    break;
                    
                case 'customer_count':
                    $stmt = $this->db->prepare("
                        SELECT COUNT(DISTINCT id_customer) as value
                        FROM penjualan 
                        WHERE id_user = ? 
                        AND status = 'selesai'
                        AND MONTH(created_at) = MONTH(CURRENT_DATE)
                        AND YEAR(created_at) = YEAR(CURRENT_DATE)
                    ");
                    break;
                    
                case 'conversion_rate':
                    $stmt = $this->db->prepare("
                        SELECT 
                            CASE 
                                WHEN COUNT(DISTINCT p.id) = 0 THEN 0
                                ELSE ROUND(COUNT(DISTINCT CASE WHEN p.status = 'selesai' THEN p.id END) * 100.0 / COUNT(DISTINCT p.id), 2)
                            END as value
                        FROM penjualan p
                        WHERE p.id_user = ? 
                        AND MONTH(created_at) = MONTH(CURRENT_DATE)
                        AND YEAR(created_at) = YEAR(CURRENT_DATE)
                    ");
                    break;
                    
                default:
                    return 0;
            }
            
            $stmt->execute([$userId]);
            return $stmt->fetch()['value'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting performance metric: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get commission rate for rule
     */
    private function getCommissionRate($rule, $saleAmount)
    {
        if ($rule['rule_type'] === 'percentage') {
            return $rule['commission_value'];
        } elseif ($rule['rule_type'] === 'tiered') {
            // Get tier rate based on sale amount
            $stmt = $this->db->prepare("
                SELECT commission_rate
                FROM commission_tiers
                WHERE rule_id = ?
                AND ? >= min_sales
                AND (max_sales IS NULL OR ? <= max_sales)
                ORDER BY tier_level DESC
                LIMIT 1
            ");
            $stmt->execute([$rule['id'], $saleAmount, $saleAmount]);
            $tier = $stmt->fetch();
            return $tier ? $tier['commission_rate'] : $rule['commission_value'];
        }
        
        return 0;
    }
    
    /**
     * Save commission calculation
     */
    private function saveCommissionCalculation($data)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO commission_calculations 
                (user_id, sale_id, rule_id, commission_amount, commission_rate, sale_amount, calculation_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['user_id'],
                $data['sale_id'],
                $data['rule_id'],
                $data['commission_amount'],
                $data['commission_rate'],
                $data['sale_amount'],
                $data['calculation_date'],
                $data['status']
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error saving commission calculation: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Approve commission calculations
     */
    public function approveCommission($calculationId, $approvedBy)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE commission_calculations 
                SET status = 'approved', approved_by = ?, approved_at = NOW()
                WHERE id = ? AND status = 'calculated'
            ");
            $result = $stmt->execute([$approvedBy, $calculationId]);
            
            return $result ? ['success' => true, 'message' => 'Commission approved'] : ['success' => false, 'message' => 'Failed to approve commission'];
        } catch (Exception $e) {
            error_log("Error approving commission: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Mark commission as paid
     */
    public function markCommissionPaid($calculationId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE commission_calculations 
                SET status = 'paid', paid_at = NOW()
                WHERE id = ? AND status = 'approved'
            ");
            $result = $stmt->execute([$calculationId]);
            
            return $result ? ['success' => true, 'message' => 'Commission marked as paid'] : ['success' => false, 'message' => 'Failed to mark commission as paid'];
        } catch (Exception $e) {
            error_log("Error marking commission as paid: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Get user commission summary
     */
    public function getUserCommissionSummary($userId, $startDate = null, $endDate = null)
    {
        try {
            $startDate = $startDate ?? date('Y-m-01');
            $endDate = $endDate ?? date('Y-m-t');
            
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_calculations,
                    SUM(CASE WHEN status = 'calculated' THEN commission_amount ELSE 0 END) as calculated_amount,
                    SUM(CASE WHEN status = 'approved' THEN commission_amount ELSE 0 END) as approved_amount,
                    SUM(CASE WHEN status = 'paid' THEN commission_amount ELSE 0 END) as paid_amount,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
                    COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count
                FROM commission_calculations
                WHERE user_id = ?
                AND calculation_date BETWEEN ? AND ?
            ");
            $stmt->execute([$userId, $startDate, $endDate]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting commission summary: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get commission performance analytics
     */
    public function getCommissionAnalytics($startDate = null, $endDate = null)
    {
        try {
            $startDate = $startDate ?? date('Y-m-01');
            $endDate = $endDate ?? date('Y-m-t');
            
            $stmt = $this->db->prepare("
                SELECT 
                    u.role,
                    u.name,
                    COUNT(cc.id) as total_commissions,
                    SUM(cc.commission_amount) as total_commission_amount,
                    AVG(cc.commission_amount) as avg_commission,
                    SUM(CASE WHEN cc.status = 'paid' THEN cc.commission_amount ELSE 0 END) as paid_amount,
                    COUNT(CASE WHEN cc.status = 'paid' THEN 1 END) as paid_count
                FROM commission_calculations cc
                JOIN users u ON cc.user_id = u.id
                WHERE cc.calculation_date BETWEEN ? AND ?
                GROUP BY u.id, u.role, u.name
                ORDER BY total_commission_amount DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting commission analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bulk calculate commissions for period
     */
    public function bulkCalculateCommissions($startDate = null, $endDate = null)
    {
        try {
            $startDate = $startDate ?? date('Y-m-01');
            $endDate = $endDate ?? date('Y-m-t');
            
            // Get all sales in period without commission
            $stmt = $this->db->prepare("
                SELECT p.id, p.id_user, p.total_harga
                FROM penjualan p
                WHERE p.status = 'selesai'
                AND p.created_at BETWEEN ? AND ?
                AND p.id NOT IN (
                    SELECT DISTINCT sale_id FROM commission_calculations
                )
            ");
            $stmt->execute([$startDate, $endDate]);
            $sales = $stmt->fetchAll();
            
            $processed = 0;
            $totalCommission = 0;
            
            foreach ($sales as $sale) {
                $result = $this->calculateCommission($sale['id'], $sale['id_user'], $sale['total_harga']);
                if ($result['success']) {
                    $processed++;
                    $totalCommission += $result['total_commission'];
                }
            }
            
            return [
                'success' => true,
                'processed_sales' => $processed,
                'total_commission' => $totalCommission,
                'message' => "Processed {$processed} sales with total commission Rp" . number_format($totalCommission, 0, ',', '.')
            ];
        } catch (Exception $e) {
            error_log("Error in bulk commission calculation: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to calculate bulk commissions'];
        }
    }
}
