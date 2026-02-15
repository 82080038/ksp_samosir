<?php
/**
 * People Service - Integration dengan people_db
 * Manages user data synchronization and enhanced profiles
 */

require_once __DIR__ . '/../../config/database_multi.php';

class PeopleService {
    
    /**
     * Sync anggota ke people_db.users
     */
    public static function syncAnggotaToPeople($anggotaData) {
        try {
            // Cek apakah user sudah ada berdasarkan email atau no_hp
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
                peopleDB()->executeNonQuery(
                    "UPDATE users SET nama = ?, email = ?, phone = ?, status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                    [
                        $anggotaData['nama_lengkap'],
                        $anggotaData['email'] ?? null,
                        $anggotaData['no_hp'] ?? null,
                        $existingUser['id']
                    ],
                    'sssi'
                );
                $userId = $existingUser['id'];
            } else {
                // Create new user
                $result = peopleDB()->executeNonQuery(
                    "INSERT INTO users (nama, email, phone, password_hash, status, preferred_channel, preferred_language, timezone) VALUES (?, ?, ?, ?, 'active', 'email', 'id', 'Asia/Jakarta')",
                    [
                        $anggotaData['nama_lengkap'],
                        $anggotaData['email'] ?? null,
                        $anggotaData['no_hp'] ?? null,
                        password_hash('default123', PASSWORD_DEFAULT) // Default password
                    ],
                    'ssss'
                );
                $userId = $result['last_id'];
            }
            
            // Sync addresses jika ada
            if (!empty($anggotaData['alamat']) || !empty($anggotaData['kabkota_id'])) {
                self::syncUserAddress($userId, $anggotaData);
            }
            
            // Sync identity data
            if (!empty($anggotaData['nik'])) {
                self::syncUserIdentity($userId, $anggotaData);
            }
            
            // Sync employment data
            if (!empty($anggotaData['pekerjaan'])) {
                self::syncUserEmployment($userId, $anggotaData);
            }
            
            return [
                'success' => true,
                'people_user_id' => $userId,
                'message' => 'User synchronized successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Error syncing anggota to people_db: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to sync user data'
            ];
        }
    }
    
    /**
     * Sync user address ke people_db.addresses
     */
    private static function syncUserAddress($userId, $anggotaData) {
        try {
            // Check if address already exists
            $existingAddress = peopleDB()->fetchRow(
                "SELECT id FROM addresses WHERE user_id = ? AND address_type = 'home'",
                [$userId],
                'i'
            );
            
            $addressData = [
                'user_id' => $userId,
                'address_type' => 'home',
                'is_primary' => 1,
                'country' => 'Indonesia',
                'province_id' => $anggotaData['provinsi_id'] ?? null,
                'city_id' => $anggotaData['kabkota_id'] ?? null,
                'district_id' => $anggotaData['kecamatan_id'] ?? null,
                'village_id' => $anggotaData['kelurahan_id'] ?? null,
                'postal_code' => $anggotaData['kode_pos'] ?? null,
                'street_address' => $anggotaData['alamat'] ?? null,
                'alamat_detil' => $anggotaData['alamat_detail'] ?? null
            ];
            
            if ($existingAddress) {
                // Update existing address
                peopleDB()->executeNonQuery(
                    "UPDATE addresses SET province_id = ?, city_id = ?, district_id = ?, village_id = ?, postal_code = ?, street_address = ?, alamat_detil = ? WHERE id = ?",
                    [
                        $addressData['province_id'],
                        $addressData['city_id'],
                        $addressData['district_id'],
                        $addressData['village_id'],
                        $addressData['postal_code'],
                        $addressData['street_address'],
                        $addressData['alamat_detil'],
                        $existingAddress['id']
                    ],
                    'iiissss'
                );
            } else {
                // Create new address
                peopleDB()->executeNonQuery(
                    "INSERT INTO addresses (user_id, address_type, is_primary, country, province_id, city_id, district_id, village_id, postal_code, street_address, alamat_detil) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $addressData['user_id'],
                        $addressData['address_type'],
                        $addressData['is_primary'],
                        $addressData['country'],
                        $addressData['province_id'],
                        $addressData['city_id'],
                        $addressData['district_id'],
                        $addressData['village_id'],
                        $addressData['postal_code'],
                        $addressData['street_address'],
                        $addressData['alamat_detil']
                    ],
                    'iisiiiiisss'
                );
            }
            
        } catch (Exception $e) {
            error_log("Error syncing user address: " . $e->getMessage());
        }
    }
    
    /**
     * Sync user identity data
     */
    private static function syncUserIdentity($userId, $anggotaData) {
        try {
            // Check if identity already exists
            $existingIdentity = peopleDB()->fetchRow(
                "SELECT id FROM identities WHERE user_id = ? AND identity_type = 'KTP'",
                [$userId],
                'i'
            );
            
            $identityData = [
                'user_id' => $userId,
                'identity_type' => 'KTP',
                'identity_number' => $anggotaData['nik'],
                'full_name' => $anggotaData['nama_lengkap'],
                'place_of_birth' => $anggotaData['tempat_lahir'] ?? null,
                'date_of_birth' => $anggotaData['tanggal_lahir'] ?? null,
                'gender' => $anggotaData['jenis_kelamin'] ?? null,
                'is_verified' => false,
                'verification_method' => 'admin'
            ];
            
            if ($existingIdentity) {
                // Update existing identity
                peopleDB()->executeNonQuery(
                    "UPDATE identities SET identity_number = ?, full_name = ?, place_of_birth = ?, date_of_birth = ?, gender = ? WHERE id = ?",
                    [
                        $identityData['identity_number'],
                        $identityData['full_name'],
                        $identityData['place_of_birth'],
                        $identityData['date_of_birth'],
                        $identityData['gender'],
                        $existingIdentity['id']
                    ],
                    'sssssi'
                );
            } else {
                // Create new identity
                peopleDB()->executeNonQuery(
                    "INSERT INTO identities (user_id, identity_type, identity_number, full_name, place_of_birth, date_of_birth, gender, is_verified, verification_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $identityData['user_id'],
                        $identityData['identity_type'],
                        $identityData['identity_number'],
                        $identityData['full_name'],
                        $identityData['place_of_birth'],
                        $identityData['date_of_birth'],
                        $identityData['gender'],
                        $identityData['is_verified'],
                        $identityData['verification_method']
                    ],
                    'isssssis'
                );
            }
            
        } catch (Exception $e) {
            error_log("Error syncing user identity: " . $e->getMessage());
        }
    }
    
    /**
     * Sync user employment data
     */
    private static function syncUserEmployment($userId, $anggotaData) {
        try {
            // Check if employment already exists
            $existingEmployment = peopleDB()->fetchRow(
                "SELECT id FROM employment_records WHERE user_id = ? AND is_current = 1",
                [$userId],
                'i'
            );
            
            $employmentData = [
                'user_id' => $userId,
                'company_name' => $anggotaData['pekerjaan'] ?? null,
                'position' => $anggotaData['jabatan'] ?? null,
                'department' => $anggotaData['satker'] ?? null,
                'employment_type' => 'permanent',
                'is_current' => 1,
                'start_date' => date('Y-m-d')
            ];
            
            if ($existingEmployment) {
                // Update existing employment
                peopleDB()->executeNonQuery(
                    "UPDATE employment_records SET company_name = ?, position = ?, department = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                    [
                        $employmentData['company_name'],
                        $employmentData['position'],
                        $employmentData['department'],
                        $existingEmployment['id']
                    ],
                    'sssi'
                );
            } else {
                // Create new employment
                peopleDB()->executeNonQuery(
                    "INSERT INTO employment_records (user_id, company_name, position, department, employment_type, is_current, start_date) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $employmentData['user_id'],
                        $employmentData['company_name'],
                        $employmentData['position'],
                        $employmentData['department'],
                        $employmentData['employment_type'],
                        $employmentData['is_current'],
                        $employmentData['start_date']
                    ],
                    'issssis'
                );
            }
            
        } catch (Exception $e) {
            error_log("Error syncing user employment: " . $e->getMessage());
        }
    }
    
    /**
     * Get user profile from people_db
     */
    public static function getUserProfile($anggotaId) {
        try {
            // Get people_user_id from anggota table
            $anggota = coreDB()->fetchRow(
                "SELECT people_user_id, nama_lengkap, email, no_hp FROM anggota WHERE id = ?",
                [$anggotaId],
                'i'
            );
            
            if (!$anggota || !$anggota['people_user_id']) {
                return [
                    'success' => false,
                    'message' => 'User not found in people_db'
                ];
            }
            
            $userId = $anggota['people_user_id'];
            
            // Get user data
            $user = peopleDB()->fetchRow(
                "SELECT * FROM users WHERE id = ?",
                [$userId],
                'i'
            );
            
            // Get addresses
            $addresses = peopleDB()->fetchAll(
                "SELECT * FROM addresses WHERE user_id = ?",
                [$userId],
                'i'
            );
            
            // Get identities
            $identities = peopleDB()->fetchAll(
                "SELECT * FROM identities WHERE user_id = ?",
                [$userId],
                'i'
            );
            
            // Get employment
            $employment = peopleDB()->fetchAll(
                "SELECT * FROM employment_records WHERE user_id = ? ORDER BY start_date DESC",
                [$userId],
                'i'
            );
            
            return [
                'success' => true,
                'data' => [
                    'user' => $user,
                    'addresses' => $addresses,
                    'identities' => $identities,
                    'employment' => $employment
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error getting user profile: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get user profile'
            ];
        }
    }
    
    /**
     * Update anggota table dengan people_user_id
     */
    public static function updateAnggotaPeopleId($anggotaId, $peopleUserId) {
        try {
            coreDB()->executeNonQuery(
                "UPDATE anggota SET people_user_id = ? WHERE id = ?",
                [$peopleUserId, $anggotaId],
                'ii'
            );
            
            return [
                'success' => true,
                'message' => 'Anggota updated with people_user_id'
            ];
            
        } catch (Exception $e) {
            error_log("Error updating anggota people_user_id: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update anggota'
            ];
        }
    }
    
    /**
     * Get people_db statistics
     */
    public static function getPeopleStatistics() {
        try {
            return [
                'total_users' => (peopleDB()->fetchRow("SELECT COUNT(*) as count FROM users") ?? [])['count'] ?? 0,
                'active_users' => (peopleDB()->fetchRow("SELECT COUNT(*) as count FROM users WHERE status = 'active'") ?? [])['count'] ?? 0,
                'total_addresses' => (peopleDB()->fetchRow("SELECT COUNT(*) as count FROM addresses") ?? [])['count'] ?? 0,
                'total_identities' => (peopleDB()->fetchRow("SELECT COUNT(*) as count FROM identities") ?? [])['count'] ?? 0,
                'total_employment' => (peopleDB()->fetchRow("SELECT COUNT(*) as count FROM employment_records") ?? [])['count'] ?? 0,
                'data_source' => 'people_db',
                'last_updated' => (peopleDB()->fetchRow("SELECT MAX(updated_at) as last_updated FROM users") ?? [])['last_updated'] ?? null
            ];
        } catch (Exception $e) {
            error_log("Error getting people statistics: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Search users in people_db
     */
    public static function searchUsers($query, $limit = 10) {
        try {
            $searchTerm = "%$query%";
            
            return peopleDB()->fetchAll(
                "SELECT id, nama, email, phone, status FROM users 
                 WHERE nama LIKE ? OR email LIKE ? OR phone LIKE ? 
                 ORDER BY nama LIMIT ?",
                [$searchTerm, $searchTerm, $searchTerm, $limit],
                'sssi'
            );
            
        } catch (Exception $e) {
            error_log("Error searching users: " . $e->getMessage());
            return [];
        }
    }
}

?>
