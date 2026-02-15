<?php
/**
 * Address Service - Integration dengan alamat_db (BPS Data)
 * Menyediakan data wilayah Indonesia yang lengkap dan valid
 */

require_once __DIR__ . '/../../config/database_multi.php';

class AddressService {
    
    /**
     * Get semua provinsi dari alamat_db
     */
    public static function getProvinces() {
        try {
            return addressDB()->fetchAll(
                "SELECT id, code, name FROM provinces ORDER BY name ASC"
            );
        } catch (Exception $e) {
            error_log("Error getting provinces: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get kabupaten berdasarkan provinsi_id
     */
    public static function getRegencies($provinceId) {
        try {
            return addressDB()->fetchAll(
                "SELECT id, code, name, postal_code FROM regencies WHERE province_id = ? ORDER BY name ASC",
                [$provinceId],
                'i'
            );
        } catch (Exception $e) {
            error_log("Error getting regencies: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get kecamatan berdasarkan kabupaten_id
     */
    public static function getDistricts($regencyId) {
        try {
            return addressDB()->fetchAll(
                "SELECT id, name FROM districts WHERE regency_id = ? ORDER BY name ASC",
                [$regencyId],
                'i'
            );
        } catch (Exception $e) {
            error_log("Error getting districts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get kelurahan berdasarkan kecamatan_id
     */
    public static function getVillages($districtId) {
        try {
            return addressDB()->fetchAll(
                "SELECT id, name, postal_code FROM villages WHERE district_id = ? ORDER BY name ASC",
                [$districtId],
                'i'
            );
        } catch (Exception $e) {
            error_log("Error getting villages: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get detail alamat lengkap
     */
    public static function getFullAddress($provinceId, $regencyId, $districtId, $villageId) {
        try {
            $province = addressDB()->fetchRow(
                "SELECT name FROM provinces WHERE id = ?",
                [$provinceId],
                'i'
            );
            
            $regency = addressDB()->fetchRow(
                "SELECT name, postal_code FROM regencies WHERE id = ?",
                [$regencyId],
                'i'
            );
            
            $district = addressDB()->fetchRow(
                "SELECT name FROM districts WHERE id = ?",
                [$districtId],
                'i'
            );
            
            $village = addressDB()->fetchRow(
                "SELECT name, postal_code FROM villages WHERE id = ?",
                [$villageId],
                'i'
            );
            
            return [
                'province' => $province['name'] ?? '',
                'regency' => $regency['name'] ?? '',
                'district' => $district['name'] ?? '',
                'village' => $village['name'] ?? '',
                'postal_code' => $village['postal_code'] ?? $regency['postal_code'] ?? '',
                'full_address' => sprintf(
                    "%s, %s, %s, %s %s",
                    $village['name'] ?? '',
                    $district['name'] ?? '',
                    $regency['name'] ?? '',
                    $province['name'] ?? '',
                    $village['postal_code'] ?? $regency['postal_code'] ?? ''
                )
            ];
        } catch (Exception $e) {
            error_log("Error getting full address: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validasi kode BPS
     */
    public static function validateBPSCode($type, $code) {
        try {
            switch ($type) {
                case 'province':
                    $result = addressDB()->fetchRow(
                        "SELECT id FROM provinces WHERE code = ?",
                        [$code],
                        's'
                    );
                    return !empty($result);
                    
                case 'regency':
                    $result = addressDB()->fetchRow(
                        "SELECT id FROM regencies WHERE code = ?",
                        [$code],
                        's'
                    );
                    return !empty($result);
                    
                default:
                    return false;
            }
        } catch (Exception $e) {
            error_log("Error validating BPS code: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Search alamat ( untuk autocomplete )
     */
    public static function searchAddress($query, $limit = 10) {
        try {
            $searchTerm = "%$query%";
            
            // Search di semua level
            $provinces = addressDB()->fetchAll(
                "SELECT 'province' as type, id, name, code, '' as parent_id FROM provinces WHERE name LIKE ? LIMIT ?",
                [$searchTerm, $limit],
                'si'
            );
            
            $regencies = addressDB()->fetchAll(
                "SELECT 'regency' as type, id, name, code, province_id as parent_id FROM regencies WHERE name LIKE ? LIMIT ?",
                [$searchTerm, $limit],
                'si'
            );
            
            $districts = addressDB()->fetchAll(
                "SELECT 'district' as type, id, name, '' as code, regency_id as parent_id FROM districts WHERE name LIKE ? LIMIT ?",
                [$searchTerm, $limit],
                'si'
            );
            
            $villages = addressDB()->fetchAll(
                "SELECT 'village' as type, id, name, '' as code, district_id as parent_id FROM villages WHERE name LIKE ? LIMIT ?",
                [$searchTerm, $limit],
                'si'
            );
            
            // Combine dan limit hasil
            $allResults = array_merge($provinces, $regencies, $districts, $villages);
            return array_slice($allResults, 0, $limit);
            
        } catch (Exception $e) {
            error_log("Error searching address: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get postal code berdasarkan lokasi
     */
    public static function getPostalCode($provinceId, $regencyId, $districtId = null, $villageId = null) {
        try {
            // Priority: village > district > regency
            if ($villageId) {
                $result = addressDB()->fetchRow(
                    "SELECT postal_code FROM villages WHERE id = ? AND postal_code IS NOT NULL",
                    [$villageId],
                    'i'
                );
                if ($result) return $result['postal_code'];
            }
            
            if ($districtId) {
                $result = addressDB()->fetchRow(
                    "SELECT postal_code FROM villages WHERE district_id = ? AND postal_code IS NOT NULL LIMIT 1",
                    [$districtId],
                    'i'
                );
                if ($result) return $result['postal_code'];
            }
            
            if ($regencyId) {
                $result = addressDB()->fetchRow(
                    "SELECT postal_code FROM regencies WHERE id = ? AND postal_code IS NOT NULL",
                    [$regencyId],
                    'i'
                );
                if ($result) return $result['postal_code'];
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error getting postal code: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Sync data anggota dengan alamat_db
     */
    public static function syncMemberAddress($anggotaId, $addressData) {
        try {
            // Update anggota table dengan address IDs
            coreDB()->executeNonQuery(
                "UPDATE anggota SET 
                 provinsi_id = ?, kabupaten_id = ?, kecamatan_id = ?, kelurahan_id = ?, 
                 kode_pos = ? WHERE id = ?",
                [
                    $addressData['province_id'] ?? null,
                    $addressData['regency_id'] ?? null,
                    $addressData['district_id'] ?? null,
                    $addressData['village_id'] ?? null,
                    $addressData['postal_code'] ?? null,
                    $anggotaId
                ],
                'iiissi'
            );
            
            return [
                'success' => true,
                'message' => 'Address synced successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Error syncing member address: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to sync address'
            ];
        }
    }
    
    /**
     * Get statistics untuk admin
     */
    public static function getAddressStatistics() {
        try {
            return [
                'total_provinces' => (addressDB()->fetchRow("SELECT COUNT(*) as count FROM provinces") ?? [])['count'] ?? 0,
                'total_regencies' => (addressDB()->fetchRow("SELECT COUNT(*) as count FROM regencies") ?? [])['count'] ?? 0,
                'total_districts' => (addressDB()->fetchRow("SELECT COUNT(*) as count FROM districts") ?? [])['count'] ?? 0,
                'total_villages' => (addressDB()->fetchRow("SELECT COUNT(*) as count FROM villages") ?? [])['count'] ?? 0,
                'data_source' => 'alamat_db (BPS Data - English Tables)',
                'last_updated' => (addressDB()->fetchRow("SELECT MAX(created_at) as last_updated FROM regencies") ?? [])['last_updated'] ?? null
            ];
        } catch (Exception $e) {
            error_log("Error getting address statistics: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validate address completeness
     */
    public static function validateAddressCompleteness($addressData) {
        $required = ['province_id', 'regency_id'];
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($addressData[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            return [
                'valid' => false,
                'missing' => $missing,
                'message' => 'Required fields missing: ' . implode(', ', $missing)
            ];
        }
        
        // Validate that IDs exist in alamat_db
        $province = addressDB()->fetchRow(
            "SELECT id FROM provinces WHERE id = ?",
            [$addressData['province_id']],
            'i'
        );
        
        if (!$province) {
            return [
                'valid' => false,
                'message' => 'Invalid province ID'
            ];
        }
        
        $regency = addressDB()->fetchRow(
            "SELECT id FROM regencies WHERE id = ? AND province_id = ?",
            [$addressData['regency_id'], $addressData['province_id']],
            'ii'
        );
        
        if (!$regency) {
            return [
                'valid' => false,
                'message' => 'Invalid regency ID or does not belong to selected province'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Address is valid'
        ];
    }
}

?>
