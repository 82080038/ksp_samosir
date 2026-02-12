<?php
require_once __DIR__ . '/BaseController.php';

class PenjualanController extends BaseController {
    public function index() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $this->render(__DIR__ . '/../views/penjualan/index.php');
    }

    public function create() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $this->render(__DIR__ . '/../views/penjualan/create.php');
    }

    public function store() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development

        flashMessage('success', 'Penjualan berhasil dibuat');
        // Payment gateway integration stub: e.g., redirect to Midtrans or similar
        // header('Location: https://payment.gateway.com/pay?amount=' . $total_harga);
        redirect('penjualan');
    }

    public function edit($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $this->render(__DIR__ . '/../views/penjualan/edit.php', ['id' => $id]);
    }

    public function update($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        flashMessage('success', 'Transaksi penjualan diperbarui (stub).');
        redirect('penjualan');
    }

    public function delete($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        flashMessage('success', 'Transaksi penjualan dihapus (stub).');
        redirect('penjualan');
    }

    public function detail($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $this->render(__DIR__ . '/../views/penjualan/detail.php', ['id' => $id]);
    }
}
