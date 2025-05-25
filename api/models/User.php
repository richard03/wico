<?php
require_once __DIR__ . '/../../config/database.php';

class User {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        try {
            $query = "SELECT id, nickname, email, created_at, updated_at, last_login, phone, gps 
                     FROM users";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in getAll: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function get($id) {
        try {
            $query = "SELECT id, nickname, email, created_at, updated_at, last_login, phone, gps 
                     FROM users 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":id", $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in get: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function create($data) {
        try {
            // Generate a random 10-digit number for id
            $id = mt_rand(1000000000, 9999999999);
            
            $query = "INSERT INTO users (id, nickname, email, phone, gps) 
                     VALUES (:id, :nickname, :email, :phone, :gps)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindValue(":id", $id);
            $stmt->bindValue(":nickname", $data['nickname'] ?? null);
            $stmt->bindValue(":email", $data['email']);
            $stmt->bindValue(":phone", $data['phone'] ?? null);
            $stmt->bindValue(":gps", $data['gps'] ?? null);
            
            if ($stmt->execute()) {
                return [
                    "id" => $id,
                    "nickname" => $data['nickname'] ?? null,
                    "email" => $data['email'],
                    "phone" => $data['phone'] ?? null,
                    "gps" => $data['gps'] ?? null
                ];
            }
            
            throw new Exception("Unable to create user");
        } catch (PDOException $e) {
            error_log("SQL Error in create: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function update($id, $data) {
        try {
            $query = "UPDATE users SET 
                        nickname = :nickname,
                        phone = :phone,
                        gps = :gps,
                        updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindValue(":id", $id);
            $stmt->bindValue(":nickname", $data['nickname'] ?? null);
            $stmt->bindValue(":phone", $data['phone'] ?? null);
            $stmt->bindValue(":gps", $data['gps'] ?? null);
            
            if ($stmt->execute()) {
                return $this->get($id);
            }
            
            throw new Exception("Unable to update user");
        } catch (PDOException $e) {
            error_log("SQL Error in update: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            $query = "DELETE FROM users WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":id", $id);
            
            if ($stmt->execute()) {
                return ["message" => "User deleted successfully"];
            }
            
            throw new Exception("Unable to delete user");
        } catch (PDOException $e) {
            error_log("SQL Error in delete: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
} 