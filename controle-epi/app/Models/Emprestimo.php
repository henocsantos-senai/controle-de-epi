<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

class Emprestimo
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->garantirEstruturaTabela();
    }

    /**
     * Garante colunas DATETIME e colunas de Auditoria (quem entregou / quem recebeu)
     */
    private function garantirEstruturaTabela(): void
    {
        try {
            $colunas = $this->db->query("SHOW COLUMNS FROM emprestimos_epi LIKE 'data_prevista_devolucao'")->fetchAll();
            if (!empty($colunas) && strtolower($colunas[0]['Type']) === 'date') {
                $this->db->exec("
                    ALTER TABLE emprestimos_epi 
                    MODIFY COLUMN data_emprestimo DATETIME NOT NULL,
                    MODIFY COLUMN data_prevista_devolucao DATETIME NULL,
                    MODIFY COLUMN data_devolucao DATETIME NULL
                ");
            }

            $colEntrega = $this->db->query("SHOW COLUMNS FROM emprestimos_epi LIKE 'usuario_entrega_id'")->fetchAll();
            if (count($colEntrega) === 0) {
                $this->db->exec("ALTER TABLE emprestimos_epi ADD COLUMN usuario_entrega_id INT NULL AFTER colaborador_id");
            }

            $colBaixa = $this->db->query("SHOW COLUMNS FROM emprestimos_epi LIKE 'usuario_baixa_id'")->fetchAll();
            if (count($colBaixa) === 0) {
                $this->db->exec("ALTER TABLE emprestimos_epi ADD COLUMN usuario_baixa_id INT NULL AFTER observacao_devolucao");
            }
        } catch (PDOException $e) {
            // Ignora caso a tabela ainda não exista
        }
    }

    public function obterContadores(): array
    {
        $contadores = ['Emprestado' => 0, 'Devolvido' => 0, 'Perdido' => 0];
        $stmt = $this->db->query("
            SELECT status, COUNT(*) as total 
            FROM emprestimos_epi 
            WHERE status IN ('Emprestado', 'Fornecido', 'Devolvido', 'Perdido', 'Danificado') 
            GROUP BY status
        ");
        foreach ($stmt->fetchAll() as $row) {
            if ($row['status'] === 'Perdido' || $row['status'] === 'Danificado') {
                $contadores['Perdido'] += (int) $row['total'];
            } elseif ($row['status'] === 'Emprestado' || $row['status'] === 'Fornecido') {
                $contadores['Emprestado'] += (int) $row['total'];
            } else {
                $contadores[$row['status']] = (int) $row['total'];
            }
        }
        return $contadores;
    }

    public function buscarPorIdDetalhado(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT e.*, 
                   eq.nome AS nome_equipamento, eq.ca AS ca_equipamento, eq.codigo_barras AS codigo_equipamento,
                   c.nome AS nome_colaborador, c.cpf AS cpf_colaborador, c.cargo AS cargo_colaborador, c.setor AS setor_colaborador,
                   u1.nome AS operador_entrega,
                   u2.nome AS operador_baixa
            FROM emprestimos_epi e
            INNER JOIN equipamentos eq ON e.equipamento_id = eq.id
            INNER JOIN colaboradores c ON e.colaborador_id = c.id
            LEFT JOIN usuarios u1 ON e.usuario_entrega_id = u1.id
            LEFT JOIN usuarios u2 ON e.usuario_baixa_id = u2.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function filtrarRelatorio(string $equipamento, string $colaborador, string $status): array
    {
        $condicoesSql = ["1=1"];
        $parametrosSql = [];

        if ($equipamento !== '') {
            $condicoesSql[] = "eq.nome LIKE :equipamento";
            $parametrosSql[':equipamento'] = "%" . $equipamento . "%";
        }
        if ($colaborador !== '') {
            $condicoesSql[] = "c.nome LIKE :colaborador";
            $parametrosSql[':colaborador'] = "%" . $colaborador . "%";
        }
        if ($status !== '') {
            $condicoesSql[] = "e.status = :status";
            $parametrosSql[':status'] = $status;
        }

        $sql = "
            SELECT 
                e.id AS ID_Emprestimo,
                eq.nome AS Equipamento_Emprestado,
                c.nome AS Nome_Colaborador,
                e.data_emprestimo AS Data_Emprestimo,
                e.data_prevista_devolucao AS Data_Prevista_Devolucao,
                e.status AS Status_Emprestimo,
                e.data_devolucao AS Data_Devolucao,
                u1.nome AS Operador_Entrega,
                u2.nome AS Operador_Baixa
            FROM emprestimos_epi e
            INNER JOIN equipamentos eq ON e.equipamento_id = eq.id
            INNER JOIN colaboradores c ON e.colaborador_id = c.id
            LEFT JOIN usuarios u1 ON e.usuario_entrega_id = u1.id
            LEFT JOIN usuarios u2 ON e.usuario_baixa_id = u2.id
            WHERE " . implode(" AND ", $condicoesSql) . "
            ORDER BY e.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametrosSql);
        return $stmt->fetchAll();
    }

    /**
     * Cria o empréstimo e desconta 1 unidade do estoque em transação atômica
     */
    public function criar(int $equipamentoId, int $colaboradorId, string $dataEmprestimo, string $dataPrevista, string $status, string $condicoes, int $usuarioEntregaId): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO emprestimos_epi 
                (equipamento_id, colaborador_id, usuario_entrega_id, data_emprestimo, data_prevista_devolucao, status, condicoes_emprestimo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$equipamentoId, $colaboradorId, $usuarioEntregaId, $dataEmprestimo, $dataPrevista, $status, $condicoes]);

            // Desconta 1 unidade do estoque do equipamento
            $stmtEst = $this->db->prepare("UPDATE equipamentos SET quantidade = GREATEST(0, quantidade - 1) WHERE id = ?");
            $stmtEst->execute([$equipamentoId]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Atualiza o status, registra quem deu baixa e ajusta o estoque automaticamente
     */
    public function atualizarStatus(int $id, string $novoStatus, ?string $dataDevolucao, ?string $obsDevolucao, int $usuarioBaixaId): bool
    {
        $atual = $this->buscarPorIdDetalhado($id);
        if (!$atual) {
            return false;
        }

        $statusAnterior = $atual['status'];
        $equipamentoId = (int) $atual['equipamento_id'];

        try {
            $this->db->beginTransaction();

            $operadorBaixa = in_array($novoStatus, ['Devolvido', 'Danificado', 'Perdido'], true) ? $usuarioBaixaId : null;

            $stmt = $this->db->prepare("
                UPDATE emprestimos_epi 
                SET status = ?, data_devolucao = ?, observacao_devolucao = ?, usuario_baixa_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$novoStatus, $dataDevolucao, $obsDevolucao, $operadorBaixa, $id]);

            // Regra de Estoque:
            // Se antes não estava "Devolvido" e agora passou para "Devolvido" -> devolve +1 ao estoque
            if ($statusAnterior !== 'Devolvido' && $novoStatus === 'Devolvido') {
                $stmtEst = $this->db->prepare("UPDATE equipamentos SET quantidade = quantidade + 1 WHERE id = ?");
                $stmtEst->execute([$equipamentoId]);
            }
            // Se já estava "Devolvido" e foi revertido para outro status -> retira -1 do estoque novamente
            elseif ($statusAnterior === 'Devolvido' && $novoStatus !== 'Devolvido') {
                $stmtEst = $this->db->prepare("UPDATE equipamentos SET quantidade = GREATEST(0, quantidade - 1) WHERE id = ?");
                $stmtEst->execute([$equipamentoId]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function listarAtivos(string $pesquisa = ''): array
    {
        $sql = "
            SELECT e.id, eq.nome AS equipamento, c.nome AS colaborador, 
                   e.data_emprestimo, e.data_prevista_devolucao, e.status, e.data_devolucao
            FROM emprestimos_epi e
            INNER JOIN equipamentos eq ON e.equipamento_id = eq.id
            INNER JOIN colaboradores c ON e.colaborador_id = c.id
            WHERE e.status IN ('Emprestado', 'Fornecido')
        ";

        if ($pesquisa !== '') {
            $sql .= " AND (c.nome LIKE :busca OR eq.nome LIKE :busca)";
            $stmt = $this->db->prepare($sql . " ORDER BY e.id DESC");
            $stmt->execute([':busca' => "%$pesquisa%"]);
        } else {
            $stmt = $this->db->query($sql . " ORDER BY e.id DESC");
        }

        return $stmt->fetchAll();
    }
}