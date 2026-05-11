<?php
require_once __DIR__ . "/../config/conn.php";
require_once __DIR__ . '/../config/jwt.php';
  class Authentication extends Connection {

     function sign_up($email,$username,$password, $role = "user"){
        
      //   if($_SESSION['csrf_token'] !== $csrf_token) {
      //    // $data = array();
      //    echo json_encode ([
      //       "mesage" => "Invalid token",
      //       "status" => "error"
      //       ]);
      //    return;
      //   }

        if(filter_var($email,   FILTER_VALIDATE_EMAIL) === false){
           $data = array("status" => "error", "message" => "Invalid email format");
           echo json_encode ($data);
           return;
         }

         //   if(!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/', $password)){
         //      echo json_encode("Password must contain uppercase, lowercase, number and be 8+ characters");
         //      return;
         //    }
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $conn = $this->conn();
            $quary = "SELECT email FROM users WHERE email = $1";
            $result = pg_query_params($conn, $quary, array($email));
            $row = pg_fetch_assoc($result);

            if($row){
            $data = array("status" => "error", "message" => "Email already exists");
            echo json_encode ($data);
            return;
            }
            if($email == "samadadmin@zoom.com"){
               $role = "admin";
            }
               $quary2 = "INSERT INTO users (email, username, password, role) VALUES ($1, $2, $3, $4)";
               $stmt2 = pg_query_params($conn, $quary2, array($email, $username, $hashed_password, $role));
               if($stmt2){
               $data = array("status" => "success", "message" => "User registered");
            }
            echo json_encode ($data);
         
    }

     function log_in($username_email, $password){
      // echo json_encode (["status" => "good", "message" => $username_email]);
         $conn = $this->conn();
         $quary = "SELECT * FROM users WHERE email = $1 OR username = $2";
         $result = pg_query_params($conn, $quary, array($username_email, $username_email));
         if(!$result || pg_num_rows($result) == 0){
            echo json_encode (["status" => "error", "message" => "Invalid credentials"]);
            exit();
         }
         $user = pg_fetch_assoc($result);

            if (!isset($user['password'])) {
               http_response_code(500);
               echo json_encode([
                     "status" => "error",
                     "message" => "Corrupted user data"
               ]);
               return;
            }

         if(password_verify($password, $user['password'])){
            // $_SESSION['user_id'] = $user['id'];
               $jwtt = generate_jwt($user);
            echo json_encode (["status" => "success", "token" => $jwtt]);
         } else {
            echo json_encode (["status" => "error", "message" => "Invalid credentials"]);
         }
     }
  }
?>