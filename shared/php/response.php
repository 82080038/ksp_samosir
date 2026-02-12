<?php

/**
 * Response helper sederhana untuk JSON.
 * Fokus: konsistensi format, mudah di-extend.
 */
function json_success($data = [], array $meta = [], int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $data,
        'meta' => $meta,
    ]);
    exit;
}

function json_error(string $message, int $status = 400, array $meta = []): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => $message,
            'code' => $status,
        ],
        'meta' => $meta,
    ]);
    exit;
}

/**
 * Simple try/catch wrapper untuk endpoint.
 * $callback harus return array data.
 */
function respond(callable $handler): void
{
    try {
        // Buffer output untuk menghindari headers already sent
        ob_start();
        
        $result = $handler();
        
        // Bersihkan buffer sebelum mengirim header
        ob_end_clean();
        
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(200);
        }
        
        echo json_encode($result);
        exit;
    } catch (Throwable $e) {
        ob_end_clean();
        
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(500);
        }
        
        echo json_encode([
            'success' => false,
            'error' => [
                'message' => $e->getMessage(),
                'code' => 500
            ]
        ]);
        exit;
    }
}

function success_response($data = null) {
    return ['success' => true, 'data' => $data];
}

function error_response($message, $code = 400) {
    http_response_code($code);
    return [
        'success' => false,
        'error' => [
            'message' => $message,
            'code' => $code
        ]
    ];
}
