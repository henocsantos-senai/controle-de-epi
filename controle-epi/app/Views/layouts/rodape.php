</main>
</section>

<!-- Modal Global do Scanner de Câmera -->
<section class="modal fade" id="modalScannerCodigo" tabindex="-1" aria-hidden="true">
    <section class="modal-dialog modal-dialog-centered">
        <section class="modal-content modal-wireframe">
            <section class="modal-header border-0 pb-1">
                <h5 class="modal-title">Leitor de QR Code / Código de Barras</h5>
            </section>
            <section class="modal-body text-center py-3">
                <p class="small text-secondary mb-2">Aponte a câmera para o QR Code para preencher a ficha:</p>
                <div id="leitor-camera-container" style="width: 100%; max-width: 360px; margin: 0 auto; border-radius: 8px; overflow: hidden;"></div>
                <div id="status-scanner" class="small text-danger fw-bold mt-2"></div>
            </section>
            <section class="modal-footer border-0 justify-content-center pt-1">
                <button type="button" class="btn-modal-cancelar" data-bs-dismiss="modal" onclick="pararScannerCamera()">Fechar Câmera</button>
            </section>
        </section>
    </section>
</section>

<!-- Modal Global da Etiqueta (Original) -->
<section class="modal fade" id="modalEtiqueta" tabindex="-1" aria-hidden="true">
    <section class="modal-dialog modal-dialog-centered">
        <section class="modal-content modal-wireframe">
            <section class="modal-header border-0 pb-1">
                <h5 class="modal-title" id="tituloModalEtiqueta">Etiqueta de Identificação</h5>
            </section>
            <section class="modal-body text-center py-3">
                <div id="areaImpressaoEtiqueta" class="p-3 border border-dark rounded bg-white mx-auto" style="max-width: 320px;">
                    <div class="fw-bold text-uppercase small border-bottom border-dark pb-1 mb-2" id="etiquetaSubtitulo">CONTROLE DE EPI</div>
                    <div class="fw-bold mb-1" id="etiquetaNome" style="font-size: 1rem;"></div>
                    <div class="small text-muted mb-2" id="etiquetaDetalhe"></div>
                    <div id="etiquetaQrCode" class="d-flex justify-content-center my-2"></div>
                    <svg id="etiquetaBarcode" class="img-fluid"></svg>
                </div>
            </section>
            <section class="modal-footer border-0 justify-content-center gap-2 pt-1">
                <button type="button" class="btn-modal-cancelar" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-dark fw-bold px-3 py-1" onclick="imprimirEtiquetaModal()">&#128424; Imprimir</button>
            </section>
        </section>
    </section>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    let html5QrCode = null;
    let inputAlvoScanner = null;
    let modalScannerInstancia = null;

    function abrirScannerCamera(idInputDestino) {
        inputAlvoScanner = document.getElementById(idInputDestino);
        document.getElementById('status-scanner').innerText = '';

        const modalEl = document.getElementById('modalScannerCodigo');
        modalScannerInstancia = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalScannerInstancia.show();

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("leitor-camera-container");
        }

        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 180 } },
            (textoDecodificado) => {
                processarLeituraCodigo(textoDecodificado);
                pararScannerCamera();
                if (modalScannerInstancia) modalScannerInstancia.hide();
            },
            (erroLeitura) => { }
        ).catch((err) => {
            document.getElementById('status-scanner').innerText =
                "Câmera inacessível. Verifique as permissões no navegador ou utilize um leitor de código de barras USB.";
        });
    }

    function pararScannerCamera() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
            }).catch(() => { });
        }
    }

    /**
     * Motor de Preenchimento Automático Inteligente
     * Reconhece dados em JSON ou texto chave-valor e preenche o formulário
     */
    function processarLeituraCodigo(texto) {
        if (!texto) return;
        texto = texto.trim();

        let dados = null;

        // 1. Tenta decodificar como JSON
        try {
            dados = JSON.parse(texto);
        } catch (e) {
            // 2. Se não for JSON, faz parse por linha/delimitador chave: valor
            dados = {};
            const linhas = texto.split(/[\n;\r]+/);
            linhas.forEach(linha => {
                const partes = linha.split(/[:=](.*)/s);
                if (partes.length >= 2) {
                    const chave = partes[0].trim().toLowerCase();
                    const valor = partes[1].trim();
                    if (chave.includes('nome')) dados.nome = valor;
                    else if (chave.includes('cpf')) dados.cpf = valor;
                    else if (chave.includes('tel') || chave.includes('fone')) dados.telefone = valor;
                    else if (chave.includes('cargo') || chave.includes('funcao')) dados.cargo = valor;
                    else if (chave.includes('setor') || chave.includes('departamento')) dados.setor = valor;
                    else if (chave.includes('ca')) dados.ca = valor;
                    else if (chave.includes('validade')) dados.validade_ca = valor;
                    else if (chave.includes('qtd') || chave.includes('quantidade')) dados.quantidade = valor;
                    else if (chave.includes('desc')) dados.descricao = valor;
                    else if (chave.includes('cod') || chave.includes('barras')) dados.codigo_barras = valor;
                }
            });
        }

        // --- PREENCHIMENTO NA TELA DE CADASTRO DE COLABORADOR ---
        if (document.getElementById('nome') && (document.getElementById('cpf') || document.getElementById('cargo'))) {
            if (dados.nome) $('#nome').val(dados.nome);
            if (dados.cpf) $('#cpf').val(dados.cpf).trigger('input');
            if (dados.telefone) $('#telefone').val(dados.telefone).trigger('input');
            if (dados.setor) $('#setor').val(dados.setor);
            if (dados.codigo_barras) $('#codigo_barras').val(dados.codigo_barras);
            else if (dados.codigo) $('#codigo_barras').val(dados.codigo);
            else if (!texto.startsWith('{')) $('#codigo_barras').val(texto);

            if (dados.cargo) {
                // Tenta selecionar no dropdown de cargos
                let selecionou = false;
                $('#cargo option').each(function () {
                    if ($(this).val().toLowerCase() === dados.cargo.toLowerCase()) {
                        $('#cargo').val($(this).val());
                        selecionou = true;
                        return false;
                    }
                });
                if (!selecionou) $('#cargo').val('Outro');
            }
        }

        // --- PREENCHIMENTO NA TELA DE CADASTRO DE EQUIPAMENTO ---
        if (document.getElementById('ca') && document.getElementById('quantidade')) {
            if (dados.nome) $('#nome').val(dados.nome);
            if (dados.ca) $('#ca').val(dados.ca);
            if (dados.quantidade !== undefined) $('#quantidade').val(parseInt(dados.quantidade) || 1);
            if (dados.validade_ca) {
                // Trata formato dd/mm/yyyy para yyyy-mm-dd
                let d = dados.validade_ca;
                if (d.includes('/')) {
                    d = d.split('/').reverse().join('-');
                }
                $('#validade_ca').val(d);
            }
            if (dados.descricao) $('#descricao').val(dados.descricao);
            if (dados.codigo_barras) $('#codigo_barras').val(dados.codigo_barras);
            else if (dados.codigo) $('#codigo_barras').val(dados.codigo);
            else if (!texto.startsWith('{')) $('#codigo_barras').val(texto);
        }

        // --- PREENCHIMENTO NA TELA DE EMPRÉSTIMO ---
        if (inputAlvoScanner) {
            inputAlvoScanner.value = dados.codigo || dados.codigo_barras || dados.ca || dados.cpf || texto;
            $(inputAlvoScanner).trigger('change');
        }
    }

    document.getElementById('modalScannerCodigo')?.addEventListener('hidden.bs.modal', pararScannerCamera);

    // Exibe Etiqueta Padrão
    function exibirEtiqueta(tipo, codigo, nome, detalhe) {
        document.getElementById('etiquetaSubtitulo').innerText = tipo;
        document.getElementById('etiquetaNome').innerText = nome;
        document.getElementById('etiquetaDetalhe').innerText = detalhe;

        const qrContainer = document.getElementById('etiquetaQrCode');
        qrContainer.innerHTML = '';
        new QRCode(qrContainer, {
            text: String(codigo),
            width: 110,
            height: 110
        });

        JsBarcode("#etiquetaBarcode", String(codigo), {
            format: "CODE128",
            width: 1.6,
            height: 40,
            displayValue: true,
            fontSize: 13,
            margin: 6
        });

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEtiqueta'));
        modal.show();
    }

    function imprimirEtiquetaModal() {
        const conteudo = document.getElementById('areaImpressaoEtiqueta').outerHTML;
        const janela = window.open('', '_blank', 'width=450,height=550');
        janela.document.write(`
            <html>
            <head>
                <title>Imprimir Etiqueta</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                    @media print { .no-print { display: none; } }
                </style>
            </head>
            <body onload="window.print(); window.close();">
                ${conteudo}
            </body>
            </html>
        `);
        janela.document.close();
    }

    // Leitura por pistola leitora USB física
    $(document).on('change', '.input-leitor-barras', function () {
        processarLeituraCodigo($(this).val());
    });

    $(document).on('keydown', '.input-leitor-barras', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            processarLeituraCodigo($(this).val());
            const inputs = $(this).closest('form').find('input:visible, select:visible, button:visible');
            const idx = inputs.index(this);
            if (idx > -1 && idx + 1 < inputs.length) {
                inputs.eq(idx + 1).focus();
            }
        }
    });
</script>
</body>

</html>
