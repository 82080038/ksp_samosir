# Automated Backup System Documentation

## Overview
The KSP Samosir system includes a comprehensive automated backup system that provides:
- Scheduled database backups
- Manual backup creation
- Backup verification and integrity checking
- Automated cleanup of old backups
- Web-based backup management interface
- Disaster recovery capabilities

## Components

### 1. DatabaseBackup Class (`scripts/automated_backup.php`)
Main backup engine that handles:
- Full database dumps using mysqldump
- Compression with gzip
- Backup verification
- Automatic cleanup
- Notification system (placeholder for email/SMS)

### 2. BackupController (`app/controllers/BackupController.php`)
Web interface for backup management:
- Manual backup creation
- Backup file downloads
- Backup restoration
- Schedule configuration
- Backup statistics and monitoring

### 3. Cron Job Script (`scripts/backup_cron.sh`)
Automated scheduling script:
- Prevents concurrent backup processes
- Comprehensive logging
- Error handling and recovery
- PID file management

## Features

### Automated Scheduling
```bash
# Add to crontab for daily backups at 2 AM
0 2 * * * /path/to/backup_cron.sh
```

### Manual Backup Creation
```php
$backup = new DatabaseBackup();
$result = $backup->createBackup('manual', 'Backup description');
```

### Backup Verification
- File integrity checks
- SQL syntax validation
- Compression verification
- Size validation

### Automated Cleanup
- Age-based cleanup (30 days retention)
- Count-based cleanup (max 50 files)
- Configurable retention policies

## Configuration

### Backup Settings
```php
class BackupConfig {
    const BACKUP_DIR = '/var/www/html/ksp_samosir/backups/';
    const LOG_DIR = '/var/www/html/ksp_samosir/logs/';
    const RETENTION_DAYS = 30;
    const MAX_BACKUPS = 50;
}
```

### Database Configuration
Uses existing database configuration from `config/config.php`

## Usage Examples

### Web Interface
- Access via `/backup` routes
- Manual backup creation
- Backup file management
- Schedule configuration

### Command Line
```bash
# Run scheduled backup
php scripts/automated_backup.php --scheduled

# Manual backup
php scripts/automated_backup.php
```

### Cron Setup
```bash
# Edit crontab
crontab -e

# Add line for daily backups
0 2 * * * /var/www/html/ksp_samosir/scripts/backup_cron.sh
```

## Backup File Format
- **Uncompressed**: `backup_[type]_[timestamp].sql`
- **Compressed**: `backup_[type]_[timestamp].sql.gz`
- **Timestamp Format**: `YYYY-MM-DD_HH-II-SS`

## Recovery Procedures

### Database Restoration
1. Identify backup file
2. Confirm restoration (creates pre-restore backup)
3. Execute restore command
4. Verify data integrity
5. Log restoration event

### Emergency Recovery
1. Stop application services
2. Restore from backup
3. Verify application functionality
4. Restart services
5. Monitor system health

## Monitoring and Alerts

### System Health Checks
- Database connectivity
- File system permissions
- Disk space monitoring
- Memory usage tracking

### Alert Types
- Backup failure notifications
- Disk space warnings
- Restoration completions
- Schedule execution confirmations

## Security Considerations

### File Permissions
- Backup directory: 0755 (readable by web server)
- Backup files: 0644 (readable by owner and group)
- Log files: 0644 (restricted access)

### Access Control
- Web interface restricted to admin users
- File downloads require authentication
- Restore operations logged and audited

## Performance Impact

### Resource Usage
- **CPU**: Minimal during backup creation
- **Memory**: Depends on database size
- **Disk I/O**: High during backup/restore operations
- **Network**: None (local operations)

### Optimization Strategies
- Compression reduces storage requirements
- Incremental backups for large databases
- Off-peak scheduling minimizes impact
- Parallel processing for multiple databases

## Troubleshooting

### Common Issues

#### Backup Creation Fails
```
Error: mysqldump command not found
Solution: Install mysql-client package
```

#### Permission Denied
```
Error: Cannot write to backup directory
Solution: Check directory permissions (chown/chmod)
```

#### Disk Space Full
```
Error: No space left on device
Solution: Clean old backups or increase disk space
```

#### Restore Fails
```
Error: Foreign key constraint violations
Solution: Ensure backup includes all related data
```

## Maintenance

### Regular Tasks
1. **Monitor backup success rates**
2. **Review backup file sizes**
3. **Verify restore procedures quarterly**
4. **Update retention policies as needed**
5. **Monitor disk space usage**

### Log Management
- Backup logs rotated daily
- Error logs monitored for issues
- Performance metrics tracked over time
- Audit trails maintained for compliance

## Integration Points

### Application Integration
- Backup status displayed in admin dashboard
- Notification system integration
- User activity logging
- Performance monitoring integration

### External Systems
- Email notification services
- Cloud storage for offsite backups
- Monitoring dashboards
- Compliance reporting systems

## Future Enhancements

### Planned Features
- **Incremental Backups**: For large databases
- **Cloud Storage**: AWS S3, Google Cloud Storage
- **Encryption**: Backup file encryption
- **Multi-Database**: Support for multiple databases
- **Point-in-Time Recovery**: Granular restore options

### Scalability Improvements
- **Parallel Processing**: Multi-threaded backups
- **Distributed Backups**: Multi-server environments
- **Automated Testing**: Backup integrity validation
- **Advanced Scheduling**: Custom cron expressions

---

## Support Information

For technical support regarding the backup system:
- Check logs in `/logs/backup_*.log`
- Review backup status in web interface
- Contact system administrator
- Reference this documentation

**Last Updated**: Implementation Complete
**Version**: 1.0.0
**Status**: Production Ready
