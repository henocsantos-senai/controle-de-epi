<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Emprestimo;
use App\Models\Equipamento;
use App\Models\Colaborador;
use PDOException;

class EmprestimoController extends Controller
{
    private Emprestimo $emprestimoModel;
    private Equipamento $equipamentoModel;
    private Colaborador $colaboradorModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->emprestimoModel = new Emprestimo();
        $this->equipamentoModel = new Equipamento();
        $this->colaboradorModel = new Colaborador();
    }

    public function handle(string $acao): void
    {
        $tituloPagina = 'Tela de Controle de EPI';
        $acao = ($acao === 'index') ? 'relatorio' : $acao;
        $mensagemSucesso = '';
        $mensagemErro = '';

        $statusCadastro = ['Emprestado', 'Fornecido'];
        $statusEdicao = ['Emprestado', 'Fornecido', 'Devolvido', 'Danificado', 'Perdido'];
        $statusExigemDevolucao = ['Devolvido', 'Danificado', 'Perdido'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $operacao = $_POST['operacao'] ?? '';

            if ($operacao === 'cadastrar') {
                $equipamento_id = filter_input(INPUT_POST, 'equipamento_id', FILTER_VALIDATE_INT);
                $colaborador_id = filter_input(INPUT_POST, 'colaborador_id', FILTER_VALIDATE_INT);
                $data_emprestimo = $_POST['data_emprestimo'] ?? '';
                $data_prevista_devolucao = !empty($_POST['data_prevista_devolucao']) ? $_POST['data_prevista_devolucao'] : null;
                $status = $_POST['status'] ?? '';
                $condicoes_emprestimo = trim($_POST['condicoes_emprestimo'] ?? '');

                if (!$equipamento_id || !$colaborador_id || empty($data_emprestimo) || !in_array($status, $statusCadastro, true)) {
                    $mensagemErro = "Erro ao realizar cadastro do empréstimo! Código: 400";
                    $acao = 'cadastrar';
                } else {
                    try {
                        $this->emprestimoModel->criar($equipamento_id, $colaborador_id, $data_emprestimo, $data_prevista_devolucao, $status, $condicoes_emprestimo);
                        $mensagemSucesso = "Item cadastrado com sucesso!";
                        $acao = 'cadastrar';
                    } catch (PDOException $e) {
                        $mensagemErro = "Erro ao realizar cadastro do empréstimo! Código: " . $e->getCode();
                        $acao = 'cadastrar';
                    }
                }
            } elseif ($operacao === 'atualizar') {
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $status = $_POST['status'] ?? '';
                $data_devolucao = !empty($_POST['data_devolucao']) ? $_POST['data_devolucao'] : null;
                $observacao_devolucao = trim($_POST['observacao_devolucao'] ?? '');

                if (!$id || !in_array($status, $statusEdicao, true)) {
                    $mensagemErro = "Erro ao atualizar! Código: 400 (Status inválido)";
                    $acao = 'editar';
                    $_GET['id'] = $id;
                } elseif (in_array($status, $statusExigemDevolucao, true) && empty($data_devolucao)) {
                    $mensagemErro = "Informe a Data da devolução para o status selecionado!";
                    $acao = 'editar';
                    $_GET['id'] = $id;
                } else {
                    if (!in_array($status, $statusExigemDevolucao, true)) {
                        $data_devolucao = null;
                        $observacao_devolucao = null;
                    }
                    try {
                        $this->emprestimoModel->atualizarStatus($id, $status, $data_devolucao, $observacao_devolucao);
                        $mensagemSucesso = "Status do empréstimo atualizado com sucesso!";
                        $acao = 'editar';
                        $_GET['id'] = $id;
                    } catch (PDOException $e) {
                        $mensagemErro = "Erro ao atualizar empréstimo! Código: " . $e->getCode();
                        $acao = 'editar';
                        $_GET['id'] = $id;
                    }
                }
            }
        }

        $listaEquipamentos = $this->equipamentoModel->listarParaSelect();
        $listaColaboradores = $this->colaboradorModel->listarParaSelect();

        $registroEdicao = null;
        if ($acao === 'editar') {
            $idEdicao = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($idEdicao) {
                $registroEdicao = $this->emprestimoModel->buscarPorIdDetalhado($idEdicao);
            }
            if (!$registroEdicao) {
                $mensagemErro = "Registro de empréstimo não encontrado.";
                $acao = 'relatorio';
            }
        }

        $filtroEquipamento = trim($_GET['filtro_equipamento'] ?? '');
        $filtroColaborador = trim($_GET['filtro_colaborador'] ?? '');
        $filtroStatus = trim($_GET['filtro_status'] ?? '');

        $listaEmprestimos = $this->emprestimoModel->filtrarRelatorio($filtroEquipamento, $filtroColaborador, $filtroStatus);

        $this->view('emprestimos/index', compact(
            'tituloPagina',
            'acao',
            'mensagemSucesso',
            'mensagemErro',
            'statusCadastro',
            'statusEdicao',
            'listaEquipamentos',
            'listaColaboradores',
            'registroEdicao',
            'filtroEquipamento',
            'filtroColaborador',
            'filtroStatus',
            'listaEmprestimos'
        ));
    }
}