<?php
	namespace models;

	class SolicitacaoModel extends Model
	{
		public function listarSolicitacoes() {
			try {
				// Consulta para retornar apenas as solicitações com status 'aguardando'
				$sql = "SELECT 
							s.id_solicitacao,
							u.nome AS usuario_criador,
							s.solicitante,
							s.setor,
							s.subsetor,
							DATE_FORMAT(s.data, '%d/%m/%Y') AS data,  -- Formata a data
							s.status 
						FROM 
							solicitacoes s
						JOIN 
							usuario u ON s.usuario_criador = u.id
						WHERE 
							s.status = 'Aguardando'  -- Filtra as solicitações com status 'aguardando'
						ORDER BY 
							s.data ASC";  // Ordena por data
		
				$stmt = $this->pdo->prepare($sql);
				$stmt->execute();
				
				// Retorna as solicitações como um array associativo
				return $stmt->fetchAll(\PDO::FETCH_ASSOC);
				
			} catch (\PDOException $e) {
				error_log("Erro ao listar solicitações: " . $e->getMessage());
				die("Erro ao listar solicitações: " . $e->getMessage());
			}
		}
		
		public function listarSolicitacoesfinal($offset = 0, $limit = 10) {
			try {
				// Consulta para retornar apenas as solicitações com status 'finalizado'
				$sql = "SELECT 
							s.id_solicitacao,
							u.nome AS usuario_criador,
							s.solicitante,
							s.setor,
							s.subsetor,
							DATE_FORMAT(s.data, '%d/%m/%Y') AS data,
							s.status,
							s.receptor,
							DATE_FORMAT((
								SELECT MIN(r.data_registro)
								FROM registros r
								WHERE r.id_solicitacao = s.id_solicitacao
								AND r.tipo = 'solicitacao'
							), '%d/%m/%Y') AS data_finalizacao
						FROM 
							solicitacoes s
						JOIN 
							usuario u ON s.usuario_criador = u.id
						WHERE 
							s.status = 'finalizado'
						ORDER BY (
							SELECT MIN(r.data_registro)
							FROM registros r
							WHERE r.id_solicitacao = s.id_solicitacao
							AND r.tipo = 'solicitacao'
						) DESC
						LIMIT :offset, :limit";  // Limita os resultados
		
				$stmt = $this->pdo->prepare($sql);
				$stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
				$stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
				$stmt->execute();
		
				// Retorna as solicitações como um array associativo
				return $stmt->fetchAll(\PDO::FETCH_ASSOC);
			} catch (\PDOException $e) {
				error_log("Erro ao listar solicitações: " . $e->getMessage());
				die("Erro ao listar solicitações: " . $e->getMessage());
			}
		}
		
		
		
		public function contarSolicitacoesfinal() {
			try {
				// Consulta para contar o total de solicitações 'finalizado'
				$sql = "SELECT COUNT(*) AS total FROM solicitacoes WHERE status = 'finalizado'";
				$stmt = $this->pdo->prepare($sql);
				$stmt->execute();
				$result = $stmt->fetch(\PDO::FETCH_ASSOC);
				return $result['total'];
			} catch (\PDOException $e) {
				error_log("Erro ao contar solicitações: " . $e->getMessage());
				die("Erro ao contar solicitações: " . $e->getMessage());
			}
		}
		

		
		
		

		public function produtosSol($id) {
			try {
				// Consulta para buscar os produtos associados à solicitação e os nomes dos produtos
				$sql = "SELECT sp.id_produto, p.nome AS nome_produto, sp.quantidade, p.saldo, p.custo, p.local, p.sublocal
						FROM solicitacao_produto sp
						INNER JOIN produtos p ON sp.id_produto = p.id_produto
						WHERE sp.id_solicitacao = :id_solicitacao";
						
				$stmt = $this->pdo->prepare($sql);
				$stmt->bindValue(':id_solicitacao', $id, \PDO::PARAM_INT);
				$stmt->execute();
		
				// Retorna os produtos como um array associativo, incluindo nome do produto
				$produtos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		
				return $produtos;
		
			} catch (\PDOException $e) {
				error_log("Erro ao buscar produtos: " . $e->getMessage());
				echo json_encode(['erro' => "Erro ao buscar produtos: " . $e->getMessage()]);
			}
		}


		public function alterarQuantidadeProduto($idSolicitacao, $idProduto, $novaQuantidade) {
			try {
				// Consulta para atualizar a quantidade de um produto na tabela solicitacao_produto
				$sql = "UPDATE solicitacao_produto 
						SET quantidade = :novaQuantidade 
						WHERE id_solicitacao = :idSolicitacao AND id_produto = :idProduto";
				
				// Preparação da consulta
				$stmt = $this->pdo->prepare($sql);
				
				// Bind dos parâmetros
				$stmt->bindValue(':novaQuantidade', $novaQuantidade, \PDO::PARAM_INT);
				$stmt->bindValue(':idSolicitacao', $idSolicitacao, \PDO::PARAM_INT);
				$stmt->bindValue(':idProduto', $idProduto, \PDO::PARAM_INT);
				
				// Execução da consulta
				$stmt->execute();
				
				// Verifica se a atualização foi bem-sucedida
				if ($stmt->rowCount() > 0) {
					return ['sucesso' => 'Quantidade atualizada com sucesso.'];
				} else {
					return ['erro' => 'Nenhuma alteração foi realizada.'];
				}
				
			} catch (\PDOException $e) {
				error_log("Erro ao atualizar quantidade do produto: " . $e->getMessage());
				return ['erro' => 'Erro ao atualizar quantidade: ' . $e->getMessage()];
			}
		}		

		public function reader() {
			try {
				// Verifica se o parâmetro de busca está presente na URL
				$search = isset($_GET['search']) ? $_GET['search'] : '';
		
				// Inicia a base da consulta SQL
				$sqlStr = "SELECT id_produto, nome, saldo, custo, local 
						   FROM produtos ";
		
				// Adiciona a condição "visivel = 1" apenas se o usuário não for ADMIN ou INFRA
				if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'infra')) {
					$sqlStr .= "WHERE visivel = 1 AND saldo > 0";
				} else {
					// Retira "visivel = 1" mas mantém "saldo > 0" para todos os usuários
					$sqlStr .= "WHERE saldo > 0";
				}
		
				// Adiciona o filtro de busca, se houver
				if (!empty($search)) {
					$sqlStr .= " AND nome LIKE :search";
				}
		
				// Adiciona a ordenação
				$sqlStr .= " ORDER BY local ASC";
		
				// Prepara a consulta SQL
				$sql = $this->pdo->prepare($sqlStr);
		
				// Se houver um parâmetro de busca, vincula o valor
				if (!empty($search)) {
					$sql->bindValue(':search', '%' . $search . '%');
				}
		
				// Executa a consulta
				$sql->execute();
				$resultados = $sql->fetchAll(\PDO::FETCH_ASSOC);
				return $resultados;
		
			} catch (\PDOException $e) {
				error_log("Erro na consulta: " . $e->getMessage());
				die("Erro na consulta: " . $e->getMessage());
			}
		}
		
		



        public function getProdutoById($id) {
            try {
                $sql = $this->pdo->prepare("SELECT nome FROM produtos WHERE id_produto = :id");
                $sql->bindValue(':id', $id);
                $sql->execute();
                return $sql->fetch(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log("Erro na consulta: " . $e->getMessage());
                return false;
            }
        }




		// Supondo que você já tenha uma conexão com o banco de dados ($this->pdo)
		public function inserirSolicitacao($dados) {
			try {
				$this->pdo->beginTransaction();
		
				// Inserir na tabela `solicitacoes`
				$sqlSolicitacao = "INSERT INTO solicitacoes (usuario_criador, solicitante, setor, subsetor, data, status) 
								VALUES (:usuario_criador, :solicitante, :setor, :subsetor, NOW(), 'Aguardando')";
				$stmtSolicitacao = $this->pdo->prepare($sqlSolicitacao);
				$stmtSolicitacao->bindValue(':usuario_criador', $dados['usuario_id']);
				$stmtSolicitacao->bindValue(':solicitante', $dados['solicitante']);
				$stmtSolicitacao->bindValue(':setor', $dados['setor']);
				$stmtSolicitacao->bindValue(':subsetor', $dados['subsetor']);
				$stmtSolicitacao->execute();
		
				$idSolicitacao = $this->pdo->lastInsertId();
		
				// Inserir na tabela `produtos_solicitacao`
				$sqlProdutoSolicitacao = "INSERT INTO solicitacao_produto (id_solicitacao, id_produto, quantidade) 
										VALUES (:id_solicitacao, :id_produto, :quantidade)";
				$stmtProdutoSolicitacao = $this->pdo->prepare($sqlProdutoSolicitacao);
		
				foreach ($dados['produtos'] as $produto) {
					$stmtProdutoSolicitacao->bindValue(':id_solicitacao', $idSolicitacao);
					$stmtProdutoSolicitacao->bindValue(':id_produto', $produto['id_produto']);
					$stmtProdutoSolicitacao->bindValue(':quantidade', $produto['quantidade']);
					$stmtProdutoSolicitacao->execute();
				}
		
				$this->pdo->commit();
		
				// Define a mensagem de confirmação após sucesso total
				$this->setMensagemConfirmacao("Solicitação criada com sucesso, procure o setor de infraestrutura para pegar seus produtos.");
		
			} catch (\PDOException $e) {
				$this->pdo->rollBack();
				error_log("Erro ao inserir solicitação: " . $e->getMessage());
				die("Erro ao inserir solicitação: " . $e->getMessage());
			}
		}
		
		// Função helper para definir a mensagem de confirmação
		private function setMensagemConfirmacao($mensagem) {
			$_SESSION['mensagem_confirmacao'] = $mensagem;
		}
		
		public function getSaldoProduto($id) {
			try {
				$sql = "SELECT saldo FROM produtos WHERE id = :id";
				$stmt = $this->pdo->prepare($sql);
				$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
				$stmt->execute();
				echo '';
				if ($stmt->rowCount() > 0) {
					$resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
					return $resultado['saldo'];
				} else {
					return 'err1'; // Produto não encontrado
				}
			} catch (\PDOException $e) {
				error_log("Erro ao obter saldo do produto: " . $e->getMessage());
				return 'erro2'; // Retorna null em caso de erro
			}
		}
		
		

        
    }

?>

