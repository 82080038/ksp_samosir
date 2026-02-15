/**
 * KSP Samosir - Simple Robust Framework
 * Minimal framework for dynamic sidenav and consistency
 */

// Wait for jQuery to be ready
(function() {
    'use strict';
    
    // Initialize when everything is ready
    function initKSP() {
        if (typeof $ === 'undefined') {
            setTimeout(initKSP, 100);
            return;
        }
        
        console.log('KSP Framework starting...');
        
        // Simple object structure
        window.KSP = {
            // Simple sidenav manager
            Sidenav: {
                updateActive: function() {
                    var currentPage = $('.page-title').data('page');
                    if (currentPage) {
                        $('.sidebar .nav-link').removeClass('active');
                        $('.sidebar .nav-link[data-page="' + currentPage + '"]').addClass('active');
                    }
                },
                
                // Future: load from database
                loadFromDatabase: function() {
                    // Placeholder for future database loading
                    console.log('Database sidenav loading ready for implementation');
                }
            },
            
            // Simple page manager
            Page: {
                updateTitle: function() {
                    var title = $('.page-title').text();
                    if (title && title.trim()) {
                        document.title = title.trim() + ' - KSP Samosir';
                        console.log('Page title updated:', title.trim());
                    }
                },
                
                // Enhanced title update with data attributes
                setTitle: function(title, page) {
                    if (typeof $ !== 'undefined') {
                        $('.page-title').text(title).attr('data-page', page || title.toLowerCase());
                        this.updateTitle();
                    }
                }
            },
            
            // Simple notification system
            Notification: {
                show: function(message, type) {
                    type = type || 'info';
                    var alertClass = 'alert-' + type;
                    var icon = this.getIcon(type);
                    
                    var html = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert" style="display:none;">' +
                               '<i class="bi ' + icon + ' me-2"></i>' + message +
                               '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                    
                    $('#page-header').after(html);
                    $('.alert:first').slideDown(300);
                    
                    setTimeout(function() {
                        $('.alert:first').fadeOut(300, function() { $(this).remove(); });
                    }, 5000);
                },
                
                getIcon: function(type) {
                    var icons = {
                        success: 'bi-check-circle',
                        error: 'bi-exclamation-triangle',
                        warning: 'bi-exclamation-triangle',
                        info: 'bi-info-circle'
                    };
                    return icons[type] || icons.info;
                }
            }
        };
        
        // Initialize components
        KSP.Sidenav.updateActive();
        KSP.Page.updateTitle();
        
        // Setup basic handlers
        $(document).on('click', '[data-action]', function(e) {
            e.preventDefault();
            var action = $(this).data('action');
            
            switch(action) {
                case 'refresh':
                    location.reload();
                    break;
                case 'print':
                    window.print();
                    break;
                case 'export':
                    var format = $(this).data('format') || 'excel';
                    window.open(window.location.pathname + '?export=' + format, '_blank');
                    break;
                default:
                    console.log('Action:', action);
            }
        });
        
        console.log('KSP Framework initialized successfully');
    }
    
    // Start initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKSP);
    } else {
        initKSP();
    }
})();

// Global helper functions
window.updatePageTitle = function(title, page) {
    console.log('updatePageTitle called with:', title, page);
    if (typeof $ !== 'undefined') {
        $('.page-title').text(title).attr('data-page', page || title.toLowerCase());
        console.log('Page title element updated');
    }
    if (window.KSP && window.KSP.Page && window.KSP.Page.updateTitle) {
        window.KSP.Page.updateTitle();
        console.log('KSP Page updateTitle called');
    }
    
    // Also update browser title directly as fallback
    if (title && title.trim()) {
        document.title = title.trim() + ' - KSP Samosir';
        console.log('Browser title updated directly');
    }
};

// Add init function for compatibility
window.KSP.init = function() {
    console.log('KSP Framework initialized');
    // Initialize core components if they exist
    if (window.KSP.NotificationSystem) {
        console.log('Initializing Notification System');
    }
    if (window.KSP.ResponsiveNav) {
        console.log('Initializing Responsive Navigation');
    }
    if (window.KSP.EnhancedComponents) {
        console.log('Initializing Enhanced Components');
    }
};

window.showNotification = function(message, type) {
    if (window.KSP && window.KSP.Notification) {
        window.KSP.Notification.show(message, type);
    } else {
        alert(message);
    }
};

console.log('KSP Simple Framework loaded');
