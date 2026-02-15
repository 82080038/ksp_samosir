<?php
/**
 * Data Visualization Controller
 * Batch 7: Advanced Analytics
 */

class DataVisualizationController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Create new visualization chart
     */
    public function createChart($chartData)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO visualization_charts 
                (chart_name, chart_type, data_query, chart_config, refresh_interval, is_public, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $chartData['chart_name'],
                $chartData['chart_type'],
                $chartData['data_query'],
                json_encode($chartData['chart_config'] ?? []),
                $chartData['refresh_interval'] ?? 300,
                $chartData['is_public'] ?? false,
                $_SESSION['user_id']
            ]);
            
            if ($result) {
                $chartId = $this->db->lastInsertId();
                return [
                    'success' => true,
                    'chart_id' => $chartId,
                    'message' => 'Chart created successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create chart'];
        } catch (Exception $e) {
            error_log("Error creating chart: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Get chart data
     */
    public function getChartData($chartId)
    {
        try {
            // Get chart configuration
            $stmt = $this->db->prepare("
                SELECT * FROM visualization_charts 
                WHERE id = ? AND (is_public = 1 OR created_by = ?)
            ");
            $stmt->execute([$chartId, $_SESSION['user_id']]);
            $chart = $stmt->fetch();
            
            if (!$chart) {
                return ['success' => false, 'message' => 'Chart not found or access denied'];
            }
            
            // Execute data query
            $data = $this->executeChartQuery($chart['data_query']);
            
            // Format data for chart
            $formattedData = $this->formatChartData($data, $chart['chart_type'], json_decode($chart['chart_config'], true));
            
            return [
                'success' => true,
                'chart' => [
                    'id' => $chart['id'],
                    'name' => $chart['chart_name'],
                    'type' => $chart['chart_type'],
                    'config' => json_decode($chart['chart_config'], true),
                    'data' => $formattedData,
                    'last_updated' => date('Y-m-d H:i:s')
                ]
            ];
        } catch (Exception $e) {
            error_log("Error getting chart data: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get chart data'];
        }
    }
    
    /**
     * Get all charts
     */
    public function getAllCharts($userId = null)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT vc.*, u.full_name as created_by_name
                FROM visualization_charts vc
                JOIN users u ON vc.created_by = u.id
                WHERE vc.is_public = 1 OR vc.created_by = ?
                ORDER BY vc.created_at DESC
            ");
            $stmt->execute([$userId ?? $_SESSION['user_id']]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting all charts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Execute chart query
     */
    private function executeChartQuery($query)
    {
        try {
            $stmt = $this->db->query($query);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error executing chart query: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Format data for different chart types
     */
    private function formatChartData($data, $chartType, $config)
    {
        try {
            switch ($chartType) {
                case 'line':
                    return $this->formatLineChartData($data, $config);
                case 'bar':
                    return $this->formatBarChartData($data, $config);
                case 'pie':
                    return $this->formatPieChartData($data, $config);
                case 'area':
                    return $this->formatAreaChartData($data, $config);
                case 'scatter':
                    return $this->formatScatterChartData($data, $config);
                case 'gauge':
                    return $this->formatGaugeChartData($data, $config);
                case 'heatmap':
                    return $this->formatHeatmapData($data, $config);
                case 'funnel':
                    return $this->formatFunnelChartData($data, $config);
                default:
                    return $data;
            }
        } catch (Exception $e) {
            error_log("Error formatting chart data: " . $e->getMessage());
            return $data;
        }
    }
    
    /**
     * Format line chart data
     */
    private function formatLineChartData($data, $config)
    {
        $xField = $config['xAxis'] ?? 'date';
        $yField = $config['yAxis'] ?? 'value';
        
        $formatted = [
            'labels' => [],
            'datasets' => []
        ];
        
        // Extract labels
        foreach ($data as $row) {
            $formatted['labels'][] = $row[$xField];
        }
        
        // Handle multiple series
        if (isset($config['series'])) {
            foreach ($config['series'] as $series) {
                $dataset = [
                    'label' => $series['label'],
                    'data' => [],
                    'borderColor' => $series['color'] ?? '#007bff',
                    'backgroundColor' => $series['backgroundColor'] ?? 'rgba(0, 123, 255, 0.1)',
                    'fill' => $series['fill'] ?? false
                ];
                
                foreach ($data as $row) {
                    $dataset['data'][] = $row[$series['field']] ?? 0;
                }
                
                $formatted['datasets'][] = $dataset;
            }
        } else {
            // Single dataset
            $dataset = [
                'label' => $config['label'] ?? 'Data',
                'data' => [],
                'borderColor' => $config['color'] ?? '#007bff',
                'backgroundColor' => $config['backgroundColor'] ?? 'rgba(0, 123, 255, 0.1)',
                'fill' => $config['fill'] ?? false
            ];
            
            foreach ($data as $row) {
                $dataset['data'][] = $row[$yField] ?? 0;
            }
            
            $formatted['datasets'][] = $dataset;
        }
        
        return $formatted;
    }
    
    /**
     * Format bar chart data
     */
    private function formatBarChartData($data, $config)
    {
        $xField = $config['xAxis'] ?? 'label';
        $yField = $config['yAxis'] ?? 'value';
        
        $formatted = [
            'labels' => [],
            'datasets' => []
        ];
        
        foreach ($data as $row) {
            $formatted['labels'][] = $row[$xField];
        }
        
        if (isset($config['series'])) {
            foreach ($config['series'] as $series) {
                $dataset = [
                    'label' => $series['label'],
                    'data' => [],
                    'backgroundColor' => $series['color'] ?? '#007bff'
                ];
                
                foreach ($data as $row) {
                    $dataset['data'][] = $row[$series['field']] ?? 0;
                }
                
                $formatted['datasets'][] = $dataset;
            }
        } else {
            $dataset = [
                'label' => $config['label'] ?? 'Data',
                'data' => [],
                'backgroundColor' => $config['backgroundColor'] ?? '#007bff'
            ];
            
            foreach ($data as $row) {
                $dataset['data'][] = $row[$yField] ?? 0;
            }
            
            $formatted['datasets'][] = $dataset;
        }
        
        return $formatted;
    }
    
    /**
     * Format pie chart data
     */
    private function formatPieChartData($data, $config)
    {
        $labelField = $config['labelField'] ?? 'label';
        $valueField = $config['valueField'] ?? 'value';
        
        $formatted = [
            'labels' => [],
            'datasets' => [[
                'data' => [],
                'backgroundColor' => []
            ]]
        ];
        
        $colors = $config['colors'] ?? [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
        ];
        
        foreach ($data as $index => $row) {
            $formatted['labels'][] = $row[$labelField];
            $formatted['datasets'][0]['data'][] = $row[$valueField] ?? 0;
            $formatted['datasets'][0]['backgroundColor'][] = $colors[$index % count($colors)];
        }
        
        return $formatted;
    }
    
    /**
     * Format area chart data
     */
    private function formatAreaChartData($data, $config)
    {
        $formatted = $this->formatLineChartData($data, $config);
        
        // Set fill to true for area charts
        foreach ($formatted['datasets'] as &$dataset) {
            $dataset['fill'] = true;
        }
        
        return $formatted;
    }
    
    /**
     * Format scatter chart data
     */
    private function formatScatterChartData($data, $config)
    {
        $xField = $config['xField'] ?? 'x';
        $yField = $config['yField'] ?? 'y';
        
        $formatted = [
            'datasets' => [[
                'label' => $config['label'] ?? 'Data',
                'data' => [],
                'backgroundColor' => $config['color'] ?? '#007bff'
            ]]
        ];
        
        foreach ($data as $row) {
            $formatted['datasets'][0]['data'][] = [
                'x' => $row[$xField] ?? 0,
                'y' => $row[$yField] ?? 0
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Format gauge chart data
     */
    private function formatGaugeChartData($data, $config)
    {
        $valueField = $config['valueField'] ?? 'value';
        $maxField = $config['maxField'] ?? 'max';
        
        if (empty($data)) {
            return ['value' => 0, 'max' => 100];
        }
        
        $row = $data[0];
        $value = $row[$valueField] ?? 0;
        $max = $row[$maxField] ?? 100;
        
        return [
            'value' => $value,
            'max' => $max,
            'percentage' => $max > 0 ? ($value / $max) * 100 : 0
        ];
    }
    
    /**
     * Format heatmap data
     */
    private function formatHeatmapData($data, $config)
    {
        $xField = $config['xField'] ?? 'x';
        $yField = $config['yField'] ?? 'y';
        $valueField = $config['valueField'] ?? 'value';
        
        $formatted = [
            'data' => [],
            'xLabels' => [],
            'yLabels' => []
        ];
        
        $xValues = [];
        $yValues = [];
        
        // Extract unique x and y values
        foreach ($data as $row) {
            $xValues[] = $row[$xField];
            $yValues[] = $row[$yField];
        }
        
        $formatted['xLabels'] = array_values(array_unique($xValues));
        $formatted['yLabels'] = array_values(array_unique($yValues));
        
        // Format data points
        foreach ($data as $row) {
            $formatted['data'][] = [
                'x' => array_search($row[$xField], $formatted['xLabels']),
                'y' => array_search($row[$yField], $formatted['yLabels']),
                'v' => $row[$valueField] ?? 0
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Format funnel chart data
     */
    private function formatFunnelChartData($data, $config)
    {
        $labelField = $config['labelField'] ?? 'label';
        $valueField = $config['valueField'] ?? 'value';
        
        $formatted = [
            'labels' => [],
            'values' => [],
            'backgroundColor' => []
        ];
        
        $colors = $config['colors'] ?? [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
        ];
        
        foreach ($data as $index => $row) {
            $formatted['labels'][] = $row[$labelField];
            $formatted['values'][] = $row[$valueField] ?? 0;
            $formatted['backgroundColor'][] = $colors[$index % count($colors)];
        }
        
        return $formatted;
    }
    
    /**
     * Get predefined charts
     */
    public function getPredefinedCharts()
    {
        return [
            [
                'chart_name' => 'Revenue Trend',
                'chart_type' => 'line',
                'data_query' => 'SELECT DATE(tanggal_penjualan) as date, SUM(total_harga) as revenue FROM penjualan WHERE status_pembayaran = "lunas" AND tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(tanggal_penjualan) ORDER BY date',
                'chart_config' => [
                    'xAxis' => 'date',
                    'yAxis' => 'revenue',
                    'label' => 'Daily Revenue',
                    'color' => '#28a745',
                    'fill' => true
                ],
                'is_public' => true
            ],
            [
                'chart_name' => 'Sales by Category',
                'chart_type' => 'pie',
                'data_query' => 'SELECT kp.nama_kategori as category, SUM(dp.total_harga) as total FROM detail_penjualan dp JOIN produk p ON dp.id_produk = p.id JOIN kategori_produk kp ON p.kategori_id = kp.id JOIN penjualan pen ON dp.id_penjualan = pen.id WHERE pen.status_pembayaran = "lunas" AND pen.tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY kp.id',
                'chart_config' => [
                    'labelField' => 'category',
                    'valueField' => 'total'
                ],
                'is_public' => true
            ],
            [
                'chart_name' => 'Top Customers',
                'chart_type' => 'bar',
                'data_query' => 'SELECT u.full_name as name, SUM(p.total_harga) as total FROM penjualan p JOIN users u ON p.user_id = u.id WHERE p.status_pembayaran = "lunas" AND p.tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY u.id ORDER BY total DESC LIMIT 10',
                'chart_config' => [
                    'xAxis' => 'name',
                    'yAxis' => 'total',
                    'label' => 'Total Purchase',
                    'backgroundColor' => '#007bff'
                ],
                'is_public' => true
            ],
            [
                'chart_name' => 'Commission Distribution',
                'chart_type' => 'doughnut',
                'data_query' => 'SELECT r.name as role, COALESCE(SUM(cc.commission_amount), 0) as total FROM commission_calculations cc JOIN users u ON cc.user_id = u.id JOIN roles r ON u.role_id = r.id WHERE cc.status = "paid" AND cc.calculation_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY r.id',
                'chart_config' => [
                    'labelField' => 'role',
                    'valueField' => 'total'
                ],
                'is_public' => true
            ],
            [
                'chart_name' => 'Order Volume Trend',
                'chart_type' => 'area',
                'data_query' => 'SELECT DATE(tanggal_penjualan) as date, COUNT(*) as orders FROM penjualan WHERE status_pembayaran = "lunas" AND tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(tanggal_penjualan) ORDER BY date',
                'chart_config' => [
                    'xAxis' => 'date',
                    'yAxis' => 'orders',
                    'label' => 'Daily Orders',
                    'color' => '#ffc107',
                    'fill' => true
                ],
                'is_public' => true
            ]
        ];
    }
    
    /**
     * Update chart
     */
    public function updateChart($chartId, $chartData)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE visualization_charts 
                SET chart_name = ?, chart_type = ?, data_query = ?, chart_config = ?, refresh_interval = ?, is_public = ?, updated_at = NOW()
                WHERE id = ? AND created_by = ?
            ");
            
            $result = $stmt->execute([
                $chartData['chart_name'],
                $chartData['chart_type'],
                $chartData['data_query'],
                json_encode($chartData['chart_config'] ?? []),
                $chartData['refresh_interval'] ?? 300,
                $chartData['is_public'] ?? false,
                $chartId,
                $_SESSION['user_id']
            ]);
            
            return $result ? 
                ['success' => true, 'message' => 'Chart updated successfully'] : 
                ['success' => false, 'message' => 'Failed to update chart'];
        } catch (Exception $e) {
            error_log("Error updating chart: " . $e->getMessage());
            return ['success' => false, 'message' => 'Update failed'];
        }
    }
    
    /**
     * Delete chart
     */
    public function deleteChart($chartId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM visualization_charts 
                WHERE id = ? AND created_by = ?
            ");
            
            $result = $stmt->execute([$chartId, $_SESSION['user_id']]);
            
            return $result ? 
                ['success' => true, 'message' => 'Chart deleted successfully'] : 
                ['success' => false, 'message' => 'Failed to delete chart'];
        } catch (Exception $e) {
            error_log("Error deleting chart: " . $e->getMessage());
            return ['success' => false, 'message' => 'Delete failed'];
        }
    }
    
    /**
     * Get chart analytics
     */
    public function getChartAnalytics($chartId, $startDate = null, $endDate = null)
    {
        try {
            $startDate = $startDate ?? date('Y-m-01');
            $endDate = $endDate ?? date('Y-m-t');
            
            // Get chart info
            $stmt = $this->db->prepare("
                SELECT * FROM visualization_charts 
                WHERE id = ? AND (is_public = 1 OR created_by = ?)
            ");
            $stmt->execute([$chartId, $_SESSION['user_id']]);
            $chart = $stmt->fetch();
            
            if (!$chart) {
                return ['success' => false, 'message' => 'Chart not found'];
            }
            
            // Get current data
            $currentData = $this->executeChartQuery($chart['data_query']);
            
            // Get comparison data (previous period)
            $comparisonQuery = $this->modifyQueryForComparison($chart['data_query'], $startDate, $endDate);
            $comparisonData = $this->executeChartQuery($comparisonQuery);
            
            return [
                'success' => true,
                'chart_info' => [
                    'name' => $chart['chart_name'],
                    'type' => $chart['chart_type'],
                    'last_updated' => $chart['updated_at']
                ],
                'current_period' => [
                    'data_count' => count($currentData),
                    'data' => $currentData
                ],
                'comparison_period' => [
                    'data_count' => count($comparisonData),
                    'data' => $comparisonData
                ],
                'analytics' => $this->calculateChartAnalytics($currentData, $comparisonData, $chart['chart_type'])
            ];
        } catch (Exception $e) {
            error_log("Error getting chart analytics: " . $e->getMessage());
            return ['success' => false, 'message' => 'Analytics failed'];
        }
    }
    
    /**
     * Modify query for comparison period
     */
    private function modifyQueryForComparison($query, $startDate, $endDate)
    {
        // Simple modification - in a real implementation, this would be more sophisticated
        $daysDiff = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24);
        $prevStart = date('Y-m-d', strtotime($startDate) - ($daysDiff * 24 * 60 * 60));
        $prevEnd = date('Y-m-d', strtotime($endDate) - ($daysDiff * 24 * 60 * 60));
        
        // Replace date ranges in query
        $query = str_replace($startDate, $prevStart, $query);
        $query = str_replace($endDate, $prevEnd, $query);
        
        return $query;
    }
    
    /**
     * Calculate chart analytics
     */
    private function calculateChartAnalytics($currentData, $comparisonData, $chartType)
    {
        $analytics = [];
        
        switch ($chartType) {
            case 'line':
            case 'bar':
            case 'area':
                $currentSum = array_sum(array_column($currentData, 'value') ?? array_column($currentData, 'total') ?? array_column($currentData, 'revenue') ?? []);
                $comparisonSum = array_sum(array_column($comparisonData, 'value') ?? array_column($comparisonData, 'total') ?? array_column($comparisonData, 'revenue') ?? []);
                
                $analytics['current_total'] = $currentSum;
                $analytics['comparison_total'] = $comparisonSum;
                $analytics['change_percentage'] = $comparisonSum > 0 ? (($currentSum - $comparisonSum) / $comparisonSum) * 100 : 0;
                $analytics['trend'] = $analytics['change_percentage'] > 0 ? 'up' : 'down';
                break;
                
            case 'pie':
            case 'doughnut':
                $analytics['segments_count'] = count($currentData);
                $analytics['top_segment'] = !empty($currentData) ? max($currentData) : null;
                break;
                
            default:
                $analytics['data_points'] = count($currentData);
                break;
        }
        
        return $analytics;
    }
    
    /**
     * Export chart data
     */
    public function exportChartData($chartId, $format = 'json')
    {
        try {
            $chartData = $this->getChartData($chartId);
            
            if (!$chartData['success']) {
                return $chartData;
            }
            
            switch ($format) {
                case 'json':
                    return [
                        'success' => true,
                        'data' => json_encode($chartData['chart'], JSON_PRETTY_PRINT),
                        'filename' => 'chart_' . $chartId . '.json'
                    ];
                    
                case 'csv':
                    $csv = $this->convertChartDataToCSV($chartData['chart']['data']);
                    return [
                        'success' => true,
                        'data' => $csv,
                        'filename' => 'chart_' . $chartId . '.csv'
                    ];
                    
                default:
                    return ['success' => false, 'message' => 'Unsupported export format'];
            }
        } catch (Exception $e) {
            error_log("Error exporting chart data: " . $e->getMessage());
            return ['success' => false, 'message' => 'Export failed'];
        }
    }
    
    /**
     * Convert chart data to CSV
     */
    private function convertChartDataToCSV($chartData)
    {
        $csv = '';
        
        if (isset($chartData['labels'])) {
            // Chart.js format
            $csv .= implode(',', $chartData['labels']) . "\n";
            
            foreach ($chartData['datasets'] as $dataset) {
                $csv .= $dataset['label'] . ',' . implode(',', $dataset['data']) . "\n";
            }
        } else {
            // Raw data format
            if (!empty($chartData)) {
                $csv .= implode(',', array_keys($chartData[0])) . "\n";
                
                foreach ($chartData as $row) {
                    $csv .= implode(',', $row) . "\n";
                }
            }
        }
        
        return $csv;
    }
}
