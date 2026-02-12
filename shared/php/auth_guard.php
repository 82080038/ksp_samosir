<?php

/**
 * Auth guard minimal untuk halaman/API.
 * Asumsi session sudah dimulai di bootstrap.
 */

function require_login(): void
{
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => ['message' => 'Unauthorized']]);
        exit;
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Cek role (array atau string). Return void jika ok, exit jika gagal.
 */
function require_role($roles): void
{
    // Pastikan tidak ada output sebelumnya
    if (headers_sent()) {
        error_log('Warning: Headers already sent when require_role() called');
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Debug logging - hanya ke error log
    error_log('Auth check - Session: ' . print_r($_SESSION, true));
    
    if (!isset($_SESSION['user_id'])) {
        if (!headers_sent()) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json');
        }
        exit(json_encode(['success' => false, 'error' => ['message' => 'Unauthorized']]));
    }

    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], (array)$roles)) {
        if (!headers_sent()) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
        }
        exit(json_encode(['success' => false, 'error' => ['message' => 'Forbidden']]));
    }

    // Tambahkan validasi tambahan
    if (empty($_SESSION['koperasi_id'])) {
        if (!headers_sent()) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
        }
        exit(json_encode(['success' => false, 'error' => ['message' => 'Invalid cooperative session']]));
    }
}

function check_role($roles) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['role']) && in_array($_SESSION['role'], (array)$roles);
}
