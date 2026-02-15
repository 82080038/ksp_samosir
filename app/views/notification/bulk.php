<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="page-header">
    <h1 class="h2 page-title" id="page-title" style="color: black;" data-page="notification-bulk">Kirim Notifikasi Massal</h1>
    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
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
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>Form Notifikasi Massal</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= base_url('notification/sendBulkNotification') ?>">
                    <div class="mb-3">
                        <label for="target_audience" class="form-label">Target Audience *</label>
                        <select class="form-select" id="target_audience" name="target_audience" required>
                            <option value="all">Semua Anggota</option>
                            <option value="investors">Investor</option>
                            <option value="agents">Agen</option>
                            <option value="customers">Pelanggan Aktif</option>
                        </select>
                        <div class="form-text">Pilih kelompok penerima notifikasi</div>
                    </div>

                    <div class="mb-3">
                        <label for="channel" class="form-label">Kanal Pengiriman *</label>
                        <select class="form-select" id="channel" name="channel" required>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="sms">SMS</option>
                        </select>
                        <div class="form-text">Pilih kanal pengiriman notifikasi</div>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label">Subjek (Opsional)</label>
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Judul notifikasi">
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Pesan *</label>
                        <textarea class="form-control" id="message" name="message" rows="6" placeholder="Tulis pesan yang akan dikirim..." required></textarea>
                        <div class="form-text">Maksimal 500 karakter</div>
                    </div>

                    <div class="alert alert-info">
                        <h6>Informasi Pengiriman:</h6>
                        <ul>
                            <li><strong>WhatsApp:</strong> Pesan dikirim via WhatsApp Business API</li>
                            <li><strong>SMS:</strong> Pesan dikirim via SMS Gateway</li>
                            <li>Pengiriman dilakukan secara bertahap untuk menghindari spam</li>
                            <li>Sistem akan mencatat status pengiriman</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Kirim Notifikasi</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="previewMessage()">Preview Pesan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Template Pesan</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <button type="button" class="list-group-item list-group-item-action" onclick="loadTemplate('selamat_datang')">
                        Selamat Datang Anggota Baru
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="loadTemplate('promo_bulanan')">
                        Promo Bulanan
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="loadTemplate('pengingat_simpanan')">
                        Pengingat Simpanan
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="loadTemplate('update_aplikasi')">
                        Update Aplikasi
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="loadTemplate('rapat_anggota')">
                        Undangan Rapat Anggota
                    </button>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Statistik Pengiriman</h5>
            </div>
            <div class="card-body">
                <?php
                $total_sent = (fetchRow("SELECT COUNT(*) as total FROM notification_logs WHERE DATE(sent_at) = CURDATE()") ?? [])['total'] ?? 0;
                $whatsapp_sent = (fetchRow("SELECT COUNT(*) as total FROM notification_logs WHERE channel = 'whatsapp' AND DATE(sent_at) = CURDATE()") ?? [])['total'] ?? 0;
                $sms_sent = (fetchRow("SELECT COUNT(*) as total FROM notification_logs WHERE channel = 'sms' AND DATE(sent_at) = CURDATE()") ?? [])['total'] ?? 0;
                ?>
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-primary"><?= $total_sent ?></h4>
                        <small>Total Hari Ini</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-success"><?= $whatsapp_sent ?></h4>
                        <small>WhatsApp</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info"><?= $sms_sent ?></h4>
                        <small>SMS</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for message preview -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Pesan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent"></div>
            </div>
        </div>
    </div>
</div>

<script>
const templates = {
    'selamat_datang': {
        subject: 'Selamat Datang di KSP Samosir',
        message: `Selamat datang di Koperasi Pemasaran Kepolisian Polres Samosir!

Terima kasih telah bergabung dengan kami. Kami berkomitmen untuk memberikan pelayanan terbaik dalam hal simpanan, pinjaman, dan produk berkualitas.

Informasi lebih lanjut dapat diakses melalui aplikasi kami.

Salam,
Tim KSP Samosir`
    },
    'promo_bulanan': {
        subject: 'Promo Spesial Bulan Ini',
        message: `Promo Spesial Bulan Ini!

Dapatkan diskon hingga 20% untuk semua produk koperasi. Promo berlaku untuk anggota aktif.

Kunjungi toko kami atau akses melalui aplikasi.

Periode promo: 1-31 bulan ini.

Salam,
Tim KSP Samosir`
    },
    'pengingat_simpanan': {
        subject: 'Pengingat Simpanan Wajib',
        message: `Pengingat Simpanan Wajib

Jangan lupa untuk melakukan setoran simpanan wajib bulan ini. Simpanan wajib merupakan bagian penting dari partisipasi Anda sebagai anggota koperasi.

Informasi lebih lanjut hubungi pengurus.

Salam,
Tim KSP Samosir`
    },
    'update_aplikasi': {
        subject: 'Update Aplikasi KSP Samosir',
        message: `Update Aplikasi KSP Samosir

Aplikasi kami telah diperbarui dengan fitur-fitur terbaru:
- Dashboard yang lebih informatif
- Sistem pembayaran yang lebih aman
- Laporan yang lebih detail

Silakan update aplikasi Anda ke versi terbaru.

Salam,
Tim KSP Samosir`
    },
    'rapat_anggota': {
        subject: 'Undangan Rapat Anggota',
        message: `Undangan Rapat Anggota

Kami mengundang Anda untuk hadir dalam Rapat Anggota Tahunan yang akan dilaksanakan pada:

Tanggal: [Tanggal Rapat]
Waktu: [Waktu Rapat]
Tempat: [Tempat Rapat]

Agenda: [Agenda Rapat]

Kehadiran Anda sangat penting untuk kemajuan koperasi.

Salam,
Pengurus KSP Samosir`
    }
};

function loadTemplate(templateKey) {
    const template = templates[templateKey];
    if (template) {
        document.getElementById('subject').value = template.subject;
        document.getElementById('message').value = template.message;
    }
}

function previewMessage() {
    const message = document.getElementById('message').value;
    const subject = document.getElementById('subject').value;
    const channel = document.getElementById('channel').value;
    
    let previewContent = '';
    if (subject) {
        previewContent += `<h6>${subject}</h6>`;
    }
    previewContent += `<p>${message.replace(/\n/g, '<br>')}</p>`;
    previewContent += `<small class="text-muted">Dikirim via: ${channel.toUpperCase()}</small>`;
    
    document.getElementById('previewContent').innerHTML = previewContent;
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

// Character counter for message
document.getElementById('message').addEventListener('input', function() {
    const maxLength = 500;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    let text = `Maksimal 500 karakter`;
    if (remaining < 50) {
        text += ` - Tersisa ${remaining} karakter`;
        this.classList.add('is-warning');
    } else {
        this.classList.remove('is-warning');
    }
    
    this.nextElementSibling.textContent = text;
});
</script>
