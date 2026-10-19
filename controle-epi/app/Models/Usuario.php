<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->garantirColunaPerfil();
    }

    private function garantirColunaPerfil(): void
    {
        try {
            $colunas = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'perfil'")->fetchAll();
            if (count($colunas) === 0) {
                $this->db->exec("ALTER TABLE usuarios ADD COLUMN perfil ENUM('Administrador', 'Operador') NOT NULL DEFAULT 'Administrador' AFTER email");
            }
        } catch (PDOException $e) {
            // Ignora caso tabela ainda não exista ou coluna já exista
        }
    }

    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT id, nome, email, perfil, senha FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, nome, email, perfil, senha, criado_em FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function buscarPerfil(int $id): ?string
    {
        $stmt = $this->db->prepare("SELECT perfil FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $perfil = $stmt->fetchColumn();
        return $perfil ?: null;
    }

    public function listar(string $pesquisa = ''): array
    {
        if ($pesquisa !== '') {
            $stmt = $this->db->prepare("SELECT id, nome, email, perfil, criado_em FROM usuarios WHERE nome LIKE :busca OR email LIKE :busca ORDER BY id DESC");
            $stmt->execute([':busca' => "%$pesquisa%"]);
        } else {
            $stmt = $this->db->query("SELECT id, nome, email, perfil, criado_em FROM usuarios ORDER BY id DESC");
        }
        return $stmt->fetchAll();
    }

    public function criar(string $nome, string $email, string $senhaHash, string $perfil = 'Operador'): bool
    {
        $stmt = $this->db->prepare("INSERT INTO usuarios (nome, email, perfil, senha) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$nome, $email, $perfil, $senhaHash]);
    }

    public function atualizarConta(int $id, string $nome, string $email, ?string $senhaHash = null): bool
    {
        if ($senhaHash !== null) {
            $stmt = $this->db->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?");
            return $stmt->execute([$nome, $email, $senhaHash, $id]);
        }
        $stmt = $this->db->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
        return $stmt->execute([$nome, $email, $id]);
    }

    public function atualizarPeloAdmin(int $id, string $nome, string $email, string $perfil, ?string $senhaHash = null): bool
    {
        if ($senhaHash !== null) {
            $stmt = $this->db->prepare("UPDATE usuarios SET nome = ?, email = ?, perfil = ?, senha = ? WHERE id = ?");
            return $stmt->execute([$nome, $email, $perfil, $senhaHash, $id]);
        }
        $stmt = $this->db->prepare("UPDATE usuarios SET nome = ?, email = ?, perfil = ? WHERE id = ?");
        return $stmt->execute([$nome, $email, $perfil, $id]);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}