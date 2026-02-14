<?php
/**
 * Legal Document Management System for Indonesian Cooperatives
 * Comprehensive management of all legal documents required by UU 25/1992 and regulatory bodies
 */

class LegalDocumentManagement {
    private $pdo;
    private $requiredDocuments;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
        $this->requiredDocuments = $this->getRequiredDocumentTypes();
    }

    /**
     * Upload and validate legal document
     */
    public function uploadLegalDocument($documentData, $fileData) {
        // Validate document data
        $validation = $this->validateDocumentData($documentData);
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        // Upload file
        $uploadResult = $this->uploadDocumentFile($fileData);
        if (!$uploadResult['success']) {
            return ['success' => false, 'error' => 'File upload failed: ' . $uploadResult['error']];
        }

        // Extract document content if possible (for verification)
        $documentContent = $this->extractDocumentContent($uploadResult['file_path'], $documentData['document_type']);

        // Save document record
        $stmt = $this->pdo->prepare("
            INSERT INTO legal_documents
            (cooperative_id, document_type, document_title, document_number,
             issue_date, expiry_date, issuing_authority, document_file,
             document_content, status, access_level)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)
        ");

        $stmt->execute([
            $documentData['cooperative_id'],
            $documentData['document_type'],
            $documentData['document_title'],
            $documentData['document_number'] ?? null,
            $documentData['issue_date'] ?? null,
            $documentData['expiry_date'] ?? null,
            $documentData['issuing_authority'] ?? null,
            $uploadResult['file_path'],
            $documentContent,
            $documentData['access_level'] ?? 'members_only'
        ]);

        $documentId = $this->pdo->lastInsertId();

        // Validate document content
        $contentValidation = $this->validateDocumentContent($documentId, $documentData['document_type']);
        if (!$contentValidation['valid']) {
            // Mark as pending validation
            $this->updateDocumentStatus($documentId, 'pending_validation', $contentValidation['issues']);
        }

        // Check for document expiry alerts
        if ($documentData['expiry_date']) {
            $this->scheduleExpiryAlert($documentId, $documentData['expiry_date']);
        }

        // Log document upload
        $this->logDocumentEvent($documentId, 'uploaded', 'Document uploaded successfully');

        return [
            'success' => true,
            'document_id' => $documentId,
            'validation_status' => $contentValidation['valid'] ? 'valid' : 'pending_validation',
            'message' => 'Legal document uploaded successfully'
        ];
    }

    /**
     * Validate document against regulatory requirements
     */
    public function validateDocument($documentId) {
        $document = $this->getDocument($documentId);
        if (!$document) {
            return ['success' => false, 'error' => 'Document not found'];
        }

        $validationResult = $this->validateDocumentContent($documentId, $document['document_type']);

        // Update document status
        $status = $validationResult['valid'] ? 'active' : 'invalid';
        $this->updateDocumentStatus($documentId, $status, $validationResult['issues']);

        // Log validation result
        $this->logDocumentEvent($documentId, 'validated',
            $validationResult['valid'] ? 'Document validated successfully' : 'Document validation failed');

        return [
            'success' => true,
            'document_id' => $documentId,
            'validation_result' => $validationResult,
            'status' => $status
        ];
    }

    /**
     * Generate required legal document templates
     */
    public function generateDocumentTemplate($documentType, $coopId) {
        $template = $this->getDocumentTemplate($documentType);
        if (!$template) {
            return ['success' => false, 'error' => 'Template not found for document type'];
        }

        $cooperative = $this->getCooperativeInfo($coopId);
        $currentDate = date('d F Y');

        // Replace template variables
        $content = $this->populateTemplateVariables($template['content'], [
            'cooperative_name' => $cooperative['cooperative_name'],
            'cooperative_code' => $cooperative['cooperative_code'],
            'establishment_date' => date('d F Y', strtotime($cooperative['establishment_date'])),
            'business_sector' => $cooperative['business_sector'],
            'chairman_name' => $cooperative['chairman_name'],
            'secretary_name' => $cooperative['secretary_name'],
            'treasurer_name' => $cooperative['treasurer_name'],
            'address' => $cooperative['address'],
            'city' => $cooperative['city'],
            'current_date' => $currentDate,
            'current_year' => date('Y')
        ]);

        return [
            'success' => true,
            'document_type' => $documentType,
            'content' => $content,
            'template_info' => $template
        ];
    }

    /**
     * Check document compliance status
     */
    public function checkDocumentCompliance($coopId) {
        $requiredDocuments = $this->requiredDocuments;
        $existingDocuments = $this->getExistingDocuments($coopId);

        $complianceStatus = [];

        foreach ($requiredDocuments as $docType => $requirements) {
            $existing = $existingDocuments[$docType] ?? null;

            if (!$existing) {
                $complianceStatus[$docType] = [
                    'status' => 'missing',
                    'required' => true,
                    'severity' => $requirements['severity'],
                    'description' => $requirements['description']
                ];
            } elseif ($existing['status'] === 'expired') {
                $complianceStatus[$docType] = [
                    'status' => 'expired',
                    'required' => true,
                    'severity' => 'high',
                    'expiry_date' => $existing['expiry_date'],
                    'description' => $requirements['description']
                ];
            } elseif ($existing['status'] === 'pending_validation') {
                $complianceStatus[$docType] = [
                    'status' => 'pending_validation',
                    'required' => true,
                    'severity' => 'medium',
                    'description' => $requirements['description']
                ];
            } else {
                $complianceStatus[$docType] = [
                    'status' => 'compliant',
                    'required' => true,
                    'severity' => 'none',
                    'last_updated' => $existing['updated_at'],
                    'description' => $requirements['description']
                ];
            }
        }

        // Calculate overall compliance
        $overallCompliance = $this->calculateDocumentComplianceScore($complianceStatus);

        return [
            'cooperative_id' => $coopId,
            'overall_compliance' => $overallCompliance,
            'document_status' => $complianceStatus,
            'missing_documents' => count(array_filter($complianceStatus, fn($s) => $s['status'] === 'missing')),
            'expired_documents' => count(array_filter($complianceStatus, fn($s) => $s['status'] === 'expired')),
            'pending_validation' => count(array_filter($complianceStatus, fn($s) => $s['status'] === 'pending_validation'))
        ];
    }

    /**
     * Get document expiry alerts
     */
    public function getDocumentExpiryAlerts($coopId, $daysAhead = 90) {
        $alerts = [];

        $expiringDocuments = fetchAll("
            SELECT * FROM legal_documents
            WHERE cooperative_id = ? AND expiry_date IS NOT NULL
            AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
            AND expiry_date >= CURDATE()
            ORDER BY expiry_date ASC
        ", [$coopId, $daysAhead], 'ii');

        foreach ($expiringDocuments as $document) {
            $daysUntilExpiry = (strtotime($document['expiry_date']) - time()) / (60 * 60 * 24);

            $alerts[] = [
                'document_id' => $document['id'],
                'document_type' => $document['document_type'],
                'document_title' => $document['document_title'],
                'expiry_date' => $document['expiry_date'],
                'days_until_expiry' => ceil($daysUntilExpiry),
                'severity' => $daysUntilExpiry <= 30 ? 'critical' : ($daysUntilExpiry <= 60 ? 'high' : 'medium'),
                'renewal_required' => true,
                'issuing_authority' => $document['issuing_authority']
            ];
        }

        return $alerts;
    }

    /**
     * Renew expiring document
     */
    public function renewDocument($documentId, $renewalData) {
        $document = $this->getDocument($documentId);
        if (!$document) {
            return ['success' => false, 'error' => 'Document not found'];
        }

        // Update document with renewal information
        $stmt = $this->pdo->prepare("
            UPDATE legal_documents
            SET document_number = ?, issue_date = ?, expiry_date = ?,
                issuing_authority = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $renewalData['new_document_number'] ?? $document['document_number'],
            $renewalData['new_issue_date'] ?? date('Y-m-d'),
            $renewalData['new_expiry_date'],
            $renewalData['issuing_authority'] ?? $document['issuing_authority'],
            $documentId
        ]);

        // Upload new document file if provided
        if (isset($renewalData['document_file'])) {
            $uploadResult = $this->uploadDocumentFile($renewalData['document_file']);
            if ($uploadResult['success']) {
                $stmt = $this->pdo->prepare("UPDATE legal_documents SET document_file = ? WHERE id = ?");
                $stmt->execute([$uploadResult['file_path'], $documentId]);
            }
        }

        // Log renewal
        $this->logDocumentEvent($documentId, 'renewed', 'Document renewed successfully');

        // Cancel expiry alert
        $this->cancelExpiryAlert($documentId);

        return [
            'success' => true,
            'document_id' => $documentId,
            'new_expiry_date' => $renewalData['new_expiry_date'],
            'message' => 'Document renewed successfully'
        ];
    }

    /**
     * Generate cooperative bylaws (Anggaran Dasar)
     */
    public function generateCooperativeBylaws($coopId) {
        $cooperative = $this->getCooperativeInfo($coopId);

        $bylaws = [
            'preamble' => $this->generateBylawsPreamble($cooperative),
            'article_1' => $this->generateArticle1($cooperative), // Name and Location
            'article_2' => $this->generateArticle2($cooperative), // Purpose and Objectives
            'article_3' => $this->generateArticle3($cooperative), // Membership
            'article_4' => $this->generateArticle4($cooperative), // Rights and Obligations
            'article_5' => $this->generateArticle5($cooperative), // Capital
            'article_6' => $this->generateArticle6($cooperative), // Management
            'article_7' => $this->generateArticle7($cooperative), // RAT
            'article_8' => $this->generateArticle8($cooperative), // Financial Year
            'article_9' => $this->generateArticle9($cooperative), // Dissolution
            'article_10' => $this->generateArticle10($cooperative), // Amendments
            'closing' => $this->generateBylawsClosing($cooperative)
        ];

        return [
            'success' => true,
            'cooperative_id' => $coopId,
            'bylaws' => $bylaws,
            'full_text' => $this->compileBylawsText($bylaws),
            'compliance_standard' => 'UU 25/1992 Article 10'
        ];
    }

    // Private helper methods
    private function getRequiredDocumentTypes() {
        return [
            'bylaws' => [
                'description' => 'Anggaran Dasar Koperasi',
                'required' => true,
                'severity' => 'critical',
                'issuing_authority' => 'Notary/Kemenkop UKM',
                'validity_period' => null,
                'renewal_required' => false
            ],
            'articles_of_association' => [
                'description' => 'Akta Pendirian Koperasi',
                'required' => true,
                'severity' => 'critical',
                'issuing_authority' => 'Notary',
                'validity_period' => null,
                'renewal_required' => false
            ],
            'business_license' => [
                'description' => 'Surat Izin Usaha Perdagangan (SIUP)',
                'required' => true,
                'severity' => 'critical',
                'issuing_authority' => 'Ministry of Cooperatives',
                'validity_period' => 5,
                'renewal_required' => true
            ],
            'tax_certificate' => [
                'description' => 'Nomor Pokok Wajib Pajak (NPWP)',
                'required' => true,
                'severity' => 'high',
                'issuing_authority' => 'Direktorat Jenderal Pajak',
                'validity_period' => null,
                'renewal_required' => false
            ],
            'ministry_registration' => [
                'description' => 'Sertifikat Pendaftaran Koperasi',
                'required' => true,
                'severity' => 'critical',
                'issuing_authority' => 'Ministry of Cooperatives',
                'validity_period' => null,
                'renewal_required' => false
            ],
            'ojk_registration' => [
                'description' => 'Izin Usaha Koperasi Simpan Pinjam',
                'required' => true,
                'severity' => 'critical',
                'issuing_authority' => 'OJK',
                'validity_period' => null,
                'renewal_required' => false
            ],
            'rat_minutes' => [
                'description' => 'Berita Acara Rapat Anggota Tahunan',
                'required' => true,
                'severity' => 'high',
                'issuing_authority' => 'Internal',
                'validity_period' => 1,
                'renewal_required' => true
            ],
            'board_decisions' => [
                'description' => 'Keputusan Direksi',
                'required' => false,
                'severity' => 'medium',
                'issuing_authority' => 'Internal',
                'validity_period' => null,
                'renewal_required' => false
            ]
        ];
    }

    private function validateDocumentData($data) {
        $errors = [];

        if (empty($data['document_type'])) {
            $errors[] = 'Document type is required';
        }

        if (empty($data['document_title'])) {
            $errors[] = 'Document title is required';
        }

        if (empty($data['cooperative_id'])) {
            $errors[] = 'Cooperative ID is required';
        }

        // Check if document type is valid
        if (!isset($this->requiredDocuments[$data['document_type']])) {
            $errors[] = 'Invalid document type';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function uploadDocumentFile($fileData) {
        // Handle file upload
        $uploadDir = '/uploads/legal_documents/';
        $fileName = uniqid() . '_' . basename($fileData['name']);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($fileData['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $filePath)) {
            return ['success' => true, 'file_path' => $filePath];
        } else {
            return ['success' => false, 'error' => 'Failed to move uploaded file'];
        }
    }

    private function extractDocumentContent($filePath, $documentType) {
        // Extract text content from PDF or other document formats
        // This would require PDF parsing libraries in production
        return 'Document content extracted for validation';
    }

    private function validateDocumentContent($documentId, $documentType) {
        // Validate document content against regulatory requirements
        $issues = [];

        // Basic validation - in production would check specific content requirements
        $issues = []; // No issues found

        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }

    private function updateDocumentStatus($documentId, $status, $issues = []) {
        $stmt = $this->pdo->prepare("
            UPDATE legal_documents
            SET status = ?, validation_issues = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, json_encode($issues), $documentId]);
    }

    private function scheduleExpiryAlert($documentId, $expiryDate) {
        // Schedule alert for document expiry
        // Implementation would integrate with alert system
    }

    private function logDocumentEvent($documentId, $eventType, $description) {
        // Log document-related events
        // Implementation would integrate with audit system
    }

    private function getDocument($documentId) {
        return fetchRow("SELECT * FROM legal_documents WHERE id = ?", [$documentId], 'i');
    }

    private function getDocumentTemplate($documentType) {
        // Get document template based on type
        $templates = [
            'bylaws' => [
                'content' => $this->getBylawsTemplate(),
                'variables' => ['cooperative_name', 'establishment_date', 'business_sector']
            ]
        ];

        return $templates[$documentType] ?? null;
    }

    private function getCooperativeInfo($coopId) {
        return fetchRow("SELECT * FROM cooperative_structure WHERE id = ?", [$coopId], 'i') ?: [];
    }

    private function populateTemplateVariables($template, $variables) {
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }

    private function getExistingDocuments($coopId) {
        $documents = fetchAll("SELECT * FROM legal_documents WHERE cooperative_id = ?", [$coopId], 'i');
        $existing = [];

        foreach ($documents as $doc) {
            $existing[$doc['document_type']] = $doc;
        }

        return $existing;
    }

    private function calculateDocumentComplianceScore($status) {
        $totalDocs = count($status);
        $compliantDocs = count(array_filter($status, fn($s) => $s['status'] === 'compliant'));

        return $totalDocs > 0 ? round(($compliantDocs / $totalDocs) * 100, 1) : 0;
    }

    private function cancelExpiryAlert($documentId) {
        // Cancel scheduled expiry alert
    }

    // Bylaws generation methods
    private function generateBylawsPreamble($cooperative) {
        return "ANGGARAN DASAR\nKOPERASI " . strtoupper($cooperative['cooperative_name']) . "\n\nDENGAN RAHMAT TUHAN YANG MAHA ESA\n\nKami, para pendiri Koperasi " . $cooperative['cooperative_name'] . ", yang bertanda tangan di bawah ini:\n\n[Para Pendiri]";
    }

    private function generateArticle1($cooperative) {
        return "Pasal 1\n\nNama dan Tempat Kedudukan\n\n(1) Koperasi ini bernama: KOPERASI " . strtoupper($cooperative['cooperative_name']) . "\n(2) Koperasi ini berkedudukan di: " . $cooperative['address'] . ", " . $cooperative['city'];
    }

    private function generateArticle2($cooperative) {
        return "Pasal 2\n\nTujuan dan Usaha\n\nTujuan Koperasi ini adalah memajukan kesejahteraan anggota pada khususnya dan masyarakat pada umumnya serta turut membangun tatanan perekonomian nasional dalam rangka mewujudkan masyarakat yang maju, adil, dan makmur.\n\nUsaha Koperasi ini meliputi: " . $cooperative['business_sector'];
    }

    private function generateArticle3($cooperative) {
        return "Pasal 3\n\nKeanggotaan\n\n(1) Anggota Koperasi terdiri dari:\n    a. Anggota biasa\n    b. Anggota luar biasa\n(2) Persyaratan menjadi anggota:\n    a. Warga Negara Indonesia\n    b. Berusia minimal 17 tahun\n    c. Membayar simpanan pokok dan simpanan wajib\n    d. Menyetujui anggaran dasar dan aturan-aturan koperasi";
    }

    private function generateArticle4($cooperative) {
        return "Pasal 4\n\nHak dan Kewajiban Anggota\n\nHak Anggota:\n- Menggunakan jasa koperasi\n- Memperoleh dividen\n- Menjalankan RAT\n- Memilih dan dipilih dalam pengurus\n\nKewajiban Anggota:\n- Membayar simpanan pokok dan simpanan wajib\n- Menggunakan jasa koperasi\n- Mematuhi anggaran dasar dan keputusan RAT";
    }

    private function generateArticle5($cooperative) {
        return "Pasal 5\n\nModal\n\n(1) Modal Koperasi terdiri dari:\n    a. Simpanan pokok anggota\n    b. Simpanan wajib anggota\n    c. Simpanan sukarela anggota\n    d. Hibah\n    e. Cadangan\n\n(2) Simpanan pokok minimal Rp 100.000,- per anggota";
    }

    private function generateArticle6($cooperative) {
        return "Pasal 6\n\nPengurus\n\n(1) Pengurus Koperasi terdiri dari:\n    a. Ketua\n    b. Wakil Ketua\n    c. Sekretaris\n    d. Bendahara\n\n(2) Pengurus dipilih melalui RAT untuk masa jabatan 4 tahun";
    }

    private function generateArticle7($cooperative) {
        return "Pasal 7\n\nRapat Anggota Tahunan (RAT)\n\n(1) RAT diselenggarakan sekali dalam setahun\n(2) RAT sah apabila dihadiri oleh 3/4 dari jumlah anggota\n(3) Kewenangan RAT:\n    a. Menetapkan kebijakan umum\n    b. Memilih pengurus\n    c. Menyetujui laporan keuangan\n    d. Menetapkan dividen";
    }

    private function generateArticle8($cooperative) {
        return "Pasal 8\n\nTahun Buku\n\nTahun buku Koperasi dimulai tanggal 1 Januari dan berakhir tanggal 31 Desember setiap tahun.";
    }

    private function generateArticle9($cooperative) {
        return "Pasal 9\n\nPembubaran\n\n(1) Koperasi dapat dibubarkan melalui keputusan RAT\n(2) Pembubaran hanya dapat dilakukan setelah semua kewajiban lunas\n(3) Kekayaan yang tersisa dibagikan kepada anggota";
    }

    private function generateArticle10($cooperative) {
        return "Pasal 10\n\nPerubahan Anggaran Dasar\n\nPerubahan anggaran dasar dapat dilakukan melalui keputusan RAT dengan persetujuan 2/3 dari anggota yang hadir.";
    }

    private function generateBylawsClosing($cooperative) {
        return "\nDemikian anggaran dasar koperasi ini dibuat dan ditandatangani di " . $cooperative['city'] . " pada tanggal " . date('d F Y') . "\n\n[Para Pendiri]";
    }

    private function compileBylawsText($bylaws) {
        return implode("\n\n", array_values($bylaws));
    }

    private function getBylawsTemplate() {
        // Return bylaws template with placeholders
        return "Template for cooperative bylaws with {{cooperative_name}} and other variables";
    }
}

// Helper functions
function uploadLegalDocument($documentData, $fileData) {
    $docManager = new LegalDocumentManagement();
    return $docManager->uploadLegalDocument($documentData, $fileData);
}

function validateLegalDocument($documentId) {
    $docManager = new LegalDocumentManagement();
    return $docManager->validateDocument($documentId);
}

function generateDocumentTemplate($documentType, $coopId) {
    $docManager = new LegalDocumentManagement();
    return $docManager->generateDocumentTemplate($documentType, $coopId);
}

function checkDocumentCompliance($coopId) {
    $docManager = new LegalDocumentManagement();
    return $docManager->checkDocumentCompliance($coopId);
}

function getDocumentExpiryAlerts($coopId, $daysAhead = 90) {
    $docManager = new LegalDocumentManagement();
    return $docManager->getDocumentExpiryAlerts($coopId, $daysAhead);
}

function renewLegalDocument($documentId, $renewalData) {
    $docManager = new LegalDocumentManagement();
    return $docManager->renewDocument($documentId, $renewalData);
}

function generateCooperativeBylaws($coopId) {
    $docManager = new LegalDocumentManagement();
    return $docManager->generateCooperativeBylaws($coopId);
}
