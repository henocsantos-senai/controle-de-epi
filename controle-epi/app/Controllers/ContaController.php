<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use PDOException;

class ContaController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->usuarioModel = new Usuario();
    }

    public function index(): void
    {
        $tituloPagina = 'Minha Conta - Controle de EPI';
        $usuarioId = (int) $_SESSION['usuario_id'];
        $mensagem = '';
        $tipoAlerta = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha_atual = $_POST['senha_atual'] ?? '';
            $nova_senha = $_POST['nova_senha'] ?? '';
            $confirma_senha = $_POST['confirma_senha'] ?? '';

            if (empty($nome) || empty($email)) {
                $mensagem = 'O Nome e o E-mail são obrigatórios!';
                $tipoAlerta = 'erro';
            } else {
                try {
                    $usuarioBanco = $this->usuarioModel->buscarPorId($usuarioId);
                    if (!$usuarioBanco) {
                        $mensagem = 'Utilizador não encontrado!';
                        $tipoAlerta = 'erro';
                    } else {
                        if ($nova_senha !== '' || $confirma_senha !== '') {
                            if (empty($senha_atual) || !password_verify($senha_atual, $usuarioBanco['senha'])) {
                                $mensagem = 'A palavra-passe atual está incorreta!';
                                $tipoAlerta = 'erro';
                            } elseif ($nova_senha !== $confirma_senha) {
                                $mensagem = 'A nova palavra-passe e a confirmação não coincidem!';
                                $tipoAlerta = 'erro';
                            } elseif (strlen($nova_senha) < 6) {
                                $mensagem = 'A nova palavra-passe deve ter pelo menos 6 caracteres!';
                                $tipoAlerta = 'erro';
                            } else {
                                $senhaHash = password_hash($nova_senha, PASSWORD_DEFAULT);
                                $this->usuarioModel->atualizarConta($usuarioId, $nome, $email, $senhaHash);
                                $_SESSION['usuario_nome'] = $nome;
                                $_SESSION['usuario_email'] = $email;
                                $mensagem = 'Dados cadastrais e palavra-passe atualizados com sucesso!';
                                $tipoAlerta = 'sucesso';
                            }
                        } else {
                            $this->usuarioModel->atualizarConta($usuarioId, $nome, $email);
                            $_SESSION['usuario_nome'] = $nome;
                            $_SESSION['usuario_email'] = $email;
                            $mensagem = 'Dados cadastrais atualizados com sucesso!';
                            $tipoAlerta = 'sucesso';
                        }
                    }
                } catch (PDOException $e) {
                    $mensagem = ($e->getCode() == '23000')
                        ? 'Este endereço de e-mail já está a ser utilizado por outra conta!'
                        : 'Erro ao atualizar dados! Código: ' . $e->getCode();
                    $tipoAlerta = 'erro';
                }
            }
        }

        $meusDados = $this->usuarioModel->buscarPorId($usuarioId);
        $this->view('conta/index', compact('tituloPagina', 'mensagem', 'tipoAlerta', 'meusDados'));
    }
}