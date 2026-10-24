<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Controle de EPI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="\controle-epi\app\Assets\style_login.css">
    <link rel="stylesheet" href="\controle-epi\app\Assets\responsivo.css">
    <link rel="shortcut icon" href="\controle-epi\app\Assets\logo.ico" type="image/x-icon">
</head>

<body>
    <header class="topo">Controle de EPI - Acesso ao Sistema</header>
    <div class="login-container">
        <section class="painel-login">
            <h2>Login</h2>
            <?php if ($sucesso !== ''): ?>
                <div class="alerta-sucesso">
                    <?= htmlspecialchars($sucesso) ?>
                </div>
            <?php endif; ?>
            <?php if ($erro !== ''): ?>
                <div class="alerta-erro">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?rota=login">
                <div class="mb-3">
                    <label for="email" class="form-label fw-bold small mb-1">E-mail *</label>
                    <input type="email" name="email" id="email" class="form-control border-secondary" placeholder="xxxx@email.com" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label fw-bold small mb-1">Senha *</label>
                    <input type="password" name="senha" id="senha" class="form-control border-secondary" placeholder="******" required>
                </div>
                <button type="submit" class="btn-entrar">Entrar</button>
                <div class="text-center mt-3">
                    <a href="index.php?rota=cadastro_usuario" class="small fw-bold text-decoration-none">Não tem uma conta? Registe-se aqui</a>
                    <br>
                    <a href="index.php?rota=recuperacao_senha">Esqueci minha senha</a>
                </div>
            </form>

            <div class="dica-acesso">
                Acesso: <code>admin@email.com</code><br>
                Senha: <code>123456</code>
            </div>
        </section>
    </div>
</body>

</html>