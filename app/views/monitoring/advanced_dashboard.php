<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-graph-up text-primary me-2"></i>Advanced Monitoring Dashboard</h2>
        <p class="text-muted mb-0">Real-time analytics with AI-powered insights</p>
    </div>
    <div class="btn-group">
        <button class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
        <button class="btn btn-outline-secondary btn-sm" onclick="exportDashboard()">
            <i class="bi bi-download"></i> Export
        </button>
    </div>
</div>

<!-- Real-time Status Indicators -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <div class="h1 text-primary mb-0" id="system-status">●</div>
                <h6 class="card-title">System Status</h6>
                <small class="text-muted" id="system-uptime">Loading...</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <div class="h4 text-success mb-1" id="active-users">0</div>
                <h6 class="card-title">Active Users</h6>
                <small class="text-muted">Real-time</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <div class="h4 text-warning mb-1" id="response-time">0ms</div>
                <h6 class="card-title">Avg Response Time</h6>
                <small class="text-muted">Last 1 hour</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <div class="h4 text-danger mb-1" id="error-rate">0%</div>
                <h6 class="card-title">Error Rate</h6>
                <small class="text-muted">Last 24 hours</small>
            </div>
        </div>
    </div>
</div>

<!-- AI Insights Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-brain text-primary me-2"></i>AI-Powered Insights</h5>
            </div>
            <div class="card-body">
                <div id="ai-insights" class="row">
                    <!-- Insights will be loaded dynamically -->
                    <div class="col-12 text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading insights...</span>
                        </div>
                        <p class="mt-2">Generating AI insights...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Phase Progress & KPIs -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart text-success me-2"></i>Phase Progress & KPIs</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Phase 1 -->
                    <div class="col-md-6 mb-3">
                        <h6 class="text-primary">Phase 1: Foundation Strengthening</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 85%" id="phase1-progress">85%</div>
                        </div>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h6 mb-0" id="digital-transactions">68%</div>
                                <small class="text-muted">Digital Txns</small>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0" id="app-adoption">52%</div>
                                <small class="text-muted">App Adoption</small>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0" id="system-uptime-kpi">99.8%</div>
                                <small class="text-muted">Uptime</small>
                            </div>
                        </div>
                    </div>

                    <!-- Phase 2 -->
                    <div class="col-md-6 mb-3">
                        <h6 class="text-warning">Phase 2: Operational Excellence</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 45%" id="phase2-progress">45%</div>
                        </div>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h6 mb-0" id="efficiency-gain">52%</div>
                                <small class="text-muted">Efficiency</small>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0" id="risk-reduction">75%</div>
                                <small class="text-muted">Risk Reduction</small>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0" id="member-satisfaction">4.3</div>
                                <small class="text-muted">Satisfaction</small>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <!-- Phase 3 -->
                    <div class="col-md-6 mb-3">
                        <h6 class="text-info">Phase 3: Ecosystem Expansion</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 25%" id="phase3-progress">25%</div>
                        </div>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h6 mb-0" id="ecosystem-growth">180%</div>
                                <small class="text-muted">Growth</small>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0" id="engagement-rate">72%</div>
                                <small class="text-muted">Engagement</small>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0" id="revenue-diversification">25%</div>
                                <small class="text-muted">Revenue Mix</small>
                            </div>
                        </div>
                    </div>

                    <!-- Phase 4 -->
                    <div class="col-md-6 mb-3">
                        <h6 class="text-secondary">Phase 4: Innovation & Scale</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-secondary" role="progressbar" style="width: 10%" id="phase4-progress">10%</div>
                        </div>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h6 mb-0" id="ai-adoption">15%</div>
                                <small class="text-muted">AI Adoption</small>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0" id="network-scale">2</div>
                                <small class="text-muted">Network Size</small>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0" id="innovation-index">12%</div>
                                <small class="text-muted">Innovation</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts & Recommendations -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Active Alerts</h5>
            </div>
            <div class="card-body">
                <div id="active-alerts">
                    <div class="text-center text-muted">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        Loading alerts...
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightbulb text-success me-2"></i>Recommendations</h5>
            </div>
            <div class="card-body">
                <div id="recommendations-list">
                    <div class="text-center text-muted">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        Analyzing data...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Trends Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up text-info me-2"></i>Performance Trends (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="performanceChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Predictive Analytics Preview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-robot text-purple me-2"></i>Predictive Analytics</h5>
                <a href="<?= base_url('advanced-monitoring/predictive-analytics') ?>" class="btn btn-sm btn-outline-primary">
                    View Full Report
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <div class="h4 text-success mb-1" id="revenue-prediction">Rp 75M</div>
                        <small class="text-muted">Next Quarter Revenue</small>
                        <div class="text-success small">↑ 18% predicted</div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="h4 text-primary mb-1" id="member-growth-prediction">+25%</div>
                        <small class="text-muted">Member Growth</small>
                        <div class="text-primary small">Next 6 months</div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="h4 text-warning mb-1" id="risk-trend">↓ 15%</div>
                        <small class="text-muted">Risk Reduction</small>
                        <div class="text-success small">Trend improving</div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="h4 text-info mb-1" id="tech-adoption">78%</div>
                        <small class="text-muted">Tech Adoption</small>
                        <div class="text-info small">Within 12 months</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ROI Summary -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cash-coin text-success me-2"></i>Investment ROI Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center mb-3">
                            <div class="h3 text-success mb-1" id="current-roi">180%</div>
                            <h6>Current ROI</h6>
                            <small class="text-muted">Based on Phase 1-2 investments</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center mb-3">
                            <div class="h3 text-primary mb-1" id="payback-period">8.5</div>
                            <h6>Payback Period</h6>
                            <small class="text-muted">Months to break even</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center mb-3">
                            <div class="h3 text-warning mb-1" id="projected-growth">+350%</div>
                            <h6>Projected Growth</h6>
                            <small class="text-muted">Next 24 months</small>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <a href="<?= base_url('advanced-monitoring/roi-tracker') ?>" class="btn btn-success">
                        <i class="bi bi-graph-up me-2"></i>View Detailed ROI Analysis
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Real-time Updates -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let performanceChart;
let refreshInterval;

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    setupRealTimeUpdates();
});

function initializeDashboard() {
    // Initialize performance chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Response Time (ms)',
                borderColor: 'rgb(75, 192, 192)',
                data: [],
                fill: false
            }, {
                label: 'Error Rate (%)',
                borderColor: 'rgb(255, 99, 132)',
                data: [],
                fill: false
            }, {
                label: 'Active Users',
                borderColor: 'rgb(54, 162, 235)',
                data: [],
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Load initial data
    refreshDashboard();
}

function setupRealTimeUpdates() {
    // Update every 30 seconds
    refreshInterval = setInterval(refreshDashboard, 30000);
}

function refreshDashboard() {
    // Fetch real-time data
    fetch('<?= base_url('advanced-monitoring/api-realtime-data') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboard(data.data);
            }
        })
        .catch(error => console.error('Error fetching dashboard data:', error));

    // Fetch AI insights
    fetch('<?= base_url('advanced-monitoring/api-ai-insights') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateAIInsights(data.insights);
            }
        })
        .catch(error => console.error('Error fetching AI insights:', error));
}

function updateDashboard(data) {
    // Update system status indicators
    document.getElementById('system-status').textContent = data.system.error_rate < 1 ? '●' : '●';
    document.getElementById('system-status').className = data.system.error_rate < 1 ? 'text-success h1 mb-0' : 'text-danger h1 mb-0';

    document.getElementById('active-users').textContent = data.system.active_users || 0;
    document.getElementById('response-time').textContent = Math.round(data.system.response_time || 0) + 'ms';
    document.getElementById('error-rate').textContent = (data.system.error_rate || 0).toFixed(2) + '%';

    // Update KPIs
    if (data.business) {
        document.getElementById('digital-transactions').textContent = (data.business.digital_transaction_volume || 0).toFixed(1) + '%';
        document.getElementById('app-adoption').textContent = (data.business.member_app_adoption || 0).toFixed(1) + '%';
        document.getElementById('system-uptime-kpi').textContent = (data.business.system_uptime || 99.9).toFixed(1) + '%';

        document.getElementById('efficiency-gain').textContent = (data.business.operational_efficiency || 0).toFixed(1) + '%';
        document.getElementById('risk-reduction').textContent = (data.business.risk_reduction || 0).toFixed(1) + '%';
        document.getElementById('member-satisfaction').textContent = (data.business.member_satisfaction || 0).toFixed(1);

        document.getElementById('ecosystem-growth').textContent = (data.business.ecosystem_growth || 0).toFixed(1) + '%';
        document.getElementById('engagement-rate').textContent = (data.business.member_engagement || 0).toFixed(1) + '%';
        document.getElementById('revenue-diversification').textContent = (data.business.revenue_diversification || 0).toFixed(1) + '%';

        document.getElementById('ai-adoption').textContent = (data.business.ai_adoption || 0).toFixed(1) + '%';
        document.getElementById('network-scale').textContent = data.business.network_scale || 0;
        document.getElementById('innovation-index').textContent = (data.business.innovation_index || 0).toFixed(1) + '%';
    }

    // Update alerts
    updateAlerts(data.alerts || []);

    // Update recommendations
    updateRecommendations(data.recommendations || []);

    // Update chart with performance trends
    updatePerformanceChart(data.performance_trends || []);
}

function updateAIInsights(insights) {
    const container = document.getElementById('ai-insights');
    if (insights.length === 0) {
        container.innerHTML = '<div class="col-12 text-center text-muted">No insights available at this time.</div>';
        return;
    }

    let html = '';
    insights.forEach(insight => {
        const badgeClass = insight.type === 'warning' ? 'badge-warning' :
                          insight.type === 'opportunity' ? 'badge-success' : 'badge-info';

        html += `
            <div class="col-md-6 mb-3">
                <div class="card border-${insight.type === 'warning' ? 'warning' : insight.type === 'opportunity' ? 'success' : 'info'}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">${insight.title}</h6>
                            <span class="badge ${badgeClass}">${insight.priority}</span>
                        </div>
                        <p class="card-text small mb-2">${insight.description}</p>
                        <div class="small text-muted">
                            <strong>Impact:</strong> ${insight.impact}<br>
                            <strong>Confidence:</strong> ${(insight.confidence * 100).toFixed(0)}%
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function updateAlerts(alerts) {
    const container = document.getElementById('active-alerts');
    if (alerts.length === 0) {
        container.innerHTML = '<div class="text-center text-muted">No active alerts</div>';
        return;
    }

    let html = '';
    alerts.slice(0, 5).forEach(alert => {
        const alertClass = alert.alert_level === 'critical' ? 'alert-danger' :
                          alert.alert_level === 'warning' ? 'alert-warning' : 'alert-info';

        html += `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <strong>${alert.alert_type.toUpperCase()}:</strong> ${alert.message}
                <button type="button" class="btn-close" onclick="resolveAlert(${alert.id})"></button>
            </div>
        `;
    });

    container.innerHTML = html;
}

function updateRecommendations(recommendations) {
    const container = document.getElementById('recommendations-list');
    if (recommendations.length === 0) {
        container.innerHTML = '<div class="text-center text-muted">No recommendations available</div>';
        return;
    }

    let html = '';
    recommendations.slice(0, 3).forEach(rec => {
        const priorityClass = rec.priority === 'high' ? 'text-danger' :
                             rec.priority === 'medium' ? 'text-warning' : 'text-info';

        html += `
            <div class="d-flex align-items-start mb-3">
                <i class="bi bi-lightbulb-fill ${priorityClass} me-2 mt-1"></i>
                <div>
                    <strong>${rec.title}</strong>
                    <p class="small mb-1">${rec.description}</p>
                    <small class="text-muted">Expected impact: ${rec.impact}</small>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function updatePerformanceChart(trends) {
    if (trends.length === 0) return;

    const labels = trends.map(t => t.date);
    const responseTimes = trends.map(t => t.avg_response_time);
    const errorRates = trends.map(t => t.avg_error_rate);
    const activeUsers = trends.map(t => t.avg_active_users);

    performanceChart.data.labels = labels;
    performanceChart.data.datasets[0].data = responseTimes;
    performanceChart.data.datasets[1].data = errorRates;
    performanceChart.data.datasets[2].data = activeUsers;
    performanceChart.update();
}

function resolveAlert(alertId) {
    fetch('<?= base_url('monitoring/resolve-alert') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ alert_id: alertId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshDashboard();
        }
    });
}

function exportDashboard() {
    // Implement dashboard export functionality
    alert('Export functionality will be implemented in Phase 3');
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>
