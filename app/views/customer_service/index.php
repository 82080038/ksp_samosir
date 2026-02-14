<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Dashboard Customer Service</h2>
        <p class="text-muted">Kelola tiket, return, dan komunikasi pelanggan</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['total_tickets'] ?></h5>
                        <p class="card-text">Total Tiket</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-ticket-alt fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $stats['open_tickets'] ?></h5>
                        <p class="card-text">Tiket Terbuka</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['pending_returns'] ?></h5>
                        <p class="card-text">Return Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-undo fa-2x opacity-75"></i>
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
                        <h5 class="card-title mb-0"><?= $stats['processed_returns_month'] ?></h5>
                        <p class="card-text">Return Diproses Bulan Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
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
                        <a href="<?= base_url('customer_service/tickets') ?>" class="btn btn-outline-info btn-block">
                            <i class="fas fa-ticket-alt"></i> Kelola Tiket
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('customer_service/createTicket') ?>" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-plus"></i> Buat Tiket Baru
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('customer_service/returns') ?>" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-undo"></i> Kelola Return
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= base_url('customer_service/communication') ?>" class="btn btn-outline-success btn-block">
                            <i class="fas fa-comments"></i> Komunikasi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tickets and Returns -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Tiket Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_tickets)): ?>
                    <p class="text-muted">Belum ada tiket masuk.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_tickets as $ticket): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($ticket['subjek']) ?></h6>
                                    <small class="text-muted">
                                        <span class="badge bg-<?= $ticket['status'] === 'open' ? 'warning' : ($ticket['status'] === 'resolved' ? 'success' : 'info') ?>">
                                            <?= htmlspecialchars($ticket['status']) ?>
                                        </span>
                                    </small>
                                </div>
                                <p class="mb-1">Oleh: <?= htmlspecialchars($ticket['customer_name']) ?></p>
                                <small class="text-muted">
                                    Prioritas: <span class="badge bg-secondary"><?= htmlspecialchars($ticket['prioritas']) ?></span> |
                                    Dibuat: <?= htmlspecialchars(formatDate($ticket['created_at'], 'd M Y')) ?>
                                </small>
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
                <h5>Return Pending</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pending_returns)): ?>
                    <p class="text-muted">Tidak ada return pending.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pending_returns as $return): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Return #<?= htmlspecialchars($return['id']) ?></h6>
                                    <small class="text-muted">Pending</small>
                                </div>
                                <p class="mb-1">Order: <?= htmlspecialchars($return['no_faktur']) ?></p>
                                <small class="text-muted">
                                    Oleh: <?= htmlspecialchars($return['customer_name']) ?> |
                                    Dibuat: <?= htmlspecialchars(formatDate($return['created_at'], 'd M Y')) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
