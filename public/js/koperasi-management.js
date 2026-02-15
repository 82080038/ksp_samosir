/**
 * Koperasi Management JavaScript
 * Handles all frontend interactions for KSP Samosir koperasi management
 */

class KoperasiManagement {
    constructor() {
        this.currentPage = 'dashboard';
        this.apiBase = '/api/koperasi';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadDashboard();
        this.loadUnits();
    }

    setupEventListeners() {
        // Menu navigation
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const page = e.currentTarget.dataset.page;
                this.navigateToPage(page);
            });
        });

        // Search functionality
        document.getElementById('searchAnggota')?.addEventListener('input', (e) => {
            this.debounce(() => this.searchAnggota(e.target.value), 500)();
        });
    }

    navigateToPage(page) {
        // Update active menu
        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-page="${page}"]`).classList.add('active');

        // Hide all pages
        document.querySelectorAll('.page-content').forEach(p => {
            p.style.display = 'none';
        });

        // Show selected page
        const targetPage = document.getElementById(`${page}-page`);
        if (targetPage) {
            targetPage.style.display = 'block';
            this.currentPage = page;
            this.loadPageContent(page);
        }
    }

    loadPageContent(page) {
        switch (page) {
            case 'dashboard':
                this.loadDashboard();
                break;
            case 'anggota':
                this.loadAnggota();
                break;
            case 'simpanan':
                this.loadSimpanan();
                break;
            case 'pinjaman':
                this.loadPinjaman();
                break;
            case 'angsuran':
                this.loadAngsuran();
                break;
        }
    }

    async loadDashboard() {
        try {
            const response = await this.apiCall('/dashboard/overview', 'GET');
            if (response.success) {
                this.updateDashboardStats(response.data);
                this.loadPendingPinjaman();
                this.loadJatuhTempo();
            }
        } catch (error) {
            console.error('Error loading dashboard:', error);
            this.showNotification('Gagal memuat dashboard', 'error');
        }
    }

    updateDashboardStats(data) {
        document.getElementById('totalAnggota').textContent = this.formatNumber(data.total_anggota);
        document.getElementById('totalSimpanan').textContent = this.formatCurrency(data.total_simpanan);
        document.getElementById('totalPinjaman').textContent = this.formatCurrency(data.total_pinjaman);
        document.getElementById('angsuranBulanIni').textContent = this.formatCurrency(data.angsuran_bulan_ini);
    }

    async loadPendingPinjaman() {
        try {
            const response = await this.apiCall('/pinjaman', 'GET', { status: 'pending', limit: 5 });
            if (response.success) {
                this.renderPendingPinjamanTable(response.data);
            }
        } catch (error) {
            console.error('Error loading pending pinjaman:', error);
        }
    }

    renderPendingPinjamanTable(data) {
        const tbody = document.getElementById('pendingPinjamanTable');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Tidak ada pinjaman pending</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(pinjaman => `
            <tr>
                <td>${pinjaman.no_anggota}</td>
                <td>${pinjaman.nama_lengkap}</td>
                <td>${this.formatCurrency(pinjaman.jumlah_pinjaman)}</td>
                <td><span class="badge bg-warning">Pending</span></td>
            </tr>
        `).join('');
    }

    async loadJatuhTempo() {
        try {
            const response = await this.apiCall('/pinjaman/jatuh-tempo', 'GET');
            if (response.success) {
                this.renderJatuhTempoTable(response.data);
            }
        } catch (error) {
            console.error('Error loading jatuh tempo:', error);
        }
    }

    renderJatuhTempoTable(data) {
        const tbody = document.getElementById('jatuhTempoTable');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Tidak ada angsuran jatuh tempo</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(pinjaman => `
            <tr>
                <td>${pinjaman.no_anggota}</td>
                <td>${pinjaman.nama_lengkap}</td>
                <td>${this.formatDate(pinjaman.tanggal_jatuh_tempo)}</td>
                <td><span class="badge bg-info">Jatuh Tempo</span></td>
            </tr>
        `).join('');
    }

    async loadAnggota(filters = {}) {
        try {
            this.showLoading('anggotaTable');
            const response = await this.apiCall('/anggota', 'GET', filters);
            if (response.success) {
                this.renderAnggotaTable(response.data);
            }
        } catch (error) {
            console.error('Error loading anggota:', error);
            this.showNotification('Gagal memuat data anggota', 'error');
        }
    }

    renderAnggotaTable(data) {
        const tbody = document.getElementById('anggotaTable');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Tidak ada data anggota</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(anggota => `
            <tr>
                <td>${anggota.no_anggota}</td>
                <td>${anggota.nama_lengkap}</td>
                <td>${anggota.nama_unit || '-'}</td>
                <td>${this.formatCurrency(anggota.total_simpanan)}</td>
                <td>${this.formatCurrency(anggota.total_pinjaman)}</td>
                <td><span class="badge badge-status bg-${this.getStatusColor(anggota.status)}">${anggota.status}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="koperasi.editAnggota(${anggota.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info" onclick="koperasi.viewAnggota(${anggota.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    async loadSimpanan(filters = {}) {
        try {
            const response = await this.apiCall('/simpanan', 'GET', filters);
            if (response.success) {
                this.renderSimpananTable(response.data);
            }
        } catch (error) {
            console.error('Error loading simpanan:', error);
        }
    }

    renderSimpananTable(data) {
        // Implementation for simpanan table
        console.log('Simpanan data:', data);
    }

    async loadPinjaman(filters = {}) {
        try {
            const response = await this.apiCall('/pinjaman', 'GET', filters);
            if (response.success) {
                this.renderPinjamanTable(response.data);
            }
        } catch (error) {
            console.error('Error loading pinjaman:', error);
        }
    }

    renderPinjamanTable(data) {
        // Implementation for pinjaman table
        console.log('Pinjaman data:', data);
    }

    async loadAngsuran(filters = {}) {
        try {
            const response = await this.apiCall('/angsuran', 'GET', filters);
            if (response.success) {
                this.renderAngsuranTable(response.data);
            }
        } catch (error) {
            console.error('Error loading angsuran:', error);
        }
    }

    renderAngsuranTable(data) {
        // Implementation for angsuran table
        console.log('Angsuran data:', data);
    }

    async loadUnits() {
        try {
            const response = await this.apiCall('/units', 'GET');
            if (response.success) {
                this.populateUnitSelects(response.data);
            }
        } catch (error) {
            console.error('Error loading units:', error);
        }
    }

    populateUnitSelects(units) {
        // Ensure units is an array
        if (!Array.isArray(units)) {
            console.warn('Units data is not an array:', units);
            units = [];
        }

        const selects = [
            document.querySelector('select[name="unit_id"]'),
            document.getElementById('filterUnit')
        ];

        selects.forEach(select => {
            if (select) {
                const currentValue = select.value;
                select.innerHTML = '<option value="">Pilih Unit</option>';
                units.forEach(unit => {
                    select.innerHTML += `<option value="${unit.id}">${unit.nama_unit}</option>`;
                });
                select.value = currentValue;
            }
        });
    }

    async saveAnggota() {
        const form = document.getElementById('anggotaForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            this.showButtonLoading(event.target);
            const response = await this.apiCall('/anggota', 'POST', data);
            
            if (response.success) {
                this.showNotification('Anggota berhasil ditambahkan', 'success');
                bootstrap.Modal.getInstance(document.getElementById('anggotaModal')).hide();
                form.reset();
                this.loadAnggota();
                this.loadDashboard();
            } else {
                this.showNotification(response.message || 'Gagal menambah anggota', 'error');
            }
        } catch (error) {
            console.error('Error saving anggota:', error);
            this.showNotification('Terjadi kesalahan, silakan coba lagi', 'error');
        } finally {
            this.hideButtonLoading(event.target);
        }
    }

    async approvePinjaman(pinjamanId) {
        try {
            const response = await this.apiCall(`/pinjaman/${pinjamanId}/approve`, 'POST', {
                tanggal_disetujui: new Date().toISOString().split('T')[0],
                tanggal_cair: new Date().toISOString().split('T')[0]
            });

            if (response.success) {
                this.showNotification('Pinjaman disetujui', 'success');
                this.loadPinjaman();
                this.loadDashboard();
            } else {
                this.showNotification(response.message || 'Gagal menyetujui pinjaman', 'error');
            }
        } catch (error) {
            console.error('Error approving pinjaman:', error);
            this.showNotification('Terjadi kesalahan, silakan coba lagi', 'error');
        }
    }

    // Utility methods
    async apiCall(endpoint, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        if (method === 'GET' && data) {
            const params = new URLSearchParams(data);
            endpoint += '?' + params.toString();
        }

        const response = await fetch(this.apiBase + endpoint, options);
        return await response.json();
    }

    formatNumber(num) {
        if (window.KSP && window.KSP.Helpers && window.KSP.Helpers.formatNumber) {
            return window.KSP.Helpers.formatNumber(num);
        }
        return new Intl.NumberFormat('id-ID').format(num);
    }

    formatCurrency(amount) {
        if (window.KSP && window.KSP.Helpers && window.KSP.Helpers.formatCurrency) {
            return window.KSP.Helpers.formatCurrency(amount);
        }
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('id-ID');
    }

    getStatusColor(status) {
        const colors = {
            'aktif': 'success',
            'nonaktif': 'warning',
            'keluar': 'danger',
            'pending': 'warning',
            'disetujui': 'success',
            'ditolak': 'danger',
            'lunas': 'success',
            'proses': 'info'
        };
        return colors[status] || 'secondary';
    }

    showLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = '<tr><td colspan="100%" class="text-center"><div class="spinner-border text-primary" role="status"></div></td></tr>';
        }
    }

    showButtonLoading(button) {
        if (button) {
            button.disabled = true;
            button.innerHTML = '<span class="loading-spinner"></span> Loading...';
        }
    }

    hideButtonLoading(button) {
        if (button) {
            button.disabled = false;
            button.innerHTML = button.getAttribute('data-original-text') || '<i class="fas fa-save"></i> Simpan';
        }
    }

    showNotification(message, type = 'info') {
        // Create toast notification
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        const toastContainer = document.getElementById('toastContainer') || this.createToastContainer();
        const toastElement = document.createElement('div');
        toastElement.innerHTML = toastHtml;
        toastContainer.appendChild(toastElement.firstElementChild);

        const toast = new bootstrap.Toast(toastContainer.lastElementChild);
        toast.show();
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    searchAnggota(query) {
        this.loadAnggota({ search: query });
    }

    filterAnggota() {
        const status = document.getElementById('filterStatus').value;
        const unit = document.getElementById('filterUnit').value;
        const search = document.getElementById('searchAnggota').value;

        const filters = {};
        if (status) filters.status = status;
        if (unit) filters.unit_id = unit;
        if (search) filters.search = search;

        this.loadAnggota(filters);
    }

    refreshDashboard() {
        this.loadDashboard();
        this.showNotification('Dashboard refreshed', 'success');
    }

    editAnggota(id) {
        // Load anggota data and show edit modal
        console.log('Edit anggota:', id);
    }

    viewAnggota(id) {
        // Show anggota details
        console.log('View anggota:', id);
    }

    showModal(modalId) {
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
    }
}

// Global functions for onclick handlers
window.koperasi = null;
window.refreshDashboard = function() {
    if (window.koperasi) window.koperasi.refreshDashboard();
};
window.showModal = function(modalId) {
    if (window.koperasi) window.koperasi.showModal(modalId);
};
window.saveAnggota = function() {
    if (window.koperasi) window.koperasi.saveAnggota();
};
window.filterAnggota = function() {
    if (window.koperasi) window.koperasi.filterAnggota();
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.koperasi = new KoperasiManagement();
});
