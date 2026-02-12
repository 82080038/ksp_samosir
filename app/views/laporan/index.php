<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Laporan</h1>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
