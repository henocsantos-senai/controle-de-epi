<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Senha - Controle de EPI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="\controle-epi\app\Assets\style_red.css">
    <link rel="stylesheet" href="\controle-epi\app\Assets\responsivo.css">
    <link rel="shortcut icon" href="\controle-epi\app\Assets\logo.ico" type="image/x-icon">
</head>

<body>
    <header class="topo">Controle de EPI - Redefinir Senha</header>
    <section class="box-central">
        <section class="painel">
            <h2>Nova Senha</h2>
            <?php if ($erro !== ''): ?>
                <section class="alerta-erro">
                    <?= htmlspecialchars($erro) ?>
                </section>
            <?php endif; ?>

            <?php if ($tokenValido): ?>
                <p class="small text-secondary text-center mb-3">
                    Olá, <strong>
                        <?= htmlspecialchars($registroToken['nome']) ?>
                    </strong>. Defina sua nova senha abaixo:
                </p>
                <form method="POST" action="index.php?rota=redefinir_senha">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($tokenPuro) ?>">
                    <section class="mb-3">
                        <label for="nova_senha" class="form-label fw-bold small mb-1">Nova Senha (mín. 6 caracteres) *</label>
                        <input type="password" name="nova_senha" id="nova_senha" class="form-control border-secondary" required autofocus>
                    </section>
                    <section class="mb-3">
                        <label for="confirma_senha" class="form-label fw-bold small mb-1">Confirmar Nova Senha *</label>
                        <input type="password" name="confirma_senha" id="confirma_senha" class="form-control border-secondary" required>
                    </section>
                    <button type="submit" class="btn-acao">Salvar Nova Senha</button>
                </form>
            <?php else: ?>
                <section class="text-center mt-3">
                    <a href="index.php?rota=recuperacao_senha" class="btn btn-outline-secondary btn-sm">Solicitar novo link</a>
                </section>
            <?php endif; ?>

            <section class="text-center mt-3">
                <a href="index.php?rota=login" class="text-secondary small text-decoration-none fw-bold">&larr; Voltar para o Login</a>
            </section>
        </section>
    </section>
</body>

</html>