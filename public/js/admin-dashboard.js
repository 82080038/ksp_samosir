/**
 * Admin Dashboard JavaScript
 * Handles all admin dashboard functionality
 */

class AdminDashboard {
    constructor() {
        this.currentModule = 'dashboard';
        this.dashboardData = null;
        this.charts = {};
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadDashboardData();
        this.initializeCharts();
        this.startRealTimeUpdates();
    }

    setupEventListeners() {
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Module navigation
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const module = e.currentTarget.dataset.module;
                this.switchModule(module);
            });
        });

        // Mobile sidebar
        if (window.innerWidth <= 768) {
            document.addEventListener('click', (e) => {
                const sidebar = document.getElementById('sidebar');
                const isClickInside = sidebar.contains(e.target) || e.target.id === 'sidebarToggle';
                
                if (!isClickInside && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            });
        }
    }

    async loadDashboardData() {
        try {
            this.showLoading(true);
            const response = await this.apiCall('/api/admin/dashboard', 'GET');
            
            if (response.success) {
                this.dashboardData = response.dashboard;
                this.updateQuickStats(response.dashboard.quick_stats);
                this.updateModules(response.dashboard.modules);
                this.updateRecentActivities(response.dashboard.recent_activities);
                this.updateSystemStatus(response.dashboard.system_status);
                this.updateNotifications(response.dashboard.notifications);
            } else {
                this.showError('Failed to load dashboard data');
            }
        } catch (error) {
            console.error('Error loading dashboard:', error);
            this.showError('Error loading dashboard data');
        } finally {
            this.showLoading(false);
        }
    }

    updateQuickStats(stats) {
        document.getElementById('totalUsers').textContent = this.formatNumber(stats.users || 0);
        document.getElementById('totalMembers').textContent = this.formatNumber(stats.members || 0);
        document.getElementById('totalUnits').textContent = this.formatNumber(stats.units || 0);
        document.getElementById('todayRevenue').textContent = this.formatCurrency(stats.today_revenue || 0);
    }

    updateModules(modules) {
        const container = document.getElementById('modulesContainer');
        container.innerHTML = '';

        modules.forEach(module => {
            const moduleCard = this.createModuleCard(module);
            container.appendChild(moduleCard);
        });
    }

    createModuleCard(module) {
        const card = document.createElement('div');
        card.className = 'module-card';
        card.innerHTML = `
            <div class="module-icon">
                <i class="${module.icon}"></i>
            </div>
            <h5 class="mb-2">${module.module_name}</h5>
            <p class="text-muted small mb-3">${module.description}</p>
            <div class="d-flex justify-content-between align-items-center">
                <span class="badge bg-primary">${module.permissions_count} permissions</span>
                <i class="fas fa-arrow-right text-primary"></i>
            </div>
        `;

        card.addEventListener('click', () => {
            this.openModule(module);
        });

        return card;
    }

    updateRecentActivities(activities) {
        const container = document.getElementById('recentActivities');
        container.innerHTML = '';

        if (activities.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">No recent activities</p>';
            return;
        }

        activities.slice(0, 5).forEach(activity => {
            const activityItem = this.createActivityItem(activity);
            container.appendChild(activityItem);
        });
    }

    createActivityItem(activity) {
        const item = document.createElement('div');
        item.className = 'activity-item';
        
        const icon = this.getActivityIcon(activity.type);
        const time = this.formatTime(activity.created_at);
        
        item.innerHTML = `
            <div class="d-flex align-items-start">
                <div class="me-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="${icon}"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${this.getActivityTitle(activity)}</h6>
                    <p class="text-muted small mb-1">${this.getActivityDescription(activity)}</p>
                    <small class="text-muted">${time}</small>
                </div>
            </div>
        `;

        return item;
    }

    getActivityIcon(type) {
        const icons = {
            'sale': 'fas fa-shopping-cart',
            'user_registration': 'fas fa-user-plus',
            'ai_prediction': 'fas fa-brain',
            'default': 'fas fa-circle'
        };
        return icons[type] || icons.default;
    }

    getActivityTitle(activity) {
        const titles = {
            'sale': `Sale ${activity.no_faktur}`,
            'user_registration': `New User: ${activity.username}`,
            'ai_prediction': `AI Prediction: ${activity.prediction_type}`,
            'default': 'Activity'
        };
        return titles[activity.type] || titles.default;
    }

    getActivityDescription(activity) {
        const descriptions = {
            'sale': `Amount: ${this.formatCurrency(activity.total_harga)}`,
            'user_registration': `Email: ${activity.email}`,
            'ai_prediction': `Confidence: ${activity.confidence_score}%`,
            'default': 'System activity'
        };
        return descriptions[activity.type] || descriptions.default;
    }

    updateSystemStatus(status) {
        const healthElement = document.getElementById('systemHealth');
        const health = status.database.status === 'healthy' && 
                      status.ai_services.status === 'healthy' ? 'healthy' : 'warning';
        
        healthElement.className = `system-health ${health}`;
        healthElement.innerHTML = `
            <i class="fas fa-heartbeat"></i>
            <span>System ${health}</span>
        `;
    }

    updateNotifications(notifications) {
        const countElement = document.getElementById('notificationCount');
        countElement.textContent = notifications.length;
        
        if (notifications.length > 0) {
            countElement.style.display = 'flex';
        } else {
            countElement.style.display = 'none';
        }
    }

    initializeCharts() {
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        this.charts.revenue = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.getLast7Days(),
                datasets: [{
                    label: 'Revenue',
                    data: [0, 0, 0, 0, 0, 0, 0], // Will be updated with real data
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => this.formatCurrency(value)
                        }
                    }
                }
            }
        });
    }

    async switchModule(module) {
        // Update active menu
        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-module="${module}"]`).classList.add('active');

        // Load module content
        this.currentModule = module;
        await this.loadModuleContent(module);
    }

    async loadModuleContent(module) {
        try {
            this.showLoading(true);
            const response = await this.apiCall(`/api/admin/module/${module}`, 'GET');
            
            if (response.success) {
                this.renderModuleContent(module, response.data);
            } else {
                this.showError(`Failed to load ${module} module`);
            }
        } catch (error) {
            console.error(`Error loading ${module}:`, error);
            this.showError(`Error loading ${module} module`);
        } finally {
            this.showLoading(false);
        }
    }

    renderModuleContent(module, data) {
        const container = document.querySelector('.container-fluid');
        
        // This would render specific module content
        // For now, just show a placeholder
        container.innerHTML = `
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>${module.charAt(0).toUpperCase() + module.slice(1)} Module</h2>
                        <div>
                            <button class="btn btn-primary me-2" onclick="dashboard.refreshModule()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button class="btn btn-outline-primary" onclick="dashboard.backToDashboard()">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </button>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <p>Module content for ${module} will be rendered here.</p>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    openModule(module) {
        this.switchModule(module.module_code);
    }

    backToDashboard() {
        location.reload();
    }

    async refreshModule() {
        await this.loadModuleContent(this.currentModule);
    }

    startRealTimeUpdates() {
        // Update dashboard every 30 seconds
        setInterval(() => {
            if (this.currentModule === 'dashboard') {
                this.loadDashboardData();
            }
        }, 30000);

        // Update charts every 5 minutes
        setInterval(() => {
            this.updateChartData();
        }, 300000);
    }

    async updateChartData() {
        try {
            const response = await this.apiCall('/api/admin/charts/revenue', 'GET');
            if (response.success && this.charts.revenue) {
                this.charts.revenue.data.datasets[0].data = response.data;
                this.charts.revenue.update();
            }
        } catch (error) {
            console.error('Error updating chart data:', error);
        }
    }

    async apiCall(endpoint, method, data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(endpoint, options);
        return await response.json();
    }

    showLoading(show) {
        // Implement loading indicator
        const loadingElements = document.querySelectorAll('.loading-spinner');
        loadingElements.forEach(el => {
            el.style.display = show ? 'inline-block' : 'none';
        });
    }

    showError(message) {
        // Implement error notification
        console.error(message);
        // You could use a toast notification library here
        alert(message); // Simple fallback
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

    formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes} minutes ago`;
        if (hours < 24) return `${hours} hours ago`;
        if (days < 7) return `${days} days ago`;
        
        return date.toLocaleDateString('id-ID');
    }

    getLast7Days() {
        const days = [];
        for (let i = 6; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            days.push(date.toLocaleDateString('id-ID', { weekday: 'short' }));
        }
        return days;
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.dashboard = new AdminDashboard();
});

// Handle responsive sidebar
window.addEventListener('resize', () => {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth > 768) {
        sidebar.classList.remove('show');
    }
});
