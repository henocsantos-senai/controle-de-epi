<?php
namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], bool $useLayout = true): void
    {
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
    }
}