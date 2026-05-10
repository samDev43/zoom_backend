<?php
  
require_once __DIR__ . "/../config/conn.php";
require_once __DIR__ . '/../config/jwt.php';

class ActionController extends Connection {
   function getUser($token){
      $validate_token = validate_jwt($token);
      if(!$validate_token){
         echo json_encode([
            "status" => "error",
            "message" => "Invalid token"
         ]);
         return;
      }
      $user_id = $validate_token->user_id;
      $conn = $this->conn();
      $quary = "SELECT id, username, email, role, profile_picture FROM users WHERE id = ?";
      $stmt = $conn->prepare($quary);
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $user = $result->fetch_assoc();
      echo json_encode([
         "status" => "success",
         "data" => $user
      ]);
   }
    function addPost($title, $excerpt, $content, $image, $temp_image, $path, $imageSize, $token){
      
      if($image){
          $allowed = ['jpg', 'jpeg', 'png'];
         $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));

         if(!in_array($ext, $allowed)){
         echo json_encode('invalid file');
         return;
         }
         if($image === null){
            $image = '';
         }
         $finalName = uniqid() . "_" . $image;

         $destination = $path . $finalName;
         move_uploaded_file($temp_image, $destination);
         if($imageSize > 2 * 1024 *1024) {
            echo json_encode('File is too large');
            return;
         }
      }else{
          $finalName = "default.png";
         // return;
      }
         $validate_token = validate_jwt($token);
         if(!$validate_token){
            echo json_encode([
                  "status" => "error",
                  "message" => "Invalid token"
            ]);
            return;
         }

         $user_id = $validate_token->user_id;
         $conn = $this->conn();
         $quary = 'INSERT INTO posts (user_id, title, content, header_image) VALUE(?,?,?,?)';
         $stmt = $conn->prepare($quary);
         $stmt->bind_param("isss", $user_id, $title, $content, $finalName);
         $stmt->execute();
         if($stmt->affected_rows > 0){
            echo json_encode([
            "status" => "success",
            "message" => "Post created successfully",
            ]);
         }else{
            echo json_encode([
            "status" => "error",
            "message" => "Failed to create post"
            ]);
            }

    }

    function getUserPosts($token){
         $validate_token = validate_jwt($token);
         if(!$validate_token){
            echo json_encode([
                  "status" => "error",
                  "message" => "Invalid token"
            ]);
            return;
         }
         $user_id = $validate_token->user_id;
         $user_name = $validate_token->username;
         $conn = $this->conn();
         $quary = "SELECT * FROM posts WHERE user_id = ?";
         $stmt = $conn->prepare($quary);
         $stmt->bind_param("i", $user_id);
         $stmt->execute();
         $result = $stmt->get_result();
         $userPosts = [];
         while($row = $result->fetch_assoc()){
            $userPosts[] = $row;
         }
         echo json_encode([
            "status" => "success",
            "message" => "User posts retrieved successfully",
            "user" => $user_name,
            "data" => $userPosts
         ]);
    }

    function getAllPost(){
         $conn = $this->conn();

         $query = "
            SELECT posts.*, users.username 
            FROM posts
            JOIN users ON posts.user_id = users.id
            ORDER BY posts.created_at DESC
         ";

         $stmt = $conn->prepare($query);
         $stmt->execute();
         $result = $stmt->get_result();

         $posts = [];

         while($row = $result->fetch_assoc()){
            $posts[] = $row;
         }

         echo json_encode([
            "status" => "success",
            "posts" => $posts,
         ]);


    }

    function getSinglePost($post_id){
      $conn = $this->conn();
      $quary = " SELECT posts.*, users.username, users.profile_picture 
         FROM posts 
         JOIN users ON posts.user_id = users.id 
         WHERE posts.id = ?";
      $stmt = $conn->prepare($quary);
      $stmt->bind_param("i", $post_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $post = $result->fetch_assoc();

      $quary2 = "SELECT comments.*, users.username, users.profile_picture FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ?";
      $stmt2 = $conn->prepare($quary2);
      $stmt2->bind_param("i", $post_id);
      $stmt2->execute();
      $result2 = $stmt2->get_result();
      $comments = [];
      while($row = $result2->fetch_assoc()){
         $comments[] = $row;
      }
      echo json_encode([
         "status"=>"success",
         "data"=> $post,
         "comments" => $comments,
         "postId" => $post_id
      ]);
    }

    function postComment($comment, $post_id, $token){
       $validate_token = validate_jwt($token);

         if(!$validate_token){
            echo json_encode([
                  "status" => "error",
                  "message" => $token
            ]);
            return;
         }
         $user_id = $validate_token->user_id;
         $conn = $this->conn();
         $quary = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
         $stmt = $conn->prepare($quary);
         $stmt->bind_param("iis", $post_id, $user_id, $comment);
         $stmt->execute();

         $quary2 = "SELECT comments.*, users.username, users.profile_picture FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ?";
         $stmt2 = $conn->prepare($quary2);
         $stmt2->bind_param("i", $post_id);
         $stmt2->execute();
         $result2 = $stmt2->get_result();
         $comments = [];
         while($row = $result2->fetch_assoc()){
         $comments[] = $row;
      }

         if($stmt->affected_rows > 0){
            echo json_encode([
               "status" => "success",
               "message" => "Comment posted successfully",
               "comments" => $comments
            ]);
         }else{
            echo json_encode([
               "status" => "error",
               "message" => "Failed to post comment"
            ]);

      }
   }

    function deletePost($post_id, $token){
       $conn = $this->conn();
      $validate_token = validate_jwt($token);
      if(!$validate_token){
         echo json_encode([
            "status" => "error",
            "message" => "invalid token",
            "token" => $token
         ]);
         return;
      }
      $user_id = $validate_token->user_id;
      $user_role = $validate_token->role;
       $quary0 = "SELECT user_id FROM posts WHERE id = ?";
       $stmt0 = $conn->prepare($quary0);
       $stmt0->bind_param('i', $post_id);
       $stmt0->execute();
       $result = $stmt0->get_result();
       $userId = $result->fetch_assoc();
       if($userId['user_id'] !== $user_id && $user_role !== "admin"){
          echo json_encode([
            "message" => "You cant delete this post",
            "id" => $userId['user_id'],
            "seid" => $user_id
          ]);
          return;
       }
       $quary = "DELETE FROM posts WHERE id = ?";
       $stmt = $conn->prepare($quary);
       $stmt->bind_param("i", $post_id);
       if($stmt->execute()){
          echo json_encode([
               "status" => "success",
               "message" => "Post deleted successfully",
            ]);
       }else{
         echo json_encode([
               "status" => "error",
               "message" => "failed to  delete post",
            ]);
       }
    }
    
    function uploadProfile($token, $profile, $temp_profile, $directory){
         $validate_jwt = validate_jwt($token);
            if(!$validate_jwt){
               echo json_encode([
                  "status" => "error",
                  "message" => "Invalid token"
               ]);
               return;
            }
            $user_id = $validate_jwt->user_id;
            $allowed = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($profile, PATHINFO_EXTENSION));
            if(!in_array($ext, $allowed)){
               echo json_encode([
                  "status" => "error",
                  "message" => "Invalid file type"
                  ]);
               return;
            }
            $finalName = uniqid() . "_" . $profile;
            $destination = $directory . $finalName;
            move_uploaded_file($temp_profile, $destination);
            $conn = $this->conn();
            $quary = "UPDATE users SET profile_picture = ? WHERE id = ?";
            $stmt = $conn->prepare($quary);
            $stmt->bind_param("si", $finalName, $user_id);
            if($stmt->execute()){
               echo json_encode([
                  "status" => "success",
                  "message" => "Profile picture updated successfully",
               ]);
               }else{
               echo json_encode([
                  "status" => "error",
                  "message" => "Failed to update profile picture",
               ]);
            }
   }
   
   function updateUserName($token, $new_username){
      $validate_jwt = validate_jwt($token);
      if(!$validate_jwt){
         echo json_encode([
            "status" => "error",
            "message" => "Invalid token"
         ]);
         return;
      }
      $user_id = $validate_jwt->user_id;
      $conn = $this->conn();
      $quary = "UPDATE users SET username = ? WHERE id = ?";
      $stmt = $conn->prepare($quary);
      $stmt->bind_param("si", $new_username, $user_id);
      if($stmt->execute()){
         echo json_encode([
            "status" => "success",
            "message" => "Username updated successfully",
         ]);
      }else{
         echo json_encode([
            "status" => "error",
            "message" => "Failed to update username",
            ]);
      }
      
   }

   function updateEmail($token, $new_email){
      $validate_jwt = validate_jwt($token);
      if(!$validate_jwt){
         echo json_encode([
            "status" => "error",
            "message" => "Invalid token"
         ]);
         return;
      }
      $user_id = $validate_jwt->user_id;
      $conn = $this->conn();
      $quary = "UPDATE users SET email = ? WHERE id = ?";
      $stmt = $conn->prepare($quary);
      $stmt->bind_param("si", $new_email, $user_id);
      if($stmt->execute()){
         echo json_encode([
            "status" => "success",
            "message" => "Email updated successfully",
            ]);
      }else{
         echo json_encode([
            "status" => "error",
            "message" => "Failed to update email",
         ]);
         }
   }
   
   function updatePassword($token, $new_password){
            $validate_jwt = validate_jwt($token);
            if(!$validate_jwt){
               echo json_encode([
                  "status" => "error",
                  "message" => "Invalid token"
               ]);
               return;
            }
            $user_id = $validate_jwt->user_id;
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $conn = $this->conn();
            $quary = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($quary);
            $stmt->bind_param("si", $hashed_password, $user_id);
            if($stmt->execute()){
               echo json_encode([
                  "status" => "success",
                  "message" => "Password updated successfully",
               ]);
            }else{
               echo json_encode([
                  "status" => "error",
                  "message" => "Failed to update password",
               ]);
               }
   }


   function deleteAccount($tokenn, $id){
      $conn = $this->conn();
      $validate_jwt = validate_jwt($tokenn);
      if(!$validate_jwt){
            echo json_encode([
            "status" => "error",
            "message" => "Invalid token"
            ]);
            return;
      }
      $user_id = $validate_jwt->user_id;
      if($user_id !== $id && $validate_jwt->role !== "admin"){
         echo json_encode([
            "status" => "error",
            "message" => "You can only delete your own account"
         ]);
         return;
      }
      if($validate_jwt->role === "admin" && $user_id === $id){
         echo json_encode([
            "status" => "error",
            "message" => "Admin accounts cannot be deleted"
         ]);
         return;
      }
      $quary = "DELETE FROM users WHERE id = ?";
      $stmt = $conn->prepare($quary);
      $stmt->bind_param("i", $id);
      if($stmt->execute()){
         echo json_encode([
            "status" => "success",
            "message" => "Account deleted successfully",
         ]);
      }
   }

   function getAllUsers($token){
      $validate_jwt = validate_jwt($token);
      if(!$validate_jwt){
         echo json_encode([
            "status" => "error",
            "message" => "Invalid token"
         ]);
         return;
      }
      $conn = $this->conn();
      $quary = "SELECT id, username, email, role, timestamp FROM users";
      $stmt = $conn->prepare($quary);
      $stmt->execute();
      $result = $stmt->get_result();
      $users = [];
      while($row = $result->fetch_assoc()){
         $users[] = $row;
      }
      echo json_encode([
         "status" => "success",
         "users" => $users
      ]);
   }
   
}

?>