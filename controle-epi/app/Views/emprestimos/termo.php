<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Termo de Responsabilidade de EPI #
        <?= (int) $termo['id'] ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
            padding: 24px;
        }

        .folha-a4 {
            background: #fff;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            border: 1.5px solid #333;
        }

        .assinatura-box {
            border-top: 1px solid #000;
            width: 280px;
            margin: 50px auto 0;
            padding-top: 6px;
            text-align: center;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .folha-a4 {
                border: none;
                padding: 10px;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="text-center mb-3 no-print">
        <button onclick="window.print()" class="btn btn-success fw-bold px-4">&#128424; Imprimir Termo (NR-6)</button>
        <a href="javascript:history.back()" class="btn btn-outline-secondary ms-2">&larr; Voltar</a>
    </div>

    <div class="folha-a4">
        <h4 class="text-center fw-bold text-decoration-underline mb-4">
            TERMO DE RESPONSABILIDADE E FICHA DE CONTROLE DE EPI (NR-6)
        </h4>

        <p class="small text-justify mb-4">
            Declaro para os devidos fins que recebi gratuitamente o Equipamento de Proteção Individual (EPI) abaixo especificado, em perfeitas condições de uso e com Certificado de Aprovação (CA) válido, comprometendo-me a utilizá-lo apenas para a finalidade a que se destina, responsabilizando-me por sua guarda, conservação e devolução, conforme determina a Norma Regulamentadora NR-6.
        </p>

        <h6 class="fw-bold border-bottom pb-1">1. Dados do Colaborador</h6>
        <div class="row mb-3 small">
            <div class="col-6"><strong>Nome:</strong>
                <?= htmlspecialchars($termo['nome_colaborador']) ?>
            </div>
            <div class="col-6"><strong>CPF:</strong>
                <?= htmlspecialchars($termo['cpf_colaborador'] ?? '-') ?>
            </div>
            <div class="col-6 mt-1"><strong>Cargo:</strong>
                <?= htmlspecialchars($termo['cargo_colaborador'] ?? '-') ?>
            </div>
            <div class="col-6 mt-1"><strong>Setor:</strong>
                <?= htmlspecialchars($termo['setor_colaborador'] ?? '-') ?>
            </div>
        </div>

        <h6 class="fw-bold border-bottom pb-1">2. Dados do Equipamento (EPI) e Empréstimo (#
            <?= (int) $termo['id'] ?>)
        </h6>
        <table class="table table-bordered table-sm small text-center align-middle mb-4">
            <thead class="table-light">
                <tr>
                    <th>Equipamento</th>
                    <th>CA</th>
                    <th>Data/Hora Entrega</th>
                    <th>Prev. Devolução</th>
                    <th>Status Atual</th>
                    <th>Data Efetiva Devolução</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?= htmlspecialchars($termo['nome_equipamento']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($termo['ca_equipamento'] ?? '-') ?>
                    </td>
                    <td>
                        <?= date('d/m/Y H:i', strtotime($termo['data_emprestimo'])) ?>
                    </td>
                    <td>
                        <?= !empty($termo['data_prevista_devolucao']) ? date('d/m/Y H:i', strtotime($termo['data_prevista_devolucao'])) : '-' ?>
                    </td>
                    <td><strong>
                            <?= htmlspecialchars($termo['status']) ?>
                        </strong></td>
                    <td>
                        <?= !empty($termo['data_devolucao']) ? date('d/m/Y H:i', strtotime($termo['data_devolucao'])) : '-' ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="small mb-4">
            <strong>Condições na Entrega:</strong>
            <?= htmlspecialchars(!empty($termo['condicoes_emprestimo']) ? $termo['condicoes_emprestimo'] : 'Sem observações') ?><br>
            <?php if (!empty($termo['observacao_devolucao'])): ?>
                <strong>Observações na Devolução:</strong>
                <?= htmlspecialchars($termo['observacao_devolucao']) ?><br>
            <?php endif; ?>
            <strong>Entregue por (Almoxarifado):</strong>
            <?= htmlspecialchars($termo['operador_entrega'] ?? 'Sistema') ?>
            <?php if (!empty($termo['operador_baixa'])): ?>
                | <strong>Baixa realizada por:</strong>
                <?= htmlspecialchars($termo['operador_baixa']) ?>
            <?php endif; ?>
        </div>

        <div class="row mt-5 pt-3">
            <div class="col-6">
                <div class="assinatura-box small">
                    <strong>
                        <?= htmlspecialchars($termo['nome_colaborador']) ?>
                    </strong><br>
                    Assinatura do Colaborador
                </div>
            </div>
            <div class="col-6">
                <div class="assinatura-box small">
                    <strong>
                        <?= htmlspecialchars($termo['operador_entrega'] ?? 'Responsável pelo Almoxarifado') ?>
                    </strong><br>
                    Responsável pela Entrega (Almoxarifado)
                </div>
            </div>
        </div>

        <div class="text-center text-muted mt-4" style="font-size: 0.72rem;">
            Documento emitido eletronicamente pelo Sistema de Controle de EPI em
            <?= date('d/m/Y \à\s H:i') ?>.
        </div>
    </div>
</body>

</html>