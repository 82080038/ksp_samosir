<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Dashboard E-Learning</h2>
        <p class="text-muted">Platform pembelajaran koperasi dan pengembangan SDM</p>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- Learning Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['total_courses'] ?></h5>
                        <p class="card-text">Total Kursus</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-book fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['active_courses'] ?></h5>
                        <p class="card-text">Kursus Aktif</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-graduation-cap fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['total_enrollments'] ?></h5>
                        <p class="card-text">Total Pendaftar</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['completion_rate'] ?>%</h5>
                        <p class="card-text">Tingkat Kelulusan</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-percentage fa-2x opacity-75"></i>
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
                <h5>Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="<?= base_url('learning/courses') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-list"></i> Kelola Kursus
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('learning/createCourse') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-plus"></i> Buat Kursus Baru
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('learning/myCourses') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-user-graduate"></i> Kursus Saya
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('learning/myCertificates') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-certificate"></i> Sertifikat Saya
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Course Categories and Recent Courses -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Kursus Populer</h5>
            </div>
            <div class="card-body">
                <?php if (empty($popular_courses)): ?>
                    <p class="text-muted">Belum ada data kursus populer.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($popular_courses as $course): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($course['category'])) ?></span>
                                        <?= htmlspecialchars($course['title']) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-users"></i> <?= $course['enrollment_count'] ?> peserta
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Kursus Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_courses)): ?>
                    <p class="text-muted">Belum ada kursus yang dibuat.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_courses as $course): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($course['title']) ?></h6>
                                    <small class="text-muted">
                                        <span class="badge bg-<?= $course['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= htmlspecialchars($course['status']) ?>
                                        </span>
                                    </small>
                                </div>
                                <p class="mb-1">
                                    <strong>Kode:</strong> <?= htmlspecialchars($course['course_code']) ?> |
                                    <strong>Kategori:</strong> <?= htmlspecialchars(ucfirst($course['category'])) ?>
                                </p>
                                <small class="text-muted">
                                    Dibuat: <?= htmlspecialchars(formatDate($course['created_at'], 'd M Y')) ?> |
                                    Oleh: <?= htmlspecialchars($course['created_by_name']) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Learning Progress Overview -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Ringkasan Pembelajaran</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <i class="fas fa-book-open fa-2x text-primary mb-2"></i>
                            <h6>Kursus Dasar Koperasi</h6>
                            <p class="small text-muted">Pelajari dasar-dasar koperasi, AD/ART, dan prinsip-prinsip koperasi</p>
                            <span class="badge bg-primary">Wajib</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                            <h6>Manajemen Keuangan</h6>
                            <p class="small text-muted">Pelajari akuntansi koperasi, laporan keuangan, dan analisis</p>
                            <span class="badge bg-success">Rekomendasi</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <i class="fas fa-gavel fa-2x text-warning mb-2"></i>
                            <h6>Hukum & Regulasi</h6>
                            <p class="small text-muted">Pahami regulasi koperasi, UU No. 25/1992, dan kepatuhan</p>
                            <span class="badge bg-warning">Rekomendasi</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <i class="fas fa-users-cog fa-2x text-info mb-2"></i>
                            <h6>Kepemimpinan & SDM</h6>
                            <p class="small text-muted">Keterampilan kepemimpinan, manajemen SDM, dan pengembangan organisasi</p>
                            <span class="badge bg-info">Opsional</span>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Program Pembelajaran KSP Samosir</h6>
                    <p class="mb-2">Platform e-learning ini dirancang untuk meningkatkan kompetensi anggota dan pengurus koperasi melalui:</p>
                    <ul class="mb-0">
                        <li>Materi pembelajaran interaktif dengan modul-modul terstruktur</li>
                        <li>Tracking progress pembelajaran real-time</li>
                        <li>Sertifikat kompetensi setelah menyelesaikan kursus</li>
                        <li>Assessment dan evaluasi pemahaman materi</li>
                        <li>Forum diskusi dan dukungan komunitas</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
