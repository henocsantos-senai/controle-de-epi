<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

class Equipamento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->garantirColunasExtras();
    }

    private function garantirColunasExtras(): void
    {
        try {
            $colBarras = $this->db->query("SHOW COLUMNS FROM equipamentos LIKE 'codigo_barras'")->fetchAll();
            if (count($colBarras) === 0) {
                $this->db->exec("ALTER TABLE equipamentos ADD COLUMN codigo_barras VARCHAR(100) NULL UNIQUE AFTER id");
            }

            $colQtd = $this->db->query("SHOW COLUMNS FROM equipamentos LIKE 'quantidade'")->fetchAll();
            if (count($colQtd) === 0) {
                $this->db->exec("ALTER TABLE equipamentos ADD COLUMN quantidade INT NOT NULL DEFAULT 1 AFTER ca");
            }

            $colValidade = $this->db->query("SHOW COLUMNS FROM equipamentos LIKE 'validade_ca'")->fetchAll();
            if (count($colValidade) === 0) {
                $this->db->exec("ALTER TABLE equipamentos ADD COLUMN validade_ca DATE NULL AFTER quantidade");
            }
        } catch (PDOException $e) {
        }
    }

    public function listar(string $pesquisa = ''): array
    {
        if ($pesquisa !== '') {
            $stmt = $this->db->prepare("SELECT * FROM equipamentos WHERE nome LIKE :busca OR codigo_barras LIKE :busca OR ca LIKE :busca ORDER BY id DESC");
            $stmt->execute([':busca' => "%$pesquisa%"]);
        } else {
            $stmt = $this->db->query("SELECT * FROM equipamentos ORDER BY id DESC");
        }
        return $stmt->fetchAll();
    }

    public function listarParaSelect(): array
    {
        return $this->db->query("SELECT id, codigo_barras, nome, ca, quantidade, validade_ca FROM equipamentos ORDER BY nome")->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM equipamentos WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function criar(string $nome, string $ca, string $descricao, ?string $codigoBarras = null, int $quantidade = 1, ?string $validadeCa = null): bool
    {
        $codigoBarras = !empty($codigoBarras) ? $codigoBarras : null;
        $validadeCa = !empty($validadeCa) ? $validadeCa : null;
        $stmt = $this->db->prepare("
            INSERT INTO equipamentos (codigo_barras, nome, ca, quantidade, validade_ca, descricao) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$codigoBarras, $nome, $ca, $quantidade, $validadeCa, $descricao]);
    }

    public function atualizar(int $id, string $nome, string $ca, string $descricao, ?string $codigoBarras = null, int $quantidade = 1, ?string $validadeCa = null): bool
    {
        $codigoBarras = !empty($codigoBarras) ? $codigoBarras : null;
        $validadeCa = !empty($validadeCa) ? $validadeCa : null;
        $stmt = $this->db->prepare("
            UPDATE equipamentos 
            SET codigo_barras = ?, nome = ?, ca = ?, quantidade = ?, validade_ca = ?, descricao = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$codigoBarras, $nome, $ca, $quantidade, $validadeCa, $descricao, $id]);
    }

    public function ajustarEstoque(int $id, int $delta): bool
    {
        $stmt = $this->db->prepare("UPDATE equipamentos SET quantidade = GREATEST(0, quantidade + ?) WHERE id = ?");
        return $stmt->execute([$delta, $id]);
    }

    public function excluir(int $id): bool
    {
        $stmtAtivos = $this->db->prepare("
            SELECT COUNT(*) FROM emprestimos_epi 
            WHERE equipamento_id = ? AND status IN ('Emprestado', 'Fornecido')
        ");
        $stmtAtivos->execute([$id]);

        if ((int) $stmtAtivos->fetchColumn() > 0) {
            throw new PDOException("Equipamento possui empréstimos ativos.", 23000);
        }

        try {
            $this->db->beginTransaction();
            $stmtHist = $this->db->prepare("DELETE FROM emprestimos_epi WHERE equipamento_id = ?");
            $stmtHist->execute([$id]);

            $stmtEq = $this->db->prepare("DELETE FROM equipamentos WHERE id = ?");
            $res = $stmtEq->execute([$id]);

            $this->db->commit();
            return $res;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}