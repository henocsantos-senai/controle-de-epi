<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class RecuperacaoSenha {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function invalidarTokensAntigos(int $usuarioId): void {
        $stmt = $this->db->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE usuario_id = ?");
        $stmt->execute([$usuarioId]);
    }

    public function criarToken(int $usuarioId, string $tokenHash, string $expiraEm): bool {
        $stmt = $this->db->prepare("INSERT INTO recuperacao_senha (usuario_id, token_hash, expira_em) VALUES (?, ?, ?)");
        return $stmt->execute([$usuarioId, $tokenHash, $expiraEm]);
    }

    public function buscarTokenValido(string $tokenHash): ?array {
        $stmt = $this->db->prepare("
            SELECT r.id, r.usuario_id, r.expira_em, r.usado, u.nome 
            FROM recuperacao_senha r
            INNER JOIN usuarios u ON r.usuario_id = u.id
            WHERE r.token_hash = ? AND r.usado = 0 AND r.expira_em > NOW()
            LIMIT 1
        ");
        $stmt->execute([$tokenHash]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function redefinirSenha(int $tokenId, int $usuarioId, string $senhaHash): bool {
        try {
            $this->db->beginTransaction();

            $stmtUser = $this->db->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmtUser->execute([$senhaHash, $usuarioId]);

            $stmtBurn = $this->db->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE id = ?");
            $stmtBurn->execute([$tokenId]);

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
}