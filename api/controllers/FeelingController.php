<?php
require_once __DIR__ . '/../../config/database.php';

class FeelingController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        $query = "SELECT f.*, u.name as user_name 
                 FROM feelings f 
                 JOIN users u ON f.user_id = u.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function get($id) {
        $query = "SELECT f.*, u.name as user_name 
                 FROM feelings f 
                 JOIN users u ON f.user_id = u.id 
                 WHERE f.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        if (!isset($data['user_id']) || !isset($data['feeling'])) {
            throw new Exception("user_id and feeling are required");
        }
        
        $query = "INSERT INTO feelings (`key`, user_id, feeling) 
                 VALUES (:key, :user_id, :feeling)";
        
        $stmt = $this->conn->prepare($query);
        
        // Generate a random key if not provided
        $key = $data['key'] ?? mt_rand(1000000000, 9999999999);
        
        $stmt->bindParam(":key", $key);
        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->bindParam(":feeling", $data['feeling']);
        
        if ($stmt->execute()) {
            return [
                "id" => $this->conn->lastInsertId(),
                "key" => $key,
                "user_id" => $data['user_id'],
                "feeling" => $data['feeling']
            ];
        }
        
        throw new Exception("Unable to create feeling");
    }
    
    public function update($id, $data) {
        if (!isset($data['feeling'])) {
            throw new Exception("feeling is required");
        }
        
        $query = "UPDATE feelings SET feeling = :feeling WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":feeling", $data['feeling']);
        
        if ($stmt->execute()) {
            return $this->get($id);
        }
        
        throw new Exception("Unable to update feeling");
    }
    
    public function delete($id) {
        $query = "DELETE FROM feelings WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if ($stmt->execute()) {
            return ["message" => "Feeling deleted successfully"];
        }
        
        throw new Exception("Unable to delete feeling");
    }
} 