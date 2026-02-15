<?php
/**
 * SidebarHelper - Dynamic sidebar menu from database
 * 
 * Fetches sidebar_menus table, filters by role, and builds
 * a nested structure (sections → items) for the layout to render.
 * 
 * Uses session-based caching to avoid repeated DB queries per request.
 */

/**
 * Get sidebar menus for the current user's role.
 * Returns a flat array of sections and items, already sorted.
 *
 * Structure:
 * [
 *   ['menu_type'=>'section', 'title'=>'Keanggotaan', 'items'=>[
 *       ['title'=>'Anggota', 'url'=>'anggota', 'icon'=>'bi-people', ...],
 *       ...
 *   ]],
 *   ['menu_type'=>'item', 'title'=>'Dashboard', 'url'=>'dashboard', 'icon'=>'bi-speedometer2', ...],
 *   ...
 * ]
 */
function getSidebarMenus($role = null) {
    if ($role === null) {
        $user = getCurrentUser();
        $role = $user['role'] ?? 'member';
    }

    // Session cache key per role
    $cacheKey = 'sidebar_menus_' . $role;
    if (isset($_SESSION[$cacheKey]) && !empty($_SESSION[$cacheKey])) {
        return $_SESSION[$cacheKey];
    }

    try {
        // Fetch all active menus, sorted by sort_order
        $allMenus = fetchAll(
            "SELECT id, parent_id, menu_type, title, url, icon, roles, sort_order, badge_query
             FROM sidebar_menus
             WHERE is_active = 1
             ORDER BY sort_order ASC"
        ) ?? [];
    } catch (Exception $e) {
        error_log("SidebarHelper: Failed to fetch menus: " . $e->getMessage());
        return [];
    }

    // Filter by role using JSON_CONTAINS equivalent in PHP
    $filtered = [];
    foreach ($allMenus as $menu) {
        $menuRoles = json_decode($menu['roles'] ?? '[]', true);
        if (is_array($menuRoles) && in_array($role, $menuRoles, true)) {
            $filtered[] = $menu;
        }
    }

    // Build nested structure: top-level items + sections with children
    $topLevel = [];   // items with parent_id = NULL
    $children = [];   // items grouped by parent_id

    foreach ($filtered as $menu) {
        if ($menu['parent_id'] === null || $menu['parent_id'] === 0) {
            $topLevel[$menu['id']] = $menu;
        } else {
            $children[$menu['parent_id']][] = $menu;
        }
    }

    // Assemble final structure
    $result = [];
    foreach ($topLevel as $id => $menu) {
        if ($menu['menu_type'] === 'section') {
            $menu['items'] = $children[$id] ?? [];
            // Only add section if it has visible items
            if (!empty($menu['items'])) {
                $result[] = $menu;
            }
        } else {
            // Standalone item (e.g. Dashboard)
            $result[] = $menu;
        }
    }

    // Resolve badge counts (optional dynamic badges)
    foreach ($result as &$entry) {
        if ($entry['menu_type'] === 'section' && !empty($entry['items'])) {
            foreach ($entry['items'] as &$item) {
                $item['badge'] = resolveBadge($item);
            }
        } else {
            $entry['badge'] = resolveBadge($entry);
        }
    }

    // Cache in session
    $_SESSION[$cacheKey] = $result;

    return $result;
}

/**
 * Resolve optional badge count from badge_query column.
 * Returns int count or null if no badge.
 */
function resolveBadge($menu) {
    if (empty($menu['badge_query'])) {
        return null;
    }
    try {
        $row = fetchRow($menu['badge_query']);
        if ($row) {
            // Return first column value
            return (int) reset($row);
        }
    } catch (Exception $e) {
        // Silently fail - badges are optional
    }
    return null;
}

/**
 * Clear sidebar cache (call after menu updates).
 */
function clearSidebarCache() {
    foreach ($_SESSION as $key => $val) {
        if (strpos($key, 'sidebar_menus_') === 0) {
            unset($_SESSION[$key]);
        }
    }
}

/**
 * Render the sidebar HTML for the current user.
 * This produces the <ul class="nav flex-column"> content.
 */
function renderSidebarMenus() {
    $menus = getSidebarMenus();
    $output = '';

    foreach ($menus as $entry) {
        if ($entry['menu_type'] === 'section') {
            // Section header
            $output .= '<li class="sidebar-heading text-uppercase text-white-50 px-3 mt-2 mb-1" style="font-size:0.65rem;letter-spacing:0.05em">';
            $output .= '<span class="sidebar-text">' . htmlspecialchars($entry['title']) . '</span>';
            $output .= '</li>' . "\n";

            // Section items
            foreach ($entry['items'] as $item) {
                $output .= renderMenuItem($item);
            }
        } else {
            // Standalone item (e.g. Dashboard)
            $output .= renderMenuItem($entry);
        }
    }

    return $output;
}

/**
 * Render a single menu item <li>.
 */
function renderMenuItem($item) {
    $url = $item['url'] ?? '#';
    $icon = $item['icon'] ?? 'bi-circle';
    $title = htmlspecialchars($item['title']);
    $activeClass = isActivePage($url) ? ' active' : '';
    $badge = '';

    if (!empty($item['badge']) && $item['badge'] > 0) {
        $badge = ' <span class="badge bg-danger rounded-pill ms-auto">' . (int)$item['badge'] . '</span>';
    }

    $html  = '<li class="nav-item">';
    $html .= '<a class="nav-link text-white' . $activeClass . '" href="' . base_url($url) . '">';
    $html .= '<i class="bi ' . htmlspecialchars($icon) . ' me-2"></i>';
    $html .= '<span class="sidebar-text">' . $title . '</span>';
    $html .= $badge;
    $html .= '</a></li>' . "\n";

    return $html;
}
