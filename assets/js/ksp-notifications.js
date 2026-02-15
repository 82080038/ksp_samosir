/**
 * KSP Notification System - Toast & Alert Components
 * Bootstrap-based notification system with responsive design
 */

(function() {
    'use strict';
    
    // Extend KSP framework with notification system
    if (typeof window.KSP === 'undefined') {
        window.KSP = {};
    }
    
    // Enhanced Notification System
    KSP.NotificationSystem = {
        // Configuration
        config: {
            toastContainer: '#toast-container',
            toastPosition: 'top-end', // top-start, top-center, top-end, etc.
            toastDelay: 5000,
            maxToasts: 5,
            enableAnimations: true,
            enableSounds: false,
            soundPath: '/assets/sounds/'
        },
        
        // Toast queue
        toastQueue: [],
        activeToasts: 0,
        
        // Initialize notification system
        init: function(options) {
            this.config = $.extend({}, this.config, options);
            this.createToastContainer();
            this.setupGlobalHandlers();
            console.log('KSP Notification System initialized');
        },
        
        // Create toast container
        createToastContainer: function() {
            if (!$(this.config.toastContainer).length) {
                var positionClass = this.getPositionClass();
                var containerHTML = `
                    <div id="toast-container" class="toast-container position-fixed ${positionClass}" 
                         style="z-index: 1055; top: 20px; right: 20px;">
                    </div>
                `;
                $('body').append(containerHTML);
            }
        },
        
        // Get position class for toast container
        getPositionClass: function() {
            var positions = {
                'top-start': 'top-0 start-0',
                'top-center': 'top-0 start-50 translate-middle-x',
                'top-end': 'top-0 end-0',
                'middle-start': 'top-50 start-0 translate-middle-y',
                'middle-center': 'top-50 start-50 translate-middle',
                'middle-end': 'top-50 end-0 translate-middle-y',
                'bottom-start': 'bottom-0 start-0',
                'bottom-center': 'bottom-0 start-50 translate-middle-x',
                'bottom-end': 'bottom-0 end-0'
            };
            return positions[this.config.toastPosition] || positions['top-end'];
        },
        
        // Show toast notification
        showToast: function(message, type, options) {
            var self = this;
            options = $.extend({
                title: '',
                delay: this.config.toastDelay,
                autohide: true,
                closeButton: true,
                icon: this.getIcon(type)
            }, options);
            
            // Check if we've reached max toasts
            if (this.activeToasts >= this.config.maxToasts) {
                // Remove oldest toast
                var $oldestToast = $(this.config.toastContainer + ' .toast:first');
                if ($oldestToast.length) {
                    this.removeToast($oldestToast);
                }
            }
            
            var toastId = 'toast-' + Date.now();
            var toastHTML = this.createToastHTML(toastId, message, type, options);
            
            // Add to container
            $(this.config.toastContainer).append(toastHTML);
            var $toast = $('#' + toastId);
            
            // Initialize Bootstrap toast
            var bsToast = new bootstrap.Toast($toast[0], {
                autohide: options.autohide,
                delay: options.delay
            });
            
            // Show toast
            bsToast.show();
            this.activeToasts++;
            
            // Play sound if enabled
            if (this.config.enableSounds) {
                this.playSound(type);
            }
            
            // Handle toast events
            $toast.on('hidden.bs.toast', function() {
                self.removeToast($(this));
            });
            
            // Auto-remove if not auto-hiding
            if (!options.autohide) {
                setTimeout(function() {
                    self.removeToast($toast);
                }, options.delay + 1000);
            }
            
            return toastId;
        },
        
        // Create toast HTML
        createToastHTML: function(id, message, type, options) {
            var bgClass = this.getBackgroundClass(type);
            var textClass = this.getTextClass(type);
            var iconHtml = options.icon ? `<i class="bi ${options.icon} me-2"></i>` : '';
            var titleHtml = options.title ? `<strong class="me-auto">${options.title}</strong>` : '';
            var closeBtn = options.closeButton ? 
                '<button type="button" class="btn-close" data-bs-dismiss="toast"></button>' : '';
            
            return `
                <div id="${id}" class="toast align-items-center ${bgClass} ${textClass} border-0" 
                     role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${iconHtml}${message}
                        </div>
                        ${titleHtml}
                        ${closeBtn}
                    </div>
                </div>
            `;
        },
        
        // Show alert message
        showAlert: function(message, type, container, options) {
            options = $.extend({
                dismissible: true,
                icon: this.getIcon(type),
                timeout: 0 // 0 = no auto-hide
            }, options);
            
            var alertId = 'alert-' + Date.now();
            var alertHTML = this.createAlertHTML(alertId, message, type, options);
            
            var $container = container ? $(container) : $('.main-content').first();
            $container.prepend(alertHTML);
            
            var $alert = $('#' + alertId);
            
            // Auto-hide if timeout is set
            if (options.timeout > 0) {
                setTimeout(function() {
                    this.dismissAlert($alert);
                }.bind(this), options.timeout);
            }
            
            return alertId;
        },
        
        // Create alert HTML
        createAlertHTML: function(id, message, type, options) {
            var alertClass = this.getAlertClass(type);
            var iconHtml = options.icon ? `<i class="bi ${options.icon} me-2"></i>` : '';
            var dismissBtn = options.dismissible ? 
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' : '';
            
            return `
                <div id="${id}" class="alert ${alertClass} alert-dismissible fade show d-flex align-items-center" 
                     role="alert">
                    ${iconHtml}
                    <div class="flex-grow-1">${message}</div>
                    ${dismissBtn}
                </div>
            `;
        },
        
        // Show success message (toast)
        success: function(message, title) {
            return this.showToast(message, 'success', { title: title });
        },
        
        // Show error message (alert)
        error: function(message, container) {
            return this.showAlert(message, 'danger', container, { timeout: 10000 });
        },
        
        // Show warning message (alert)
        warning: function(message, container) {
            return this.showAlert(message, 'warning', container, { timeout: 8000 });
        },
        
        // Show info message (toast)
        info: function(message, title) {
            return this.showToast(message, 'info', { title: title });
        },
        
        // Show loading toast
        loading: function(message) {
            return this.showToast(message || 'Memproses...', 'info', {
                title: 'Memuat',
                autohide: false,
                icon: 'bi-arrow-clockwise spin'
            });
        },
        
        // Update existing toast
        updateToast: function(toastId, message, type) {
            var $toast = $('#' + toastId);
            if ($toast.length) {
                var $body = $toast.find('.toast-body');
                var icon = this.getIcon(type);
                $body.html(`<i class="bi ${icon} me-2"></i>${message}`);
                
                // Update classes
                $toast.removeClass('bg-success bg-danger bg-warning bg-info text-white text-dark')
                      .addClass(this.getBackgroundClass(type) + ' ' + this.getTextClass(type));
            }
        },
        
        // Remove toast
        removeToast: function($toast) {
            $toast.remove();
            this.activeToasts--;
        },
        
        // Dismiss alert
        dismissAlert: function($alert) {
            $alert.fadeOut(300, function() {
                $(this).remove();
            });
        },
        
        // Clear all toasts
        clearAllToasts: function() {
            $(this.config.toastContainer + ' .toast').each(function() {
                var toast = bootstrap.Toast.getInstance(this);
                if (toast) {
                    toast.hide();
                } else {
                    $(this).remove();
                }
            });
            this.activeToasts = 0;
        },
        
        // Get background class for type
        getBackgroundClass: function(type) {
            var classes = {
                'success': 'bg-success',
                'error': 'bg-danger',
                'danger': 'bg-danger',
                'warning': 'bg-warning',
                'info': 'bg-info',
                'primary': 'bg-primary',
                'secondary': 'bg-secondary'
            };
            return classes[type] || 'bg-info';
        },
        
        // Get text class for type
        getTextClass: function(type) {
            var lightText = ['success', 'danger', 'primary', 'info', 'warning'];
            return lightText.includes(type) ? 'text-white' : 'text-dark';
        },
        
        // Get alert class for type
        getAlertClass: function(type) {
            var classes = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'danger': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info',
                'primary': 'alert-primary',
                'secondary': 'alert-secondary'
            };
            return classes[type] || 'alert-info';
        },
        
        // Get icon for type
        getIcon: function(type) {
            var icons = {
                'success': 'bi-check-circle-fill',
                'error': 'bi-exclamation-triangle-fill',
                'danger': 'bi-exclamation-triangle-fill',
                'warning': 'bi-exclamation-triangle-fill',
                'info': 'bi-info-circle-fill',
                'primary': 'bi-info-circle-fill',
                'secondary': 'bi-info-circle-fill',
                'loading': 'bi-arrow-clockwise spin'
            };
            return icons[type] || 'bi-info-circle-fill';
        },
        
        // Play notification sound
        playSound: function(type) {
            if (!this.config.enableSounds) return;
            
            var sounds = {
                'success': 'success.mp3',
                'error': 'error.mp3',
                'warning': 'warning.mp3',
                'info': 'info.mp3'
            };
            
            var soundFile = sounds[type] || sounds['info'];
            var audio = new Audio(this.config.soundPath + soundFile);
            audio.volume = 0.3;
            audio.play().catch(function(e) {
                console.log('Could not play sound:', e);
            });
        },
        
        // Setup global handlers
        setupGlobalHandlers: function() {
            var self = this;
            
            // Handle keyboard shortcuts
            $(document).on('keydown', function(e) {
                // Esc to dismiss all toasts
                if (e.key === 'Escape' && e.ctrlKey) {
                    self.clearAllToasts();
                }
            });
            
            // Handle page visibility change
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    // Pause auto-hide when page is not visible
                    self.pauseAutoHide();
                } else {
                    // Resume auto-hide when page is visible
                    self.resumeAutoHide();
                }
            });
        },
        
        // Pause auto-hide for all toasts
        pauseAutoHide: function() {
            $(this.config.toastContainer + ' .toast').each(function() {
                var toast = bootstrap.Toast.getInstance(this);
                if (toast) {
                    toast._config.autohide = false;
                }
            });
        },
        
        // Resume auto-hide for all toasts
        resumeAutoHide: function() {
            var self = this;
            $(this.config.toastContainer + ' .toast').each(function() {
                var toast = bootstrap.Toast.getInstance(this);
                if (toast) {
                    toast._config.autohide = true;
                    // Restart timer
                    setTimeout(function() {
                        toast.hide();
                    }, toast._config.delay);
                }
            });
        }
    };
    
    // Auto-initialize when DOM is ready
    $(document).ready(function() {
        if (window.KSP && window.KSP.NotificationSystem) {
            KSP.NotificationSystem.init();
        }
    });
    
    // Global shortcuts for backward compatibility
    window.showNotification = function(message, type, title) {
        if (type === 'error' || type === 'danger' || type === 'warning') {
            return KSP.NotificationSystem.showAlert(message, type);
        } else {
            return KSP.NotificationSystem.showToast(message, type, { title: title });
        }
    };
    
    window.showToast = function(message, type, options) {
        return KSP.NotificationSystem.showToast(message, type, options);
    };
    
    window.showAlert = function(message, type, container, options) {
        return KSP.NotificationSystem.showAlert(message, type, container, options);
    };
    
    console.log('KSP Notification System loaded');
})();
