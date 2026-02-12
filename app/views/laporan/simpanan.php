<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Simpanan - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Laporan Simpanan</h1>
            <a href="<?= base_url('laporan') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Rekapitulasi Simpanan per Jenis</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Jenis Simpanan</th>
                            <th>Jumlah Rekening</th>
                            <th>Total Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $reports = fetchAll("SELECT js.nama, COUNT(s.id) as jumlah_rekening, COALESCE(SUM(s.saldo), 0) as total_saldo 
                                          FROM simpanan s 
                                          JOIN jenis_simpanan js ON s.jenis_simpanan_id = js.id 
                                          WHERE s.status = 'aktif' 
                                          GROUP BY js.id, js.nama");
                        foreach ($reports as $report): ?>
                        <tr>
                            <td><?= htmlspecialchars($report['nama']) ?></td>
                            <td><?= number_format($report['jumlah_rekening']) ?></td>
                            <td><?= formatCurrency($report['total_saldo']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
