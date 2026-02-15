<?php
// Dependency management
if (!function_exists('initView')) {
    require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';
}
if (!function_exists('getCurrentUser')) {
    require_once __DIR__ . '/../../../config/config.php';
}
$pageInfo = $pageInfo ?? (function_exists('initView') ? initView() : []);
$user = $user ?? (function_exists('getCurrentUser') ? getCurrentUser() : []);
$role = $role ?? ($user['role'] ?? 'admin');
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="tax-compliance">Tax Compliance</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tax Compliance Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Add Compliance Record
        </button>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Tax Compliance Dashboard</strong> - Monitor and manage tax compliance for cooperative operations.
</div>

<!-- Compliance Overview -->
<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">PPh 21 Compliance</h5>
                        <span class="h2 font-weight-bold mb-0 text-success">98%</span>
                    </div>
                    <div class="col-auto">
                        <div class="bg-success bg-opacity-25 rounded p-3">
                            <i class="bi bi-check-circle text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">PPh 23 Compliance</h5>
                        <span class="h2 font-weight-bold mb-0 text-warning">95%</span>
                    </div>
                    <div class="col-auto">
                        <div class="bg-warning bg-opacity-25 rounded p-3">
                            <i class="bi bi-exclamation-triangle text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">PPh 25 Compliance</h5>
                        <span class="h2 font-weight-bold mb-0 text-info">100%</span>
                    </div>
                    <div class="col-auto">
                        <div class="bg-info bg-opacity-25 rounded p-3">
                            <i class="bi bi-calendar-check text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Overall Compliance</h5>
                        <span class="h2 font-weight-bold mb-0 text-primary">97%</span>
                    </div>
                    <div class="col-auto">
                        <div class="bg-primary bg-opacity-25 rounded p-3">
                            <i class="bi bi-shield-check text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Compliance Records Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Tax Compliance Records</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Tax Type</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Submission Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Januari 2026</td>
                        <td>PPh 21</td>
                        <td><span class="badge bg-success">Submitted</span></td>
                        <td>2026-02-15</td>
                        <td>2026-02-10</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">View</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Januari 2026</td>
                        <td>PPh 23</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                        <td>2026-02-20</td>
                        <td>-</td>
                        <td>
                            <button class="btn btn-sm btn-primary">Submit</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Desember 2025</td>
                        <td>PPh 25</td>
                        <td><span class="badge bg-success">Submitted</span></td>
                        <td>2026-01-31</td>
                        <td>2026-01-25</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">View</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any JavaScript functionality here
    console.log('Tax compliance page loaded');
});
</script>
