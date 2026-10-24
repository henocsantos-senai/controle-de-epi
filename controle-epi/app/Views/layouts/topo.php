<?php
$nomeUsuarioLogado = $_SESSION['usuario_nome'] ?? '';
$perfilAtual = $perfilUsuario ?? ($_SESSION['usuario_perfil'] ?? 'Colaborador');
$rotaAtual = $_GET['rota'] ?? 'home';
$acaoAtual = $_GET['acao'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'Sistema de Controle de EPI') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="app/Assets/style_topo.css">
    <link rel="stylesheet" href="\controle-epi\app\Assets\responsivo.css">
    <link rel="shortcut icon" href="\controle-epi\app\Assets\logo.png" type="image/x-icon">
</head>

<body>
    <header class="topo">
        <section class="brand"><a href="index.php?rota=home"><img src="\controle-epi\app\Assets\stock.png" alt=""> Home</a></section>
        <section class="usuario">
            <span class="nome-logado"><?= htmlspecialchars($nomeUsuarioLogado) ?></span>
            <span class="badge bg-secondary" style="font-size: 0.72rem;"><?= htmlspecialchars($perfilAtual) ?></span>
            <span aria-hidden="true">&#128100;</span>
        </section>
    </header>

    <section class="layout-wrapper">
        <aside class="sidebar">
            <section>
                <?php if (in_array($perfilAtual, ['Administrador', 'Almoxarife'], true)): ?>
                    <section class="menu-grupo">
                        <h4>Cadastros</h4>
                        <ul>
                            <li>
                                <a href="index.php?rota=equipamentos" class="<?= $rotaAtual === 'equipamentos' ? 'ativo' : '' ?>">Equipamento</a>
                            </li>
                            <li>
                                <a href="index.php?rota=colaboradores" class="<?= $rotaAtual === 'colaboradores' ? 'ativo' : '' ?>">Colaborador</a>
                            </li>
                        </ul>
                    </section>
                    <section class="menu-grupo">
                        <h4>Ação</h4>
                        <ul>
                            <li>
                                <a href="index.php?rota=controle_epi&acao=cadastrar" class="<?= ($rotaAtual === 'controle_epi' && $acaoAtual === 'cadastrar') ? 'ativo' : '' ?>">Controle de EPI</a>
                            </li>
                        </ul>
                    </section>
                <?php endif; ?>

                <section class="menu-grupo">
                    <h4>Relatórios</h4>
                    <ul>
                        <li>
                            <a href="index.php?rota=home" class="<?= $rotaAtual === 'home' ? 'ativo' : '' ?>">Dashboard / Início</a>
                        </li>
                        <li>
                            <a href="index.php?rota=controle_epi&acao=relatorio" class="<?= ($rotaAtual === 'controle_epi' && ($acaoAtual === 'relatorio' || $acaoAtual === '')) ? 'ativo' : '' ?>">Relatório Avançado</a>
                        </li>
                    </ul>
                </section>

                <section class="menu-grupo">
                    <h4>Configurações</h4>
                    <ul>
                        <li>
                            <a href="index.php?rota=conta" class="<?= $rotaAtual === 'conta' ? 'ativo' : '' ?>">Conta</a>
                        </li>
                        <?php if ($perfilAtual === 'Administrador'): ?>
                            <li>
                                <a href="index.php?rota=sistema" class="<?= $rotaAtual === 'sistema' ? 'ativo' : '' ?>">Sistema</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </section>
            </section>
            <a href="index.php?rota=logout" class="btn-sair" accesskey="q" title="Atalho: Alt + Q">Sair</a>
        </aside>

        <!-- Aberta aqui, fechada no rodape.php -->
        <main class="conteudo">