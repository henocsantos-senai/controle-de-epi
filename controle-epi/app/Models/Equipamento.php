<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Equipamento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(string $pesquisa = ''): array
    {
        if ($pesquisa !== '') {
            $stmt = $this->db->prepare("SELECT * FROM equipamentos WHERE nome LIKE ? ORDER BY id DESC");
            $stmt->execute(["%$pesquisa%"]);
        } else {
            $stmt = $this->db->query("SELECT * FROM equipamentos ORDER BY id DESC");
        }
        return $stmt->fetchAll();
    }

    public function listarParaSelect(): array
    {
        return $this->db->query("SELECT id, nome, ca FROM equipamentos ORDER BY nome")->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM equipamentos WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function criar(string $nome, string $ca, string $descricao): bool
    {
        $stmt = $this->db->prepare("INSERT INTO equipamentos (nome, ca, descricao) VALUES (?, ?, ?)");
        return $stmt->execute([$nome, $ca, $descricao]);
    }

    public function atualizar(int $id, string $nome, string $ca, string $descricao): bool
    {
        $stmt = $this->db->prepare("UPDATE equipamentos SET nome = ?, ca = ?, descricao = ? WHERE id = ?");
        return $stmt->execute([$nome, $ca, $descricao, $id]);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM equipamentos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}