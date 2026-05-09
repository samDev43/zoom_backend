<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
  class Connection {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "zoom_database";
    protected $conn;
    
    public function __construct(){
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        if($this->conn->connect_error){
            die("Connection Failed: " . $this->conn->connect_error);
        }
    }
    
    public function conn(){
        return $this->conn;
    }
  }

?>