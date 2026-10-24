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
        $this->requireRole(['Administrador', 'Almoxarife']);
        $this->model = new Equipamento();
    }

    public function handle(string $acao): void
    {
        $tituloPagina = 'Tela de Equipamento - Controle de EPI';
        $mensagem = '';
        $tipoAlerta = '';
        $acao = ($acao === 'index') ? 'listar' : $acao;

        if ($acao === 'excluir' && isset($_GET['id'])) {
            if (!$this->temPermissao(['Administrador'])) {
                $mensagem = "Acesso negado! Apenas Administradores podem excluir equipamentos.";
                $tipoAlerta = "erro";
            } else {
                $idExcluir = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
                if ($idExcluir) {
                    try {
                        $this->model->excluir($idExcluir);
                        $mensagem = "Equipamento removido com sucesso!";
                        $tipoAlerta = "sucesso";
                    } catch (PDOException $e) {
                        $mensagem = ($e->getCode() == '23000')
                            ? "Não é possível remover! Este equipamento possui empréstimos ativos em andamento."
                            : "Erro ao remover equipamento! Código: " . $e->getCode();
                        $tipoAlerta = "erro";
                    }
                }
            }
            $acao = 'listar';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $acao !== 'excluir') {
            $id = $_POST['id'] ?? '';
            $codigo_barras = trim($_POST['codigo_barras'] ?? '');
            $nome = trim($_POST['nome'] ?? '');
            $ca = trim($_POST['ca'] ?? '');
            $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
            $validade_ca = !empty($_POST['validade_ca']) ? $_POST['validade_ca'] : null;
            $descricao = trim($_POST['descricao'] ?? '');

            if ($quantidade === false || $quantidade === null || $quantidade < 0) {
                $mensagem = "Informe uma quantidade em estoque válida (0 ou mais)!";
                $tipoAlerta = "erro";
                $acao = empty($id) ? 'cadastrar' : 'editar';
                $_GET['id'] = $id;
            } elseif (!empty($nome) && !empty($ca)) {
                try {
                    if (empty($id)) {
                        $this->model->criar($nome, $ca, $descricao, $codigo_barras, $quantidade, $validade_ca);
                        $mensagem = "Equipamento cadastrado com sucesso!";
                        $tipoAlerta = "sucesso";
                        $acao = 'cadastrar';
                    } else {
                        $this->model->atualizar((int) $id, $nome, $ca, $descricao, $codigo_barras, $quantidade, $validade_ca);
                        $mensagem = "Equipamento atualizado com sucesso!";
                        $tipoAlerta = "sucesso";
                        $acao = 'listar';
                    }
                } catch (PDOException $e) {
                    $mensagem = ($e->getCode() == '23000')
                        ? "Erro: Já existe um equipamento com este CA ou Código de Barras!"
                        : "Erro ao realizar cadastro! Código: " . $e->getCode();
                    $tipoAlerta = "erro";
                    $acao = empty($id) ? 'cadastrar' : 'editar';
                    $_GET['id'] = $id;
                }
            } else {
                $mensagem = "Erro ao realizar cadastro! Preencha todos os campos obrigatórios.";
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