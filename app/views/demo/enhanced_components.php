<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!-- Main Content Container -->
<div class="main-content" id="main-content" data-page="components-demo">

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="components-demo">Enhanced Components Demo</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-primary btn-enhanced" data-bs-toggle="tooltip" data-bs-placement="top" title="Tooltip untuk tombol">
                <i class="bi bi-magic me-2"></i>Enhanced Button
            </button>
            <button type="button" class="btn btn-success btn-enhanced" data-loading-text="Loading...">
                <i class="bi bi-play-circle me-2"></i>With Loading
            </button>
        </div>
    </div>
</div>

<!-- Enhanced Alert Examples -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3">Enhanced Alerts</h4>
        <div class="alert alert-success alert-enhanced alert-success-enhanced" data-auto-dismiss="5000">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Success!</strong> This is an enhanced success alert with auto-dismiss.
        </div>
        <div class="alert alert-danger alert-enhanced alert-danger-enhanced">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Error!</strong> This is an enhanced danger alert with custom styling.
        </div>
        <div class="alert alert-warning alert-enhanced alert-warning-enhanced">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Warning!</strong> This is an enhanced warning alert.
        </div>
        <div class="alert alert-info alert-enhanced alert-info-enhanced">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>Info!</strong> This is an enhanced info alert.
        </div>
    </div>
</div>

<!-- Enhanced Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card ksp-card-enhanced" data-enhanced>
            <div class="card-header">
                <i class="bi bi-graph-up me-2"></i>Statistics Card
            </div>
            <div class="card-body">
                <h5 class="card-title">Enhanced Card</h5>
                <p class="card-text">This card has enhanced hover effects and styling.</p>
                <div class="progress progress-enhanced mb-3">
                    <div class="progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">75%</div>
                </div>
                <button class="btn btn-primary btn-enhanced btn-sm">Action Button</button>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card ksp-card-enhanced" data-enhanced data-expandable>
            <div class="card-header">
                <i class="bi bi-arrows-expand me-2"></i>Expandable Card
            </div>
            <div class="card-body">
                <h5 class="card-title">Click to Expand</h5>
                <p class="card-text">Click this card to see the expand effect.</p>
                <div class="collapse" id="expandContent">
                    <div class="card card-body bg-light">
                        <h6>Expanded Content</h6>
                        <p>This content appears when the card is expanded.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card ksp-card-enhanced" data-enhanced data-loading>
            <div class="card-header">
                <i class="bi bi-hourglass-split me-2"></i>Loading Card
            </div>
            <div class="card-body">
                <h5 class="card-title">With Loading State</h5>
                <p class="card-text">This card shows a loading overlay.</p>
                <button class="btn btn-info btn-enhanced btn-sm" onclick="toggleLoading(this)">Toggle Loading</button>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Forms -->
<div class="row mb-4">
    <div class="col-md-6">
        <h4 class="mb-3">Enhanced Forms</h4>
        <form data-enhanced-validation>
            <div class="form-floating form-floating-enhanced mb-3">
                <input type="text" class="form-control" id="floatingName" placeholder="Nama Lengkap" required>
                <label for="floatingName">Nama Lengkap *</label>
                <div class="invalid-feedback">Nama lengkap harus diisi</div>
            </div>
            <div class="form-floating form-floating-enhanced mb-3">
                <input type="email" class="form-control" id="floatingEmail" placeholder="Email" required>
                <label for="floatingEmail">Email Address *</label>
                <div class="invalid-feedback">Email harus diisi</div>
            </div>
            <div class="form-floating form-floating-enhanced mb-3">
                <select class="form-control" id="floatingSelect" required>
                    <option value="">Pilih Kategori</option>
                    <option value="1">Kategori 1</option>
                    <option value="2">Kategori 2</option>
                    <option value="3">Kategori 3</option>
                </select>
                <label for="floatingSelect">Kategori *</label>
                <div class="invalid-feedback">Kategori harus dipilih</div>
            </div>
            <div class="form-floating form-floating-enhanced mb-3">
                <textarea class="form-control" id="floatingMessage" placeholder="Pesan" style="height: 100px"></textarea>
                <label for="floatingMessage">Pesan</label>
            </div>
            <button type="submit" class="btn btn-primary btn-enhanced">
                <i class="bi bi-send me-2"></i>Submit Form
            </button>
        </form>
    </div>
    <div class="col-md-6">
        <h4 class="mb-3">Button Groups</h4>
        <div class="btn-group" role="group" aria-label="Basic example">
            <button type="button" class="btn btn-outline-primary btn-enhanced">Left</button>
            <button type="button" class="btn btn-outline-primary btn-enhanced">Middle</button>
            <button type="button" class="btn btn-outline-primary btn-enhanced">Right</button>
        </div>
        
        <h5 class="mt-4 mb-3">Enhanced Badges</h5>
        <span class="badge bg-primary badge-enhanced me-2">Primary</span>
        <span class="badge bg-success badge-enhanced me-2">Success</span>
        <span class="badge bg-danger badge-enhanced me-2">Danger</span>
        <span class="badge bg-warning badge-enhanced me-2">Warning</span>
        <span class="badge bg-info badge-enhanced">Info</span>
        
        <h5 class="mt-4 mb-3">Tooltips & Popovers</h5>
        <button type="button" class="btn btn-secondary btn-enhanced me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Tooltip on top">
            Tooltip on top
        </button>
        <button type="button" class="btn btn-secondary btn-enhanced me-2" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Popover content">
            Popover
        </button>
        <button type="button" class="btn btn-secondary btn-enhanced" data-bs-toggle="popover" data-bs-placement="bottom" title="Popover Title" data-bs-content="This is a popover with title">
            Popover with Title
        </button>
    </div>
</div>

<!-- Enhanced Table -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3">Enhanced Table</h4>
        <div class="table-responsive">
            <table class="table table-enhanced" data-enhanced data-selectable>
                <thead>
                    <tr>
                        <th data-sortable>No</th>
                        <th data-sortable>Nama</th>
                        <th data-sortable>Email</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>John Doe</td>
                        <td>john@example.com</td>
                        <td><span class="badge bg-success badge-enhanced">Active</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-enhanced" data-bs-toggle="tooltip" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-enhanced" data-bs-toggle="tooltip" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Jane Smith</td>
                        <td>jane@example.com</td>
                        <td><span class="badge bg-warning badge-enhanced">Pending</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-enhanced" data-bs-toggle="tooltip" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-enhanced" data-bs-toggle="tooltip" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Bob Johnson</td>
                        <td>bob@example.com</td>
                        <td><span class="badge bg-danger badge-enhanced">Inactive</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-enhanced" data-bs-toggle="tooltip" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-enhanced" data-bs-toggle="tooltip" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Enhanced Modal Demo -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3">Enhanced Modals</h4>
        <button type="button" class="btn btn-primary btn-enhanced me-2" data-bs-toggle="modal" data-bs-target="#enhancedModal">
            <i class="bi bi-window-stack me-2"></i>Open Enhanced Modal
        </button>
        <button type="button" class="btn btn-success btn-enhanced me-2" onclick="showConfirm('Apakah Anda yakin ingin melanjutkan?', function() {
            showSuccess('Konfirmasi berhasil!', 'Success');
        })">
            <i class="bi bi-check-circle me-2"></i>Show Confirmation
        </button>
        <button type="button" class="btn btn-info btn-enhanced" onclick="showInfo('Ini adalah pesan informasi', 'Info')">
            <i class="bi bi-info-circle me-2"></i>Show Info Toast
        </button>
    </div>
</div>

<!-- Enhanced Modal -->
<div class="modal fade modal-enhanced" id="enhancedModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-magic me-2"></i>Enhanced Modal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This is an enhanced modal with improved styling and animations.</p>
                <div class="alert alert-info alert-enhanced alert-info-enhanced">
                    <i class="bi bi-info-circle me-2"></i>
                    Modal has backdrop blur effect and enhanced transitions.
                </div>
                <form>
                    <div class="form-floating form-floating-enhanced mb-3">
                        <input type="text" class="form-control" id="modalInput" placeholder="Input">
                        <label for="modalInput">Sample Input</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-enhanced" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-enhanced" onclick="showSuccess('Modal action completed!')">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Breadcrumb -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3">Enhanced Breadcrumb</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-enhanced">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>"><i class="bi bi-house me-1"></i>Home</a></li>
                <li class="breadcrumb-item"><a href="#">Components</a></li>
                <li class="breadcrumb-item active" aria-current="page">Enhanced Demo</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Enhanced Pagination -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3">Enhanced Pagination</h4>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-enhanced justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

</div>

<!-- JavaScript for Demo -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Enhanced Components Demo initialized');
    
    // Update page title dynamically
    if (typeof updatePageTitle !== 'undefined') {
        updatePageTitle('Enhanced Components Demo', 'components-demo');
    }
    
    // Demo functions
    window.toggleLoading = function(button) {
        var $card = $(button).closest('.card');
        if ($card.hasClass('loading')) {
            KSP.EnhancedComponents.hideLoading($card);
            $card.removeClass('loading');
            button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Toggle Loading';
        } else {
            KSP.EnhancedComponents.showLoading($card, 'Loading data...');
            $card.addClass('loading');
            button.innerHTML = '<i class="bi bi-check-circle me-2"></i>Stop Loading';
        }
    };
    
    // Enhanced form submission
    $('form[data-enhanced-validation]').on('submit', function(e) {
        e.preventDefault();
        showSuccess('Form validated successfully!', 'Form Validation');
    });
    
    // Table row selection
    $('.table-enhanced tbody tr').on('click', function(e) {
        if (!$(e.target).closest('button').length) {
            $(this).toggleClass('table-selected');
        }
    });
    
    // Table sorting
    $('.table-enhanced th[data-sortable]').on('click', function() {
        var $th = $(this);
        var sortClass = $th.hasClass('sort-asc') ? 'sort-desc' : 'sort-asc';
        $('.table-enhanced th').removeClass('sort-asc sort-desc');
        $th.addClass(sortClass);
        
        // Simulate sorting animation
        var $tbody = $('.table-enhanced tbody');
        $tbody.fadeOut(200, function() {
            $(this).fadeIn(200);
        });
    });
});
</script>

<style>
/* Demo-specific styles */
.demo-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 0.75rem;
}

.demo-section h4 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 1rem;
}

.table-selected {
    background-color: rgba(0, 123, 255, 0.1) !important;
}

/* Animation demo */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.pulse {
    animation: pulse 2s infinite;
}
</style>
