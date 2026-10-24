<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

class Colaborador
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->garantirColunaCodigoBarras();
    }

    private function garantirColunaCodigoBarras(): void
    {
        try {
            $colunas = $this->db->query("SHOW COLUMNS FROM colaboradores LIKE 'codigo_barras'")->fetchAll();
            if (count($colunas) === 0) {
                $this->db->exec("ALTER TABLE colaboradores ADD COLUMN codigo_barras VARCHAR(100) NULL UNIQUE AFTER id");
            }
        } catch (PDOException $e) {
            // Ignora caso tabela ainda não exista
        }
    }

    public function listar(string $pesquisa = ''): array
    {
        if ($pesquisa !== '') {
            $stmt = $this->db->prepare("SELECT * FROM colaboradores WHERE nome LIKE :busca OR codigo_barras LIKE :busca OR cpf LIKE :busca ORDER BY id DESC");
            $stmt->execute([':busca' => "%$pesquisa%"]);
        } else {
            $stmt = $this->db->query("SELECT * FROM colaboradores ORDER BY id DESC");
        }
        return $stmt->fetchAll();
    }

    public function listarParaSelect(): array
    {
        return $this->db->query("SELECT id, codigo_barras, nome, cpf, cargo FROM colaboradores ORDER BY nome")->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM colaboradores WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function criar(string $nome, string $cpf, string $telefone, string $cargo, string $setor, ?string $codigoBarras = null): bool
    {
        $codigoBarras = !empty($codigoBarras) ? $codigoBarras : null;
        $stmt = $this->db->prepare("INSERT INTO colaboradores (codigo_barras, nome, cpf, telefone, cargo, setor) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$codigoBarras, $nome, $cpf, $telefone, $cargo, $setor]);
    }

    public function atualizar(int $id, string $nome, string $cpf, string $telefone, string $cargo, string $setor, ?string $codigoBarras = null): bool
    {
        $codigoBarras = !empty($codigoBarras) ? $codigoBarras : null;
        $stmt = $this->db->prepare("UPDATE colaboradores SET codigo_barras = ?, nome = ?, cpf = ?, telefone = ?, cargo = ?, setor = ? WHERE id = ?");
        return $stmt->execute([$codigoBarras, $nome, $cpf, $telefone, $cargo, $setor, $id]);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM colaboradores WHERE id = ?");
        return $stmt->execute([$id]);
    }
}