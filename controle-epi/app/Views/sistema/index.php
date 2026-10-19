<?php if ($mensagem !== ''): ?>
    <section class="alerta-wireframe <?= $tipoAlerta === 'sucesso' ? 'alerta-sucesso' : 'alerta-erro' ?>">
        <?= htmlspecialchars($mensagem) ?>
    </section>
<?php endif; ?>

<?php if ($acao === 'cadastrar' || ($acao === 'editar' && $usuarioEdite)): ?>
    <section class="painel">
        <h2>
            <?= $usuarioEdite ? 'Editar Usuário do Sistema' : 'Novo Usuário do Sistema' ?>
        </h2>

        <form method="POST" action="index.php?rota=sistema">
            <input type="hidden" name="id" value="<?= htmlspecialchars($usuarioEdite['id'] ?? '') ?>">

            <section class="mb-3">
                <label for="nome" class="form-label fw-bold small mb-1">Nome Completo *</label>
                <input type="text" name="nome" id="nome" class="form-control border-secondary" value="<?= htmlspecialchars($usuarioEdite['nome'] ?? '') ?>" required>
            </section>

            <section class="row g-3 mb-3">
                <section class="col-md-7">
                    <label for="email" class="form-label fw-bold small mb-1">E-mail de Login *</label>
                    <input type="email" name="email" id="email" class="form-control border-secondary" value="<?= htmlspecialchars($usuarioEdite['email'] ?? '') ?>" required>
                </section>
                <section class="col-md-5">
                    <label for="perfil" class="form-label fw-bold small mb-1">Nível de Acesso (Perfil) *</label>
                    <select name="perfil" id="perfil" class="form-select border-secondary" required>
                        <?php $perfilSel = $usuarioEdite['perfil'] ?? 'Administrador'; ?>
                        <option value="Administrador" <?= $perfilSel === 'Administrador' ? 'selected' : '' ?>>Administrador (Total)</option>
                        <option value="Operador" <?= $perfilSel === 'Operador' ? 'selected' : '' ?>>Operador (Padrão)</option>
                    </select>
                </section>
            </section>

            <section class="mb-3">
                <label for="senha" class="form-label fw-bold small mb-1">
                    <?= $usuarioEdite ? 'Redefinir Senha (deixe em branco para manter a atual)' : 'Senha Inicial (mínimo 6 caracteres) *' ?>
                </label>
                <input type="password" name="senha" id="senha" class="form-control border-secondary" placeholder="<?= $usuarioEdite ? 'Digite apenas se quiser trocar a senha deste usuário' : 'Mínimo 6 caracteres' ?>" <?= $usuarioEdite ? '' : 'required' ?>>
            </section>

            <button type="submit" class="btn-confirmar-form">
                <?= $usuarioEdite ? 'SALVAR' : 'Cadastrar Usuário' ?>
            </button>

            <section class="text-center mt-3">
                <a href="index.php?rota=sistema" class="text-secondary small text-decoration-none">
                    &larr; Voltar para o gerenciamento de usuários
                </a>
            </section>
        </form>
    </section>

<?php else: ?>
    <section class="painel">
        <h2>Gerenciar Usuários do Sistema</h2>

        <form method="GET" action="index.php" class="barra-busca">
            <input type="hidden" name="rota" value="sistema">
            <section class="input-busca-box">
                <input type="text" name="pesquisa" placeholder="Pesquise pelo nome ou e-mail do usuário" value="<?= htmlspecialchars($pesquisa) ?>">
                <button type="submit" title="Pesquisar">&#128269;</button>
            </section>
            <?php if (!empty($pesquisa)): ?>
                <a href="index.php?rota=sistema" class="btn-topo-tabela">Limpar</a>
            <?php endif; ?>
            <a href="index.php?rota=sistema&acao=cadastrar" class="btn-topo-tabela">+ Novo</a>
        </form>

        <?php if (count($listaUsuarios) > 0): ?>
            <section class="table-responsive">
                <table class="tabela-wireframe">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Cadastro</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listaUsuarios as $u): ?>
                            <tr>
                                <td>
                                    <?= (int) $u['id'] ?>
                                </td>
                                <td>
                                    <strong>
                                        <?= htmlspecialchars($u['nome']) ?>
                                    </strong>
                                    <?php if ((int) $u['id'] === $usuarioLogado): ?>
                                        <span class="badge bg-primary ms-1">Você</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($u['email']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($u['perfil'] ?? 'Administrador') ?>
                                </td>
                                <td>
                                    <?= !empty($u['criado_em']) ? date('d/m/Y', strtotime($u['criado_em'])) : '-' ?>
                                </td>
                                <td>
                                    <?php if ((int) $u['id'] !== $usuarioLogado): ?>
                                        <button type="button" class="btn-remover" onclick="confirmarExclusaoUsuario(<?= (int) $u['id'] ?>, '<?= htmlspecialchars($u['nome'], ENT_QUOTES) ?>')">
                                            Remover
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn-remover opacity-50" disabled title="Você não pode excluir sua própria conta">
                                            Remover
                                        </button>
                                    <?php endif; ?>

                                    <a href="index.php?rota=sistema&acao=editar&id=<?= (int) $u['id'] ?>" class="btn-editar">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php else: ?>
            <section class="msg-vazio">
                Desculpe, mas não encontramos dados correspondentes à sua busca.
            </section>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="modal fade" id="modalExcluirUsuario" tabindex="-1" aria-hidden="true">
    <section class="modal-dialog modal-dialog-centered">
        <section class="modal-content modal-wireframe">
            <section class="modal-header border-0 pb-1">
                <h5 class="modal-title">Remover Usuário</h5>
            </section>
            <section class="modal-body text-center py-3">
                <p class="mb-1">Você quer remover o acesso deste usuário ao sistema?</p>
                <small class="text-muted fw-bold" id="nomeUsuarioSistemaExcluir"></small>
            </section>
            <section class="modal-footer border-0 justify-content-center gap-3 pt-1">
                <button type="button" class="btn-modal-cancelar" data-bs-dismiss="modal">Cancelar</button>
                <a id="linkConfirmarExclusaoUsuario" href="#" class="btn-modal-remover">Remover</a>
            </section>
        </section>
    </section>
</section>

<script>
    function confirmarExclusaoUsuario(id, nome) {
        document.getElementById('nomeUsuarioSistemaExcluir').innerText = nome;
        document.getElementById('linkConfirmarExclusaoUsuario').href = 'index.php?rota=sistema&acao=excluir&id=' + id;
        var modal = new bootstrap.Modal(document.getElementById('modalExcluirUsuario'));
        modal.show();
    }
</script>