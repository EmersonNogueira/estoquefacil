<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produtos</title>
    <link rel="stylesheet" href="styles.css"> <!-- Incluindo o CSS existente -->
</head>
<body>
    <div class="add-product-container">
        <h1>Cadastro de Novo Produto</h1>
        <form action="<?php echo $base_url; ?>produto/maddproduto" method="post">
            <div class="form-group">
                <label for="nome">DESCRIÇÃO DO ITEM - REF - TAM - COR MARCA ou FABRICANTE:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="categoria">Categoria:</label>
                <select id="categoria" name="categoria" required>
                    <option value="" disabled selected>Selecione uma Categoria</option>
                    <option value="expediente">Expediente</option>
                    <option value="manutenção">Manutenção</option>
                    <option value="gestao rh">Gestão RH</option>
                    <option value="informática">Informática</option>
                    <option value="limpeza">Limpeza</option>
                    <option value="copa">Copa</option>
                </select>
            </div>


            
            <div class="form-group">
                <label for="local">Depósito:</label>
                <select id="local" name="local" required>
                    <option value="" disabled selected>Selecione um Depósito</option>
                    <option value="TI">TI</option>
                    <option value="Zelad.">Zelad.</option>
                    <option value="Infra.">Infra.</option>
                    <option value="Almox.">Almox.</option>
                </select>
            </div>




            <div class="form-group">
                <label for="sublocal">Local:</label>
                <input type="text" id="sublocal" name="sublocal" required>
            </div>

            <div class="form-group">
                <label for="situacao">Situação:</label>
                <select id="situacao" name = "situacao" required>
                <option value="" disabled selected>Selecione a Situação</option>
                    <option value="NOVO">NOVO</option>
                    <option value="USADO">USADO</option>
                </select>
            </div>

            <div class="form-group">
                <label for="saldo">Saldo:</label>
                <input type="number" id="saldo" name="saldo" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="custo">Custo:</label>
                <input type="number" id="custo" name="custo" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="situacao">Visivel:</label>
                <select id="visivel" name = "visivel" required>
                <option value="" disabled selected>Visivel para solicitante ? </option>
                    <option value="1">SIM</option>
                    <option value="0">NÃO</option>
                </select>
            </div>
            <button type="submit" class="btn-submit">Cadastrar Produto</button>
        </form>
    </div>
</body>
</html>
