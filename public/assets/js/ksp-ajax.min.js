/**
 * Optimized AJAX Handler for KSP Samosir
 * Enhanced with caching, debouncing, and efficient DOM manipulation
 */

window.KSP = {
    // Base configuration
    baseUrl: window.location.origin + '/ksp_samosir',
    csrfToken: null,
    cache: new Map(), // Response cache
    pendingRequests: new Map(), // Prevent duplicate requests
    debounceTimers: new Map(), // Debounce timers

    // Initialize with optimizations
    init: function () {
        this.setupAjaxDefaults();
        this.setupEventListeners();
        this.setupTooltips();
        this.setupModals();
        this.setupPerformanceMonitoring();
        this.setupOfflineDetection();
    },

    // Enhanced AJAX defaults with caching and optimizations
    setupAjaxDefaults: function () {
        const self = this;

        $.ajaxSetup({
            url: this.baseUrl + '/api',
            method: 'POST',
            dataType: 'json',
            cache: true,
            timeout: 30000, // 30 second timeout
            beforeSend: function (xhr, settings) {
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                if (window.KSP.csrfToken) {
                    xhr.setRequestHeader('X-CSRF-Token', window.KSP.csrfToken);
                }

                // Prevent duplicate requests
                const requestKey = self.getRequestKey(settings);
                if (self.pendingRequests.has(requestKey)) {
                    return false; // Abort duplicate request
                }
                self.pendingRequests.set(requestKey, true);
            },
            complete: function (xhr, status) {
                // Clean up pending requests
                const requestKey = self.getRequestKey(this);
                self.pendingRequests.delete(requestKey);
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    url: this.url,
                    method: this.method
                });

                // Handle specific error types
                switch (xhr.status) {
                    case 401:
                        window.KSP.showError('Sesi telah berakhir. Silakan login kembali.');
                        setTimeout(() => window.location.href = '/ksp_samosir/login', 2000);
                        break;
                    case 403:
                        window.KSP.showError('Anda tidak memiliki akses untuk tindakan ini.');
                        break;
                    case 500:
                        window.KSP.showError('Terjadi kesalahan server. Silakan coba lagi.');
                        break;
                    default:
                        window.KSP.showError('Terjadi kesalahan koneksi. Silakan coba lagi.');
                }
            }
        });
    },

    // Generate unique request key for duplicate prevention
    getRequestKey: function (settings) {
        return `${settings.method}_${settings.url}_${JSON.stringify(settings.data)}`;
    },

    // Enhanced AJAX method with RESTful API support
    ajax: function (url, options = {}) {
        const self = this;
        const defaults = {
            method: 'GET',
            dataType: 'json',
            cache: true,
            timeout: 30000,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        };

        // Set CSRF token if available
        if (window.KSP.csrfToken) {
            defaults.headers['X-CSRF-Token'] = window.KSP.csrfToken;
        }

        const settings = { ...defaults, ...options };

        // Convert data to JSON for non-GET requests
        if (settings.method !== 'GET' && settings.data && typeof settings.data === 'object') {
            settings.data = JSON.stringify(settings.data);
        }

        // Handle caching for GET requests
        const cacheKey = settings.cache && settings.method === 'GET' ? url : null;
        if (cacheKey && this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < (settings.cacheTime || 300000)) {
                return Promise.resolve(cached.data);
            } else {
                this.cache.delete(cacheKey);
            }
        }

        return new Promise((resolve, reject) => {
            // Prevent duplicate requests
            const requestKey = `${settings.method}_${url}`;
            if (this.pendingRequests.has(requestKey)) {
                reject(new Error('Request already in progress'));
                return;
            }
            this.pendingRequests.set(requestKey, true);

            const xhr = new XMLHttpRequest();
            xhr.open(settings.method, url, true);

            // Set headers
            Object.keys(settings.headers).forEach(header => {
                xhr.setRequestHeader(header, settings.headers[header]);
            });

            xhr.timeout = settings.timeout;
            xhr.responseType = settings.dataType === 'json' ? 'json' : 'text';

            xhr.onload = () => {
                this.pendingRequests.delete(requestKey);

                if (xhr.status >= 200 && xhr.status < 300) {
                    let response = xhr.response;

                    // Parse JSON if needed
                    if (settings.dataType === 'json' && typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            reject(new Error('Invalid JSON response'));
                            return;
                        }
                    }

                    // Cache successful GET responses
                    if (cacheKey && response.success !== false) {
                        this.cache.set(cacheKey, {
                            data: response,
                            timestamp: Date.now()
                        });
                    }

                    resolve(response);
                } else {
                    reject(new Error(`HTTP ${xhr.status}: ${xhr.statusText}`));
                }
            };

            xhr.onerror = () => {
                this.pendingRequests.delete(requestKey);
                reject(new Error('Network error'));
            };

            xhr.ontimeout = () => {
                this.pendingRequests.delete(requestKey);
                reject(new Error('Request timeout'));
            };

            xhr.send(settings.data || null);
        });
    },

    // Debounced AJAX for search/input fields
    debouncedAjax: function (url, options = {}, delay = 300) {
        const key = `debounce_${url}`;

        if (this.debounceTimers.has(key)) {
            clearTimeout(this.debounceTimers.get(key));
        }

        return new Promise((resolve, reject) => {
            const timer = setTimeout(async () => {
                try {
                    const result = await this.ajax(url, options);
                    resolve(result);
                } catch (error) {
                    reject(error);
                }
                this.debounceTimers.delete(key);
            }, delay);

            this.debounceTimers.set(key, timer);
        });
    },

    // Optimized DOM manipulation methods
    dom: {
        // Efficient element creation with caching
        createElement: function (tag, attributes = {}, content = '') {
            const element = document.createElement(tag);

            // Batch attribute setting
            Object.keys(attributes).forEach(attr => {
                if (attr === 'class') {
                    element.className = attributes[attr];
                } else if (attr === 'style' && typeof attributes[attr] === 'object') {
                    Object.assign(element.style, attributes[attr]);
                } else if (attr.startsWith('data-')) {
                    element.setAttribute(attr, attributes[attr]);
                } else {
                    element[attr] = attributes[attr];
                }
            });

            if (content) {
                if (typeof content === 'string') {
                    element.innerHTML = content;
                } else if (content.nodeType) {
                    element.appendChild(content);
                }
            }

            return element;
        },

        // Batch DOM updates for performance
        batchUpdate: function (updates) {
            const fragment = document.createDocumentFragment();

            updates.forEach(update => {
                const element = typeof update.selector === 'string'
                    ? document.querySelector(update.selector)
                    : update.element;

                if (element && update.action) {
                    update.action(element);
                }
            });

            return fragment;
        },

        // Efficient table row updates
        updateTableRow: function (tableId, rowId, data) {
            const table = document.getElementById(tableId);
            if (!table) return;

            let row = table.querySelector(`tr[data-id="${rowId}"]`);
            if (!row) {
                row = this.createElement('tr', { 'data-id': rowId });
                table.querySelector('tbody').appendChild(row);
            }

            // Update cells efficiently
            Object.keys(data).forEach((key, index) => {
                let cell = row.cells[index];
                if (!cell) {
                    cell = row.insertCell(index);
                }
                cell.textContent = data[key];
            });
        }
    },

    // Enhanced event handling with delegation
    events: {
        // Event delegation for better performance
        delegate: function (selector, event, handler, container = document) {
            container.addEventListener(event, function (e) {
                if (e.target.matches(selector) || e.target.closest(selector)) {
                    handler.call(e.target, e);
                }
            });
        },

        // Throttled event handling
        throttle: function (func, limit) {
            let inThrottle;
            return function () {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            }
        },

        // Lazy loading for images and content
        lazyLoad: function (selector = 'img[data-src]') {
            const images = document.querySelectorAll(selector);
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        }
    },

    // Enhanced UI utilities
    ui: {
        // Efficient loading states
        setLoading: function (element, loading = true) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }

            if (!element) return;

            element.classList.toggle('loading', loading);
            element.style.pointerEvents = loading ? 'none' : '';
            element.setAttribute('aria-busy', loading);
        },

        // Optimized notifications with queue
        notifications: [],
        showNotification: function (message, type = 'info', duration = 5000) {
            // Remove existing notification
            const existing = document.querySelector('.ksp-notification');
            if (existing) {
                existing.remove();
            }

            const notification = KSP.dom.createElement('div', {
                class: `alert alert-${type} alert-dismissible fade show position-fixed`,
                style: {
                    top: '20px',
                    right: '20px',
                    zIndex: '1050',
                    maxWidth: '400px'
                },
                role: 'alert'
            }, `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `);

            document.body.appendChild(notification);

            // Auto remove
            if (duration > 0) {
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, duration);
            }

            return notification;
        },

        // Efficient modal management
        modalQueue: [],
        showModal: function (content, options = {}) {
            // Reuse existing modal if available
            let modal = document.getElementById('ksp-dynamic-modal');
            if (!modal) {
                modal = KSP.dom.createElement('div', {
                    id: 'ksp-dynamic-modal',
                    class: 'modal fade',
                    tabindex: '-1'
                });
                modal.innerHTML = `
                    <div class="modal-dialog ${options.size ? 'modal-' + options.size : ''}">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${options.title || 'Modal'}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">${content}</div>
                            ${options.footer ? `<div class="modal-footer">${options.footer}</div>` : ''}
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            } else {
                modal.querySelector('.modal-body').innerHTML = content;
                if (options.title) {
                    modal.querySelector('.modal-title').textContent = options.title;
                }
            }

            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            return bsModal;
        }
    },

    // API endpoints - RESTful implementation
    api: {
        // Member operations
        getMembers: async function (params = {}) {
            const queryString = new URLSearchParams(params).toString();
            const url = `/ksp_samosir/api/v1/members${queryString ? '?' + queryString : ''}`;
            const response = await window.KSP.ajax(url, { method: 'GET' });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to fetch members'));
        },

        getMember: async function (memberId) {
            const response = await window.KSP.ajax(`/ksp_samosir/api/v1/members/${memberId}`, { method: 'GET' });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to fetch member'));
        },

        createMember: async function (memberData) {
            const response = await window.KSP.ajax('/ksp_samosir/api/v1/members', {
                method: 'POST',
                data: memberData
            });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to create member'));
        },

        updateMember: async function (memberId, memberData) {
            const response = await window.KSP.ajax(`/ksp_samosir/api/v1/members/${memberId}`, {
                method: 'PUT',
                data: memberData
            });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to update member'));
        },

        deleteMember: async function (memberId) {
            const response = await window.KSP.ajax(`/ksp_samosir/api/v1/members/${memberId}`, { method: 'DELETE' });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to delete member'));
        },

        // Loan operations
        getLoans: async function (params = {}) {
            const queryString = new URLSearchParams(params).toString();
            const url = `/ksp_samosir/api/v1/loans${queryString ? '?' + queryString : ''}`;
            const response = await window.KSP.ajax(url, { method: 'GET' });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to fetch loans'));
        },

        getLoan: async function (loanId) {
            const response = await window.KSP.ajax(`/ksp_samosir/api/v1/loans/${loanId}`, { method: 'GET' });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to fetch loan'));
        },

        createLoan: async function (loanData) {
            const response = await window.KSP.ajax('/ksp_samosir/api/v1/loans', {
                method: 'POST',
                data: loanData
            });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to create loan'));
        },

        approveLoan: async function (loanId) {
            const response = await window.KSP.ajax(`/ksp_samosir/api/v1/loans/${loanId}/approve`, { method: 'PUT' });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to approve loan'));
        },

        rejectLoan: async function (loanId, reason = '') {
            const response = await window.KSP.ajax(`/ksp_samosir/api/v1/loans/${loanId}/reject`, {
                method: 'PUT',
                data: { reason }
            });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to reject loan'));
        },

        // Savings operations
        getSavings: async function (params = {}) {
            const queryString = new URLSearchParams(params).toString();
            const url = `/ksp_samosir/api/v1/savings${queryString ? '?' + queryString : ''}`;
            const response = await window.KSP.ajax(url, { method: 'GET' });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to fetch savings'));
        },

        getSaving: async function (savingId) {
            const response = await window.KSP.ajax(`/ksp_samosir/api/v1/savings/${savingId}`, { method: 'GET' });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to fetch saving'));
        },

        createSaving: async function (savingData) {
            const response = await window.KSP.ajax('/ksp_samosir/api/v1/savings', {
                method: 'POST',
                data: savingData
            });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to create saving'));
        },

        // Address operations
        getProvinces: async function () {
            const response = await window.KSP.ajax('/ksp_samosir/api/v1/addresses', { method: 'GET' });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to fetch provinces'));
        },

        searchAddresses: async function (keyword, type = 'village') {
            const response = await window.KSP.ajax('/ksp_samosir/api/v1/addresses', {
                method: 'POST',
                data: { keyword, type }
            });
            return response.success ? response.data : Promise.reject(new Error(response.error?.message || 'Failed to search addresses'));
        }
    },

    // Enhanced setup methods
    setupPerformanceMonitoring: function () {
        // Monitor AJAX performance
        $(document).ajaxComplete(function (event, xhr, settings) {
            if (window.performance && window.performance.mark) {
                const duration = xhr.responseTime || 0;
                console.log(`AJAX ${settings.method} ${settings.url}: ${duration}ms`);

                // Log slow requests
                if (duration > 5000) { // 5 seconds
                    console.warn('Slow AJAX request detected:', settings.url, duration + 'ms');
                }
            }
        });
    },

    setupOfflineDetection: function () {
        window.addEventListener('online', function () {
            KSP.ui.showNotification('Koneksi internet tersambung kembali', 'success');
        });

        window.addEventListener('offline', function () {
            KSP.ui.showNotification('Koneksi internet terputus. Beberapa fitur mungkin tidak berfungsi.', 'warning', 0);
        });
    },

    // Missing methods that were called in init()
    setupEventListeners: function () {
        // Global event listeners can be added here if needed
        console.log('Event listeners setup completed');
    },

    setupTooltips: function () {
        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },

    setupModals: function () {
        // Initialize Bootstrap modals
        const modalTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="modal"]'));
        modalTriggerList.map(function (modalTriggerEl) {
            modalTriggerEl.addEventListener('click', function () {
                const target = this.getAttribute('data-bs-target');
                const modal = document.querySelector(target);
                if (modal) {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                }
            });
        });
    },

    // Enhanced utility methods
    showError: function (message) {
        this.ui.showNotification(message, 'danger');
    },

    showSuccess: function (message) {
        this.ui.showNotification(message, 'success');
    },

    // Cache management
    clearCache: function () {
        this.cache.clear();
        console.log('AJAX cache cleared');
    },

    // Performance utilities
    measurePerformance: function (name, fn) {
        const start = performance.now();
        const result = fn();
        const end = performance.now();
        console.log(`${name}: ${(end - start).toFixed(2)}ms`);
        return result;
    }
};

// Initialize on document ready
$(document).ready(function () {
    window.KSP.init();
});

// Global functions for backward compatibility
function showModal(modalId, title, content, size) {
    return window.KSP.showModal(modalId, title, content, size);
}

function hideModal(modalId) {
    window.KSP.hideModal(modalId);
}

function showToast(message, type) {
    window.KSP.showToast(message, type);
}

function formatCurrency(amount) {
    return window.KSP.formatCurrency(amount);
}
