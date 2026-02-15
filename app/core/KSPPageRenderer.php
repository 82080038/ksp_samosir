<?php
/**
 * KSP Page Renderer System
 * 
 * Sistem terpusat untuk rendering SEMUA halaman aplikasi
 * Single source of truth untuk layout, header, navigation, dan content
 * 
 * @author KSP Samosir Development Team
 * @version 2.0.0
 */

class KSPPageRenderer {
    
    // Properties
    private $config;
    private $database;
    private $user;
    private $currentPage;
    private $pageData;
    private $navigationData;
    private $breadcrumbs;
    
    // Singleton instance
    private static $instance = null;
    
    /**
     * Singleton pattern
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->config = $this->loadConfig();
        $this->database = $this->connectDatabase();
        $this->user = $this->getCurrentUser();
        $this->currentPage = $this->detectCurrentPage();
        $this->pageData = [];
        $this->navigationData = [];
        $this->breadcrumbs = [];
    }

    private function getBasePath() {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir === '/' || $scriptDir === '.') {
            return '';
        }
        return rtrim($scriptDir, '/');
    }
    
    /**
     * Main render method - ONE METHOD TO RULE THEM ALL
     */
    public function render($content = null, $options = []) {
        // Load semua data yang dibutuhkan
        $this->loadPageData();
        $this->loadNavigationData();
        $this->buildBreadcrumbs();
        
        // Prepare page variables
        $pageVars = $this->preparePageVariables($content, $options);
        
        // Render layout dengan data yang sudah diproses
        return $this->renderLayout($pageVars);
    }
    
    /**
     * Load konfigurasi sistem
     */
    private function loadConfig() {
        return [
            'app_name' => 'KSP Samosir',
            'app_version' => '2.0.0',
            'default_theme' => 'default',
            'debug_mode' => (getenv('APP_ENV') === 'development'),
            'cache_enabled' => true,
            'cache_duration' => 3600, // 1 hour
            'default_language' => 'id',
            'supported_languages' => ['id', 'en'],
            'items_per_page' => 20,
            'date_format' => 'd/m/Y',
            'datetime_format' => 'd/m/Y H:i:s',
            'currency' => 'IDR',
            'timezone' => 'Asia/Jakarta'
        ];
    }
    
    /**
     * Connect ke database
     */
    private function connectDatabase() {
        try {
            $pdo = new PDO(
                'mysql:host=localhost;dbname=ksp_samosir;charset=utf8mb4',
                'root',
                'root',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            return $pdo;
        } catch (PDOException $e) {
            // Log error tapi jangan throw exception
            error_log('Database connection failed: ' . $e->getMessage());
            return null; // Return null untuk fallback
        }
    }
    
    /**
     * Get current user
     */
    private function getCurrentUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['user'] ?? null;
    }
    
    /**
     * Detect current page dari URL/parameters
     */
    private function detectCurrentPage() {
        $requestUri = $_SERVER['REQUEST_URI'];
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Remove base path dynamically
        $basePath = $this->getBasePath();
        if ($basePath !== '' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        // Clean path
        $path = trim($path, '/');
        $segments = explode('/', $path);
        
        // Determine page
        if (empty($segments[0]) || $segments[0] === 'index.php') {
            return 'dashboard';
        }
        
        return $segments[0];
    }
    
    /**
     * Load page data dari database atau konfigurasi
     */
    private function loadPageData() {
        $cacheKey = "page_data_{$this->currentPage}";
        
        // Try cache first
        if ($this->config['cache_enabled']) {
            $cached = $this->getCache($cacheKey);
            if ($cached !== null) {
                $this->pageData = $cached;
                return;
            }
        }
        
        // Load dari database
        if ($this->database) {
            $stmt = $this->database->prepare("
                SELECT p.*, m.title as module_title, m.icon as module_icon
                FROM pages p 
                LEFT JOIN modules m ON p.module_id = m.id 
                WHERE p.page_key = :page_key AND p.is_active = 1
            ");
            $stmt->execute([':page_key' => $this->currentPage]);
            $pageData = $stmt->fetch();
            
            if ($pageData) {
                $this->pageData = $this->processPageData($pageData);
                $this->setCache($cacheKey, $this->pageData);
                return;
            }
        }
        
        // Fallback ke konfigurasi statis
        $this->pageData = $this->getDefaultPageData($this->currentPage);
    }
    
    /**
     * Process page data
     */
    private function processPageData($data) {
        return [
            'page_key' => $data['page_key'],
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'keywords' => $data['keywords'] ?? '',
            'module' => $data['module_title'] ?? '',
            'module_icon' => $data['module_icon'] ?? 'bi-circle',
            'breadcrumb_title' => $data['breadcrumb_title'] ?? $data['title'],
            'show_breadcrumb' => (bool)($data['show_breadcrumb'] ?? true),
            'show_sidebar' => (bool)($data['show_sidebar'] ?? true),
            'show_header' => (bool)($data['show_header'] ?? true),
            'layout' => $data['layout'] ?? 'default',
            'roles_required' => $data['roles_required'] ? explode(',', $data['roles_required']) : [],
            'meta' => [
                'title' => $data['meta_title'] ?? $data['title'],
                'description' => $data['meta_description'] ?? $data['description'],
                'keywords' => $data['meta_keywords'] ?? $data['keywords'],
                'author' => $data['meta_author'] ?? 'KSP Samosir',
                'robots' => $data['meta_robots'] ?? 'index,follow'
            ],
            'actions' => $this->parsePageActions($data['actions'] ?? ''),
            'scripts' => $this->parsePageScripts($data['scripts'] ?? ''),
            'styles' => $this->parsePageStyles($data['styles'] ?? '')
        ];
    }
    
    /**
     * Get default page data (fallback)
     */
    private function getDefaultPageData($page) {
        $defaultPages = [
            'dashboard' => [
                'title' => 'Dashboard',
                'description' => 'Halaman utama dashboard KSP Samosir',
                'module' => 'Dashboard',
                'module_icon' => 'bi-speedometer2',
                'breadcrumb_title' => 'Dashboard',
                'layout' => 'default'
            ],
            'anggota' => [
                'title' => 'Anggota',
                'description' => 'Manajemen data anggota KSP',
                'module' => 'Membership',
                'module_icon' => 'bi-people',
                'breadcrumb_title' => 'Anggota',
                'layout' => 'default'
            ],
            'simpanan' => [
                'title' => 'Simpanan',
                'description' => 'Manajemen simpanan anggota',
                'module' => 'Savings',
                'module_icon' => 'bi-piggy-bank',
                'breadcrumb_title' => 'Simpanan',
                'layout' => 'default'
            ],
            'pinjaman' => [
                'title' => 'Pinjaman',
                'description' => 'Manajemen pinjaman anggota',
                'module' => 'Loans',
                'module_icon' => 'bi-cash-stack',
                'breadcrumb_title' => 'Pinjaman',
                'layout' => 'default'
            ],
            'settings' => [
                'title' => 'Pengaturan',
                'description' => 'Pengaturan sistem KSP Samosir',
                'module' => 'System',
                'module_icon' => 'bi-gear',
                'breadcrumb_title' => 'Pengaturan',
                'layout' => 'default'
            ]
        ];
        
        return $defaultPages[$page] ?? [
            'title' => ucfirst($page),
            'description' => 'Halaman ' . $page,
            'module' => 'General',
            'module_icon' => 'bi-circle',
            'breadcrumb_title' => ucfirst($page),
            'layout' => 'default'
        ];
    }
    
    /**
     * Load navigation data
     */
    private function loadNavigationData() {
        $cacheKey = 'navigation_data_' . ($this->user['role'] ?? 'guest');
        
        // Try cache first
        if ($this->config['cache_enabled']) {
            $cached = $this->getCache($cacheKey);
            if ($cached !== null) {
                $this->navigationData = $cached;
                return;
            }
        }
        
        // Load dari database
        if ($this->database) {
            $userRole = $this->user['role'] ?? null;
            
            $stmt = $this->database->prepare("
                SELECT n.*, m.title as module_title
                FROM navigation n 
                LEFT JOIN modules m ON n.module_id = m.id 
                WHERE n.is_active = 1 
                AND (n.role_required IS NULL OR n.role_required = '' OR FIND_IN_SET(:role, n.role_required) > 0)
                ORDER BY n.parent_id ASC, n.sort_order ASC
            ");
            $stmt->execute([':role' => $userRole]);
            $navigation = $stmt->fetchAll();
            
            if ($navigation) {
                $this->navigationData = $this->buildNavigationTree($navigation);
                $this->setCache($cacheKey, $this->navigationData);
                return;
            }
        }
        
        // Fallback ke static navigation
        $this->navigationData = $this->getStaticNavigation();
    }
    
    /**
     * Build navigation tree dari flat array
     */
    private function buildNavigationTree($flatNavigation) {
        $tree = [];
        $indexed = [];
        
        // Index by ID
        foreach ($flatNavigation as $item) {
            $indexed[$item['id']] = $item;
            $indexed[$item['id']]['children'] = [];
        }
        
        // Build tree
        foreach ($indexed as $id => $item) {
            if ($item['parent_id']) {
                $indexed[$item['parent_id']]['children'][] = &$indexed[$id];
            } else {
                $tree[] = &$indexed[$id];
            }
        }
        
        return $tree;
    }
    
    /**
     * Get static navigation (fallback)
     */
    private function getStaticNavigation() {
        return [
            [
                'page_key' => 'dashboard',
                'title' => 'Dashboard',
                'url' => base_url('dashboard'),
                'icon_class' => 'bi-speedometer2',
                'children' => []
            ],
            [
                'page_key' => 'anggota',
                'title' => 'Anggota',
                'url' => base_url('anggota'),
                'icon_class' => 'bi-people',
                'children' => []
            ],
            [
                'page_key' => 'simpanan',
                'title' => 'Simpanan',
                'url' => base_url('simpanan'),
                'icon_class' => 'bi-piggy-bank',
                'children' => []
            ],
            [
                'page_key' => 'pinjaman',
                'title' => 'Pinjaman',
                'url' => base_url('pinjaman'),
                'icon_class' => 'bi-cash-stack',
                'children' => []
            ],
            [
                'page_key' => 'settings',
                'title' => 'Pengaturan',
                'url' => base_url('settings'),
                'icon_class' => 'bi-gear',
                'children' => []
            ]
        ];
    }
    
    /**
     * Build breadcrumbs
     */
    private function buildBreadcrumbs() {
        $this->breadcrumbs = [];
        
        // Add Home
        $this->breadcrumbs[] = [
            'title' => 'Home',
            'url' => base_url('dashboard'),
            'icon' => 'bi-house'
        ];
        
        // Add current page
        if (!empty($this->pageData['title'])) {
            $this->breadcrumbs[] = [
                'title' => $this->pageData['breadcrumb_title'],
                'url' => '#',
                'icon' => $this->pageData['module_icon']
            ];
        }
    }
    
    /**
     * Prepare page variables untuk rendering
     */
    private function preparePageVariables($content, $options) {
        return [
            // Config
            'config' => $this->config,
            
            // User
            'user' => $this->user,
            
            // Current Page
            'current_page' => $this->currentPage,
            'page_data' => $this->pageData,
            
            // Navigation
            'navigation' => $this->navigationData,
            
            // Breadcrumbs
            'breadcrumbs' => $this->breadcrumbs,
            
            // Content
            'content' => $content,
            
            // Options
            'options' => array_merge([
                'show_sidebar' => $this->pageData['show_sidebar'] ?? true,
                'show_header' => $this->pageData['show_header'] ?? true,
                'show_breadcrumb' => $this->pageData['show_breadcrumb'] ?? true,
                'layout' => $this->pageData['layout'] ?? 'default'
            ], $options),
            
            // Assets
            'styles' => $this->compileStyles(),
            'scripts' => $this->compileScripts(),
            
            // Meta
            'meta' => $this->pageData['meta'] ?? [],
            
            // Page actions
            'actions' => $this->pageData['actions'] ?? []
        ];
    }
    
    /**
     * Render layout utama
     */
    private function renderLayout($vars) {
        ob_start();
        extract($vars);
        
        include __DIR__ . '/../views/layouts/unified_layout.php';
        
        return ob_get_clean();
    }
    
    /**
     * Parse page actions dari JSON
     */
    private function parsePageActions($actionsJson) {
        if (empty($actionsJson)) return [];
        
        try {
            return json_decode($actionsJson, true) ?? [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Parse page scripts dari JSON
     */
    private function parsePageScripts($scriptsJson) {
        if (empty($scriptsJson)) return [];
        
        try {
            return json_decode($scriptsJson, true) ?? [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Parse page styles dari JSON
     */
    private function parsePageStyles($stylesJson) {
        if (empty($stylesJson)) return [];
        
        try {
            return json_decode($stylesJson, true) ?? [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Compile semua styles
     */
    private function compileStyles() {
        $styles = [
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
            'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css',
            base_url('assets/css/ksp-enhanced.css')
        ];
        
        // Add page-specific styles
        if (!empty($this->pageData['styles'])) {
            $styles = array_merge($styles, $this->pageData['styles']);
        }
        
        return $styles;
    }
    
    /**
     * Compile semua scripts
     */
    private function compileScripts() {
        $scripts = [
            'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
            base_url('assets/js/ksp-framework-simple.js'),
            base_url('assets/js/ksp-page-title-manager.js')
        ];
        
        // Add page-specific scripts
        if (!empty($this->pageData['scripts'])) {
            $scripts = array_merge($scripts, $this->pageData['scripts']);
        }
        
        return $scripts;
    }
    
    /**
     * Cache methods
     */
    private function getCache($key) {
        // Simple file cache - bisa diganti dengan Redis/Memcached
        $cacheFile = __DIR__ . '/../../cache/' . md5($key) . '.cache';
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        $data = file_get_contents($cacheFile);
        $cache = unserialize($data);
        
        if ($cache['expires'] < time()) {
            unlink($cacheFile);
            return null;
        }
        
        return $cache['data'];
    }
    
    private function setCache($key, $data) {
        $cacheFile = __DIR__ . '/../../cache/' . md5($key) . '.cache';
        $cacheDir = dirname($cacheFile);
        
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cache = [
            'data' => $data,
            'expires' => time() + $this->config['cache_duration']
        ];
        
        file_put_contents($cacheFile, serialize($cache));
    }
    
    /**
     * Public method untuk manual page rendering
     */
    public function renderPage($pageKey, $content = null, $options = []) {
        $this->currentPage = $pageKey;
        $this->loadPageData();
        $this->buildBreadcrumbs();
        
        return $this->render($content, $options);
    }
    
    /**
     * Get page data untuk external use
     */
    public function getPageData() {
        return $this->pageData;
    }
    
    /**
     * Get navigation data untuk external use
     */
    public function getNavigationData() {
        return $this->navigationData;
    }
}

// Helper function
if (!function_exists('render_page')) {
    function render_page($content = null, $options = []) {
        return KSPPageRenderer::getInstance()->render($content, $options);
    }
}

if (!function_exists('get_page_data')) {
    function get_page_data() {
        return KSPPageRenderer::getInstance()->getPageData();
    }
}

if (!function_exists('get_navigation_data')) {
    function get_navigation_data() {
        return KSPPageRenderer::getInstance()->getNavigationData();
    }
}
?>
