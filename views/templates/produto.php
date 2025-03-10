<div class="container">
    <h1>Produtos</h1>
    <?php if (isset($_SESSION['mensagem_confirmacao'])): ?>
        <script type="text/javascript">
            window.onload = function() {
                alert('<?php echo htmlspecialchars($_SESSION['mensagem_confirmacao']); ?>');
            };
        </script>
        <?php unset($_SESSION['mensagem_confirmacao']); ?>
    <?php endif; ?>

    <div class="top-bar">
        <form method="GET" action="<?php echo $base_url; ?>Produto/" id="filter-form" class="search-form">
            <div class="filters">
                <input type="search" name="search" id="search-input" placeholder="Descrição do item" class="search-input" oninput="filtrarProdutos()">
                
                <label for="local">Depósito:</label>
                <select name="local" id="local" class="filter" onchange="filtrarProdutos()">
                    <option value="" <?php echo (!isset($_GET['local']) || $_GET['local'] === '') ? 'selected' : ''; ?>>Todos</option>
                    <?php
                    $locais = array_unique(array_column($produtos, 'local'));
                    foreach ($locais as $localOption) {
                        $localOption = htmlspecialchars($localOption);
                        $selected = (isset($_GET['local']) && $_GET['local'] === $localOption) ? 'selected' : '';
                        echo "<option value=\"$localOption\" $selected>$localOption</option>";
                    }
                    ?>
                </select>

                <label for="categoria">Categoria:</label>
                <select name="categoria" id="categoria" class="filter" onchange="filtrarProdutos()">
                    <option value="" <?php echo (!isset($_GET['categoria']) || $_GET['categoria'] === '') ? 'selected' : ''; ?>>Todas</option>
                    <?php
                    $categorias = array_unique(array_map(function($produto) {
                        return !empty($produto['categoria']) ? htmlspecialchars($produto['categoria']) : 'Sem Categoria';
                    }, $produtos));

                    foreach ($categorias as $categoriaOption) {
                        $selected = (isset($_GET['categoria']) && $_GET['categoria'] === $categoriaOption) ? 'selected' : '';
                        echo "<option value=\"$categoriaOption\" $selected>$categoriaOption</option>";
                    }
                    ?>
                </select>
            </div>
        </form>
    </div>
    <div class="total-cost">
        <strong>Valor Total:</strong> R$<span id="custo-total">0.00</span>
    </div>



    <div class="card-container" id="produtos-container">
        <?php
        $localFiltro = isset($_GET['local']) ? htmlspecialchars($_GET['local']) : '';
        $categoriaFiltro = isset($_GET['categoria']) ? htmlspecialchars($_GET['categoria']) : '';

        $produtosFiltrados = array_filter($produtos, function ($produto) use ($localFiltro, $categoriaFiltro) {
            $produtoLocal = htmlspecialchars($produto['local']);
            $produtoCategoria = !empty($produto['categoria']) ? htmlspecialchars($produto['categoria']) : 'Sem Categoria';

            $localValido = empty($localFiltro) || $produtoLocal === $localFiltro;
            $categoriaValida = empty($categoriaFiltro) || $produtoCategoria === $categoriaFiltro;

            return $localValido && $categoriaValida;
        });
        ?>

        <?php if (!empty($produtosFiltrados)): ?>
            <?php foreach ($produtosFiltrados as $row): ?>
                <?php
                    $id = htmlspecialchars($row['id_produto']);
                    $categoria = !empty($row['categoria']) ? htmlspecialchars($row['categoria']) : 'Sem Categoria';
                    $nome = htmlspecialchars($row['nome']);
                    $local = htmlspecialchars($row['local']);
                    $sublocal = htmlspecialchars($row['sublocal']);
                    $situacao = htmlspecialchars($row['situacao']);
                    $saldo = htmlspecialchars($row['saldo']);
                    $custo = htmlspecialchars($row['custo']);
                    $visivel = htmlspecialchars($row['visivel']);
                ?>
                <div class="card produto-item" 
                     data-nome="<?php echo strtolower($nome); ?>"
                     data-local="<?php echo strtolower($local); ?>"
                     data-categoria="<?php echo strtolower($categoria); ?>"
                     onclick="toggleButtons(this)">
                    <h2><?php echo $nome; ?></h2>
                    <p><strong>Código:</strong> <?php echo $id; ?></p>
                    <p><strong>Categoria:</strong> <?php echo $categoria; ?></p>
                    <p><strong>Depósito:</strong> <?php echo $local; ?></p>
                    <p><strong>Local:</strong> <?php echo $sublocal; ?></p>
                    <p><strong>Situação:</strong> <?php echo $situacao; ?></p>
                    <p><strong>Saldo Final:</strong> <?php echo $saldo; ?></p>
                    <p><strong>Valor unitário:</strong> R$<?php echo number_format($custo, 2, ',', '.'); ?></p>
                    <p><strong>Visível:</strong> <?php echo $visivel; ?></p>
                    <div class="card-buttons" style="display: none;">
                        <form method="POST" action="<?php echo $base_url; ?>Produto/mvregistro">
                            <input type="hidden" name="id_produto" value="<?php echo $id; ?>">
                            <button type="submit" class="btn-register">COMPRA / AJUSTES</button>
                        </form> 
                        <form method="POST" action="<?php echo $base_url; ?>Produto/mveditar">
                            <input type="hidden" name="id_produto" value="<?php echo $id; ?>">
                            <button type="submit" class="btn-edit">EDITAR PRODUTO</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-products">
                Nenhum produto encontrado para os filtros selecionados.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function normalizeString(str) {
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

function calcularCustoTotal() {
    let custoTotal = 0;

    // Pega todos os cards de produtos visíveis
    let produtosVisiveis = document.querySelectorAll('.produto-item');

    produtosVisiveis.forEach(produto => {
        if (produto.style.display !== "none") {
            let saldo = parseFloat(produto.querySelector("p:nth-of-type(6)").textContent.replace("Saldo Final: ", "")) || 0;
            let custo = parseFloat(produto.querySelector("p:nth-of-type(7)").textContent.replace("Valor unitário: R$", "").replace(/\./g, "").replace(",", ".")) || 0;
            custoTotal += saldo * custo;
        }
    });

    document.getElementById("custo-total").textContent = custoTotal.toLocaleString("pt-BR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function filtrarProdutos() {
    let search = normalizeString(document.getElementById('search-input').value);
    let local = normalizeString(document.getElementById('local').value);
    let categoria = normalizeString(document.getElementById('categoria').value);

    let produtos = document.querySelectorAll('.produto-item');

    produtos.forEach(produto => {
        let nome = normalizeString(produto.getAttribute('data-nome'));
        let produtoLocal = normalizeString(produto.getAttribute('data-local'));
        let produtoCategoria = normalizeString(produto.getAttribute('data-categoria'));

        let nomeMatch = nome.includes(search);
        let localMatch = local === "" || produtoLocal === local;
        let categoriaMatch = categoria === "" || produtoCategoria === categoria;

        if (nomeMatch && localMatch && categoriaMatch) {
            produto.style.display = "block";
        } else {
            produto.style.display = "none";
        }
    });

    // Chama o cálculo do custo total após filtrar os produtos
    calcularCustoTotal();
}

function toggleButtons(card) {
    document.querySelectorAll('.card-buttons').forEach(buttons => {
        buttons.style.display = 'none';
    });

    let buttons = card.querySelector('.card-buttons');
    if (buttons) {
        buttons.style.display = 'block';
    }
}

// Chama a função de cálculo inicial ao carregar a página
window.onload = function () {
    calcularCustoTotal();
};
</script>
