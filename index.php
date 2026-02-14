<?php
/**
 * Main Entry Point
 * KSP Samosir - Aplikasi Koperasi Cepat
 */

// Start session
session_start();

require_once __DIR__ . '/config/config.php';

// Route handling
$request = $_SERVER['REQUEST_URI'];
$request = str_replace('/ksp_samosir', '', $request);
$request = rtrim($request, '/');
$segments = explode('/', $request);

$page = '';
if ($segments[0] === '') {
    $page = $segments[1] ?? '';
} else {
    $page = $segments[0];
}

if ($page === '') {
    redirect('dashboard');
}

$action = $segments[2] ?? 'index';
$id = $segments[3] ?? null;

// Authentication check (DISABLED for development)
// $public_pages = ['login', 'logout', 'register'];
// if (!in_array($page, $public_pages) && !isLoggedIn()) {
//     redirect('login');
// }

// Module-based access control (DISABLED for development)
// if (!in_array($page, $public_pages) && !canAccessModule($page)) {
//     flashMessage('error', 'Anda tidak memiliki izin untuk mengakses modul ' . $page);
//     redirect('dashboard');
// }

// Route to controller
$routeMatched = false;
switch ($page) {
    case 'logout':
        require_once __DIR__ . '/app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        $routeMatched = true;
        break;
        
    case 'dashboard':
        require_once __DIR__ . '/app/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        $routeMatched = true;
        break;
        
    case 'logs':
        require_once __DIR__ . '/app/controllers/LogsController.php';
        $controller = new LogsController();
        $controller->index();
        $routeMatched = true;
        break;
        
    case 'settings':
        require_once __DIR__ . '/app/controllers/SettingsController.php';
        $controller = new SettingsController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'update':
                $controller->update();
                break;
            case 'users':
                $controller->users();
                break;
            case 'createUser':
                $controller->createUser();
                break;
            case 'editUser':
                $controller->editUser($id);
                break;
            case 'processReturn':
                $controller->processReturn($id);
                break;
            case 'approveReturn':
                $controller->approveReturn($id);
                break;
            case 'refunds':
                $controller->refunds();
                break;
            case 'processRefund':
                $controller->processRefund($id);
                break;
            case 'updateUser':
                $controller->updateUser($id);
                break;
            case 'deleteUser':
                $controller->deleteUser($id);
                break;
            case 'roles':
                $controller->roles();
                break;
            case 'editRole':
                $controller->editRole($id);
                break;
            case 'updateRole':
                $controller->updateRole($id);
                break;
            case 'accounting':
                $controller->accounting();
                break;
            case 'createJournal':
                $controller->createJournal();
                break;
            case 'postJournal':
                $controller->postJournal($id);
                break;
            case 'reports':
                $controller->reports();
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'anggota':
        require_once __DIR__ . '/app/controllers/AnggotaController.php';
        $controller = new AnggotaController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'create':
                $controller->create();
                break;
            case 'edit':
                $controller->edit($id);
                break;
            case 'store':
                $controller->store();
                break;
            case 'update':
                $controller->update($id);
                break;
            case 'delete':
                $controller->delete($id);
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'simpanan':
        require_once __DIR__ . '/app/controllers/SimpananController.php';
        $controller = new SimpananController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'create':
                $controller->create();
                break;
            case 'edit':
                $controller->edit($id);
                break;
            case 'store':
                $controller->store();
                break;
            case 'update':
                $controller->update($id);
                break;
            case 'delete':
                $controller->delete($id);
                break;
            case 'transaksi':
                $controller->transaksi();
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'pinjaman':
        require_once __DIR__ . '/app/controllers/PinjamanController.php';
        $controller = new PinjamanController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'create':
                $controller->create();
                break;
            case 'edit':
                $controller->edit($id);
                break;
            case 'store':
                $controller->store();
                break;
            case 'update':
                $controller->update($id);
                break;
            case 'delete':
                $controller->delete($id);
                break;
            case 'approve':
                $controller->approve($id);
                break;
            case 'cairkan':
                $controller->cairkan($id);
                break;
            case 'angsuran':
                $controller->angsuran();
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'angsuran':
        require_once __DIR__ . '/app/controllers/PinjamanController.php';
        $controller = new PinjamanController();
        $controller->angsuran();
        $routeMatched = true;
        break;
        
    case 'rapat':
        require_once __DIR__ . '/app/controllers/RapatController.php';
        $controller = new RapatController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'create':
                $controller->create();
                break;
            case 'store':
                $controller->store();
                break;
            case 'edit':
                $controller->edit($id);
                break;
            case 'update':
                $controller->update($id);
                break;
            case 'delete':
                $controller->delete($id);
                break;
            case 'detail':
                $controller->detail($id);
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'produk':
        require_once __DIR__ . '/app/controllers/ProdukController.php';
        $controller = new ProdukController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'create':
                $controller->create();
                break;
            case 'edit':
                $controller->edit($id);
                break;
            case 'store':
                $controller->store();
                break;
            case 'update':
                $controller->update($id);
                break;
            case 'delete':
                $controller->delete($id);
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'penjualan':
        require_once __DIR__ . '/app/controllers/PenjualanController.php';
        $controller = new PenjualanController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'create':
                $controller->create();
                break;
            case 'edit':
                $controller->edit($id);
                break;
            case 'store':
                $controller->store();
                break;
            case 'update':
                $controller->update($id);
                break;
            case 'delete':
                $controller->delete($id);
                break;
            case 'detail':
                $controller->detail($id);
                break;
            case 'agentSales':
                $controller->agentSales();
                break;
            case 'createAgentSale':
                $controller->createAgentSale();
                break;
            case 'storeAgentSale':
                $controller->storeAgentSale();
                break;
            case 'commissions':
                $controller->commissions();
                break;
            case 'promos':
                $controller->promos();
                break;
            case 'createPromo':
                $controller->createPromo();
                break;
            case 'storePromo':
                $controller->storePromo();
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'laporan':
        require_once __DIR__ . '/app/controllers/LaporanController.php';
        $controller = new LaporanController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'simpanan':
                $controller->simpanan();
                break;
            case 'pinjaman':
                $controller->pinjaman();
                break;
            case 'penjualan':
                $controller->penjualan();
                break;
            case 'neraca':
                $controller->neraca();
                break;
            case 'laba_rugi':
                $controller->labaRugi();
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'login':
        require_once __DIR__ . '/app/controllers/AuthController.php';
        $controller = new AuthController();
        switch ($action) {
            case 'index':
                $controller->login();
                break;
            case 'authenticate':
                $controller->authenticate();
                break;
            default:
                $controller->login();
        }
        $routeMatched = true;
        break;
        
    case 'register':
        require_once __DIR__ . '/app/controllers/AuthController.php';
        $controller = new AuthController();
        switch ($action) {
            case 'index':
                $controller->register();
                break;
            case 'store':
                $controller->store();
                break;
            default:
                $controller->register();
        }
        $routeMatched = true;
        break;
        
    case 'api':
        require_once __DIR__ . '/app/controllers/ApiController.php';
        $controller = new ApiController();
        $controller->handleRequest();
        $routeMatched = true;
        break;
        
    case 'pengawas':
        require_once __DIR__ . '/app/controllers/PengawasController.php';
        $controller = new PengawasController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'logs':
                $controller->logs();
                break;
            case 'violations':
                $controller->violations();
                break;
            case 'sanctions':
                $controller->sanctions();
                break;
            case 'reports':
                $controller->reports();
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'shu':
        require_once __DIR__ . '/app/controllers/ShuController.php';
        $controller = new ShuController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'calculate':
                $controller->calculate();
                break;
            case 'createDistribution':
                $controller->createDistribution();
                break;
            case 'distribute':
                $controller->distribute();
                break;
            case 'reports':
                $controller->reports();
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'payment':
        require_once __DIR__ . '/app/controllers/PaymentController.php';
        $controller = new PaymentController();
        switch ($action) {
            case 'createPayment':
                $controller->createPayment($id);
                break;
            case 'notification':
                $controller->handleNotification();
                break;
            case 'success':
                $controller->success();
                break;
            case 'pending':
                $controller->pending();
                break;
            case 'error':
                $controller->error();
                break;
            case 'checkStatus':
                $controller->checkStatus($id);
                break;
            default:
                redirect('penjualan');
        }
        $routeMatched = true;
        break;
        
    case 'shipping':
        require_once __DIR__ . '/app/controllers/ShippingController.php';
        $controller = new ShippingController();
        switch ($action) {
            case 'calculateCost':
                $controller->calculateCost();
                break;
            case 'getProvinces':
                $controller->getProvinces();
                break;
            case 'getCities':
                $controller->getCities($id);
                break;
            case 'getShippingOptions':
                $controller->getShippingOptions();
                break;
            case 'saveShipping':
                $controller->saveShipping($id);
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Shipping endpoint not found']);
        }
        $routeMatched = true;
        break;
        
    case 'backup':
        require_once __DIR__ . '/app/controllers/BackupController.php';
        $controller = new BackupController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'createBackup':
                $controller->createBackup();
                break;
            case 'downloadBackup':
                $controller->downloadBackup($id);
                break;
            case 'deleteBackup':
                $controller->deleteBackup($id);
                break;
            case 'restoreBackup':
                $controller->restoreBackup($id);
                break;
            case 'scheduleBackup':
                $controller->scheduleBackup();
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'invoice':
        require_once __DIR__ . '/app/controllers/InvoiceController.php';
        $controller = new InvoiceController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'customerInvoices':
                $controller->customerInvoices();
                break;
            case 'generateCustomerInvoice':
                $controller->generateCustomerInvoice($id);
                break;
            case 'supplierInvoices':
                $controller->supplierInvoices();
                break;
            case 'recordSupplierInvoice':
                $controller->recordSupplierInvoice();
                break;
            case 'markAsPaid':
                $controller->markAsPaid($id, isset($_GET['type']) ? $_GET['type'] : 'customer');
                break;
            case 'downloadInvoice':
                $controller->downloadInvoice($id, isset($_GET['type']) ? $_GET['type'] : 'customer');
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'risk':
        require_once __DIR__ . '/app/controllers/RiskController.php';
        $controller = new RiskController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'monitorTransactions':
                $controller->monitorTransactions();
                break;
            case 'compliance':
                $controller->compliance();
                break;
            case 'generateComplianceReport':
                $controller->generateComplianceReport();
                break;
            case 'fraudDetection':
                $controller->fraudDetection();
                break;
            case 'memberRiskAssessment':
                $controller->memberRiskAssessment();
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'asset':
        require_once __DIR__ . '/app/controllers/AssetController.php';
        $controller = new AssetController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'assets':
                $controller->assets();
                break;
            case 'addAsset':
                $controller->addAsset();
                break;
            case 'assetDetail':
                $controller->assetDetail($id);
                break;
            case 'recordMaintenance':
                $controller->recordMaintenance($id);
                break;
            case 'depreciationReport':
                $controller->depreciationReport();
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'blockchain':
        require_once __DIR__ . '/app/controllers/BlockchainController.php';
        $controller = new BlockchainController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'recordGovernanceDecision':
                $controller->recordGovernanceDecision();
                break;
            case 'verifyIntegrity':
                $controller->verifyIntegrity();
                break;
            case 'transactionHistory':
                $controller->transactionHistory();
                break;
            case 'transparencyReport':
                $controller->transparencyReport();
                break;
            case 'blockDetail':
                $controller->blockDetail($id);
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    case 'monitoring':
        require_once __DIR__ . '/app/controllers/MonitoringController.php';
        $controller = new MonitoringController();
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'runHealthChecks':
                $controller->runHealthChecks();
                break;
            case 'performanceReport':
                $controller->performanceReport();
                break;
            case 'logs':
                $controller->logs();
                break;
            case 'alerts':
                $controller->alerts();
                break;
            case 'acknowledgeAlert':
                $controller->acknowledgeAlert($id);
                break;
            default:
                $controller->index();
        }
        $routeMatched = true;
        break;
        
    default:
        $routeMatched = false;
}

if (!$routeMatched) {
    http_response_code(404);
    require_once __DIR__ . '/app/views/errors/404.php';
    exit;
}
