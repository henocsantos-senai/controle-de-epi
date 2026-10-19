<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Emprestimo
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function obterContadores(): array
    {
        $contadores = ['Emprestado' => 0, 'Devolvido' => 0, 'Perdido' => 0];
        $stmt = $this->db->query("
            SELECT status, COUNT(*) as total 
            FROM emprestimos_epi 
            WHERE status IN ('Emprestado', 'Devolvido', 'Perdido') 
            GROUP BY status
        ");
        foreach ($stmt->fetchAll() as $row) {
            $contadores[$row['status']] = (int) $row['total'];
        }
        return $contadores;
    }

    public function listarHome(string $pesquisa = ''): array
    {
        if ($pesquisa !== '') {
            $stmt = $this->db->prepare("
                SELECT e.id, eq.nome AS equipamento, c.nome AS colaborador, 
                       e.data_emprestimo, e.data_prevista_devolucao, e.status, e.data_devolucao
                FROM emprestimos_epi e
                INNER JOIN equipamentos eq ON e.equipamento_id = eq.id
                INNER JOIN colaboradores c ON e.colaborador_id = c.id
                WHERE c.nome LIKE :busca OR eq.nome LIKE :busca
                ORDER BY e.id DESC
            ");
            $stmt->execute([':busca' => "%$pesquisa%"]);
        } else {
            $stmt = $this->db->query("
                SELECT e.id, eq.nome AS equipamento, c.nome AS colaborador, 
                       e.data_emprestimo, e.data_prevista_devolucao, e.status, e.data_devolucao
                FROM emprestimos_epi e
                INNER JOIN equipamentos eq ON e.equipamento_id = eq.id
                INNER JOIN colaboradores c ON e.colaborador_id = c.id
                ORDER BY e.id DESC
            ");
        }
        return $stmt->fetchAll();
    }

    public function buscarPorIdDetalhado(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT e.*, eq.nome AS nome_equipamento, c.nome AS nome_colaborador
            FROM emprestimos_epi e
            INNER JOIN equipamentos eq ON e.equipamento_id = eq.id
            INNER JOIN colaboradores c ON e.colaborador_id = c.id
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
                e.status AS Status_Emprestimo,
                e.data_devolucao AS Data_Devolucao
            FROM emprestimos_epi e
            INNER JOIN equipamentos eq ON e.equipamento_id = eq.id
            INNER JOIN colaboradores c ON e.colaborador_id = c.id
            WHERE " . implode(" AND ", $condicoesSql) . "
            ORDER BY e.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametrosSql);
        return $stmt->fetchAll();
    }

    public function criar(int $equipamentoId, int $colaboradorId, string $dataEmprestimo, ?string $dataPrevista, string $status, string $condicoes): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO emprestimos_epi 
            (equipamento_id, colaborador_id, data_emprestimo, data_prevista_devolucao, status, condicoes_emprestimo) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$equipamentoId, $colaboradorId, $dataEmprestimo, $dataPrevista, $status, $condicoes]);
    }

    public function atualizarStatus(int $id, string $status, ?string $dataDevolucao, ?string $obsDevolucao): bool
    {
        $stmt = $this->db->prepare("
            UPDATE emprestimos_epi 
            SET status = ?, data_devolucao = ?, observacao_devolucao = ?
            WHERE id = ?
        ");
        return $stmt->execute([$status, $dataDevolucao, $obsDevolucao, $id]);
    }

    /**
     * Retorna apenas os empréstimos ativos (pendentes de devolução) para o dashboard
     */
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