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
        <h2>Cadastrar Empréstimo</h2>
        <form method="POST" action="index.php?rota=controle_epi&acao=cadastrar" id="formCadastroEmprestimo">
            <input type="hidden" name="operacao" value="cadastrar">

            <section class="mb-3">
                <label for="equipamento_id" class="form-label fw-bold small mb-1">Equipamento *</label>
                <div class="input-group mb-1">
                    <input type="text" id="bipar_epi" class="form-control form-control-sm border-secondary input-leitor-barras" placeholder="Bipe o QR Code ou código do EPI aqui...">
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="abrirScannerCamera('bipar_epi')">&#128247; Ler EPI</button>
                </div>
                <select name="equipamento_id" id="equipamento_id" class="form-select border-secondary" required>
                    <option value="">Selecione um equipamento...</option>
                    <?php foreach ($listaEquipamentos as $eq): ?>
                        <?php
                        $qtd = (int) ($eq['quantidade'] ?? 0);
                        $caVencido = !empty($eq['validade_ca']) && (strtotime($eq['validade_ca']) < strtotime(date('Y-m-d')));
                        $bloqueado = ($qtd <= 0 || $caVencido);
                        $statusTexto = $caVencido ? ' [CA VENCIDO]' : ($qtd <= 0 ? ' [ESGOTADO]' : " [Estoque: {$qtd} un.]");
                        ?>
                        <option value="<?= (int) $eq['id'] ?>" data-codigo="<?= htmlspecialchars($eq['codigo_barras'] ?? '') ?>" data-ca="<?= htmlspecialchars($eq['ca'] ?? '') ?>" data-nome="<?= htmlspecialchars(strtolower($eq['nome'])) ?>" <?= $bloqueado ? 'disabled' : '' ?>>
                            <?= htmlspecialchars($eq['nome'] . ' (' . $eq['ca'] . ')' . $statusTexto) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </section>

            <section class="mb-3">
                <label for="colaborador_id" class="form-label fw-bold small mb-1">Colaborador *</label>
                <div class="input-group mb-1">
                    <input type="text" id="bipar_cracha" class="form-control form-control-sm border-secondary input-leitor-barras" placeholder="Bipe o QR Code ou crachá do colaborador aqui...">
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="abrirScannerCamera('bipar_cracha')">&#128247; Ler Crachá</button>
                </div>
                <select name="colaborador_id" id="colaborador_id" class="form-select border-secondary" required>
                    <option value="">Selecione um colaborador...</option>
                    <?php foreach ($listaColaboradores as $col): ?>
                        <option value="<?= (int) $col['id'] ?>" data-codigo="<?= htmlspecialchars($col['codigo_barras'] ?? '') ?>" data-cpf="<?= htmlspecialchars($col['cpf'] ?? '') ?>" data-nome="<?= htmlspecialchars(strtolower($col['nome'])) ?>">
                            <?= htmlspecialchars($col['nome'] . ' - ' . $col['cargo'] . (!empty($col['codigo_barras']) ? ' (Crachá: ' . $col['codigo_barras'] . ')' : '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </section>

            <section class="row g-3 mb-3">
                <section class="col-md-6">
                    <label for="data_emprestimo" class="form-label fw-bold small mb-1">Data e Hora de Entrega *</label>
                    <input type="datetime-local" name="data_emprestimo" id="data_emprestimo" class="form-control border-secondary" value="<?= date('Y-m-d\TH:i') ?>" required>
                </section>
                <section class="col-md-6">
                    <label for="data_prevista_devolucao" class="form-label fw-bold small mb-1">Data e Hora Prevista para Devolução *</label>
                    <input type="datetime-local" name="data_prevista_devolucao" id="data_prevista_devolucao" class="form-control border-secondary" min="<?= date('Y-m-d\TH:i', strtotime('+1 minute')) ?>" value="<?= date('Y-m-d\TH:i', strtotime('+7 days')) ?>" required>
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
                <label for="condicoes_emprestimo" class="form-label fw-bold small mb-1">Condições do equipamento na entrega</label>
                <input type="text" name="condicoes_emprestimo" id="condicoes_emprestimo" class="form-control border-secondary" placeholder="Descreva o estado de entrega do EPI...">
            </section>

            <button type="submit" class="btn-confirmar-form">Cadastrar</button>
        </form>
    </section>

<?php elseif ($acao === 'editar' && $registroEdicao): ?>
    <section class="painel">
        <h2>Atualizar / Dar Baixa</h2>
        <form method="POST" action="index.php?rota=controle_epi&acao=editar&id=<?= (int) $registroEdicao['id'] ?>">
            <input type="hidden" name="operacao" value="atualizar">
            <input type="hidden" name="id" value="<?= (int) $registroEdicao['id'] ?>">

            <section class="mb-3">
                <label class="form-label fw-bold small mb-1">Equipamento</label>
                <select disabled class="form-select border-secondary campo-bloqueado">
                    <option><?= htmlspecialchars($registroEdicao['nome_equipamento']) ?></option>
                </select>
            </section>

            <section class="mb-3">
                <label class="form-label fw-bold small mb-1">Colaborador</label>
                <select disabled class="form-select border-secondary campo-bloqueado">
                    <option><?= htmlspecialchars($registroEdicao['nome_colaborador']) ?></option>
                </select>
            </section>

            <section class="row g-3 mb-3">
                <section class="col-md-6">
                    <label class="form-label fw-bold small mb-1">Data de Entrega</label>
                    <input type="datetime-local" value="<?= !empty($registroEdicao['data_emprestimo']) ? date('Y-m-d\TH:i', strtotime($registroEdicao['data_emprestimo'])) : '' ?>" disabled class="form-control border-secondary campo-bloqueado">
                </section>
                <section class="col-md-6">
                    <label class="form-label fw-bold small mb-1">Data Prevista para Devolução</label>
                    <input type="datetime-local" value="<?= !empty($registroEdicao['data_prevista_devolucao']) ? date('Y-m-d\TH:i', strtotime($registroEdicao['data_prevista_devolucao'])) : '' ?>" disabled class="form-control border-secondary campo-bloqueado">
                </section>
            </section>

            <section class="row g-3 mb-3">
                <section class="col-md-6">
                    <label class="form-label fw-bold small mb-1">Entregue por (Operador)</label>
                    <input type="text" value="<?= htmlspecialchars($registroEdicao['operador_entrega'] ?? 'Não registrado') ?>" disabled class="form-control border-secondary campo-bloqueado">
                </section>
                <section class="col-md-6">
                    <label class="form-label fw-bold small mb-1">Baixa realizada por</label>
                    <input type="text" value="<?= htmlspecialchars($registroEdicao['operador_baixa'] ?? '-') ?>" disabled class="form-control border-secondary campo-bloqueado">
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
                <label class="form-label fw-bold small mb-1">Condições do equipamento na entrega</label>
                <input type="text" value="<?= htmlspecialchars($registroEdicao['condicoes_emprestimo'] ?? '') ?>" disabled class="form-control border-secondary campo-bloqueado">
            </section>

            <section id="bloco_devolucao" style="display: none;">
                <section class="mb-3">
                    <label for="data_devolucao" class="form-label fw-bold small mb-1">Data Efetiva da Devolução *</label>
                    <input type="datetime-local" name="data_devolucao" id="data_devolucao" class="form-control border-secondary" value="<?= !empty($registroEdicao['data_devolucao']) ? date('Y-m-d\TH:i', strtotime($registroEdicao['data_devolucao'])) : date('Y-m-d\TH:i') ?>">
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
            <?php if (in_array($perfilUsuario ?? '', ['Administrador', 'Almoxarife'], true)): ?>
                <a href="index.php?rota=controle_epi&acao=cadastrar" class="btn-topo-tabela">+ Novo Empréstimo</a>
            <?php endif; ?>
        </section>

        <?php if (count($listaEmprestimos) > 0): ?>
            <section class="table-responsive">
                <table class="tabela-wireframe">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Equipamento</th>
                            <th>Colaborador</th>
                            <th>Data de Entrega</th>
                            <th>Prev. Devolução</th>
                            <th>Status</th>
                            <th>Data Efetiva Devolução</th>
                            <th>Auditoria (Entrega / Baixa)</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listaEmprestimos as $item): ?>
                            <tr>
                                <td><?= (int) $item['ID_Emprestimo'] ?></td>
                                <td><?= htmlspecialchars($item['Equipamento_Emprestado']) ?></td>
                                <td><?= htmlspecialchars($item['Nome_Colaborador']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($item['Data_Emprestimo'])) ?></td>
                                <td>
                                    <?= !empty($item['Data_Prevista_Devolucao']) ? date('d/m/Y H:i', strtotime($item['Data_Prevista_Devolucao'])) : '-' ?>
                                </td>
                                <td><strong><?= htmlspecialchars($item['Status_Emprestimo']) ?></strong></td>
                                <td>
                                    <?= !empty($item['Data_Devolucao']) ? date('d/m/Y H:i', strtotime($item['Data_Devolucao'])) : '-' ?>
                                </td>
                                <td class="small">
                                    Entregue: <strong><?= htmlspecialchars($item['Operador_Entrega'] ?? '-') ?></strong>
                                    <?php if (!empty($item['Operador_Baixa'])): ?>
                                        <br>Baixa: <strong><?= htmlspecialchars($item['Operador_Baixa']) ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (in_array($perfilUsuario ?? '', ['Administrador', 'Almoxarife'], true)): ?>
                                        <a href="index.php?rota=controle_epi&acao=editar&id=<?= (int) $item['ID_Emprestimo'] ?>" class="btn-editar">Editar</a>
                                    <?php endif; ?>
                                    <a href="index.php?rota=controle_epi&acao=termo&id=<?= (int) $item['ID_Emprestimo'] ?>" class="btn-editar" style="background:#e2f0d9; border-color:#28a745;">
                                        &#128196; Termo
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

        $('#formCadastroEmprestimo').on('submit', function (e) {
            const inputPrevista = $('#data_prevista_devolucao').val();
            if (inputPrevista) {
                const dataPrevista = new Date(inputPrevista);
                const agora = new Date();
                if (dataPrevista <= agora) {
                    e.preventDefault();
                    alert('Atenção: A data prevista para devolução deve ser posterior à data e hora atuais!');
                    $('#data_prevista_devolucao').focus();
                }
            }
        });

        // Seleciona automaticamente o Equipamento ao bipar QR Code ou código
        $('#bipar_epi').on('change input', function () {
            let val = $(this).val().trim();
            if (!val) return;

            let termo = val.toLowerCase();
            try {
                const json = JSON.parse(val);
                termo = (json.codigo || json.ca || json.nome || '').toLowerCase();
            } catch (e) { }

            $('#equipamento_id option').each(function () {
                const cod = String($(this).data('codigo') || '').toLowerCase();
                const ca = String($(this).data('ca') || '').toLowerCase();
                const nome = String($(this).data('nome') || '').toLowerCase();

                if ((cod && cod === termo) || (ca && ca === termo) || (nome && nome.includes(termo))) {
                    if ($(this).is(':disabled')) {
                        alert('Atenção: Este equipamento está com estoque esgotado ou com CA vencido!');
                        return false;
                    }
                    $('#equipamento_id').val($(this).val());
                    $('#bipar_cracha').focus();
                    return false;
                }
            });
        });

        // Seleciona automaticamente o Colaborador ao bipar QR Code ou crachá
        $('#bipar_cracha').on('change input', function () {
            let val = $(this).val().trim();
            if (!val) return;

            let termo = val.toLowerCase();
            let cpfLimpo = val.replace(/\D/g, '');
            try {
                const json = JSON.parse(val);
                termo = (json.codigo || json.nome || '').toLowerCase();
                if (json.cpf) cpfLimpo = json.cpf.replace(/\D/g, '');
            } catch (e) { }

            $('#colaborador_id option').each(function () {
                const cod = String($(this).data('codigo') || '').toLowerCase();
                const cpfOp = String($(this).data('cpf') || '').replace(/\D/g, '');
                const nomeOp = String($(this).data('nome') || '').toLowerCase();

                if ((cod && cod === termo) || (cpfLimpo && cpfOp && cpfOp === cpfLimpo) || (nomeOp && nomeOp.includes(termo))) {
                    $('#colaborador_id').val($(this).val());
                    return false;
                }
            });
        });
    });
</script>
