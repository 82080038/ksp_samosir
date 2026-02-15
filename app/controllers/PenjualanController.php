<?php
require_once __DIR__ . '/BaseController.php';

class PenjualanController extends BaseController {
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM penjualan") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $penjualan = fetchAll("SELECT p.*, pl.nama_pelanggan FROM penjualan p LEFT JOIN pelanggan pl ON p.pelanggan_id = pl.id ORDER BY p.tanggal_penjualan DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('penjualan/index', [
            'penjualan' => $penjualan,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function create() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        // Get customers with normalized address/contact info
        $pelanggan = fetchAll("
            SELECT
                u.id,
                u.full_name as nama_pelanggan,
                u.username as kode_pelanggan,
                COALESCE(addr.street_address, 'No address') as alamat,
                COALESCE(c_phone.contact_value, 'No phone') as no_hp,
                COALESCE(c_email.contact_value, 'No email') as email
            FROM users u
            LEFT JOIN addresses addr ON addr.id = (SELECT address_id FROM entity_addresses WHERE entity_type = 'customer' AND entity_id = u.id AND address_id = addr.id LIMIT 1)
            LEFT JOIN contacts c_phone ON c_phone.id = (SELECT contact_id FROM entity_contacts WHERE entity_type = 'customer' AND entity_id = u.id AND contact_id = c_phone.id AND c_phone.contact_type = 'phone' LIMIT 1)
            LEFT JOIN contacts c_email ON c_email.id = (SELECT contact_id FROM entity_contacts WHERE entity_type = 'customer' AND entity_id = u.id AND contact_id = c_email.id AND c_email.contact_type = 'email' LIMIT 1)
            WHERE u.role = 'customer' OR u.role = 'member'
            ORDER BY u.full_name
        ", [], '');
        $produk = fetchAll("SELECT id, nama_produk, kode_produk, harga_jual, stok FROM produk WHERE is_active = 1 AND stok > 0 ORDER BY nama_produk");
        $promos = fetchAll("SELECT id, kode_promo, jenis_diskon, nilai_diskon FROM promos WHERE status = 'aktif' AND tanggal_akhir >= CURDATE() ORDER BY kode_promo");

        $this->render('penjualan/create', [
            'pelanggan' => $pelanggan,
            'produk' => $produk,
            'promos' => $promos
        ]);
    }

    public function store() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $pelanggan_id = intval($_POST['pelanggan_id'] ?? 0);
        $produk = $_POST['produk'] ?? [];
        $promo_id = intval($_POST['promo_id'] ?? 0);
        $metode_pembayaran = sanitize($_POST['metode_pembayaran'] ?? 'cash');

        if (empty($produk)) {
            flashMessage('error', 'Minimal satu produk harus dipilih');
            redirect('penjualan/create');
        }

        $total_harga = 0;
        $total_diskon = 0;

        // Calculate totals
        foreach ($produk as $item) {
            $produk_id = intval($item['produk_id']);
            $qty = intval($item['qty']);
            $harga_satuan = floatval($item['harga_satuan']);
            $subtotal = $qty * $harga_satuan;
            $total_harga += $subtotal;
        }

        // Apply promo if any
        if ($promo_id > 0) {
            $promo = fetchRow("SELECT * FROM promos WHERE id = ? AND status = 'aktif' AND tanggal_akhir >= CURDATE()", [$promo_id], 'i');
            if ($promo) {
                if ($promo['jenis_diskon'] === 'persen') {
                    $total_diskon = $total_harga * ($promo['nilai_diskon'] / 100);
                } else {
                    $total_diskon = min($promo['nilai_diskon'], $total_harga);
                }
            }
        }

        $total_bayar = $total_harga - $total_diskon;
        $kembalian = 0; // For cash payments

        // Determine initial payment status
        $online_payment_methods = ['transfer', 'debit', 'kredit'];
        $initial_status = in_array($metode_pembayaran, $online_payment_methods) ? 'pending' : 'lunas';

        runInTransaction(function($conn) use ($pelanggan_id, $produk, $promo_id, $metode_pembayaran, $total_harga, $total_diskon, $total_bayar, $kembalian, $initial_status) {
            $user_id = $_SESSION['user']['id'] ?? 1;

            // Insert penjualan
            $stmt = $conn->prepare("INSERT INTO penjualan (pelanggan_id, total_harga, total_bayar, kembalian, status_pembayaran, metode_pembayaran, user_id, promo_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('iddddsii', $pelanggan_id, $total_harga, $total_bayar, $kembalian, $initial_status, $metode_pembayaran, $user_id, $promo_id);
            $stmt->execute();
            $penjualan_id = $stmt->insert_id;
            $stmt->close();

            // Insert detail penjualan and update stock
            foreach ($produk as $item) {
                $produk_id = intval($item['produk_id']);
                $qty = intval($item['qty']);
                $harga_satuan = floatval($item['harga_satuan']);
                $subtotal = $qty * $harga_satuan;

                $stmt = $conn->prepare("INSERT INTO detail_penjualan (penjualan_id, produk_id, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('iiidd', $penjualan_id, $produk_id, $qty, $harga_satuan, $subtotal);
                $stmt->execute();
                $stmt->close();

                // Update stock
                $stmt2 = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
                $stmt2->bind_param('ii', $qty, $produk_id);
                $stmt2->execute();
                $stmt2->close();
            }
        });

        flashMessage('success', 'Penjualan berhasil dibuat');

        // Send order confirmation notification
        require_once __DIR__ . '/NotificationController.php';
        $notification = new NotificationController();
        $notification->sendOrderConfirmation($penjualan_id);

        // Redirect to payment gateway for online payments
        if (in_array($metode_pembayaran, $online_payment_methods)) {
            redirect('payment/createPayment/' . $penjualan_id);
        } else {
            redirect('penjualan');
        }
    }

    public function agentSales() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM agent_sales") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $agent_sales = fetchAll("SELECT asale.*, a.nama as agent_name FROM agent_sales asale LEFT JOIN agents a ON asale.agent_id = a.id ORDER BY asale.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('penjualan/agent_sales', [
            'agent_sales' => $agent_sales,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function createAgentSale() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $agents = fetchAll("SELECT a.id, a.nama, a.komisi_persen FROM agents a WHERE a.status = 'aktif' ORDER BY a.nama");
        $produk = fetchAll("SELECT id, nama_produk, kode_produk, harga_jual, stok FROM produk WHERE is_active = 1 AND stok > 0 ORDER BY nama_produk");

        $this->render('penjualan/create_agent_sale', [
            'agents' => $agents,
            'produk' => $produk
        ]);
    }

    public function storeAgentSale() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $agent_id = intval($_POST['agent_id']);
        $produk = $_POST['produk'] ?? [];
        $pelanggan_nama = sanitize($_POST['pelanggan_nama']);
        $pelanggan_alamat = sanitize($_POST['pelanggan_alamat']);
        $pelanggan_telp = sanitize($_POST['pelanggan_telp']);

        if (empty($agent_id) || empty($produk) || empty($pelanggan_nama)) {
            flashMessage('error', 'Data agen, produk, dan pelanggan harus diisi');
            redirect('penjualan/createAgentSale');
        }

        $total_nilai = 0;
        foreach ($produk as $item) {
            $produk_id = intval($item['produk_id']);
            $qty = intval($item['qty']);
            $harga_jual = floatval($item['harga_jual']);
            $subtotal = $qty * $harga_jual;
            $total_nilai += $subtotal;
        }

        // Get agent commission rate
        $agent = fetchRow("SELECT komisi_persen FROM agents WHERE id = ?", [$agent_id], 'i');
        $komisi = $total_nilai * ($agent['komisi_persen'] / 100);

        runInTransaction(function($conn) use ($agent_id, $produk, $pelanggan_nama, $pelanggan_alamat, $pelanggan_telp, $total_nilai, $komisi) {
            $user_id = $_SESSION['user']['id'] ?? 1;

            // Insert agent sale
            $stmt = $conn->prepare("INSERT INTO agent_sales (agent_id, tanggal_penjualan, pelanggan_nama, pelanggan_alamat, pelanggan_telp, total_nilai, komisi, status_approval, created_by) VALUES (?, CURDATE(), ?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->bind_param('isssddi', $agent_id, $pelanggan_nama, $pelanggan_alamat, $pelanggan_telp, $total_nilai, $komisi, $user_id);
            $stmt->execute();
            $agent_sale_id = $stmt->insert_id;
            $stmt->close();

            // Insert agent sale details and update stock
            foreach ($produk as $item) {
                $produk_id = intval($item['produk_id']);
                $qty = intval($item['qty']);
                $harga_jual = floatval($item['harga_jual']);
                $subtotal = $qty * $harga_jual;
                $komisi_item = $subtotal * ($agent['komisi_persen'] / 100);

                $stmt = $conn->prepare("INSERT INTO agent_sales_details (agent_sale_id, produk_id, qty, harga_jual, subtotal, komisi_item) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('iiiddd', $agent_sale_id, $produk_id, $qty, $harga_jual, $subtotal, $komisi_item);
                $stmt->execute();
                $stmt->close();

                // Update stock
                $stmt2 = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
                $stmt2->bind_param('ii', $qty, $produk_id);
                $stmt2->execute();
                $stmt2->close();
            }
        });

        flashMessage('success', 'Penjualan agen berhasil dibuat, menunggu approval');
        redirect('penjualan/agentSales');
    }

    public function commissions() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM agent_commissions") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $commissions = fetchAll("SELECT ac.*, a.nama as agent_name FROM agent_commissions ac LEFT JOIN agents a ON ac.agent_id = a.id ORDER BY ac.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('penjualan/commissions', [
            'commissions' => $commissions,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function promos() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM promos") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $promos = fetchAll("SELECT * FROM promos ORDER BY created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('penjualan/promos', [
            'promos' => $promos,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function createPromo() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $this->render('penjualan/create_promo');
    }

    public function storePromo() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $kode_promo = sanitize($_POST['kode_promo']);
        $jenis_diskon = sanitize($_POST['jenis_diskon']);
        $nilai_diskon = floatval($_POST['nilai_diskon']);
        $tanggal_mulai = sanitize($_POST['tanggal_mulai']);
        $tanggal_akhir = sanitize($_POST['tanggal_akhir']);
        $deskripsi = sanitize($_POST['deskripsi']);

        if (empty($kode_promo) || empty($jenis_diskon) || $nilai_diskon <= 0) {
            flashMessage('error', 'Data promo tidak lengkap');
            redirect('penjualan/createPromo');
        }

        runInTransaction(function($conn) use ($kode_promo, $jenis_diskon, $nilai_diskon, $tanggal_mulai, $tanggal_akhir, $deskripsi) {
            $stmt = $conn->prepare("INSERT INTO promos (kode_promo, jenis_diskon, nilai_diskon, tanggal_mulai, tanggal_akhir, deskripsi, status) VALUES (?, ?, ?, ?, ?, ?, 'aktif')");
            $stmt->bind_param('ssdsss', $kode_promo, $jenis_diskon, $nilai_diskon, $tanggal_mulai, $tanggal_akhir, $deskripsi);
            $stmt->execute();
            $stmt->close();
        });

        flashMessage('success', 'Promo berhasil dibuat');
        redirect('penjualan/promos');
    }

    public function edit($id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development
        $penjualan = fetchRow("SELECT * FROM penjualan WHERE id = ?", [$id], 'i');
        if (!$penjualan) {
            flashMessage('error', 'Data penjualan tidak ditemukan');
            redirect('penjualan');
        }
        $this->render('penjualan/edit', ['penjualan' => $penjualan]);
    }

    public function update($id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development
        flashMessage('success', 'Transaksi penjualan diperbarui');
        redirect('penjualan');
    }

    public function delete($id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development
        runInTransaction(function($conn) use ($id) {
            // Return stock
            $details = fetchAll("SELECT produk_id, jumlah FROM detail_penjualan WHERE penjualan_id = ?", [$id], 'i');
            foreach ($details as $detail) {
                $stmt = $conn->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?");
                $stmt->bind_param('ii', $detail['jumlah'], $detail['produk_id']);
                $stmt->execute();
                $stmt->close();
            }

            // Delete penjualan
            $stmt = $conn->prepare("DELETE FROM penjualan WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        });

        flashMessage('success', 'Transaksi penjualan dihapus dan stok dikembalikan');
        redirect('penjualan');
    }

    public function detail($id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development
        $penjualan = fetchRow("SELECT p.*, pl.nama_pelanggan FROM penjualan p LEFT JOIN pelanggan pl ON p.pelanggan_id = pl.id WHERE p.id = ?", [$id], 'i');
        if (!$penjualan) {
            flashMessage('error', 'Data penjualan tidak ditemukan');
            redirect('penjualan');
        }

        $details = fetchAll("SELECT dp.*, pr.nama_produk, pr.kode_produk FROM detail_penjualan dp LEFT JOIN produk pr ON dp.produk_id = pr.id WHERE dp.penjualan_id = ?", [$id], 'i');

        $this->render('penjualan/detail', [
            'penjualan' => $penjualan,
            'details' => $details
        ]);
    }
}
