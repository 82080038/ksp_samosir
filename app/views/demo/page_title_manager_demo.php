<?php
/**
 * Page Title Manager Demo
 * 
 * Halaman demo untuk menampilkan kemampuan sinkronisasi otomatis
 * antara judul halaman dan navigation links
 * 
 * @author KSP Samosir Development Team
 * @version 1.0.0
 */

// Include dependencies
require_once __DIR__ . '/../../app/helpers/DependencyManager.php';

// Initialize view
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;

// Set page info
$pageInfo['title'] = 'Page Title Manager Demo';
$pageInfo['description'] = 'Demonstrasi sinkronisasi otomatis judul halaman dengan navigation links';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title Manager Demo - KSP Samosir</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- KSP Enhanced CSS -->
    <link href="<?= base_url('assets/css/ksp-enhanced.css') ?>" rel="stylesheet">
    
    <style>
        .demo-section {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            background: #f8f9fa;
        }
        
        .navigation-demo {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .nav-demo-item {
            padding: 0.5rem 1rem;
            margin: 0.25rem 0;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .nav-demo-item:hover {
            background: #e9ecef;
        }
        
        .nav-demo-item.active {
            background: #0d6efd;
            color: white;
        }
        
        .code-block {
            background: #212529;
            color: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            overflow-x: auto;
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        
        .status-success { background: #198754; }
        .status-warning { background: #ffc107; }
        .status-error { background: #dc3545; }
    </style>
</head>
<body>
    <!-- Main Content Container -->
    <div class="main-content" id="main-content" data-page="page-title-manager-demo">
        
        <!-- Page Header with Dynamic Title -->
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
            <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="page-title-manager-demo">Page Title Manager Demo</h1>
            <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-sm btn-primary" onclick="testPageTitleManager()">
                        <i class="bi bi-play-circle"></i> Test Sync
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleDebug()">
                        <i class="bi bi-bug"></i> Debug
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="showNavigationCache()">
                        <i class="bi bi-database"></i> Cache
                    </button>
                </div>
            </div>
        </div>

        <!-- Demo Content -->
        <div class="container-fluid">
            <!-- Status Section -->
            <div class="demo-section">
                <h3><i class="bi bi-activity"></i> Status Sinkronisasi</h3>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Page Title Manager</h6>
                                <p class="card-text">
                                    <span class="status-indicator" id="ptm-status"></span>
                                    <span id="ptm-status-text">Checking...</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Navigation Cache</h6>
                                <p class="card-text">
                                    <span class="status-indicator" id="cache-status"></span>
                                    <span id="cache-status-text">Checking...</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Current Page</h6>
                                <p class="card-text">
                                    <span class="status-indicator status-success"></span>
                                    <span id="current-page-text">Loading...</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Demo -->
            <div class="demo-section">
                <h3><i class="bi bi-list"></i> Navigation Links Demo</h3>
                <p class="text-muted">Klik pada navigation link di bawah untuk melihat sinkronisasi judul halaman secara otomatis:</p>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>Static Navigation (HTML)</h5>
                        <div class="navigation-demo">
                            <div class="nav-demo-item active" data-page="page-title-manager-demo" onclick="simulateNavigation('page-title-manager-demo', 'Page Title Manager Demo')">
                                <i class="bi bi-speedometer2"></i> Page Title Manager Demo
                            </div>
                            <div class="nav-demo-item" data-page="dashboard" onclick="simulateNavigation('dashboard', 'Dashboard')">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </div>
                            <div class="nav-demo-item" data-page="anggota" onclick="simulateNavigation('anggota', 'Anggota')">
                                <i class="bi bi-people"></i> Anggota
                            </div>
                            <div class="nav-demo-item" data-page="simpanan" onclick="simulateNavigation('simpanan', 'Simpanan')">
                                <i class="bi bi-piggy-bank"></i> Simpanan
                            </div>
                            <div class="nav-demo-item" data-page="pinjaman" onclick="simulateNavigation('pinjaman', 'Pinjaman')">
                                <i class="bi bi-cash-stack"></i> Pinjaman
                            </div>
                            <div class="nav-demo-item" data-page="settings" onclick="simulateNavigation('settings', 'Pengaturan')">
                                <i class="bi bi-gear"></i> Pengaturan
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Dynamic Navigation (Database)</h5>
                        <div class="navigation-demo" id="dynamic-nav">
                            <div class="text-center text-muted">
                                <i class="bi bi-arrow-clockwise"></i> Loading from database...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Controls -->
            <div class="demo-section">
                <h3><i class="bi bi-gear"></i> Test Controls</h3>
                <div class="row">
                    <div class="col-md-6">
                        <h5>Manual Page Title Update</h5>
                        <div class="input-group">
                            <input type="text" class="form-control" id="manual-title" placeholder="Masukkan judul halaman">
                            <input type="text" class="form-control" id="manual-page" placeholder="Masukkan page key">
                            <button class="btn btn-primary" onclick="updateManualTitle()">Update</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Navigation Cache Operations</h5>
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-primary" onclick="rebuildCache()">Rebuild Cache</button>
                            <button class="btn btn-outline-info" onclick="showCacheContents()">Show Contents</button>
                            <button class="btn btn-outline-warning" onclick="clearCache()">Clear Cache</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Debug Console -->
            <div class="demo-section" id="debug-section" style="display: none;">
                <h3><i class="bi bi-bug"></i> Debug Console</h3>
                <div class="card">
                    <div class="card-body">
                        <div id="debug-output" class="code-block" style="max-height: 300px; overflow-y: auto;">
                            Debug output will appear here...
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Response -->
            <div class="demo-section">
                <h3><i class="bi bi-arrow-left-right"></i> Navigation API Response</h3>
                <div class="card">
                    <div class="card-body">
                        <div id="api-response" class="code-block" style="max-height: 200px; overflow-y: auto;">
                            Loading API response...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/ksp-page-title-manager.js') ?>"></script>
    
    <script>
        // Global variables
        let debugMode = false;
        
        // Initialize demo
        document.addEventListener('DOMContentLoaded', function() {
            initializeDemo();
            loadDynamicNavigation();
            loadNavigationAPI();
        });
        
        function initializeDemo() {
            // Initialize Page Title Manager dengan debug mode
            if (typeof KSP !== 'undefined' && KSP.PageTitleManager) {
                KSP.PageTitleManager.init({
                    debugMode: true
                });
                
                updateStatus('ptm-status', 'ptm-status-text', 'success', 'Initialized');
                updateCurrentPage();
            } else {
                updateStatus('ptm-status', 'ptm-status-text', 'error', 'Not Available');
            }
            
            // Setup event listeners
            document.addEventListener('pageTitleUpdate', function(e) {
                logDebug('Page title updated: ' + e.detail.title + ' (page: ' + e.detail.page + ')');
                updateCurrentPage();
            });
        }
        
        function updateStatus(statusId, textId, status, text) {
            const indicator = document.getElementById(statusId);
            const textElement = document.getElementById(textId);
            
            if (indicator) {
                indicator.className = 'status-indicator status-' + status;
            }
            if (textElement) {
                textElement.textContent = text;
            }
        }
        
        function updateCurrentPage() {
            if (typeof KSP !== 'undefined' && KSP.PageTitleManager) {
                const currentPage = KSP.PageTitleManager.getCurrentPage();
                document.getElementById('current-page-text').textContent = currentPage || 'Unknown';
            }
        }
        
        function simulateNavigation(page, title) {
            // Update active state
            document.querySelectorAll('.nav-demo-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`[data-page="${page}"]`).classList.add('active');
            
            // Update main-content data-page
            document.getElementById('main-content').setAttribute('data-page', page);
            
            // Update page title
            if (typeof KSP !== 'undefined' && KSP.PageTitleManager) {
                KSP.PageTitleManager.syncCurrentPage();
            } else {
                // Fallback
                updatePageTitle(title, page);
            }
            
            logDebug(`Navigated to: ${page} - ${title}`);
        }
        
        function testPageTitleManager() {
            if (typeof KSP !== 'undefined' && KSP.PageTitleManager) {
                KSP.PageTitleManager.sync();
                logDebug('Manual sync triggered');
                showSuccess('Page Title Manager sync completed');
            } else {
                showError('Page Title Manager not available');
            }
        }
        
        function toggleDebug() {
            debugMode = !debugMode;
            const debugSection = document.getElementById('debug-section');
            debugSection.style.display = debugMode ? 'block' : 'none';
            
            if (debugMode && typeof KSP !== 'undefined' && KSP.PageTitleManager) {
                KSP.PageTitleManager.config.debugMode = true;
                logDebug('Debug mode enabled');
            }
        }
        
        function showNavigationCache() {
            if (typeof KSP !== 'undefined' && KSP.PageTitleManager) {
                const cache = KSP.PageTitleManager.getNavigationCache();
                logDebug('Navigation Cache Contents:');
                logDebug(JSON.stringify(cache, null, 2));
                updateStatus('cache-status', 'cache-status-text', 'success', Object.keys(cache).length + ' items');
            }
        }
        
        function rebuildCache() {
            if (typeof KSP !== 'undefined' && KSP.PageTitleManager) {
                KSP.PageTitleManager.buildNavigationCache();
                logDebug('Navigation cache rebuilt');
                showSuccess('Cache rebuilt successfully');
            }
        }
        
        function showCacheContents() {
            showNavigationCache();
        }
        
        function clearCache() {
            if (typeof KSP !== 'undefined' && KSP.PageTitleManager) {
                KSP.PageTitleManager.navigationCache = {};
                logDebug('Navigation cache cleared');
                showWarning('Cache cleared');
            }
        }
        
        function updateManualTitle() {
            const title = document.getElementById('manual-title').value;
            const page = document.getElementById('manual-page').value;
            
            if (title && page) {
                updatePageTitle(title, page);
                logDebug(`Manual update: ${title} (${page})`);
                showSuccess('Title updated manually');
            } else {
                showError('Please enter both title and page');
            }
        }
        
        function loadDynamicNavigation() {
            fetch('<?= base_url('api/navigation') ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderDynamicNavigation(data.data.navigation);
                        updateStatus('cache-status', 'cache-status-text', 'success', 'Dynamic loaded');
                    } else {
                        throw new Error(data.error || 'Failed to load');
                    }
                })
                .catch(error => {
                    logDebug('Failed to load dynamic navigation: ' + error.message);
                    document.getElementById('dynamic-nav').innerHTML = 
                        '<div class="text-center text-danger"><i class="bi bi-exclamation-triangle"></i> Failed to load</div>';
                    updateStatus('cache-status', 'cache-status-text', 'error', 'Load failed');
                });
        }
        
        function renderDynamicNavigation(navigation) {
            const container = document.getElementById('dynamic-nav');
            let html = '';
            
            navigation.forEach(item => {
                html += `
                    <div class="nav-demo-item" data-page="${item.page}" onclick="simulateNavigation('${item.page}', '${item.title}')">
                        <i class="bi ${item.icon || 'bi-circle'}"></i> ${item.title}
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        function loadNavigationAPI() {
            fetch('<?= base_url('api/navigation') ?>')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('api-response').textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    document.getElementById('api-response').textContent = 'Error: ' + error.message;
                });
        }
        
        function logDebug(message) {
            if (debugMode) {
                const debugOutput = document.getElementById('debug-output');
                const timestamp = new Date().toLocaleTimeString();
                debugOutput.innerHTML += `[${timestamp}] ${message}\n`;
                debugOutput.scrollTop = debugOutput.scrollHeight;
            }
        }
        
        function showSuccess(message) {
            // Simple alert - in real app would use notification system
            alert('✅ ' + message);
        }
        
        function showError(message) {
            alert('❌ ' + message);
        }
        
        function showWarning(message) {
            alert('⚠️ ' + message);
        }
    </script>
</body>
</html>
