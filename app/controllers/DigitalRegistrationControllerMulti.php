<?php
/**
 * Digital Registration Controller - Multi-Database Version
 * Updated untuk arsitektur 5 database KSP Samosir
 */

require_once __DIR__ . '/../config/database_multi.php';
require_once __DIR__ . '/../config/database_safe.php';

class DigitalRegistrationController {
    private $conn;
    private $registrationConn;
    private $systemConn;
    
    public function __construct() {
        try {
            $this->conn = DatabaseManager::getConnection('core');        // For anggota data
            $this->registrationConn = DatabaseManager::getConnection('registration'); // For registration data
            $this->systemConn = DatabaseManager::getConnection('system'); // For logs & configs
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    /**
     * Get registration form
     */
    public function getForm() {
        try {
            $form = registrationDB()->fetchRow(
                "SELECT * FROM registration_forms WHERE is_active = 1 ORDER BY id DESC LIMIT 1"
            );
            
            if (!$form) {
                return [
                    'success' => false,
                    'message' => 'No active registration form found'
                ];
            }
            
            return [
                'success' => true,
                'data' => [
                    'id' => $form['id'],
                    'form_name' => $form['form_name'],
                    'form_type' => $form['form_type'],
                    'form_fields' => json_decode($form['form_fields'], true),
                    'signature_required' => (bool)$form['signature_required'],
                    'photo_required' => (bool)$form['photo_required'],
                    'ktp_required' => (bool)$form['ktp_required']
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error getting form: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load registration form'
            ];
        }
    }
    
    /**
     * Save draft registration
     */
    public function saveDraft($data) {
        try {
            // Validate required fields
            if (!isset($data['form_id']) || !isset($data['personal_data'])) {
                return [
                    'success' => false,
                    'message' => 'Missing required fields'
                ];
            }
            
            // Generate submission token
            $submissionToken = 'REG_' . date('Y') . '_' . uniqid();
            
            // Check if this is an update or new submission
            $existingSubmission = null;
            if (isset($data['submission_id'])) {
                $existingSubmission = registrationDB()->fetchRow(
                    "SELECT * FROM registration_submissions WHERE id = ? AND status = 'draft'",
                    [$data['submission_id']],
                    'i'
                );
            }
            
            if ($existingSubmission) {
                // Update existing draft
                registrationDB()->executeNonQuery(
                    "UPDATE registration_submissions SET 
                     personal_data = ?, address_data = ?, financial_data = ?, 
                     document_data = ?, updated_at = CURRENT_TIMESTAMP 
                     WHERE id = ?",
                    [
                        json_encode($data['personal_data']),
                        json_encode($data['address_data'] ?? []),
                        json_encode($data['financial_data'] ?? []),
                        json_encode($data['document_data'] ?? []),
                        $existingSubmission['id']
                    ],
                    'ssssi'
                );
                
                $submissionId = $existingSubmission['id'];
            } else {
                // Create new draft
                $result = registrationDB()->executeNonQuery(
                    "INSERT INTO registration_submissions 
                     (form_id, submission_token, personal_data, address_data, financial_data, document_data, status) 
                     VALUES (?, ?, ?, ?, ?, ?, 'draft')",
                    [
                        $data['form_id'],
                        $submissionToken,
                        json_encode($data['personal_data']),
                        json_encode($data['address_data'] ?? []),
                        json_encode($data['financial_data'] ?? []),
                        json_encode($data['document_data'] ?? [])
                    ],
                    'isssss'
                );
                
                $submissionId = $result['last_id'];
            }
            
            // Log the action
            systemDB()->executeNonQuery(
                "INSERT INTO audit_logs (action, table_name, record_id, new_values, ip_address, user_agent) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [
                    'save_draft',
                    'registration_submissions',
                    $submissionId,
                    json_encode($data),
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                ],
                'ssisss'
            );
            
            return [
                'success' => true,
                'message' => 'Draft saved successfully',
                'submission_id' => $submissionId,
                'token' => $submissionToken
            ];
            
        } catch (Exception $e) {
            error_log("Error saving draft: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to save draft'
            ];
        }
    }
    
    /**
     * Submit registration
     */
    public function submitRegistration($data) {
        try {
            // Validate required fields
            $required = ['submission_id', 'token', 'personal_data', 'signature_data'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "Missing required field: $field"
                    ];
                }
            }
            
            // Validate submission
            $submission = registrationDB()->fetchRow(
                "SELECT * FROM registration_submissions WHERE id = ? AND submission_token = ?",
                [$data['submission_id'], $data['token']],
                'is'
            );
            
            if (!$submission) {
                return [
                    'success' => false,
                    'message' => 'Invalid submission'
                ];
            }
            
            if ($submission['status'] !== 'draft') {
                return [
                    'success' => false,
                    'message' => 'Submission already processed'
                ];
            }
            
            // Update submission
            registrationDB()->executeNonQuery(
                "UPDATE registration_submissions SET 
                 status = 'submitted', personal_data = ?, address_data = ?, 
                 financial_data = ?, document_data = ?, signature_data = ?, 
                 photo_data = ?, submission_date = CURRENT_TIMESTAMP 
                 WHERE id = ?",
                [
                    json_encode($data['personal_data']),
                    json_encode($data['address_data'] ?? []),
                    json_encode($data['financial_data'] ?? []),
                    json_encode($data['document_data'] ?? []),
                    json_encode($data['signature_data']),
                    json_encode($data['photo_data'] ?? []),
                    $data['submission_id']
                ],
                'ssssssi'
            );
            
            // Save digital signature
            if ($data['signature_data']) {
                systemDB()->executeNonQuery(
                    "INSERT INTO digital_signatures 
                     (user_id, document_type, document_id, signature_image, signature_coordinates, device_info, ip_address, user_agent) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $data['submission_id'], // Using submission_id as user_id for now
                        'registration_form',
                        $data['submission_id'],
                        $data['signature_data']['image'] ?? null,
                        json_encode($data['signature_data']['coordinates'] ?? []),
                        json_encode($data['signature_data']['device_info'] ?? []),
                        $_SERVER['REMOTE_ADDR'] ?? '',
                        $_SERVER['HTTP_USER_AGENT'] ?? ''
                    ],
                    'isssssss'
                );
            }
            
            // Log submission
            systemDB()->executeNonQuery(
                "INSERT INTO registration_logs (submission_id, action, actor_type, description, ip_address, user_agent) 
                 VALUES (?, 'submitted', 'user', 'Registration form submitted', ?, ?)",
                [$data['submission_id'], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''],
                'isss'
            );
            
            // Send notification to admin
            $this->notifyAdmin($data['submission_id']);
            
            return [
                'success' => true,
                'message' => 'Registration submitted successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Error submitting registration: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to submit registration'
            ];
        }
    }
    
    /**
     * Get submission for review
     */
    public function getSubmission($submissionId) {
        try {
            $submission = registrationDB()->fetchRow(
                "SELECT rs.*, rf.form_name, rf.form_type 
                 FROM registration_submissions rs 
                 LEFT JOIN registration_forms rf ON rs.form_id = rf.id 
                 WHERE rs.id = ?",
                [$submissionId],
                'i'
            );
            
            if (!$submission) {
                return [
                    'success' => false,
                    'message' => 'Submission not found'
                ];
            }
            
            // Parse JSON fields
            $submission['personal_data'] = json_decode($submission['personal_data'], true);
            $submission['address_data'] = json_decode($submission['address_data'], true);
            $submission['financial_data'] = json_decode($submission['financial_data'], true);
            $submission['document_data'] = json_decode($submission['document_data'], true);
            $submission['signature_data'] = json_decode($submission['signature_data'], true);
            $submission['photo_data'] = json_decode($submission['photo_data'], true);
            
            return [
                'success' => true,
                'data' => $submission
            ];
            
        } catch (Exception $e) {
            error_log("Error getting submission: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load submission'
            ];
        }
    }
    
    /**
     * Approve registration
     */
    public function approveRegistration($submissionId, $adminId, $notes = '') {
        try {
            // Start transaction
            $this->conn->begin_transaction();
            $this->registrationConn->begin_transaction();
            $this->systemConn->begin_transaction();
            
            // Get submission data
            $submission = registrationDB()->fetchRow(
                "SELECT * FROM registration_submissions WHERE id = ?",
                [$submissionId],
                'i'
            );
            
            if (!$submission) {
                throw new Exception('Submission not found');
            }
            
            if ($submission['status'] !== 'submitted') {
                throw new Exception('Submission cannot be approved');
            }
            
            $personalData = json_decode($submission['personal_data'], true);
            $financialData = json_decode($submission['financial_data'], true);
            
            // Create anggota record in core database
            $noAnggota = $this->generateMemberNumber();
            
            coreDB()->executeNonQuery(
                "INSERT INTO anggota 
                 (no_anggota, nama_lengkap, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, 
                  alamat, no_hp, email, pekerjaan, pendapatan_bulanan, tanggal_gabung, status, registration_source) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif', 'digital')",
                [
                    $noAnggota,
                    $personalData['nama_lengkap'],
                    $personalData['no_ktp'],
                    $personalData['tempat_lahir'],
                    $personalData['tanggal_lahir'],
                    $personalData['jenis_kelamin'],
                    $personalData['alamat_lengkap'] ?? '',
                    $personalData['no_hp'],
                    $personalData['email'] ?? '',
                    $personalData['pekerjaan'] ?? '',
                    $personalData['penghasilan'] ?? 0,
                    date('Y-m-d')
                ],
                'sssssssssisss'
            );
            
            $anggotaId = $this->conn->insert_id;
            
            // Create user account
            $username = strtolower(str_replace(' ', '', $personalData['nama_lengkap'])) . rand(100, 999);
            $password = password_hash('password123', PASSWORD_DEFAULT); // Default password
            
            coreDB()->executeNonQuery(
                "INSERT INTO users (username, password, email, no_hp, anggota_id, role, status) 
                 VALUES (?, ?, ?, ?, ?, 'anggota', 'active')",
                [$username, $password, $personalData['email'] ?? '', $personalData['no_hp'], $anggotaId],
                'ssssi'
            );
            
            // Create simpanan records
            if (isset($financialData['simpanan_pokok'])) {
                $noRekeningPokok = 'SP' . date('Y') . rand(1000, 9999);
                coreDB()->executeNonQuery(
                    "INSERT INTO simpanan (anggota_id, jenis_simpanan_id, no_rekening, saldo, status) 
                     VALUES (?, 1, ?, ?, 'aktif')",
                    [$anggotaId, $noRekeningPokok, $financialData['simpanan_pokok']],
                    'isd'
                );
            }
            
            if (isset($financialData['simpanan_wajib'])) {
                $noRekeningWajib = 'SW' . date('Y') . rand(1000, 9999);
                coreDB()->executeNonQuery(
                    "INSERT INTO simpanan (anggota_id, jenis_simpanan_id, no_rekening, saldo, status) 
                     VALUES (?, 2, ?, ?, 'aktif')",
                    [$anggotaId, $noRekeningWajib, $financialData['simpanan_wajib']],
                    'isd'
                );
            }
            
            // Update submission status
            registrationDB()->executeNonQuery(
                "UPDATE registration_submissions SET 
                 status = 'approved', approved_date = CURRENT_TIMESTAMP, approved_by = ?, notes = ? 
                 WHERE id = ?",
                [$adminId, $notes, $submissionId],
                'isi'
            );
            
            // Log approval
            systemDB()->executeNonQuery(
                "INSERT INTO registration_logs (submission_id, action, actor_id, actor_type, description, ip_address) 
                 VALUES (?, 'approved', ?, 'admin', ?, ?)",
                [$submissionId, $adminId, "Registration approved. Member ID: $anggotaId", $_SERVER['REMOTE_ADDR'] ?? ''],
                'iiss'
            );
            
            // Audit log
            systemDB()->executeNonQuery(
                "INSERT INTO audit_logs (action, table_name, record_id, new_values, ip_address) 
                 VALUES ('approve_registration', 'anggota', ?, ?, ?)",
                [$anggotaId, json_encode(['member_number' => $noAnggota, 'registration_id' => $submissionId]), $_SERVER['REMOTE_ADDR'] ?? ''],
                'iss'
            );
            
            // Commit all transactions
            $this->conn->commit();
            $this->registrationConn->commit();
            $this->systemConn->commit();
            
            return [
                'success' => true,
                'message' => 'Registration approved successfully',
                'member_id' => $anggotaId,
                'member_number' => $noAnggota
            ];
            
        } catch (Exception $e) {
            // Rollback all transactions
            $this->conn->rollback();
            $this->registrationConn->rollback();
            $this->systemConn->rollback();
            
            error_log("Error approving registration: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to approve registration'
            ];
        }
    }
    
    /**
     * Reject registration
     */
    public function rejectRegistration($submissionId, $adminId, $reason) {
        try {
            $result = registrationDB()->executeNonQuery(
                "UPDATE registration_submissions SET 
                 status = 'rejected', approved_by = ?, rejection_reason = ?, review_date = CURRENT_TIMESTAMP 
                 WHERE id = ?",
                [$adminId, $reason, $submissionId],
                'isi'
            );
            
            if ($result['affected_rows'] === 0) {
                return [
                    'success' => false,
                    'message' => 'Submission not found or already processed'
                ];
            }
            
            // Log rejection
            systemDB()->executeNonQuery(
                "INSERT INTO registration_logs (submission_id, action, actor_id, actor_type, description, ip_address) 
                 VALUES (?, 'rejected', ?, 'admin', ?, ?)",
                [$submissionId, $adminId, "Registration rejected: $reason", $_SERVER['REMOTE_ADDR'] ?? ''],
                'iiss'
            );
            
            return [
                'success' => true,
                'message' => 'Registration rejected successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Error rejecting registration: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to reject registration'
            ];
        }
    }
    
    /**
     * Get printable form
     */
    public function getPrintableForm($submissionId) {
        try {
            $submission = registrationDB()->fetchRow(
                "SELECT rs.*, rf.form_template 
                 FROM registration_submissions rs 
                 LEFT JOIN registration_forms rf ON rs.form_id = rf.id 
                 WHERE rs.id = ?",
                [$submissionId],
                'i'
            );
            
            if (!$submission) {
                return [
                    'success' => false,
                    'message' => 'Submission not found'
                ];
            }
            
            $data = [
                'personal_data' => json_decode($submission['personal_data'], true),
                'address_data' => json_decode($submission['address_data'], true),
                'financial_data' => json_decode($submission['financial_data'], true),
                'signature_data' => json_decode($submission['signature_data'], true)
            ];
            
            $formContent = $this->replaceTemplateVariables($submission['form_template'], $data);
            
            return [
                'success' => true,
                'data' => [
                    'content' => $formContent,
                    'submission' => [
                        'id' => $submission['id'],
                        'submission_date' => $submission['submission_date'],
                        'status' => $submission['status']
                    ]
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error generating printable form: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to generate printable form'
            ];
        }
    }
    
    /**
     * Get statistics
     */
    public function getStatistics() {
        try {
            $stats = registrationDB()->fetchRow(
                "SELECT 
                 COUNT(*) as total_submissions,
                 COUNT(CASE WHEN status = 'draft' THEN 1 END) as drafts,
                 COUNT(CASE WHEN status = 'submitted' THEN 1 END) as pending,
                 COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                 COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
                 COUNT(CASE WHEN DATE(submission_date) = CURDATE() THEN 1 END) as today_submissions
                 FROM registration_submissions"
            );
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("Error getting statistics: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get statistics'
            ];
        }
    }
    
    /**
     * Helper methods
     */
    private function generateMemberNumber() {
        $year = date('Y');
        $lastMember = coreDB()->fetchRow(
            "SELECT no_anggota FROM anggota WHERE no_anggota LIKE ? ORDER BY id DESC LIMIT 1",
            [$year . '%'],
            's'
        );
        
        $newNum = $lastMember ? intval(substr($lastMember['no_anggota'], -4)) + 1 : 1;
        return $year . str_pad($newNum, 4, '0', STR_PAD_LEFT);
    }
    
    private function replaceTemplateVariables($template, $data) {
        // Helper function untuk konversi angka ke terbilang
        function terbilang($angka) {
            $angka = (int)$angka;
            $abil = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');
            if ($angka < 12) return ' ' . $abil[$angka];
            elseif ($angka < 20) return terbilang($angka - 10) . ' belas';
            elseif ($angka < 100) return terbilang($angka / 10) . ' puluh' . terbilang($angka % 10);
            elseif ($angka < 200) return ' seratus' . terbilang($angka - 100);
            elseif ($angka < 1000) return terbilang($angka / 100) . ' ratus' . terbilang($angka % 100);
            elseif ($angka < 2000) return ' seribu' . terbilang($angka - 1000);
            elseif ($angka < 1000000) return terbilang($angka / 1000) . ' ribu' . terbilang($angka % 1000);
            elseif ($angka < 1000000000) return terbilang($angka / 1000000) . ' juta' . terbilang($angka % 1000000);
        }
        
        $simpanan_pokok = $data['financial_data']['simpanan_pokok'] ?? 100000;
        $simpanan_wajib = $data['financial_data']['simpanan_wajib'] ?? 50000;
        
        $variables = [
            // Data Pribadi
            '{{no_ktp}}' => $data['personal_data']['no_ktp'],
            '{{nama_lengkap}}' => strtoupper($data['personal_data']['nama_lengkap']),
            '{{tempat_lahir}}' => $data['personal_data']['tempat_lahir'],
            '{{tanggal_lahir}}' => $data['personal_data']['tanggal_lahir'],
            '{{jenis_kelamin}}' => $data['personal_data']['jenis_kelamin'],
            '{{nrp}}' => $data['personal_data']['nrp'] ?? '',
            '{{satker}}' => $data['personal_data']['satker'] ?? '',
            '{{jabatan}}' => $data['personal_data']['jabatan'] ?? '',
            '{{email}}' => $data['personal_data']['email'] ?? '',
            '{{no_hp}}' => $data['personal_data']['no_hp'],
            
            // Simpanan dengan terbilang
            '{{simpanan_pokok}}' => number_format($simpanan_pokok, 0, ',', '.'),
            '{{simpanan_pokok_terbilang}}' => ucfirst(trim(terbilang($simpanan_pokok))) . ' rupiah',
            '{{simpanan_wajib}}' => number_format($simpanan_wajib, 0, ',', '.'),
            '{{simpanan_wajib_terbilang}}' => ucfirst(trim(terbilang($simpanan_wajib))) . ' rupiah',
            
            // Tanda tangan
            '{{tempat_tanda_tangan}}' => $data['address_data']['kabupaten'] ?? 'Samosir',
            '{{tanggal_tanda_tangan}}' => date('d F Y'),
            '{{signature_image}}' => $data['signature_data'] && $data['signature_data']['image'] ? 
                '<img src="' . $data['signature_data']['image'] . '" style="max-height: 50px;">' : 
                '[Tanda Tangan Digital]',
            
            // Data verifikasi (jika ada)
            '{{nama_petugas}}' => $data['verification_data']['nama_petugas'] ?? '',
            '{{nrp_petugas}}' => $data['verification_data']['nrp_petugas'] ?? '',
            '{{jabatan_petugas}}' => $data['verification_data']['jabatan_petugas'] ?? '',
            '{{tanggal_verifikasi}}' => $data['verification_data']['tanggal_verifikasi'] ?? '',
            '{{nama_ketua}}' => $data['verification_data']['nama_ketua'] ?? 'KETUA KOPERASI PEMASARAN POLRES SAMOSIR',
            '{{signature_ketua}}' => $data['verification_data']['signature_ketua'] ?? '[Tanda Tangan Ketua]'
        ];
        
        return str_replace(array_keys($variables), array_values($variables), $template);
    }
    
    private function notifyAdmin($submission_id) {
        // Implementation for admin notification
        // Could be email, SMS, or in-app notification
        systemDB()->executeNonQuery(
            "INSERT INTO notification_logs (notification_type, recipient, subject, message, status) 
             VALUES ('email', 'admin@ksp-samosir.com', 'New Registration Submission', 
                     'A new registration form has been submitted. ID: $submission_id', 'pending')",
            [],
            ''
        );
    }
    
    public function __destruct() {
        // Connections will be closed automatically by DatabaseManager
    }
}

?>
