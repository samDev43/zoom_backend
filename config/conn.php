<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: https://zoom-fontend.vercel.app");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
  class Connection {
    private $host = "dpg-d80g0dd0lvsc738mck40-a";
    private $username = "zoom_database_user";
        private $port = "5432";
    private $password = "mwz1vdugyxkPX3NLAHRxIp2iTU15Uo2S";
    private $database = "zoom_database";
    protected $conn;
    
    public function __construct(){
        $this->conn = pg_connect(
           "host={$this->host}
            port={$this->port}
            dbname={$this->database}
            user={$this->username}
            password={$this->password}"
        );
        if(!$this->conn){
            die(json_encode([
                "status" => "error",
                "message" => "PostgreSQL connection failed"
            ]));
        }
    }
    
    public function conn(){
        return $this->conn;
    }
  }

?>