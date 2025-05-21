<?php
require_once __DIR__ . '/../../config/database.php';

class DesireController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        $query = "SELECT d.*, u.name as user_name 
                 FROM desires d 
                 JOIN users u ON d.user_id = u.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function get($id) {
        $query = "SELECT d.*, u.name as user_name 
                 FROM desires d 
                 JOIN users u ON d.user_id = u.id 
                 WHERE d.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        if (!isset($data['user_id']) || !isset($data['desire'])) {
            throw new Exception("user_id and desire are required");
        }
        
        $query = "INSERT INTO desires (`key`, user_id, desire, comment) 
                 VALUES (:key, :user_id, :desire, :comment)";
        
        $stmt = $this->conn->prepare($query);
        
        // Generate a random key if not provided
        $key = $data['key'] ?? mt_rand(1000000000, 9999999999);
        
        $stmt->bindParam(":key", $key);
        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->bindParam(":desire", $data['desire']);
        $stmt->bindParam(":comment", $data['comment'] ?? null);
        
        if ($stmt->execute()) {
            return [
                "id" => $this->conn->lastInsertId(),
                "key" => $key,
                "user_id" => $data['user_id'],
                "desire" => $data['desire'],
                "comment" => $data['comment'] ?? null
            ];
        }
        
        throw new Exception("Unable to create desire");
    }
    
    public function update($id, $data) {
        if (!isset($data['desire'])) {
            throw new Exception("desire is required");
        }
        
        $query = "UPDATE desires SET 
                    desire = :desire,
                    comment = :comment
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":desire", $data['desire']);
        $stmt->bindParam(":comment", $data['comment'] ?? null);
        
        if ($stmt->execute()) {
            return $this->get($id);
        }
        
        throw new Exception("Unable to update desire");
    }
    
    public function delete($id) {
        $query = "DELETE FROM desires WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if ($stmt->execute()) {
            return ["message" => "Desire deleted successfully"];
        }
        
        throw new Exception("Unable to delete desire");
    }
} 