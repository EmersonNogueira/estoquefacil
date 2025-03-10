<!-- Modal para exibir produtos -->

<div id="modalProdutos" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharModal()">&times;</span>
        <h2>Produtos da solicitação <span id="id_solicitacao"></span> aguardando atendimento</h2>

        <!-- Formulário para envio via POST -->
        <form id="formProdutos" method="post" action="<?php echo $base_url; ?>Registro/produtos">
            <!-- Campo oculto para enviar o ID da solicitação -->
            <input type="hidden" name="id_solicitacao" id="idSolicitacaoInput" value="">
            <input type="hidden" name="destino" id="destino" value="">

            <fieldset>
                <legend>Informações da Solicitação</legend>
                <div><strong>Solicitado por:</strong> <span id="solicitanteNome"></span></div>
                <div><strong>Setor:</strong> <span id="destinoTexto"></span></div>
                <div><strong>Subsetor:</strong> <span id="subsetor"></span></div>

                <div><strong>Data da abertura:</strong> <span id="dataSolicitacao"></span></div>
            </fieldset>


            <fieldset>
                <legend>Produtos</legend>
                <div id="produtosContainer">
                    <!-- Conteúdo de produtos será carregado aqui -->
                </div>
            </fieldset>

            <fieldset>
                <legend>Recebimento</legend>
                <label for="receptor">
                    <h3>Nome de quem está recebendo os produtos</h3>
                </label>
                <input type="text" name="receptor" id="receptor" placeholder="Digite o nome do receptor" required>
            </fieldset>

            <!-- Botões de ação -->
            <div class="modal-botoes">
                <button type="button" onclick="imprimirModal()">Imprimir</button>
                <button type="submit">Finalizar</button>
            </div>
        </form>

    </div>
</div>
<!-- Tabela de Solicitação -->
<h1>Solicitações em aberto</h1>
<table border="1" cellspacing="0" cellpadding="10">
    <thead>
        <tr>
            <th>Código Solicitação</th>
            <th>Usuário Criador</th>
            <th>Solicitante</th>
            <th>Setor</th>
            <th>Subsetor</th>
            <th>Data da abetura</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($solicitacao as $sol): ?>
            <tr>
                <td><?php echo htmlspecialchars($sol['id_solicitacao']); ?></td>
                <td><?php echo htmlspecialchars($sol['usuario_criador']); ?></td>
                <td><?php echo htmlspecialchars($sol['solicitante']); ?></td>
                <td><?php echo htmlspecialchars($sol['setor']); ?></td>
                <td><?php echo htmlspecialchars($sol['subsetor']); ?></td>
                <td><?php echo htmlspecialchars($sol['data']); ?></td>
                <td>
                    <!-- Botão para mostrar os produtos da solicitação -->
                    <button class="btn-verprodutos" onclick="mostrarProdutos(
                        <?php echo $sol['id_solicitacao']; ?>, 
                        '<?php echo addslashes($sol['solicitante']); ?>', 
                        '<?php echo addslashes($sol['setor']); ?>', 
                        '<?php echo addslashes($sol['subsetor']); ?>', 
                        '<?php echo addslashes($sol['status']); ?>', 
                        '<?php echo $sol['data']; ?>'
                    )">
                        Produtos
                    </button>

                </td>


            </tr>
        <?php endforeach;?>
    </tbody>
</table>


<!-- Script JavaScript -->
<script>
// Função para buscar e exibir os produtos de uma solicitação
const base_url = '<?php echo $base_url; ?>';

function mostrarProdutos(idSolicitacao, nomeSolicitante, setor, subsetor, status, dataSolicitacao) {
    document.getElementById('solicitanteNome').innerText = nomeSolicitante;
    document.getElementById('idSolicitacaoInput').value = idSolicitacao;
    document.getElementById('destino').value = setor + ' - ' + subsetor; // Atualizando destino com setor e subsetor
    document.getElementById('dataSolicitacao').innerText = dataSolicitacao;
    document.getElementById('destinoTexto').innerText = setor;// Atualizando o texto visível
    document.getElementById('subsetor').innerText = subsetor; // Atualizando o texto visível

    document.getElementById('id_solicitacao').innerText = idSolicitacao; // Atualizando o texto visível


    fetch(`${base_url}solicitacao/getprodutos/${idSolicitacao}`)
        .then(response => response.json())
        .then(produtos => {
            const container = document.getElementById('produtosContainer');
            container.innerHTML = '';

            if (produtos.length > 0) {
                let tabela = '<table border="1" cellspacing="0" cellpadding="10"><thead><tr><th>Código Produto</th><th>Nome do Produto</th><th>Quantidade</th><th>Saldo atual</th><th>Depósito</th><th>Local</th><th>Ação</th></tr></thead><tbody>';
                
                produtos.forEach(produto => {
                    tabela += `<tr>
                        <td>${produto.id_produto}</td>
                        <td>${produto.nome_produto}</td>
                        <td>
                            <input type="number" id="quantidade_${produto.id_produto}" value="${produto.quantidade}" min="1" oninput="atualizarQuantidade(${produto.id_produto}, this.value)" style="width: 80px;">
                            <input type="hidden" name="produtos[${produto.id_produto}][id]" value="${produto.id_produto}">
                            <input type="hidden" name="produtos[${produto.id_produto}][quantidade]" id="inputQuantidade_${produto.id_produto}" value="${produto.quantidade}">
                            <input type="hidden" name="produtos[${produto.id_produto}][saldo]" value="${produto.saldo}">
                            <input type="hidden" name="produtos[${produto.id_produto}][custo]" value="${produto.custo}">
                        </td>
                        <td>${produto.saldo}</td>
                        <td>${produto.local}</td>
                        <td>${produto.sublocal}</td>

                        <td><button type="button" onclick="alterarQuantidade(${idSolicitacao}, ${produto.id_produto}, ${produto.saldo})" ${status === 'finalizado' ? 'disabled' : ''}>Atualizar Quantidade</button></td>
                    </tr>`;
                });
                
                tabela += '</tbody></table>';
                container.innerHTML = tabela;
            } else {
                container.innerHTML = 'Nenhum produto encontrado para esta solicitação.';
            }

            document.getElementById('modalProdutos').style.display = 'block';

            const finalizarButton = document.querySelector('button[type="submit"]');
            finalizarButton.style.display = status === 'finalizado' ? 'none' : 'inline-block';
        })
        .catch(error => {
            console.error('Erro ao buscar produtos:', error);
            alert('Erro ao buscar produtos.');
        });
}



// Função para atualizar a quantidade no input hidden
function atualizarQuantidade(idProduto, quantidade) {
    document.getElementById(`inputQuantidade_${idProduto}`).value = quantidade;
}

// Função para alterar a quantidade de um produto (com validação de saldo)
function alterarQuantidade(idSolicitacao, idProduto, saldoProduto) {
    const quantidade = document.getElementById(`quantidade_${idProduto}`).value;

    if (quantidade < 1) {
        alert('A quantidade deve ser maior que zero.');
        return;
    }

    if (quantidade > saldoProduto) {
        alert('A quantidade não pode ser maior que o saldo disponível.');
        fecharModal();
        mostrarProdutos(idSolicitacao, document.getElementById('solicitanteNome').innerText);
        return;
    }

    fetch(`${base_url}solicitacao/alterarQuantidade/${idSolicitacao}/${idProduto}/${quantidade}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Quantidade atualizada com sucesso!');
            } else {
                alert('Erro ao atualizar quantidade.');
            }
        })
        .catch(error => {
            console.error('Erro ao alterar quantidade:', error);
            alert('Erro ao alterar quantidade.');
        });
}

// Função para fechar o modal
function fecharModal() {
    document.getElementById('modalProdutos').style.display = 'none';
}

// Função para imprimir o conteúdo do modal sem a coluna "Ação" e os botões
function imprimirModal() {
    const modalContent = document.getElementById('modalProdutos').cloneNode(true);

    // Remove a coluna "Ação" da tabela
    const tabela = modalContent.querySelector('table');
    if (tabela) {
        // Remove o cabeçalho "Ação"
        const ths = tabela.querySelectorAll('thead tr th');
        ths.forEach((th, index) => {
            if (th.textContent === 'Ação') {
                tabela.querySelectorAll('tr').forEach(tr => {
                    tr.deleteCell(index);
                });
            }
        });
    }

    // Remove os botões de ação
    const botoes = modalContent.querySelectorAll('.modal-botoes, button');
    botoes.forEach(botao => botao.remove());

    // Gera o conteúdo de impressão
    const janelaImpressao = window.open('', '', 'width=800, height=600');
    janelaImpressao.document.write('<html><head><title>Imprimir Solicitação</title></head><body>');
    janelaImpressao.document.write(modalContent.innerHTML);
    janelaImpressao.document.write('</body></html>');
    janelaImpressao.document.close();
    janelaImpressao.print();
    janelaImpressao.close();
}


</script>