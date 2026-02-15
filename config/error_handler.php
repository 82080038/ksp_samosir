<?php
/**
 * Error Handler untuk KSP Samosir
 * Menangani error dan exception dengan logging yang proper
 */

// Custom error handler
function kspErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorTypes = [
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated'
    ];
    
    $errorType = $errorTypes[$errno] ?? 'Unknown Error';
    $message = "[$errorType] $errstr in $errfile on line $errline";
    
    // Log error
    error_log($message);
    
    // Display error for development
    if (ini_get('display_errors')) {
        echo "<div class='alert alert-danger'>
            <strong>$errorType:</strong> $errstr<br>
            <small>File: $errfile Line: $errline</small>
        </div>";
    }
    
    return true;
}

// Custom exception handler
function kspExceptionHandler($exception) {
    $message = "Uncaught exception: " . $exception->getMessage() . 
               " in " . $exception->getFile() . 
               " on line " . $exception->getLine();
    
    error_log($message);
    
    if (ini_get('display_errors')) {
        echo "<div class='alert alert-danger'>
            <strong>Fatal Error:</strong> " . htmlspecialchars($exception->getMessage()) . "<br>
            <small>File: " . $exception->getFile() . " Line: " . $exception->getLine() . "</small>
        </div>";
    } else {
        echo "<div class='alert alert-danger'>
            <strong>System Error:</strong> Terjadi kesalahan sistem. Silakan hubungi administrator.
        </div>";
    }
}

// Register error handlers
set_error_handler('kspErrorHandler');
set_exception_handler('kspExceptionHandler');

// Shutdown function untuk fatal errors
function kspShutdownHandler() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $message = "Fatal Error: " . $error['message'] . 
                   " in " . $error['file'] . 
                   " on line " . $error['line'];
        
        error_log($message);
        
        if (!ini_get('display_errors')) {
            echo "<div class='alert alert-danger'>
                <strong>System Error:</strong> Terjadi kesalahan sistem. Silakan hubungi administrator.
            </div>";
        }
    }
}

register_shutdown_function('kspShutdownHandler');

?>
