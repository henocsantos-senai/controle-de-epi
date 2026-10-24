<?php
date_default_timezone_set('America/Sao_Paulo');

// Blindagem do Cookie de Sessão contra roubo via XSS
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Autoloader automático para carregar classes dos namespaces App\*
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

$rota = $_GET['rota'] ?? 'home';
$acao = $_GET['acao'] ?? 'index';

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\EquipamentoController;
use App\Controllers\ColaboradorController;
use App\Controllers\EmprestimoController;
use App\Controllers\ContaController;
use App\Controllers\SistemaController;

switch ($rota) {
    case 'login':
        (new AuthController())->login();
        break;
    case 'cadastro_usuario':
        (new AuthController())->cadastro();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    case 'recuperacao_senha':
        (new AuthController())->recuperacao();
        break;
    case 'redefinir_senha':
        (new AuthController())->redefinir();
        break;
    case 'home':
        (new HomeController())->index();
        break;
    case 'equipamentos':
        (new EquipamentoController())->handle($acao);
        break;
    case 'colaboradores':
        (new ColaboradorController())->handle($acao);
        break;
    case 'controle_epi':
        (new EmprestimoController())->handle($acao);
        break;
    case 'conta':
        (new ContaController())->index();
        break;
    case 'sistema':
        (new SistemaController())->handle($acao);
        break;
    default:
        (new HomeController())->index();
        break;
}