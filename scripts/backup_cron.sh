#!/bin/bash
# Automated Backup Cron Job for KSP Samosir
# This script should be added to crontab for scheduled execution

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_SCRIPT="$SCRIPT_DIR/../scripts/automated_backup.php"
LOG_FILE="$SCRIPT_DIR/../logs/backup_cron_$(date +\%Y-\%m-\%d).log"

# Environment setup
export PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

# Function to log messages
log_message() {
    local level="$1"
    local message="$2"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] [$level] $message" >> "$LOG_FILE"
}

# Function to check if backup is already running
is_backup_running() {
    local pid_file="$SCRIPT_DIR/../logs/backup.pid"

    if [ -f "$pid_file" ]; then
        local pid=$(cat "$pid_file")
        if kill -0 "$pid" 2>/dev/null; then
            return 0  # Backup is running
        else
            # Stale PID file, remove it
            rm -f "$pid_file"
        fi
    fi
    return 1  # Backup is not running
}

# Function to create PID file
create_pid_file() {
    local pid_file="$SCRIPT_DIR/../logs/backup.pid"
    echo $$ > "$pid_file"
}

# Function to remove PID file
remove_pid_file() {
    local pid_file="$SCRIPT_DIR/../logs/backup.pid"
    rm -f "$pid_file"
}

# Main backup execution
main() {
    log_message "INFO" "Starting automated backup process"

    # Check if another backup is already running
    if is_backup_running; then
        log_message "WARNING" "Another backup process is already running. Skipping this execution."
        exit 1
    fi

    # Create PID file
    create_pid_file

    # Execute backup
    if [ -f "$BACKUP_SCRIPT" ]; then
        log_message "INFO" "Executing backup script: $BACKUP_SCRIPT"

        # Run backup script
        cd "$SCRIPT_DIR/.."
        php "$BACKUP_SCRIPT" --scheduled

        local exit_code=$?
        if [ $exit_code -eq 0 ]; then
            log_message "INFO" "Backup completed successfully"
        else
            log_message "ERROR" "Backup failed with exit code: $exit_code"
        fi

        # Clean up PID file
        remove_pid_file

        exit $exit_code
    else
        log_message "ERROR" "Backup script not found: $BACKUP_SCRIPT"
        remove_pid_file
        exit 1
    fi
}

# Handle script interruption
trap 'log_message "WARNING" "Backup process interrupted"; remove_pid_file; exit 1' INT TERM

# Execute main function
main
