<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Equipamento;
use PDOException;

class EquipamentoController extends Controller
{
    private Equipamento $model;

    public function __construct()
    {
        $this->requireAuth();
        $this->model = new Equipamento();
    }

    public function handle(string $acao): void
    {
        $tituloPagina = 'Tela de Equipamento - Controle de EPI';
        $mensagem = '';
        $tipoAlerta = '';
        $acao = ($acao === 'index') ? 'listar' : $acao;

        if ($acao === 'excluir' && isset($_GET['id'])) {
            $idExcluir = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($idExcluir) {
                try {
                    $this->model->excluir($idExcluir);
                    $mensagem = "Equipamento removido com sucesso!";
                    $tipoAlerta = "sucesso";
                } catch (PDOException $e) {
                    $mensagem = ($e->getCode() == '23000')
                        ? "Erro ao remover! Código: 23000 (Este equipamento possui empréstimos vinculados)"
                        : "Erro ao remover equipamento! Código: " . $e->getCode();
                    $tipoAlerta = "erro";
                }
            }
            $acao = 'listar';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $nome = trim($_POST['nome'] ?? '');
            $ca = trim($_POST['ca'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            if (!empty($nome) && !empty($ca)) {
                try {
                    if (empty($id)) {
                        $this->model->criar($nome, $ca, $descricao);
                        $mensagem = "Item cadastrado com sucesso!";
                        $tipoAlerta = "sucesso";
                        $acao = 'cadastrar';
                    } else {
                        $this->model->atualizar((int) $id, $nome, $ca, $descricao);
                        $mensagem = "Equipamento atualizado com sucesso!";
                        $tipoAlerta = "sucesso";
                        $acao = 'listar';
                    }
                } catch (PDOException $e) {
                    $mensagem = "Erro ao realizar cadastro! Código: " . $e->getCode();
                    $tipoAlerta = "erro";
                    $acao = empty($id) ? 'cadastrar' : 'editar';
                    $_GET['id'] = $id;
                }
            } else {
                $mensagem = "Erro ao realizar cadastro! Código: 400 (Preencha os campos obrigatórios)";
                $tipoAlerta = "erro";
                $acao = empty($id) ? 'cadastrar' : 'editar';
                $_GET['id'] = $id;
            }
        }

        $equipamentoEdite = null;
        if ($acao === 'editar' && isset($_GET['id'])) {
            $equipamentoEdite = $this->model->buscarPorId((int) $_GET['id']);
            if (!$equipamentoEdite) {
                $mensagem = "Equipamento não encontrado.";
                $tipoAlerta = "erro";
                $acao = 'listar';
            }
        }

        $pesquisa = trim($_GET['pesquisa'] ?? '');
        $equipamentos = $this->model->listar($pesquisa);

        $this->view('equipamentos/index', compact(
            'tituloPagina',
            'mensagem',
            'tipoAlerta',
            'acao',
            'equipamentoEdite',
            'pesquisa',
            'equipamentos'
        ));
    }
}