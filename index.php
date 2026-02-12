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
    if (isLoggedIn()) {
        redirect('dashboard');
    } else {
        redirect('login');
    }
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
        switch ($action) {
            case 'anggota':
                $controller->anggota();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'API endpoint not found']);
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
