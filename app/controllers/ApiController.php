<?php

/**
 * Basic API Controller for external access.
 */
class ApiController {
    public function anggota() {
        // Basic auth: check API key or session
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        header('Content-Type: application/json');
        $anggota = fetchAll("SELECT id, no_anggota, nama_lengkap, status FROM anggota WHERE status='aktif'");
        echo json_encode($anggota);
    }
}
