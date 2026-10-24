<?php if (!empty($mensagem)): ?>
    <section class="alerta-wireframe <?= $tipoAlerta === 'sucesso' ? 'alerta-sucesso' : 'alerta-erro' ?>">
        <?= htmlspecialchars($mensagem) ?>
    </section>
<?php endif; ?>

<?php if ($acao === 'cadastrar' || ($acao === 'editar' && $equipamentoEdite)): ?>
    <section class="painel">
        <h2><?= $equipamentoEdite ? 'Editar Equipamento' : 'Cadastrar Equipamento' ?></h2>
        <form action="index.php?rota=equipamentos&acao=<?= $equipamentoEdite ? 'editar' : 'cadastrar' ?>" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($equipamentoEdite['id'] ?? '') ?>">

            <!-- Campo do Leitor para Auto-Preenchimento -->
            <section class="mb-3 p-3 bg-light border border-secondary rounded">
                <label for="codigo_barras" class="form-label fw-bold small mb-1 text-primary">
                    &#128247; Leitor de QR Code do EPI (Preenchimento Automático)
                </label>
                <div class="input-group">
                    <input type="text" class="form-control border-secondary input-leitor-barras" id="codigo_barras" name="codigo_barras" placeholder="Bipe o QR Code da embalagem do EPI para preencher a ficha..." value="<?= htmlspecialchars($equipamentoEdite['codigo_barras'] ?? '') ?>" autofocus>
                    <button type="button" class="btn btn-primary fw-bold" onclick="abrirScannerCamera('codigo_barras')">
                        &#128247; Ler QR Code
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="gerarCodigoEpi()" title="Gerar etiqueta interna">
                        Gerar
                    </button>
                </div>
                <small class="text-muted" style="font-size: 0.78rem;">
                    Dica: Ao ler o QR Code da caixa ou nota do EPI, os campos de Nome, CA, Validade e Quantidade serão preenchidos sozinhos.
                </small>
            </section>

            <section class="mb-3">
                <label for="nome" class="form-label fw-bold small mb-1">Nome do Equipamento *</label>
                <input type="text" class="form-control border-secondary" id="nome" name="nome" value="<?= htmlspecialchars($equipamentoEdite['nome'] ?? '') ?>" required>
            </section>

            <section class="row g-3 mb-3">
                <section class="col-md-4">
                    <label for="ca" class="form-label fw-bold small mb-1">Certificado (CA) *</label>
                    <input type="text" class="form-control border-secondary" id="ca" name="ca" placeholder="Ex.: CA-31469" value="<?= htmlspecialchars($equipamentoEdite['ca'] ?? '') ?>" required>
                </section>
                <section class="col-md-4">
                    <label for="validade_ca" class="form-label fw-bold small mb-1">Validade do CA</label>
                    <input type="date" class="form-control border-secondary" id="validade_ca" name="validade_ca" value="<?= htmlspecialchars($equipamentoEdite['validade_ca'] ?? '') ?>">
                </section>
                <section class="col-md-4">
                    <label for="quantidade" class="form-label fw-bold small mb-1">Qtd. em Estoque *</label>
                    <input type="number" min="0" class="form-control border-secondary" id="quantidade" name="quantidade" value="<?= isset($equipamentoEdite['quantidade']) ? (int) $equipamentoEdite['quantidade'] : 10 ?>" required>
                </section>
            </section>

            <section class="mb-3">
                <label for="descricao" class="form-label fw-bold small mb-1">Descrição / Observações</label>
                <input type="text" class="form-control border-secondary" id="descricao" name="descricao" value="<?= htmlspecialchars($equipamentoEdite['descricao'] ?? '') ?>">
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
                <input type="text" id="pesquisa_eq" name="pesquisa" placeholder="Pesquise pelo nome, CA ou bipe o código de barras..." value="<?= htmlspecialchars($pesquisa) ?>">
                <button type="submit" title="Pesquisar">&#128269;</button>
            </section>
            <button type="button" class="btn-topo-tabela" onclick="abrirScannerCamera('pesquisa_eq')" title="Buscar com a câmera">
                &#128247;
            </button>
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
                            <th>Cód. Barras</th>
                            <th>Equipamento</th>
                            <th>CA / Validade</th>
                            <th>Estoque</th>
                            <th>Descrição</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipamentos as $eq): ?>
                            <?php
                            $qtd = (int) ($eq['quantidade'] ?? 0);
                            $valCa = $eq['validade_ca'] ?? null;
                            $caVencido = $valCa && (strtotime($valCa) < strtotime(date('Y-m-d')));
                            ?>
                            <tr>
                                <td><?= (int) $eq['id'] ?></td>
                                <td><code><?= htmlspecialchars(!empty($eq['codigo_barras']) ? $eq['codigo_barras'] : '-') ?></code></td>
                                <td><?= htmlspecialchars($eq['nome']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($eq['ca']) ?></strong>
                                    <?php if ($valCa): ?>
                                        <br>
                                        <small class="<?= $caVencido ? 'text-danger fw-bold' : 'text-muted' ?>">
                                            Val: <?= date('d/m/Y', strtotime($valCa)) ?>
                                            <?= $caVencido ? ' (Vencido!)' : '' ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($qtd <= 0): ?>
                                        <span class="badge bg-danger">Esgotado (0)</span>
                                    <?php elseif ($qtd <= 3): ?>
                                        <span class="badge bg-warning text-dark">Baixo (<?= $qtd ?>)</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?= $qtd ?> un.</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(!empty($eq['descricao']) ? $eq['descricao'] : '-') ?></td>
                                <td>
                                    <?php $codEpi = !empty($eq['codigo_barras']) ? $eq['codigo_barras'] : $eq['ca']; ?>
                                    <button type="button" class="btn-editar" style="background:#e2f0d9; border-color:#28a745;" onclick="exibirEtiqueta('ETIQUETA DE EPI', '<?= htmlspecialchars($codEpi, ENT_QUOTES) ?>', '<?= htmlspecialchars($eq['nome'], ENT_QUOTES) ?>', 'CA: <?= htmlspecialchars($eq['ca'], ENT_QUOTES) ?>')">
                                        &#127991; Etiqueta
                                    </button>
                                    <?php if (($perfilUsuario ?? '') === 'Administrador'): ?>
                                        <button type="button" class="btn-remover" onclick="confirmarExclusaoEquipamento(<?= (int) $eq['id'] ?>, '<?= htmlspecialchars($eq['nome'], ENT_QUOTES) ?>')">
                                            Remover
                                        </button>
                                    <?php endif; ?>
                                    <a href="index.php?rota=equipamentos&acao=editar&id=<?= (int) $eq['id'] ?>" class="btn-editar">
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
    function gerarCodigoEpi() {
        const aleatorio = Math.floor(100000000000 + Math.random() * 900000000000);
        document.getElementById('codigo_barras').value = '789' + aleatorio;
    }

    function confirmarExclusaoEquipamento(id, nome) {
        document.getElementById('nomeEquipamentoExcluir').innerText = nome;
        document.getElementById('linkConfirmarExclusaoEquipamento').href = 'index.php?rota=equipamentos&acao=excluir&id=' + id;
        var modal = new bootstrap.Modal(document.getElementById('modalExcluirEquipamento'));
        modal.show();
    }
</script>
