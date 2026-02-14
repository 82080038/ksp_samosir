/**
 * Mobile Data Optimization for KSP Samosir
 * Provides device-aware data delivery and mobile-specific optimizations
 */

class MobileOptimizer {
    // Device detection and capabilities
    public static function detectDevice() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        $device = [
            'type' => 'desktop',
            'is_mobile' => false,
            'is_tablet' => false,
            'screen_size' => 'large',
            'touch_enabled' => false,
            'bandwidth' => 'high',
            'connection_type' => 'unknown'
        ];

        // Detect mobile devices
        if (preg_match('/(android|iphone|ipad|ipod|blackberry|windows phone|mobile)/i', $userAgent)) {
            $device['is_mobile'] = true;
            $device['type'] = 'mobile';
            $device['touch_enabled'] = true;
            $device['bandwidth'] = 'low';
        }

        // Detect tablets
        if (preg_match('/(ipad|android.*tablet|tablet)/i', $userAgent)) {
            $device['is_tablet'] = true;
            $device['type'] = 'tablet';
            $device['bandwidth'] = 'medium';
        }

        // Detect screen size from User-Agent or use default assumptions
        if (isset($_SERVER['HTTP_X_SCREEN_WIDTH'])) {
            $width = intval($_SERVER['HTTP_X_SCREEN_WIDTH']);
            if ($width <= 480) {
                $device['screen_size'] = 'small';
                $device['bandwidth'] = 'low';
            } elseif ($width <= 768) {
                $device['screen_size'] = 'medium';
                $device['bandwidth'] = 'medium';
            }
        }

        // Detect connection type (if available)
        if (isset($_SERVER['HTTP_SAVE_DATA']) && $_SERVER['HTTP_SAVE_DATA'] === 'on') {
            $device['bandwidth'] = 'low';
        }

        return $device;
    }

    // Get optimized pagination for device
    public static function getOptimizedPagination($device = null) {
        if (!$device) {
            $device = self::detectDevice();
        }

        $pagination = [
            'desktop' => ITEMS_PER_PAGE,      // 20 items
            'tablet' => ITEMS_PER_PAGE / 2,   // 10 items
            'mobile' => ITEMS_PER_PAGE / 4    // 5 items
        ];

        return $pagination[$device['type']] ?? ITEMS_PER_PAGE;
    }

    // Get optimized field selection for device
    public static function getOptimizedFields($table, $device = null) {
        if (!$device) {
            $device = self::detectDevice();
        }

        $fieldSets = [
            'anggota' => [
                'desktop' => ['id', 'no_anggota', 'nama_lengkap', 'nik', 'status', 'tanggal_gabung', 'alamat', 'no_hp', 'email'],
                'tablet' => ['id', 'no_anggota', 'nama_lengkap', 'status', 'tanggal_gabung', 'alamat', 'no_hp'],
                'mobile' => ['id', 'nama_lengkap', 'status', 'no_hp']
            ],
            'penjualan' => [
                'desktop' => ['id', 'no_faktur', 'tanggal_penjualan', 'total_harga', 'status_pembayaran', 'customer_name', 'alamat', 'no_hp'],
                'tablet' => ['id', 'no_faktur', 'tanggal_penjualan', 'total_harga', 'status_pembayaran', 'customer_name'],
                'mobile' => ['id', 'no_faktur', 'tanggal_penjualan', 'total_harga', 'status_pembayaran']
            ],
            'pinjaman' => [
                'desktop' => ['id', 'no_pinjaman', 'nama_lengkap', 'jumlah_pinjaman', 'bunga_persen', 'tenor_bulan', 'status', 'tanggal_pengajuan'],
                'tablet' => ['id', 'no_pinjaman', 'nama_lengkap', 'jumlah_pinjaman', 'status', 'tanggal_pengajuan'],
                'mobile' => ['id', 'nama_lengkap', 'jumlah_pinjaman', 'status']
            ]
        ];

        return $fieldSets[$table][$device['type']] ?? $fieldSets[$table]['desktop'] ?? ['*'];
    }

    // Optimize SQL query for device capabilities
    public static function buildOptimizedQuery($baseQuery, $table, $device = null) {
        if (!$device) {
            $device = self::detectDevice();
        }

        $fields = self::getOptimizedFields($table, $device);

        // Replace SELECT * with optimized field selection
        if (strpos($baseQuery, 'SELECT *') !== false && $fields[0] !== '*') {
            $fieldList = implode(', ', $fields);
            $baseQuery = str_replace('SELECT *', "SELECT {$fieldList}", $baseQuery);
        }

        // Add LIMIT for mobile devices if not present
        if ($device['is_mobile'] && strpos($baseQuery, 'LIMIT') === false) {
            $limit = self::getOptimizedPagination($device);
            $baseQuery .= " LIMIT {$limit}";
        }

        return $baseQuery;
    }

    // Compress response data for mobile devices
    public static function compressResponseData($data, $device = null) {
        if (!$device) {
            $device = self::detectDevice();
        }

        // For mobile devices, compress data
        if ($device['is_mobile'] && $device['bandwidth'] === 'low') {
            // Remove unnecessary fields
            if (is_array($data)) {
                $data = array_map(function($item) {
                    if (is_array($item)) {
                        // Remove large text fields on mobile
                        unset($item['description'], $item['notes'], $item['comments']);
                        // Truncate long strings
                        foreach ($item as $key => $value) {
                            if (is_string($value) && strlen($value) > 100) {
                                $item[$key] = substr($value, 0, 100) . '...';
                            }
                        }
                    }
                    return $item;
                }, $data);
            }
        }

        return $data;
    }

    // Get device-aware API response format
    public static function formatApiResponse($data, $device = null) {
        if (!$device) {
            $device = self::detectDevice();
        }

        $response = [
            'success' => true,
            'data' => $data,
            'meta' => [
                'device' => $device['type'],
                'timestamp' => time(),
                'compressed' => $device['is_mobile']
            ]
        ];

        // Add pagination info if data is paginated
        if (isset($data['items']) && isset($data['total'])) {
            $response['meta']['pagination'] = [
                'total' => $data['total'],
                'per_page' => self::getOptimizedPagination($device),
                'optimized' => $device['is_mobile']
            ];
        }

        return $response;
    }

    // Check if request should use mobile optimization
    public static function shouldOptimizeForMobile() {
        $device = self::detectDevice();
        return $device['is_mobile'] || $device['bandwidth'] === 'low';
    }
}

// Helper functions for easy integration
function getDeviceInfo() {
    return MobileOptimizer::detectDevice();
}

function getMobilePagination() {
    return MobileOptimizer::getOptimizedPagination();
}

function optimizeQueryForDevice($query, $table) {
    return MobileOptimizer::buildOptimizedQuery($query, $table);
}

function compressMobileData($data) {
    return MobileOptimizer::compressResponseData($data);
}

function formatMobileApiResponse($data) {
    return MobileOptimizer::formatApiResponse($data);
}
