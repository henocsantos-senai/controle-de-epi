<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar Utilizador - Controle de EPI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="\controle-epi\app\Assets\style_cad.css">
</head>

<body>
    <header class="topo">Controle de EPI - Registo de Usuário</header>
    <main class="cadastro-container">
        <section class="painel-cadastro">
            <h2>Novo Usuário</h2>
            <?php if ($mensagem !== ''): ?>
                <section class="alerta-erro">
                    <?= htmlspecialchars($mensagem) ?>
                </section>
            <?php endif; ?>

            <form method="POST" action="index.php?rota=cadastro_usuario">
                <section class="mb-3">
                    <label for="nome" class="form-label fw-bold small mb-1">Nome Completo *</label>
                    <input type="text" name="nome" id="nome" class="form-control border-secondary" placeholder="Ex.: Carlos Mendes" required>
                </section>
                <section class="mb-3">
                    <label for="email" class="form-label fw-bold small mb-1">E-mail de Acesso *</label>
                    <input type="email" name="email" id="email" class="form-control border-secondary" placeholder="carlos@epi.com" required>
                </section>
                <section class="mb-3">
                    <label for="senha" class="form-label fw-bold small mb-1">Senha * (mínimo 6 caracteres)</label>
                    <input type="password" name="senha" id="senha" class="form-control border-secondary" required>
                </section>
                <section class="mb-3">
                    <label for="confirma_senha" class="form-label fw-bold small mb-1">Confirmar Senha *</label>
                    <input type="password" name="confirma_senha" id="confirma_senha" class="form-control border-secondary" required>
                </section>
                <button type="submit" class="btn-cadastrar">Cadastrar</button>
            </form>

            <section class="text-center mt-3">
                <a href="index.php?rota=login" class="text-secondary small text-decoration-none fw-bold">&larr; Voltar para a tela de Login</a>
            </section>
        </section>
    </main>
</body>

</html>