/**
 * KSP Dynamic Page Title Manager
 * 
 * Sistem global untuk sinkronisasi judul halaman dengan navigation links
 * Mendukung navigation links statis (HTML) dan dinamis (database)
 * 
 * @author KSP Samosir Development Team
 * @version 1.0.0
 */

window.KSP = window.KSP || {};

window.KSP.PageTitleManager = {
    
    // Cache untuk navigation links
    navigationCache: null,
    
    // Cache untuk current page info
    currentPageInfo: null,
    
    // Konfigurasi
    config: {
        navigationSelector: '.sidebar .nav-link[data-page]',
        titleSelector: '.page-title',
        headerSelector: '#page-header .page-title',
        autoSync: true,
        debugMode: false
    },
    
    /**
     * Inisialisasi Page Title Manager
     */
    init: function(config = {}) {
        this.config = { ...this.config, ...config };
        this.log('PageTitleManager initializing...');
        
        // Build navigation cache
        this.buildNavigationCache();
        
        // Auto-sync current page
        if (this.config.autoSync) {
            this.syncCurrentPage();
        }
        
        // Setup observer untuk navigation changes
        this.setupNavigationObserver();
        
        // Setup auto-sync untuk navigation clicks
        this.setupNavigationClickHandler();
        
        this.log('PageTitleManager initialized successfully');
    },
    
    /**
     * Build cache dari navigation links
     */
    buildNavigationCache: function() {
        this.navigationCache = {};
        
        // Scrape navigation links dari DOM
        const navLinks = document.querySelectorAll(this.config.navigationSelector);
        
        navLinks.forEach(link => {
            const page = link.getAttribute('data-page');
            const text = this.extractTextContent(link);
            
            if (page && text) {
                this.navigationCache[page] = {
                    text: text.trim(),
                    element: link,
                    href: link.href,
                    page: page
                };
                
                this.log(`Cached navigation: ${page} -> "${text.trim()}"`);
            }
        });
        
        // Jika navigation dinamis dari API, load di sini
        this.loadDynamicNavigation();
    },
    
    /**
     * Load navigation dari database/API (jika ada)
     */
    loadDynamicNavigation: function() {
        // Cek apakah ada endpoint untuk navigation data
        if (typeof base_url !== 'undefined') {
            fetch(`${base_url}api/navigation`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.navigation) {
                        this.processDynamicNavigation(data.navigation);
                    }
                })
                .catch(error => {
                    this.log('Failed to load dynamic navigation:', error);
                });
        }
    },
    
    /**
     * Process navigation data dari database
     */
    processDynamicNavigation: function(navigationData) {
        navigationData.forEach(item => {
            if (item.page && item.title) {
                this.navigationCache[item.page] = {
                    text: item.title,
                    element: null, // Tidak ada DOM element untuk dynamic nav
                    href: item.url || '#',
                    page: item.page,
                    isDynamic: true
                };
                
                this.log(`Cached dynamic navigation: ${item.page} -> "${item.title}"`);
            }
        });
        
        // Re-sync current page setelah load dynamic navigation
        this.syncCurrentPage();
    },
    
    /**
     * Sinkronkan judul halaman saat ini
     */
    syncCurrentPage: function() {
        const currentPage = this.getCurrentPage();
        
        if (!currentPage) {
            this.log('No current page detected');
            return;
        }
        
        const navInfo = this.getNavigationInfo(currentPage);
        
        if (navInfo) {
            this.updatePageTitle(navInfo.text, currentPage);
            this.setActiveNavigation(currentPage);
        } else {
            this.log(`No navigation info found for page: ${currentPage}`);
        }
    },
    
    /**
     * Get current page dari berbagai sumber
     */
    getCurrentPage: function() {
        // Priority 1: Dari main-content data-page attribute
        const mainContent = document.querySelector('#main-content');
        if (mainContent) {
            const page = mainContent.getAttribute('data-page');
            if (page) return page;
        }
        
        // Priority 2: Dari URL
        const urlPath = window.location.pathname;
        const segments = urlPath.split('/').filter(s => s);
        if (segments.length > 0) {
            return segments[segments.length - 1];
        }
        
        // Priority 3: Dari body class
        const bodyPage = document.body.getAttribute('data-page');
        if (bodyPage) return bodyPage;
        
        return null;
    },
    
    /**
     * Get navigation info untuk page tertentu
     */
    getNavigationInfo: function(page) {
        // Safety check
        if (!page || !this.navigationCache) {
            return null;
        }
        
        // Direct match
        if (this.navigationCache[page]) {
            return this.navigationCache[page];
        }
        
        // Fuzzy matching untuk variasi page names
        const variations = this.getPageVariations(page);
        for (const variation of variations) {
            if (this.navigationCache[variation]) {
                return this.navigationCache[variation];
            }
        }
        
        return null;
    },
    
    /**
     * Generate variasi page names untuk matching
     */
    getPageVariations: function(page) {
        const variations = [page];
        
        // Remove file extension
        if (page.includes('.php')) {
            variations.push(page.replace('.php', ''));
        }
        
        // Remove common prefixes
        const prefixes = ['app-', 'admin-', 'user-', 'member-'];
        prefixes.forEach(prefix => {
            if (page.startsWith(prefix)) {
                variations.push(page.substring(prefix.length));
            }
        });
        
        // Convert underscores to dashes and vice versa
        variations.push(page.replace(/_/g, '-'));
        variations.push(page.replace(/-/g, '_'));
        
        // Remove duplicates
        return [...new Set(variations)];
    },
    
    /**
     * Update judul halaman di multiple locations
     */
    updatePageTitle: function(title, page) {
        // Update page title element
        const titleElements = document.querySelectorAll(this.config.titleSelector);
        titleElements.forEach(element => {
            element.textContent = title;
        });
        
        // Update browser title
        document.title = `${title} - KSP Samosir`;
        
        // Update meta title jika ada
        const metaTitle = document.querySelector('meta[property="og:title"]');
        if (metaTitle) {
            metaTitle.content = title;
        }
        
        // Trigger custom event
        this.triggerPageTitleUpdate(title, page);
        
        this.log(`Updated page title to: "${title}" for page: ${page}`);
    },
    
    /**
     * Set active navigation state
     */
    setActiveNavigation: function(page) {
        // Remove all active classes
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Add active class to current page navigation
        const navInfo = this.getNavigationInfo(page);
        if (navInfo && navInfo.element) {
            navInfo.element.classList.add('active');
        }
    },
    
    /**
     * Setup observer untuk navigation changes
     */
    setupNavigationObserver: function() {
        // Observer untuk DOM changes
        const observer = new MutationObserver((mutations) => {
            let shouldRebuild = false;
            
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) { // Element node
                            if (node.matches && node.matches(this.config.navigationSelector) ||
                                node.querySelector && node.querySelector(this.config.navigationSelector)) {
                                shouldRebuild = true;
                            }
                        }
                    });
                }
            });
            
            if (shouldRebuild) {
                this.log('Navigation changed, rebuilding cache...');
                setTimeout(() => {
                    this.buildNavigationCache();
                    this.syncCurrentPage();
                }, 100);
            }
        });
        
        // Start observing
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            observer.observe(sidebar, {
                childList: true,
                subtree: true
            });
        }
    },
    
    /**
     * Setup auto-sync untuk navigation clicks
     */
    setupNavigationClickHandler: function() {
        document.addEventListener('click', (e) => {
            const navLink = e.target.closest(this.config.navigationSelector);
            if (navLink) {
                const page = navLink.getAttribute('data-page');
                if (page) {
                    // Preload navigation info untuk halaman berikutnya
                    this.getNavigationInfo(page);
                }
            }
        });
    },
    
    /**
     * Extract text content dari navigation link
     */
    extractTextContent: function(element) {
        // Clone element untuk avoid modifying original
        const clone = element.cloneNode(true);
        
        // Remove icon elements
        clone.querySelectorAll('i, .bi, svg').forEach(icon => {
            icon.remove();
        });
        
        return clone.textContent || '';
    },
    
    /**
     * Trigger custom event untuk page title update
     */
    triggerPageTitleUpdate: function(title, page) {
        const event = new CustomEvent('pageTitleUpdate', {
            detail: {
                title: title,
                page: page,
                timestamp: Date.now()
            }
        });
        
        document.dispatchEvent(event);
    },
    
    /**
     * Public method untuk manual sync
     */
    sync: function() {
        this.syncCurrentPage();
    },
    
    /**
     * Public method untuk add navigation manual
     */
    addNavigation: function(page, title, url = '#') {
        this.navigationCache[page] = {
            text: title,
            element: null,
            href: url,
            page: page,
            isManual: true
        };
        
        this.log(`Added manual navigation: ${page} -> "${title}"`);
    },
    
    /**
     * Get semua navigation cache
     */
    getNavigationCache: function() {
        return { ...this.navigationCache };
    },
    
    /**
     * Logging method
     */
    log: function(...args) {
        if (this.config.debugMode && console.log) {
            console.log('[PageTitleManager]', ...args);
        }
    }
};

// Auto-initialize saat DOM ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.KSP !== 'undefined' && window.KSP.PageTitleManager) {
        // Initialize dengan debug mode di development
        const isDebug = window.location.hostname === 'localhost' || 
                       window.location.hostname.includes('dev') ||
                       window.location.search.includes('debug=true');
        
        window.KSP.PageTitleManager.init({
            debugMode: isDebug
        });
    }
});

// Override updatePageTitle global function untuk integrasi
window.updatePageTitle = function(title, page) {
    if (typeof window.KSP !== 'undefined' && window.KSP.PageTitleManager) {
        // Gunakan navigation info jika ada
        const navInfo = window.KSP.PageTitleManager.getNavigationInfo(page);
        if (navInfo) {
            title = navInfo.text; // Override dengan navigation text
        }
        
        window.KSP.PageTitleManager.updatePageTitle(title, page);
    } else {
        // Fallback ke original behavior
        console.log('updatePageTitle called:', title, page);
        if (typeof $ !== 'undefined') {
            $('.page-title').text(title).attr('data-page', page || title.toLowerCase());
        }
    }
};
