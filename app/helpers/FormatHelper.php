<?php
/**
 * Formatting Helper Functions
 * Provides consistent formatting for dates, currency, numbers across the application
 */

/**
 * Format tanggal ke format Indonesia
 */
function formatTanggal($tanggal, $format = 'd/m/Y') {
    if (empty($tanggal) || $tanggal === '0000-00-00' || $tanggal === '0000-00-00 00:00:00') {
        return '-';
    }
    
    try {
        $date = new DateTime($tanggal);
        return $date->format($format);
    } catch (Exception $e) {
        return $tanggal; // Return original if can't parse
    }
}

/**
 * Format tanggal dengan nama bulan Indonesia
 */
function formatTanggalLengkap($tanggal) {
    if (empty($tanggal) || $tanggal === '0000-00-00' || $tanggal === '0000-00-00 00:00:00') {
        return '-';
    }
    
    try {
        $date = new DateTime($tanggal);
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        return $date->format('d') . ' ' . $bulan[(int)$date->format('m')] . ' ' . $date->format('Y');
    } catch (Exception $e) {
        return $tanggal;
    }
}

/**
 * Format uang ke format Indonesia
 */
function formatUang($amount, $prefix = 'Rp ') {
    if ($amount === null || $amount === '') {
        return '-';
    }
    
    return $prefix . number_format((float)$amount, 0, ',', '.');
}

/**
 * Format uang dengan desimal
 */
function formatUangDecimal($amount, $decimal = 2, $prefix = 'Rp ') {
    if ($amount === null || $amount === '') {
        return '-';
    }
    
    return $prefix . number_format((float)$amount, $decimal, ',', '.');
}

/**
 * Format angka dengan pemisah ribuan
 */
function formatAngka($number) {
    if ($number === null || $number === '') {
        return '-';
    }
    
    return number_format((float)$number, 0, ',', '.');
}

/**
 * Format persentase
 */
function formatPersentase($value, $decimal = 2) {
    if ($value === null || $value === '') {
        return '-';
    }
    
    return number_format((float)$value, $decimal, ',', '.') . '%';
}

/**
 * Format nomor telepon Indonesia
 */
function formatTelepon($phone) {
    if (empty($phone)) {
        return '-';
    }
    
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Format based on length
    if (strlen($phone) >= 10) {
        if (substr($phone, 0, 2) === '62') {
            // International format: +62 812-3456-7890
            return '+62 ' . substr($phone, 2, 3) . '-' . substr($phone, 5, 4) . '-' . substr($phone, 9);
        } elseif (substr($phone, 0, 1) === '0') {
            // National format: 0812-3456-7890
            return '0' . substr($phone, 1, 3) . '-' . substr($phone, 4, 4) . '-' . substr($phone, 8);
        }
    }
    
    return $phone;
}

/**
 * Format status badge
 */
function formatStatusBadge($status, $type = 'anggota') {
    $badges = [
        'anggota' => [
            'aktif' => 'success',
            'nonaktif' => 'secondary',
            'keluar' => 'danger',
            'pending' => 'warning'
        ],
        'pinjaman' => [
            'aktif' => 'primary',
            'lunas' => 'success',
            'macet' => 'danger',
            'pending' => 'warning'
        ],
        'simpanan' => [
            'aktif' => 'success',
            'nonaktif' => 'secondary'
        ]
    ];
    
    $badgeClass = $badges[$type][$status] ?? 'secondary';
    return "<span class=\"badge bg-{$badgeClass}\">" . ucfirst($status) . "</span>";
}

/**
 * Format jenis kelamin
 */
function formatJenisKelamin($jk) {
    $jk = strtolower($jk);
    
    switch ($jk) {
        case 'l':
        case 'laki-laki':
        case 'pria':
            return 'Laki-laki';
        case 'p':
        case 'perempuan':
        case 'wanita':
            return 'Perempuan';
        default:
            return ucfirst($jk);
    }
}

/**
 * Format ukuran file
 */
function formatUkuranFile($bytes) {
    if ($bytes === 0) return '0 Bytes';
    
    $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Format durasi waktu (dalam detik)
 */
function formatDurasi($seconds) {
    if ($seconds < 60) {
        return $seconds . ' detik';
    } elseif ($seconds < 3600) {
        $minutes = floor($seconds / 60);
        return $minutes . ' menit';
    } elseif ($seconds < 86400) {
        $hours = floor($seconds / 3600);
        return $hours . ' jam';
    } else {
        $days = floor($seconds / 86400);
        return $days . ' hari';
    }
}

/**
 * Generate JavaScript helper functions for client-side formatting
 */
function generateJsHelpers() {
    ob_start();
    ?>
    <script>
    // Client-side formatting helpers
    window.KSP = window.KSP || {};
    window.KSP.Helpers = {
        
        formatDate: function(dateString, format = 'id-ID') {
            if (!dateString) return '-';
            
            try {
                let date;
                if (dateString.includes('T')) {
                    date = new Date(dateString);
                } else if (dateString.includes('-')) {
                    const parts = dateString.split('-');
                    date = new Date(parts[0], parts[1] - 1, parts[2]);
                } else if (dateString.includes('/')) {
                    const parts = dateString.split('/');
                    date = new Date(parts[2], parts[1] - 1, parts[0]);
                } else {
                    date = new Date(dateString);
                }
                
                if (isNaN(date.getTime())) {
                    return dateString;
                }
                
                return date.toLocaleDateString(format, {
                    day: '2-digit',
                    month: '2-digit', 
                    year: 'numeric'
                });
            } catch (error) {
                console.warn('Date formatting error:', error);
                return dateString || '-';
            }
        },
        
        formatCurrency: function(amount, prefix = 'Rp ') {
            if (amount === null || amount === '') return '-';
            
            return prefix + parseFloat(amount).toLocaleString('id-ID');
        },
        
        formatNumber: function(number) {
            if (number === null || number === '') return '-';
            
            return parseFloat(number).toLocaleString('id-ID');
        },
        
        formatPhone: function(phone) {
            if (!phone) return '-';
            
            phone = phone.replace(/[^0-9]/g, '');
            
            if (phone.length >= 10) {
                if (phone.startsWith('62')) {
                    return '+62 ' + phone.substring(2, 5) + '-' + phone.substring(5, 9) + '-' + phone.substring(9);
                } else if (phone.startsWith('0')) {
                    return '0' + phone.substring(1, 4) + '-' + phone.substring(4, 8) + '-' + phone.substring(8);
                }
            }
            
            return phone;
        }
    };
    </script>
    <?php
    return ob_get_clean();
}
?>
