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

    private function formatarDateTimeBanco(?string $valorInput): ?string
    {
        if (empty($valorInput)) {
            return null;
        }
        $ts = strtotime($valorInput);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }

    public function handle(string $acao): void
    {
        if ($acao === 'termo') {
            $idTermo = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            $termo = $idTermo ? $this->emprestimoModel->buscarPorIdDetalhado($idTermo) : null;
            if (!$termo) {
                $this->redirect('index.php?rota=controle_epi&acao=relatorio');
            }
            $this->view('emprestimos/termo', compact('termo'), false);
            return;
        }
        
        $tituloPagina = 'Tela de Controle de EPI';
        $acao = ($acao === 'index') ? 'relatorio' : $acao;
        $mensagemSucesso = '';
        $mensagemErro = '';
        $usuarioLogadoId = (int) ($_SESSION['usuario_id'] ?? 0);

        if (($acao === 'cadastrar' || $acao === 'editar' || $_SERVER['REQUEST_METHOD'] === 'POST') && !$this->temPermissao(['Administrador', 'Almoxarife'])) {
            $mensagemErro = "Acesso negado: Apenas Administradores e Almoxarifes podem registrar ou alterar empréstimos.";
            $acao = 'relatorio';
        }

        $statusCadastro = ['Emprestado', 'Fornecido'];
        $statusEdicao = ['Emprestado', 'Fornecido', 'Devolvido', 'Danificado', 'Perdido'];
        $statusExigemDevolucao = ['Devolvido', 'Danificado', 'Perdido'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->temPermissao(['Administrador', 'Almoxarife'])) {
            $operacao = $_POST['operacao'] ?? '';

            if ($operacao === 'cadastrar') {
                $equipamento_id = filter_input(INPUT_POST, 'equipamento_id', FILTER_VALIDATE_INT);
                $colaborador_id = filter_input(INPUT_POST, 'colaborador_id', FILTER_VALIDATE_INT);
                $data_emprestimo = $this->formatarDateTimeBanco($_POST['data_emprestimo'] ?? '');
                $data_prevista_devolucao = $this->formatarDateTimeBanco($_POST['data_prevista_devolucao'] ?? '');
                $status = $_POST['status'] ?? '';
                $condicoes_emprestimo = trim($_POST['condicoes_emprestimo'] ?? '');

                $equipamentoBanco = $equipamento_id ? $this->equipamentoModel->buscarPorId($equipamento_id) : null;
                $caVencido = !empty($equipamentoBanco['validade_ca']) && (strtotime($equipamentoBanco['validade_ca']) < strtotime(date('Y-m-d')));

                if (!$equipamento_id || !$colaborador_id || empty($data_emprestimo) || empty($data_prevista_devolucao) || !in_array($status, $statusCadastro, true)) {
                    $mensagemErro = "Preencha todos os campos obrigatórios (incluindo a data prevista para devolução)!";
                    $acao = 'cadastrar';
                } elseif (!$equipamentoBanco || (int) ($equipamentoBanco['quantidade'] ?? 0) <= 0) {
                    $mensagemErro = "Operação bloqueada: O equipamento selecionado está ESGOTADO no estoque (0 unidades)!";
                    $acao = 'cadastrar';
                } elseif ($caVencido) {
                    $mensagemErro = "Operação bloqueada (NR-6): O Certificado de Aprovação (CA) deste EPI está VENCIDO desde " . date('d/m/Y', strtotime($equipamentoBanco['validade_ca'])) . "!";
                    $acao = 'cadastrar';
                } elseif (strtotime($data_prevista_devolucao) <= time()) {
                    $mensagemErro = "A data e hora previstas para devolução devem ser posteriores à data e hora atuais (" . date('d/m/Y H:i') . ")!";
                    $acao = 'cadastrar';
                } elseif (strtotime($data_prevista_devolucao) <= strtotime($data_emprestimo)) {
                    $mensagemErro = "A data prevista para devolução deve ser posterior à data de entrega do equipamento!";
                    $acao = 'cadastrar';
                } else {
                    try {
                        $this->emprestimoModel->criar(
                            $equipamento_id,
                            $colaborador_id,
                            $data_emprestimo,
                            $data_prevista_devolucao,
                            $status,
                            $condicoes_emprestimo,
                            $usuarioLogadoId
                        );
                        $mensagemSucesso = "Empréstimo registrado com sucesso! (1 unidade descontada do estoque)";
                        $acao = 'cadastrar';
                    } catch (PDOException $e) {
                        $mensagemErro = "Erro ao realizar cadastro do empréstimo! Código: " . $e->getCode();
                        $acao = 'cadastrar';
                    }
                }
            } elseif ($operacao === 'atualizar') {
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $status = $_POST['status'] ?? '';
                $data_devolucao = $this->formatarDateTimeBanco($_POST['data_devolucao'] ?? '');
                $observacao_devolucao = trim($_POST['observacao_devolucao'] ?? '');

                if (!$id || !in_array($status, $statusEdicao, true)) {
                    $mensagemErro = "Erro ao atualizar! Código: 400 (Status inválido)";
                    $acao = 'editar';
                    $_GET['id'] = $id;
                } elseif (in_array($status, $statusExigemDevolucao, true) && empty($data_devolucao)) {
                    $mensagemErro = "Informe a Data e Hora efetiva da devolução para o status selecionado!";
                    $acao = 'editar';
                    $_GET['id'] = $id;
                } else {
                    if (!in_array($status, $statusExigemDevolucao, true)) {
                        $data_devolucao = null;
                        $observacao_devolucao = null;
                    }
                    try {
                        $this->emprestimoModel->atualizarStatus($id, $status, $data_devolucao, $observacao_devolucao, $usuarioLogadoId);
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