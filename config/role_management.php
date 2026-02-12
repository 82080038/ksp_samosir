<?php
/**
 * Role Management Helper Functions
 * KSP Samosir - Advanced Role-Based Access Control
 */

/**
 * Check if current user has specific permission
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    // Super admin has all permissions
    if ($user['role'] === 'super_admin') {
        return true;
    }
    
    // Check user permissions from database
    $sql = "SELECT COUNT(*) as has_perm 
            FROM user_roles ur 
            JOIN role_permissions rp ON ur.role_id = rp.role_id 
            JOIN permissions p ON rp.permission_id = p.id 
            WHERE ur.user_id = ? AND p.name = ?";
    
    $result = fetchRow($sql, [$user['id'], $permission], 'si');
    return $result && $result['has_perm'] > 0;
}

/**
 * Check if current user has any of the specified permissions
 */
function hasAnyPermission($permissions) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    // Super admin has all permissions
    if ($user['role'] === 'super_admin') {
        return true;
    }
    
    foreach ($permissions as $permission) {
        if (hasPermission($permission)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get all permissions for current user
 */
function getUserPermissions() {
    if (!isLoggedIn()) {
        return [];
    }
    
    $user = getCurrentUser();
    if (!$user) {
        return [];
    }
    
    // Super admin has all permissions
    if ($user['role'] === 'super_admin') {
        $all_perms = fetchAll("SELECT name FROM permissions WHERE is_active = 1");
        return array_column($all_perms, 'name');
    }
    
    $sql = "SELECT p.name 
            FROM user_roles ur 
            JOIN role_permissions rp ON ur.role_id = rp.role_id 
            JOIN permissions p ON rp.permission_id = p.id 
            WHERE ur.user_id = ? AND p.is_active = 1";
    
    $permissions = fetchAll($sql, [$user['id']], 'i');
    return array_column($permissions, 'name');
}

/**
 * Get user's role information
 */
function getUserRole() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $user = getCurrentUser();
    if (!$user) {
        return null;
    }
    
    $sql = "SELECT r.* 
            FROM user_roles ur 
            JOIN roles r ON ur.role_id = r.id 
            WHERE ur.user_id = ? AND r.is_active = 1";
    
    return fetchRow($sql, [$user['id']], 'i');
}

/**
 * Check if user can access specific module
 */
function canAccessModule($module) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    // Super admin has access to all modules
    if ($user['role'] === 'super_admin') {
        return true;
    }
    
    $module_permissions = [
        'dashboard' => ['view_dashboard'],
        'anggota' => ['view_anggota'],
        'simpanan' => ['view_simpanan'],
        'pinjaman' => ['view_pinjaman'],
        'produk' => ['view_produk'],
        'penjualan' => ['view_penjualan'],
        'laporan' => ['view_laporan'],
        'settings' => ['view_settings'],
        'logs' => ['view_logs']
    ];
    
    if (!isset($module_permissions[$module])) {
        return false;
    }
    
    return hasAnyPermission($module_permissions[$module]);
}

/**
 * Middleware function to check permissions before accessing controller
 */
function requirePermission($permission) {
    if (!hasPermission($permission)) {
        flashMessage('error', 'Anda tidak memiliki izin untuk mengakses halaman ini');
        redirect('dashboard');
    }
}

/**
 * Middleware function to check module access
 */
function requireModuleAccess($module) {
    if (!canAccessModule($module)) {
        flashMessage('error', 'Anda tidak memiliki izin untuk mengakses modul ' . $module);
        redirect('dashboard');
    }
}

/**
 * Get all available roles
 */
function getAllRoles() {
    return fetchAll("SELECT * FROM roles WHERE is_active = 1 ORDER BY name");
}

/**
 * Get permissions for a specific role
 */
function getRolePermissions($roleId) {
    $sql = "SELECT p.* 
            FROM role_permissions rp 
            JOIN permissions p ON rp.permission_id = p.id 
            WHERE rp.role_id = ? AND p.is_active = 1";
    
    return fetchAll($sql, [$roleId], 'i');
}

/**
 * Assign permission to role
 */
function assignPermissionToRole($roleId, $permissionId) {
    $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE assigned_at = NOW()";
    
    return executeNonQuery($sql, [$roleId, $permissionId], 'ii');
}

/**
 * Remove permission from role
 */
function removePermissionFromRole($roleId, $permissionId) {
    $sql = "DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?";
    return executeNonQuery($sql, [$roleId, $permissionId], 'ii');
}

/**
 * Assign role to user
 */
function assignRoleToUser($userId, $roleId, $assignedBy = null) {
    $sql = "INSERT INTO user_roles (user_id, role_id, assigned_by) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE assigned_at = NOW(), assigned_by = ?";
    
    return executeNonQuery($sql, [$userId, $roleId, $assignedBy, $assignedBy], 'iii');
}

/**
 * Remove role from user
 */
function removeRoleFromUser($userId, $roleId) {
    $sql = "DELETE FROM user_roles WHERE user_id = ? AND role_id = ?";
    return executeNonQuery($sql, [$userId, $roleId], 'ii');
}
?>
