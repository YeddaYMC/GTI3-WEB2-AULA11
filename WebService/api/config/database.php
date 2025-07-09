<?php
class Database
{
    // Credenciais do banco de dados
    private $host = "localhost";
    private $db_name = "aps2 crud login";
    private $username = "root";   // Verifique se seu usuário é 'root'
    private $password = "";       // Verifique sua senha (geralmente vazia no WAMP/XAMPP)
    public $conn;

    // Método para obter a conexão com o banco de dados
    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            die("Erro de conexão: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
