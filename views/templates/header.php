<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php
 		// Detecta o ambiente (localhost ou hospedagem)
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
		// Ambiente local
			$base_url= '/estrutura_mvc_base/';
		} else {
		// Ambiente de produção
			$base_url = '/';
		}
    ?>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo $base_url; ?>styles.css?v=100.0">
    <title>ESTOQUE FÁCIL</title>
</head>
<body>
<!-- Cabeçalho -->
<header>
    <div class="header-content">
        <img src="<?php echo $base_url; ?>img/logo2.jpg" alt="Logo CCBJ" class="logo">
        <h1>Estoque Fácil</h1>
        <!-- Ícone do menu hambúrguer para mobile -->
        <div class="menu-toggle" id="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </div>

    <nav class="header-navbar" id="navbar">
        <ul class="main-nav">
            <?php if (isset($_SESSION['tipo']) && ($_SESSION['tipo'] === 'infra' || $_SESSION['tipo'] === 'admin')): ?>
                <li>
                    <span>Produtos</span>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo $base_url; ?>Produto">Listar Produtos</a></li>
                        <li><a href="<?php echo $base_url; ?>Produto/vaddproduto">Cadastrar novo Produto</a></li>
                    </ul>
                </li>
                <li>
                    <a>Registros</a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo $base_url; ?>Registro/index">Saída</a></li>
                        <li><a href="<?php echo $base_url; ?>Registro/entrada">Entrada</a></li>
                    </ul>
                </li>
                <li>
                    <span>Solicitações</span>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo $base_url; ?>Solicitacao/solicitacaoproduto">Nova Solicitação</a></li>
                        <li><a href="<?php echo $base_url; ?>Solicitacao/index">Solicitações em aberto</a></li>
                        <li><a href="<?php echo $base_url; ?>Solicitacao/solicitacaofinal">Solicitações Finalizadas</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <li><a href="<?php echo $base_url; ?>Login/novasenha" style="white-space: nowrap;">Nova Senha</a></li>
            <li><a href="<?php echo $base_url; ?>Login/logout" style="white-space: nowrap;">Logout</a></li>
        </ul>
    </nav>

</header>


<script>
    // Script para o menu hambúrguer
    document.getElementById('mobile-menu').addEventListener('click', function() {
        const navbar = document.getElementById('navbar');
        navbar.classList.toggle('active');
    });
</script>