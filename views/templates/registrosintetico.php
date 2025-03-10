<style>
table {
    table-layout: fixed;
    width: 100%;
}

th, td {
    width: calc(100% / 12); /* Divide o espaço igualmente entre 12 meses */
    text-align: center;
}


</style>
<div class="filters">
  <!-- Filtro por Tipo -->
  <label for="tipo">Tipo:</label>
  <select id="tipo" class="filter">
    <option value="">Todos</option>
    <?php
      $tipos = array_unique(array_column($registros, 'tipo'));
      sort($tipos);
      foreach ($tipos as $tipo) {
          if (!empty($tipo)) {
              $tipo = htmlspecialchars($tipo);
              echo "<option value=\"$tipo\">$tipo</option>";
          }
      }
    ?>
  </select>
  <!-- Filtro por Setor -->
  <label for="setor">Setor:</label>
  <select id="setor" class="filter">
    <option value="">Todos</option>
    <?php
        // Cria uma lista única de setores e os exibe como opções
        $setores = array_unique(array_column($registros, 'setor'));
        foreach ($setores as $set) {
            if (empty($set)) {
                $set = 'Ajuste Negativo'; // Caso o setor seja vazio ou null, substitui por "Ajuste Negativo"
            } else {
                $set = htmlspecialchars($set);
            }
            echo "<option value=\"$set\">$set</option>";
        }
    ?>
  </select>
    <!-- Filtro por Subsetor -->
    <label for="subsetor">Subsetor:</label>
    <select id="subsetor" class="filter">
        <option value="">Todos</option>
        <?php
            $subsetores = array_unique(array_column($registros, 'subsetor'));
            sort($subsetores);
            foreach ($subsetores as $subsetor) {
                if (!empty($subsetor)) {
                    $subsetor = htmlspecialchars($subsetor);
                    echo "<option value=\"$subsetor\">$subsetor</option>";
                }
            }
        ?>
    </select>


  <!-- Filtro por Produto -->
  <label for="produto">Produto:</label>
  <select id="produto" class="filter">
    <option value="">Todos</option>
    <?php
      $produtos = array_unique(array_column($registros, 'nome_produto'));
      sort($produtos);
      foreach ($produtos as $produto) {
          if (!empty($produto)) {
              $produto = htmlspecialchars($produto);
              echo "<option value=\"$produto\">$produto</option>";
          }
      }
    ?>
  </select>

  <!-- Filtro por Data Início -->
  <label for="dataInicio">Data Início:</label>
  <input type="date" id="dataInicio" class="filter">

  <!-- Filtro por Data Fim -->
  <label for="dataFim">Data Fim:</label>
  <input type="date" id="dataFim" class="filter" value="<?php echo date('Y-m-d'); ?>">

  <!-- Botão de Impressão -->
  <button onclick="imprimirTabela()">Imprimir Tabela</button>
</div>

<div id="resultados"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const setorFilter = document.getElementById('setor');
    const produtoFilter = document.getElementById('produto');
    const tipoFilter = document.getElementById('tipo');
    const dataInicioFilter = document.getElementById('dataInicio');
    const dataFimFilter = document.getElementById('dataFim');
    const resultadosContainer = document.getElementById('resultados');
    const registros = <?php echo json_encode($registros); ?>;
    const subsetorFilter = document.getElementById('subsetor');
    // Função para definir a data de início automaticamente
    function definirDataInicio() {
        const dataAtual = new Date();
        const mesAtual = dataAtual.getMonth(); // Mês atual (0 - Janeiro, 1 - Fevereiro, ..., 11 - Dezembro)
        const anoAtual = dataAtual.getFullYear();

        let mesInicio = mesAtual - 2; // Subtrai 2 meses para obter 3 meses atrás (margem de 3 meses)

        if (mesInicio < 0) {
            // Se o mês inicial for negativo (antes de janeiro), ajusta para o ano anterior
            mesInicio += 12;
        }

        // Define o primeiro dia do mês calculado
        const dataInicio = new Date(anoAtual, mesInicio, 1);
        
        // Formatar data no formato YYYY-MM-DD (compatível com o input date)
        const dataInicioFormatada = dataInicio.toISOString().split('T')[0];
        
        // Atribui a data de início ao campo de data
        dataInicioFilter.value = dataInicioFormatada;
    }

    // Chama a função para definir a data de início automaticamente
    definirDataInicio();


    function calcularCustoTotal() {
        const setorSelecionado = setorFilter.value;
        const produtoSelecionado = produtoFilter.value;
        const tipoSelecionado = tipoFilter.value;
        const subsetorSelecionado = subsetorFilter.value;

        let dataInicio = dataInicioFilter.value;
        let dataFim = dataFimFilter.value;


        if (dataInicio) {
            dataInicio = new Date(dataInicio);
            dataInicio.setUTCHours(0, 0, 0, 0);
        }

        if (dataFim) {
            dataFim = new Date(dataFim);
            dataFim.setUTCHours(23, 59, 59, 999);
        }

        // Agrupar registros por mês
        const registrosPorMes = {};
        registros.forEach(function(registro) {
            const dataRegistro = new Date(registro.data_registro);
            if ((!dataInicio || dataRegistro >= dataInicio) &&
                (!dataFim || dataRegistro <= dataFim)) {
                const nomeSetor = registro.setor === '' || registro.setor === null ? 'Ajuste Negativo' : registro.setor;
                
                if ((!setorSelecionado || nomeSetor === setorSelecionado) &&
                    (!subsetorSelecionado || registro.subsetor === subsetorSelecionado) &&
                    (!produtoSelecionado || registro.nome_produto === produtoSelecionado) &&
                    (!tipoSelecionado || registro.tipo === tipoSelecionado)) {
                    const mesAno = `${dataRegistro.getFullYear()}-${String(dataRegistro.getMonth() + 1).padStart(2, '0')}`;
                    if (!registrosPorMes[mesAno]) {
                        registrosPorMes[mesAno] = [];
                    }
                    registrosPorMes[mesAno].push(registro);
                }
            }
        });

        exibirTabelaPorMes(registrosPorMes);
    }

    function exibirTabelaPorMes(registrosPorMes) {
    resultadosContainer.innerHTML = '';

    if (Object.keys(registrosPorMes).length === 0) {
        resultadosContainer.innerHTML = '<p>Nenhum registro encontrado com os filtros selecionados.</p>';
        return;
    }

    // Criar a tabela de uma única vez
    const tabela = document.createElement('table');
    tabela.style.width = '100%';
    tabela.style.borderCollapse = 'collapse';
    tabela.border = '1';

    const thead = document.createElement('thead');
    const trHead = document.createElement('tr');

    // Colunas fixas
    const thSetor = document.createElement('th');
    thSetor.innerText = 'Setor';
    trHead.appendChild(thSetor);

    // Adiciona as colunas para cada mês
    const meses = Object.keys(registrosPorMes).sort();
    meses.forEach((mesAno) => {
        const [ano, mes] = mesAno.split('-');
        const nomeMes = new Date(ano, mes - 1).toLocaleString('pt-BR', { month: 'long' });
        const thItensTotal = document.createElement('th');
        thItensTotal.innerText = `Itens Total ${nomeMes}`;
        trHead.appendChild(thItensTotal);
        
        const thCustoTotal = document.createElement('th');
        thCustoTotal.innerText = `Custo Total ${nomeMes}`;
        trHead.appendChild(thCustoTotal);
    });

    thead.appendChild(trHead);
    tabela.appendChild(thead);

    const tbody = document.createElement('tbody');
    const setoresTotais = {};
    const itensTotalPorSetor = {};
    let custoTotalGeral = 0;

    // Agrupar dados por setor e mês
    meses.forEach((mesAno) => {
        registrosPorMes[mesAno].forEach(function(registro) {
            const nomeSetor = registro.setor === '' || registro.setor === null ? 'Ajuste Negativo' : registro.setor;
            const custoTotal = parseFloat(registro.custo) * parseFloat(registro.quantidade);
            const quantidadeItens = parseFloat(registro.quantidade);

            if (setoresTotais[nomeSetor]) {
                setoresTotais[nomeSetor] += custoTotal;
                itensTotalPorSetor[nomeSetor] += quantidadeItens;
            } else {
                setoresTotais[nomeSetor] = custoTotal;
                itensTotalPorSetor[nomeSetor] = quantidadeItens;
            }

            custoTotalGeral += custoTotal;
        });
    });

    // Criar as linhas para cada setor
    Object.keys(setoresTotais).forEach((setor) => {
        const tr = document.createElement('tr');
        
        const tdSetor = document.createElement('td');
        tdSetor.innerText = setor;
        tr.appendChild(tdSetor);

        meses.forEach((mesAno) => {
            const custoTotal = setoresTotais[setor] || 0;
            const itensTotal = itensTotalPorSetor[setor] || 0;

            const tdItensTotal = document.createElement('td');
            tdItensTotal.innerText = itensTotal.toFixed(2).replace('.', ',');

            const tdCustoTotal = document.createElement('td');
            tdCustoTotal.innerText = custoTotal.toFixed(2).replace('.', ',');

            tr.appendChild(tdItensTotal);
            tr.appendChild(tdCustoTotal);
        });

        tbody.appendChild(tr);
    });

    // Adiciona uma linha total
    const trTotalGeral = document.createElement('tr');
    const tdTotalSetor = document.createElement('td');
    tdTotalSetor.innerText = 'Total Geral';
    trTotalGeral.appendChild(tdTotalSetor);

    meses.forEach((mesAno) => {
        const totalItens = Object.values(itensTotalPorSetor).reduce((a, b) => a + b, 0);
        const totalCusto = Object.values(setoresTotais).reduce((a, b) => a + b, 0);

        const tdTotalItens = document.createElement('td');
        tdTotalItens.innerText = totalItens.toFixed(2).replace('.', ',');

        const tdTotalValor = document.createElement('td');
        tdTotalValor.innerText = totalCusto.toFixed(2).replace('.', ',');

        trTotalGeral.appendChild(tdTotalItens);
        trTotalGeral.appendChild(tdTotalValor);
    });

    tbody.appendChild(trTotalGeral);
    trTotalGeral.style.backgroundColor = '#f2f2f2';

    tabela.appendChild(tbody);

    resultadosContainer.appendChild(tabela);
}


    setorFilter.addEventListener('change', calcularCustoTotal);
    produtoFilter.addEventListener('change', calcularCustoTotal);
    tipoFilter.addEventListener('change', calcularCustoTotal);
    dataInicioFilter.addEventListener('change', calcularCustoTotal);
    dataFimFilter.addEventListener('change', calcularCustoTotal);
    subsetorFilter.addEventListener('change', calcularCustoTotal);


    calcularCustoTotal();

    window.imprimirTabela = function() {
        const tabelaConteudo = resultadosContainer.innerHTML;
        const setor = setorFilter.value || 'Todos';
        const produto = produtoFilter.value || 'Todos';
        const tipo = tipoFilter.value || 'Todos';
        const dataInicio = dataInicioFilter.value 
            ? dataInicioFilter.value.split('-').reverse().join('/')
            : 'Não definido';
        const dataFim = dataFimFilter.value
            ? dataFimFilter.value.split('-').reverse().join('/')
            : 'Não definido';

        const janelaImpressao = window.open('', '', 'height=600,width=800');
        janelaImpressao.document.write('<html><head><title>Imprimir Tabela</title>');
        janelaImpressao.document.write('<style>');
        janelaImpressao.document.write('table {width: 100%; border-collapse: collapse;} th, td {border: 1px solid black; padding: 8px; text-align: left;} th {background-color: #f2f2f2; font-weight: bold;} footer {position: fixed; bottom: 0; width: 100%; text-align: center; padding: 10px 0;} header {text-align: center; padding: 20px 0;}');
        janelaImpressao.document.write('</style>');
        janelaImpressao.document.write('</head><body>');

        janelaImpressao.document.write('<header>');
        janelaImpressao.document.write('<h1>Relatório de Totais por Setor</h1>');
        janelaImpressao.document.write('<p>Filtros Aplicados: Setor: ' + setor + ' | Produto: ' + produto + ' | Tipo: ' + tipo + ' | Data Início: ' + dataInicio + ' | Data Fim: ' + dataFim + '</p>');
        janelaImpressao.document.write('</header>');

        janelaImpressao.document.write(tabelaConteudo);

        janelaImpressao.document.write('<footer>');
        janelaImpressao.document.write('<p>Emitido em ' + new Date().toLocaleString('pt-BR') + '</p>');
        janelaImpressao.document.write('</footer>');

        janelaImpressao.document.write('</body></html>');
        janelaImpressao.document.close();
        janelaImpressao.print();
    }
});
</script>