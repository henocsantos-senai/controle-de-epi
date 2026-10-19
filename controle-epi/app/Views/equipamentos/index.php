<?php if (!empty($mensagem)): ?>
    <section class="alerta-wireframe <?= $tipoAlerta === 'sucesso' ? 'alerta-sucesso' : 'alerta-erro' ?>">
        <?= htmlspecialchars($mensagem) ?>
    </section>
<?php endif; ?>

<?php if ($acao === 'cadastrar' || ($acao === 'editar' && $equipamentoEdite)): ?>
    <section class="painel">
        <h2><?= $equipamentoEdite ? 'Editar Equipamento' : 'Cadastrar Equipamento' ?></h2>
        <form action="index.php?rota=equipamentos" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($equipamentoEdite['id'] ?? '') ?>">

            <section class="mb-3">
                <label for="nome" class="form-label fw-bold small mb-1">Nome do Equipamento *</label>
                <input type="text" class="form-control border-secondary" id="nome" name="nome"
                       value="<?= htmlspecialchars($equipamentoEdite['nome'] ?? '') ?>" required>
            </section>

            <section class="mb-3">
                <label for="ca" class="form-label fw-bold small mb-1">Certificado de Aprovação (CA) *</label>
                <input type="text" class="form-control border-secondary" id="ca" name="ca"
                       placeholder="Ex.: CA-31469"
                       value="<?= htmlspecialchars($equipamentoEdite['ca'] ?? '') ?>" required>
            </section>

            <section class="mb-3">
                <label for="descricao" class="form-label fw-bold small mb-1">Descrição / Observações</label>
                <input type="text" class="form-control border-secondary" id="descricao" name="descricao"
                       value="<?= htmlspecialchars($equipamentoEdite['descricao'] ?? '') ?>">
            </section>

            <button type="submit" class="btn-confirmar-form">
                <?= $equipamentoEdite ? 'SALVAR' : 'Cadastrar' ?>
            </button>

            <section class="text-center mt-3">
                <a href="index.php?rota=equipamentos" class="text-secondary small text-decoration-none">
                    &larr; Voltar para a listagem de equipamentos
                </a>
            </section>
        </form>
    </section>

<?php else: ?>
    <section class="painel">
        <h2>Equipamento</h2>

        <form method="GET" action="index.php" class="barra-busca">
            <input type="hidden" name="rota" value="equipamentos">
            <section class="input-busca-box">
                <input type="text" name="pesquisa" placeholder="Pesquise pelo nome"
                       value="<?= htmlspecialchars($pesquisa) ?>">
                <button type="submit" title="Pesquisar">&#128269;</button>
            </section>
            <?php if (!empty($pesquisa)): ?>
                <a href="index.php?rota=equipamentos" class="btn-topo-tabela">Limpar</a>
            <?php endif; ?>
            <a href="index.php?rota=equipamentos&acao=cadastrar" class="btn-topo-tabela">+ Novo</a>
        </form>

        <?php if (count($equipamentos) > 0): ?>
            <section class="table-responsive">
                <table class="tabela-wireframe">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome do Equipamento</th>
                            <th>CA</th>
                            <th>Descrição</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipamentos as $eq): ?>
                            <tr>
                                <td><?= (int)$eq['id'] ?></td>
                                <td><?= htmlspecialchars($eq['nome']) ?></td>
                                <td><?= htmlspecialchars($eq['ca']) ?></td>
                                <td><?= htmlspecialchars(!empty($eq['descricao']) ? $eq['descricao'] : '-') ?></td>
                                <td>
                                    <button type="button" class="btn-remover"
                                            onclick="confirmarExclusaoEquipamento(<?= (int)$eq['id'] ?>, '<?= htmlspecialchars($eq['nome'], ENT_QUOTES) ?>')">
                                        Remover
                                    </button>
                                    <a href="index.php?rota=equipamentos&acao=editar&id=<?= (int)$eq['id'] ?>" class="btn-editar">
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

<section class="modal fade" id="modalExcluirEquipamento" tabindex="-1" aria-hidden="true">
    <section class="modal-dialog modal-dialog-centered">
        <section class="modal-content modal-wireframe">
            <section class="modal-header border-0 pb-1">
                <h5 class="modal-title">Remover Equipamento</h5>
            </section>
            <section class="modal-body text-center py-3">
                <p class="mb-1">Você quer remover esse equipamento?</p>
                <small class="text-muted fw-bold" id="nomeEquipamentoExcluir"></small>
            </section>
            <section class="modal-footer border-0 justify-content-center gap-3 pt-1">
                <button type="button" class="btn-modal-cancelar" data-bs-dismiss="modal">Cancelar</button>
                <a id="linkConfirmarExclusaoEquipamento" href="#" class="btn-modal-remover">Remover</a>
            </section>
        </section>
    </section>
</section>

<script>
    function confirmarExclusaoEquipamento(id, nome) {
        document.getElementById('nomeEquipamentoExcluir').innerText = nome;
        document.getElementById('linkConfirmarExclusaoEquipamento').href = 'index.php?rota=equipamentos&acao=excluir&id=' + id;
        var modal = new bootstrap.Modal(document.getElementById('modalExcluirEquipamento'));
        modal.show();
    }
</script>