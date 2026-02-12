<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Laporan</h2>
    <div class="btn-group">
        <button class="btn btn-primary btn-sm">Export Laporan</button>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="card ksp-stats-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Pilih Jenis Laporan</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="<?= base_url('laporan/simpanan') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-piggy-bank me-2"></i> Laporan Simpanan
                    </a>
                    <a href="<?= base_url('laporan/pinjaman') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-cash-stack me-2"></i> Laporan Pinjaman
                    </a>
                    <a href="<?= base_url('laporan/penjualan') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-cart3 me-2"></i> Laporan Penjualan
                    </a>
                    <a href="<?= base_url('laporan/neraca') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-balance-scale me-2"></i> Laporan Neraca
                    </a>
                    <a href="<?= base_url('laporan/laba_rugi') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-graph-up me-2"></i> Laporan Laba Rugi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
