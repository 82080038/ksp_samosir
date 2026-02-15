/**
 * KSP Enhanced Bootstrap Components
 * Applying Bootstrap 5.3 and jQuery knowledge for superior UI
 */

(function() {
    'use strict';
    
    // Extend KSP framework with enhanced Bootstrap components
    if (typeof window.KSP === 'undefined') {
        window.KSP = {};
    }
    
    // Enhanced Bootstrap Components Manager
    KSP.EnhancedComponents = {
        // Configuration
        config: {
            tooltipDelay: { show: 500, hide: 100 },
            popoverTrigger: 'hover focus',
            toastAnimation: true,
            modalBackdrop: true,
            enableAnimations: true,
            animationDuration: 300
        },
        
        // Initialize all enhanced components
        init: function() {
            console.log('Initializing Enhanced Bootstrap Components');
            
            this.initTooltips();
            this.initPopovers();
            this.initEnhancedModals();
            this.initEnhancedAlerts();
            this.initEnhancedCards();
            this.initEnhancedButtons();
            this.initEnhancedForms();
            this.initEnhancedTables();
            this.initEnhancedNavigation();
            
            console.log('Enhanced Bootstrap Components initialized');
        },
        
        // Initialize Tooltips with enhanced features
        initTooltips: function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    delay: KSP.EnhancedComponents.config.tooltipDelay,
                    animation: KSP.EnhancedComponents.config.enableAnimations,
                    customClass: 'ksp-tooltip'
                });
            });
            
            // Auto-initialize dynamic tooltips
            $(document).on('DOMNodeInserted', function(e) {
                $(e.target).find('[data-bs-toggle="tooltip"]').each(function() {
                    new bootstrap.Tooltip(this, {
                        delay: KSP.EnhancedComponents.config.tooltipDelay,
                        animation: KSP.EnhancedComponents.config.enableAnimations
                    });
                });
            });
        },
        
        // Initialize Popovers with enhanced features
        initPopovers: function() {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl, {
                    trigger: KSP.EnhancedComponents.config.popoverTrigger,
                    animation: KSP.EnhancedComponents.config.enableAnimations,
                    customClass: 'ksp-popover'
                });
            });
        },
        
        // Enhanced Modals with additional features
        initEnhancedModals: function() {
            // Add enhanced modal classes
            $('.modal').each(function() {
                var $modal = $(this);
                
                // Add size classes based on content
                if (!$modal.hasClass('modal-sm') && !$modal.hasClass('modal-lg') && !$modal.hasClass('modal-xl')) {
                    $modal.find('.modal-dialog').addClass('modal-dialog-centered');
                }
                
                // Add backdrop animation
                $modal.on('show.bs.modal', function() {
                    $('body').addClass('modal-open-enhanced');
                });
                
                $modal.on('hidden.bs.modal', function() {
                    $('body').removeClass('modal-open-enhanced');
                });
            });
            
            // Enhanced modal close on escape
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('.modal.show').length > 0) {
                    $('.modal.show').last().modal('hide');
                }
            });
        },
        
        // Enhanced Alerts with auto-dismiss and animations
        initEnhancedAlerts: function() {
            // Auto-dismiss alerts
            $('.alert[data-auto-dismiss]').each(function() {
                var $alert = $(this);
                var delay = parseInt($alert.data('auto-dismiss')) || 5000;
                
                setTimeout(function() {
                    $alert.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, delay);
            });
            
            // Enhanced alert close animations
            $('.alert .btn-close').on('click', function() {
                var $alert = $(this).closest('.alert');
                $alert.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        },
        
        // Enhanced Cards with hover effects and interactions
        initEnhancedCards: function() {
            $('.card[data-enhanced]').each(function() {
                var $card = $(this);
                
                // Add hover effect
                $card.addClass('ksp-card-enhanced');
                
                // Add click-to-expand if specified
                if ($card.data('expandable')) {
                    $card.addClass('card-expandable').css('cursor', 'pointer');
                    $card.on('click', function() {
                        $(this).toggleClass('card-expanded');
                    });
                }
                
                // Add loading state
                if ($card.data('loading')) {
                    $card.append('<div class="card-loading-overlay"><div class="spinner-border text-primary" role="status"></div></div>');
                }
            });
        },
        
        // Enhanced Buttons with states and animations
        initEnhancedButtons: function() {
            // Loading state for buttons
            $('[data-loading-text]').on('click', function() {
                var $btn = $(this);
                var originalText = $btn.html();
                var loadingText = $btn.data('loading-text');
                
                $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>' + loadingText)
                   .prop('disabled', true)
                   .addClass('loading');
                
                // Simulate loading completion (remove this in production)
                setTimeout(function() {
                    $btn.html(originalText)
                       .prop('disabled', false)
                       .removeClass('loading');
                }, 2000);
            });
            
            // Button groups with enhanced behavior
            $('.btn-group[data-toggle="buttons"]').each(function() {
                $(this).find('.btn').on('click', function() {
                    $(this).siblings().removeClass('active');
                    $(this).addClass('active');
                });
            });
        },
        
        // Enhanced Forms with validation and floating labels
        initEnhancedForms: function() {
            // Auto-floating labels
            $('.form-floating input, .form-floating textarea').each(function() {
                var $input = $(this);
                var $label = $input.siblings('label');
                
                if ($input.val()) {
                    $label.addClass('focused');
                }
                
                $input.on('focus', function() {
                    $label.addClass('focused');
                }).on('blur', function() {
                    if (!$(this).val()) {
                        $label.removeClass('focused');
                    }
                });
            });
            
            // Enhanced form validation
            $('form[data-enhanced-validation]').each(function() {
                var $form = $(this);
                
                $form.on('submit', function(e) {
                    var isValid = true;
                    
                    $form.find('input[required], select[required], textarea[required]').each(function() {
                        var $field = $(this);
                        var $formGroup = $field.closest('.form-group, .mb-3');
                        
                        if (!$field.val()) {
                            $formGroup.addClass('has-error');
                            $field.addClass('is-invalid');
                            isValid = false;
                        } else {
                            $formGroup.removeClass('has-error');
                            $field.removeClass('is-invalid').addClass('is-valid');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        KSP.NotificationSystem.error('Mohon lengkapi semua field yang wajib diisi');
                    }
                });
            });
        },
        
        // Enhanced Tables with responsive features
        initEnhancedTables: function() {
            $('.table[data-enhanced]').each(function() {
                var $table = $(this);
                
                // Add responsive wrapper if not exists
                if (!$table.parent().hasClass('table-responsive')) {
                    $table.wrap('<div class="table-responsive"></div>');
                }
                
                // Add hover effects
                $table.addClass('table-hover table-striped');
                
                // Add row selection
                if ($table.data('selectable')) {
                    $table.find('tbody tr').on('click', function() {
                        $(this).toggleClass('table-selected');
                    });
                }
                
                // Add sorting indicators
                $table.find('th[data-sortable]').on('click', function() {
                    var $th = $(this);
                    var sortClass = $th.hasClass('sort-asc') ? 'sort-desc' : 'sort-asc';
                    
                    $table.find('th').removeClass('sort-asc sort-desc');
                    $th.addClass(sortClass);
                });
            });
        },
        
        // Enhanced Navigation with active states
        initEnhancedNavigation: function() {
            // Update active navigation based on current page
            var currentPath = window.location.pathname;
            var currentPage = currentPath.split('/').pop().replace('.php', '') || 'dashboard';
            
            $('.navbar-nav .nav-link, .sidebar .nav-link').each(function() {
                var $link = $(this);
                var href = $link.attr('href');
                
                if (href && href.includes(currentPage)) {
                    $link.addClass('active');
                }
            });
            
            // Smooth scroll for anchor links
            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                
                if ($(target).length) {
                    $('html, body').animate({
                        scrollTop: $(target).offset().top - 70
                    }, 500);
                }
            });
        },
        
        // Utility Methods
        showLoading: function(element, message) {
            var $element = $(element);
            var loadingHTML = '<div class="loading-overlay">' +
                              '<div class="spinner-border text-primary" role="status">' +
                              '<span class="visually-hidden">Loading...</span>' +
                              '</div>' +
                              (message ? '<div class="mt-2">' + message + '</div>' : '') +
                              '</div>';
            
            $element.append(loadingHTML);
        },
        
        hideLoading: function(element) {
            $(element).find('.loading-overlay').fadeOut(300, function() {
                $(this).remove();
            });
        },
        
        // Enhanced confirmation dialog
        confirm: function(message, callback, options) {
            options = $.extend({
                title: 'Konfirmasi',
                confirmText: 'Ya',
                cancelText: 'Batal',
                confirmClass: 'btn-primary',
                cancelClass: 'btn-secondary'
            }, options);
            
            var modalHTML = `
                <div class="modal fade" id="confirmModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${options.title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>${message}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn ${options.cancelClass}" data-bs-dismiss="modal">${options.cancelText}</button>
                                <button type="button" class="btn ${options.confirmClass}" id="confirmBtn">${options.confirmText}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing confirm modal
            $('#confirmModal').remove();
            
            // Add new modal
            $('body').append(modalHTML);
            
            var $modal = $('#confirmModal');
            var modal = new bootstrap.Modal($modal[0]);
            
            $modal.find('#confirmBtn').on('click', function() {
                modal.hide();
                if (typeof callback === 'function') {
                    callback();
                }
            });
            
            modal.show();
        }
    };
    
    // Auto-initialize when DOM is ready
    $(document).ready(function() {
        if (window.KSP && window.KSP.EnhancedComponents) {
            KSP.EnhancedComponents.init();
        }
    });
    
    console.log('KSP Enhanced Components loaded');
})();
