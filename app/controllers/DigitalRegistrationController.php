<?php
/**
 * KSP Samosir - Digital Registration Controller
 * Controller untuk pendaftaran anggota digital dengan tanda tangan digital
 */

class DigitalRegistrationController
{
    private $conn;
    
    public function __construct()
    {
        $this->conn = new mysqli('localhost', 'root', 'root', 'ksp_samosir');
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
    }
    
    /**
     * Get registration form
     */
    public function getRegistrationForm($form_id = null)
    {
        $sql = "SELECT * FROM registration_forms WHERE is_active = 1";
        if ($form_id) {
            $sql .= " AND id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }
        
        $form = $result->fetch_assoc();
        
        if ($form) {
            $form['form_fields'] = json_decode($form['form_fields'], true);
            return [
                'success' => true,
                'data' => $form
            ];
        } else {
            return ['success' => false, 'message' => 'Form not found'];
        }
    }
    
    /**
     * Save registration draft
     */
    public function saveDraft($data)
    {
        // Generate submission token
        $token = $this->generateToken();
        
        $sql = "INSERT INTO registration_submissions 
                (form_id, submission_token, personal_data, address_data, financial_data, document_data, status)
                VALUES (?, ?, ?, ?, ?, ?, 'draft')";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssss", 
            $data['form_id'],
            $token,
            json_encode($data['personal_data']),
            json_encode($data['address_data']),
            json_encode($data['financial_data']),
            json_encode($data['document_data'] ?? [])
        );
        
        if ($stmt->execute()) {
            $submission_id = $this->conn->insert_id;
            
            // Log action
            $this->logRegistrationAction($submission_id, 'created', 'user', 'Draft registration created');
            
            return [
                'success' => true,
                'message' => 'Draft saved successfully',
                'submission_id' => $submission_id,
                'token' => $token
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to save draft: ' . $stmt->error];
        }
    }
    
    /**
     * Update registration draft
     */
    public function updateDraft($submission_id, $data, $token)
    {
        // Verify token
        if (!$this->verifyToken($submission_id, $token)) {
            return ['success' => false, 'message' => 'Invalid token'];
        }
        
        $sql = "UPDATE registration_submissions 
                SET personal_data = ?, address_data = ?, financial_data = ?, document_data = ?, updated_at = NOW()
                WHERE id = ? AND status = 'draft'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssi", 
            json_encode($data['personal_data']),
            json_encode($data['address_data']),
            json_encode($data['financial_data']),
            json_encode($data['document_data'] ?? []),
            $submission_id
        );
        
        if ($stmt->execute()) {
            // Log action
            $this->logRegistrationAction($submission_id, 'updated', 'user', 'Draft registration updated');
            
            return ['success' => true, 'message' => 'Draft updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update draft: ' . $stmt->error];
        }
    }
    
    /**
     * Submit registration
     */
    public function submitRegistration($submission_id, $data, $token)
    {
        // Verify token
        if (!$this->verifyToken($submission_id, $token)) {
            return ['success' => false, 'message' => 'Invalid token'];
        }
        
        // Validate required data
        $validation = $this->validateRegistrationData($data);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation['errors']];
        }
        
        // Update submission
        $sql = "UPDATE registration_submissions 
                SET personal_data = ?, address_data = ?, financial_data = ?, document_data = ?, 
                    signature_data = ?, photo_data = ?, status = 'submitted', updated_at = NOW()
                WHERE id = ? AND status = 'draft'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssi", 
            json_encode($data['personal_data']),
            json_encode($data['address_data']),
            json_encode($data['financial_data']),
            json_encode($data['document_data'] ?? []),
            json_encode($data['signature_data'] ?? []),
            json_encode($data['photo_data'] ?? []),
            $submission_id
        );
        
        if ($stmt->execute()) {
            // Log action
            $this->logRegistrationAction($submission_id, 'submitted', 'user', 'Registration submitted');
            
            // Send notification to admin
            $this->notifyAdmin($submission_id);
            
            return ['success' => true, 'message' => 'Registration submitted successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to submit registration: ' . $stmt->error];
        }
    }
    
    /**
     * Get submission for review
     */
    public function getSubmission($submission_id)
    {
        $sql = "SELECT s.*, f.form_name, f.form_template, f.form_fields
                FROM registration_submissions s
                JOIN registration_forms f ON s.form_id = f.id
                WHERE s.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $submission_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $submission = $result->fetch_assoc();
        
        if ($submission) {
            $submission['form_fields'] = json_decode($submission['form_fields'], true);
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
        } else {
            return ['success' => false, 'message' => 'Submission not found'];
        }
    }
    
    /**
     * Get all submissions for admin
     */
    public function getSubmissions($status = null, $limit = 20, $offset = 0)
    {
        $sql = "SELECT s.*, f.form_name,
                       JSON_UNQUOTE(JSON_EXTRACT(s.personal_data, '$.nama_lengkap')) as nama_lengkap,
                       JSON_UNQUOTE(JSON_EXTRACT(s.personal_data, '$.no_ktp')) as no_ktp
                FROM registration_submissions s
                JOIN registration_forms f ON s.form_id = f.id";
        
        $params = [];
        $types = "";
        
        if ($status) {
            $sql .= " WHERE s.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        $sql .= " ORDER BY s.submission_date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $submissions = [];
        while ($row = $result->fetch_assoc()) {
            $submissions[] = $row;
        }
        
        return [
            'success' => true,
            'data' => $submissions
        ];
    }
    
    /**
     * Approve registration
     */
    public function approveRegistration($submission_id, $admin_id)
    {
        // Get submission data
        $submission = $this->getSubmission($submission_id);
        if (!$submission['success']) {
            return ['success' => false, 'message' => 'Submission not found'];
        }
        
        $data = $submission['data'];
        
        // Create anggota record
        $no_anggota = $this->generateNoAnggota();
        
        $sql = "INSERT INTO anggota 
                (no_anggota, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, 
                 status_perkawinan, agama, pendidikan, pekerjaan, no_ktp, no_hp, email,
                 alamat, rt, rw, kelurahan, kecamatan, kabupaten, provinsi, kode_pos,
                 penghasilan, sumber_penghasilan, nama_bank, no_rekening, atas_nama_rekening,
                 registration_source, registration_ip, registration_device, is_verified,
                 verification_date, verified_by, digital_signature, signature_coordinates, form_data, form_template_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssssssssissssssiiss", 
            $no_anggota,
            $data['personal_data']['nama_lengkap'],
            $data['personal_data']['tempat_lahir'],
            $data['personal_data']['tanggal_lahir'],
            $data['personal_data']['jenis_kelamin'],
            $data['personal_data']['status_perkawinan'],
            $data['personal_data']['agama'],
            $data['personal_data']['pendidikan'],
            $data['personal_data']['pekerjaan'],
            $data['personal_data']['no_ktp'],
            $data['personal_data']['no_hp'],
            $data['personal_data']['email'] ?? null,
            $data['address_data']['alamat_lengkap'],
            $data['address_data']['rt'],
            $data['address_data']['rw'],
            $data['address_data']['kelurahan'],
            $data['address_data']['kecamatan'],
            $data['address_data']['kabupaten'],
            $data['address_data']['provinsi'],
            $data['address_data']['kode_pos'],
            $data['financial_data']['penghasilan'],
            $data['financial_data']['sumber_penghasilan'],
            $data['financial_data']['nama_bank'],
            $data['financial_data']['no_rekening'],
            $data['financial_data']['atas_nama_rekening'],
            'digital',
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            1, // verified
            date('Y-m-d H:i:s'),
            $admin_id,
            json_encode($data['signature_data']),
            json_encode($data['signature_data']['coordinates'] ?? []),
            json_encode($data),
            $data['form_id']
        );
        
        $this->conn->begin_transaction();
        
        try {
            if ($stmt->execute()) {
                $anggota_id = $this->conn->insert_id;
                
                // Create simpanan records
                $this->createInitialSavings($anggota_id, $data['financial_data']);
                
                // Update submission status
                $this->updateSubmissionStatus($submission_id, 'approved', $admin_id);
                
                // Create user account
                $this->createUserAccount($anggota_id, $data);
                
                // Save digital signature
                $this->saveDigitalSignature($anggota_id, 'registration_form', $anggota_id, $data['signature_data']);
                
                $this->conn->commit();
                
                // Log action
                $this->logRegistrationAction($submission_id, 'approved', 'admin', "Registration approved by admin {$admin_id}");
                
                return [
                    'success' => true,
                    'message' => 'Registration approved successfully',
                    'anggota_id' => $anggota_id,
                    'no_anggota' => $no_anggota
                ];
            } else {
                throw new Exception('Failed to create anggota record');
            }
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Failed to approve registration: ' . $e->getMessage()];
        }
    }
    
    /**
     * Reject registration
     */
    public function rejectRegistration($submission_id, $admin_id, $reason)
    {
        $sql = "UPDATE registration_submissions 
                SET status = 'rejected', rejection_reason = ?, approved_by = ?, approved_date = NOW()
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $reason, $admin_id, $submission_id);
        
        if ($stmt->execute()) {
            // Log action
            $this->logRegistrationAction($submission_id, 'rejected', 'admin', "Registration rejected: {$reason}");
            
            return ['success' => true, 'message' => 'Registration rejected successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to reject registration: ' . $stmt->error];
        }
    }
    
    /**
     * Generate printable form
     */
    public function generatePrintableForm($submission_id)
    {
        $submission = $this->getSubmission($submission_id);
        if (!$submission['success']) {
            return ['success' => false, 'message' => 'Submission not found'];
        }
        
        $data = $submission['data'];
        $template = $data['form_template'];
        
        // Replace placeholders with actual data
        $form_content = $this->replaceTemplateVariables($template, $data);
        
        return [
            'success' => true,
            'data' => [
                'form_content' => $form_content,
                'signature_image' => $data['signature_data']['image'] ?? null,
                'photo_image' => $data['photo_data']['image'] ?? null,
                'submission_date' => $data['submission_date']
            ]
        ];
    }
    
    /**
     * Get registration statistics
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_submissions,
                    COUNT(CASE WHEN status = 'submitted' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
                    COUNT(CASE WHEN DATE(submission_date) = CURDATE() THEN 1 END) as today_submissions
                FROM registration_submissions";
        
        $result = $this->conn->query($sql);
        $stats = $result->fetch_assoc();
        
        return [
            'success' => true,
            'data' => $stats
        ];
    }
    
    /**
     * Helper methods
     */
    private function generateToken()
    {
        return bin2hex(random_bytes(16));
    }
    
    private function verifyToken($submission_id, $token)
    {
        $sql = "SELECT COUNT(*) as count FROM registration_submissions WHERE id = ? AND submission_token = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $submission_id, $token);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'] > 0;
    }
    
    private function validateRegistrationData($data)
    {
        $errors = [];
        $valid = true;
        
        // Validate personal data
        if (empty($data['personal_data']['nama_lengkap'])) {
            $errors[] = 'Nama lengengkap wajib diisi';
            $valid = false;
        }
        
        if (empty($data['personal_data']['no_ktp'])) {
            $errors[] = 'No. KTP wajib diisi';
            $valid = false;
        }
        
        if (empty($data['personal_data']['no_hp'])) {
            $errors[] = 'No. HP wajib diisi';
            $valid = false;
        }
        
        // Validate signature if required
        if (empty($data['signature_data'])) {
            $errors[] = 'Tanda tangan digital wajib';
            $valid = false;
        }
        
        return ['valid' => $valid, 'errors' => $errors];
    }
    
    private function generateNoAnggota()
    {
        $sql = "SELECT COUNT(*) as count FROM anggota WHERE DATE(created_at) = CURDATE()";
        $result = $this->conn->query($sql);
        $count = $result->fetch_assoc()['count'];
        
        return sprintf('KSP-%03d', $count + 1);
    }
    
    private function createInitialSavings($anggota_id, $financial_data)
    {
        // Get jenis simpanan
        $sql = "SELECT id FROM jenis_simpanan WHERE kode = 'POKOK' LIMIT 1";
        $result = $this->conn->query($sql);
        $pokok_id = $result->fetch_assoc()['id'];
        
        $sql = "SELECT id FROM jenis_simpanan WHERE kode = 'WAJIB' LIMIT 1";
        $result = $this->conn->query($sql);
        $wajib_id = $result->fetch_assoc()['id'];
        
        // Create simpanan pokok
        $sql = "INSERT INTO simpanan (anggota_id, jenis_simpanan_id, no_rekening, saldo, status, tanggal_buka, created_by, created_at)
                VALUES (?, ?, ?, ?, 'aktif', CURDATE(), 1, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisds", $anggota_id, $pokok_id, "SP-" . date('Y-m-d') . "-{$anggota_id}", $financial_data['simpanan_pokok']);
        $stmt->execute();
        
        // Create simpanan wajib
        $stmt->bind_param("iisds", $anggota_id, $wajib_id, "SW-" . date('Y-m-d') . "-{$anggota_id}", $financial_data['simpanan_wajib']);
        $stmt->execute();
    }
    
    private function createUserAccount($anggota_id, $data)
    {
        // Generate username from nama and no anggota
        $username = strtolower(str_replace(' ', '', $data['personal_data']['nama_lengkap'])) . $anggota_id;
        $password = password_hash($data['personal_data']['no_ktp'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, password, email, no_hp, anggota_id, role, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'anggota', 'active', NOW())";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssii", $username, $password, $data['personal_data']['email'], $data['personal_data']['no_hp'], $anggota_id);
        $stmt->execute();
    }
    
    private function saveDigitalSignature($user_id, $document_type, $document_id, $signature_data)
    {
        $sql = "INSERT INTO digital_signatures 
                (user_id, document_type, document_id, signature_image, signature_coordinates, device_info, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssssss", 
            $user_id,
            $document_type,
            $document_id,
            $signature_data['image'] ?? null,
            json_encode($signature_data['coordinates'] ?? []),
            json_encode($signature_data['device_info'] ?? []),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );
        $stmt->execute();
    }
    
    private function updateSubmissionStatus($submission_id, $status, $admin_id)
    {
        $sql = "UPDATE registration_submissions 
                SET status = ?, approved_by = ?, approved_date = NOW()
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $status, $admin_id, $submission_id);
        $stmt->execute();
    }
    
    private function logRegistrationAction($submission_id, $action, $actor_type, $description)
    {
        $sql = "INSERT INTO registration_logs 
                (submission_id, action, actor_type, description, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssss", $submission_id, $action, $actor_type, $description, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
        $stmt->execute();
    }
    
    private function notifyAdmin($submission_id)
    {
        // Implementation for admin notification
        // Could be email, SMS, or in-app notification
    }
    
    private function replaceTemplateVariables($template, $data)
    {
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
    
    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
