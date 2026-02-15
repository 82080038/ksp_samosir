<?php
require_once __DIR__ . '/BaseController.php';

/**
 * BackupController handles database backups and disaster recovery.
 * Provides automated backup creation, scheduling, and restoration.
 */
class BackupController extends BaseController {
    private $backup_path = __DIR__ . '/../../backups/';
    private $db_config;

    public function __construct() {
        $this->db_config = [
            'host' => DB_HOST,
            'user' => DB_USER,
            'pass' => DB_PASS,
            'name' => DB_NAME
        ];
        
        // Create backup directory if not exists
        if (!is_dir($this->backup_path)) {
            @mkdir($this->backup_path, 0755, true);
        }
        // Fallback to tmp if backup dir is not writable
        if (!is_dir($this->backup_path) || !is_writable($this->backup_path)) {
            $this->backup_path = sys_get_temp_dir() . '/ksp_samosir_backups/';
            if (!is_dir($this->backup_path)) {
                @mkdir($this->backup_path, 0755, true);
            }
        }
    }

    /**
     * Display backup management dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        $backups = $this->listBackups();
        $backup_stats = $this->getBackupStats();
        $scheduled_backups = $this->getScheduledBackups();

        $this->render('backup/index', [
            'backups' => $backups,
            'backup_stats' => $backup_stats,
            'scheduled_backups' => $scheduled_backups
        ]);
    }

    /**
     * Create a new database backup.
     */
    public function createBackup() {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        $backup_type = $_POST['backup_type'] ?? 'full';
        $description = sanitize($_POST['description'] ?? '');

        try {
            $backup_file = $this->performBackup($backup_type, $description);
            
            // Log backup creation
            $this->logBackupActivity('create', $backup_file, 'success', $description);
            
            flashMessage('success', 'Backup berhasil dibuat: ' . basename($backup_file));
        } catch (Exception $e) {
            $this->logBackupActivity('create', '', 'failed', $e->getMessage());
            flashMessage('error', 'Gagal membuat backup: ' . $e->getMessage());
        }

        redirect('backup');
    }

    /**
     * Download a backup file.
     */
    public function downloadBackup($filename) {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        $file_path = $this->backup_path . $filename;
        
        if (!file_exists($file_path)) {
            flashMessage('error', 'File backup tidak ditemukan');
            redirect('backup');
        }

        // Check if file is within backup directory
        $real_path = realpath($file_path);
        $real_backup_path = realpath($this->backup_path);
        
        if (strpos($real_path, $real_backup_path) !== 0) {
            flashMessage('error', 'Akses file tidak diizinkan');
            redirect('backup');
        }

        // Download file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup($filename) {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        $file_path = $this->backup_path . $filename;
        
        if (!file_exists($file_path)) {
            flashMessage('error', 'File backup tidak ditemukan');
            redirect('backup');
        }

        if (unlink($file_path)) {
            $this->logBackupActivity('delete', $filename, 'success');
            flashMessage('success', 'Backup berhasil dihapus');
        } else {
            $this->logBackupActivity('delete', $filename, 'failed');
            flashMessage('error', 'Gagal menghapus backup');
        }

        redirect('backup');
    }

    /**
     * Restore database from backup.
     */
    public function restoreBackup($filename) {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render('backup/restore_confirm', [
                'filename' => $filename
            ]);
            return;
        }

        $confirmation = $_POST['confirm_restore'] ?? '';
        
        if ($confirmation !== 'RESTORE') {
            flashMessage('error', 'Konfirmasi restore tidak valid');
            redirect('backup');
        }

        try {
            $this->performRestore($filename);
            $this->logBackupActivity('restore', $filename, 'success');
            flashMessage('success', 'Database berhasil direstore dari backup: ' . $filename);
        } catch (Exception $e) {
            $this->logBackupActivity('restore', $filename, 'failed', $e->getMessage());
            flashMessage('error', 'Gagal merestore database: ' . $e->getMessage());
        }

        redirect('backup');
    }

    /**
     * Schedule automatic backups.
     */
    public function scheduleBackup() {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $frequency = sanitize($_POST['frequency'] ?? 'daily');
            $time = sanitize($_POST['time'] ?? '02:00');
            $enabled = isset($_POST['enabled']) ? 1 : 0;

            // Save schedule (simplified - in production use cron or task scheduler)
            runInTransaction(function($conn) use ($frequency, $time, $enabled) {
                $stmt = $conn->prepare("INSERT INTO backup_schedules (frequency, scheduled_time, enabled, created_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE frequency = VALUES(frequency), scheduled_time = VALUES(scheduled_time), enabled = VALUES(enabled), updated_at = NOW()");
                $stmt->bind_param('ssi', $frequency, $time, $enabled);
                $stmt->execute();
                $stmt->close();
            });

            flashMessage('success', 'Jadwal backup berhasil disimpan');
            redirect('backup');
        }

        $current_schedule = fetchRow("SELECT * FROM backup_schedules ORDER BY created_at DESC LIMIT 1");

        $this->render('backup/schedule', [
            'current_schedule' => $current_schedule
        ]);
    }

    /**
     * Perform database backup.
     */
    private function performBackup($type, $description) {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$type}_{$timestamp}.sql";
        $filepath = $this->backup_path . $filename;

        // Use mysqldump command
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg($this->db_config['user']),
            escapeshellarg($this->db_config['pass']),
            escapeshellarg($this->db_config['host']),
            escapeshellarg($this->db_config['name']),
            escapeshellarg($filepath)
        );

        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            throw new Exception('mysqldump command failed');
        }

        // Compress the file
        $zip_filepath = $filepath . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zip_filepath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($filepath, basename($filepath));
            $zip->close();
            unlink($filepath); // Remove uncompressed file
            $filepath = $zip_filepath;
            $filename = basename($zip_filepath);
        }

        // Store backup info in database
        runInTransaction(function($conn) use ($filename, $type, $description) {
            $stmt = $conn->prepare("INSERT INTO backup_files (filename, type, description, file_size, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $file_size = filesize($this->backup_path . $filename);
            $created_by = $_SESSION['user']['id'] ?? 1;
            $stmt->bind_param('sssii', $filename, $type, $description, $file_size, $created_by);
            $stmt->execute();
            $stmt->close();
        });

        return $filepath;
    }

    /**
     * Perform database restore.
     */
    private function performRestore($filename) {
        $filepath = $this->backup_path . $filename;

        if (!file_exists($filepath)) {
            throw new Exception('Backup file not found');
        }

        // If compressed, extract first
        if (pathinfo($filepath, PATHINFO_EXTENSION) === 'zip') {
            $zip = new ZipArchive();
            $extract_path = $this->backup_path . 'temp_' . time() . '/';
            mkdir($extract_path);
            
            if ($zip->open($filepath) === TRUE) {
                $zip->extractTo($extract_path);
                $zip->close();
                
                // Find SQL file
                $sql_files = glob($extract_path . '*.sql');
                if (empty($sql_files)) {
                    throw new Exception('No SQL file found in archive');
                }
                $sql_filepath = $sql_files[0];
            } else {
                throw new Exception('Failed to open zip file');
            }
        } else {
            $sql_filepath = $filepath;
            $extract_path = null;
        }

        // Restore database
        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s %s < %s',
            escapeshellarg($this->db_config['user']),
            escapeshellarg($this->db_config['pass']),
            escapeshellarg($this->db_config['host']),
            escapeshellarg($this->db_config['name']),
            escapeshellarg($sql_filepath)
        );

        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        // Cleanup
        if ($extract_path) {
            array_map('unlink', glob($extract_path . '*'));
            rmdir($extract_path);
        }

        if ($return_var !== 0) {
            throw new Exception('mysql restore command failed');
        }
    }

    /**
     * List available backups.
     */
    private function listBackups() {
        try {
            return fetchAll("SELECT * FROM backup_files ORDER BY created_at DESC LIMIT 20") ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get backup statistics.
     */
    private function getBackupStats() {
        try {
            $stats = [
                'total_backups' => (fetchRow("SELECT COUNT(*) as total FROM backup_files") ?? [])['total'] ?? 0,
                'total_size' => (fetchRow("SELECT COALESCE(SUM(file_size), 0) as total FROM backup_files") ?? [])['total'] ?? 0,
                'last_backup' => (fetchRow("SELECT created_at FROM backup_files ORDER BY created_at DESC LIMIT 1") ?? [])['created_at'] ?? null,
                'oldest_backup' => (fetchRow("SELECT created_at FROM backup_files ORDER BY created_at ASC LIMIT 1") ?? [])['created_at'] ?? null,
            ];
        } catch (Exception $e) {
            $stats = ['total_backups' => 0, 'total_size' => 0, 'last_backup' => null, 'oldest_backup' => null];
        }
        return $stats;
    }

    /**
     * Get scheduled backups.
     */
    private function getScheduledBackups() {
        try {
            return fetchAll("SELECT * FROM backup_schedules WHERE enabled = 1 ORDER BY created_at DESC") ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Log backup activity.
     */
    private function logBackupActivity($action, $filename, $status, $details = '') {
        runInTransaction(function($conn) use ($action, $filename, $status, $details) {
            $stmt = $conn->prepare("INSERT INTO backup_logs (action, filename, status, details, performed_by, performed_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $performed_by = $_SESSION['user']['id'] ?? null;
            $stmt->bind_param('ssssi', $action, $filename, $status, $details, $performed_by);
            $stmt->execute();
            $stmt->close();
        });
    }
}
