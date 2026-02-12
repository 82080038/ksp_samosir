<?php
/**
 * Responsive Layout System
 * KSP Samosir - Bootstrap 5 Responsive Design
 */

/**
 * Generate responsive container classes
 */
function getResponsiveContainer($fluid = false) {
    return $fluid ? 'container-fluid' : 'container';
}

/**
 * Generate responsive grid classes
 */
function getResponsiveGrid($columns = 12, $breakpoint = 'md') {
    return "col-{$breakpoint}-{$columns}";
}

/**
 * Generate responsive card classes
 */
function getResponsiveCard($hover = true, $shadow = true) {
    $classes = ['card'];
    
    if ($shadow) {
        $classes[] = 'shadow-sm';
    }
    
    if ($hover) {
        $classes[] = 'h-100';
    }
    
    return implode(' ', $classes);
}

/**
 * Generate responsive navigation
 */
function getResponsiveNav($type = 'sidebar') {
    $nav_classes = [
        'navbar navbar-expand-lg navbar-dark bg-dark'
    ];
    
    if ($type === 'sidebar') {
        $nav_classes[] = 'fixed-top';
    }
    
    return implode(' ', $nav_classes);
}

/**
 * Generate responsive table classes
 */
function getResponsiveTable($striped = true, $bordered = true, $hover = true) {
    $classes = ['table'];
    
    if ($striped) {
        $classes[] = 'table-striped';
    }
    
    if ($bordered) {
        $classes[] = 'table-bordered';
    }
    
    if ($hover) {
        $classes[] = 'table-hover';
    }
    
    $classes[] = 'table-responsive';
    
    return implode(' ', $classes);
}

/**
 * Generate responsive form classes
 */
function getResponsiveForm($floating = false) {
    $classes = ['needs-validation'];
    
    if ($floating) {
        $classes[] = 'was-validated';
    }
    
    return implode(' ', $classes);
}

/**
 * Generate responsive button classes
 */
function getResponsiveButton($size = 'md', $outline = false, $block = false) {
    $classes = ['btn'];
    
    // Size classes
    $size_classes = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg'
    ];
    
    if (isset($size_classes[$size])) {
        $classes[] = $size_classes[$size];
    }
    
    // Outline
    if ($outline) {
        $classes[] = 'btn-outline-primary';
    } else {
        $classes[] = 'btn-primary';
    }
    
    // Block level
    if ($block) {
        $classes[] = 'd-block w-100';
    }
    
    return implode(' ', $classes);
}

/**
 * Generate responsive sidebar
 */
function getResponsiveSidebar() {
    return [
        'container' => 'd-flex flex-column flex-shrink-0 p-0 text-white bg-dark',
        'sidebar' => 'd-none d-lg-block sidebar collapse',
        'main' => 'flex-grow-1 ms-lg-3 px-4',
        'content' => 'container-fluid'
    ];
}

/**
 * Generate responsive dashboard layout
 */
function getResponsiveDashboard() {
    return [
        'sidebar_class' => 'col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse text-white',
        'main_class' => 'col-md-9 ms-sm-auto px-4',
        'container_class' => 'container-fluid px-4'
    ];
}

/**
 * Generate responsive card grid
 */
function getResponsiveCardGrid($cards_per_row = 3) {
    $grid_classes = [
        'row' => 'row g-4',
        'card' => 'col-md-6 col-xl-4 mb-4'
    ];
    
    switch ($cards_per_row) {
        case 1:
            $grid_classes['card'] = 'col-12 mb-4';
            break;
        case 2:
            $grid_classes['card'] = 'col-md-6 mb-4';
            break;
        case 3:
            $grid_classes['card'] = 'col-md-6 col-xl-4 mb-4';
            break;
        case 4:
            $grid_classes['card'] = 'col-md-6 col-lg-3 mb-4';
            break;
    }
    
    return $grid_classes;
}

/**
 * Generate responsive table wrapper
 */
function getResponsiveTableWrapper() {
    return [
        'wrapper' => 'table-responsive',
        'table' => 'table table-striped table-hover table-bordered',
        'pagination' => 'd-flex justify-content-between align-items-center mt-3'
    ];
}

/**
 * Generate responsive modal classes
 */
function getResponsiveModal($size = 'lg') {
    $modal_classes = [
        'modal' => 'modal fade',
        'dialog' => 'modal-dialog modal-dialog-centered',
        'content' => 'modal-content'
    ];
    
    $size_classes = [
        'sm' => 'modal-sm',
        'md' => 'modal-md',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl'
    ];
    
    if (isset($size_classes[$size])) {
        $modal_classes['dialog'] .= ' ' . $size_classes[$size];
    }
    
    return $modal_classes;
}

/**
 * Generate responsive form layout
 */
function getResponsiveFormLayout() {
    return [
        'container' => 'container-fluid',
        'row' => 'row justify-content-center',
        'form' => 'col-md-8 col-lg-6',
        'card' => 'card shadow-sm'
    ];
}

/**
 * Generate responsive navigation items
 */
function getResponsiveNavItems($items) {
    $nav_html = '';
    
    foreach ($items as $item) {
        $active_class = isset($item['active']) && $item['active'] ? 'active' : '';
        $nav_html .= '<li class="nav-item">';
        $nav_html .= '<a class="nav-link text-white ' . $active_class . '" href="' . $item['url'] . '">';
        
        if (isset($item['icon'])) {
            $nav_html .= '<i class="' . $item['icon'] . ' me-2"></i>';
        }
        
        $nav_html .= '<span class="d-none d-md-inline">' . $item['text'] . '</span>';
        $nav_html .= '</a>';
        $nav_html .= '</li>';
    }
    
    return $nav_html;
}

/**
 * Generate responsive breakpoints helper
 */
function getResponsiveBreakpoints() {
    return [
        'xs' => '<576px',   // Extra small
        'sm' => '≥576px',   // Small
        'md' => '≥768px',   // Medium
        'lg' => '≥992px',   // Large
        'xl' => '≥1200px',  // Extra large
        'xxl' => '≥1400px'  // Extra extra large
    ];
}

/**
 * Generate responsive utilities
 */
function getResponsiveUtilities() {
    return [
        'display' => [
            'd-block d-md-none' => 'Hidden on desktop, visible on mobile',
            'd-none d-md-block' => 'Visible on desktop, hidden on mobile',
            'd-block d-lg-none' => 'Hidden on large screens, visible on mobile/tablet',
            'd-none d-lg-block' => 'Visible on large screens, hidden on mobile/tablet'
        ],
        'text' => [
            'text-center text-md-start' => 'Center on mobile, left on desktop',
            'text-start text-md-center' => 'Left on mobile, center on desktop',
            'text-wrap text-md-nowrap' => 'Wrap on mobile, nowrap on desktop'
        ],
        'flex' => [
            'flex-column flex-md-row' => 'Column on mobile, row on desktop',
            'flex-row flex-md-column' => 'Row on mobile, column on desktop'
        ]
    ];
}

/**
 * Generate responsive sidebar toggle
 */
function getResponsiveSidebarToggle() {
    return '
        <button class="navbar-toggler position-absolute d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    ';
}

/**
 * Generate responsive footer
 */
function getResponsiveFooter() {
    return [
        'container' => 'container-fluid',
        'text' => 'text-center text-muted',
        'links' => 'd-flex justify-content-center flex-wrap'
    ];
}

/**
 * Check if mobile device
 */
function isMobileDevice() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $mobile_agents = ['Mobile', 'Android', 'iPhone', 'iPad', 'Tablet'];
    
    foreach ($mobile_agents as $agent) {
        if (stripos($user_agent, $agent) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get responsive container based on device
 */
function getAdaptiveContainer() {
    return isMobileDevice() ? 'container-fluid' : 'container';
}

/**
 * Generate responsive grid system
 */
function getResponsiveGridSystem() {
    return [
        'container' => 'container',
        'row' => 'row',
        'col' => [
            'xs' => 'col-12',
            'sm' => 'col-sm-12',
            'md' => 'col-md-12',
            'lg' => 'col-lg-12',
            'xl' => 'col-xl-12'
        ],
        'offsets' => [
            'md' => ['offset-md-1', 'offset-md-2', 'offset-md-3'],
            'lg' => ['offset-lg-1', 'offset-lg-2', 'offset-lg-3']
        ]
    ];
}
?>
