<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Emprestimo;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $emprestimoModel = new Emprestimo();

        $tituloPagina = 'Dashboard - Controle de EPI';
        $pesquisa = trim($_GET['pesquisa'] ?? '');
        $contadores = $emprestimoModel->obterContadores();

        // Agora a Home busca apenas empréstimos em andamento (ativos)
        $emprestimos = $emprestimoModel->listarAtivos($pesquisa);

        $this->view('home/index', compact('tituloPagina', 'pesquisa', 'contadores', 'emprestimos'));
    }
}