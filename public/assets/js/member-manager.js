/**
 * KSP Samosir Member Management Module
 * Implements member CRUD operations using templates and components
 */

(function($, window, document) {
    'use strict';

    // Member Management Module
    const MemberManager = {
        tableId: 'members-table',
        modalId: 'member-modal',

        init: function() {
            this.bindEvents();
            this.createDataTable();
            this.createModal();
        },

        bindEvents: function() {
            const self = this;

            // Add member button
            $(document).on('click', `#${this.tableId}-add`, function() {
                self.showAddForm();
            });

            // Edit member buttons
            $(document).on('click', '.edit-member', function() {
                const memberId = $(this).data('id');
                self.showEditForm(memberId);
            });

            // Delete member buttons
            $(document).on('click', '.delete-member', function() {
                const memberId = $(this).data('id');
                self.confirmDelete(memberId);
            });

            // Modal form submission
            window.KSP.ModalManager.onSubmit(this.modalId, function() {
                self.saveMember();
            });
        },

        createDataTable: function() {
            const self = this;

            window.KSP.DataTable.create('members-container', {
                title: 'Anggota Koperasi',
                tableId: this.tableId,
                searchPlaceholder: 'Cari nama anggota, nomor anggota, atau KTP...',
                columns: [
                    { field: 'no_anggota', label: 'No. Anggota' },
                    { field: 'nama_lengkap', label: 'Nama Lengkap' },
                    { field: 'no_ktp', label: 'No. KTP' },
                    {
                        field: 'status',
                        label: 'Status',
                        render: function(item) {
                            const badgeClass = item.status === 'aktif' ? 'success' : 'secondary';
                            return `<span class="badge badge-status-${item.status}">aktif</span>`;
                        }
                    },
                    {
                        field: 'tanggal_gabung',
                        label: 'Tanggal Daftar',
                        render: function(item) {
                            // Use centralized helper if available, otherwise fallback
                            if (window.KSP && window.KSP.Helpers && window.KSP.Helpers.formatDate) {
                                return window.KSP.Helpers.formatDate(item.tanggal_gabung);
                            }
                            
                            // Fallback formatting
                            if (!item.tanggal_gabung) return '-';
                            
                            try {
                                let date = new Date(item.tanggal_gabung);
                                if (isNaN(date.getTime())) {
                                    return item.tanggal_gabung;
                                }
                                return date.toLocaleDateString('id-ID', {
                                    day: '2-digit',
                                    month: '2-digit', 
                                    year: 'numeric'
                                });
                            } catch (error) {
                                console.warn('Date formatting error:', error);
                                return item.tanggal_gabung || '-';
                            }
                        }
                    }
                ],
                actions: [
                    {
                        label: 'Edit',
                        icon: 'pencil',
                        variant: 'primary',
                        handler: 'MemberManager.showEditForm'
                    },
                    {
                        label: 'Hapus',
                        icon: 'trash',
                        variant: 'danger',
                        handler: 'MemberManager.confirmDelete'
                    }
                ],
                dataUrl: '/ksp_samosir/api/v1/members'
            });
        },

        createModal: function() {
            window.KSP.ModalManager.create(this.modalId, {
                title: 'Tambah Anggota',
                content: '<div id="member-form-container"><div class="text-center"><div class="spinner-border" role="status"></div></div></div>',
                size: 'modal-lg',
                cancelText: 'Batal',
                submitText: 'Simpan'
            });
        },

        showAddForm: function() {
            this.currentMemberId = null;

            window.KSP.TemplateLoader.load('member-form')
                .then(template => {
                    const rendered = window.KSP.TemplateLoader.render(template, {
                        modalTitle: 'Tambah Anggota Baru',
                        submitText: 'Simpan'
                    });
                    $('#member-form-container').html(rendered);

                    // Update modal title
                    window.KSP.ModalManager.modals.get(this.modalId).config.title = 'Tambah Anggota Baru';
                    $(`#${this.modalId}Label`).text('Tambah Anggota Baru');

                    window.KSP.ModalManager.show(this.modalId);
                })
                .catch(error => {
                    KSP.showError('Failed to load form template');
                    console.error(error);
                });
        },

        showEditForm: async function(memberId) {
            try {
                // Show loading
                $('#member-form-container').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
                window.KSP.ModalManager.show(this.modalId);

                // Load member data
                const member = await window.KSP.api.getMember(memberId);

                // Load and render form template
                const template = await window.KSP.TemplateLoader.load('member-form');
                const rendered = window.KSP.TemplateLoader.render(template, {
                    ...member,
                    tanggal_lahir: member.tanggal_lahir ? member.tanggal_lahir.split('T')[0] : '',
                    jenis_kelamin_selected_L: member.jenis_kelamin === 'L' ? 'selected' : '',
                    jenis_kelamin_selected_P: member.jenis_kelamin === 'P' ? 'selected' : '',
                    modalTitle: 'Edit Anggota',
                    submitText: 'Update'
                });

                $('#member-form-container').html(rendered);

                // Update modal title
                window.KSP.ModalManager.modals.get(this.modalId).config.title = 'Edit Anggota';
                $(`#${this.modalId}Label`).text('Edit Anggota');

                this.currentMemberId = memberId;

            } catch (error) {
                KSP.showError('Failed to load member data: ' + error.message);
                window.KSP.ModalManager.hide(this.modalId);
            }
        },

        saveMember: async function() {
            try {
                const formData = this.getFormData();

                if (!this.validateForm(formData)) {
                    return;
                }

                // Show loading on submit button
                const submitBtn = $(`#${this.modalId}-submit`);
                const originalHtml = submitBtn.html();
                submitBtn.html('<i class="bi bi-hourglass me-1"></i>Menyimpan...').prop('disabled', true);

                let result;
                if (this.currentMemberId) {
                    // Update existing member
                    result = await window.KSP.api.updateMember(this.currentMemberId, formData);
                } else {
                    // Create new member
                    result = await window.KSP.api.createMember(formData);
                }

                // Success
                KSP.showSuccess(result.message || 'Member saved successfully');

                // Close modal and refresh table
                window.KSP.ModalManager.hide(this.modalId);

                // Refresh the data table
                if (window.KSP.DataTable && window.KSP.DataTable.instances) {
                    // Find the members table instance and refresh
                    Object.values(window.KSP.DataTable.instances).forEach(instance => {
                        if (instance.config.tableId === this.tableId) {
                            instance.loadData();
                        }
                    });
                }

            } catch (error) {
                KSP.showError('Failed to save member: ' + error.message);
            } finally {
                // Restore submit button
                const submitBtn = $(`#${this.modalId}-submit`);
                submitBtn.html(originalHtml).prop('disabled', false);
            }
        },

        confirmDelete: function(memberId) {
            // Simple confirmation dialog
            if (confirm('Apakah Anda yakin ingin menghapus anggota ini?')) {
                this.deleteMember(memberId);
            }
        },

        deleteMember: async function(memberId) {
            try {
                const result = await window.KSP.api.deleteMember(memberId);
                KSP.showSuccess(result.message || 'Member deleted successfully');

                // Refresh table
                if (window.KSP.DataTable && window.KSP.DataTable.instances) {
                    Object.values(window.KSP.DataTable.instances).forEach(instance => {
                        if (instance.config.tableId === this.tableId) {
                            instance.loadData();
                        }
                    });
                }

            } catch (error) {
                KSP.showError('Failed to delete member: ' + error.message);
            }
        },

        getFormData: function() {
            const form = $('#member-form');
            return {
                nama_lengkap: form.find('#nama_lengkap').val(),
                no_ktp: form.find('#no_ktp').val(),
                tanggal_lahir: form.find('#tanggal_lahir').val(),
                jenis_kelamin: form.find('#jenis_kelamin').val(),
                alamat: form.find('#alamat').val(),
                no_telepon: form.find('#no_telepon').val(),
                email: form.find('#email').val(),
                pekerjaan: form.find('#pekerjaan').val(),
                pendapatan_bulanan: form.find('#pendapatan_bulanan').val()
            };
        },

        validateForm: function(data) {
            // Basic validation
            if (!data.nama_lengkap || !data.nama_lengkap.trim()) {
                KSP.showError('Nama lengkap wajib diisi');
                return false;
            }

            if (!data.no_ktp || !data.no_ktp.trim()) {
                KSP.showError('Nomor KTP wajib diisi');
                return false;
            }

            if (!data.alamat || !data.alamat.trim()) {
                KSP.showError('Alamat wajib diisi');
                return false;
            }

            // KTP validation (16 digits)
            if (!/^\d{16}$/.test(data.no_ktp)) {
                KSP.showError('Nomor KTP harus 16 digit angka');
                return false;
            }

            return true;
        }
    };

    // Make globally available
    window.MemberManager = MemberManager;

    // Auto-initialize when DOM is ready
    $(document).ready(function() {
        if ($('#members-container').length > 0) {
            MemberManager.init();
        }
    });

})(jQuery, window, document);
