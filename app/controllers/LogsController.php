<?php
require_once __DIR__ . '/BaseController.php';

class LogsController extends BaseController {
    public function index() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development

        $filter_action = $_GET['action'] ?? '';
        $filter_table = $_GET['table'] ?? '';
        $filter_user = intval($_GET['user'] ?? 0);

        $sql = "SELECT l.*, u.full_name AS user_name FROM logs l LEFT JOIN users u ON l.user_id = u.id WHERE 1=1";
        $params = [];
        $types = '';

        if ($filter_action) {
            $sql .= " AND l.action LIKE ?";
            $params[] = '%' . $filter_action . '%';
            $types .= 's';
        }
        if ($filter_table) {
            $sql .= " AND l.table_name LIKE ?";
            $params[] = '%' . $filter_table . '%';
            $types .= 's';
        }
        if ($filter_user) {
            $sql .= " AND l.user_id = ?";
            $params[] = $filter_user;
            $types .= 'i';
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT 100"; // basic pagination
        $logs = fetchAll($sql, $params, $types);

        $this->render(__DIR__ . '/../views/logs/index.php', [
            'logs' => $logs,
            'filter_action' => $filter_action,
            'filter_table' => $filter_table,
            'filter_user' => $filter_user
        ]);
    }
}
