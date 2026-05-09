<?php
require_once __DIR__ . "/../conn.php";
require_once "config/jwt.php";
  class Authentication extends Connection {

     function sign_up($email,$username,$password, $csrf_token, $role = "user"){
        
        if($_SESSION['csrf_token'] !== $csrf_token) {
         // $data = array();
         echo json_encode ([
            "mesage" => "Invalid token",
            "status" => "error"
            ]);
         return;
        }

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
            $quary = "SELECT email FROM users WHERE email = ?";
            $stmt = $conn->prepare($quary);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0){
            $data = array("status" => "error", "message" => "Email already exists");
            echo json_encode ($data);
            return;
            }
            if($email == "samadadmin@zoom.com"){
               $role = "admin";
            }
               $quary2 = "INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, ?)";
               $stmt2 = $conn->prepare($quary2);
               $stmt2->bind_param("ssss", $email, $username, $hashed_password, $role);
               if($stmt2->execute()){
               $data = array("status" => "success", "message" => "User registered");
            }
            echo json_encode ($data);
         
    }

     function log_in($username_email, $password){
      // echo json_encode (["status" => "good", "message" => $username_email]);
         $conn = $this->conn();
         $quary = "SELECT * FROM users WHERE email = ? OR username = ?";
         $stmt = $conn->prepare($quary);
         $stmt->bind_param("ss", $username_email, $username_email);
         $stmt->execute();
         $result = $stmt->get_result();
         if($result->num_rows == 0){
            echo json_encode (["status" => "error", "message" => "Invalid username or email or password 4"]);
            return;
         }
         $user = $result->fetch_assoc();
         if(password_verify($password, $user['password'])){
            // $_SESSION['user_id'] = $user['id'];
               $jwtt = generate_jwt($user);
            echo json_encode (["status" => "success", "token" => $jwtt]);
         } else {
            echo json_encode (["status" => "error", "message" => "Invalid username or email or password"]);
         }
     }
  }
?>