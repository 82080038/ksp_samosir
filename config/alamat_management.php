<?php
/**
 * Address Management System
 * KSP Samosir - Indonesian Address Integration
 */

require_once __DIR__ . '/database_multi.php';

/**
 * Get provinces list
 */
function getProvinces() {
    return addressDB()->fetchAll("SELECT id, name FROM provinces ORDER BY name");
}

/**
 * Get regencies by province
 */
function getRegencies($province_id) {
    return addressDB()->fetchAll("SELECT id, name FROM regencies WHERE province_id = ? ORDER BY name", [$province_id], 'i');
}

/**
 * Get districts by regency
 */
function getDistricts($regency_id) {
    return fetchAll("SELECT id, name FROM alamat_db.districts WHERE regency_id = ? ORDER BY name", [$regency_id], 'i') ?? [];
}

/**
 * Get villages by district
 */
function getVillages($district_id) {
    return fetchAll("SELECT id, name, kodepos FROM alamat_db.villages WHERE district_id = ? ORDER BY name", [$district_id], 'i') ?? [];
}

/**
 * Get full address by IDs
 */
function getFullAddress($province_id, $regency_id, $district_id, $village_id) {
    $sql = "SELECT 
                p.name as province_name,
                r.name as regency_name,
                d.name as district_name,
                v.name as village_name,
                v.kodepos,
                CONCAT(
                    IFNULL(v.name, ''), ', ',
                    IFNULL(d.name, ''), ', ',
                    IFNULL(r.name, ''), ', ',
                    IFNULL(p.name, '')
                ) as alamat_lengkap
            FROM alamat_db.provinces p
            LEFT JOIN alamat_db.regencies r ON r.province_id = p.id AND r.id = ?
            LEFT JOIN alamat_db.districts d ON d.regency_id = r.id AND d.id = ?
            LEFT JOIN alamat_db.villages v ON v.district_id = d.id AND v.id = ?
            WHERE p.id = ?";
    
    return fetchRow($sql, [$regency_id, $district_id, $village_id, $province_id], 'iiii');
}

/**
 * Validate address ID
 */
function validateAddressId($table, $id) {
    $valid_tables = ['province', 'regency', 'district', 'village'];
    if (!in_array($table, $valid_tables)) {
        return false;
    }
    
    $table_map = [
        'province' => 'alamat_db.provinces',
        'regency' => 'alamat_db.regencies', 
        'district' => 'alamat_db.districts',
        'village' => 'alamat_db.villages'
    ];
    
    $result = fetchRow("SELECT COUNT(*) as count FROM " . $table_map[$table] . " WHERE id = ?", [$id], 'i');
    return $result && $result['count'] > 0;
}

/**
 * Format address for display
 */
function formatAddress($alamat_data) {
    if (!$alamat_data) {
        return '-';
    }
    
    $parts = [];
    if (!empty($alamat_data['alamat'])) {
        $parts[] = $alamat_data['alamat'];
    }
    if (!empty($alamat_data['village_name'])) {
        $parts[] = $alamat_data['village_name'];
    }
    if (!empty($alamat_data['district_name'])) {
        $parts[] = $alamat_data['district_name'];
    }
    if (!empty($alamat_data['regency_name'])) {
        $parts[] = $alamat_data['regency_name'];
    }
    if (!empty($alamat_data['province_name'])) {
        $parts[] = $alamat_data['province_name'];
    }
    if (!empty($alamat_data['kodepos'])) {
        $parts[] = $alamat_data['kodepos'];
    }
    
    return implode(', ', $parts);
}

/**
 * Get address statistics
 */
function getAddressStats() {
    return fetchRow("SELECT * FROM address_stats LIMIT 1");
}

/**
 * Search address by name
 */
function searchAddress($keyword, $type = 'all') {
    $keyword = '%' . $keyword . '%';
    
    switch ($type) {
        case 'province':
            return fetchAll("SELECT id, name, 'province' as type FROM alamat_db.provinces WHERE name LIKE ? ORDER BY name", [$keyword], 's') ?? [];
        case 'regency':
            return fetchAll("
                SELECT r.id, r.name, 'regency' as type, p.name as parent_name
                FROM alamat_db.regencies r 
                JOIN alamat_db.provinces p ON r.province_id = p.id 
                WHERE r.name LIKE ? ORDER BY r.name", [$keyword], 's') ?? [];
        case 'district':
            return fetchAll("
                SELECT d.id, d.name, 'district' as type, r.name as parent_name
                FROM alamat_db.districts d 
                JOIN alamat_db.regencies r ON d.regency_id = r.id 
                WHERE d.name LIKE ? ORDER BY d.name", [$keyword], 's') ?? [];
        case 'village':
            return fetchAll("
                SELECT v.id, v.name, 'village' as type, d.name as parent_name, v.kodepos
                FROM alamat_db.villages v 
                JOIN alamat_db.districts d ON v.district_id = d.id 
                WHERE v.name LIKE ? ORDER BY v.name", [$keyword], 's') ?? [];
        default:
            $provinces = fetchAll("SELECT id, name, 'province' as type FROM alamat_db.provinces WHERE name LIKE ? ORDER BY name LIMIT 5", [$keyword], 's');
            $regencies = fetchAll("
                SELECT r.id, r.name, 'regency' as type, p.name as parent_name
                FROM alamat_db.regencies r 
                JOIN alamat_db.provinces p ON r.province_id = p.id 
                WHERE r.name LIKE ? ORDER BY r.name LIMIT 5", [$keyword], 's');
            $districts = fetchAll("
                SELECT d.id, d.name, 'district' as type, r.name as parent_name
                FROM alamat_db.districts d 
                JOIN alamat_db.regencies r ON d.regency_id = r.id 
                WHERE d.name LIKE ? ORDER BY d.name LIMIT 5", [$keyword], 's');
            $villages = fetchAll("
                SELECT v.id, v.name, 'village' as type, d.name as parent_name, v.kodepos
                FROM alamat_db.villages v 
                JOIN alamat_db.districts d ON v.district_id = d.id 
                WHERE v.name LIKE ? ORDER BY v.name LIMIT 5", [$keyword], 's');
            
            return array_merge($provinces, $regencies, $districts, $villages);
    }
}

/**
 * Get address hierarchy
 */
function getAddressHierarchy($village_id) {
    $sql = "SELECT 
                v.id as village_id, v.name as village_name, v.kodepos,
                d.id as district_id, d.name as district_name,
                r.id as regency_id, r.name as regency_name,
                p.id as province_id, p.name as province_name
            FROM ref_villages v
            JOIN ref_districts d ON v.district_id = d.id
            JOIN ref_regencies r ON d.regency_id = r.id
            JOIN ref_provinces p ON r.province_id = p.id
            WHERE v.id = ?";
    
    return fetchRow($sql, [$village_id], 'i');
}

/**
 * Update anggota address
 */
function updateAnggotaAddress($anggota_id, $province_id, $regency_id, $district_id, $village_id, $alamat_detail) {
    // Validate address IDs
    if ($province_id && !validateAddressId('province', $province_id)) {
        throw new Exception('Invalid province ID');
    }
    if ($regency_id && !validateAddressId('regency', $regency_id)) {
        throw new Exception('Invalid regency ID');
    }
    if ($district_id && !validateAddressId('district', $district_id)) {
        throw new Exception('Invalid district ID');
    }
    if ($village_id && !validateAddressId('village', $village_id)) {
        throw new Exception('Invalid village ID');
    }
    
    $sql = "UPDATE anggota SET 
                province_id = ?, 
                regency_id = ?, 
                district_id = ?, 
                village_id = ?, 
                alamat = ? 
            WHERE id = ?";
    
    return executeNonQuery($sql, [$province_id, $regency_id, $district_id, $village_id, $alamat_detail, $anggota_id], 'iiissi');
}

/**
 * Get anggota with full address
 */
function getAnggotaWithAddress($anggota_id) {
    $sql = "SELECT 
                a.*,
                p.name as province_name,
                r.name as regency_name,
                d.name as district_name,
                v.name as village_name,
                v.kodepos
            FROM anggota a
            LEFT JOIN ref_provinces p ON a.province_id = p.id
            LEFT JOIN ref_regencies r ON a.regency_id = r.id
            LEFT JOIN ref_districts d ON a.district_id = d.id
            LEFT JOIN ref_villages v ON a.village_id = v.id
            WHERE a.id = ?";
    
    return fetchRow($sql, [$anggota_id], 'i');
}

/**
 * Generate address options for dropdown
 */
function generateAddressOptions($type, $selected_id = null, $parent_id = null) {
    $options = '';
    
    switch ($type) {
        case 'province':
            $provinces = getProvinces();
            foreach ($provinces as $province) {
                $selected = $province['id'] == $selected_id ? 'selected' : '';
                $options .= "<option value='{$province['id']}' {$selected}>{$province['name']}</option>";
            }
            break;
        case 'regency':
            if ($parent_id) {
                $regencies = getRegencies($parent_id);
                foreach ($regencies as $regency) {
                    $selected = $regency['id'] == $selected_id ? 'selected' : '';
                    $options .= "<option value='{$regency['id']}' {$selected}>{$regency['name']}</option>";
                }
            }
            break;
        case 'district':
            if ($parent_id) {
                $districts = getDistricts($parent_id);
                foreach ($districts as $district) {
                    $selected = $district['id'] == $selected_id ? 'selected' : '';
                    $options .= "<option value='{$district['id']}' {$selected}>{$district['name']}</option>";
                }
            }
            break;
        case 'village':
            if ($parent_id) {
                $villages = getVillages($parent_id);
                foreach ($villages as $village) {
                    $selected = $village['id'] == $selected_id ? 'selected' : '';
                    $kodepos = $village['kodepos'] ? " ({$village['kodepos']})" : '';
                    $options .= "<option value='{$village['id']}' {$selected}>{$village['name']}{$kodepos}</option>";
                }
            }
            break;
    }
    
    return $options;
}
?>
