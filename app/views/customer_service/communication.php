<?php
// Use centralized dependency management
require_once __DIR__ . '/../../../app/helpers/DependencyManager.php';

// Initialize view with all dependencies
$pageInfo = initView();
$user = getCurrentUser();
$role = $user['role'] ?? null;
?>

<!-- Page Header with Dynamic Title -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="cs-communication">Komunikasi Pelanggan</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <a href="<?= base_url('customer_service') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
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
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Kirim Pesan ke Pelanggan</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= base_url('customer_service/communication') ?>">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Pilih Pelanggan *</label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">Pilih pelanggan</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>"><?= htmlspecialchars($customer['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tipe_komunikasi" class="form-label">Tipe Komunikasi *</label>
                        <select class="form-select" id="tipe_komunikasi" name="tipe_komunikasi" required>
                            <option value="">Pilih tipe</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="internal">Notifikasi Internal</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="subjek" class="form-label">Subjek *</label>
                        <input type="text" class="form-control" id="subjek" name="subjek" placeholder="Judul pesan" required>
                    </div>

                    <div class="mb-3">
                        <label for="pesan" class="form-label">Pesan *</label>
                        <textarea class="form-control" id="pesan" name="pesan" rows="6" placeholder="Tulis pesan yang akan dikirim..." required></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Template Pesan</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <button type="button" class="list-group-item list-group-item-action" onclick="loadTemplate('konfirmasi_pesanan')">
                        Konfirmasi Pesanan
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="loadTemplate('pengiriman')">
                        Update Pengiriman
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="loadTemplate('return_disetujui')">
                        Return Disetujui
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="loadTemplate('komplain_resolved')">
                        Komplain Resolved
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="loadTemplate('promo')">
                        Informasi Promo
                    </button>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Integrasi Komunikasi</h5>
            </div>
            <div class="card-body">
                <h6>Tersedia:</h6>
                <ul>
                    <li><i class="fab fa-whatsapp text-success"></i> WhatsApp Business API</li>
                    <li><i class="fas fa-envelope text-primary"></i> Email SMTP</li>
                    <li><i class="fas fa-sms text-info"></i> SMS Gateway</li>
                    <li><i class="fas fa-bell text-warning"></i> Notifikasi Internal</li>
                </ul>

                <hr>

                <h6>Catatan:</h6>
                <p class="small text-muted">
                    Saat ini menggunakan placeholder. Integrasi dengan API pihak ketiga (WhatsApp, SMS, Email) dapat ditambahkan sesuai kebutuhan.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
const templates = {
    'konfirmasi_pesanan': {
        subjek: 'Konfirmasi Pesanan',
        pesan: `Yth. [Nama Pelanggan],

Terima kasih telah berbelanja di Koperasi Pemasaran Kepolisian Polres Samosir.

Pesanan Anda dengan nomor [No Order] telah kami terima dan sedang diproses.

Detail pesanan:
- Total: Rp [Total]
- Metode pembayaran: [Metode]

Kami akan menginformasikan update selanjutnya.

Salam,
Tim Customer Service
KSP Samosir`
    },
    'pengiriman': {
        subjek: 'Update Status Pengiriman',
        pesan: `Yth. [Nama Pelanggan],

Pesanan Anda dengan nomor [No Order] telah dikirim.

Detail pengiriman:
- Kurir: [Kurir]
- No. Resi: [No Resi]
- Estimasi tiba: [Estimasi]

Anda dapat melacak pengiriman melalui website kurir.

Salam,
Tim Customer Service
KSP Samosir`
    },
    'return_disetujui': {
        subjek: 'Return Request Disetujui',
        pesan: `Yth. [Nama Pelanggan],

Return request untuk order [No Order] telah disetujui.

Silakan kirim barang ke alamat:
[Alamat Return]

Setelah barang diterima, refund sebesar Rp [Jumlah Refund] akan diproses dalam 3-5 hari kerja.

Salam,
Tim Customer Service
KSP Samosir`
    },
    'komplain_resolved': {
        subjek: 'Komplain Telah Diselesaikan',
        pesan: `Yth. [Nama Pelanggan],

Komplain Anda dengan nomor tiket [No Tiket] telah diselesaikan.

Solusi: [Deskripsi Solusi]

Jika ada pertanyaan lebih lanjut, silakan hubungi kami.

Terima kasih atas masukannya.

Salam,
Tim Customer Service
KSP Samosir`
    },
    'promo': {
        subjek: 'Informasi Promo Spesial',
        pesan: `Yth. [Nama Pelanggan],

Kami memiliki promo spesial untuk Anda!

[Deskripsi Promo]

Gunakan kode promo: [Kode Promo]
Berlaku sampai: [Tanggal Akhir]

Kunjungi website kami untuk detail lebih lanjut.

Salam,
Tim Customer Service
KSP Samosir`
    }
};

function loadTemplate(templateKey) {
    const template = templates[templateKey];
    if (template) {
        document.getElementById('subjek').value = template.subjek;
        document.getElementById('pesan').value = template.pesan;
    }
}
</script>
