<?php if (!empty($mensagem)): ?>
    <section class="alerta-wireframe <?= $tipoAlerta === 'sucesso' ? 'alerta-sucesso' : 'alerta-erro' ?>">
        <?= htmlspecialchars($mensagem) ?>
    </section>
<?php endif; ?>

<?php if ($acao === 'cadastrar' || ($acao === 'editar' && $colaboradorEdite)): ?>
    <section class="painel">
        <h2><?= $colaboradorEdite ? 'Editar Colaborador' : 'Cadastrar Colaborador' ?></h2>

        <form action="index.php?rota=colaboradores" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($colaboradorEdite['id'] ?? '') ?>">

            <section class="mb-3">
                <label for="nome" class="form-label fw-bold small mb-1">Nome Completo *</label>
                <input type="text" class="form-control border-secondary" id="nome" name="nome"
                       value="<?= htmlspecialchars($colaboradorEdite['nome'] ?? '') ?>" required>
            </section>

            <section class="row g-3 mb-3">
                <section class="col-md-6">
                    <label for="cpf" class="form-label fw-bold small mb-1">CPF *</label>
                    <input type="text" class="form-control border-secondary" id="cpf" name="cpf"
                           placeholder="000.000.000-00"
                           value="<?= htmlspecialchars($colaboradorEdite['cpf'] ?? '') ?>" required>
                </section>
                <section class="col-md-6">
                    <label for="telefone" class="form-label fw-bold small mb-1">Telefone</label>
                    <input type="text" class="form-control border-secondary" id="telefone" name="telefone"
                           placeholder="(00) 00000-0000"
                           value="<?= htmlspecialchars($colaboradorEdite['telefone'] ?? '') ?>">
                </section>
            </section>

            <section class="row g-3 mb-3">
                <section class="col-md-6">
                    <label for="cargo" class="form-label fw-bold small mb-1">Cargo *</label>
                    <select class="form-select border-secondary" id="cargo" name="cargo" required>
                        <option value="" disabled <?= empty($colaboradorEdite['cargo']) ? 'selected' : '' ?>>Selecione o cargo...</option>
                        <?php foreach ($cargosDisponiveis as $cOpt): ?>
                            <?php $selected = (isset($colaboradorEdite['cargo']) && $colaboradorEdite['cargo'] === $cOpt) ? 'selected' : ''; ?>
                            <option value="<?= htmlspecialchars($cOpt) ?>" <?= $selected ?>><?= htmlspecialchars($cOpt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </section>
                <section class="col-md-6">
                    <label for="setor" class="form-label fw-bold small mb-1">Setor *</label>
                    <input type="text" class="form-control border-secondary" id="setor" name="setor"
                           value="<?= htmlspecialchars($colaboradorEdite['setor'] ?? '') ?>" required>
                </section>
            </section>

            <button type="submit" class="btn-confirmar-form">
                <?= $colaboradorEdite ? 'SALVAR' : 'Cadastrar' ?>
            </button>

            <section class="text-center mt-3">
                <a href="index.php?rota=colaboradores" class="text-secondary small text-decoration-none">
                    &larr; Voltar para a listagem de colaboradores
                </a>
            </section>
        </form>
    </section>

<?php else: ?>
    <section class="painel">
        <h2>Colaborador</h2>

        <form method="GET" action="index.php" class="barra-busca">
            <input type="hidden" name="rota" value="colaboradores">
            <section class="input-busca-box">
                <input type="text" name="pesquisa" placeholder="Pesquise pelo nome"
                       value="<?= htmlspecialchars($pesquisa) ?>">
                <button type="submit" title="Pesquisar">&#128269;</button>
            </section>
            <?php if (!empty($pesquisa)): ?>
                <a href="index.php?rota=colaboradores" class="btn-topo-tabela">Limpar</a>
            <?php endif; ?>
            <a href="index.php?rota=colaboradores&acao=cadastrar" class="btn-topo-tabela">+ Novo</a>
        </form>

        <?php if (count($colaboradores) > 0): ?>
            <section class="table-responsive">
                <table class="tabela-wireframe">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>Cargo</th>
                            <th>Setor</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($colaboradores as $c): ?>
                            <tr>
                                <td><?= (int) $c['id'] ?></td>
                                <td><?= htmlspecialchars($c['nome']) ?></td>
                                <td><?= htmlspecialchars($c['cpf']) ?></td>
                                <td><?= htmlspecialchars(!empty($c['telefone']) ? $c['telefone'] : '-') ?></td>
                                <td><?= htmlspecialchars($c['cargo']) ?></td>
                                <td><?= htmlspecialchars($c['setor']) ?></td>
                                <td>
                                    <button type="button" class="btn-remover"
                                            onclick="confirmarExclusao(<?= (int) $c['id'] ?>, '<?= htmlspecialchars($c['nome'], ENT_QUOTES) ?>')">
                                        Remover
                                    </button>
                                    <a href="index.php?rota=colaboradores&acao=editar&id=<?= (int) $c['id'] ?>" class="btn-editar">
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

<!-- Modal de Exclusão -->
<section class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <section class="modal-dialog modal-dialog-centered">
        <section class="modal-content modal-wireframe">
            <section class="modal-header border-0 pb-1">
                <h5 class="modal-title">Remover Colaborador</h5>
            </section>
            <section class="modal-body text-center py-3">
                <p class="mb-1">Você tem certeza que deseja remover este colaborador?</p>
                <small class="text-muted fw-bold" id="nomeColaboradorExcluir"></small>
            </section>
            <section class="modal-footer border-0 justify-content-center gap-3 pt-1">
                <button type="button" class="btn-modal-cancelar" data-bs-dismiss="modal">Cancelar</button>
                
                <!-- Formulário com método POST para exclusão -->
                <form method="POST" action="index.php?rota=colaboradores&acao=excluir" style="display: inline;">
                    <input type="hidden" name="id" id="idColaboradorExcluir" value="">
                    <button type="submit" class="btn-modal-remover border-0">Remover</button>
                </form>
            </section>
        </section>
    </section>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function () {
        $('#cpf').mask('000.000.000-00');
        var SPMaskBehavior = function (val) {
            return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-0000';
        },
        spOptions = {
            onKeyPress: function (val, e, field, options) {
                field.mask(SPMaskBehavior.apply({}, arguments), options);
            }
        };
        $('#telefone').mask(SPMaskBehavior, spOptions);
    });

    function confirmarExclusao(id, nome) {
        document.getElementById('nomeColaboradorExcluir').innerText = nome;
        document.getElementById('idColaboradorExcluir').value = id; // Define o ID no input hidden
        var modal = new bootstrap.Modal(document.getElementById('modalExcluir'));
        modal.show();
    }
</script>