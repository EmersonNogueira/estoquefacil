<?php

namespace models;
use PDO;
use PDOException;

class Model
{
    
    protected $pdo;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $host = 'sql311.infinityfree.com'; // Nome do host MySQL fornecido
        $dbName = 'if0_37323896_estoqueidmccbj'; // Nome do banco de dados fornecido
        $username = 'if0_37323896'; // Nome do usuário MySQL fornecido
        $password = 'Belinha2002'; // Senha MySQL fornecida

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $username, $password);

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die("Falha na conexão: " . $e->getMessage());
        }
    }
    
}

?>
