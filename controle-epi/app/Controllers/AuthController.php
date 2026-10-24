<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Models\RecuperacaoSenha;
use PDOException;

class AuthController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function login(): void
    {
        if (!empty($_SESSION['usuario_id']) && !empty($_SESSION['usuario_nome'])) {
            $this->redirect('index.php?rota=home');
        }

        $erro = '';
        $sucesso = (isset($_GET['cadastro']) && $_GET['cadastro'] === 'sucesso')
            ? 'Usuário cadastrado/atualizado com sucesso! Faça login abaixo.'
            : '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';

            if ($email !== '' && $senha !== '') {
                $usuario = $this->usuarioModel->buscarPorEmail($email);
                if ($usuario && password_verify($senha, $usuario['senha'])) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    $_SESSION['usuario_email'] = $usuario['email'];
                    $_SESSION['usuario_perfil'] = $usuario['perfil'] ?? 'Operador';
                    $this->redirect('index.php?rota=home');
                } else {
                    $erro = 'E-mail ou Senha incorretos!';
                }
            } else {
                $erro = 'Preencha todos os campos obrigatórios!';
            }
        }

        $this->view('auth/login', compact('erro', 'sucesso'), false);
    }

    public function cadastro(): void
    {
        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $confirma_senha = $_POST['confirma_senha'] ?? '';

            if (empty($nome) || empty($email) || empty($senha)) {
                $mensagem = 'Preencha todos os campos obrigatórios!';
            } elseif ($senha !== $confirma_senha) {
                $mensagem = 'As palavras-passe digitadas não coincidem!';
            } elseif (strlen($senha) < 6) {
                $mensagem = 'A Senha deve ter pelo menos 6 caracteres!';
            } else {
                try {
                    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                    $this->usuarioModel->criar($nome, $email, $senhaHash);
                    $this->redirect('index.php?rota=login&cadastro=sucesso');
                } catch (PDOException $e) {
                    $mensagem = ($e->getCode() == '23000')
                        ? 'Este e-mail já está registado no sistema!'
                        : 'Erro ao registar utilizador! Código: ' . $e->getCode();
                }
            }
        }

        $this->view('auth/cadastro', compact('mensagem'), false);
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        $this->redirect('index.php?rota=login');
    }

    public function recuperacao(): void
    {
        $mensagem = '';
        $linkSimulado = '';
        $recModel = new RecuperacaoSenha();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            if ($email !== '') {
                $usuario = $this->usuarioModel->buscarPorEmail($email);
                if ($usuario) {
                    $recModel->invalidarTokensAntigos((int) $usuario['id']);
                    $tokenPuro = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $tokenPuro);
                    $expiraEm = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                    $recModel->criarToken((int) $usuario['id'], $tokenHash, $expiraEm);

                    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
                    $urlBase = $protocolo . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
                    $linkSimulado = rtrim($urlBase, '/\\') . "/index.php?rota=redefinir_senha&token=" . $tokenPuro;
                }
                $mensagem = 'Se o e-mail informado estiver cadastrado, um link de redefinição válido por 15 minutos foi enviado.';
            }
        }

        $this->view('auth/recuperacao', compact('mensagem', 'linkSimulado'), false);
    }

    public function redefinir(): void
    {
        $recModel = new RecuperacaoSenha();
        $tokenPuro = trim($_GET['token'] ?? $_POST['token'] ?? '');
        $erro = '';
        $tokenValido = false;
        $registroToken = null;

        if ($tokenPuro !== '' && strlen($tokenPuro) === 64) {
            $tokenHash = hash('sha256', $tokenPuro);
            $registroToken = $recModel->buscarTokenValido($tokenHash);

            if ($registroToken) {
                $tokenValido = true;
            } else {
                $erro = 'Este link de recuperação é inválido, já foi utilizado ou expirou (limite de 15 minutos).';
            }
        } else {
            $erro = 'Token de recuperação ausente ou em formato inválido.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValido) {
            $nova_senha = $_POST['nova_senha'] ?? '';
            $confirma_senha = $_POST['confirma_senha'] ?? '';

            if (strlen($nova_senha) < 6) {
                $erro = 'A nova senha deve ter pelo menos 6 caracteres!';
            } elseif ($nova_senha !== $confirma_senha) {
                $erro = 'As senhas digitadas não coincidem!';
            } else {
                $senhaHash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $sucesso = $recModel->redefinirSenha((int) $registroToken['id'], (int) $registroToken['usuario_id'], $senhaHash);
                if ($sucesso) {
                    $this->redirect('index.php?rota=login&cadastro=sucesso');
                } else {
                    $erro = 'Erro ao redefinir a senha. Tente novamente.';
                }
            }
        }

        $this->view('auth/redefinir', compact('tokenPuro', 'erro', 'tokenValido', 'registroToken'), false);
    }
}