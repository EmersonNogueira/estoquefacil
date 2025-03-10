<?php
	namespace controllers;

	class ProdutoController extends Controller{
	
		protected $base_url;
		
		//private $produtos; 
		public function __construct($view,$model){
			$this->checkAccess();
			parent::__construct($view,$model);
			if (session_status() == PHP_SESSION_NONE) {
				session_start();
			}
			

		}

		private function checkAccess() {
			// Verifica se a sessão está iniciada
			if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
				// Usuário não está logado, redireciona para a página de login
				header('Location: ' . $this->base_url . 'Login/login');
				exit;
			}
			//header('Location: ' . $this->base_url . 'Login/login');

			// Verifica o tipo de usuário
			if (isset($_SESSION['tipo'])) {
				if ($_SESSION['tipo'] === 'admin' || $_SESSION['tipo']=='infra') {
					// Se o usuário for admin, permite o acesso
					return;
				} elseif ($_SESSION['tipo'] === 'solicitante') {
					// Se o usuário for solicitante, permite o acesso à rota de solicitação de produto
					if ($_SERVER['REQUEST_URI'] ===  $this->base_url.'Solicitacao/solicitacaoproduto') {
						return; // Permite o acesso à rota de solicitante
					} else {
						// Redireciona para a página de solicitação de produtos se tentar acessar outra rota
						header('Location:'. $this->base_url.'Solicitacao/solicitacaoproduto');
						exit;
					}
				} else {
					// Se não for admin nem solicitante, exibe uma mensagem de acesso negado
					die('Acesso negado.');
				}
			} else {

				// Caso o tipo de usuário não esteja definido, exibe uma mensagem de erro
				die('Tipo de usuário não definido.');
			}
		}

		// Carrega a lista de produtos
		public function index(){
			try {
				$produtos = $this->model->reader();
				$this->view->render('produto.php', ['produtos' => $produtos]);
			} catch (\Exception $e) {
				error_log("Erro ao carregar produtos: " . $e->getMessage());
				die("Erro ao carregar produtos: " . $e->getMessage());
			}
		}
		

		// Renderiza a página de adicionar produto
		public function vaddproduto(){
			$this->view->render('vaddproduto.php');
		}

		// Método para adicionar um produto
		public function maddproduto(){
			$postData = $_POST;
			$result = $this->model->maddproduto($postData);
			if ($result) {
				$_SESSION['mensagem_confirmacao'] = "Produto cadastrado com sucesso";

				header("Location: {$this->base_url}Produto/");
				exit;
			} else {
				echo "Erro ao cadastrar o produto.";
			}
		}

		// Renderiza a página de edição de produto
		public function mveditar(){
			$id = $_POST['id_produto'];
			$produto = $this->model->buscarProdutoPorId($id);
			$this->view->render('editarproduto.php', ['produto' => $produto]);
		}

		// Renderiza a página de registro do produto
		public function mvregistro(){
			$id = $_POST['id_produto']; // Correção: o ID deve ser passado corretamente
			$produto = $this->model->buscarProdutoPorId($id); // Correção: passando o ID para a função
			$this->view->render('registrarproduto.php', ['produto' => $produto]);
		}

		// Atualiza o produto
		public function matualizar(){
			$postData = $_POST;
			$result = $this->model->matualizar($postData); // Executa a atualização no model
			if ($result) {
				header("Location: {$this->base_url}Produto/"); // Redireciona para a página de listagem de produtos
				exit;
			} else {
				echo "Erro ao atualizar o produto.";
			}
		}


		public function atualizarSaldo(){
			$postData = $_POST;
			$result = $this->model->atualizarSaldo($postData); // Executa a atualização no model
			if ($result) {
				exit;
			} else {
				echo "Erro ao atualizar o produto.";
			}			
		}

	}

?>
