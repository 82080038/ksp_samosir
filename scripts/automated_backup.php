<?php
/**
 * Automated Backup System for KSP Samosir
 * Handles scheduled database backups with verification and cleanup
 */

// Configuration
class BackupConfig {
    const DB_HOST = 'localhost';
    const DB_USER = 'root';
    const DB_PASS = 'root';
    const DB_NAME = 'ksp_samosir';

    const BACKUP_DIR = __DIR__ . '/../../backups/';
    const LOG_DIR = __DIR__ . '/../../logs/';

    const RETENTION_DAYS = 30; // Keep backups for 30 days
    const MAX_BACKUPS = 50;    // Maximum number of backup files

    const CRON_SCHEDULE = '0 2 * * *'; // Daily at 2 AM

    // Email notifications (if SMTP configured)
    const NOTIFY_EMAIL = 'admin@ksp-samosir.com';
    const SMTP_ENABLED = false;
}

/**
 * Database Backup Manager
 */
class DatabaseBackup {
    private $config;

    public function __construct() {
        $this->config = new BackupConfig();
        $this->ensureDirectoriesExist();
    }

    /**
     * Create a full database backup
     */
    public function createBackup($type = 'full', $description = '') {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$type}_{$timestamp}.sql";
        $filepath = $this->config::BACKUP_DIR . $filename;

        try {
            $this->log("Starting {$type} backup: {$filename}");

            // Create mysqldump command
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --single-transaction --routines --triggers %s > %s 2>> %s',
                escapeshellarg($this->config::DB_USER),
                escapeshellarg($this->config::DB_PASS),
                escapeshellarg($this->config::DB_HOST),
                escapeshellarg($this->config::DB_NAME),
                escapeshellarg($filepath),
                escapeshellarg($this->config::LOG_DIR . 'backup_error.log')
            );

            $output = [];
            $return_var = 0;
            exec($command, $output, $return_var);

            if ($return_var !== 0) {
                throw new Exception("mysqldump failed with return code: {$return_var}");
            }

            // Compress the backup
            $compressed_file = $this->compressBackup($filepath);
            if ($compressed_file) {
                unlink($filepath); // Remove uncompressed file
                $filepath = $compressed_file;
                $filename = basename($compressed_file);
            }

            // Get file size
            $file_size = filesize($filepath);

            // Record backup in database
            $this->recordBackup($filename, $type, $file_size, $description);

            // Verify backup integrity
            if (!$this->verifyBackup($filepath)) {
                throw new Exception("Backup verification failed");
            }

            // Cleanup old backups
            $this->cleanupOldBackups();

            $this->log("Backup completed successfully: {$filename} ({$this->formatBytes($file_size)})");

            // Send notification if configured
            $this->sendNotification('success', "Backup completed: {$filename}", "File size: {$this->formatBytes($file_size)}");

            return [
                'success' => true,
                'filename' => $filename,
                'size' => $file_size,
                'path' => $filepath
            ];

        } catch (Exception $e) {
            $this->log("Backup failed: " . $e->getMessage(), 'error');

            // Send failure notification
            $this->sendNotification('error', "Backup failed: {$filename}", $e->getMessage());

            // Clean up failed backup file if it exists
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Restore database from backup
     */
    public function restoreBackup($filename) {
        $filepath = $this->config::BACKUP_DIR . $filename;

        if (!file_exists($filepath)) {
            throw new Exception("Backup file not found: {$filename}");
        }

        try {
            $this->log("Starting restore from: {$filename}");

            // Create pre-restore backup
            $this->createBackup('pre_restore', "Pre-restore backup before restoring {$filename}");

            // Decompress if needed
            $sql_file = $filepath;
            if (pathinfo($filepath, PATHINFO_EXTENSION) === 'gz') {
                $sql_file = $this->decompressBackup($filepath);
            }

            // Restore command
            $command = sprintf(
                'mysql --user=%s --password=%s --host=%s %s < %s 2>> %s',
                escapeshellarg($this->config::DB_USER),
                escapeshellarg($this->config::DB_PASS),
                escapeshellarg($this->config::DB_HOST),
                escapeshellarg($this->config::DB_NAME),
                escapeshellarg($sql_file),
                escapeshellarg($this->config::LOG_DIR . 'restore_error.log')
            );

            $output = [];
            $return_var = 0;
            exec($command, $output, $return_var);

            // Clean up decompressed file if created
            if ($sql_file !== $filepath) {
                unlink($sql_file);
            }

            if ($return_var !== 0) {
                throw new Exception("mysql restore failed with return code: {$return_var}");
            }

            // Record restore operation
            $this->recordRestore($filename);

            $this->log("Restore completed successfully from: {$filename}");
            $this->sendNotification('success', "Restore completed from: {$filename}", "Database has been restored successfully");

            return ['success' => true];

        } catch (Exception $e) {
            $this->log("Restore failed: " . $e->getMessage(), 'error');
            $this->sendNotification('error', "Restore failed from: {$filename}", $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Compress backup file using gzip
     */
    private function compressBackup($filepath) {
        $compressed_file = $filepath . '.gz';

        $command = "gzip -c " . escapeshellarg($filepath) . " > " . escapeshellarg($compressed_file);

        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        if ($return_var === 0 && file_exists($compressed_file)) {
            return $compressed_file;
        }

        return false;
    }

    /**
     * Decompress backup file
     */
    private function decompressBackup($filepath) {
        $decompressed_file = preg_replace('/\.gz$/', '', $filepath);

        $command = "gzip -dc " . escapeshellarg($filepath) . " > " . escapeshellarg($decompressed_file);

        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        if ($return_var === 0 && file_exists($decompressed_file)) {
            return $decompressed_file;
        }

        return false;
    }

    /**
     * Verify backup file integrity
     */
    private function verifyBackup($filepath) {
        try {
            // For SQL files, check if they contain valid SQL structure
            if (pathinfo($filepath, PATHINFO_EXTENSION) === 'sql' ||
                pathinfo($filepath, PATHINFO_EXTENSION) === 'gz') {

                $sql_file = $filepath;
                if (pathinfo($filepath, PATHINFO_EXTENSION) === 'gz') {
                    $sql_file = $this->decompressBackup($filepath);
                }

                // Check if file contains CREATE TABLE statements
                $content = file_get_contents($sql_file, false, null, 0, 1024);
                $has_tables = strpos($content, 'CREATE TABLE') !== false;

                // Clean up decompressed file
                if ($sql_file !== $filepath) {
                    unlink($sql_file);
                }

                return $has_tables;
            }

            return true; // For other file types, assume valid

        } catch (Exception $e) {
            $this->log("Backup verification error: " . $e->getMessage(), 'warning');
            return false;
        }
    }

    /**
     * Record backup in database
     */
    private function recordBackup($filename, $type, $size, $description) {
        $conn = getLegacyConnection();
        $stmt = $conn->prepare("INSERT INTO backup_files (filename, type, description, file_size, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $created_by = 1; // System user
        $stmt->bind_param('sssdi', $filename, $type, $description, $size, $created_by);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }

    /**
     * Record restore operation
     */
    private function recordRestore($filename) {
        $conn = getLegacyConnection();
        $stmt = $conn->prepare("INSERT INTO backup_logs (action, filename, status, details, performed_by, performed_at) VALUES (?, ?, 'completed', 'Database restored from backup', ?, NOW())");
        $performed_by = 1; // System user
        $stmt->bind_param('ssi', 'restore', $filename, $performed_by);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }

    /**
     * Cleanup old backup files
     */
    private function cleanupOldBackups() {
        // Get list of backup files older than retention period
        $files = glob($this->config::BACKUP_DIR . '*.sql.gz');
        $files = array_merge($files, glob($this->config::BACKUP_DIR . '*.sql'));

        $deleted_count = 0;
        $oldest_date = strtotime('-' . $this->config::RETENTION_DAYS . ' days');

        foreach ($files as $file) {
            if (filemtime($file) < $oldest_date) {
                if (unlink($file)) {
                    $deleted_count++;
                    $this->log("Deleted old backup: " . basename($file));
                }
            }
        }

        // Also limit total number of backups
        if (count($files) > $this->config::MAX_BACKUPS) {
            // Sort by modification time (oldest first)
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            $to_delete = count($files) - $this->config::MAX_BACKUPS;
            for ($i = 0; $i < $to_delete; $i++) {
                if (unlink($files[$i])) {
                    $deleted_count++;
                    $this->log("Deleted excess backup: " . basename($files[$i]));
                }
            }
        }

        if ($deleted_count > 0) {
            $this->log("Cleanup completed: {$deleted_count} old backup files removed");
        }
    }

    /**
     * Get backup statistics
     */
    public function getBackupStats() {
        $stats = [];

        // Count total backups
        $stats['total_backups'] = count(glob($this->config::BACKUP_DIR . '*.sql*'));

        // Get total size
        $total_size = 0;
        $files = glob($this->config::BACKUP_DIR . '*.sql*');
        foreach ($files as $file) {
            $total_size += filesize($file);
        }
        $stats['total_size'] = $total_size;

        // Get last backup date
        $last_backup = 0;
        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ($mtime > $last_backup) {
                $last_backup = $mtime;
            }
        }
        $stats['last_backup'] = $last_backup > 0 ? date('Y-m-d H:i:s', $last_backup) : null;

        // Get oldest backup
        $oldest_backup = time();
        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ($mtime < $oldest_backup) {
                $oldest_backup = $mtime;
            }
        }
        $stats['oldest_backup'] = $oldest_backup < time() ? date('Y-m-d H:i:s', $oldest_backup) : null;

        return $stats;
    }

    /**
     * Send notification (placeholder for email/SMS integration)
     */
    private function sendNotification($type, $subject, $message) {
        // Log notification
        $this->log("Notification ({$type}): {$subject} - {$message}");

        // In production, integrate with email/SMS service
        if ($this->config::SMTP_ENABLED) {
            // Send email notification
            // mail($this->config::NOTIFY_EMAIL, $subject, $message);
        }
    }

    /**
     * Log backup operations
     */
    private function log($message, $level = 'info') {
        $log_file = $this->config::LOG_DIR . 'backup_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [{$level}] {$message}\n";

        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Ensure required directories exist
     */
    private function ensureDirectoriesExist() {
        if (!is_dir($this->config::BACKUP_DIR)) {
            mkdir($this->config::BACKUP_DIR, 0755, true);
        }
        if (!is_dir($this->config::LOG_DIR)) {
            mkdir($this->config::LOG_DIR, 0755, true);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

/**
 * Cron job script for automated backups
 * This should be called by cron scheduler
 */
class BackupScheduler {
    public static function runScheduledBackup() {
        $backup = new DatabaseBackup();

        // Check if backup should run today
        if (self::shouldRunBackup()) {
            $result = $backup->createBackup('scheduled', 'Automated scheduled backup');

            if ($result['success']) {
                echo "Scheduled backup completed: {$result['filename']}\n";
            } else {
                echo "Scheduled backup failed: {$result['error']}\n";
                exit(1);
            }
        } else {
            echo "Scheduled backup skipped (not due today)\n";
        }
    }

    private static function shouldRunBackup() {
        // Check if backup already exists for today
        $backup = new DatabaseBackup();
        $stats = $backup->getBackupStats();

        if ($stats['last_backup']) {
            $last_backup_date = date('Y-m-d', strtotime($stats['last_backup']));
            $today = date('Y-m-d');

            return $last_backup_date !== $today;
        }

        return true; // No previous backup, so run one
    }
}

// Usage examples:
//
// 1. Manual backup
// $backup = new DatabaseBackup();
// $result = $backup->createBackup('manual', 'Manual backup from admin panel');
//
// 2. Scheduled backup (for cron)
// BackupScheduler::runScheduledBackup();
//
// 3. Restore from backup
// $backup = new DatabaseBackup();
// $result = $backup->restoreBackup('backup_full_2024-01-15_14-30-00.sql.gz');
//
// 4. Get backup statistics
// $backup = new DatabaseBackup();
// $stats = $backup->getBackupStats();
?>
