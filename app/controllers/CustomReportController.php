<?php
/**
 * Custom Report Builder Controller
 * Batch 7: Advanced Analytics
 */

class CustomReportController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Create new report template
     */
    public function createReportTemplate($templateData)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO report_templates 
                (template_name, description, report_type, data_sources, filters, columns_config, chart_config, schedule_config, is_public, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $templateData['template_name'],
                $templateData['description'] ?? null,
                $templateData['report_type'],
                json_encode($templateData['data_sources']),
                json_encode($templateData['filters'] ?? []),
                json_encode($templateData['columns_config'] ?? []),
                json_encode($templateData['chart_config'] ?? []),
                json_encode($templateData['schedule_config'] ?? []),
                $templateData['is_public'] ?? false,
                $_SESSION['user_id']
            ]);
            
            if ($result) {
                $templateId = $this->db->lastInsertId();
                return [
                    'success' => true,
                    'template_id' => $templateId,
                    'message' => 'Report template created successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create report template'];
        } catch (Exception $e) {
            error_log("Error creating report template: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Generate report from template
     */
    public function generateReport($templateId, $parameters = [], $format = 'pdf')
    {
        try {
            // Get template
            $template = $this->getReportTemplate($templateId);
            if (!$template) {
                return ['success' => false, 'message' => 'Report template not found'];
            }
            
            // Merge template parameters with runtime parameters
            $mergedParameters = array_merge(
                json_decode($template['filters'] ?? '{}', true),
                $parameters
            );
            
            // Generate report data
            $reportData = $this->generateReportData($template, $mergedParameters);
            
            // Create report instance
            $reportInstanceId = $this->createReportInstance($templateId, $parameters, $reportData, $format);
            
            if ($reportInstanceId) {
                // Generate file
                $filePath = $this->generateReportFile($reportInstanceId, $reportData, $template, $format);
                
                if ($filePath) {
                    return [
                        'success' => true,
                        'report_instance_id' => $reportInstanceId,
                        'file_path' => $filePath,
                        'download_url' => "/reports/download/{$reportInstanceId}",
                        'message' => 'Report generated successfully'
                    ];
                }
            }
            
            return ['success' => false, 'message' => 'Failed to generate report'];
        } catch (Exception $e) {
            error_log("Error generating report: " . $e->getMessage());
            return ['success' => false, 'message' => 'Report generation failed'];
        }
    }
    
    /**
     * Get report template
     */
    public function getReportTemplate($templateId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT rt.*, u.full_name as created_by_name
                FROM report_templates rt
                JOIN users u ON rt.created_by = u.id
                WHERE rt.id = ?
            ");
            $stmt->execute([$templateId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting report template: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all report templates
     */
    public function getReportTemplates($userId = null)
    {
        try {
            $sql = "
                SELECT rt.*, u.full_name as created_by_name
                FROM report_templates rt
                JOIN users u ON rt.created_by = u.id
                WHERE (rt.is_public = 1 OR rt.created_by = ?)
                ORDER BY rt.created_at DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId ?? $_SESSION['user_id']]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting report templates: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate report data based on template
     */
    private function generateReportData($template, $parameters)
    {
        try {
            $dataSources = json_decode($template['data_sources'], true);
            $reportData = [];
            
            foreach ($dataSources as $dataSource) {
                $data = $this->executeDataSourceQuery($dataSource, $parameters);
                $reportData[$dataSource['alias']] = $data;
            }
            
            return $reportData;
        } catch (Exception $e) {
            error_log("Error generating report data: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Execute data source query
     */
    private function executeDataSourceQuery($dataSource, $parameters)
    {
        try {
            $query = $dataSource['query'];
            $params = [];
            
            // Replace parameter placeholders
            foreach ($parameters as $key => $value) {
                $placeholder = ':' . $key;
                if (strpos($query, $placeholder) !== false) {
                    $query = str_replace($placeholder, '?', $query);
                    $params[] = $value;
                }
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error executing data source query: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create report instance
     */
    private function createReportInstance($templateId, $parameters, $reportData, $format)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO report_instances 
                (template_id, report_name, parameters, generated_data, file_type, status, generated_by)
                VALUES (?, ?, ?, ?, ?, 'generating', ?)
            ");
            
            $reportName = 'Report_' . date('Y-m-d_H-i-s');
            
            $result = $stmt->execute([
                $templateId,
                $reportName,
                json_encode($parameters),
                json_encode($reportData),
                $format,
                $_SESSION['user_id']
            ]);
            
            return $result ? $this->db->lastInsertId() : false;
        } catch (Exception $e) {
            error_log("Error creating report instance: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate report file
     */
    private function generateReportFile($reportInstanceId, $reportData, $template, $format)
    {
        try {
            $filePath = "/reports/report_{$reportInstanceId}." . $format;
            $fullPath = __DIR__ . "/../.." . $filePath;
            
            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            switch ($format) {
                case 'pdf':
                    $this->generatePDFReport($fullPath, $reportData, $template);
                    break;
                case 'excel':
                    $this->generateExcelReport($fullPath, $reportData, $template);
                    break;
                case 'csv':
                    $this->generateCSVReport($fullPath, $reportData, $template);
                    break;
                default:
                    throw new Exception("Unsupported format: {$format}");
            }
            
            // Update report instance
            $this->updateReportInstance($reportInstanceId, $filePath, 'completed');
            
            return $filePath;
        } catch (Exception $e) {
            error_log("Error generating report file: " . $e->getMessage());
            $this->updateReportInstance($reportInstanceId, null, 'failed');
            return false;
        }
    }
    
    /**
     * Generate PDF report
     */
    private function generatePDFReport($filePath, $reportData, $template)
    {
        try {
            // This would use a PDF library like TCPDF or DomPDF
            // For now, we'll create a simple HTML-based PDF
            
            $html = $this->generateReportHTML($reportData, $template);
            
            // Simple HTML to PDF conversion (would need actual PDF library)
            file_put_contents($filePath, $html);
            
            return true;
        } catch (Exception $e) {
            error_log("Error generating PDF report: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate Excel report
     */
    private function generateExcelReport($filePath, $reportData, $template)
    {
        try {
            // This would use a library like PhpSpreadsheet
            // For now, we'll create a simple CSV with Excel headers
            
            $csvContent = $this->generateCSVContent($reportData, $template);
            file_put_contents($filePath, $csvContent);
            
            return true;
        } catch (Exception $e) {
            error_log("Error generating Excel report: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate CSV report
     */
    private function generateCSVReport($filePath, $reportData, $template)
    {
        try {
            $csvContent = $this->generateCSVContent($reportData, $template);
            file_put_contents($filePath, $csvContent);
            
            return true;
        } catch (Exception $e) {
            error_log("Error generating CSV report: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate report HTML
     */
    private function generateReportHTML($reportData, $template)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>' . htmlspecialchars($template['template_name']) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .section { margin-bottom: 30px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .chart-placeholder { background-color: #f9f9f9; border: 1px dashed #ccc; height: 200px; display: flex; align-items: center; justify-content: center; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . htmlspecialchars($template['template_name']) . '</h1>
        <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
        
        if ($template['description']) {
            $html .= '<p>' . htmlspecialchars($template['description']) . '</p>';
        }
        
        $html .= '</div>';
        
        // Add data sections
        foreach ($reportData as $alias => $data) {
            $html .= '<div class="section">
                <h2>' . htmlspecialchars(ucfirst($alias)) . '</h2>';
            
            if (!empty($data)) {
                $html .= '<table>
                    <thead>
                        <tr>';
                
                // Headers
                foreach (array_keys($data[0]) as $column) {
                    $html .= '<th>' . htmlspecialchars($column) . '</th>';
                }
                
                $html .= '</tr>
                    </thead>
                    <tbody>';
                
                // Data rows
                foreach ($data as $row) {
                    $html .= '<tr>';
                    foreach ($row as $value) {
                        $html .= '<td>' . htmlspecialchars($value) . '</td>';
                    }
                    $html .= '</tr>';
                }
                
                $html .= '</tbody>
                </table>';
            } else {
                $html .= '<p>No data available</p>';
            }
            
            $html .= '</div>';
        }
        
        // Add chart placeholders if configured
        $chartConfig = json_decode($template['chart_config'] ?? '{}', true);
        if (!empty($chartConfig)) {
            $html .= '<div class="section">
                <h2>Charts</h2>';
            
            foreach ($chartConfig as $chart) {
                $html .= '<div class="chart-placeholder">
                    Chart: ' . htmlspecialchars($chart['title'] ?? 'Untitled Chart') . '
                </div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</body>
</html>';
        
        return $html;
    }
    
    /**
     * Generate CSV content
     */
    private function generateCSVContent($reportData, $template)
    {
        $csv = '';
        
        foreach ($reportData as $alias => $data) {
            $csv .= "\n" . strtoupper($alias) . "\n";
            
            if (!empty($data)) {
                // Headers
                $csv .= implode(',', array_keys($data[0])) . "\n";
                
                // Data rows
                foreach ($data as $row) {
                    $csvRow = [];
                    foreach ($row as $value) {
                        $csvRow[] = '"' . str_replace('"', '""', $value) . '"';
                    }
                    $csv .= implode(',', $csvRow) . "\n";
                }
            } else {
                $csv .= "No data available\n";
            }
        }
        
        return $csv;
    }
    
    /**
     * Update report instance
     */
    private function updateReportInstance($reportInstanceId, $filePath, $status)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE report_instances 
                SET file_path = ?, status = ?, generated_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$filePath, $status, $reportInstanceId]);
        } catch (Exception $e) {
            error_log("Error updating report instance: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get report instances
     */
    public function getReportInstances($userId = null, $limit = 20)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ri.*, rt.template_name, u.full_name as generated_by_name
                FROM report_instances ri
                JOIN report_templates rt ON ri.template_id = rt.id
                JOIN users u ON ri.generated_by = u.id
                WHERE ri.generated_by = ? OR rt.is_public = 1
                ORDER BY ri.generated_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId ?? $_SESSION['user_id'], $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting report instances: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Download report file
     */
    public function downloadReport($reportInstanceId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM report_instances 
                WHERE id = ? AND (generated_by = ? OR template_id IN (
                    SELECT id FROM report_templates WHERE is_public = 1
                ))
            ");
            $stmt->execute([$reportInstanceId, $_SESSION['user_id']]);
            $report = $stmt->fetch();
            
            if (!$report) {
                return ['success' => false, 'message' => 'Report not found or access denied'];
            }
            
            if ($report['status'] !== 'completed') {
                return ['success' => false, 'message' => 'Report is not ready for download'];
            }
            
            $filePath = __DIR__ . "/../.." . $report['file_path'];
            if (!file_exists($filePath)) {
                return ['success' => false, 'message' => 'Report file not found'];
            }
            
            return [
                'success' => true,
                'file_path' => $filePath,
                'file_name' => basename($report['file_path']),
                'file_type' => $report['file_type']
            ];
        } catch (Exception $e) {
            error_log("Error downloading report: " . $e->getMessage());
            return ['success' => false, 'message' => 'Download failed'];
        }
    }
    
    /**
     * Delete report instance
     */
    public function deleteReportInstance($reportInstanceId)
    {
        try {
            // Get report info
            $stmt = $this->db->prepare("
                SELECT * FROM report_instances 
                WHERE id = ? AND generated_by = ?
            ");
            $stmt->execute([$reportInstanceId, $_SESSION['user_id']]);
            $report = $stmt->fetch();
            
            if (!$report) {
                return ['success' => false, 'message' => 'Report not found or access denied'];
            }
            
            // Delete file if exists
            if ($report['file_path']) {
                $filePath = __DIR__ . "/../.." . $report['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Delete database record
            $stmt = $this->db->prepare("
                DELETE FROM report_instances 
                WHERE id = ? AND generated_by = ?
            ");
            $result = $stmt->execute([$reportInstanceId, $_SESSION['user_id']]);
            
            return $result ? 
                ['success' => true, 'message' => 'Report deleted successfully'] : 
                ['success' => false, 'message' => 'Failed to delete report'];
        } catch (Exception $e) {
            error_log("Error deleting report instance: " . $e->getMessage());
            return ['success' => false, 'message' => 'Delete failed'];
        }
    }
    
    /**
     * Get predefined report templates
     */
    public function getPredefinedTemplates()
    {
        return [
            [
                'template_name' => 'Sales Performance Report',
                'description' => 'Comprehensive sales performance analysis',
                'report_type' => 'detailed',
                'data_sources' => [
                    [
                        'alias' => 'sales_summary',
                        'query' => 'SELECT DATE(tanggal_penjualan) as date, COUNT(*) as orders, SUM(total_harga) as revenue FROM penjualan WHERE status_pembayaran = "lunas" AND tanggal_penjualan BETWEEN :start_date AND :end_date GROUP BY DATE(tanggal_penjualan)'
                    ],
                    [
                        'alias' => 'top_products',
                        'query' => 'SELECT p.nama_produk, SUM(dp.jumlah) as quantity, SUM(dp.total_harga) as revenue FROM detail_penjualan dp JOIN produk p ON dp.id_produk = p.id JOIN penjualan pen ON dp.id_penjualan = pen.id WHERE pen.status_pembayaran = "lunas" AND pen.tanggal_penjualan BETWEEN :start_date AND :end_date GROUP BY p.id ORDER BY revenue DESC LIMIT 10'
                    ]
                ],
                'filters' => [
                    'start_date' => date('Y-m-01'),
                    'end_date' => date('Y-m-t')
                ],
                'columns_config' => [
                    'sales_summary' => ['date', 'orders', 'revenue'],
                    'top_products' => ['nama_produk', 'quantity', 'revenue']
                ]
            ],
            [
                'template_name' => 'Customer Analytics Report',
                'description' => 'Customer behavior and analytics',
                'report_type' => 'summary',
                'data_sources' => [
                    [
                        'alias' => 'customer_summary',
                        'query' => 'SELECT u.full_name, COUNT(p.id) as orders, SUM(p.total_harga) as total_spent, MAX(p.tanggal_penjualan) as last_order FROM users u LEFT JOIN penjualan p ON u.id = p.user_id AND p.status_pembayaran = "lunas" WHERE u.is_active = 1 GROUP BY u.id ORDER BY total_spent DESC'
                    ]
                ],
                'filters' => [],
                'columns_config' => [
                    'customer_summary' => ['full_name', 'orders', 'total_spent', 'last_order']
                ]
            ],
            [
                'template_name' => 'Commission Report',
                'description' => 'Commission calculations and payments',
                'report_type' => 'detailed',
                'data_sources' => [
                    [
                        'alias' => 'commission_summary',
                        'query' => 'SELECT u.full_name, r.name as role, COUNT(cc.id) as commissions, SUM(cc.commission_amount) as total_commission, cc.status FROM commission_calculations cc JOIN users u ON cc.user_id = u.id JOIN roles r ON u.role_id = r.id WHERE cc.calculation_date BETWEEN :start_date AND :end_date GROUP BY u.id'
                    ]
                ],
                'filters' => [
                    'start_date' => date('Y-m-01'),
                    'end_date' => date('Y-m-t')
                ],
                'columns_config' => [
                    'commission_summary' => ['full_name', 'role', 'commissions', 'total_commission', 'status']
                ]
            ]
        ];
    }
    
    /**
     * Schedule report generation
     */
    public function scheduleReport($templateId, $scheduleConfig)
    {
        try {
            // This would integrate with a job scheduler
            // For now, we'll just update the template with schedule config
            
            $stmt = $this->db->prepare("
                UPDATE report_templates 
                SET schedule_config = ?
                WHERE id = ? AND created_by = ?
            ");
            
            $result = $stmt->execute([
                json_encode($scheduleConfig),
                $templateId,
                $_SESSION['user_id']
            ]);
            
            return $result ? 
                ['success' => true, 'message' => 'Report scheduled successfully'] : 
                ['success' => false, 'message' => 'Failed to schedule report'];
        } catch (Exception $e) {
            error_log("Error scheduling report: " . $e->getMessage());
            return ['success' => false, 'message' => 'Scheduling failed'];
        }
    }
}
