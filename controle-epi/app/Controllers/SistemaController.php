<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use PDOException;

class SistemaController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->usuarioModel = new Usuario();
    }

    public function handle(string $acao): void
    {
        $usuarioLogado = (int) $_SESSION['usuario_id'];
        $perfilReal = $this->usuarioModel->buscarPerfil($usuarioLogado);

        if ($perfilReal !== 'Administrador') {
            http_response_code(403);
            die("Acesso negado: Esta página é restrita apenas a Administradores do sistema.");
        }

        $tituloPagina = 'Configurações do Sistema - Gerenciar Usuários';
        $mensagem = '';
        $tipoAlerta = '';
        $acao = ($acao === 'index') ? 'listar' : $acao;
        $perfilAtual = $perfilReal;

        if ($acao === 'excluir' && isset($_GET['id'])) {
            $idExcluir = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($idExcluir === $usuarioLogado) {
                $mensagem = 'Operação bloqueada: Você não pode remover a sua própria conta ativa!';
                $tipoAlerta = 'erro';
            } elseif ($idExcluir) {
                try {
                    $this->usuarioModel->excluir($idExcluir);
                    $mensagem = 'Usuário removido do sistema com sucesso!';
                    $tipoAlerta = 'sucesso';
                } catch (PDOException $e) {
                    $mensagem = 'Erro ao remover usuário! Código: ' . $e->getCode();
                    $tipoAlerta = 'erro';
                }
            }
            $acao = 'listar';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $perfil = $_POST['perfil'] ?? 'Operador';
            $senha = $_POST['senha'] ?? '';
            $perfil = $_POST['perfil'] ?? 'Colaborador';

            if (!in_array($perfil, ['Administrador', 'Almoxarife', 'Colaborador'], true)) {
                $perfil = 'Colaborador';
            }

            if (empty($nome) || empty($email)) {
                $mensagem = 'Preencha Nome e E-mail obrigatórios!';
                $tipoAlerta = 'erro';
                $acao = empty($id) ? 'cadastrar' : 'editar';
                $_GET['id'] = $id;
            } else {
                try {
                    if (empty($id)) {
                        if (strlen($senha) < 6) {
                            $mensagem = 'Para cadastrar um novo usuário, informe uma senha de no mínimo 6 caracteres!';
                            $tipoAlerta = 'erro';
                            $acao = 'cadastrar';
                        } else {
                            $this->usuarioModel->criar($nome, $email, password_hash($senha, PASSWORD_DEFAULT), $perfil);
                            $mensagem = 'Usuário cadastrado com sucesso pelo Administrador!';
                            $tipoAlerta = 'sucesso';
                            $acao = 'listar';
                        }
                    } else {
                        if ($senha !== '') {
                            if (strlen($senha) < 6) {
                                $mensagem = 'A nova senha deve ter pelo menos 6 caracteres!';
                                $tipoAlerta = 'erro';
                                $acao = 'editar';
                                $_GET['id'] = $id;
                            } else {
                                $this->usuarioModel->atualizarPeloAdmin((int) $id, $nome, $email, $perfil, password_hash($senha, PASSWORD_DEFAULT));
                                $mensagem = 'Usuário e senha atualizados com sucesso!';
                                $tipoAlerta = 'sucesso';
                                $acao = 'listar';
                            }
                        } else {
                            $this->usuarioModel->atualizarPeloAdmin((int) $id, $nome, $email, $perfil);
                            $mensagem = 'Dados do usuário atualizados com sucesso!';
                            $tipoAlerta = 'sucesso';
                            $acao = 'listar';
                        }

                        if ((int) $id === $usuarioLogado && $tipoAlerta === 'sucesso') {
                            $_SESSION['usuario_nome'] = $nome;
                            $_SESSION['usuario_email'] = $email;
                        }
                    }
                } catch (PDOException $e) {
                    $mensagem = ($e->getCode() == '23000')
                        ? 'Erro: Este e-mail já está em uso por outro usuário!'
                        : 'Erro ao salvar usuário! Código: ' . $e->getCode();
                    $tipoAlerta = 'erro';
                    $acao = empty($id) ? 'cadastrar' : 'editar';
                    $_GET['id'] = $id;
                }
            }
        }

        $usuarioEdite = null;
        if ($acao === 'editar' && isset($_GET['id'])) {
            $usuarioEdite = $this->usuarioModel->buscarPorId((int) $_GET['id']);
            if (!$usuarioEdite) {
                $mensagem = 'Usuário não encontrado.';
                $tipoAlerta = 'erro';
                $acao = 'listar';
            }
        }

        $pesquisa = trim($_GET['pesquisa'] ?? '');
        $listaUsuarios = $this->usuarioModel->listar($pesquisa);

        $this->view('sistema/index', compact(
            'tituloPagina',
            'usuarioLogado',
            'mensagem',
            'tipoAlerta',
            'acao',
            'perfilAtual',
            'usuarioEdite',
            'pesquisa',
            'listaUsuarios'
        ));
    }
}