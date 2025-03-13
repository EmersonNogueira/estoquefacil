<div class="add-product-container">
    <h1>Editar Produto</h1>
    <form action="<?php echo $base_url; ?>Produto/matualizar" method="post">

        <!-- Campo oculto para ID -->
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($produto['id_produto']); ?>">

        <div class="form-group">
            <label for="nome">Descrição do item:</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
        </div>
        <div class="form-group">
        <label for="categoria">Categoria:</label>
            <select id="categoria" name="categoria" required>
                <option value="" disabled>Selecione uma Categoria</option>
                <option value="expediente" <?php echo ($produto['categoria'] == 'Expediente') ? 'selected' : ''; ?>>Expediente</option>
                <option value="manutenção" <?php echo ($produto['categoria'] == 'Manutenção') ? 'selected' : ''; ?>>Manutenção</option>
                <option value="gestao rh" <?php echo ($produto['categoria'] == 'Gestão RH') ? 'selected' : ''; ?>>Gestão RH</option>
                <option value="informática" <?php echo ($produto['categoria'] == 'Informática') ? 'selected' : ''; ?>>Informática</option>
                <option value="limpeza" <?php echo ($produto['categoria'] == 'Limpeza') ? 'selected' : ''; ?>>Limpeza</option>
                <option value="copa" <?php echo ($produto['categoria'] == 'Copa') ? 'selected' : ''; ?>>Copa</option>
            </select>
        </div>

        <div class="form-group">
            <label for="local">Local:</label>
            <select id="local" name="local" required>
                <option value="TI" <?php echo ($produto['local'] === 'TI') ? 'selected' : ''; ?>>TI</option>
                <option value="Zeladoria" <?php echo ($produto['local'] === 'Zeladoria') ? 'selected' : ''; ?>>Zeladoria</option>
                <option value="Infra" <?php echo ($produto['local'] === 'Infra') ? 'selected' : ''; ?>>Infra</option>
                <option value="Almoxarifado" <?php echo ($produto['local'] === 'Almoxarifado') ? 'selected' : ''; ?>>Almoxarifado</option>
                <option value="<?php echo htmlspecialchars($produto['local']); ?>" selected>
                    <?php echo htmlspecialchars($produto['local']); ?>
                </option>
            </select>
        </div>
        <div class="form-group">
            <label for="sublocal">Sublocal:</label>
            <input type="text" id="sublocal" name="sublocal" value="<?php echo htmlspecialchars($produto['sublocal']); ?>" required>
        </div>
        <div class="form-group">
            <label for="situacao">Situação:</label>
            <select id="situacao" name="situacao" required>
                <option value="">Selecione-</option>
                <option value="NOVO" <?php echo ($produto['situacao'] == 'NOVO') ? 'selected' : ''; ?>>NOVO</option>
                <option value="USADO" <?php echo ($produto['situacao'] == 'USADO') ? 'selected' : ''; ?>>USADO</option>
            </select>
        </div>
        <div class="form-group">
            <label for="custo">Custo:</label>
            <input type="text" id="custo" name="custo" step="0.01" value="<?php echo htmlspecialchars($produto['custo']); ?>" required>
        </div>
        <div class="form-group">
            <label for="visivel">Visível:</label>
            <select id="visivel" name="visivel" required>
                <option value="1" <?php echo ($produto['visivel'] == 1) ? 'selected' : ''; ?>>Sim</option>
                <option value="0" <?php echo ($produto['visivel'] == 0) ? 'selected' : ''; ?>>Não</option>
            </select>
        </div>
        <button type="submit" class="btn-submit">Salvar Alterações</button>
    </form>
</div>

<script>

document.getElementById('custo').addEventListener('input', function() {
    this.value = this.value.replace(',', '.'); // Substitui vírgula por ponto em tempo real
});

document.querySelector('form').addEventListener('submit', function() {
    let custoInput = document.getElementById('custo');
    custoInput.value = custoInput.value.replace(',', '.'); // Garante a substituição antes do envio
});
</script>