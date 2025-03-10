<?php
	namespace models;

	class ProdutoModel extends Model
	{
		
		public function reader() {
			try {
				// Verifica se o parâmetro de busca está presente na URL
				$search = isset($_GET['search']) ? $_GET['search'] : '';
		
				// Prepara a consulta SQL com ou sem filtro de busca
				$sqlStr = "SELECT * FROM produtos";
		
				// Adiciona o filtro de busca, se houver
				if (!empty($search)) {
					$sqlStr .= " WHERE Nome LIKE :search";
				}
		
				// Adiciona a ordenação
				$sqlStr .= " ORDER BY local ASC";
		
				// Prepara a consulta SQL
				$sql = $this->pdo->prepare($sqlStr);
		
				// Se houver um parâmetro de busca, vincula o valor
				if (!empty($search)) {
					$sql->bindValue(':search', '%' . $search . '%');
				}
		
				$sql->execute();
				$resultados = $sql->fetchAll(\PDO::FETCH_ASSOC);
				return $resultados;
			} catch (\PDOException $e) {
				error_log("Erro na consulta: " . $e->getMessage());
				die("Erro na consulta: " . $e->getMessage());
			}
		}

		public function maddproduto($data) {
			$sql = "INSERT INTO produtos (nome, local, sublocal, situacao, saldo, custo, visivel, categoria) 
					VALUES (:nome, :local, :sublocal, :situacao, :saldo, :custo, :visivel, :categoria)";
			
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindParam(':nome', $data['nome']);
			$stmt->bindParam(':local', $data['local']);
			$stmt->bindParam(':sublocal', $data['sublocal']);
			$stmt->bindParam(':situacao', $data['situacao']);
			$stmt->bindParam(':saldo', $data['saldo']);
			$stmt->bindParam(':custo', $data['custo']);
			$stmt->bindParam(':visivel', $data['visivel']);
			$stmt->bindParam(':categoria', $data['categoria']); // Adicionando categoria
			
			return $stmt->execute();
		}
		


		public function buscarProdutoPorId($id) {
			
			$stmt = $this->pdo->prepare("SELECT * FROM produtos WHERE id_produto = :id");
			$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
			
			// Verifique o valor vinculado antes da execução
			$stmt->execute();
			
			// Retorna o produto se encontrado, ou null se não encontrado
			$res = $stmt->fetch(\PDO::FETCH_ASSOC);
			return $res; // Retorne a variável $res
		}
		
		

		public function matualizar($data) {
			// Prepara a consulta SQL para atualizar os dados do produto
			$stmt = $this->pdo->prepare("
				UPDATE produtos SET 
					nome = :nome,
					local = :local,
					sublocal = :sublocal,
					situacao = :situacao,
					categoria = :categoria,
					custo = :custo,
					visivel = :visivel
				WHERE id_produto = :id
			");
		
			// Associar os parâmetros da consulta com os valores do array $data
			$stmt->bindParam(':nome', $data['nome'], \PDO::PARAM_STR);
			$stmt->bindParam(':local', $data['local'], \PDO::PARAM_STR);
			$stmt->bindParam(':sublocal', $data['sublocal'], \PDO::PARAM_STR);
			$stmt->bindParam(':situacao', $data['situacao'], \PDO::PARAM_STR);
			$stmt->bindParam(':categoria', $data['categoria'], \PDO::PARAM_STR);
			$stmt->bindParam(':custo', $data['custo'], \PDO::PARAM_STR);
			$stmt->bindParam(':visivel', $data['visivel'], \PDO::PARAM_INT);
			$stmt->bindParam(':id', $data['id'], \PDO::PARAM_INT);
		
			// Executa a consulta
			return $stmt->execute(); // Retorna true em caso de sucesso ou false em caso de erro
		}
		


	



		
		
	}
?>