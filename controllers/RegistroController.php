<?php
	namespace controllers;

	class RegistroController extends Controller{
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



		public function index(){
			try {
				$registros = $this->model->reader();
				$this->view->render('registro.php', ['registros' => $registros]);
			} catch (\Exception $e) {
				error_log("Erro ao carregar produtos: " . $e->getMessage());
				die("Erro ao carregar produtos: " . $e->getMessage());
			}
		}

        public function sintetico(){
			try {
				$registros = $this->model->sintetico();
				$this->view->render('registrosintetico.php', ['registros' => $registros]);
			} catch (\Exception $e) {
				error_log("Erro ao carregar produtos: " . $e->getMessage());
				die("Erro ao carregar produtos: " . $e->getMessage());
			}
		}

        public function sinteticoentrada(){
			try {
				$registros = $this->model->sinteticoentrada();
				$this->view->render('registrosinteticoentrada.php', ['registros' => $registros]);
			} catch (\Exception $e) {
				error_log("Erro ao carregar produtos: " . $e->getMessage());
				die("Erro ao carregar produtos: " . $e->getMessage());
			}
		}


        
		public function entrada(){
			try {
				$registros = $this->model->entrada();
				$this->view->render('registroentrada.php', ['registros' => $registros]);
			} catch (\Exception $e) {
				error_log("Erro ao carregar produtos: " . $e->getMessage());
				die("Erro ao carregar produtos: " . $e->getMessage());
			}
		}

        public function produtos() {
            $dados = $_POST;
        
            $id_solicitacao = $dados['id_solicitacao'];
            $numero_nota = null; 
            $tipo = 'Solicitação';
            $receptor = $dados['receptor'];

            $data = isset($postData['data']) && !empty($postData['data']) 
            ? $postData['data'] 
            : date('Y-m-d H:i:s');

          
            foreach ($dados['produtos'] as $produto) {
                $id = $produto['id'];
                $quantidade = $produto['quantidade'];
                $custo = $produto['custo'];
                $saldo_final = $produto['saldo'] - $quantidade; 
        
                $this->model->novoregistro($tipo, $quantidade, $id, $id_solicitacao, $numero_nota, $custo, $saldo_final,$data);
            }
        
            $this->model->setstatus($id_solicitacao);
            $this->model->setreceptor($id_solicitacao,$receptor); 
            $_SESSION['mensagem_confirmacao'] = "Registro(s) de saída efetuado com sucesso";
            header('Location:'. $this->base_url.'Registro/index');

        }

        public function mvdevolucao(){ //ALTERAR OS DADOS DOS REGISTRO E CRIAR UM NOVO REGISTRO
            $dados = $_POST;
            $this->model->devolucao($dados['id_registro'],$dados['quantidadeDevolvida']); //ATUALIZAR REGISTROS
            $saldo = $this->getSaldoProd($dados['id_produto']);
            $tipo ="devolucao";
            $quantidade = $dados['quantidadeDevolvida'];
            $id = $dados['id_produto'];
            $id_solicitacao = $dados['id_solicitacao'];
            $numero_nota = null;
            $custo = null;
            $saldo_final = $saldo + $quantidade;
            $this->model->novoregistro($tipo, $quantidade, $id, $id_solicitacao, $numero_nota, $custo, $saldo_final);

            $_SESSION['mensagem_confirmacao'] = "Registro(s) de devolução efetuado com sucesso";
            header('Location:'. $this->base_url.'Registro/entrada');
        }

        public function getSaldoProd($id_produto){
            return $this->model->getSaldoProd($id_produto); // Retorne o saldo diretamente
        }

        
        
        public function novoregistro(){
            $postData = $_POST;
            if (isset($postData['tipo'])) {
                $tipo = $postData['tipo'];
                $id = $postData['id_produto'];
                $quantidade = $postData['quantidade'];
                $saldo_atual = $postData['saldo_atual'];
                $custo = $postData['custo_atual'];
                $custo_novo = $postData['custo_novo']; 
                $destino = $postData['destino'];
                $numero_nota = $postData['numero_nota'];
                
                $data = isset($postData['data']) && !empty($postData['data']) 
                    ? $postData['data'] 
                    : date('Y-m-d');

                // Dependendo do tipo de operação, realiza uma ação específica
                
                switch ($tipo) {
                    case 'Compra':
                        $saldo_final = $saldo_atual + $quantidade;

                        if ($saldo_atual == 0) {
                            // Se o saldo atual for zero, o novo custo é apenas o custo da nova compra
                            $custo = $custo_novo;
                        } else {
                            // Se já houver estoque, calcular o novo custo médio ponderado
                            $valor_atual = $custo * $saldo_atual;
                            $valor_novo = $custo_novo * $quantidade;
                            
                            if ($saldo_final > 0) {
                                $custo = ($valor_atual + $valor_novo) / $saldo_final;
                            } else {
                                $custo = $custo_novo; // Evita divisão por zero
                            }
                        }

                        $_SESSION['mensagem_confirmacao'] = "Registro de compra efetuado com sucesso";
                        break;
        
                    case 'Solicitação':
                        // Lógica para a operação de venda
                        $saldo_final = $saldo_atual - $quantidade;
                        break;
        
                    case 'Devolução':
                        // Lógica para a operação de devolução
                        $saldo_final = $saldo_atual + $quantidade;
                        break;
        
                    case 'Ajuste Positivo':
                        // Lógica para a operação de devolução

                        $saldo_final = $saldo_atual + $quantidade;
                        $_SESSION['mensagem_confirmacao'] = "Registro de ajuste positivo efetuado com sucesso";

                        break;

                    case 'Ajuste Negativo':
                        // Lógica para a operação de devolução
                        $saldo_final = $saldo_atual - $quantidade;
                        $_SESSION['mensagem_confirmacao'] = "Registro de ajuste negetivo efetuado com sucesso";

                        break;
                    default:
                        // Se o tipo de operação não for reconhecido
                        echo "Operação inválida!";
                        return;
                }
            }else{
                // Se o campo 'tipo_operacao' não estiver presente
                echo "Tipo de operação não especificado!";
                return;
            }



            $id_solicitacao = null;
            $this->model->novoregistro($tipo,$quantidade,$id,$id_solicitacao, $numero_nota,$custo_novo,$saldo_final,$data);
            $this->model->setcustoprod($id,$custo);
            header('Location:'. $this->base_url.'Produto/');

        
        }


    }


?>


    