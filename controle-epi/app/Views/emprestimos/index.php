<?php if ($mensagemSucesso !== ''): ?>
    <section class="alerta-wireframe alerta-sucesso">
        <?= htmlspecialchars($mensagemSucesso) ?>
    </section>
<?php endif; ?>
<?php if ($mensagemErro !== ''): ?>
    <section class="alerta-wireframe alerta-erro">
        <?= htmlspecialchars($mensagemErro) ?>
    </section>
<?php endif; ?>

<?php if ($acao === 'cadastrar'): ?>
    <section class="painel">
        <h2>Cadastrar</h2>
        <form method="POST" action="index.php?rota=controle_epi&acao=cadastrar">
            <input type="hidden" name="operacao" value="cadastrar">

            <section class="mb-3">
                <label for="equipamento_id" class="form-label fw-bold small mb-1">Equipamento *</label>
                <select name="equipamento_id" id="equipamento_id" class="form-select border-secondary" required>
                    <option value="">Selecione um equipamento...</option>
                    <?php foreach ($listaEquipamentos as $eq): ?>
                        <option value="<?= (int) $eq['id'] ?>">
                            <?= htmlspecialchars($eq['nome'] . ' (' . $eq['ca'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </section>

            <section class="mb-3">
                <label for="colaborador_id" class="form-label fw-bold small mb-1">Colaborador *</label>
                <select name="colaborador_id" id="colaborador_id" class="form-select border-secondary" required>
                    <option value="">Selecione um colaborador...</option>
                    <?php foreach ($listaColaboradores as $col): ?>
                        <option value="<?= (int) $col['id'] ?>">
                            <?= htmlspecialchars($col['nome'] . ' - ' . $col['cargo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </section>

            <section class="row g-3 mb-3">
                <section class="col-md-6">
                    <label for="data_emprestimo" class="form-label fw-bold small mb-1">Data do empréstimo *</label>
                    <input type="date" name="data_emprestimo" id="data_emprestimo" class="form-control border-secondary" value="<?= date('Y-m-d') ?>" required>
                </section>
                <section class="col-md-6">
                    <label for="data_prevista_devolucao" class="form-label fw-bold small mb-1">Data prevista de devolução</label>
                    <input type="date" name="data_prevista_devolucao" id="data_prevista_devolucao" class="form-control border-secondary">
                </section>
            </section>

            <section class="mb-3">
                <label for="status_cadastro" class="form-label fw-bold small mb-1">Status *</label>
                <select name="status" id="status_cadastro" class="form-select border-secondary" required>
                    <?php foreach ($statusCadastro as $st): ?>
                        <option value="<?= $st ?>">
                            <?= $st ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </section>

            <section class="mb-3">
                <label for="condicoes_emprestimo" class="form-label fw-bold small mb-1">Condições do equipamento no empréstimo</label>
                <input type="text" name="condicoes_emprestimo" id="condicoes_emprestimo" class="form-control border-secondary" placeholder="Descreva o estado de entrega do EPI...">
            </section>

            <button type="submit" class="btn-confirmar-form">Cadastrar</button>
        </form>
    </section>

<?php elseif ($acao === 'editar' && $registroEdicao): ?>
    <section class="painel">
        <h2>Atualizar</h2>
        <form method="POST" action="index.php?rota=controle_epi&acao=editar&id=<?= (int) $registroEdicao['id'] ?>">
            <input type="hidden" name="operacao" value="atualizar">
            <input type="hidden" name="id" value="<?= (int) $registroEdicao['id'] ?>">

            <section class="mb-3">
                <label class="form-label fw-bold small mb-1">Equipamento</label>
                <select disabled class="form-select border-secondary campo-bloqueado">
                    <option>
                        <?= htmlspecialchars($registroEdicao['nome_equipamento']) ?>
                    </option>
                </select>
            </section>

            <section class="mb-3">
                <label class="form-label fw-bold small mb-1">Colaborador</label>
                <select disabled class="form-select border-secondary campo-bloqueado">
                    <option>
                        <?= htmlspecialchars($registroEdicao['nome_colaborador']) ?>
                    </option>
                </select>
            </section>

            <section class="row g-3 mb-3">
                <section class="col-md-6">
                    <label class="form-label fw-bold small mb-1">Data do empréstimo</label>
                    <input type="date" value="<?= htmlspecialchars($registroEdicao['data_emprestimo']) ?>" disabled class="form-control border-secondary campo-bloqueado">
                </section>
                <section class="col-md-6">
                    <label class="form-label fw-bold small mb-1">Data prevista de devolução</label>
                    <input type="date" value="<?= htmlspecialchars($registroEdicao['data_prevista_devolucao'] ?? '') ?>" disabled class="form-control border-secondary campo-bloqueado">
                </section>
            </section>

            <section class="mb-3">
                <label for="status_edicao" class="form-label fw-bold small mb-1">Status *</label>
                <select name="status" id="status_edicao" class="form-select border-secondary" required>
                    <?php foreach ($statusEdicao as $st): ?>
                        <option value="<?= $st ?>" <?= $registroEdicao['status'] === $st ? 'selected' : '' ?>>
                            <?= $st ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </section>

            <section class="mb-3">
                <label class="form-label fw-bold small mb-1">Condições do equipamento no empréstimo</label>
                <input type="text" value="<?= htmlspecialchars($registroEdicao['condicoes_emprestimo'] ?? '') ?>" disabled class="form-control border-secondary campo-bloqueado">
            </section>

            <section id="bloco_devolucao" style="display: none;">
                <section class="mb-3">
                    <label for="data_devolucao" class="form-label fw-bold small mb-1">Data da devolução *</label>
                    <input type="date" name="data_devolucao" id="data_devolucao" class="form-control border-secondary" value="<?= htmlspecialchars($registroEdicao['data_devolucao'] ?? date('Y-m-d')) ?>">
                </section>
                <section class="mb-3">
                    <label for="observacao_devolucao" class="form-label fw-bold small mb-1">Observação na devolução</label>
                    <input type="text" name="observacao_devolucao" id="observacao_devolucao" class="form-control border-secondary" value="<?= htmlspecialchars($registroEdicao['observacao_devolucao'] ?? '') ?>" placeholder="Informe avarias, motivo do extravio ou condições de devolução...">
                </section>
            </section>

            <button type="submit" class="btn-confirmar-form">Atualizar</button>
        </form>
    </section>

<?php else: ?>
    <section class="painel">
        <h2>Filtro de Relatório (Pesquisa Combinada)</h2>
        <form method="GET" action="index.php">
            <input type="hidden" name="rota" value="controle_epi">
            <input type="hidden" name="acao" value="relatorio">

            <section class="mb-3">
                <label for="filtro_equipamento" class="form-label fw-bold small mb-1">Equipamento</label>
                <input type="text" name="filtro_equipamento" id="filtro_equipamento" class="form-control border-secondary" value="<?= htmlspecialchars($filtroEquipamento) ?>" placeholder="Pesquisar pelo nome do equipamento...">
            </section>

            <section class="mb-3">
                <label for="filtro_colaborador" class="form-label fw-bold small mb-1">Colaborador</label>
                <input type="text" name="filtro_colaborador" id="filtro_colaborador" class="form-control border-secondary" value="<?= htmlspecialchars($filtroColaborador) ?>" placeholder="Pesquisar pelo nome do colaborador...">
            </section>

            <section class="mb-3">
                <label for="filtro_status" class="form-label fw-bold small mb-1">Status</label>
                <select name="filtro_status" id="filtro_status" class="form-select border-secondary">
                    <option value="">Todos os status</option>
                    <?php foreach ($statusEdicao as $st): ?>
                        <option value="<?= $st ?>" <?= $filtroStatus === $st ? 'selected' : '' ?>>
                            <?= $st ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </section>

            <button type="submit" class="btn-confirmar-form">Pesquisar</button>
        </form>
    </section>

    <section class="painel">
        <section class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Resultados do Relatório</h2>
            <a href="index.php?rota=controle_epi&acao=cadastrar" class="btn-topo-tabela">+ Novo Empréstimo</a>
        </section>

        <?php if (count($listaEmprestimos) > 0): ?>
            <section class="table-responsive">
                <table class="tabela-wireframe">
                    <thead>
                        <tr>
                            <th>ID_Emprestimo</th>
                            <th>Equipamento_Emprestado</th>
                            <th>Nome_Colaborador</th>
                            <th>Data_Emprestimo</th>
                            <th>Status_Emprestimo</th>
                            <th>Data_Devolucao</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listaEmprestimos as $item): ?>
                            <tr>
                                <td>
                                    <?= (int) $item['ID_Emprestimo'] ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($item['Equipamento_Emprestado']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($item['Nome_Colaborador']) ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($item['Data_Emprestimo'])) ?>
                                </td>
                                <td><strong>
                                        <?= htmlspecialchars($item['Status_Emprestimo']) ?>
                                    </strong></td>
                                <td>
                                    <?= !empty($item['Data_Devolucao']) ? date('d/m/Y', strtotime($item['Data_Devolucao'])) : '-' ?>
                                </td>
                                <td>
                                    <a href="index.php?rota=controle_epi&acao=editar&id=<?= (int) $item['ID_Emprestimo'] ?>" class="btn-editar">Editar</a>
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

<script>
    $(document).ready(function () {
        function verificarStatusDevolucao() {
            const statusSelecionado = $('#status_edicao').val();
            const statusComDevolucao = ['Devolvido', 'Danificado', 'Perdido'];

            if (statusComDevolucao.includes(statusSelecionado)) {
                $('#bloco_devolucao').slideDown(200);
                $('#data_devolucao').prop('required', true);
            } else {
                $('#bloco_devolucao').slideUp(200);
                $('#data_devolucao').prop('required', false);
            }
        }

        if ($('#status_edicao').length) {
            verificarStatusDevolucao();
            $('#status_edicao').on('change', verificarStatusDevolucao);
        }
    });
</script>