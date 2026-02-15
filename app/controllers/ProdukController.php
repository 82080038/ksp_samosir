<?php
require_once __DIR__ . '/BaseController.php';

class ProdukController extends BaseController {
    public function index() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $this->render('produk/index');
    }

    public function create() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $this->render('produk/create');
    }

    public function store() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        flashMessage('success', 'Produk ditambahkan (stub).');
        redirect('produk');
    }

    public function edit($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $this->render('produk/edit', ['id' => $id]);
    }

    public function update($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        flashMessage('success', 'Produk diperbarui (stub).');
        redirect('produk');
    }

    public function delete($id) {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        flashMessage('success', 'Produk dihapus (stub).');
        redirect('produk');
    }
}
