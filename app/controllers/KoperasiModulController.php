<?php
/**
 * KoperasiModulController - Generic controller for all koperasi-specific modules
 * 
 * Handles routing for all 39 non-core koperasi modules by loading
 * module metadata from koperasi_modul table and rendering a consistent view.
 */

require_once __DIR__ . '/../core/BaseController.php';

class KoperasiModulController extends BaseController {

    public function index() {
        // Default: show koperasi module overview
        $this->render('koperasi_modul/index', [
            'modul' => null,
            'jenis' => null,
            'all_jenis' => $this->getAllJenisKoperasi(),
            'all_moduls' => $this->getAllModuls(),
            'page_title' => 'Modul Koperasi'
        ]);
    }

    /**
     * Handle a specific koperasi module by its kode_modul (lowercase).
     */
    public function handleModule($kodeModul) {
        $kodeUpper = strtoupper($kodeModul);

        // Fetch module info from DB
        $modul = null;
        try {
            $modul = fetchRow(
                "SELECT * FROM koperasi_modul WHERE kode_modul = ?",
                [$kodeUpper], 's'
            );
        } catch (Exception $e) {
            error_log("KoperasiModulController: " . $e->getMessage());
        }

        if (!$modul) {
            http_response_code(404);
            $this->render('errors/404', [
                'requested_page' => 'koperasi_modul/' . $kodeModul
            ]);
            return;
        }

        // Fetch parent jenis koperasi
        $jenis = null;
        $kategori = $modul['kategori'] ?? '';
        if ($kategori && $kategori !== 'core') {
            try {
                $jenis = fetchRow(
                    "SELECT * FROM koperasi_jenis WHERE LOWER(kode_jenis) = ?",
                    [strtolower($kategori)], 's'
                );
            } catch (Exception $e) {
                error_log("KoperasiModulController jenis: " . $e->getMessage());
            }
        }

        // Fetch sibling modules in same category
        $siblings = [];
        try {
            $siblings = fetchAll(
                "SELECT kode_modul, nama_modul, icon FROM koperasi_modul WHERE kategori = ? AND kode_modul != ? ORDER BY id",
                [$kategori, $kodeUpper], 'ss'
            ) ?? [];
        } catch (Exception $e) {}

        $this->render('koperasi_modul/module_dashboard', [
            'modul' => $modul,
            'jenis' => $jenis,
            'siblings' => $siblings,
            'page_title' => $modul['nama_modul']
        ]);
    }

    /**
     * Render view with layout
     */
    public function render($view, $data = []) {
        extract($data);
        ob_start();
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo '<div class="alert alert-danger">View not found: ' . htmlspecialchars($view) . '</div>';
        }
        $content = ob_get_clean();

        $layoutFile = __DIR__ . '/../views/layouts/main.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    private function getAllJenisKoperasi() {
        try {
            return fetchAll("SELECT * FROM koperasi_jenis WHERE is_active = 1 ORDER BY urutan_tampil") ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    private function getAllModuls() {
        try {
            return fetchAll("SELECT * FROM koperasi_modul ORDER BY kategori, id") ?? [];
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
