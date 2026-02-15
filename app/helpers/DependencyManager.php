<?php
/**
 * KSP Samosir Centralized Dependency Manager
 * Holistic dependency management system
 */

class KSPDependencyManager {
    private static $loaded = [];
    private static $helpers = [];
    private static $configs = [];
    
    /**
     * Auto-load required dependencies
     */
    public static function init() {
        // Define all required dependencies
        self::$helpers = [
            'FormatHelper' => __DIR__ . '/FormatHelper.php',
            'TitleHelper' => __DIR__ . '/TitleHelper.php',
            'UnitHelper' => __DIR__ . '/UnitHelper.php',
        ];
        
        self::$configs = [
            'database' => __DIR__ . '/../../config/config.php',
        ];
        
        // Auto-load core dependencies
        self::loadHelper('FormatHelper');
        self::loadHelper('TitleHelper');
        
        // Load configurations
        self::loadConfig('database');
    }
    
    /**
     * Load helper file
     */
    public static function loadHelper($name) {
        if (isset(self::$loaded[$name])) {
            return true;
        }
        
        if (isset(self::$helpers[$name])) {
            require_once self::$helpers[$name];
            self::$loaded[$name] = true;
            return true;
        }
        
        return false;
    }
    
    /**
     * Load configuration
     */
    public static function loadConfig($name) {
        if (isset(self::$loaded['config_' . $name])) {
            return true;
        }
        
        if (isset(self::$configs[$name])) {
            require_once self::$configs[$name];
            self::$loaded['config_' . $name] = true;
            return true;
        }
        
        return false;
    }
    
    /**
     * Initialize view with all dependencies
     */
    public static function initView($page = null) {
        // Load all required helpers
        self::loadHelper('FormatHelper');
        self::loadHelper('TitleHelper');
        self::loadHelper('AuthHelper');
        
        // Initialize page title system
        if (class_exists('TitleHelper')) {
            return initPageTitle();
        }
        
        return [
            'page' => $page ?? 'dashboard',
            'title' => 'Dashboard',
            'full_title' => 'Dashboard - KSP Samosir'
        ];
    }
    
    /**
     * Get all loaded dependencies
     */
    public static function getLoaded() {
        return self::$loaded;
    }
    
    /**
     * Check if dependency is loaded
     */
    public static function isLoaded($name) {
        return isset(self::$loaded[$name]);
    }
}

// Auto-initialize when file is included
KSPDependencyManager::init();

/**
 * Global shortcut functions
 */
function loadHelper($name) {
    return KSPDependencyManager::loadHelper($name);
}

function initView($page = null) {
    return KSPDependencyManager::initView($page);
}

function isHelperLoaded($name) {
    return KSPDependencyManager::isLoaded($name);
}
?>
