<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Controle de EPI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="\controle-epi\app\Assets\style_rec.css">
</head>
<body>
    <header class="topo">Controle de EPI - Recuperação de Senha</header>
    <section class="box-central">
        <section class="painel">
            <h2>Esqueci Minha Senha</h2>
            <?php if ($mensagem !== ''): ?>
                <section class="alerta-info"><?= htmlspecialchars($mensagem) ?></section>
            <?php endif; ?>

            <form method="POST" action="index.php?rota=recuperacao_senha">
                <p class="small text-secondary mb-3">
                    Informe o seu e-mail de acesso. Enviaremos um link temporário seguro para você criar uma nova senha.
                </p>
                <section class="mb-3">
                    <label for="email" class="form-label fw-bold small mb-1">E-mail Cadastrado *</label>
                    <input type="email" name="email" id="email" class="form-control border-secondary" placeholder="joao@epi.com" required autofocus>
                </section>
                <button type="submit" class="btn-acao">Enviar Link de Recuperação</button>
            </form>

            <?php if ($linkSimulado !== ''): ?>
                <section class="caixa-dev">
                    <strong>[Ambiente Local - Simulação de E-mail]:</strong><br>
                    Clique no link abaixo para testar a redefinição com o token gerado:<br>
                    <a href="<?= htmlspecialchars($linkSimulado) ?>" class="fw-bold"><?= htmlspecialchars($linkSimulado) ?></a>
                </section>
            <?php endif; ?>

            <section class="text-center mt-3">
                <a href="index.php?rota=login" class="text-secondary small text-decoration-none fw-bold">&larr; Voltar para o Login</a>
            </section>
        </section>
    </section>
</body>
</html>