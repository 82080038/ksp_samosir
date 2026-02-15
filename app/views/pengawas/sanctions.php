r<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="pengawas-sanctions">Sanksi</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="row">
    <?php foreach ($sanctions as $sanksi): ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <span class="badge bg-danger me-2">
                            <?= htmlspecialchars(str_replace(['teguran_lisan', 'teguran_tertulis', 'pemberhentian_sementara'], ['Teguran Lisan', 'Teguran Tertulis', 'Pemberhentian Sementara'], $sanksi['jenis_sanksi'])) ?>
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        <strong>Deskripsi:</strong><br>
                        <?= htmlspecialchars($sanksi['deskripsi']) ?>
                    </p>
                    <?php if (!empty($sanksi['dasar_hukum'])): ?>
                        <p class="card-text">
                            <strong>Dasar Hukum:</strong><br>
                            <small class="text-muted"><?= htmlspecialchars($sanksi['dasar_hukum']) ?></small>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-muted">
                    <small>Ditambahkan: <?= htmlspecialchars(formatDate($sanksi['created_at'], 'd M Y')) ?></small>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($sanctions)): ?>
    <div class="alert alert-info">
        <h5>Belum ada referensi sanksi</h5>
        <p>Sistem sanksi akan diisi berdasarkan AD/ART dan regulasi yang berlaku.</p>
    </div>
<?php endif; ?>

<!-- Information Card -->
<div class="card mt-4">
    <div class="card-header">
        <h5>Informasi Sanksi</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6>Teguran Lisan</h6>
                <p class="small">Peringatan verbal kepada pelanggar untuk memperbaiki perilaku.</p>
            </div>
            <div class="col-md-4">
                <h6>Teguran Tertulis</h6>
                <p class="small">Peringatan resmi dalam bentuk surat tertulis dengan catatan resmi.</p>
            </div>
            <div class="col-md-4">
                <h6>Pemberhentian Sementara</h6>
                <p class="small">Penghentian jabatan sementara sesuai AD/ART dan regulasi.</p>
            </div>
        </div>
        <hr>
        <p class="small text-muted">
            Sanksi diberlakukan sesuai dengan AD/ART Koperasi Pemasaran Kepolisian Polres Samosir dan UU No. 25 Tahun 1992 tentang Perkoperasian.
        </p>
    </div>
</div>
