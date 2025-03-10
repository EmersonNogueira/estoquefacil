<?php
	namespace models;

	class RegistroModel extends Model
	{

        public function reader() {
            try {
                // Verifica se o parâmetro de busca está presente na URL
                $search = isset($_GET['search']) ? $_GET['search'] : '';
        
                // Prepara a consulta SQL com JOIN para obter o nome do produto, setor e nome do usuário
                $sqlStr = "SELECT r.*, 
                    p.nome AS nome_produto, 
                    s.setor, 
                    s.subSetor, 
                    u.nome AS nome_usuario
                FROM registros r
                JOIN produtos p ON r.id_produto = p.id_produto
                LEFT JOIN solicitacoes s ON r.id_solicitacao = s.id_solicitacao
                LEFT JOIN usuario u ON r.id_usuario = u.id
                WHERE r.tipo IN ('Solicitação', 'Ajuste Negativo')";
        
                // Adiciona a condição de busca se o parâmetro de pesquisa estiver presente
                if (!empty($search)) {
                    $sqlStr .= " AND p.nome LIKE :search"; // Filtra pelo nome do produto
                }
        
                // Ordena pelos registros mais recentes
                $sqlStr .= " ORDER BY r.data_registro DESC"; // Ordena pelos registros mais recentes
        
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
        
                // Vincula o parâmetro de busca se necessário
                if (!empty($search)) {
                    $sql->bindValue(':search', '%' . $search . '%'); // Utiliza o operador LIKE para pesquisar em qualquer parte do nome
                }
        
                $sql->execute();
                $resultados = $sql->fetchAll(\PDO::FETCH_ASSOC);
                return $resultados;
            } catch (\PDOException $e) {
                error_log("Erro na consulta: " . $e->getMessage());
                die("Erro na consulta: " . $e->getMessage());
            }
        }

        public function sintetico() {
            try {
                // Prepara a consulta SQL com JOIN para obter o nome do produto, setor e nome do usuário
                $sqlStr = "SELECT r.*, 
                    s.setor,
                    s.subsetor,
                    p.nome AS nome_produto
                FROM registros r
                LEFT JOIN solicitacoes s ON r.id_solicitacao = s.id_solicitacao
                JOIN produtos p ON r.id_produto = p.id_produto
                WHERE r.tipo IN ('Solicitação', 'Ajuste Negativo')";

        
                // Ordena pelos registros mais recentes
                $sqlStr .= " ORDER BY r.data_registro DESC"; // Ordena pelos registros mais recentes
        
                // Log da consulta SQL para depuração
                error_log("Consulta SQL: " . $sqlStr);
        
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
        
                // Executa a consulta
                $sql->execute();
        
                // Verifica se há resultados
                $resultados = $sql->fetchAll(\PDO::FETCH_ASSOC);
        
                // Loga os resultados para depuração
                error_log("Resultados: " . print_r($resultados, true));
        
                // Se não houver resultados, retorna uma mensagem apropriada
                if (empty($resultados)) {
                    return ['message' => 'Nenhum registro encontrado.'];
                }
        
                return $resultados;
        
            } catch (\PDOException $e) {
                // Loga o erro para análise posterior
                error_log("Erro na consulta: " . $e->getMessage());
                
                // Retorna uma mensagem de erro genérica sem expor detalhes da exceção ao usuário
                return ['error' => 'Erro ao processar a consulta, tente novamente mais tarde.'];
            }
        }
        public function sinteticoentrada(){
            try {
                // Prepara a consulta SQL com JOIN para obter o nome do produto, setor e nome do usuário
                $sqlStr = "SELECT r.*, 
                            p.nome AS nome_produto
                        FROM registros r
                        JOIN produtos p ON r.id_produto = p.id_produto
                        WHERE r.tipo IN ('Compra', 'Ajuste Positivo')"; // Inclui 'ajuste' na condição WHERE
                
                // Ordena pelos registros mais recentes
                $sqlStr .= " ORDER BY r.data_registro DESC"; // Ordena pelos registros mais recentes
                
                // Log da consulta SQL para depuração
                error_log("Consulta SQL: " . $sqlStr);
                
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
                
                // Executa a consulta
                $sql->execute();
                
                // Verifica se há resultados
                $resultados = $sql->fetchAll(\PDO::FETCH_ASSOC);
                
                // Loga os resultados para depuração
                error_log("Resultados: " . print_r($resultados, true));
                
                // Se não houver resultados, retorna uma mensagem apropriada
                if (empty($resultados)) {
                    return ['message' => 'Nenhum registro encontrado.'];
                }
                
                return $resultados;
            
            } catch (\PDOException $e) {
                // Loga o erro para análise posterior
                error_log("Erro na consulta: " . $e->getMessage());
                
                // Retorna uma mensagem de erro genérica sem expor detalhes da exceção ao usuário
                return ['error' => 'Erro ao processar a consulta, tente novamente mais tarde.'];
            }
        }
        
        
        
        
        public function entrada() {
            try {
                // Verifica se o parâmetro de busca está presente na URL
                $search = isset($_GET['search']) ? $_GET['search'] : '';
        
                // Prepara a consulta SQL com JOIN para obter o nome do produto e as informações da tabela solicitacoes
                $sqlStr = "SELECT r.*, p.nome AS nome_produto, u.nome AS nome_usuario
                           FROM registros r
                           JOIN produtos p ON r.id_produto = p.id_produto
                           JOIN usuario u ON r.id_usuario = u.id

                           WHERE r.tipo IN ('Compra', 'Devolução', 'Ajuste Positivo')"; // Filtra registros do tipo compra ou devolucao
        
                // Adiciona a condição de busca se o parâmetro de pesquisa estiver presente
                if (!empty($search)) {
                    $sqlStr .= " AND p.nome LIKE :search"; // Filtra pelo nome do produto
                }
        
                // Ordena pelos registros mais recentes
                $sqlStr .= " ORDER BY r.data_registro DESC"; // Ordena pelos registros mais recentes
        
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
        
                // Vincula o parâmetro de busca se necessário
                if (!empty($search)) {
                    $sql->bindValue(':search', '%' . $search . '%'); // Utiliza o operador LIKE para pesquisar em qualquer parte do nome
                }
        
                $sql->execute();
                $resultados = $sql->fetchAll(\PDO::FETCH_ASSOC);
                return $resultados;
            } catch (\PDOException $e) {
                error_log("Erro na consulta: " . $e->getMessage());
                die("Erro na consulta: " . $e->getMessage());
            }
        }
        
        public function novoregistro($tipo, $quantidade, $id_produto, $id_solicitacao, $numero_nota, $custo, $saldo, $data) {
            // Obter o ID do usuário da sessão
            $id_usuario = $_SESSION['id'] ?? null; // Caso $_SESSION['id'] não esteja definido, usa null como padrão
            
            // SQL para inserir um novo registro, incluindo o campo 'id_usuario' e usando a data recebida
            $sql = "INSERT INTO registros (tipo, quantidade, id_produto, id_solicitacao, numero_nota, data_registro, custo, id_usuario)
                    VALUES (:tipo, :quantidade, :id_produto, :id_solicitacao, :numero_nota, :data_registro, :custo, :id_usuario)";
            
            // Preparar a consulta para evitar SQL injection
            $stmt = $this->pdo->prepare($sql);
            
            // Bind de parâmetros para a consulta
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':quantidade', $quantidade);
            $stmt->bindParam(':id_produto', $id_produto);
            $stmt->bindParam(':id_solicitacao', $id_solicitacao);
            $stmt->bindParam(':numero_nota', $numero_nota);
            $stmt->bindParam(':data_registro', $data); // Agora a data é um parâmetro passado pelo usuário
            $stmt->bindParam(':custo', $custo);
            $stmt->bindParam(':id_usuario', $id_usuario, \PDO::PARAM_INT); // Vincular o valor do ID do usuário como inteiro
            
            // Executar a consulta e verificar se a inserção foi bem-sucedida
            if ($stmt->execute()) {
                // Atualizar saldo após a inserção bem-sucedida
                $this->atualizarSaldo($id_produto, $saldo);
                return true; // Sucesso
            } else {
                return false; // Falha
            }
        }
        
        



        public function atualizarSaldo($id,$saldo){
			$stmt = $this->pdo->prepare("
			UPDATE produtos SET 
				saldo = :saldo
			WHERE id_produto = :id_produto
		");

		$stmt->bindParam(':saldo', $saldo, \PDO::PARAM_STR);
		$stmt->bindParam(':id_produto', $id, \PDO::PARAM_INT);

		return $stmt->execute(); // Retorna true em caso de sucesso ou false em caso de erro


		}
        public function setstatus($id_solicitacao) {
            $status = 'Finalizado'; // Definindo o valor do status como uma variável
            $stmt = $this->pdo->prepare("
                UPDATE solicitacoes SET 
                    status = :status
                WHERE id_solicitacao = :id_solicitacao
            ");
            
            $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
            $stmt->bindParam(':id_solicitacao', $id_solicitacao, \PDO::PARAM_INT);
        
            return $stmt->execute();     
        }



        public function setreceptor($id_solicitacao,$receptor){
            $sql = "UPDATE solicitacoes SET receptor = :receptor WHERE id_solicitacao = :id_solicitacao";
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':id_solicitacao', $id_solicitacao, \PDO::PARAM_INT);
            $stmt->bindParam(':receptor', $receptor);

            

            return $stmt->execute();     

        }


        public function setcustoprod($id,$custo){
            $stmt = $this->pdo->prepare("
                UPDATE produtos SET 
                custo = :custo
                WHERE id_produto = :id_produto
            ");
    
            $stmt->bindParam(':custo', $custo, \PDO::PARAM_STR);
            $stmt->bindParam(':id_produto', $id, \PDO::PARAM_INT);
    
            return $stmt->execute(); //

        }

        public function getSaldoProd($idProduto) {
            try {
                // Prepara a consulta SQL para buscar a quantidade do produto específico
                $sqlStr = "SELECT saldo FROM produtos WHERE id_produto = :idProduto";
                
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
        
                // Vincula o ID do produto ao parâmetro :idProduto
                $sql->bindValue(':idProduto', $idProduto, \PDO::PARAM_INT);
        
                // Executa a consulta
                $sql->execute();
        
                // Obtém o resultado
                $resultado = $sql->fetch(\PDO::FETCH_ASSOC);
        
                // Retorna a quantidade ou saldo do produto, se encontrado; caso contrário, retorna null
                return $resultado ? $resultado['saldo'] : null;
                
            } catch (\PDOException $e) {
                error_log("Erro ao buscar saldo: " . $e->getMessage());
                die("Erro ao buscar saldo: " . $e->getMessage());
            }
        }


        public function getCustProd($idProduto) {
            try {
                // Prepara a consulta SQL para buscar o custo do produto específico
                $sqlStr = "SELECT custo FROM produtos WHERE id_produto = :idProduto";
        
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
        
                // Vincula o ID do produto ao parâmetro :idProduto
                $sql->bindValue(':idProduto', $idProduto, \PDO::PARAM_INT);
        
                // Executa a consulta
                $sql->execute();
        
                // Obtém o resultado
                $resultado = $sql->fetch(\PDO::FETCH_ASSOC);
        
                // Retorna o custo do produto, se encontrado; caso contrário, retorna null
                return $resultado ? $resultado['custo'] : null;
                
            } catch (\PDOException $e) {
                error_log("Erro ao buscar custo: " . $e->getMessage());
                die("Erro ao buscar custo: " . $e->getMessage());
            }
        }
        
        


        public function devolucao($id_registro, $quantidadeDevolvida){
            // Passo 1: Consultar o registro para obter a quantidade atual
            $stmt = $this->pdo->prepare("
            SELECT quantidade 
            FROM registros 
            WHERE id_registro = :id_registro
            ");
            $stmt->bindParam(':id_registro', $id_registro, \PDO::PARAM_INT);
            $stmt->execute();

            // Obter o valor atual da quantidade
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($registro) {
                $quantidadeAtual = $registro['quantidade'];

                // Passo 2: Subtrair a quantidade devolvida
                $novaQuantidade = $quantidadeAtual - $quantidadeDevolvida;
                
                if ($novaQuantidade < 0) {
                    // Opcional: Verificar se a quantidade não é negativa
                    throw new Exception("A quantidade não pode ser negativa.");
                }

                // Passo 3: Atualizar o valor da quantidade na tabela
                $stmtUpdate = $this->pdo->prepare("
                    UPDATE registros 
                    SET quantidade = :nova_quantidade 
                    WHERE id_registro = :id_registro
                ");
                $stmtUpdate->bindParam(':nova_quantidade', $novaQuantidade, \PDO::PARAM_STR);
                $stmtUpdate->bindParam(':id_registro', $id_registro, \PDO::PARAM_INT);

                return $stmtUpdate->execute(); // Retorna true em caso de sucesso
            } else {
                throw new Exception("Registro não encontrado.");
            }
        }
            
        
    }


?>