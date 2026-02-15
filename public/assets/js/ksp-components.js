/**
 * KSP Samosir Member Management Module
 * Uses templates and modern JavaScript practices
 */

(function($, window, document) {
    'use strict';

    // Template loader utility
    const TemplateLoader = {
        cache: new Map(),

        load: function(templateName) {
            if (this.cache.has(templateName)) {
                return Promise.resolve(this.cache.get(templateName));
            }

            return $.get(`/ksp_samosir/templates/${templateName}.html`)
                .then(template => {
                    this.cache.set(templateName, template);
                    return template;
                });
        },

        render: function(template, data = {}) {
            let rendered = template;

            // Simple template replacement
            Object.keys(data).forEach(key => {
                const regex = new RegExp(`{{${key}}}`, 'g');
                rendered = rendered.replace(regex, data[key] || '');
            });

            return rendered;
        }
    };

    // Modal Manager
    const ModalManager = {
        modals: new Map(),

        create: function(modalId, options = {}) {
            const defaults = {
                title: 'Modal',
                content: '',
                size: '', // 'modal-lg', 'modal-xl', etc.
                footer: true,
                cancelText: 'Batal',
                submitText: 'Simpan'
            };

            const config = { ...defaults, ...options };

            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
                    <div class="modal-dialog ${config.size}">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="${modalId}Label">${config.title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="${modalId}-body">
                                ${config.content}
                            </div>
                            ${config.footer ? `
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-1"></i>${config.cancelText}
                                </button>
                                <button type="button" class="btn btn-ksp-primary" id="${modalId}-submit">
                                    <i class="bi bi-check-circle me-1"></i>${config.submitText}
                                </button>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if any
            $(`#${modalId}`).remove();

            // Add to DOM
            $('body').append(modalHtml);

            // Create Bootstrap modal instance
            const modal = new bootstrap.Modal(document.getElementById(modalId), {
                backdrop: 'static',
                keyboard: false
            });

            this.modals.set(modalId, { modal, config });

            return modal;
        },

        show: function(modalId, content = null) {
            const modalData = this.modals.get(modalId);
            if (!modalData) return;

            if (content) {
                $(`#${modalId}-body`).html(content);
            }

            modalData.modal.show();
        },

        hide: function(modalId) {
            const modalData = this.modals.get(modalId);
            if (modalData) {
                modalData.modal.hide();
            }
        },

        onSubmit: function(modalId, callback) {
            $(document).on('click', `#${modalId}-submit`, callback);
        }
    };

    // Data Table Component
    const DataTable = {
        create: function(containerId, options = {}) {
            const defaults = {
                title: 'Data',
                tableId: containerId + '-table',
                searchPlaceholder: 'Cari data...',
                perPageOptions: [15, 25, 50],
                columns: [],
                dataUrl: null,
                actions: []
            };

            const config = { ...defaults, ...options };

            const tableHtml = `
                <div class="card ksp-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">${config.title}</h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="${config.tableId}-refresh">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-sm btn-ksp-primary" id="${config.tableId}-add">
                                <i class="bi bi-plus-circle"></i> Tambah
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Search and Filter -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="${config.tableId}-search"
                                           placeholder="${config.searchPlaceholder}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-6">
                                        <select class="form-select" id="${config.tableId}-status-filter">
                                            <option value="">Semua Status</option>
                                            <option value="aktif">Aktif</option>
                                            <option value="nonaktif">Nonaktif</option>
                                            <option value="pending">Pending</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <select class="form-select" id="${config.tableId}-per-page">
                                            ${config.perPageOptions.map(opt => `<option value="${opt}">${opt} per halaman</option>`).join('')}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div id="${config.tableId}-loading" class="text-center py-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Memuat data...</div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="${config.tableId}">
                                <thead class="table-dark">
                                    <tr>
                                        ${config.columns.map(col => `<th>${col.label}</th>`).join('')}
                                        ${config.actions.length > 0 ? '<th>Aksi</th>' : ''}
                                    </tr>
                                </thead>
                                <tbody id="${config.tableId}-tbody">
                                    <!-- Data rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div id="${config.tableId}-empty" class="text-center py-5" style="display: none;">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">Tidak ada data</h5>
                            <p class="text-muted">Belum ada ${config.title.toLowerCase()} yang tercatat dalam sistem.</p>
                        </div>

                        <!-- Pagination -->
                        <nav id="${config.tableId}-pagination" aria-label="Table pagination" style="display: none;">
                            <ul class="pagination justify-content-center" id="${config.tableId}-pagination-list">
                                <!-- Pagination links will be inserted here -->
                            </ul>
                        </nav>
                    </div>
                </div>
            `;

            $(`#${containerId}`).html(tableHtml);

            // Initialize table instance
            return new DataTableInstance(config);
        }
    };

    // Data Table Instance
    function DataTableInstance(config) {
        this.config = config;
        this.currentPage = 1;
        this.perPage = 15;
        this.searchTerm = '';
        this.statusFilter = '';

        this.init();
    }

    DataTableInstance.prototype.init = function() {
        const self = this;

        // Bind events
        $(`#${this.config.tableId}-refresh`).on('click', () => this.loadData());
        $(`#${this.config.tableId}-add`).on('click', () => this.onAdd());

        $(`#${this.config.tableId}-search`).on('input', function() {
            self.searchTerm = $(this).val();
            self.debouncedSearch();
        });

        $(`#${this.config.tableId}-status-filter`).on('change', function() {
            self.statusFilter = $(this).val();
            self.loadData();
        });

        $(`#${this.config.tableId}-per-page`).on('change', function() {
            self.perPage = parseInt($(this).val());
            self.loadData();
        });

        // Debounced search
        this.debouncedSearch = this.debounce(() => this.loadData(), 300);

        // Load initial data
        this.loadData();
    };

    DataTableInstance.prototype.loadData = function() {
        const loadingEl = $(`#${this.config.tableId}-loading`);
        const tableEl = $(`#${this.config.tableId}`);
        const emptyEl = $(`#${this.config.tableId}-empty`);

        loadingEl.show();
        tableEl.hide();
        emptyEl.hide();

        // Build query parameters
        const params = {
            page: this.currentPage,
            per_page: this.perPage
        };

        if (this.searchTerm) params.search = this.searchTerm;
        if (this.statusFilter) params.status = this.statusFilter;

        // Load data using API
        if (this.config.dataUrl) {
            KSP.ajax(this.config.dataUrl, {
                method: 'GET',
                data: params
            }).then(response => {
                // Validate response structure
                if (!response || typeof response !== 'object') {
                    throw new Error('Invalid response format');
                }
                this.renderData(response);
            }).catch(error => {
                KSP.showError('Failed to load data: ' + error.message);
            }).finally(() => {
                loadingEl.hide();
            });
        }
    };

    DataTableInstance.prototype.renderData = function(response) {
        const tbody = $(`#${this.config.tableId}-tbody`);
        const tableEl = $(`#${this.config.tableId}`);
        const emptyEl = $(`#${this.config.tableId}-empty`);

        tbody.empty();

        // Handle different response formats
        let data = response.data || [];
        
        // If data is not an array, try to extract array from response
        if (!Array.isArray(data)) {
            if (data.items && Array.isArray(data.items)) {
                // Handle nested structure like { data: { items: [...] } }
                data = data.items;
            } else if (response.anggota && Array.isArray(response.anggota)) {
                data = response.anggota;
            } else if (response.members && Array.isArray(response.members)) {
                data = response.members;
            } else if (response.items && Array.isArray(response.items)) {
                data = response.items;
            } else {
                console.warn('Response data is not an array:', response);
                data = [];
            }
        }

        if (data.length === 0) {
            tableEl.hide();
            emptyEl.show();
            return;
        }

        // Render rows
        data.forEach((item, index) => {
            const row = $('<tr>');

            // Add data columns
            this.config.columns.forEach(col => {
                const cell = $('<td>');
                if (col.render) {
                    cell.html(col.render(item));
                } else {
                    cell.text(item[col.field] || '');
                }
                row.append(cell);
            });

            // Add actions column
            if (this.config.actions.length > 0) {
                const actionsCell = $('<td>');
                const actionsHtml = this.config.actions.map(action => {
                    return `<button class="btn btn-sm btn-outline-${action.variant || 'primary'} me-1"
                                    onclick="${action.handler}(${item.id})">
                               <i class="bi bi-${action.icon}"></i> ${action.label}
                            </button>`;
                }).join('');
                actionsCell.html(actionsHtml);
                row.append(actionsCell);
            }

            tbody.append(row);
        });

        tableEl.show();
        emptyEl.hide();

        // Render pagination
        this.renderPagination(response.meta?.pagination);
    };

    DataTableInstance.prototype.renderPagination = function(pagination) {
        if (!pagination) return;

        const paginationEl = $(`#${this.config.tableId}-pagination`);
        const paginationList = $(`#${this.config.tableId}-pagination-list`);

        if (pagination.last_page <= 1) {
            paginationEl.hide();
            return;
        }

        paginationList.empty();

        // Previous button
        const prevBtn = $(`<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="return false;">Previous</a>
                          </li>`);
        if (pagination.current_page > 1) {
            prevBtn.find('a').on('click', () => {
                this.currentPage--;
                this.loadData();
            });
        }
        paginationList.append(prevBtn);

        // Page numbers
        for (let i = Math.max(1, pagination.current_page - 2);
             i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) {
            const pageBtn = $(`<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="return false;">${i}</a>
                              </li>`);
            pageBtn.find('a').on('click', () => {
                this.currentPage = i;
                this.loadData();
            });
            paginationList.append(pageBtn);
        }

        // Next button
        const nextBtn = $(`<li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="return false;">Next</a>
                          </li>`);
        if (pagination.current_page < pagination.last_page) {
            nextBtn.find('a').on('click', () => {
                this.currentPage++;
                this.loadData();
            });
        }
        paginationList.append(nextBtn);

        paginationEl.show();
    };

    DataTableInstance.prototype.debounce = function(func, delay) {
        let timeoutId;
        return function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(func, delay);
        };
    };

    // Make components globally available
    window.KSP = window.KSP || {};
    window.KSP.TemplateLoader = TemplateLoader;
    window.KSP.ModalManager = ModalManager;
    window.KSP.DataTable = DataTable;

})(jQuery, window, document);
