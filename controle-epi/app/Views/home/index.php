<section class="cards-resumo">
    <section class="card-item">
        <span class="numero text-primary"><?= (int) ($contadores['Emprestado'] ?? 0) ?></span>
        <span class="rotulo">Emprestados</span>
    </section>
    <section class="card-item">
        <span class="numero text-success"><?= (int) ($contadores['Devolvido'] ?? 0) ?></span>
        <span class="rotulo">Devolvidos</span>
    </section>
    <section class="card-item">
        <span class="numero text-danger"><?= (int) ($contadores['Perdido'] ?? 0) ?></span>
        <span class="rotulo">Perdidos / Danificados</span>
    </section>
</section>

<section class="painel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Empréstimos em Andamento</h2>
        <a href="index.php?rota=controle_epi&acao=relatorio" class="btn btn-sm btn-outline-secondary">Ver Histórico Completo &rarr;</a>
    </div>

    <form method="GET" action="index.php" class="barra-busca">
        <input type="hidden" name="rota" value="home">
        <section class="input-busca-box">
            <input type="text" name="pesquisa" placeholder="Pesquisar empréstimos ativos por colaborador ou equipamento..." value="<?= htmlspecialchars($pesquisa) ?>">
            <button type="submit" title="Pesquisar">&#128269;</button>
        </section>
        <?php if (!empty($pesquisa)): ?>
            <a href="index.php?rota=home" class="btn-topo-tabela">Limpar</a>
        <?php endif; ?>
        <a href="index.php?rota=controle_epi&acao=cadastrar" class="btn-topo-tabela">+ Novo Empréstimo</a>
    </form>

    <?php if (count($emprestimos) > 0): ?>
        <section class="table-responsive">
            <table class="tabela-wireframe">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Equipamento</th>
                        <th>Colaborador</th>
                        <th>Data Empréstimo</th>
                        <th>Prev. Devolução</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emprestimos as $item): ?>
                        <tr>
                            <td><?= (int) $item['id'] ?></td>
                            <td><?= htmlspecialchars($item['equipamento']) ?></td>
                            <td><?= htmlspecialchars($item['colaborador']) ?></td>
                            <td><?= date('d/m/Y', strtotime($item['data_emprestimo'])) ?></td>
                            <td>
                                <?php if (!empty($item['data_prevista_devolucao'])): ?>
                                    <?php
                                    $atrasado = strtotime($item['data_prevista_devolucao']) < strtotime(date('Y-m-d'));
                                    ?>
                                    <span class="<?= $atrasado ? 'text-danger fw-bold' : '' ?>">
                                        <?= date('d/m/Y', strtotime($item['data_prevista_devolucao'])) ?>
                                        <?= $atrasado ? ' (Atrasado)' : '' ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($item['status']) ?></span></td>
                            <td>
                                <a href="index.php?rota=controle_epi&acao=editar&id=<?= (int) $item['id'] ?>" class="btn-editar">Dar Baixa / Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php else: ?>
        <section class="msg-vazio">
            Nenhum empréstimo pendente no momento.
        </section>
    <?php endif; ?>
</section>