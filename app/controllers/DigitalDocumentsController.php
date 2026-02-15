<?php
require_once __DIR__ . '/BaseController.php';

/**
 * Digital Documents Controller
 * Manages digital document storage and management
 */

class DigitalDocumentsController extends BaseController {

    public function index() {
        // $this->ensureLoginAndRole(['admin', 'staff']); // DISABLED for development

        $documents = $this->getDocuments();
        $stats = $this->getDocumentStats();

        $this->render('digital_documents/index', [
            'documents' => $documents,
            'stats' => $stats
        ]);
    }

    public function upload() {
        // $this->ensureLoginAndRole(['admin', 'staff']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processUpload();
            return;
        }

        $this->render('digital_documents/upload', []);
    }

    public function processUpload() {
        // $this->ensureLoginAndRole(['admin', 'staff']); // DISABLED for development

        if (!isset($_FILES['document'])) {
            flashMessage('error', 'No file uploaded');
            redirect('digital_documents/upload');
            return;
        }

        $file = $_FILES['document'];
        $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png'];

        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedTypes)) {
            flashMessage('error', 'File type not allowed');
            redirect('digital_documents/upload');
            return;
        }

        if ($fileError !== 0) {
            flashMessage('error', 'Error uploading file');
            redirect('digital_documents/upload');
            return;
        }

        if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
            flashMessage('error', 'File too large (max 10MB)');
            redirect('digital_documents/upload');
            return;
        }

        $newFileName = uniqid('', true) . '.' . $fileExt;
        $uploadPath = __DIR__ . '/../../uploads/digital_documents/' . $newFileName;

        if (move_uploaded_file($fileTmp, $uploadPath)) {
            // Save to database
            executeNonQuery(
                "INSERT INTO digital_documents (original_name, file_name, file_size, file_type, uploaded_by) VALUES (?, ?, ?, ?, ?)",
                [$fileName, $newFileName, $fileSize, $fileExt, $_SESSION['user']['id'] ?? null],
                'ssiss'
            );

            flashMessage('success', 'Document uploaded successfully');
            redirect('digital_documents');
        } else {
            flashMessage('error', 'Failed to save file');
            redirect('digital_documents/upload');
        }
    }

    public function download($id) {
        // $this->ensureLoginAndRole(['admin', 'staff']); // DISABLED for development

        $doc = fetchRow("SELECT * FROM digital_documents WHERE id = ?", [$id], 'i');

        if (!$doc) {
            flashMessage('error', 'Document not found');
            redirect('digital_documents');
            return;
        }

        $filePath = __DIR__ . '/../../uploads/digital_documents/' . $doc['file_name'];

        if (file_exists($filePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $doc['original_name'] . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            flashMessage('error', 'File not found on server');
            redirect('digital_documents');
        }
    }

    public function delete($id) {
        // $this->ensureLoginAndRole(['admin']); // DISABLED for development

        $doc = fetchRow("SELECT * FROM digital_documents WHERE id = ?", [$id], 'i');

        if (!$doc) {
            flashMessage('error', 'Document not found');
            redirect('digital_documents');
            return;
        }

        $filePath = __DIR__ . '/../../uploads/digital_documents/' . $doc['file_name'];

        // Delete from database
        executeNonQuery("DELETE FROM digital_documents WHERE id = ?", [$id], 'i');

        // Delete file
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        flashMessage('success', 'Document deleted successfully');
        redirect('digital_documents');
    }

    private function getDocuments() {
        return fetchAll(
            "SELECT dd.*, u.username as uploaded_by_name
             FROM digital_documents dd
             LEFT JOIN users u ON dd.uploaded_by = u.id
             ORDER BY dd.uploaded_at DESC"
        ) ?? [];
    }

    private function getDocumentStats() {
        return [
            'total_documents' => (fetchRow("SELECT COUNT(*) as count FROM digital_documents") ?? [])['count'] ?? 0,
            'total_size' => (fetchRow("SELECT SUM(file_size) as total FROM digital_documents") ?? [])['total'] ?? 0,
            'this_month' => (fetchRow("SELECT COUNT(*) as count FROM digital_documents WHERE MONTH(uploaded_at) = MONTH(CURRENT_DATE) AND YEAR(uploaded_at) = YEAR(CURRENT_DATE)") ?? [])['count'] ?? 0
        ];
    }
}
?>
