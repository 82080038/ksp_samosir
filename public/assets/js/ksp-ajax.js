/**
 * Global AJAX Handler for KSP Samosir
 * Comprehensive jQuery/AJAX integration with Bootstrap modals
 */

window.KSP = {
    // Base configuration
    baseUrl: window.location.origin + '/ksp_samosir',
    csrfToken: null,
    
    // Initialize
    init: function() {
        this.setupAjaxDefaults();
        this.setupEventListeners();
        this.setupTooltips();
        this.setupModals();
    },
    
    // AJAX defaults
    setupAjaxDefaults: function() {
        $.ajaxSetup({
            url: this.baseUrl + '/api',
            method: 'POST',
            dataType: 'json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                if (window.KSP.csrfToken) {
                    xhr.setRequestHeader('X-CSRF-Token', window.KSP.csrfToken);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                window.KSP.showError('Terjadi kesalahan koneksi. Silakan coba lagi.');
            }
        });
    },
    
    // Main AJAX method
    ajax: function(action, data, successCallback, errorCallback) {
        var requestData = {
            action: action,
            data: data || {}
        };
        
        $.ajax({
            data: requestData,
            success: function(response) {
                if (response.success) {
                    if (typeof successCallback === 'function') {
                        successCallback(response.data);
                    }
                } else {
                    window.KSP.showError(response.message || 'Terjadi kesalahan.');
                    if (typeof errorCallback === 'function') {
                        errorCallback(response);
                    }
                }
            },
            error: function(xhr) {
                var message = 'Terjadi kesalahan server.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                window.KSP.showError(message);
                if (typeof errorCallback === 'function') {
                    errorCallback(xhr);
                }
            }
        });
    },
    
    // API endpoints
    api: {
        // Member operations
        getMemberDetails: function(memberId, callback) {
            window.KSP.ajax('get_member_details', {member_id: memberId}, callback);
        },
        
        saveMember: function(memberData, callback) {
            window.KSP.ajax('save_member', memberData, callback);
        },
        
        deleteMember: function(memberId, callback) {
            window.KSP.ajax('delete_member', {member_id: memberId}, callback);
        },
        
        // Loan operations
        getLoanDetails: function(loanId, callback) {
            window.KSP.ajax('get_loan_details', {loan_id: loanId}, callback);
        },
        
        approveLoan: function(loanId, callback) {
            window.KSP.ajax('approve_loan', {loan_id: loanId}, callback);
        },
        
        rejectLoan: function(loanId, reason, callback) {
            window.KSP.ajax('reject_loan', {loan_id: loanId, reason: reason}, callback);
        },
        
        // Savings operations
        getSavingsHistory: function(memberId, callback) {
            window.KSP.ajax('get_savings_history', {member_id: memberId}, callback);
        },
        
        addSavings: function(savingsData, callback) {
            window.KSP.ajax('add_savings', savingsData, callback);
        },
        
        // Address operations
        getProvinces: function(callback) {
            window.KSP.ajax('get_provinces', {}, callback);
        },
        
        getRegencies: function(provinceId, callback) {
            window.KSP.ajax('get_regencies', {province_id: provinceId}, callback);
        },
        
        getDistricts: function(regencyId, callback) {
            window.KSP.ajax('get_districts', {regency_id: regencyId}, callback);
        },
        
        getVillages: function(districtId, callback) {
            window.KSP.ajax('get_villages', {district_id: districtId}, callback);
        },
        
        searchAddress: function(keyword, type, callback) {
            window.KSP.ajax('search_address', {keyword: keyword, type: type}, callback);
        },
        
        // Settings operations
        getSettings: function(callback) {
            window.KSP.ajax('get_settings', {}, callback);
        },
        
        updateSettings: function(settingsData, callback) {
            window.KSP.ajax('update_settings', settingsData, callback);
        },
        
        // Reports operations
        getFinancialReport: function(params, callback) {
            window.KSP.ajax('get_financial_report', params, callback);
        },
        
        getSHUReport: function(periodId, callback) {
            window.KSP.ajax('get_shu_report', {period_id: periodId}, callback);
        },
        
        // Activity operations
        getActivitySummary: function(params, callback) {
            window.KSP.ajax('get_activity_summary', params, callback);
        },
        
        // Meeting operations
        getMeetings: function(callback) {
            window.KSP.ajax('get_meetings', {}, callback);
        },
        
        saveMeetingAttendance: function(attendanceData, callback) {
            window.KSP.ajax('save_meeting_attendance', attendanceData, callback);
        }
    },
    
    // Modal management
    showModal: function(modalId, title, content, size) {
        var modal = $('#' + modalId);
        
        // Set size class
        if (size) {
            modal.find('.modal-dialog').removeClass('modal-sm modal-lg modal-xl').addClass('modal-' + size);
        }
        
        // Set title
        if (title) {
            modal.find('.modal-title').text(title);
        }
        
        // Set content
        if (content) {
            modal.find('.modal-body').html(content);
        }
        
        // Show modal
        var bsModal = new bootstrap.Modal(modal[0]);
        bsModal.show();
        
        return bsModal;
    },
    
    hideModal: function(modalId) {
        var modal = $('#' + modalId);
        var bsModal = bootstrap.Modal.getInstance(modal[0]);
        if (bsModal) {
            bsModal.hide();
        }
    },
    
    // Predefined modals
    modals: {
        // Member details modal
        showMemberDetails: function(memberId) {
            window.KSP.showModal('memberDetailsModal', 'Detail Anggota', '<div class="text-center"><div class="spinner-border"></div></div>');
            window.KSP.api.getMemberDetails(memberId, function(data) {
                var content = window.KSP.templates.memberDetails(data);
                $('#memberDetailsModal .modal-body').html(content);
            });
        },
        
        // Loan details modal
        showLoanDetails: function(loanId) {
            window.KSP.showModal('loanDetailsModal', 'Detail Pinjaman', '<div class="text-center"><div class="spinner-border"></div></div>');
            window.KSP.api.getLoanDetails(loanId, function(data) {
                var content = window.KSP.templates.loanDetails(data);
                $('#loanDetailsModal .modal-body').html(content);
            });
        },
        
        // Address search modal
        showAddressSearch: function(callback) {
            window.KSP.showModal('addressSearchModal', 'Cari Alamat', window.KSP.templates.addressSearch());
            
            // Setup search functionality
            $('#searchAddressBtn').off('click').on('click', function() {
                var keyword = $('#addressSearch').val();
                var type = $('#addressType').val();
                
                if (!keyword.trim()) {
                    $('#addressResults').html('<div class="text-center text-muted">Masukkan kata kunci pencarian</div>');
                    return;
                }
                
                $('#addressResults').html('<div class="text-center"><div class="spinner-border"></div></div>');
                
                window.KSP.api.searchAddress(keyword, type, function(results) {
                    var content = window.KSP.templates.addressResults(results);
                    $('#addressResults').html(content);
                });
            });
            
            // Enter key search
            $('#addressSearch').off('keypress').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#searchAddressBtn').click();
                }
            });
        },
        
        // Activity summary modal
        showActivitySummary: function() {
            window.KSP.showModal('activitySummaryModal', 'Ringkasan Aktivitas', window.KSP.templates.activitySummaryForm());
            
            // Load summary on button click
            $('#loadSummaryBtn').off('click').on('click', function() {
                var startDate = $('#summaryStartDate').val();
                var endDate = $('#summaryEndDate').val();
                
                $('#activitySummaryContent').html('<div class="text-center"><div class="spinner-border"></div></div>');
                
                window.KSP.api.getActivitySummary({
                    start_date: startDate,
                    end_date: endDate
                }, function(data) {
                    var content = window.KSP.templates.activitySummary(data);
                    $('#activitySummaryContent').html(content);
                });
            });
        },
        
        // Confirmation modal
        confirm: function(message, onConfirm, onCancel) {
            var content = `
                <div class="text-center">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Konfirmasi</h5>
                    <p class="text-muted">${message}</p>
                    <div class="mt-4">
                        <button class="btn btn-danger me-2" id="confirmBtn">Ya</button>
                        <button class="btn btn-secondary" id="cancelBtn">Tidak</button>
                    </div>
                </div>
            `;
            
            window.KSP.showModal('confirmModal', 'Konfirmasi', content, 'sm');
            
            $('#confirmBtn').off('click').on('click', function() {
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
                window.KSP.hideModal('confirmModal');
            });
            
            $('#cancelBtn').off('click').on('click', function() {
                if (typeof onCancel === 'function') {
                    onCancel();
                }
                window.KSP.hideModal('confirmModal');
            });
        }
    },
    
    // Templates
    templates: {
        memberDetails: function(data) {
            return `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Pribadi</h6>
                        <table class="table table-sm">
                            <tr><td>No Anggota</td><td>${data.no_anggota || '-'}</td></tr>
                            <tr><td>Nama Lengkap</td><td>${data.nama_lengkap}</td></tr>
                            <tr><td>NIK</td><td>${data.nik || '-'}</td></tr>
                            <tr><td>Jenis Kelamin</td><td>${data.jenis_kelamin || '-'}</td></tr>
                            <tr><td>Tanggal Lahir</td><td>${data.tanggal_lahir || '-'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Informasi Kontak</h6>
                        <table class="table table-sm">
                            <tr><td>Alamat</td><td>${data.alamat || '-'}</td></tr>
                            <tr><td>Provinsi</td><td>${data.province_name || '-'}</td></tr>
                            <tr><td>Kabupaten</td><td>${data.regency_name || '-'}</td></tr>
                            <tr><td>Kecamatan</td><td>${data.district_name || '-'}</td></tr>
                            <tr><td>Desa</td><td>${data.village_name || '-'}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6>Informasi Keuangan</h6>
                        <table class="table table-sm">
                            <tr><td>Total Simpanan</td><td>Rp ${window.KSP.formatCurrency(data.total_savings || 0)}</td></tr>
                            <tr><td>Status</td><td><span class="badge bg-${data.status === 'aktif' ? 'success' : 'secondary'}">${data.status}</span></td></tr>
                        </table>
                    </div>
                </div>
            `;
        },
        
        loanDetails: function(data) {
            return `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Pinjaman</h6>
                        <table class="table table-sm">
                            <tr><td>No Pinjaman</td><td>${data.no_pinjaman}</td></tr>
                            <tr><td>Nama Anggota</td><td>${data.nama_lengkap}</td></tr>
                            <tr><td>Jumlah Pinjaman</td><td>Rp ${window.KSP.formatCurrency(data.jumlah_pinjaman)}</td></tr>
                            <tr><td>Bunga (%)</td><td>${data.bunga_persen}%</td></tr>
                            <tr><td>Tenor</td><td>${data.tenor_bulan} bulan</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Status Pembayaran</h6>
                        <table class="table table-sm">
                            <tr><td>Total Angsuran</td><td>${data.total_installments || 0}</td></tr>
                            <tr><td>Total Dibayar</td><td>Rp ${window.KSP.formatCurrency(data.total_paid || 0)}</td></tr>
                            <tr><td>Sisa Pembayaran</td><td>Rp ${window.KSP.formatCurrency(data.jumlah_pinjaman - (data.total_paid || 0))}</td></tr>
                            <tr><td>Status</td><td><span class="badge bg-${data.status === 'disetujui' ? 'success' : 'warning'}">${data.status}</span></td></tr>
                        </table>
                    </div>
                </div>
            `;
        },
        
        addressSearch: function() {
            return `
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="addressSearch" placeholder="Ketik nama alamat...">
                    <select class="form-select" id="addressType" style="max-width: 150px;">
                        <option value="all">Semua</option>
                        <option value="province">Provinsi</option>
                        <option value="regency">Kabupaten</option>
                        <option value="district">Kecamatan</option>
                        <option value="village">Desa</option>
                    </select>
                    <button class="btn btn-outline-secondary" id="searchAddressBtn">Cari</button>
                </div>
                <div id="addressResults" class="list-group">
                    <div class="text-center text-muted">Ketik untuk mencari alamat</div>
                </div>
            `;
        },
        
        addressResults: function(results) {
            if (results.length === 0) {
                return '<div class="text-center text-muted">Tidak ada hasil ditemukan</div>';
            }
            
            var content = '';
            results.forEach(function(result) {
                var typeIcon = '';
                var typeClass = '';
                
                switch(result.type) {
                    case 'province': typeIcon = 'bi-geo-alt'; typeClass = 'text-primary'; break;
                    case 'regency': typeIcon = 'bi-building'; typeClass = 'text-success'; break;
                    case 'district': typeIcon = 'bi-house'; typeClass = 'text-warning'; break;
                    case 'village': typeIcon = 'bi-tree'; typeClass = 'text-info'; break;
                }
                
                var detail = result.parent_name ? ` - ${result.parent_name}` : '';
                var kodepos = result.kodepos ? ` (${result.kodepos})` : '';
                
                content += `
                    <a href="#" class="list-group-item list-group-item-action" onclick="window.KSP.selectAddress(${result.id}, '${result.type}')">
                        <i class="bi ${typeIcon} ${typeClass} me-2"></i>
                        <strong>${result.name}${kodepos}</strong>${detail}
                        <small class="text-muted ms-2">${result.type}</small>
                    </a>
                `;
            });
            
            return content;
        },
        
        activitySummaryForm: function() {
            return `
                <form class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="summaryStartDate" value="${new Date().toISOString().split('T')[0].substring(0, 8) + '01'}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="summaryEndDate" value="${new Date().toISOString().split('T')[0]}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary d-block" id="loadSummaryBtn">Muat Ringkasan</button>
                    </div>
                </form>
                <div id="activitySummaryContent">
                    <div class="text-center">
                        <div class="spinner-border"></div>
                    </div>
                </div>
            `;
        },
        
        activitySummary: function(data) {
            return `
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-primary">${data.total_transactions}</h5>
                                <p class="card-text">Total Transaksi</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-success">Rp ${window.KSP.formatCurrency(data.total_credit)}</h5>
                                <p class="card-text">Total Kredit</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-danger">Rp ${window.KSP.formatCurrency(data.total_debit)}</h5>
                                <p class="card-text">Total Debit</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-info">${data.active_members}</h5>
                                <p class="card-text">Anggota Aktif</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
    },
    
    // Utility functions
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    },
    
    showToast: function(message, type) {
        type = type || 'info';
        var toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        var toastContainer = $('#toastContainer');
        if (toastContainer.length === 0) {
            toastContainer = $('<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>');
            $('body').append(toastContainer);
        }
        
        var toastElement = $(toastHtml);
        toastContainer.append(toastElement);
        
        var toast = new bootstrap.Toast(toastElement[0]);
        toast.show();
        
        toastElement.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    },
    
    showSuccess: function(message) {
        this.showToast(message, 'success');
    },
    
    showError: function(message) {
        this.showToast(message, 'error');
    },
    
    showWarning: function(message) {
        this.showToast(message, 'warning');
    },
    
    showInfo: function(message) {
        this.showToast(message, 'info');
    },
    
    // Form helpers
    serializeForm: function(form) {
        var formData = {};
        $(form).find('input, select, textarea').each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            var type = $(this).attr('type');
            
            if (name) {
                if (type === 'checkbox' || type === 'radio') {
                    formData[name] = $(this).is(':checked');
                } else {
                    formData[name] = value;
                }
            }
        });
        return formData;
    },
    
    resetForm: function(form) {
        $(form)[0].reset();
        $(form).find('.is-invalid').removeClass('is-invalid');
        $(form).find('.invalid-feedback').remove();
    },
    
    // Address selection
    selectAddress: function(id, type) {
        // This function will be called when user selects an address from search results
        // Override this in your specific implementation
        console.log('Selected address:', id, type);
        window.KSP.hideModal('addressSearchModal');
    },
    
    // Setup event listeners
    setupEventListeners: function() {
        // Auto-save forms
        $('form[data-auto-save]').off('change').on('change', 'input, select, textarea', function() {
            var form = $(this).closest('form');
            var formData = window.KSP.serializeForm(form);
            
            // Debounce auto-save
            clearTimeout(window.KSP.autoSaveTimeout);
            window.KSP.autoSaveTimeout = setTimeout(function() {
                // Implement auto-save logic here
                console.log('Auto-saving form:', formData);
            }, 1000);
        });
        
        // Confirm delete actions
        $('[data-confirm]').off('click').on('click', function(e) {
            e.preventDefault();
            var message = $(this).data('confirm');
            var action = $(this).attr('href') || $(this).data('action');
            
            window.KSP.modals.confirm(message, function() {
                if (action) {
                    window.location.href = action;
                }
            });
        });
        
        // Dynamic address dropdowns
        $('[data-address-cascade]').off('change').on('change', function() {
            var target = $(this).data('address-cascade');
            var value = $(this).val();
            
            if (target && value) {
                window.KSP.loadAddressOptions(target, value);
            }
        });
    },
    
    // Load address options for cascading dropdowns
    loadAddressOptions: function(target, parentId) {
        var targetSelect = $('#' + target);
        var currentValue = targetSelect.val();
        
        targetSelect.html('<option value="">Loading...</option>');
        
        var apiMethod = '';
        switch(target) {
            case 'regency_id': apiMethod = 'getRegencies'; break;
            case 'district_id': apiMethod = 'getDistricts'; break;
            case 'village_id': apiMethod = 'getVillages'; break;
        }
        
        if (apiMethod && window.KSP.api[apiMethod]) {
            window.KSP.api[apiMethod](parentId, function(options) {
                var html = '<option value="">Pilih...</option>';
                options.forEach(function(option) {
                    html += `<option value="${option.id}" ${option.id == currentValue ? 'selected' : ''}>${option.name}</option>`;
                });
                targetSelect.html(html);
            });
        }
    },
    
    // Setup tooltips
    setupTooltips: function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },
    
    // Setup modals
    setupModals: function() {
        // Handle modal shown events
        $('.modal').off('shown.bs.modal').on('shown.bs.modal', function() {
            // Focus first input when modal is shown
            var firstInput = $(this).find('input:visible:first');
            if (firstInput.length) {
                firstInput.focus();
            }
        });
        
        // Handle modal hidden events
        $('.modal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
            // Reset form when modal is hidden
            $(this).find('form')[0]?.reset();
        });
    },
    
    // Loading states
    showLoading: function(element, text) {
        text = text || 'Loading...';
        var loadingHtml = `
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                ${text}
            </div>
        `;
        
        if (element) {
            $(element).html(loadingHtml).prop('disabled', true);
        }
    },
    
    hideLoading: function(element, originalContent) {
        if (element) {
            $(element).html(originalContent).prop('disabled', false);
        }
    },
    
    // Pagination
    setupPagination: function(container, data, callback) {
        var pagination = $(container).find('.pagination');
        var itemsPerPage = 10;
        var currentPage = 1;
        var totalPages = Math.ceil(data.length / itemsPerPage);
        
        function renderPagination() {
            var html = '';
            
            // Previous button
            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
            </li>`;
            
            // Page numbers
            for (var i = 1; i <= totalPages; i++) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
            }
            
            // Next button
            html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>`;
            
            pagination.html(html);
            
            // Show current page data
            var start = (currentPage - 1) * itemsPerPage;
            var end = start + itemsPerPage;
            var pageData = data.slice(start, end);
            
            if (typeof callback === 'function') {
                callback(pageData, currentPage);
            }
        }
        
        // Handle pagination clicks
        pagination.off('click').on('click', '.page-link', function(e) {
            e.preventDefault();
            var page = parseInt($(this).data('page'));
            
            if (page >= 1 && page <= totalPages && page !== currentPage) {
                currentPage = page;
                renderPagination();
            }
        });
        
        renderPagination();
    }
};

// Initialize on document ready
$(document).ready(function() {
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
