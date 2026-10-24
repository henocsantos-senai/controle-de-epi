<?php
namespace App\Core;

use App\Models\Usuario;

abstract class Controller
{
    protected string $perfilLogado = 'Colaborador';

    protected function view(string $view, array $data = [], bool $useLayout = true): void
    {
        $data['perfilUsuario'] = $this->perfilLogado;
        extract($data);
        if ($useLayout) {
            require __DIR__ . '/../Views/layouts/topo.php';
        }
        require __DIR__ . '/../Views/' . $view . '.php';
        if ($useLayout) {
            require __DIR__ . '/../Views/layouts/rodape.php';
        }
    }

    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['usuario_id']) || empty($_SESSION['usuario_nome'])) {
            $this->redirect('index.php?rota=login');
        }

        // Atualiza o perfil a partir do banco para garantir segurança imediata
        $usuarioModel = new Usuario();
        $perfilBanco = $usuarioModel->buscarPerfil((int) $_SESSION['usuario_id']);
        if (!$perfilBanco) {
            session_unset();
            session_destroy();
            $this->redirect('index.php?rota=login');
        }

        $this->perfilLogado = $perfilBanco;
        $_SESSION['usuario_perfil'] = $perfilBanco;
    }

    protected function temPermissao(array $perfisPermitidos): bool
    {
        return in_array($this->perfilLogado, $perfisPermitidos, true);
    }

    protected function requireRole(array $perfisPermitidos): void
    {
        if (!$this->temPermissao($perfisPermitidos)) {
            http_response_code(403);
            die("Acesso negado: O seu perfil ({$this->perfilLogado}) não possui permissão para acessar este recurso.");
        }
    }
}