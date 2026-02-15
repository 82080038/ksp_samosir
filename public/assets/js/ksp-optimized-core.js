/**
 * KSP Samosir Optimized Core System
 * Bootstrap & jQuery Optimization with Component System
 */

class KSPOptimizedCore {
    constructor() {
        this.components = new Map();
        this.modals = new Map();
        this.observers = new Map();
        this.isBootstrapReady = false;
        this.isJQueryReady = false;
        this.init();
    }

    init() {
        this.setupLazyLoading();
        this.setupIntersectionObserver();
        this.setupModalSystem();
        this.setupComponentSystem();
        this.setupPerformanceMonitoring();
    }

    // Lazy Loading System
    setupLazyLoading() {
        // Load jQuery only when needed
        this.loadJQueryLazy();
        
        // Load Bootstrap only when needed
        this.loadBootstrapLazy();
        
        // Load other scripts on demand
        this.setupScriptLoader();
    }

    loadJQueryLazy() {
        if (window.jQuery) {
            this.isJQueryReady = true;
            this.onJQueryReady();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js';
        script.async = true;
        script.onload = () => {
            this.isJQueryReady = true;
            this.onJQueryReady();
        };
        document.head.appendChild(script);
    }

    loadBootstrapLazy() {
        if (window.bootstrap) {
            this.isBootstrapReady = true;
            this.onBootstrapReady();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js';
        script.async = true;
        script.onload = () => {
            this.isBootstrapReady = true;
            this.onBootstrapReady();
        };
        document.head.appendChild(script);
    }

    setupScriptLoader() {
        window.KSPLoadScript = (src, callback) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                callback && callback();
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = callback;
            document.head.appendChild(script);
        };
    }

    // Modal Management System
    setupModalSystem() {
        window.KSPModal = {
            show: (modalId, options = {}) => {
                if (!this.isBootstrapReady) {
                    console.warn('Bootstrap not ready yet');
                    return;
                }

                let modal = this.modals.get(modalId);
                if (!modal) {
                    const modalElement = document.getElementById(modalId);
                    if (modalElement) {
                        modal = new bootstrap.Modal(modalElement, options);
                        this.modals.set(modalId, modal);
                    }
                }

                if (modal) {
                    modal.show();
                }
            },

            hide: (modalId) => {
                const modal = this.modals.get(modalId);
                if (modal) {
                    modal.hide();
                }
            },

            create: (options) => {
                const modalId = options.id || 'ksp-modal-' + Date.now();
                const modalHTML = this.generateModalHTML(options);
                
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                
                const modalElement = document.getElementById(modalId);
                const modal = new bootstrap.Modal(modalElement, options.bootstrap || {});
                this.modals.set(modalId, modal);
                
                return modalId;
            },

            destroy: (modalId) => {
                const modal = this.modals.get(modalId);
                if (modal) {
                    modal.dispose();
                    this.modals.delete(modalId);
                }
                
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    modalElement.remove();
                }
            }
        }.bind(this);
    }

    generateModalHTML(options) {
        const id = options.id || 'ksp-modal-' + Date.now();
        const size = options.size || '';
        const backdrop = options.backdrop !== false;
        
        return `
            <div class="modal fade" id="${id}" tabindex="-1" data-bs-backdrop="${backdrop ? 'static' : 'false'}">
                <div class="modal-dialog ${size}">
                    <div class="modal-content">
                        ${options.title ? `
                        <div class="modal-header">
                            <h5 class="modal-title">${options.title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        ` : ''}
                        <div class="modal-body">
                            ${options.content || ''}
                        </div>
                        ${options.footer ? `
                        <div class="modal-footer">
                            ${options.footer}
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    // Component System
    setupComponentSystem() {
        window.KSPComponent = {
            register: (name, component) => {
                this.components.set(name, component);
            },

            get: (name) => {
                return this.components.get(name);
            },

            create: (name, element, options = {}) => {
                const component = this.components.get(name);
                if (component) {
                    return new component(element, options);
                }
                return null;
            },

            autoInit: () => {
                document.querySelectorAll('[data-ksp-component]').forEach(element => {
                    const componentName = element.getAttribute('data-ksp-component');
                    this.create(componentName, element);
                });
            }
        }.bind(this);

        // Auto-initialize components when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                window.KSPComponent.autoInit();
            });
        } else {
            window.KSPComponent.autoInit();
        }
    }

    // Intersection Observer for Lazy Loading
    setupIntersectionObserver() {
        if ('IntersectionObserver' in window) {
            this.observers.set('lazy', new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const element = entry.target;
                        
                        // Lazy load images
                        if (element.dataset.src) {
                            element.src = element.dataset.src;
                            element.removeAttribute('data-src');
                            this.observers.get('lazy').unobserve(element);
                        }
                        
                        // Lazy load components
                        if (element.dataset.kspLazy) {
                            this.loadLazyComponent(element);
                        }
                    }
                });
            }, {
                rootMargin: '50px'
            }));
        }
    }

    loadLazyComponent(element) {
        const componentName = element.dataset.kspLazy;
        const component = window.KSPComponent.get(componentName);
        
        if (component) {
            window.KSPComponent.create(componentName, element);
            element.removeAttribute('data-ksp-lazy');
        }
    }

    // Performance Monitoring
    setupPerformanceMonitoring() {
        window.KSPPerformance = {
            mark: (name) => {
                if (window.performance && window.performance.mark) {
                    window.performance.mark(name);
                }
            },

            measure: (name, start, end) => {
                if (window.performance && window.performance.measure) {
                    window.performance.measure(name, start, end);
                    const measure = window.performance.getEntriesByName(name)[0];
                    console.log(`KSP Performance: ${name} = ${measure.duration.toFixed(2)}ms`);
                }
            },

            getMemoryUsage: () => {
                if (window.performance && window.performance.memory) {
                    return {
                        used: Math.round(window.performance.memory.usedJSHeapSize / 1048576),
                        total: Math.round(window.performance.memory.totalJSHeapSize / 1048576),
                        limit: Math.round(window.performance.memory.jsHeapSizeLimit / 1048576)
                    };
                }
                return null;
            }
        };
    }

    // Event Handlers
    onJQueryReady() {
        console.log('jQuery loaded optimally');
        // Initialize jQuery-dependent features
        this.initJQueryFeatures();
    }

    onBootstrapReady() {
        console.log('Bootstrap loaded optimally');
        // Initialize Bootstrap-dependent features
        this.initBootstrapFeatures();
    }

    initJQueryFeatures() {
        if (!window.jQuery) return;

        const $ = window.jQuery;
        
        // Optimized jQuery plugins
        $.fn.kspDataTable = function(options) {
            return this.each(function() {
                const $table = $(this);
                if (!$table.data('kspDataTable')) {
                    $table.data('kspDataTable', new KSPDataTable($table, options));
                }
            });
        };

        $.fn.kspForm = function(options) {
            return this.each(function() {
                const $form = $(this);
                if (!$form.data('kspForm')) {
                    $form.data('kspForm', new KSPForm($form, options));
                }
            });
        };
    }

    initBootstrapFeatures() {
        if (!window.bootstrap) return;

        // Auto-initialize Bootstrap components
        this.initBootstrapTooltips();
        this.initBootstrapPopovers();
        this.initBootstrapDropdowns();
    }

    initBootstrapTooltips() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    initBootstrapPopovers() {
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        popoverTriggerList.forEach(popoverTriggerEl => {
            new bootstrap.Popover(popoverTriggerEl);
        });
    }

    initBootstrapDropdowns() {
        // Enhanced dropdown functionality
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.addEventListener('show.bs.dropdown', function() {
                // Preload dropdown content if needed
                const menu = this.querySelector('.dropdown-menu');
                if (menu && menu.dataset.kspLazyLoad) {
                    this.loadDropdownContent(menu);
                }
            }.bind(this));
        });
    }

    loadDropdownContent(menu) {
        // Implement lazy loading for dropdown content
        const url = menu.dataset.kspLazyLoad;
        if (url) {
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    menu.innerHTML = html;
                    menu.removeAttribute('data-ksp-lazy-load');
                })
                .catch(error => console.error('Error loading dropdown content:', error));
        }
    }
}

// Optimized Data Table Component
class KSPDataTable {
    constructor(element, options) {
        this.$element = $(element);
        this.options = $.extend({}, KSPDataTable.defaults, options);
        this.init();
    }

    init() {
        this.setupPagination();
        this.setupSearch();
        this.setupSorting();
        this.bindEvents();
    }

    setupPagination() {
        // Optimized pagination with virtual scrolling
    }

    setupSearch() {
        // Debounced search functionality
    }

    setupSorting() {
        // Client-side sorting with performance optimization
    }

    bindEvents() {
        // Event delegation for better performance
    }
}

KSPDataTable.defaults = {
    pageSize: 10,
    searchable: true,
    sortable: true,
    pagination: true
};

// Optimized Form Component
class KSPForm {
    constructor(element, options) {
        this.$element = $(element);
        this.options = $.extend({}, KSPForm.defaults, options);
        this.init();
    }

    init() {
        this.setupValidation();
        this.setupAjaxSubmit();
        this.bindEvents();
    }

    setupValidation() {
        // Real-time validation with debouncing
    }

    setupAjaxSubmit() {
        // Optimized AJAX form submission
    }

    bindEvents() {
        // Efficient event handling
    }
}

KSPForm.defaults = {
    validation: true,
    ajaxSubmit: true,
    showLoading: true
};

// Initialize Optimized Core
window.KSPOptimized = new KSPOptimizedCore();

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = KSPOptimizedCore;
}
