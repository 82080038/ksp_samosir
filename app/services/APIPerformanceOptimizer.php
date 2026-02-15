<?php
/**
 * KSP Samosir - API Performance Optimization
 * Optimized API responses and caching
 */

class APIPerformanceOptimizer
{
    private static $cache = [];
    private static $cacheEnabled = true;
    private static $cacheTimeout = 300; // 5 minutes
    
    /**
     * Get cached data or execute callback
     */
    public static function cached($key, $callback, $timeout = null)
    {
        if (!self::$cacheEnabled) {
            return $callback();
        }
        
        $timeout = $timeout ?? self::$cacheTimeout;
        $cacheKey = self::getCacheKey($key);
        
        // Check if cache exists and is not expired
        if (isset(self::$cache[$cacheKey])) {
            $cached = self::$cache[$cacheKey];
            if (time() - $cached['timestamp'] < $timeout) {
                return $cached['data'];
            }
        }
        
        // Execute callback and cache result
        $data = $callback();
        self::$cache[$cacheKey] = [
            'data' => $data,
            'timestamp' => time()
        ];
        
        return $data;
    }
    
    /**
     * Clear cache
     */
    public static function clearCache($pattern = null)
    {
        if ($pattern) {
            foreach (self::$cache as $key => $value) {
                if (strpos($key, $pattern) !== false) {
                    unset(self::$cache[$key]);
                }
            }
        } else {
            self::$cache = [];
        }
    }
    
    /**
     * Generate cache key
     */
    private static function getCacheKey($key)
    {
        return 'ksp_' . md5($key);
    }
    
    /**
     * Optimize API response
     */
    public static function optimizeResponse($data, $includes = [])
    {
        // Remove unnecessary fields
        if (is_array($data)) {
            $data = self::removeNullValues($data);
            
            // Apply includes/excludes
            if (!empty($includes)) {
                $data = self::filterFields($data, $includes);
            }
        }
        
        return $data;
    }
    
    /**
     * Remove null values from array/object
     */
    private static function removeNullValues($data)
    {
        if (is_array($data)) {
            return array_filter($data, function($value) {
                return $value !== null && $value !== '';
            });
        } elseif (is_object($data)) {
            $objectVars = get_object_vars($data);
            foreach ($objectVars as $key => $value) {
                if ($value === null || $value === '') {
                    unset($data->$key);
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Filter fields based on includes
     */
    private static function filterFields($data, $includes)
    {
        if (is_array($data)) {
            $filtered = [];
            foreach ($includes as $field) {
                if (isset($data[$field])) {
                    $filtered[$field] = $data[$field];
                }
            }
            return $filtered;
        }
        
        return $data;
    }
    
    /**
     * Compress response if possible
     */
    public static function compressResponse($data)
    {
        $json = json_encode($data);
        
        // Check if gzip compression is available
        if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false && function_exists('gzencode')) {
            header('Content-Encoding: gzip');
            return gzencode($json);
        }
        
        return $json;
    }
    
    /**
     * Set performance headers
     */
    public static function setPerformanceHeaders()
    {
        // Cache control
        header('Cache-Control: public, max-age=300');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 300) . ' GMT');
        
        // Compression
        if (ob_get_length()) {
            ob_gzhandler();
        }
        
        // Content type
        header('Content-Type: application/json; charset=utf-8');
    }
}

/**
 * Optimized Koperasi Controller with caching
 */
class OptimizedKoperasiController extends KoperasiController
{
    /**
     * Get dashboard overview with caching
     */
    public function getDashboardOverview()
    {
        return APIPerformanceOptimizer::cached('dashboard_overview', function() {
            return parent::getDashboardOverview();
        }, 60); // Cache for 1 minute
    }
    
    /**
     * Get anggota with caching and optimization
     */
    public function getAnggota($filters = [])
    {
        $cacheKey = 'anggota_' . md5(serialize($filters));
        
        return APIPerformanceOptimizer::cached($cacheKey, function() use ($filters) {
            $result = parent::getAnggota($filters);
            
            // Optimize response
            if ($result['success']) {
                $result['data'] = APIPerformanceOptimizer::optimizeResponse($result['data'], [
                    'id', 'no_anggota', 'nama_lengkap', 'nama_unit', 'total_simpanan', 
                    'total_pinjaman', 'status', 'created_at'
                ]);
            }
            
            return $result;
        }, 180); // Cache for 3 minutes
    }
    
    /**
     * Get simpanan with caching
     */
    public function getSimpanan($filters = [])
    {
        $cacheKey = 'simpanan_' . md5(serialize($filters));
        
        return APIPerformanceOptimizer::cached($cacheKey, function() use ($filters) {
            return parent::getSimpanan($filters);
        }, 120); // Cache for 2 minutes
    }
    
    /**
     * Get pinjaman with caching
     */
    public function getPinjaman($filters = [])
    {
        $cacheKey = 'pinjaman_' . md5(serialize($filters));
        
        return APIPerformanceOptimizer::cached($cacheKey, function() use ($filters) {
            return parent::getPinjaman($filters);
        }, 120); // Cache for 2 minutes
    }
}

/**
 * Performance Monitoring
 */
class PerformanceMonitor
{
    private static $queries = [];
    private static $startTime;
    
    public static function start()
    {
        self::$startTime = microtime(true);
    }
    
    public static function logQuery($sql, $executionTime)
    {
        self::$queries[] = [
            'sql' => $sql,
            'execution_time' => $executionTime,
            'timestamp' => microtime(true)
        ];
    }
    
    public static function getReport()
    {
        $totalTime = microtime(true) - self::$startTime;
        $slowQueries = array_filter(self::$queries, function($query) {
            return $query['execution_time'] > 0.1; // Queries longer than 100ms
        });
        
        return [
            'total_execution_time' => $totalTime,
            'total_queries' => count(self::$queries),
            'slow_queries' => count($slowQueries),
            'average_query_time' => count(self::$queries) > 0 ? 
                array_sum(array_column(self::$queries, 'execution_time')) / count(self::$queries) : 0,
            'queries' => self::$queries
        ];
    }
}

/**
 * Database Query Optimizer
 */
class QueryOptimizer
{
    /**
     * Execute query with performance monitoring
     */
    public static function executeQuery($conn, $sql, $params = [])
    {
        $start = microtime(true);
        
        try {
            if (empty($params)) {
                $result = $conn->query($sql);
            } else {
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                $result = $stmt;
            }
            
            $executionTime = microtime(true) - $start;
            PerformanceMonitor::logQuery($sql, $executionTime);
            
            return $result;
        } catch (Exception $e) {
            $executionTime = microtime(true) - $start;
            PerformanceMonitor::logQuery($sql . ' [ERROR: ' . $e->getMessage() . ']', $executionTime);
            throw $e;
        }
    }
    
    /**
     * Get optimized query for dashboard
     */
    public static function getDashboardQuery()
    {
        return "SELECT * FROM v_dashboard_overview";
    }
    
    /**
     * Get optimized query for anggota list
     */
    public static function getAnggotaQuery($filters = [])
    {
        $sql = "SELECT * FROM v_anggota_summary WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['unit_id'])) {
            $sql .= " AND unit_id = ?";
            $params[] = $filters['unit_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (nama_lengkap LIKE ? OR no_anggota LIKE ? OR email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        return [$sql, $params];
    }
}

// Performance optimization middleware
function performanceMiddleware()
{
    // Start performance monitoring
    PerformanceMonitor::start();
    
    // Set performance headers
    APIPerformanceOptimizer::setPerformanceHeaders();
}

// Register performance middleware
if (!function_exists('register_performance_middleware')) {
    function register_performance_middleware() {
        performanceMiddleware();
    }
}
?>
