<?php
/**
 * Admin Settings Controller
 * KSP Samosir - Administrative Management
 */

require_once __DIR__ . '/BaseController.php';

class SettingsController extends BaseController {
    
    public function index() {
        // requirePermission('view_settings'); // DISABLED for development
        $this->render('settings/index', [
            'settings' => function_exists('getPengaturan') ? getPengaturan() : [],
            'roles' => function_exists('getAllRoles') ? getAllRoles() : [],
            'permissions' => fetchAll("SELECT * FROM permissions WHERE is_active = 1 ORDER BY module, name")
        ]);
    }
    
    public function update() {
        requirePermission('edit_settings');
        
        $key = sanitize($_POST['key'] ?? '');
        $value = sanitize($_POST['value'] ?? '');
        
        if (empty($key)) {
            flashMessage('error', 'Key pengaturan wajib diisi');
            redirect('settings');
        }
        
        if (updatePengaturan($key, $value)) {
            flashMessage('success', 'Pengaturan berhasil diperbarui');
        } else {
            flashMessage('error', 'Gagal memperbarui pengaturan');
        }
        
        redirect('settings');
    }
    
    public function users() {
        // requirePermission('manage_users'); // DISABLED for development
        $users = fetchAll("SELECT u.*, r.name as role_name 
                          FROM users u 
                          LEFT JOIN user_roles ur ON u.id = ur.user_id 
                          LEFT JOIN roles r ON ur.role_id = r.id 
                          ORDER BY u.username");
        
        $this->render('settings/users', [
            'users' => $users,
            'roles' => function_exists('getAllRoles') ? getAllRoles() : [],
            'settings' => function_exists('getPengaturan') ? getPengaturan() : [],
            'permissions' => []
        ]);
    }
    
    public function createUser() {
        requirePermission('create_users');
        
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = sanitize($_POST['full_name'] ?? '');
        $role_id = intval($_POST['role_id'] ?? 0);
        
        if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($role_id)) {
            flashMessage('error', 'Semua field wajib diisi');
            redirect('settings/users');
        }
        
        // Check duplicates
        $existing = fetchRow("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email], 'ss');
        if ($existing) {
            flashMessage('error', 'Username atau email sudah terdaftar');
            redirect('settings/users');
        }
        
        runInTransaction(function($conn) use ($username, $email, $password, $full_name, $role_id) {
            // Insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param('ssss', $username, $email, $hashed_password, $full_name);
            $stmt->execute();
            $user_id = $conn->insert_id;
            $stmt->close();
            
            // Assign role
            assignRoleToUser($user_id, $role_id, $_SESSION['user']['id']);
        });
        
        flashMessage('success', 'User berhasil dibuat');
        redirect('settings/users');
    }
    
    public function editUser($id) {
        requirePermission('edit_users');
        
        $user = fetchRow("SELECT u.*, ur.role_id 
                         FROM users u 
                         LEFT JOIN user_roles ur ON u.id = ur.user_id 
                         WHERE u.id = ?", [$id], 'i');
        
        if (!$user) {
            flashMessage('error', 'User tidak ditemukan');
            redirect('settings/users');
        }
        
        $this->render('settings/edit_user', [
            'user' => $user,
            'roles' => getAllRoles()
        ]);
    }
    
    public function updateUser($id) {
        requirePermission('edit_users');
        
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $full_name = sanitize($_POST['full_name'] ?? '');
        $role_id = intval($_POST['role_id'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($username) || empty($email) || empty($full_name) || empty($role_id)) {
            flashMessage('error', 'Semua field wajib diisi');
            redirect('settings/edit_user/' . $id);
        }
        
        runInTransaction(function($conn) use ($id, $username, $email, $full_name, $role_id, $is_active) {
            // Update user
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param('sssii', $username, $email, $full_name, $is_active, $id);
            $stmt->execute();
            $stmt->close();
            
            // Update role
            executeNonQuery("DELETE FROM user_roles WHERE user_id = ?", [$id], 'i');
            assignRoleToUser($id, $role_id, $_SESSION['user']['id']);
        });
        
        flashMessage('success', 'User berhasil diperbarui');
        redirect('settings/users');
    }
    
    public function deleteUser($id) {
        requirePermission('delete_users');
        
        if ($id == $_SESSION['user']['id']) {
            flashMessage('error', 'Tidak dapat menghapus user yang sedang login');
            redirect('settings/users');
        }
        
        runInTransaction(function($conn) use ($id) {
            // Delete user roles
            $stmt = $conn->prepare("DELETE FROM user_roles WHERE user_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            
            // Delete user
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        });
        
        flashMessage('success', 'User berhasil dihapus');
        redirect('settings/users');
    }
    
    public function roles() {
        requirePermission('manage_permissions');
        
        $this->render('settings/roles', [
            'roles' => getAllRoles(),
            'permissions' => fetchAll("SELECT * FROM permissions WHERE is_active = 1 ORDER BY module, name")
        ]);
    }
    
    public function editRole($id) {
        requirePermission('manage_permissions');
        
        $role = fetchRow("SELECT * FROM roles WHERE id = ?", [$id], 'i');
        if (!$role) {
            flashMessage('error', 'Role tidak ditemukan');
            redirect('settings/roles');
        }
        
        $role_permissions = getRolePermissions($id);
        $permission_ids = array_column($role_permissions, 'id');
        
        $this->render('settings/edit_role', [
            'role' => $role,
            'permissions' => fetchAll("SELECT * FROM permissions WHERE is_active = 1 ORDER BY module, name"),
            'role_permissions' => $permission_ids
        ]);
    }
    
    public function updateRole($id) {
        requirePermission('manage_permissions');
        
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $permission_ids = $_POST['permission_ids'] ?? [];
        
        if (empty($name)) {
            flashMessage('error', 'Nama role wajib diisi');
            redirect('settings/edit_role/' . $id);
        }
        
        runInTransaction(function($conn) use ($id, $name, $description, $permission_ids) {
            // Update role
            $stmt = $conn->prepare("UPDATE roles SET name = ?, description = ? WHERE id = ?");
            $stmt->bind_param('ssi', $name, $description, $id);
            $stmt->execute();
            $stmt->close();
            
            // Update permissions
            $stmt = $conn->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            
            foreach ($permission_ids as $permission_id) {
                assignPermissionToRole($id, $permission_id);
            }
        });
        
        flashMessage('success', 'Role berhasil diperbarui');
        redirect('settings/roles');
    }
    
    public function accounting() {
        requirePermission('view_accounts');
        
        $coa = fetchAll("SELECT * FROM coa WHERE is_active = 1 ORDER BY code");
        $journals = fetchAll("SELECT j.*, u.full_name as posted_by_name 
                            FROM jurnal j 
                            LEFT JOIN users u ON j.posted_by = u.id 
                            ORDER BY j.entry_date DESC, j.created_at DESC LIMIT 20");
        
        $this->render('settings/accounting', [
            'coa' => $coa,
            'journals' => $journals
        ]);
    }
    
    public function createJournal() {
        requirePermission('manage_accounts');
        
        $entry_date = sanitize($_POST['entry_date'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $reference_number = sanitize($_POST['reference_number'] ?? '');
        $entries = $_POST['entries'] ?? [];
        
        if (empty($entry_date) || empty($description) || empty($entries)) {
            flashMessage('error', 'Tanggal, deskripsi, dan entries wajib diisi');
            redirect('settings/accounting');
        }
        
        try {
            $jurnal_id = createJournalEntry($entry_date, $description, $entries, $reference_number);
            flashMessage('success', 'Jurnal berhasil dibuat');
        } catch (Exception $e) {
            flashMessage('error', 'Error: ' . $e->getMessage());
        }
        
        redirect('settings/accounting');
    }
    
    public function postJournal($id) {
        requirePermission('manage_accounts');
        
        if (postJournal($id)) {
            flashMessage('success', 'Jurnal berhasil diposting');
        } else {
            flashMessage('error', 'Gagal posting jurnal');
        }
        
        redirect('settings/accounting');
    }
    
    public function reports() {
        requirePermission('view_reports');
        
        $date_from = sanitize($_GET['date_from'] ?? date('Y-m-01'));
        $date_to = sanitize($_GET['date_to'] ?? date('Y-m-d'));
        $jenis = sanitize($_GET['jenis'] ?? 'laba_rugi');
        
        $report = generateLaporanKeuangan($date_to, $jenis);
        
        $this->render('settings/reports', [
            'report' => $report,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'jenis' => $jenis
        ]);
    }
}
?>
