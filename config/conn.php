<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: http://localhost:5173");
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
    private $password = "mwz1vdugyxkPX3NLAHRxIp2iTU15Uo2S";
    private $database = "zoom_database";
    private $port = "5432";

    public $conn;

    public function __construct() {
        $this->conn = new PDO(
            "pgsql:host={$this->host};port={$this->port};dbname={$this->database}",
            $this->username,
            $this->password
        );

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function conn() {
        return $this->conn;
    }
}   
?>