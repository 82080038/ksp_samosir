/**
 * KSP Samosir jQuery & Bootstrap Optimization Bundle
 * Minimal footprint with maximum performance
 */

(function(window, document) {
    'use strict';

    // Optimized jQuery Loader
    const jQueryLoader = {
        loaded: false,
        callbacks: [],
        
        load: function(callback) {
            if (this.loaded) {
                callback && callback();
                return;
            }
            
            this.callbacks.push(callback);
            
            if (this.callbacks.length === 1) {
                const script = document.createElement('script');
                script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js';
                script.async = true;
                script.onload = () => {
                    this.loaded = true;
                    this.callbacks.forEach(cb => cb());
                    this.callbacks = [];
                };
                document.head.appendChild(script);
            }
        }
    };

    // Optimized Bootstrap Loader
    const BootstrapLoader = {
        loaded: false,
        callbacks: [],
        
        load: function(callback) {
            if (this.loaded) {
                callback && callback();
                return;
            }
            
            this.callbacks.push(callback);
            
            if (this.callbacks.length === 1) {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js';
                script.async = true;
                script.onload = () => {
                    this.loaded = true;
                    this.callbacks.forEach(cb => cb());
                    this.callbacks = [];
                };
                document.head.appendChild(script);
            }
        }
    };

    // Optimized Modal Manager
    const ModalManager = {
        modals: new Map(),
        
        show: function(modalId, options = {}) {
            BootstrapLoader.load(() => {
                let modal = this.modals.get(modalId);
                if (!modal) {
                    const element = document.getElementById(modalId);
                    if (element) {
                        modal = new bootstrap.Modal(element, options);
                        this.modals.set(modalId, modal);
                    }
                }
                
                if (modal) {
                    modal.show();
                }
            });
        },
        
        hide: function(modalId) {
            const modal = this.modals.get(modalId);
            if (modal) {
                modal.hide();
            }
        },
        
        create: function(options) {
            const modalId = 'ksp-modal-' + Date.now();
            const modalHTML = this.generateModalHTML(options, modalId);
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            BootstrapLoader.load(() => {
                const element = document.getElementById(modalId);
                const modal = new bootstrap.Modal(element, options.bootstrap || {});
                this.modals.set(modalId, modal);
                modal.show();
            });
            
            return modalId;
        },
        
        generateModalHTML: function(options, modalId) {
            return `
                <div class="modal fade" id="${modalId}" tabindex="-1">
                    <div class="modal-dialog ${options.size || ''}">
                        <div class="modal-content">
                            ${options.title ? `
                            <div class="modal-header">
                                <h5 class="modal-title">${options.title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            ` : ''}
                            <div class="modal-body">${options.content || ''}</div>
                            ${options.footer ? `
                            <div class="modal-footer">${options.footer}</div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        }
    };

    // Optimized Tooltip Manager
    const TooltipManager = {
        initialized: false,
        
        init: function() {
            if (this.initialized) return;
            
            BootstrapLoader.load(() => {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    new bootstrap.Tooltip(el);
                });
                this.initialized = true;
            });
        },
        
        add: function(element) {
            BootstrapLoader.load(() => {
                new bootstrap.Tooltip(element);
            });
        }
    };

    // Optimized Form Handler
    const FormHandler = {
        init: function() {
            jQueryLoader.load(() => {
                const $ = window.jQuery;
                
                // Optimized form validation
                $('form[data-ksp-validate]').on('submit', function(e) {
                    const $form = $(this);
                    let isValid = true;
                    
                    $form.find('[required]').each(function() {
                        const $input = $(this);
                        if (!$input.val().trim()) {
                            $input.addClass('is-invalid');
                            isValid = false;
                        } else {
                            $input.removeClass('is-invalid');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
                
                // Clear validation on input
                $('form[data-ksp-validate] [required]').on('input', function() {
                    $(this).removeClass('is-invalid');
                });
            });
        }
    };

    // Optimized Data Table
    const DataTable = {
        init: function() {
            jQueryLoader.load(() => {
                const $ = window.jQuery;
                
                $('.table[data-ksp-datatable]').each(function() {
                    const $table = $(this);
                    const options = $table.data('ksp-datatable');
                    
                    // Simple pagination
                    const pageSize = options.pageSize || 10;
                    const $tbody = $table.find('tbody');
                    const $rows = $tbody.find('tr');
                    
                    if ($rows.length > pageSize) {
                        // Add pagination controls
                        const totalPages = Math.ceil($rows.length / pageSize);
                        let paginationHTML = '<nav><ul class="pagination">';
                        
                        for (let i = 1; i <= totalPages; i++) {
                            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                        }
                        
                        paginationHTML += '</ul></nav>';
                        $table.after(paginationHTML);
                        
                        // Show first page
                        $rows.hide().slice(0, pageSize).show();
                        
                        // Handle pagination clicks
                        $table.next('.pagination').on('click', 'a', function(e) {
                            e.preventDefault();
                            const page = parseInt($(this).data('page'));
                            const start = (page - 1) * pageSize;
                            const end = start + pageSize;
                            
                            $rows.hide();
                            $rows.slice(start, end).show();
                            
                            // Update active state
                            $(this).closest('.pagination').find('li').removeClass('active');
                            $(this).closest('li').addClass('active');
                        });
                    }
                });
            });
        }
    };

    // Optimized Alert System
    const AlertSystem = {
        show: function(message, type = 'info', duration = 5000) {
            const alertId = 'ksp-alert-' + Date.now();
            const alertHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" id="${alertId}" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Insert at the top of main content
            const mainContent = document.querySelector('main') || document.body;
            mainContent.insertAdjacentHTML('afterbegin', alertHTML);
            
            // Auto-dismiss
            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert) {
                    BootstrapLoader.load(() => {
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                        bsAlert.close();
                    });
                }
            }, duration);
        }
    };

    // Optimized Loading System
    const LoadingSystem = {
        show: function(target = document.body) {
            const loadingId = 'ksp-loading-' + Date.now();
            const loadingHTML = `
                <div id="${loadingId}" class="ksp-loading-overlay">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            target.style.position = 'relative';
            target.insertAdjacentHTML('beforeend', loadingHTML);
            
            return loadingId;
        },
        
        hide: function(loadingId) {
            const loading = document.getElementById(loadingId);
            if (loading) {
                loading.remove();
            }
        }
    };

    // Initialize everything when DOM is ready
    function initOptimizedSystem() {
        // Auto-initialize components
        TooltipManager.init();
        FormHandler.init();
        DataTable.init();
        
        // Global shortcuts
        window.KSPModal = ModalManager;
        window.KSPAlert = AlertSystem;
        window.KSPLoading = LoadingSystem;
        
        // Performance optimization: Debounce scroll events
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }
            scrollTimeout = setTimeout(function() {
                // Handle scroll events
            }, 100);
        });
        
        // Performance optimization: Debounce resize events
        let resizeTimeout;
        window.addEventListener('resize', function() {
            if (resizeTimeout) {
                clearTimeout(resizeTimeout);
            }
            resizeTimeout = setTimeout(function() {
                // Handle resize events
            }, 250);
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initOptimizedSystem);
    } else {
        initOptimizedSystem();
    }

    // Export for external use
    window.KSPOptimized = {
        jQuery: jQueryLoader,
        Bootstrap: BootstrapLoader,
        Modal: ModalManager,
        Tooltip: TooltipManager,
        Form: FormHandler,
        Table: DataTable,
        Alert: AlertSystem,
        Loading: LoadingSystem
    };

})(window, document);
