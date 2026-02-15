<?php
require_once __DIR__ . '/BaseController.php';

/**
 * Digital Document Management & E-signature Controller
 * Handles digital documents, templates, and electronic signatures
 * Based on fintech trends 2024 - Digital transformation
 */

class DigitalDocumentController extends BaseController {

    public function index() {
        // $this->ensureLoginAndRole(['admin', 'staff', 'manager']); // DISABLED for development

        $stats = $this->getDocumentStats();
        $recentDocuments = $this->getRecentDocuments();
        $pendingSignatures = $this->getPendingSignatures();

        $this->render('digital_documents/index', [
            'stats' => $stats,
            'recent_documents' => $recentDocuments,
            'pending_signatures' => $pendingSignatures
        ]);
    }

    public function documents() {
        // $this->ensureLoginAndRole(['admin', 'staff', 'manager']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $status = $_GET['status'] ?? null;
        $type = $_GET['type'] ?? null;

        $whereClause = "";
        $params = [];

        if ($status) {
            $whereClause .= " AND status = ?";
            $params[] = $status;
        }

        if ($type) {
            $whereClause .= " AND jenis_dokumen = ?";
            $params[] = $type;
        }

        $total = (fetchRow("SELECT COUNT(*) as count FROM digital_documents WHERE 1=1 $whereClause", $params) ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $documents = fetchAll(
            "SELECT dd.*, a.nama_lengkap as member_name, u.username as created_by_name
             FROM digital_documents dd
             LEFT JOIN anggota a ON dd.member_id = a.id
             LEFT JOIN users u ON dd.created_by = u.id
             WHERE 1=1 $whereClause
             ORDER BY dd.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $this->render('digital_documents/documents', [
            'documents' => $documents,
            'page' => $page,
            'totalPages' => $totalPages,
            'status' => $status,
            'type' => $type,
            'total' => $total
        ]);
    }

    public function createDocument() {
        // $this->ensureLoginAndRole(['admin', 'staff']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeDocument();
            return;
        }

        $templates = fetchAll("SELECT * FROM document_templates WHERE is_active = 1 ORDER BY nama_template");
        $members = fetchAll("SELECT id, nama_lengkap, no_anggota FROM anggota WHERE status = 'aktif' ORDER BY nama_lengkap");

        $this->render('digital_documents/create', [
            'templates' => $templates,
            'members' => $members
        ]);
    }

    public function storeDocument() {
        // $this->ensureLoginAndRole(['admin', 'staff']); // DISABLED for development

        $data = [
            'judul_dokumen' => sanitize($_POST['judul_dokumen'] ?? ''),
            'jenis_dokumen' => $_POST['jenis_dokumen'] ?? 'general',
            'template_id' => intval($_POST['template_id'] ?? 0),
            'member_id' => intval($_POST['member_id'] ?? 0),
            'loan_id' => intval($_POST['loan_id'] ?? 0),
            'konten_dokumen' => $_POST['konten_dokumen'] ?? '',
            'priority' => $_POST['priority'] ?? 'medium'
        ];

        // Generate document number
        $data['nomor_dokumen'] = $this->generateDocumentNumber();

        // If template is selected, populate content from template
        if ($data['template_id'] > 0) {
            $template = fetchRow("SELECT * FROM document_templates WHERE id = ?", [$data['template_id']]);
            if ($template) {
                $data['konten_dokumen'] = $this->populateTemplate($template['template_content'], $data);
            }
        }

        $documentId = executeNonQuery(
            "INSERT INTO digital_documents (nomor_dokumen, judul_dokumen, jenis_dokumen, template_id, konten_dokumen, member_id, loan_id, priority, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['nomor_dokumen'],
                $data['judul_dokumen'],
                $data['jenis_dokumen'],
                $data['template_id'] ?: null,
                $data['konten_dokumen'],
                $data['member_id'] ?: null,
                $data['loan_id'] ?: null,
                $data['priority'],
                $_SESSION['user']['id'] ?? null
            ],
            'sssissssi'
        )['last_id'];

        // Log document creation
        $this->logDocumentAction($documentId, 'created', 'Document created');

        flashMessage('success', 'Dokumen berhasil dibuat');
        redirect('digital_documents/view/' . $documentId);
    }

    public function viewDocument($documentId) {
        // Check access permissions
        if (!$this->canAccessDocument($documentId)) {
            flashMessage('error', 'Anda tidak memiliki akses ke dokumen ini');
            redirect('digital_documents');
            return;
        }

        $document = fetchRow(
            "SELECT dd.*, a.nama_lengkap as member_name, u.username as created_by_name
             FROM digital_documents dd
             LEFT JOIN anggota a ON dd.member_id = a.id
             LEFT JOIN users u ON dd.created_by = u.id
             WHERE dd.id = ?",
            [$documentId]
        );

        if (!$document) {
            flashMessage('error', 'Dokumen tidak ditemukan');
            redirect('digital_documents');
            return;
        }

        $signatures = fetchAll(
            "SELECT ds.*, u.username as signer_name_staff
             FROM document_signatures ds
             LEFT JOIN users u ON ds.signer_id = u.id
             WHERE ds.document_id = ?
             ORDER BY ds.created_at",
            [$documentId]
        );

        $auditLog = fetchAll(
            "SELECT dal.*, u.username as performed_by_name
             FROM document_audit_log dal
             LEFT JOIN users u ON dal.performed_by = u.id
             WHERE dal.document_id = ?
             ORDER BY dal.performed_at DESC
             LIMIT 20",
            [$documentId]
        );

        $this->render('digital_documents/view', [
            'document' => $document,
            'signatures' => $signatures,
            'audit_log' => $auditLog
        ]);
    }

    public function signDocument($documentId) {
        // $this->ensureLoginAndRole(['member', 'staff', 'admin']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('digital_documents/view/' . $documentId);
            return;
        }

        $signatureData = $_POST['signature_data'] ?? '';
        $consent = isset($_POST['consent']) && $_POST['consent'] === 'on';

        if (!$consent) {
            flashMessage('error', 'Anda harus menyetujui persyaratan untuk menandatangani dokumen');
            redirect('digital_documents/view/' . $documentId);
            return;
        }

        // Get current user info
        $userId = $_SESSION['user']['id'] ?? null;
        $userInfo = $userId ? fetchRow("SELECT * FROM users WHERE id = ?", [$userId]) : null;

        // Create signature record
        $signatureId = executeNonQuery(
            "INSERT INTO document_signatures (document_id, signer_id, signer_type, signer_name, signer_email, signature_type, signature_data, ip_address, user_agent, consent_given, consent_timestamp, status, signed_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'signed', NOW())",
            [
                $documentId,
                $userId,
                $userInfo ? 'staff' : 'member',
                $userInfo ? $userInfo['username'] : 'Anonymous User',
                $userInfo ? $userInfo['email'] : '',
                'electronic',
                $signatureData,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                true
            ],
            'iisssssss'
        )['last_id'];

        // Update document status if all required signatures are complete
        $this->checkDocumentCompletion($documentId);

        // Log signature action
        $this->logDocumentAction($documentId, 'signed', 'Document signed by ' . ($userInfo ? $userInfo['username'] : 'Anonymous User'), $signatureId);

        flashMessage('success', 'Dokumen berhasil ditandatangani');
        redirect('digital_documents/view/' . $documentId);
    }

    public function templates() {
        // $this->ensureLoginAndRole(['admin', 'staff']); // DISABLED for development

        $templates = fetchAll("SELECT * FROM document_templates WHERE is_active = 1 ORDER BY nama_template");

        $this->render('digital_documents/templates', [
            'templates' => $templates
        ]);
    }

    public function createTemplate() {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeTemplate();
            return;
        }

        $this->render('digital_documents/create_template');
    }

    public function storeTemplate() {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        $data = [
            'nama_template' => sanitize($_POST['nama_template'] ?? ''),
            'jenis_dokumen' => $_POST['jenis_dokumen'] ?? 'general',
            'template_content' => $_POST['template_content'] ?? '',
            'variables' => json_encode(explode("\n", sanitize($_POST['variables'] ?? '')))
        ];

        executeNonQuery(
            "INSERT INTO document_templates (nama_template, jenis_dokumen, template_content, variables, created_by) VALUES (?, ?, ?, ?, ?)",
            [
                $data['nama_template'],
                $data['jenis_dokumen'],
                $data['template_content'],
                $data['variables'],
                $_SESSION['user']['id'] ?? null
            ],
            'sssss'
        );

        flashMessage('success', 'Template dokumen berhasil dibuat');
        redirect('digital_documents/templates');
    }

    public function downloadDocument($documentId) {
        // Check access permissions
        if (!$this->canAccessDocument($documentId)) {
            http_response_code(403);
            echo "Access denied";
            return;
        }

        $document = fetchRow("SELECT * FROM digital_documents WHERE id = ?", [$documentId]);

        if (!$document) {
            http_response_code(404);
            echo "Document not found";
            return;
        }

        // Log download action
        $this->logDocumentAction($documentId, 'downloaded', 'Document downloaded');

        // Generate PDF or send HTML content
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="' . $document['nomor_dokumen'] . '.html"');

        echo $document['konten_dokumen'];
        exit;
    }

    // Helper methods
    private function generateDocumentNumber() {
        $year = date('Y');
        $month = date('m');

        $lastDoc = fetchRow("SELECT nomor_dokumen FROM digital_documents WHERE nomor_dokumen LIKE ? ORDER BY id DESC LIMIT 1", ["DOC-$year-$month%"]);

        if ($lastDoc) {
            $lastNum = intval(substr($lastDoc['nomor_dokumen'], -4));
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        return sprintf("DOC-%s-%s-%04d", $year, $month, $newNum);
    }

    private function populateTemplate($template, $data) {
        // Simple template variable replacement
        $replacements = [
            '{{nomor_pinjaman}}' => $data['loan_id'] ? fetchRow("SELECT no_pinjaman FROM pinjaman WHERE id = ?", [$data['loan_id']])['no_pinjaman'] ?? 'N/A' : 'N/A',
            '{{tanggal_perjanjian}}' => date('d F Y'),
            '{{nama_anggota}}' => $data['member_id'] ? fetchRow("SELECT nama_lengkap FROM anggota WHERE id = ?", [$data['member_id']])['nama_lengkap'] ?? 'N/A' : 'N/A',
            '{{jumlah_pinjaman}}' => number_format($data['loan_amount'] ?? 0),
            '{{tenor_bulan}}' => '12', // Default
            '{{bunga_persen}}' => '12', // Default
            '{{angsuran_per_bulan}}' => number_format(($data['loan_amount'] ?? 0) / 12),
            '{{tanggal_jatuh_tempo}}' => date('d F Y', strtotime('+12 months')),
            '{{tanggal_tanda_tangan}}' => date('d F Y'),
            '{{tanggal_cetak}}' => date('d F Y')
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private function canAccessDocument($documentId) {
        // Simplified access control - in production, implement proper ACL
        $document = fetchRow("SELECT * FROM digital_documents WHERE id = ?", [$documentId]);

        if (!$document) return false;

        // Document creator can always access
        if ($document['created_by'] == ($_SESSION['user']['id'] ?? null)) return true;

        // Members can access their own documents
        if ($document['member_id'] == ($_SESSION['user']['id'] ?? null)) return true;

        // Admins can access all documents
        return true; // Simplified - in production check user roles
    }

    private function checkDocumentCompletion($documentId) {
        // Check if all required signatures are complete
        $totalSignatures = (fetchRow("SELECT COUNT(*) as count FROM document_signatures WHERE document_id = ?", [$documentId]) ?? [])['count'] ?? 0;
        $completedSignatures = (fetchRow("SELECT COUNT(*) as count FROM document_signatures WHERE document_id = ? AND status = 'signed'", [$documentId]) ?? [])['count'] ?? 0;

        if ($totalSignatures > 0 && $completedSignatures >= $totalSignatures) {
            executeNonQuery("UPDATE digital_documents SET status = 'signed', signed_at = NOW() WHERE id = ?", [$documentId]);
        }
    }

    private function logDocumentAction($documentId, $action, $description, $signatureId = null) {
        executeNonQuery(
            "INSERT INTO document_audit_log (document_id, signature_id, action, description, ip_address, user_agent, performed_by) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $documentId,
                $signatureId,
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $_SESSION['user']['id'] ?? null
            ],
            'iisssss'
        );
    }

    private function getDocumentStats() {
        return [
            'total_documents' => (fetchRow("SELECT COUNT(*) as count FROM digital_documents") ?? [])['count'] ?? 0,
            'signed_documents' => (fetchRow("SELECT COUNT(*) as count FROM digital_documents WHERE status = 'signed'") ?? [])['count'] ?? 0,
            'pending_signatures' => (fetchRow("SELECT COUNT(*) as count FROM document_signatures WHERE status = 'pending'") ?? [])['count'] ?? 0,
            'completed_signatures' => (fetchRow("SELECT COUNT(*) as count FROM document_signatures WHERE status = 'signed'") ?? [])['count'] ?? 0
        ];
    }

    private function getRecentDocuments() {
        return fetchAll(
            "SELECT dd.*, a.nama_lengkap as member_name
             FROM digital_documents dd
             LEFT JOIN anggota a ON dd.member_id = a.id
             ORDER BY dd.created_at DESC
             LIMIT 5"
        ) ?? [];
    }

    private function getPendingSignatures() {
        return fetchAll(
            "SELECT ds.*, dd.judul_dokumen, dd.nomor_dokumen, a.nama_lengkap as member_name
             FROM document_signatures ds
             JOIN digital_documents dd ON ds.document_id = dd.id
             LEFT JOIN anggota a ON dd.member_id = a.id
             WHERE ds.status = 'pending'
             ORDER BY ds.created_at DESC
             LIMIT 10"
        ) ?? [];
    }
}
?>
