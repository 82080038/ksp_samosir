/**
 * KSP Responsive Navigation Framework
 * Bootstrap-based responsive navigation with mobile optimization
 */

(function() {
    'use strict';
    
    // Extend KSP framework with responsive navigation
    if (typeof window.KSP === 'undefined') {
        window.KSP = {};
    }
    
    // Responsive Navigation Manager
    KSP.ResponsiveNav = {
        // Configuration
        config: {
            sidebarSelector: '#sidebarMenu',
            contentSelector: '.main-content',
            navbarSelector: '.navbar',
            offcanvasSelector: '#mobileNavOffcanvas',
            toggleSelector: '[data-nav-toggle]',
            breakpoint: 992, // lg breakpoint
            collapsedClass: 'sidebar-collapsed',
            mobileClass: 'mobile-nav',
            desktopClass: 'desktop-nav'
        },
        
        // State tracking
        state: {
            isMobile: false,
            isCollapsed: false,
            sidebarWidth: 250,
            currentBreakpoint: 'desktop'
        },
        
        // Initialize responsive navigation
        init: function() {
            console.log('Initializing Responsive Navigation');
            
            this.setupEventHandlers();
            this.checkBreakpoint();
            this.setupResponsiveHandlers();
            this.initializeComponents();
            
            // Check on resize
            var resizeTimer;
            $(window).on('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    KSP.ResponsiveNav.checkBreakpoint();
                }, 250);
            });
        },
        
        // Setup event handlers
        setupEventHandlers: function() {
            var self = this;
            
            // Sidebar toggle buttons
            $(document).on('click', this.config.toggleSelector, function(e) {
                e.preventDefault();
                self.toggleSidebar();
            });
            
            // Offcanvas close handlers
            $(document).on('click', '[data-bs-dismiss="offcanvas"]', function() {
                self.closeMobileNav();
            });
            
            // Escape key to close mobile nav
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.state.isMobile) {
                    self.closeMobileNav();
                }
            });
            
            // Click outside to close mobile nav
            $(document).on('click', function(e) {
                if (self.state.isMobile && 
                    $(e.target).closest(self.config.offcanvasSelector).length === 0 &&
                    $(e.target).closest(self.config.toggleSelector).length === 0) {
                    self.closeMobileNav();
                }
            });
        },
        
        // Check current breakpoint and adjust layout
        checkBreakpoint: function() {
            var windowWidth = $(window).width();
            var wasMobile = this.state.isMobile;
            
            if (windowWidth < this.config.breakpoint) {
                this.state.isMobile = true;
                this.state.currentBreakpoint = 'mobile';
                this.enableMobileLayout();
            } else {
                this.state.isMobile = false;
                this.state.currentBreakpoint = 'desktop';
                this.enableDesktopLayout();
            }
            
            // Log breakpoint changes
            if (wasMobile !== this.state.isMobile) {
                console.log('Breakpoint changed to:', this.state.currentBreakpoint);
            }
        },
        
        // Enable mobile layout
        enableMobileLayout: function() {
            var $body = $('body');
            var $sidebar = $(this.config.sidebarSelector);
            var $content = $(this.config.contentSelector);
            
            // Add mobile class
            $body.addClass(this.config.mobileClass).removeClass(this.config.desktopClass);
            
            // Hide desktop sidebar
            $sidebar.hide();
            
            // Remove desktop margins
            $content.css('margin-left', '');
            
            // Show mobile toggle button
            $('[data-nav-toggle="mobile"]').show();
            $('[data-nav-toggle="desktop"]').hide();
            
            // Initialize offcanvas if not exists
            this.ensureOffcanvasExists();
        },
        
        // Enable desktop layout
        enableDesktopLayout: function() {
            var $body = $('body');
            var $sidebar = $(this.config.sidebarSelector);
            var $content = $(this.config.contentSelector);
            
            // Add desktop class
            $body.addClass(this.config.desktopClass).removeClass(this.config.mobileClass);
            
            // Show desktop sidebar
            $sidebar.show();
            
            // Apply desktop margins if not collapsed
            if (!this.state.isCollapsed) {
                $content.css('margin-left', this.state.sidebarWidth + 'px');
            }
            
            // Show desktop toggle button
            $('[data-nav-toggle="desktop"]').show();
            $('[data-nav-toggle="mobile"]').hide();
            
            // Close offcanvas if open
            this.closeMobileNav();
        },
        
        // Ensure offcanvas exists for mobile
        ensureOffcanvasExists: function() {
            if (!$(this.config.offcanvasSelector).length) {
                this.createMobileOffcanvas();
            }
        },
        
        // Create mobile offcanvas navigation
        createMobileOffcanvas: function() {
            var $sidebar = $(this.config.sidebarSelector);
            var sidebarContent = $sidebar.html();
            
            var offcanvasHTML = `
                <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNavOffcanvas" 
                     aria-labelledby="mobileNavLabel" style="width: 280px;">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="mobileNavLabel">
                            <i class="bi bi-list"></i> Menu Navigasi
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                    </div>
                    <div class="offcanvas-body p-0">
                        ${sidebarContent}
                    </div>
                </div>
            `;
            
            $('body').append(offcanvasHTML);
            
            // Update sidebar links in offcanvas
            $(this.config.offcanvasSelector + ' .nav-link').each(function() {
                var $link = $(this);
                $link.off('click').on('click', function(e) {
                    // Close offcanvas after navigation
                    var offcanvas = bootstrap.Offcanvas.getInstance('#mobileNavOffcanvas');
                    if (offcanvas) {
                        offcanvas.hide();
                    }
                });
            });
        },
        
        // Toggle sidebar (desktop/tablet)
        toggleSidebar: function() {
            if (this.state.isMobile) {
                this.openMobileNav();
            } else {
                this.toggleDesktopSidebar();
            }
        },
        
        // Toggle desktop sidebar
        toggleDesktopSidebar: function() {
            var $body = $('body');
            var $sidebar = $(this.config.sidebarSelector);
            var $content = $(this.config.contentSelector);
            
            this.state.isCollapsed = !this.state.isCollapsed;
            
            if (this.state.isCollapsed) {
                $body.addClass(this.config.collapsedClass);
                $sidebar.css('width', '70px');
                $content.css('margin-left', '70px');
                
                // Hide text, show only icons
                $sidebar.find('.nav-link span').hide();
                $sidebar.find('.sidebar-text').hide();
                $sidebar.find('.sidebar-brand-text').hide();
            } else {
                $body.removeClass(this.config.collapsedClass);
                $sidebar.css('width', '');
                $content.css('margin-left', this.state.sidebarWidth + 'px');
                
                // Show text
                $sidebar.find('.nav-link span').show();
                $sidebar.find('.sidebar-text').show();
                $sidebar.find('.sidebar-brand-text').show();
            }
            
            // Trigger resize event for charts and other components
            $(window).trigger('resize');
        },
        
        // Open mobile navigation
        openMobileNav: function() {
            var offcanvasElement = document.getElementById('mobileNavOffcanvas');
            if (offcanvasElement) {
                var offcanvas = new bootstrap.Offcanvas(offcanvasElement);
                offcanvas.show();
            }
        },
        
        // Close mobile navigation
        closeMobileNav: function() {
            var offcanvasElement = document.getElementById('mobileNavOffcanvas');
            if (offcanvasElement) {
                var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                if (offcanvas) {
                    offcanvas.hide();
                }
            }
        },
        
        // Setup responsive handlers
        setupResponsiveHandlers: function() {
            var self = this;
            
            // Handle dropdown menus in mobile
            $(document).on('click', '.mobile-nav .dropdown-toggle', function(e) {
                if (self.state.isMobile) {
                    e.preventDefault();
                    $(this).next('.dropdown-menu').slideToggle();
                }
            });
            
            // Handle active menu highlighting
            this.updateActiveMenu();
        },
        
        // Update active menu highlighting
        updateActiveMenu: function() {
            var currentPage = $('.page-title').data('page') || 'dashboard';
            
            // Update desktop sidebar
            $(this.config.sidebarSelector + ' .nav-link').removeClass('active');
            $(this.config.sidebarSelector + ' .nav-link[data-page="' + currentPage + '"]').addClass('active');
            
            // Update mobile offcanvas
            $(this.config.offcanvasSelector + ' .nav-link').removeClass('active');
            $(this.config.offcanvasSelector + ' .nav-link[data-page="' + currentPage + '"]').addClass('active');
        },
        
        // Initialize Bootstrap components
        initializeComponents: function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialize popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        },
        
        // Get current state
        getState: function() {
            return {
                isMobile: this.state.isMobile,
                isCollapsed: this.state.isCollapsed,
                breakpoint: this.state.currentBreakpoint,
                sidebarWidth: this.state.sidebarWidth
            };
        }
    };
    
    // Auto-initialize when DOM is ready
    $(document).ready(function() {
        if (window.KSP && window.KSP.ResponsiveNav) {
            KSP.ResponsiveNav.init();
        }
    });
    
    console.log('KSP Responsive Navigation loaded');
})();
