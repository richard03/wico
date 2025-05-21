<?php
require_once __DIR__ . '/../../config/database.php';

class ContactController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        $query = "SELECT c.*, u1.name as user1_name, u2.name as user2_name 
                 FROM contacts c 
                 JOIN users u1 ON c.user_1 = u1.id 
                 JOIN users u2 ON c.user_2 = u2.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function get($id) {
        $query = "SELECT c.*, u1.name as user1_name, u2.name as user2_name 
                 FROM contacts c 
                 JOIN users u1 ON c.user_1 = u1.id 
                 JOIN users u2 ON c.user_2 = u2.id 
                 WHERE c.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        if (!isset($data['user_1']) || !isset($data['user_2'])) {
            throw new Exception("user_1 and user_2 are required");
        }
        
        $query = "INSERT INTO contacts (user_1, user_2) VALUES (:user_1, :user_2)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_1", $data['user_1']);
        $stmt->bindParam(":user_2", $data['user_2']);
        
        if ($stmt->execute()) {
            return [
                "id" => $this->conn->lastInsertId(),
                "user_1" => $data['user_1'],
                "user_2" => $data['user_2']
            ];
        }
        
        throw new Exception("Unable to create contact");
    }
    
    public function update($id, $data) {
        if (!isset($data['user_1']) || !isset($data['user_2'])) {
            throw new Exception("user_1 and user_2 are required");
        }
        
        $query = "UPDATE contacts SET user_1 = :user_1, user_2 = :user_2 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_1", $data['user_1']);
        $stmt->bindParam(":user_2", $data['user_2']);
        
        if ($stmt->execute()) {
            return [
                "id" => $id,
                "user_1" => $data['user_1'],
                "user_2" => $data['user_2']
            ];
        }
        
        throw new Exception("Unable to update contact");
    }
    
    public function delete($id) {
        $query = "DELETE FROM contacts WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if ($stmt->execute()) {
            return ["message" => "Contact deleted successfully"];
        }
        
        throw new Exception("Unable to delete contact");
    }
} 