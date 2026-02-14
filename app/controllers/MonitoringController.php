<?php
require_once __DIR__ . '/BaseController.php';

/**
 * MonitoringController handles system monitoring, performance tracking, and health checks.
 * Provides real-time monitoring of database, application, and system performance.
 */
class MonitoringController extends BaseController {
    /**
     * Display monitoring dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        $system_status = $this->getSystemStatus();
        $performance_metrics = $this->getPerformanceMetrics();
        $recent_alerts = $this->getRecentAlerts();
        $health_checks = $this->getHealthChecks();

        $this->render(__DIR__ . '/../views/monitoring/index.php', [
            'system_status' => $system_status,
            'performance_metrics' => $performance_metrics,
            'recent_alerts' => $recent_alerts,
            'health_checks' => $health_checks
        ]);
    }

    /**
     * Run health checks.
     */
    public function runHealthChecks() {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        $results = [];

        // Database health check
        $results['database'] = $this->checkDatabaseHealth();

        // Application health check
        $results['application'] = $this->checkApplicationHealth();

        // File system health check
        $results['filesystem'] = $this->checkFileSystemHealth();

        // Memory and CPU health check
        $results['system'] = $this->checkSystemHealth();

        // Store health check results
        $this->storeHealthCheckResults($results);

        flashMessage('success', 'Health checks completed. ' . $this->countIssues($results) . ' issues found.');
        redirect('monitoring/index');
    }

    /**
     * Get performance report.
     */
    public function performanceReport() {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        $period = $_GET['period'] ?? '24h';
        $metrics = $this->getPerformanceReport($period);

        $this->render(__DIR__ . '/../views/monitoring/performance_report.php', [
            'metrics' => $metrics,
            'period' => $period
        ]);
    }

    /**
     * View system logs.
     */
    public function logs() {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $level = $_GET['level'] ?? 'all';
        $category = $_GET['category'] ?? 'all';

        $where_conditions = [];
        $params = [];

        if ($level !== 'all') {
            $where_conditions[] = 'log_level = ?';
            $params[] = $level;
        }

        if ($category !== 'all') {
            $where_conditions[] = 'category = ?';
            $params[] = $category;
        }

        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        $total = fetchRow("SELECT COUNT(*) as count FROM application_logs {$where_clause}", $params)['count'];
        $totalPages = ceil($total / $perPage);

        $logs = fetchAll("SELECT al.*, u.full_name as user_name FROM application_logs al LEFT JOIN users u ON al.user_id = u.id {$where_clause} ORDER BY al.logged_at DESC LIMIT ? OFFSET ?", array_merge($params, [$perPage, $offset]), str_repeat('s', count($params)) . 'ii');

        $this->render(__DIR__ . '/../views/monitoring/logs.php', [
            'logs' => $logs,
            'page' => $page,
            'totalPages' => $totalPages,
            'level' => $level,
            'category' => $category
        ]);
    }

    /**
     * View alerts.
     */
    public function alerts() {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $status = $_GET['status'] ?? 'active';

        $total = fetchRow("SELECT COUNT(*) as count FROM alerts WHERE status = ?", [$status], 's')['count'];
        $totalPages = ceil($total / $perPage);

        $alerts = fetchAll("SELECT a.*, u1.full_name as acknowledged_by_name, u2.full_name as resolved_by_name FROM alerts a LEFT JOIN users u1 ON a.acknowledged_by = u1.id LEFT JOIN users u2 ON a.resolved_by = u2.id WHERE a.status = ? ORDER BY a.created_at DESC LIMIT ? OFFSET ?", [$status, $perPage, $offset], 'sii');

        $this->render(__DIR__ . '/../views/monitoring/alerts.php', [
            'alerts' => $alerts,
            'page' => $page,
            'totalPages' => $totalPages,
            'status' => $status
        ]);
    }

    /**
     * Acknowledge alert.
     */
    public function acknowledgeAlert($alert_id) {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        runInTransaction(function($conn) use ($alert_id) {
            $acknowledged_by = $_SESSION['user']['id'] ?? 1;
            $stmt = $conn->prepare("UPDATE alerts SET status = 'acknowledged', acknowledged_by = ?, acknowledged_at = NOW() WHERE id = ?");
            $stmt->bind_param('ii', $acknowledged_by, $alert_id);
            $stmt->execute();
            $stmt->close();
        });

        flashMessage('success', 'Alert berhasil diakui');
        redirect('monitoring/alerts');
    }

    /**
     * Get system status.
     */
    private function getSystemStatus() {
        return [
            'database_status' => $this->checkDatabaseHealth()['status'],
            'application_status' => $this->checkApplicationHealth()['status'],
            'last_check' => date('Y-m-d H:i:s'),
            'uptime' => $this->getSystemUptime()
        ];
    }

    /**
     * Get performance metrics.
     */
    private function getPerformanceMetrics() {
        // Get recent performance data
        $query_time_avg = fetchRow("SELECT AVG(value) as avg_time FROM performance_metrics WHERE metric_type = 'query_time' AND measured_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)")['avg_time'] ?? 0;
        $page_load_avg = fetchRow("SELECT AVG(value) as avg_load FROM performance_metrics WHERE metric_type = 'page_load' AND measured_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)")['avg_load'] ?? 0;
        $memory_usage = fetchRow("SELECT AVG(value) as avg_memory FROM performance_metrics WHERE metric_type = 'memory_usage' AND measured_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)")['avg_memory'] ?? 0;

        return [
            'avg_query_time' => round($query_time_avg, 2),
            'avg_page_load' => round($page_load_avg, 2),
            'avg_memory_usage' => round($memory_usage, 2),
            'period' => 'Last 1 hour'
        ];
    }

    /**
     * Get recent alerts.
     */
    private function getRecentAlerts() {
        return fetchAll("SELECT * FROM alerts WHERE status = 'active' ORDER BY created_at DESC LIMIT 5");
    }

    /**
     * Get health checks.
     */
    private function getHealthChecks() {
        return fetchAll("SELECT * FROM system_health_checks ORDER BY last_check DESC LIMIT 10");
    }

    /**
     * Check database health.
     */
    private function checkDatabaseHealth() {
        $start_time = microtime(true);

        try {
            // Test basic connectivity
            $conn = getConnection();

            // Test query execution
            $result = $conn->query("SELECT 1");

            // Test complex query
            $complex_result = fetchRow("SELECT COUNT(*) as count FROM anggota");

            $response_time = (microtime(true) - $start_time) * 1000; // Convert to milliseconds

            $this->storeMonitoringData('database', 'health_check', 'healthy', $response_time, null, null, null, 'Database connection and queries working properly');

            return [
                'status' => 'healthy',
                'response_time' => round($response_time, 2),
                'message' => 'Database operational',
                'details' => ['record_count' => $complex_result['count']]
            ];

        } catch (Exception $e) {
            $response_time = (microtime(true) - $start_time) * 1000;

            $this->storeMonitoringData('database', 'health_check', 'error', $response_time, null, null, null, $e->getMessage());

            return [
                'status' => 'error',
                'response_time' => round($response_time, 2),
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check application health.
     */
    private function checkApplicationHealth() {
        $start_time = microtime(true);

        try {
            // Test critical files exist
            $critical_files = [
                __DIR__ . '/../../config/config.php',
                __DIR__ . '/../../shared/php/helpers.php',
                __DIR__ . '/BaseController.php'
            ];

            $missing_files = [];
            foreach ($critical_files as $file) {
                if (!file_exists($file)) {
                    $missing_files[] = basename($file);
                }
            }

            if (!empty($missing_files)) {
                throw new Exception('Missing critical files: ' . implode(', ', $missing_files));
            }

            // Test session handling
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $response_time = (microtime(true) - $start_time) * 1000;

            $this->storeMonitoringData('application', 'health_check', 'healthy', $response_time, null, null, null, 'Application files and session handling working properly');

            return [
                'status' => 'healthy',
                'response_time' => round($response_time, 2),
                'message' => 'Application operational'
            ];

        } catch (Exception $e) {
            $response_time = (microtime(true) - $start_time) * 1000;

            $this->storeMonitoringData('application', 'health_check', 'error', $response_time, null, null, null, $e->getMessage());

            return [
                'status' => 'error',
                'response_time' => round($response_time, 2),
                'message' => 'Application error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check file system health.
     */
    private function checkFileSystemHealth() {
        try {
            $upload_dir = __DIR__ . '/../../public/uploads/';
            $log_dir = __DIR__ . '/../../logs/';

            // Check if directories exist and are writable
            $checks = [
                'uploads_dir' => is_writable($upload_dir),
                'logs_dir' => is_writable($log_dir),
                'temp_dir' => is_writable(sys_get_temp_dir())
            ];

            $failed_checks = array_filter($checks, function($check) { return !$check; });

            if (!empty($failed_checks)) {
                $failed_items = array_keys($failed_checks);
                throw new Exception('File system issues: ' . implode(', ', $failed_items) . ' not writable');
            }

            // Check disk usage
            $disk_usage = $this->getDiskUsage();
            if ($disk_usage > 90) {
                $this->createAlert('high', 'Disk usage critical', 'Disk usage is above 90%');
            }

            return [
                'status' => 'healthy',
                'message' => 'File system healthy',
                'disk_usage' => $disk_usage
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'File system error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check system health.
     */
    private function checkSystemHealth() {
        try {
            // Get memory usage
            $memory_usage = memory_get_peak_usage(true) / 1024 / 1024; // MB

            // Get CPU usage (simplified)
            $cpu_usage = $this->getCpuUsage();

            if ($memory_usage > 256) { // More than 256MB
                $this->createAlert('medium', 'High memory usage', 'Application memory usage is above 256MB');
            }

            return [
                'status' => 'healthy',
                'memory_usage' => round($memory_usage, 2),
                'cpu_usage' => $cpu_usage,
                'message' => 'System resources normal'
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'System health check failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Store monitoring data.
     */
    private function storeMonitoringData($check_type, $check_name, $status, $response_time, $memory_usage, $cpu_usage, $disk_usage, $error_message = null) {
        runInTransaction(function($conn) use ($check_type, $check_name, $status, $response_time, $memory_usage, $cpu_usage, $disk_usage, $error_message) {
            $stmt = $conn->prepare("INSERT INTO system_monitoring (check_type, check_name, status, response_time, memory_usage, cpu_usage, disk_usage, error_message, checked_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param('sssdddds', $check_type, $check_name, $status, $response_time, $memory_usage, $cpu_usage, $disk_usage, $error_message);
            $stmt->execute();
            $stmt->close();
        });
    }

    /**
     * Store health check results.
     */
    private function storeHealthCheckResults($results) {
        runInTransaction(function($conn) use ($results) {
            foreach ($results as $category => $result) {
                $stmt = $conn->prepare("INSERT INTO system_health_checks (check_name, check_category, last_check, status, response_time, details) VALUES (?, ?, NOW(), ?, ?, ?) ON DUPLICATE KEY UPDATE last_check = NOW(), status = VALUES(status), response_time = VALUES(response_time), details = VALUES(details)");
                $stmt->bind_param('sssds', $category . '_check', $category, $result['status'], $result['response_time'] ?? 0, json_encode($result));
                $stmt->execute();
                $stmt->close();
            }
        });
    }

    /**
     * Create alert.
     */
    private function createAlert($severity, $title, $message) {
        runInTransaction(function($conn) use ($severity, $title, $message) {
            $stmt = $conn->prepare("INSERT INTO alerts (alert_type, severity, title, message, status) VALUES ('system', ?, ?, ?, 'active')");
            $stmt->bind_param('sss', $severity, $title, $message);
            $stmt->execute();
            $stmt->close();
        });
    }

    /**
     * Get system uptime.
     */
    private function getSystemUptime() {
        // Simplified uptime calculation
        return 'System operational';
    }

    /**
     * Get disk usage.
     */
    private function getDiskUsage() {
        $disk_total = disk_total_space('/');
        $disk_free = disk_free_space('/');
        $disk_used = $disk_total - $disk_free;
        return round(($disk_used / $disk_total) * 100, 1);
    }

    /**
     * Get CPU usage.
     */
    private function getCpuUsage() {
        // Simplified CPU usage - in production, use system calls
        return rand(10, 30); // Mock data
    }

    /**
     * Count issues in health check results.
     */
    private function countIssues($results) {
        $issues = 0;
        foreach ($results as $result) {
            if ($result['status'] !== 'healthy') {
                $issues++;
            }
        }
        return $issues;
    }

    /**
     * Get performance report.
     */
    private function getPerformanceReport($period) {
        // Convert period to hours
        $hours = match($period) {
            '1h' => 1,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24
        };

        $metrics = fetchAll("SELECT metric_type, metric_name, AVG(value) as avg_value, MIN(value) as min_value, MAX(value) as max_value, COUNT(*) as count FROM performance_metrics WHERE measured_at >= DATE_SUB(NOW(), INTERVAL ? HOUR) GROUP BY metric_type, metric_name ORDER BY metric_type, metric_name", [$hours], 'i');

        return $metrics;
    }
}
