/**
 * KSP CRUD Framework - Complex CRUD with Simple Interface
 * jQuery + Bootstrap powered CRUD operations
 */

(function() {
    'use strict';
    
    // Extend KSP framework with CRUD capabilities
    if (typeof window.KSP === 'undefined') {
        window.KSP = {};
    }
    
    // CRUD Manager Class
    KSP.CRUD = {
        // Default configuration
        defaults: {
            tableSelector: '#crud-table',
            modalSelector: '#crud-modal',
            formSelector: '#crud-form',
            searchSelector: '#crud-search',
            paginationSelector: '#crud-pagination',
            loadingClass: 'loading',
            apiEndpoint: '/api/crud',
            itemsPerPage: 10,
            enableInlineEdit: true,
            enableBulkActions: true,
            enableInstantSearch: true
        },
        
        // Initialize CRUD for a module
        init: function(module, options) {
            var self = this;
            this.module = module;
            this.config = $.extend({}, this.defaults, options);
            this.currentPage = 1;
            this.searchTerm = '';
            this.selectedItems = [];
            
            console.log('Initializing CRUD for module:', module);
            
            // Setup event handlers
            this.setupEventHandlers();
            
            // Load initial data
            this.loadData();
            
            // Setup search functionality
            if (this.config.enableInstantSearch) {
                this.setupInstantSearch();
            }
            
            // Setup bulk actions
            if (this.config.enableBulkActions) {
                this.setupBulkActions();
            }
        },
        
        // Setup event handlers
        setupEventHandlers: function() {
            var self = this;
            
            // Add button click
            $(document).on('click', '[data-action="crud-add"]', function() {
                self.showAddForm();
            });
            
            // Edit button click
            $(document).on('click', '[data-action="crud-edit"]', function() {
                var id = $(this).data('id');
                self.showEditForm(id);
            });
            
            // View button click
            $(document).on('click', '[data-action="crud-view"]', function() {
                var id = $(this).data('id');
                self.showViewForm(id);
            });
            
            // Delete button click
            $(document).on('click', '[data-action="crud-delete"]', function() {
                var id = $(this).data('id');
                self.confirmDelete(id);
            });
            
            // Form submission
            $(document).on('submit', this.config.formSelector, function(e) {
                e.preventDefault();
                self.saveForm();
            });
            
            // Modal hidden event
            $(this.config.modalSelector).on('hidden.bs.modal', function() {
                self.resetForm();
            });
            
            // Pagination click
            $(document).on('click', '[data-action="crud-page"]', function() {
                var page = $(this).data('page');
                self.goToPage(page);
            });
            
            // Refresh button
            $(document).on('click', '[data-action="crud-refresh"]', function() {
                self.loadData();
            });
        },
        
        // Load data via AJAX
        loadData: function() {
            var self = this;
            
            this.showLoading(true);
            
            var params = {
                module: this.module,
                page: this.currentPage,
                search: this.searchTerm,
                per_page: this.config.itemsPerPage
            };
            
            $.ajax({
                url: this.config.apiEndpoint + '/read',
                method: 'GET',
                data: params,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.renderTable(response.data);
                        self.renderPagination(response.pagination);
                    } else {
                        self.showError('Gagal memuat data: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    self.showError('Terjadi kesalahan saat memuat data');
                    console.error('CRUD load error:', error);
                },
                complete: function() {
                    self.showLoading(false);
                }
            });
        },
        
        // Render table with data
        renderTable: function(data) {
            var $table = $(this.config.tableSelector);
            var $tbody = $table.find('tbody');
            
            if (!data || data.length === 0) {
                $tbody.html('<tr><td colspan="100%" class="text-center text-muted">Tidak ada data</td></tr>');
                return;
            }
            
            var html = '';
            data.forEach(function(item, index) {
                html += self.renderTableRow(item, index);
            });
            
            $tbody.html(html);
            
            // Add Bootstrap table styling
            $table.addClass('table table-striped table-hover');
        },
        
        // Render table row
        renderTableRow: function(item, index) {
            var self = this;
            var html = '<tr>';
            
            // Checkbox for bulk actions
            if (this.config.enableBulkActions) {
                html += '<td><input type="checkbox" class="form-check-input crud-select-item" data-id="' + item.id + '"></td>';
            }
            
            // Data columns (dynamic based on item properties)
            Object.keys(item).forEach(function(key) {
                if (key !== 'id' && !key.startsWith('_')) {
                    var value = item[key] || '-';
                    html += '<td>' + self.escapeHtml(value) + '</td>';
                }
            });
            
            // Actions column
            html += '<td>';
            html += '<div class="btn-group btn-group-sm" role="group">';
            html += '<button type="button" class="btn btn-outline-primary btn-sm" data-action="crud-view" data-id="' + item.id + '" title="Lihat">';
            html += '<i class="bi bi-eye"></i></button>';
            html += '<button type="button" class="btn btn-outline-warning btn-sm" data-action="crud-edit" data-id="' + item.id + '" title="Edit">';
            html += '<i class="bi bi-pencil"></i></button>';
            html += '<button type="button" class="btn btn-outline-danger btn-sm" data-action="crud-delete" data-id="' + item.id + '" title="Hapus">';
            html += '<i class="bi bi-trash"></i></button>';
            html += '</div>';
            html += '</td>';
            
            html += '</tr>';
            return html;
        },
        
        // Render pagination
        renderPagination: function(pagination) {
            var $pagination = $(this.config.paginationSelector);
            
            if (!pagination || pagination.total_pages <= 1) {
                $pagination.empty();
                return;
            }
            
            var html = '<nav><ul class="pagination justify-content-center">';
            
            // Previous button
            if (pagination.current_page > 1) {
                html += '<li class="page-item"><a class="page-link" href="#" data-action="crud-page" data-page="' + (pagination.current_page - 1) + '">Previous</a></li>';
            }
            
            // Page numbers
            for (var i = 1; i <= pagination.total_pages; i++) {
                var active = i === pagination.current_page ? 'active' : '';
                html += '<li class="page-item ' + active + '"><a class="page-link" href="#" data-action="crud-page" data-page="' + i + '">' + i + '</a></li>';
            }
            
            // Next button
            if (pagination.current_page < pagination.total_pages) {
                html += '<li class="page-item"><a class="page-link" href="#" data-action="crud-page" data-page="' + (pagination.current_page + 1) + '">Next</a></li>';
            }
            
            html += '</ul></nav>';
            $pagination.html(html);
        },
        
        // Show add form
        showAddForm: function() {
            var self = this;
            var $modal = $(this.config.modalSelector);
            
            // Set modal title
            $modal.find('.modal-title').text('Tambah Data Baru');
            
            // Reset form
            this.resetForm();
            
            // Show modal
            $modal.modal('show');
        },
        
        // Show edit form
        showEditForm: function(id) {
            var self = this;
            var $modal = $(this.config.modalSelector);
            
            // Set modal title
            $modal.find('.modal-title').text('Edit Data');
            
            // Load data for editing
            this.loadFormData(id);
            
            // Show modal
            $modal.modal('show');
        },
        
        // Show view form
        showViewForm: function(id) {
            var self = this;
            var $modal = $(this.config.modalSelector);
            
            // Set modal title
            $modal.find('.modal-title').text('Detail Data');
            
            // Load data for viewing
            this.loadFormData(id, true); // readonly = true
            
            // Show modal
            $modal.modal('show');
        },
        
        // Load form data
        loadFormData: function(id, readonly) {
            var self = this;
            
            $.ajax({
                url: this.config.apiEndpoint + '/read',
                method: 'GET',
                data: { module: this.module, id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        self.populateForm(response.data, readonly);
                    } else {
                        self.showError('Gagal memuat data: ' + response.message);
                    }
                },
                error: function() {
                    self.showError('Terjadi kesalahan saat memuat data');
                }
            });
        },
        
        // Populate form with data
        populateForm: function(data, readonly) {
            var $form = $(this.config.formSelector);
            
            // Fill form fields
            Object.keys(data).forEach(function(key) {
                var $field = $form.find('[name="' + key + '"]');
                if ($field.length) {
                    $field.val(data[key]);
                    if (readonly) {
                        $field.prop('readonly', true);
                    }
                }
            });
            
            // Set readonly mode
            if (readonly) {
                $form.find('input, select, textarea').prop('readonly', true);
                $form.find('button[type="submit"]').hide();
            }
        },
        
        // Save form
        saveForm: function() {
            var self = this;
            var $form = $(this.config.formSelector);
            var formData = new FormData($form[0]);
            
            // Add module name
            formData.append('module', this.module);
            
            // Show loading
            this.showFormLoading(true);
            
            $.ajax({
                url: this.config.apiEndpoint + '/save',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showSuccess('Data berhasil disimpan');
                        $(self.config.modalSelector).modal('hide');
                        self.loadData(); // Refresh table
                    } else {
                        self.showError('Gagal menyimpan data: ' + response.message);
                    }
                },
                error: function() {
                    self.showError('Terjadi kesalahan saat menyimpan data');
                },
                complete: function() {
                    self.showFormLoading(false);
                }
            });
        },
        
        // Confirm delete
        confirmDelete: function(id) {
            var self = this;
            
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                this.deleteItem(id);
            }
        },
        
        // Delete item
        deleteItem: function(id) {
            var self = this;
            
            $.ajax({
                url: this.config.apiEndpoint + '/delete',
                method: 'POST',
                data: { module: this.module, id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showSuccess('Data berhasil dihapus');
                        self.loadData(); // Refresh table
                    } else {
                        self.showError('Gagal menghapus data: ' + response.message);
                    }
                },
                error: function() {
                    self.showError('Terjadi kesalahan saat menghapus data');
                }
            });
        },
        
        // Setup instant search
        setupInstantSearch: function() {
            var self = this;
            var $search = $(this.config.searchSelector);
            
            if (!$search.length) return;
            
            var searchTimeout;
            $search.on('input', function() {
                clearTimeout(searchTimeout);
                var term = $(this).val().trim();
                
                searchTimeout = setTimeout(function() {
                    self.searchTerm = term;
                    self.currentPage = 1; // Reset to first page
                    self.loadData();
                }, 300); // 300ms delay
            });
        },
        
        // Setup bulk actions
        setupBulkActions: function() {
            var self = this;
            
            // Select all checkbox
            $(document).on('change', '.crud-select-all', function() {
                var checked = $(this).prop('checked');
                $('.crud-select-item').prop('checked', checked);
                self.updateBulkActions();
            });
            
            // Individual checkbox
            $(document).on('change', '.crud-select-item', function() {
                self.updateBulkActions();
            });
            
            // Bulk delete
            $(document).on('click', '[data-action="crud-bulk-delete"]', function() {
                self.bulkDelete();
            });
        },
        
        // Update bulk actions
        updateBulkActions: function() {
            var selected = $('.crud-select-item:checked').length;
            this.selectedItems = [];
            
            $('.crud-select-item:checked').each(function() {
                this.selectedItems.push($(this).data('id'));
            });
            
            // Show/hide bulk actions
            if (selected > 0) {
                $('.crud-bulk-actions').show();
                $('.crud-bulk-count').text(selected + ' item(s) selected');
            } else {
                $('.crud-bulk-actions').hide();
            }
        },
        
        // Bulk delete
        bulkDelete: function() {
            var self = this;
            
            if (this.selectedItems.length === 0) {
                this.showError('Tidak ada item yang dipilih');
                return;
            }
            
            if (confirm('Apakah Anda yakin ingin menghapus ' + this.selectedItems.length + ' item?')) {
                $.ajax({
                    url: this.config.apiEndpoint + '/bulk-delete',
                    method: 'POST',
                    data: { 
                        module: this.module, 
                        ids: this.selectedItems 
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showSuccess('Data berhasil dihapus');
                            self.loadData(); // Refresh table
                        } else {
                            self.showError('Gagal menghapus data: ' + response.message);
                        }
                    },
                    error: function() {
                        self.showError('Terjadi kesalahan saat menghapus data');
                    }
                });
            }
        },
        
        // Go to page
        goToPage: function(page) {
            this.currentPage = page;
            this.loadData();
        },
        
        // Reset form
        resetForm: function() {
            $(this.config.formSelector)[0].reset();
            $(this.config.formSelector).find('input, select, textarea').prop('readonly', false);
            $(this.config.formSelector).find('button[type="submit"]').show();
        },
        
        // Show loading state
        showLoading: function(show) {
            var $table = $(this.config.tableSelector);
            if (show) {
                $table.addClass(this.config.loadingClass);
                $table.find('tbody').html('<tr><td colspan="100%" class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
            } else {
                $table.removeClass(this.config.loadingClass);
            }
        },
        
        // Show form loading
        showFormLoading: function(show) {
            var $form = $(this.config.formSelector);
            var $submit = $form.find('button[type="submit"]');
            
            if (show) {
                $submit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
            } else {
                $submit.prop('disabled', false).html('Simpan');
            }
        },
        
        // Show success message
        showSuccess: function(message) {
            if (window.KSP && window.KSP.Notification) {
                KSP.Notification.show(message, 'success');
            } else {
                alert(message);
            }
        },
        
        // Show error message
        showError: function(message) {
            if (window.KSP && window.KSP.Notification) {
                KSP.Notification.show(message, 'error');
            } else {
                alert(message);
            }
        },
        
        // Escape HTML
        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
    
    console.log('KSP CRUD Framework loaded');
})();
