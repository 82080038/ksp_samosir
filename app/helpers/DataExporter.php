<?php
/**
 * Data Export Helper Class
 * Provides functionality to export data to Excel and PDF formats
 */

class DataExporter {
    private $data;
    private $filename;
    private $headers;
    
    public function __construct($data, $filename, $headers = []) {
        $this->data = $data;
        $this->filename = $filename;
        $this->headers = $headers;
    }
    
    /**
     * Export data to Excel format
     */
    public function toExcel() {
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->filename . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Start output buffering
        $output = fopen('php://output', 'w');
        
        // Add BOM for proper UTF-8 encoding in Excel
        fwrite($output, "\xEF\xBB\xBF");
        
        // Add headers if provided
        if (!empty($this->headers)) {
            fputcsv($output, $this->headers, "\t");
        }
        
        // Add data rows
        foreach ($this->data as $row) {
            // Ensure row is array
            if (is_object($row)) {
                $row = (array) $row;
            }
            
            // Clean data for Excel
            $cleanRow = array_map(function($value) {
                // Remove HTML tags and escape special characters
                $value = strip_tags($value);
                $value = str_replace(["\r", "\n", "\t"], ' ', $value);
                return $value;
            }, $row);
            
            fputcsv($output, $cleanRow, "\t");
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export data to CSV format
     */
    public function toCSV() {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $this->filename . '.csv"');
        header('Cache-Control: max-age=0');
        
        $output = fopen('php://output', 'w');
        
        // Add headers if provided
        if (!empty($this->headers)) {
            fputcsv($output, $this->headers);
        }
        
        // Add data rows
        foreach ($this->data as $row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export data to PDF format (Basic implementation)
     */
    public function toPDF() {
        // For now, redirect to CSV as PDF requires external library
        // In production, you would use TCPDF or FPDF
        $this->toCSV();
    }
    
    /**
     * Create exporter from database query
     */
    public static function fromQuery($sql, $filename, $headers = [], $params = []) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return new self($data, $filename, $headers);
        } catch (PDOException $e) {
            throw new Exception('Query failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Create exporter from array
     */
    public static function fromArray($data, $filename, $headers = []) {
        return new self($data, $filename, $headers);
    }
}

/**
 * Usage Examples:
 * 
 * // Export from database query
 * $exporter = DataExporter::fromQuery(
 *     "SELECT id, nama, email, telepon FROM anggota WHERE status = ?",
 *     'data_anggota',
 *     ['ID', 'Nama', 'Email', 'Telepon'],
 *     ['active']
 * );
 * $exporter->toExcel();
 * 
 * // Export from array
 * $data = [
 *     ['id' => 1, 'nama' => 'John Doe', 'email' => 'john@example.com'],
 *     ['id' => 2, 'nama' => 'Jane Smith', 'email' => 'jane@example.com']
 * ];
 * $exporter = DataExporter::fromArray($data, 'users', ['ID', 'Name', 'Email']);
 * $exporter->toExcel();
 */

?>
