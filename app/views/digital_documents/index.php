<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="digital-documents">Dokumen Digital</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2" style="color: black;"><?= $pageInfo['title'] ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('digital_documents/createDocument') ?>" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Create Document
            </a>
            <a href="<?= base_url('digital_documents/templates') ?>" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-text"></i> Templates
            </a>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#refreshModal">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<?php if (true): ?>
<!-- Development Mode Notice -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-gear me-2"></i>
    <strong>Mode Development:</strong> Semua fitur dapat diakses tanpa autentikasi.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Flash Messages -->
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Total Documents</h5>
                        <span class="h2 font-weight-bold mb-0 text-primary">
                            <?= formatAngka($stats['total_documents'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-file-earmark-text fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Signed Documents</h5>
                        <span class="h2 font-weight-bold mb-0 text-success">
                            <?= formatAngka($stats['signed_documents'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Pending Signatures</h5>
                        <span class="h2 font-weight-bold mb-0 <?= ($stats['pending_signatures'] ?? 0) > 0 ? 'text-warning' : 'text-info' ?>">
                            <?= formatAngka($stats['pending_signatures'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock-history fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card ksp-stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase mb-0">Completed Signatures</h5>
                        <span class="h2 font-weight-bold mb-0 text-info">
                            <?= formatAngka($stats['completed_signatures'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle-fill fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Document Management Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body text-center">
                                <i class="bi bi-file-earmark-plus fa-2x text-primary mb-2"></i>
                                <h6 class="card-title">Create Document</h6>
                                <p class="card-text small">Generate new digital documents</p>
                                <a href="<?= base_url('digital_documents/createDocument') ?>" class="btn btn-primary btn-sm">Create</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <i class="bi bi-list-ul fa-2x text-success mb-2"></i>
                                <h6 class="card-title">View Documents</h6>
                                <p class="card-text small">Browse all documents</p>
                                <a href="<?= base_url('digital_documents/documents') ?>" class="btn btn-success btn-sm">Browse</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-info">
                            <div class="card-body text-center">
                                <i class="bi bi-pencil-square fa-2x text-info mb-2"></i>
                                <h6 class="card-title">E-Signature</h6>
                                <p class="card-text small">Sign documents electronically</p>
                                <button class="btn btn-info btn-sm" onclick="showSignatureGuide()">Guide</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <i class="bi bi-file-earmark-text fa-2x text-warning mb-2"></i>
                                <h6 class="card-title">Templates</h6>
                                <p class="card-text small">Manage document templates</p>
                                <a href="<?= base_url('digital_documents/templates') ?>" class="btn btn-warning btn-sm">Manage</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Documents -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Documents</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_documents)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-file-earmark-x fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No documents yet</h5>
                    <p class="text-muted mb-3">
                        Start by creating your first digital document.
                    </p>
                    <a href="<?= base_url('digital_documents/createDocument') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Create Document
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Document Number</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_documents as $doc): ?>
                            <tr>
                                <td>
                                    <code class="text-primary"><?= htmlspecialchars($doc['nomor_dokumen']) ?></code>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($doc['judul_dokumen']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars(str_replace('_', ' ', $doc['jenis_dokumen'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $status = $doc['status'];
                                    $badgeClass = 'bg-secondary';
                                    if ($status === 'signed') $badgeClass = 'bg-success';
                                    elseif ($status === 'pending_signature') $badgeClass = 'bg-warning';
                                    elseif ($status === 'completed') $badgeClass = 'bg-info';
                                    elseif ($status === 'expired') $badgeClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars(str_replace('_', ' ', $status)) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= formatDate($doc['created_at']) ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('digital_documents/view/' . $doc['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= base_url('digital_documents/download/' . $doc['id']) ?>" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pending Signatures -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Pending Signatures</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pending_signatures)): ?>
                <div class="text-center py-3">
                    <i class="bi bi-check-circle fa-2x text-success mb-2"></i>
                    <p class="text-muted small mb-0">No pending signatures</p>
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($pending_signatures, 0, 5) as $signature): ?>
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <a href="<?= base_url('digital_documents/view/' . $signature['document_id']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars(substr($signature['judul_dokumen'], 0, 30)) ?>...
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <?= htmlspecialchars($signature['signer_name']) ?> •
                                    <?= formatDate($signature['created_at'], 'd M') ?>
                                </small>
                            </div>
                            <span class="badge bg-warning">Pending</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- E-Signature Guide Modal -->
<div class="modal fade" id="signatureGuideModal" tabindex="-1" aria-labelledby="signatureGuideLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="signatureGuideLabel">E-Signature Guide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>How to Sign Documents Electronically:</h6>
                <ol>
                    <li>Open the document you need to sign</li>
                    <li>Click the "Sign Document" button</li>
                    <li>Draw your signature using the signature pad or upload an image</li>
                    <li>Read and accept the legal agreement</li>
                    <li>Click "Submit Signature" to complete the process</li>
                </ol>

                <h6 class="mt-3">Legal Compliance:</h6>
                <ul>
                    <li>Electronic signatures are legally binding under Indonesian law</li>
                    <li>All signatures are timestamped and auditable</li>
                    <li>IP address and device information are logged for security</li>
                    <li>Documents remain tamper-proof once signed</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showSignatureGuide() {
    new bootstrap.Modal(document.getElementById('signatureGuideModal')).show();
}
</script>
