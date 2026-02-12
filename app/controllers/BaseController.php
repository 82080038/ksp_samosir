<?php
/**
 * Base Controller
 * Provides common auth/role guards and simple view rendering.
 */
class BaseController {
    protected function ensureLoginAndRole($roles = null) {
        requireLogin();
        if ($roles !== null && !hasRole($roles)) {
            flashMessage('error', 'Akses tidak diizinkan untuk peran Anda');
            redirect('dashboard');
        }
    }

    protected function render($viewPath, $data = [], $useLayout = true) {
        extract($data);
        if ($useLayout && file_exists(APP_PATH . '/app/views/layouts/main.php')) {
            ob_start();
            require $viewPath;
            $content = ob_get_clean();
            require APP_PATH . '/app/views/layouts/main.php';
        } else {
            require $viewPath;
        }
    }
}
