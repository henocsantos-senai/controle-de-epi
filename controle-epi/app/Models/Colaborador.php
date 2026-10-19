<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Colaborador
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(string $pesquisa = ''): array
    {
        if ($pesquisa !== '') {
            $stmt = $this->db->prepare("SELECT * FROM colaboradores WHERE nome LIKE ? ORDER BY id DESC");
            $stmt->execute(["%$pesquisa%"]);
        } else {
            $stmt = $this->db->query("SELECT * FROM colaboradores ORDER BY id DESC");
        }
        return $stmt->fetchAll();
    }

    public function listarParaSelect(): array
    {
        return $this->db->query("SELECT id, nome, cargo FROM colaboradores ORDER BY nome")->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM colaboradores WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function criar(string $nome, string $cpf, string $telefone, string $cargo, string $setor): bool
    {
        $stmt = $this->db->prepare("INSERT INTO colaboradores (nome, cpf, telefone, cargo, setor) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$nome, $cpf, $telefone, $cargo, $setor]);
    }

    public function atualizar(int $id, string $nome, string $cpf, string $telefone, string $cargo, string $setor): bool
    {
        $stmt = $this->db->prepare("UPDATE colaboradores SET nome = ?, cpf = ?, telefone = ?, cargo = ?, setor = ? WHERE id = ?");
        return $stmt->execute([$nome, $cpf, $telefone, $cargo, $setor, $id]);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM colaboradores WHERE id = ?");
        return $stmt->execute([$id]);
    }
}