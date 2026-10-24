<?php if ($mensagem !== ''): ?>
    <section class="alerta-wireframe <?= $tipoAlerta === 'sucesso' ? 'alerta-sucesso' : 'alerta-erro' ?>">
        <?= htmlspecialchars($mensagem) ?>
    </section>
<?php endif; ?>

<section class="painel">
    <h2>Meus Dados Cadastrais</h2>

    <form method="POST" action="index.php?rota=conta">
        <section class="mb-3">
            <label for="nome" class="form-label fw-bold small mb-1">Nome Completo *</label>
            <input type="text" name="nome" id="nome" class="form-control border-secondary" value="<?= htmlspecialchars($meusDados['nome'] ?? '') ?>" required>
        </section>

        <section class="mb-3">
            <label for="email" class="form-label fw-bold small mb-1">E-mail de Acesso *</label>
            <input type="email" name="email" id="email" class="form-control border-secondary" value="<?= htmlspecialchars($meusDados['email'] ?? '') ?>" required>
        </section>

        <section class="mb-3">
            <label class="form-label fw-bold small mb-1">Conta criada em</label>
            <input type="text" class="form-control border-secondary campo-bloqueado" value="<?= !empty($meusDados['criado_em']) ? date('d/m/Y \à\s H:i', strtotime($meusDados['criado_em'])) : '-' ?>" disabled>
        </section>

        <hr class="my-4">
        <h6 class="fw-bold mb-3 text-secondary">Alterar Palavra-passe <small class="fw-normal">(Deixe em branco caso não queira mudar)</small></h6>

        <section class="mb-3">
            <label for="senha_atual" class="form-label fw-bold small mb-1">Palavra-passe Atual</label>
            <input type="password" name="senha_atual" id="senha_atual" class="form-control border-secondary" placeholder="Digite a sua palavra-passe atual para confirmar a mudança">
        </section>

        <section class="row g-3 mb-3">
            <section class="col-md-6">
                <label for="nova_senha" class="form-label fw-bold small mb-1">Nova Palavra-passe (mín. 6 caracteres)</label>
                <input type="password" name="nova_senha" id="nova_senha" class="form-control border-secondary" placeholder="Nova palavra-passe">
            </section>
            <section class="col-md-6">
                <label for="confirma_senha" class="form-label fw-bold small mb-1">Confirmar Nova Palavra-passe</label>
                <input type="password" name="confirma_senha" id="confirma_senha" class="form-control border-secondary" placeholder="Repita a nova palavra-passe">
            </section>
        </section>

        <button type="submit" class="btn-confirmar-form">SALVAR ALTERAÇÕES</button>
    </form>
</section>