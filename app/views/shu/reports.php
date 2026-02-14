<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Laporan SHU Anggota</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('shu') ?>">SHU</a></li>
                <li class="breadcrumb-item active">Laporan</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($success = getFlashMessage('success')): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error = getFlashMessage('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No Anggota</th>
                        <th>Nama</th>
                        <th>Total SHU</th>
                        <th>Jumlah Distribusi</th>
                        <th>Status Terakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($member['no_anggota']) ?></code></td>
                            <td>
                                <strong><?= htmlspecialchars($member['nama_lengkap']) ?></strong><br>
                                <small class="text-muted">NIK: <?= htmlspecialchars($member['nik']) ?></small>
                            </td>
                            <td>
                                <strong class="text-success">Rp <?= formatCurrency($member['total_shu']) ?></strong>
                            </td>
                            <td>
                                <?php
                                $dist_count = fetchRow("SELECT COUNT(*) as count FROM member_shu WHERE member_id = ?", [$member['id']], 'i')['count'];
                                echo $dist_count;
                                ?>
                            </td>
                            <td>
                                <?php
                                $last_shu = fetchRow("SELECT ms.status_pembayaran FROM member_shu ms JOIN profit_distributions pd ON ms.distribution_id = pd.id WHERE ms.member_id = ? ORDER BY pd.tanggal_distribusi DESC LIMIT 1", [$member['id']], 'i');
                                if ($last_shu): ?>
                                    <span class="badge bg-<?= $last_shu['status_pembayaran'] === 'lunas' ? 'success' : 'warning' ?>">
                                        <?= htmlspecialchars($last_shu['status_pembayaran']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url('shu/reports?member_id=' . $member['id']) ?>" class="btn btn-sm btn-outline-info">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Pagination" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Summary Card -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Ringkasan SHU</h5>
            </div>
            <div class="card-body">
                <?php
                $total_members = fetchRow("SELECT COUNT(*) as count FROM anggota WHERE status = 'aktif'")['count'];
                $total_shu_all = fetchRow("SELECT COALESCE(SUM(total_shu), 0) as total FROM member_shu")['total'];
                $avg_shu = $total_members > 0 ? $total_shu_all / $total_members : 0;
                $paid_shu = fetchRow("SELECT COALESCE(SUM(total_shu), 0) as total FROM member_shu WHERE status_pembayaran = 'lunas'")['total'];
                $pending_shu = $total_shu_all - $paid_shu;
                ?>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Total Anggota Aktif:</span>
                        <strong><?= $total_members ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Total SHU Dibagikan:</span>
                        <strong class="text-success">Rp <?= formatCurrency($total_shu_all) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Rata-rata SHU per Anggota:</span>
                        <strong>Rp <?= formatCurrency($avg_shu) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>SHU Sudah Dibayar:</span>
                        <strong class="text-success">Rp <?= formatCurrency($paid_shu) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>SHU Pending:</span>
                        <strong class="text-warning">Rp <?= formatCurrency($pending_shu) ?></strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Distribusi SHU Terbaru</h5>
            </div>
            <div class="card-body">
                <?php
                $recent_shu = fetchAll("SELECT pd.periode, pd.tanggal_distribusi, COUNT(ms.id) as member_count, SUM(ms.total_shu) as total_shu FROM profit_distributions pd LEFT JOIN member_shu ms ON pd.id = ms.distribution_id WHERE pd.status = 'approved' GROUP BY pd.id ORDER BY pd.tanggal_distribusi DESC LIMIT 3");
                if (empty($recent_shu)): ?>
                    <p class="text-muted">Belum ada distribusi SHU.</p>
                <?php else: ?>
                    <?php foreach ($recent_shu as $dist): ?>
                        <div class="border rounded p-3 mb-2">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1"><?= htmlspecialchars($dist['periode']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars(formatDate($dist['tanggal_distribusi'], 'd M Y')) ?></small>
                            </div>
                            <p class="mb-1">
                                <strong>Rp <?= formatCurrency($dist['total_shu']) ?></strong> untuk
                                <strong><?= $dist['member_count'] ?> anggota</strong>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
