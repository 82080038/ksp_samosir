<?php
require_once __DIR__ . '/BaseController.php';

/**
 * PengawasController handles supervision and sanction management.
 * Extends BaseController for common auth/role guards and rendering.
 */
class PengawasController extends BaseController {
    /**
     * Display supervision dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['pengawas']); // DISABLED for development

        $stats = $this->getSupervisionStats();
        $recent_violations = $this->getRecentViolations();
        $pending_reports = $this->getPendingReports();

        $this->render('pengawas/index', [
            'stats' => $stats,
            'recent_violations' => $recent_violations,
            'pending_reports' => $pending_reports
        ]);
    }

    /**
     * Display activity logs.
     */
    public function logs() {
        // $this->ensureLoginAndRole(['pengawas']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM logs") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $logs = fetchAll("SELECT l.*, u.full_name as user_name FROM logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('pengawas/logs', [
            'logs' => $logs,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Display violations management.
     */
    public function violations() {
        // $this->ensureLoginAndRole(['pengawas']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM pelanggaran") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $violations = fetchAll("SELECT p.*, u.full_name as user_name, s.jenis_sanksi FROM pelanggaran p LEFT JOIN users u ON p.user_id = u.id LEFT JOIN sanksi s ON p.sanksi_id = s.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('pengawas/violations', [
            'violations' => $violations,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Display sanctions reference.
     */
    public function sanctions() {
        // $this->ensureLoginAndRole(['pengawas']); // DISABLED for development

        $sanctions = fetchAll("SELECT * FROM sanksi ORDER BY jenis_sanksi");

        $this->render('pengawas/sanctions', [
            'sanctions' => $sanctions
        ]);
    }

    /**
     * Display supervision reports.
     */
    public function reports() {
        // $this->ensureLoginAndRole(['pengawas']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM laporan_pengawas") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $reports = fetchAll("SELECT lp.*, u.full_name as created_by_name FROM laporan_pengawas lp LEFT JOIN users u ON lp.created_by = u.id ORDER BY lp.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('pengawas/reports', [
            'reports' => $reports,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Get supervision statistics.
     */
    private function getSupervisionStats() {
        $stats = [];

        // Total violations
        $stats['total_violations'] = (fetchRow("SELECT COUNT(*) as total FROM pelanggaran") ?? [])['total'] ?? 0;

        // Pending violations
        $stats['pending_violations'] = (fetchRow("SELECT COUNT(*) as total FROM pelanggaran WHERE status = 'investigasi'") ?? [])['total'] ?? 0;

        // Total logs this month
        $stats['logs_this_month'] = (fetchRow("SELECT COUNT(*) as total FROM logs WHERE MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)") ?? [])['total'] ?? 0;

        // Pending reports
        $stats['pending_reports'] = (fetchRow("SELECT COUNT(*) as total FROM laporan_pengawas WHERE status = 'draft'") ?? [])['total'] ?? 0;

        return $stats;
    }

    /**
     * Get recent violations.
     */
    private function getRecentViolations() {
        return fetchAll("SELECT p.*, u.full_name as user_name FROM pelanggaran p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5") ?? [];
    }

    /**
     * Get pending reports.
     */
    private function getPendingReports() {
        return fetchAll("SELECT lp.*, u.full_name as created_by_name FROM laporan_pengawas lp LEFT JOIN users u ON lp.created_by = u.id WHERE lp.status = 'draft' ORDER BY lp.created_at DESC LIMIT 5") ?? [];
    }
}
