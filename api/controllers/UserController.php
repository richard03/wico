<?php
require_once __DIR__ . '/../../config/database.php';

class UserController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        $query = "SELECT id, `key`, name, google_id, username, profile_picture_url, email, 
                        phone, gps, created_at, updated_at, last_login 
                 FROM users";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function get($id) {
        $query = "SELECT id, `key`, name, google_id, username, profile_picture_url, email, 
                        phone, gps, created_at, updated_at, last_login 
                 FROM users 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        if (!isset($data['google_id']) || !isset($data['email'])) {
            throw new Exception("google_id and email are required");
        }
        
        $query = "INSERT INTO users (`key`, name, google_id, username, profile_picture_url, 
                                   email, phone, gps) 
                 VALUES (:key, :name, :google_id, :username, :profile_picture_url, 
                         :email, :phone, :gps)";
        
        $stmt = $this->conn->prepare($query);
        
        // Generate a random key if not provided
        $key = $data['key'] ?? mt_rand(1000000000, 9999999999);
        
        $stmt->bindValue(":key", $key);
        $stmt->bindValue(":name", $data['name'] ?? null);
        $stmt->bindValue(":google_id", $data['google_id']);
        $stmt->bindValue(":username", $data['username'] ?? null);
        $stmt->bindValue(":profile_picture_url", $data['profile_picture_url'] ?? null);
        $stmt->bindValue(":email", $data['email']);
        $stmt->bindValue(":phone", $data['phone'] ?? null);
        $stmt->bindValue(":gps", $data['gps'] ?? null);
        
        if ($stmt->execute()) {
            return [
                "id" => $this->conn->lastInsertId(),
                "key" => $key,
                "name" => $data['name'] ?? null,
                "google_id" => $data['google_id'],
                "username" => $data['username'] ?? null,
                "profile_picture_url" => $data['profile_picture_url'] ?? null,
                "email" => $data['email'],
                "phone" => $data['phone'] ?? null,
                "gps" => $data['gps'] ?? null
            ];
        }
        
        throw new Exception("Unable to create user");
    }
    
    public function update($id, $data) {
        $query = "UPDATE users SET 
                    name = :name,
                    username = :username,
                    profile_picture_url = :profile_picture_url,
                    phone = :phone,
                    gps = :gps
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindValue(":id", $id);
        $stmt->bindValue(":name", $data['name'] ?? null);
        $stmt->bindValue(":username", $data['username'] ?? null);
        $stmt->bindValue(":profile_picture_url", $data['profile_picture_url'] ?? null);
        $stmt->bindValue(":phone", $data['phone'] ?? null);
        $stmt->bindValue(":gps", $data['gps'] ?? null);
        
        if ($stmt->execute()) {
            return $this->get($id);
        }
        
        throw new Exception("Unable to update user");
    }
    
    public function delete($id) {
        $query = "DELETE FROM users WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id", $id);
        
        if ($stmt->execute()) {
            return ["message" => "User deleted successfully"];
        }
        
        throw new Exception("Unable to delete user");
    }
} 