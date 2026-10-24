<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Colaborador;
use PDOException;

class ColaboradorController extends Controller
{
    private Colaborador $model;

    private const CARGOS_DISPONIVEIS = [
        'Operador de Máquinas',
        'Costureiro(a)',
        'Revisor(a) de Qualidade',
        'Auxiliar de Produção',
        'Almoxarife de EPI',
        'Técnico de Segurança do Trabalho',
        'Eletricista',
        'Soldador',
        'Outro'
    ];

    public function __construct()
    {
        $this->requireAuth();
        // Colaborador comum não pode acessar o gerenciamento de colaboradores
        $this->requireRole(['Administrador', 'Almoxarife']);
        $this->model = new Colaborador();
    }

    public function handle(string $acao): void
    {
        $acao = ($acao === 'index') ? 'listar' : $acao;
        $mensagem = '';
        $tipoAlerta = '';

        // 1. Exclusão: Restrita EXCLUSIVAMENTE ao Administrador
        if ($acao === 'excluir') {
            if (!$this->temPermissao(['Administrador'])) {
                $mensagem = "Acesso negado! Apenas Administradores podem excluir colaboradores.";
                $tipoAlerta = "erro";
            } else {
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
                    ?: filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

                if ($id) {
                    try {
                        $this->model->excluir($id);
                        $mensagem = "Colaborador removido com sucesso!";
                        $tipoAlerta = "sucesso";
                    } catch (PDOException $e) {
                        $mensagem = ($e->getCode() == '23000')
                            ? "Não é possível remover! Este colaborador possui empréstimos de EPI vinculados ao seu cadastro."
                            : "Erro ao remover colaborador! Código: " . $e->getCode();
                        $tipoAlerta = "erro";
                    }
                } else {
                    $mensagem = "Identificador de colaborador inválido para exclusão.";
                    $tipoAlerta = "erro";
                }
            }
            $acao = 'listar';
        }

        // 2. Inserção e Atualização (Administrador e Almoxarife)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $acao !== 'excluir') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
            $codigo_barras = trim($_POST['codigo_barras'] ?? '');
            $nome = trim($_POST['nome'] ?? '');
            $cpf = trim($_POST['cpf'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $cargo = trim($_POST['cargo'] ?? '');
            $setor = trim($_POST['setor'] ?? '');

            if (empty($nome) || empty($cpf) || empty($cargo) || empty($setor)) {
                $mensagem = "Preencha todos os campos obrigatórios!";
                $tipoAlerta = "erro";
                $acao = $id ? 'editar' : 'cadastrar';
                $_GET['id'] = $id;
            } else {
                try {
                    if (!$id) {
                        $this->model->criar($nome, $cpf, $telefone, $cargo, $setor, $codigo_barras);
                        $mensagem = "Colaborador cadastrado com sucesso!";
                        $tipoAlerta = "sucesso";
                        $acao = 'cadastrar';
                    } else {
                        $this->model->atualizar($id, $nome, $cpf, $telefone, $cargo, $setor, $codigo_barras);
                        $mensagem = "Dados do colaborador atualizados com sucesso!";
                        $tipoAlerta = "sucesso";
                        $acao = 'listar';
                    }
                } catch (PDOException $e) {
                    $mensagem = ($e->getCode() == '23000')
                        ? "Erro: Já existe um colaborador com este CPF ou Código de Crachá/QR Code!"
                        : "Erro ao salvar colaborador! Código: " . $e->getCode();
                    $tipoAlerta = "erro";
                    $acao = $id ? 'editar' : 'cadastrar';
                    $_GET['id'] = $id;
                }
            }
        }

        $colaboradorEdite = null;
        if ($acao === 'editar' && isset($_GET['id'])) {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $colaboradorEdite = $this->model->buscarPorId($id);
            }
            if (!$colaboradorEdite) {
                $mensagem = "Colaborador não encontrado.";
                $tipoAlerta = "erro";
                $acao = 'listar';
            }
        }

        $pesquisa = trim($_GET['pesquisa'] ?? '');
        $colaboradores = $this->model->listar($pesquisa);

        $this->view('colaboradores/index', [
            'tituloPagina' => 'Tela de Colaborador - Controle de EPI',
            'mensagem' => $mensagem,
            'tipoAlerta' => $tipoAlerta,
            'acao' => $acao,
            'colaboradorEdite' => $colaboradorEdite,
            'pesquisa' => $pesquisa,
            'colaboradores' => $colaboradores,
            'cargosDisponiveis' => self::CARGOS_DISPONIVEIS
        ]);
    }
}