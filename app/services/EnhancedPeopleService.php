<?php
/**
 * Enhanced People Service
 * KSP Samosir - Complete People Data Management
 * Integration with people_db microservice
 */

require_once __DIR__ . '/../../config/database_multi.php';

class EnhancedPeopleService {
    
    /**
     * Get complete people profile
     */
    public static function getPeopleProfile($userId, $type = 'ksp_anggota_id') {
        try {
            $whereClause = $type === 'ksp_anggota_id' ? 'ksp_anggota_id' : 'ksp_user_id';
            
            $profile = peopleDB()->fetchRow("
                SELECT * FROM v_people_profile 
                WHERE $whereClause = ?
            ", [$userId], 'i');
            
            if ($profile) {
                // Get additional data
                $profile['contacts'] = self::getPersonContacts($profile['people_user_id']);
                $profile['addresses'] = self::getPersonAddresses($profile['people_user_id']);
                $profile['documents'] = self::getPersonDocuments($profile['people_user_id']);
                $profile['employment'] = self::getPersonEmployment($profile['people_user_id']);
            }
            
            return [
                'success' => true,
                'data' => $profile
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting profile: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Sync anggota to people_db with enhanced data
     */
    public static function syncAnggotaToPeopleEnhanced($anggotaData) {
        try {
            // Start transaction manually
            peopleDB()->executeNonQuery("START TRANSACTION");
            
            // Check existing user
            $existingUser = null;
            if (!empty($anggotaData['email'])) {
                $existingUser = peopleDB()->fetchRow(
                    "SELECT id FROM users WHERE email = ?",
                    [$anggotaData['email']],
                    's'
                );
            }
            
            if (!$existingUser && !empty($anggotaData['no_hp'])) {
                $existingUser = peopleDB()->fetchRow(
                    "SELECT id FROM users WHERE phone = ?",
                    [$anggotaData['no_hp']],
                    's'
                );
            }
            
            $userId = null;
            
            if ($existingUser) {
                // Update existing user
                peopleDB()->executeNonQuery("
                    UPDATE users SET 
                        nama = ?, 
                        email = ?, 
                        phone = ?, 
                        ksp_anggota_id = ?,
                        ksp_member_type = 'anggota',
                        ksp_member_number = ?,
                        ksp_status = 'active',
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ", [
                    $anggotaData['nama_lengkap'],
                    $anggotaData['email'] ?? null,
                    $anggotaData['no_hp'] ?? null,
                    $anggotaData['id'],
                    $anggotaData['no_anggota'] ?? null,
                    $existingUser['id']
                ], 'sssisi');
                
                $userId = $existingUser['id'];
            } else {
                // Create new user
                peopleDB()->executeNonQuery("
                    INSERT INTO users (
                        nama, email, phone, password_hash, status, ksp_anggota_id, ksp_member_type, 
                        ksp_join_date, ksp_status, ksp_member_number
                    ) VALUES (?, ?, ?, ?, 'active', ?, 'anggota', ?, 'active', ?)
                ", [
                    $anggotaData['nama_lengkap'],
                    $anggotaData['email'] ?? null,
                    $anggotaData['no_hp'] ?? null,
                    '', // Empty password hash for people_db
                    $anggotaData['id'],
                    $anggotaData['tanggal_gabung'] ?? date('Y-m-d'),
                    $anggotaData['no_anggota'] ?? null
                ], 'sssssss');
                
                $userId = (peopleDB()->fetchRow("SELECT LAST_INSERT_ID() as id") ?? [])['id'] ?? 0;
            }
            
            // Sync identity data
            if (!empty($anggotaData['nik']) || !empty($anggotaData['tempat_lahir']) || !empty($anggotaData['tanggal_lahir'])) {
                $existingIdentity = peopleDB()->fetchRow(
                    "SELECT id FROM identities WHERE user_id = ?",
                    [$userId],
                    'i'
                );
                
                if ($existingIdentity) {
                    peopleDB()->executeNonQuery("
                        UPDATE identities SET 
                            nik = ?, 
                            tempat_lahir = ?, 
                            tanggal_lahir = ?,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE user_id = ?
                    ", [
                        $anggotaData['nik'] ?? null,
                        $anggotaData['tempat_lahir'] ?? null,
                        $anggotaData['tanggal_lahir'] ?? null,
                        $userId
                    ], 'sssi');
                } else {
                    peopleDB()->executeNonQuery("
                        INSERT INTO identities (
                            user_id, nik, tempat_lahir, tanggal_lahir, status
                        ) VALUES (?, ?, ?, ?, 'complete')
                    ", [
                        $userId,
                        $anggotaData['nik'] ?? null,
                        $anggotaData['tempat_lahir'] ?? null,
                        $anggotaData['tanggal_lahir'] ?? null
                    ], 'isss');
                }
            }
            
            // Sync address
            if (!empty($anggotaData['alamat'])) {
                $existingAddress = peopleDB()->fetchRow(
                    "SELECT id FROM addresses WHERE user_id = ? AND is_primary = 1",
                    [$userId],
                    'i'
                );
                
                if ($existingAddress) {
                    peopleDB()->executeNonQuery("
                        UPDATE addresses SET 
                            street_address = ?, 
                            updated_at = CURRENT_TIMESTAMP
                        WHERE user_id = ? AND is_primary = 1
                    ", [
                        $anggotaData['alamat'],
                        $userId
                    ], 'si');
                } else {
                    peopleDB()->executeNonQuery("
                        INSERT INTO addresses (
                            user_id, street_address, address_type, is_primary
                        ) VALUES (?, ?, 'home', 1)
                    ", [
                        $userId,
                        $anggotaData['alamat']
                    ], 'is');
                }
            }
            
            // Sync contacts - disabled temporarily due to trigger conflicts
            // if (!empty($anggotaData['email'])) {
            //     self::addContactEmail($userId, $anggotaData['email'], true);
            // }
            
            // if (!empty($anggotaData['no_hp'])) {
            //     self::addContactPhone($userId, $anggotaData['no_hp'], true);
            // }
            
            peopleDB()->executeNonQuery("COMMIT");
            
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'Anggota synced successfully to people_db'
            ];
            
        } catch (Exception $e) {
            peopleDB()->executeNonQuery("ROLLBACK");
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get person contacts
     */
    private static function getPersonContacts($userId) {
        $emails = peopleDB()->fetchAll("
            SELECT email, is_primary, created_at 
            FROM contact_emails 
            WHERE user_id = ?
        ", [$userId], 'i');
        
        $phones = peopleDB()->fetchAll("
            SELECT phone, is_primary, created_at 
            FROM contact_phones 
            WHERE user_id = ?
        ", [$userId], 'i');
        
        return [
            'emails' => $emails,
            'phones' => $phones
        ];
    }
    
    /**
     * Get person addresses
     */
    private static function getPersonAddresses($userId) {
        return peopleDB()->fetchAll("
            SELECT *, 
                   CASE 
                       WHEN province_id IS NOT NULL THEN 
                           (SELECT name FROM alamat_db.provinces WHERE id = province_id)
                       ELSE NULL
                   END as province_name
            FROM addresses 
            WHERE user_id = ?
            ORDER BY is_primary DESC, created_at DESC
        ", [$userId], 'i');
    }
    
    /**
     * Get person documents
     */
    private static function getPersonDocuments($userId) {
        return peopleDB()->fetchAll("
            SELECT * FROM user_identity_documents 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ", [$userId], 'i');
    }
    
    /**
     * Get person employment
     */
    private static function getPersonEmployment($userId) {
        return peopleDB()->fetchAll("
            SELECT * FROM employment_records 
            WHERE user_id = ?
            ORDER BY end_date DESC, created_at DESC
        ", [$userId], 'i');
    }
    
    /**
     * Add contact email
     */
    private static function addContactEmail($userId, $email, $isPrimary = false) {
        // Check if email already exists
        $existing = peopleDB()->fetchRow(
            "SELECT id FROM contact_emails WHERE user_id = ? AND email = ?",
            [$userId, $email],
            'is'
        );
        
        if (!$existing) {
            // If this is primary, unset other primaries
            if ($isPrimary) {
                peopleDB()->executeNonQuery(
                    "UPDATE contact_emails SET is_primary = 0 WHERE user_id = ?",
                    [$userId],
                    'i'
                );
            }
            
            peopleDB()->executeNonQuery(
                "INSERT INTO contact_emails (user_id, email, is_primary) VALUES (?, ?, ?)",
                [$userId, $email, $isPrimary ? 1 : 0],
                'isi'
            );
        }
    }
    
    /**
     * Add contact phone
     */
    private static function addContactPhone($userId, $phone, $isPrimary = false) {
        // Check if phone already exists
        $existing = peopleDB()->fetchRow(
            "SELECT id FROM contact_phones WHERE user_id = ? AND phone = ?",
            [$userId, $phone],
            'is'
        );
        
        if (!$existing) {
            // If this is primary, unset other primaries
            if ($isPrimary) {
                peopleDB()->executeNonQuery(
                    "UPDATE contact_phones SET is_primary = 0 WHERE user_id = ?",
                    [$userId],
                    'i'
                );
            }
            
            peopleDB()->executeNonQuery(
                "INSERT INTO contact_phones (user_id, phone, is_primary) VALUES (?, ?, ?)",
                [$userId, $phone, $isPrimary ? 1 : 0],
                'isi'
            );
        }
    }
    
    /**
     * Search people with advanced filters
     */
    public static function searchPeople($query, $filters = [], $limit = 20, $offset = 0) {
        try {
            $whereConditions = ["(nama LIKE ? OR email LIKE ? OR phone LIKE ?)"];
            $params = ["%$query%", "%$query%", "%$query%"];
            $types = "sss";
            
            // Add filters
            if (!empty($filters['member_type'])) {
                $whereConditions[] = "ksp_member_type = ?";
                $params[] = $filters['member_type'];
                $types .= "s";
            }
            
            if (!empty($filters['ksp_status'])) {
                $whereConditions[] = "ksp_status = ?";
                $params[] = $filters['ksp_status'];
                $types .= "s";
            }
            
            if (!empty($filters['age_min'])) {
                $whereConditions[] = "TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= ?";
                $params[] = $filters['age_min'];
                $types .= "i";
            }
            
            if (!empty($filters['age_max'])) {
                $whereConditions[] = "TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) <= ?";
                $params[] = $filters['age_max'];
                $types .= "i";
            }
            
            $whereClause = "WHERE " . implode(" AND ", $whereConditions);
            
            $sql = "
                SELECT * FROM v_ksp_members 
                $whereClause
                ORDER BY nama ASC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
            
            $results = peopleDB()->fetchAll($sql, $params, $types);
            
            // Get total count
            $countSql = "
                SELECT COUNT(*) as total FROM v_ksp_members 
                $whereClause
            ";
            
            $countParams = array_slice($params, 0, -2); // Remove limit and offset
            $countTypes = substr($types, 0, -2);
            
            $total = (peopleDB()->fetchRow($countSql, $countParams, $countTypes) ?? [])['total'] ?? 0;
            
            return [
                'success' => true,
                'data' => $results,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $total
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get people statistics
     */
    public static function getPeopleStatistics() {
        try {
            $stats = peopleDB()->fetchRow("
                SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_users,
                    COUNT(CASE WHEN ksp_anggota_id IS NOT NULL THEN 1 END) as total_anggota,
                    COUNT(CASE WHEN ksp_member_type = 'anggota' THEN 1 END) as anggota_count,
                    COUNT(CASE WHEN ksp_member_type = 'pengurus' THEN 1 END) as pengurus_count,
                    COUNT(CASE WHEN ksp_member_type = 'pengawas' THEN 1 END) as pengawas_count,
                    COUNT(CASE WHEN ksp_member_type = 'investor' THEN 1 END) as investor_count,
                    COUNT(CASE WHEN ksp_status = 'active' THEN 1 END) as active_members,
                    COUNT(CASE WHEN ksp_status = 'inactive' THEN 1 END) as inactive_members,
                    COUNT(CASE WHEN ksp_status = 'suspended' THEN 1 END) as suspended_members,
                    AVG(CASE WHEN i.tanggal_lahir IS NOT NULL THEN TIMESTAMPDIFF(YEAR, i.tanggal_lahir, CURDATE()) ELSE NULL END) as avg_age,
                    'people_db' as data_source,
                    MAX(u.updated_at) as last_updated
                FROM people_db.users u
                LEFT JOIN people_db.identities i ON u.id = i.user_id
                WHERE u.ksp_anggota_id IS NOT NULL OR u.ksp_user_id IS NOT NULL
            ");
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Statistics failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Log API access
     */
    public static function logApiAccess($endpoint, $method, $userId, $ipAddress, $requestData, $responseCode, $responseTime) {
        try {
            peopleDB()->executeNonQuery("
                INSERT INTO api_access_logs (
                    endpoint, method, user_id, ip_address, request_data, response_code, response_time_ms
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ", [
                $endpoint,
                $method,
                $userId,
                $ipAddress,
                json_encode($requestData),
                $responseCode,
                $responseTime
            ], 'ssisisd');
        } catch (Exception $e) {
            error_log("Failed to log API access: " . $e->getMessage());
        }
    }
    
}

?>
