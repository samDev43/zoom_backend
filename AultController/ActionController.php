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
      $quary = "SELECT id, username, email, role, profile_picture 
                FROM users 
                WHERE id = $1";
      $result = pg_query_params($conn, $quary, [$user_id]);
      $user = pg_fetch_assoc($result);
      // $result = $stmt->get_result();
      // $user = $result->fetch_assoc();
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
         $quary = 'INSERT INTO posts (user_id, title, content, header_image) VALUES ($1, $2, $3, $4)';
         $result = pg_query_params($conn, $quary, [$user_id, $title, $content, $finalName]);
         if($result){
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
         $quary = "SELECT * FROM posts WHERE user_id = $1";
         $result = pg_query_params($conn, $quary, [$user_id]);
         $userPosts = [];
         while($row = pg_fetch_assoc($result)){
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

         $result = pg_query($conn, $query);

         $posts = [];

         while($row = pg_fetch_assoc($result)){
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
         WHERE posts.id = $1";
     
     $result = pg_query_params($conn, $quary, [$post_id]);
      $post = pg_fetch_assoc($result);

      $quary2 = "SELECT comments.*, users.username, users.profile_picture FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = $1";
      $result2 = pg_query_params($conn, $quary2, [$post_id]);
      $comments = [];
      while($row = pg_fetch_assoc($result2)){
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
         $quary = "INSERT INTO comments (post_id, user_id, content) VALUES ($1, $2, $3)";
         $result = pg_query_params($conn, $quary, [$post_id, $user_id, $comment]);
         if(!$result){
            echo json_encode([
               "status" => "error",
               "message" => "Failed to post comment"
            ]);
            return;
         }

         $quary2 = "SELECT comments.*, users.username, users.profile_picture FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = $1";
         $result2 = pg_query_params($conn, $quary2, [$post_id]);
         $comments = [];
         while($row = pg_fetch_assoc($result2)){
             $comments[] = $row;
         }

         if($result){
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
       $quary0 = "SELECT user_id FROM posts WHERE id = $1";
       $result0 = pg_query_params($conn, $quary0, [$post_id]);
       $userId = pg_fetch_assoc($result0);
       if($userId['user_id'] !== $user_id && $user_role !== "admin"){
          echo json_encode([
            "message" => "You cant delete this post",
            "id" => $userId['user_id'],
            "seid" => $user_id
          ]);
          return;
       }
       $quary = "DELETE FROM posts WHERE id = $1";
       $result = pg_query_params($conn, $quary, [$post_id]);
       
         if (!$result) {
            echo json_encode([
               "status" => "error",
               "message" => "Delete failed"
            ]);
            exit;
         }

         echo json_encode([
            "status" => "success",
            "message" => "Post deleted successfully"
         ]);
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
            $quary = "UPDATE users SET profile_picture = $1 WHERE id = $2";
            $result = pg_query_params($conn, $quary, [$finalName, $user_id]);
            if($result){
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
      $quary = "UPDATE users SET username = $1 WHERE id = $2";
      $result = pg_query_params($conn, $quary, [$new_username, $user_id]);
      if($result){
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
      $quary = "UPDATE users SET email = $1 WHERE id = $2";
      $result = pg_query_params($conn, $quary, [$new_email, $user_id]);
      if($result){
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
            $quary = "UPDATE users SET password = $1 WHERE id = $2";
            $result = pg_query_params($conn, $quary, [$hashed_password, $user_id]);
            if($result){
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
      $quary = "DELETE FROM users WHERE id = $1";
      $result = pg_query_params($conn, $quary, [$id]);
       if (!$result) {
         echo json_encode([
            "status" => "error",
            "message" => "Delete failed"
         ]);
         exit;
      }
       echo json_encode([
         "status" => "success",
         "message" => "Account deleted successfully",
      ]);
   
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
      $quary = "SELECT id, username, email, role, created_at FROM users";
      $result = pg_query_params($conn, $quary);
      $users = [];
      while($row = pg_fetch_assoc($result)){
         $users[] = $row;
      }
      echo json_encode([
         "status" => "success",
         "users" => $users
      ]);
   }
   
}

?>