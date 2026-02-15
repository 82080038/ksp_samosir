/**
 * KSP UI - Unified Bootstrap 5.3 + jQuery Component Library
 * Consolidates: modals, toasts, alerts, tables, forms, confirm dialogs, AJAX
 * 
 * Usage:
 *   KSP.toast.success('Data berhasil disimpan');
 *   KSP.toast.error('Gagal menyimpan data');
 *   KSP.modal.form({ title:'Tambah Anggota', url:'/anggota/create', onSuccess:fn });
 *   KSP.modal.confirm('Hapus data ini?', function(){ ... });
 *   KSP.modal.show({ title:'Detail', body:'<p>...</p>', size:'lg' });
 *   KSP.table.init('#myTable', { search:true, sort:true, pagination:true });
 *   KSP.ajax.post('/api/save', data).then(fn);
 */
(function($, bootstrap) {
    'use strict';

    if (typeof $ === 'undefined') {
        console.warn('KSP UI requires jQuery');
        return;
    }

    window.KSP = window.KSP || {};

    // =========================================================================
    // TOAST NOTIFICATIONS (Bootstrap 5.3 Toast)
    // =========================================================================
    KSP.toast = {
        _container: null,
        _defaults: { delay: 4000, position: 'top-0 end-0', maxVisible: 5 },

        _ensureContainer: function() {
            if (!this._container || !this._container.length) {
                this._container = $('#ksp-toast-container');
                if (!this._container.length) {
                    this._container = $('<div id="ksp-toast-container" class="toast-container position-fixed p-3" style="z-index:1090"></div>')
                        .addClass(this._defaults.position)
                        .appendTo('body');
                }
            }
            return this._container;
        },

        show: function(message, opts) {
            opts = $.extend({ type: 'info', title: '', delay: this._defaults.delay, icon: '' }, opts);
            var container = this._ensureContainer();

            var icons = {
                success: 'bi-check-circle-fill text-success',
                error:   'bi-x-circle-fill text-danger',
                danger:  'bi-x-circle-fill text-danger',
                warning: 'bi-exclamation-triangle-fill text-warning',
                info:    'bi-info-circle-fill text-primary'
            };
            var icon = opts.icon || icons[opts.type] || icons.info;
            var title = opts.title || { success:'Berhasil', error:'Error', danger:'Error', warning:'Peringatan', info:'Info' }[opts.type] || 'Info';
            var borderClass = 'border-' + (opts.type === 'error' ? 'danger' : opts.type);

            var html =
                '<div class="toast align-items-center ' + borderClass + ' border-start border-4" role="alert" aria-live="assertive" aria-atomic="true">' +
                '  <div class="toast-header">' +
                '    <i class="bi ' + icon + ' me-2"></i>' +
                '    <strong class="me-auto">' + title + '</strong>' +
                '    <small class="text-body-secondary">baru saja</small>' +
                '    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>' +
                '  </div>' +
                '  <div class="toast-body">' + message + '</div>' +
                '</div>';

            var $toast = $(html).appendTo(container);

            // Enforce max visible
            var toasts = container.find('.toast');
            if (toasts.length > this._defaults.maxVisible) {
                toasts.first().remove();
            }

            var bsToast = new bootstrap.Toast($toast[0], { delay: opts.delay, autohide: true });
            $toast.on('hidden.bs.toast', function() { $(this).remove(); });
            bsToast.show();
            return bsToast;
        },

        success: function(msg, title) { return this.show(msg, { type:'success', title: title }); },
        error:   function(msg, title) { return this.show(msg, { type:'error',   title: title }); },
        warning: function(msg, title) { return this.show(msg, { type:'warning', title: title }); },
        info:    function(msg, title) { return this.show(msg, { type:'info',    title: title }); }
    };

    // =========================================================================
    // ALERT INLINE (Bootstrap 5.3 Alert - dismissible, auto-hide)
    // =========================================================================
    KSP.alert = {
        show: function(message, opts) {
            opts = $.extend({ type: 'info', target: '#page-header', position: 'after', dismiss: true, autoHide: 5000, icon: true }, opts);

            var icons = { success:'bi-check-circle', error:'bi-exclamation-triangle', danger:'bi-exclamation-triangle', warning:'bi-exclamation-triangle', info:'bi-info-circle' };
            var alertType = opts.type === 'error' ? 'danger' : opts.type;
            var iconHtml = opts.icon ? '<i class="bi ' + (icons[opts.type] || icons.info) + ' me-2"></i>' : '';
            var dismissHtml = opts.dismiss ? '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' : '';

            var html = '<div class="alert alert-' + alertType + ' alert-dismissible fade show" role="alert">' +
                       iconHtml + message + dismissHtml + '</div>';
            var $alert = $(html).hide();

            if (opts.position === 'after') $(opts.target).after($alert);
            else if (opts.position === 'before') $(opts.target).before($alert);
            else if (opts.position === 'prepend') $(opts.target).prepend($alert);
            else $(opts.target).append($alert);

            $alert.slideDown(200);

            if (opts.autoHide) {
                setTimeout(function() { $alert.fadeOut(300, function() { $(this).remove(); }); }, opts.autoHide);
            }
            return $alert;
        },
        success: function(msg, opts) { return this.show(msg, $.extend({ type:'success' }, opts)); },
        error:   function(msg, opts) { return this.show(msg, $.extend({ type:'error' }, opts)); },
        warning: function(msg, opts) { return this.show(msg, $.extend({ type:'warning' }, opts)); },
        info:    function(msg, opts) { return this.show(msg, $.extend({ type:'info' }, opts)); }
    };

    // =========================================================================
    // MODAL SYSTEM (Bootstrap 5.3 Modal - dynamic, form, confirm, detail)
    // =========================================================================
    KSP.modal = {
        _stack: [],

        // Generic modal
        show: function(opts) {
            opts = $.extend({
                id: 'ksp-modal-' + Date.now(),
                title: 'Dialog',
                body: '',
                footer: null,       // null = default close button, string = custom HTML, false = no footer
                size: '',           // '', 'sm', 'lg', 'xl', 'fullscreen'
                centered: true,
                scrollable: true,
                backdrop: true,
                keyboard: true,
                onShow: null,
                onHide: null,
                onShown: null
            }, opts);

            var sizeClass = opts.size ? ' modal-' + opts.size : '';
            var centeredClass = opts.centered ? ' modal-dialog-centered' : '';
            var scrollClass = opts.scrollable ? ' modal-dialog-scrollable' : '';

            var footerHtml = '';
            if (opts.footer === false) {
                footerHtml = '';
            } else if (opts.footer) {
                footerHtml = '<div class="modal-footer">' + opts.footer + '</div>';
            } else {
                footerHtml = '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>';
            }

            var html =
                '<div class="modal fade" id="' + opts.id + '" tabindex="-1" aria-hidden="true">' +
                '  <div class="modal-dialog' + sizeClass + centeredClass + scrollClass + '">' +
                '    <div class="modal-content">' +
                '      <div class="modal-header">' +
                '        <h5 class="modal-title"><i class="bi bi-window me-2"></i>' + opts.title + '</h5>' +
                '        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                '      </div>' +
                '      <div class="modal-body">' + opts.body + '</div>' +
                footerHtml +
                '    </div>' +
                '  </div>' +
                '</div>';

            var $modal = $(html).appendTo('body');
            var bsModal = new bootstrap.Modal($modal[0], { backdrop: opts.backdrop, keyboard: opts.keyboard });

            if (opts.onShow)  $modal.on('show.bs.modal', opts.onShow);
            if (opts.onShown) $modal.on('shown.bs.modal', opts.onShown);
            if (opts.onHide)  $modal.on('hide.bs.modal', opts.onHide);
            $modal.on('hidden.bs.modal', function() { $(this).remove(); });

            bsModal.show();
            this._stack.push(bsModal);
            return { el: $modal, instance: bsModal };
        },

        // Confirm dialog
        confirm: function(message, onConfirm, opts) {
            opts = $.extend({ title: 'Konfirmasi', type: 'warning', confirmText: 'Ya, Lanjutkan', cancelText: 'Batal', confirmClass: 'btn-danger' }, opts);
            var icons = { warning:'bi-exclamation-triangle text-warning', danger:'bi-x-circle text-danger', info:'bi-question-circle text-primary' };
            var icon = icons[opts.type] || icons.warning;

            var body = '<div class="text-center py-3">' +
                       '<i class="bi ' + icon + '" style="font-size:3rem"></i>' +
                       '<p class="mt-3 mb-0 fs-5">' + message + '</p></div>';

            var footer = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' + opts.cancelText + '</button>' +
                         '<button type="button" class="btn ' + opts.confirmClass + '" id="ksp-confirm-btn">' + opts.confirmText + '</button>';

            var m = this.show({ title: opts.title, body: body, footer: footer, size: 'sm', centered: true });
            m.el.find('#ksp-confirm-btn').on('click', function() {
                m.instance.hide();
                if (typeof onConfirm === 'function') onConfirm();
            });
            return m;
        },

        // Form in modal (AJAX-loaded or inline HTML)
        form: function(opts) {
            opts = $.extend({
                title: 'Form',
                url: null,          // AJAX URL to load form content
                html: null,         // inline form HTML (if no URL)
                size: 'lg',
                method: 'POST',
                submitUrl: null,    // URL to submit form (defaults to opts.url)
                submitText: 'Simpan',
                cancelText: 'Batal',
                onSuccess: null,
                onError: null,
                validate: true
            }, opts);

            var body = opts.html || '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Memuat form...</p></div>';

            var footer =
                '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' + opts.cancelText + '</button>' +
                '<button type="button" class="btn btn-primary" id="ksp-form-submit">' +
                '<i class="bi bi-check-circle me-1"></i>' + opts.submitText + '</button>';

            var m = this.show({ title: opts.title, body: body, footer: footer, size: opts.size });

            // Load form via AJAX if URL provided
            if (opts.url && !opts.html) {
                $.get(opts.url).done(function(html) {
                    m.el.find('.modal-body').html(html);
                    KSP.form.enhance(m.el.find('form'));
                }).fail(function() {
                    m.el.find('.modal-body').html('<div class="alert alert-danger">Gagal memuat form</div>');
                });
            } else {
                KSP.form.enhance(m.el.find('form'));
            }

            // Handle submit
            m.el.find('#ksp-form-submit').on('click', function() {
                var $btn = $(this);
                var $form = m.el.find('form');
                if (!$form.length) return;

                // Bootstrap validation
                if (opts.validate && !$form[0].checkValidity()) {
                    $form.addClass('was-validated');
                    return;
                }

                var data = $form.serialize();
                var submitUrl = opts.submitUrl || opts.url || $form.attr('action');
                var method = $form.attr('method') || opts.method;

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

                $.ajax({ url: submitUrl, type: method, data: data, dataType: 'json' })
                    .done(function(res) {
                        m.instance.hide();
                        if (res.success !== false) {
                            KSP.toast.success(res.message || 'Data berhasil disimpan');
                            if (opts.onSuccess) opts.onSuccess(res);
                        } else {
                            KSP.toast.error(res.message || 'Gagal menyimpan data');
                            if (opts.onError) opts.onError(res);
                        }
                    })
                    .fail(function(xhr) {
                        $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>' + opts.submitText);
                        KSP.toast.error('Request gagal: ' + (xhr.statusText || 'Unknown error'));
                        if (opts.onError) opts.onError(xhr);
                    });
            });

            return m;
        },

        // Detail view modal (read-only)
        detail: function(opts) {
            opts = $.extend({ title: 'Detail', url: null, html: null, size: 'lg' }, opts);
            var body = opts.html || '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
            var m = this.show({ title: opts.title, body: body, size: opts.size });

            if (opts.url && !opts.html) {
                $.get(opts.url).done(function(html) {
                    m.el.find('.modal-body').html(html);
                }).fail(function() {
                    m.el.find('.modal-body').html('<div class="alert alert-danger">Gagal memuat data</div>');
                });
            }
            return m;
        },

        // Close all modals
        closeAll: function() {
            this._stack.forEach(function(m) { try { m.hide(); } catch(e){} });
            this._stack = [];
        }
    };

    // =========================================================================
    // FORM HELPERS (Bootstrap 5.3 Validation + enhancement)
    // =========================================================================
    KSP.form = {
        // Enhance form with Bootstrap validation classes
        enhance: function($form) {
            if (!$form || !$form.length) return;
            $form.attr('novalidate', true);
            $form.find('.form-control, .form-select').each(function() {
                var $input = $(this);
                if ($input.prop('required') && !$input.next('.invalid-feedback').length) {
                    $input.after('<div class="invalid-feedback">Field ini wajib diisi</div>');
                }
            });
        },

        // Serialize form to object
        toObject: function($form) {
            var obj = {};
            $form.serializeArray().forEach(function(item) { obj[item.name] = item.value; });
            return obj;
        },

        // Reset form and validation state
        reset: function($form) {
            $form[0].reset();
            $form.removeClass('was-validated');
            $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        },

        // Submit form via AJAX with loading state
        submit: function($form, opts) {
            opts = $.extend({ url: $form.attr('action'), method: $form.attr('method') || 'POST', onSuccess: null, onError: null }, opts);

            if (!$form[0].checkValidity()) {
                $form.addClass('was-validated');
                return $.Deferred().reject('validation');
            }

            var $btn = $form.find('[type="submit"]');
            var origHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Loading...');

            return $.ajax({ url: opts.url, type: opts.method, data: $form.serialize(), dataType: 'json' })
                .done(function(res) {
                    if (res.success !== false) {
                        KSP.toast.success(res.message || 'Berhasil');
                        if (opts.onSuccess) opts.onSuccess(res);
                    } else {
                        KSP.toast.error(res.message || 'Gagal');
                        if (opts.onError) opts.onError(res);
                    }
                })
                .fail(function(xhr) {
                    KSP.toast.error('Request gagal');
                    if (opts.onError) opts.onError(xhr);
                })
                .always(function() {
                    $btn.prop('disabled', false).html(origHtml);
                });
        }
    };

    // =========================================================================
    // TABLE HELPERS (Enhanced Bootstrap 5.3 Table)
    // =========================================================================
    KSP.table = {
        init: function(selector, opts) {
            opts = $.extend({ search: true, sort: true, hover: true, striped: true, responsive: true, emptyText: 'Tidak ada data' }, opts);
            var $table = $(selector);
            if (!$table.length) return;

            // Add Bootstrap classes
            $table.addClass('table');
            if (opts.hover) $table.addClass('table-hover');
            if (opts.striped) $table.addClass('table-striped');

            // Wrap in responsive container
            if (opts.responsive && !$table.parent('.table-responsive').length) {
                $table.wrap('<div class="table-responsive"></div>');
            }

            // Client-side search
            if (opts.search) {
                var $search = $('<div class="mb-3"><div class="input-group input-group-sm">' +
                    '<span class="input-group-text"><i class="bi bi-search"></i></span>' +
                    '<input type="text" class="form-control" placeholder="Cari..."></div></div>');
                $table.closest('.table-responsive').before($search);

                $search.find('input').on('keyup', function() {
                    var term = $(this).val().toLowerCase();
                    $table.find('tbody tr').each(function() {
                        var text = $(this).text().toLowerCase();
                        $(this).toggle(text.indexOf(term) > -1);
                    });
                });
            }

            // Client-side sort
            if (opts.sort) {
                $table.find('thead th').css('cursor', 'pointer').on('click', function() {
                    var idx = $(this).index();
                    var asc = !$(this).hasClass('sort-asc');
                    $table.find('thead th').removeClass('sort-asc sort-desc');
                    $(this).addClass(asc ? 'sort-asc' : 'sort-desc');

                    var rows = $table.find('tbody tr').toArray().sort(function(a, b) {
                        var va = $(a).children('td').eq(idx).text().trim();
                        var vb = $(b).children('td').eq(idx).text().trim();
                        var na = parseFloat(va.replace(/[^0-9.-]/g, ''));
                        var nb = parseFloat(vb.replace(/[^0-9.-]/g, ''));
                        if (!isNaN(na) && !isNaN(nb)) return asc ? na - nb : nb - na;
                        return asc ? va.localeCompare(vb) : vb.localeCompare(va);
                    });
                    $table.find('tbody').html(rows);
                });
            }

            // Empty state
            if ($table.find('tbody tr').length === 0) {
                var cols = $table.find('thead th').length || 1;
                $table.find('tbody').html('<tr><td colspan="' + cols + '" class="text-center text-muted py-4"><i class="bi bi-inbox" style="font-size:2rem"></i><br>' + opts.emptyText + '</td></tr>');
            }

            return $table;
        }
    };

    // =========================================================================
    // AJAX HELPERS (jQuery AJAX wrapper with toast feedback)
    // =========================================================================
    KSP.ajax = {
        _defaults: {
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        },

        request: function(url, opts) {
            opts = $.extend({}, this._defaults, { url: url }, opts);
            return $.ajax(opts).fail(function(xhr) {
                if (xhr.status === 401) KSP.toast.error('Sesi habis, silakan login ulang');
                else if (xhr.status === 403) KSP.toast.error('Akses ditolak');
                else if (xhr.status === 404) KSP.toast.error('Data tidak ditemukan');
                else if (xhr.status >= 500) KSP.toast.error('Terjadi kesalahan server');
            });
        },

        get:    function(url, data) { return this.request(url, { type: 'GET', data: data }); },
        post:   function(url, data) { return this.request(url, { type: 'POST', data: data }); },
        put:    function(url, data) { return this.request(url, { type: 'PUT', data: data }); },
        delete: function(url, data) { return this.request(url, { type: 'DELETE', data: data }); },

        // Delete with confirmation
        confirmDelete: function(url, message, onSuccess) {
            KSP.modal.confirm(message || 'Yakin ingin menghapus data ini?', function() {
                KSP.ajax.delete(url).done(function(res) {
                    if (res.success !== false) {
                        KSP.toast.success(res.message || 'Data berhasil dihapus');
                        if (onSuccess) onSuccess(res);
                    } else {
                        KSP.toast.error(res.message || 'Gagal menghapus');
                    }
                });
            });
        }
    };

    // =========================================================================
    // LOADING HELPERS
    // =========================================================================
    KSP.loading = {
        show: function(selector) {
            var $el = $(selector || '#main-content');
            if (!$el.find('.ksp-loading-overlay').length) {
                $el.css('position', 'relative').append(
                    '<div class="ksp-loading-overlay"><div class="spinner-border text-primary"></div></div>'
                );
            }
        },
        hide: function(selector) {
            $(selector || '#main-content').find('.ksp-loading-overlay').fadeOut(200, function() { $(this).remove(); });
        }
    };

    // =========================================================================
    // PAGE HELPERS
    // =========================================================================
    KSP.page = {
        setTitle: function(title, dataPage) {
            $('#page-title').text(title).attr('data-page', dataPage || title.toLowerCase().replace(/\s+/g, '-'));
            document.title = title + ' - KSP Samosir';
        },

        updateSidebar: function() {
            var page = $('#page-title').data('page');
            if (page) {
                $('.sidebar .nav-link').removeClass('active');
                $('.sidebar .nav-link[data-page="' + page + '"]').addClass('active');
            }
        }
    };

    // =========================================================================
    // AUTO-INIT on DOM Ready
    // =========================================================================
    $(function() {
        // Init tooltips
        $('[data-bs-toggle="tooltip"]').each(function() {
            new bootstrap.Tooltip(this);
        });

        // Init popovers
        $('[data-bs-toggle="popover"]').each(function() {
            new bootstrap.Popover(this);
        });

        // Update sidebar active state
        KSP.page.updateSidebar();

        // Global AJAX setup
        $.ajaxSetup({
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        // Global AJAX error handler
        $(document).ajaxError(function(e, xhr) {
            if (xhr.status === 401) {
                KSP.toast.error('Sesi habis, silakan login ulang');
            }
        });

        // Auto-enhance forms with [data-ksp-form]
        $('form[data-ksp-form]').each(function() {
            KSP.form.enhance($(this));
        });

        // Auto-enhance tables with [data-ksp-table]
        $('table[data-ksp-table]').each(function() {
            var opts = $(this).data();
            KSP.table.init(this, opts);
        });

        // Delegated events for data-ksp-* actions
        $(document).on('click', '[data-ksp-confirm]', function(e) {
            e.preventDefault();
            var $el = $(this);
            var msg = $el.data('ksp-confirm');
            var url = $el.attr('href') || $el.data('url');
            var method = $el.data('method') || 'POST';
            KSP.modal.confirm(msg, function() {
                KSP.ajax.request(url, { type: method }).done(function() { location.reload(); });
            });
        });

        $(document).on('click', '[data-ksp-modal-form]', function(e) {
            e.preventDefault();
            var $el = $(this);
            KSP.modal.form({
                title: $el.data('title') || $el.text().trim(),
                url: $el.attr('href') || $el.data('url'),
                size: $el.data('size') || 'lg',
                onSuccess: function() { location.reload(); }
            });
        });

        $(document).on('click', '[data-ksp-detail]', function(e) {
            e.preventDefault();
            var $el = $(this);
            KSP.modal.detail({
                title: $el.data('title') || 'Detail',
                url: $el.attr('href') || $el.data('url'),
                size: $el.data('size') || 'lg'
            });
        });

        console.log('KSP UI initialized');
    });

    // =========================================================================
    // BACKWARD COMPAT - global shortcuts
    // =========================================================================
    window.showSuccess      = function(msg) { KSP.toast.success(msg); };
    window.showError        = function(msg) { KSP.toast.error(msg); };
    window.showWarning      = function(msg) { KSP.toast.warning(msg); };
    window.showInfo         = function(msg) { KSP.toast.info(msg); };
    window.showNotification = function(msg, type) {
        type = type || 'info';
        if (type === 'danger') type = 'error';
        KSP.toast[type] ? KSP.toast[type](msg) : KSP.toast.info(msg);
    };
    window.showConfirm      = function(msg, cb) { KSP.modal.confirm(msg, cb); };

})(jQuery, bootstrap);
