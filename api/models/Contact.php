<?php
require_once __DIR__ . '/../../config/database.php';

class Contact {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        try {
            $query = "SELECT c.*, u1.nickname as user_1_nickname, u2.nickname as user_2_nickname 
                     FROM contacts c 
                     JOIN users u1 ON c.user_1_id = u1.id 
                     JOIN users u2 ON c.user_2_id = u2.id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in getAll: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function get($user_1_id) {
        try {
            $query = "SELECT c.*, u1.nickname as user_1_nickname, u2.nickname as user_2_nickname 
                     FROM contacts c 
                     JOIN users u1 ON c.user_1_id = u1.id 
                     JOIN users u2 ON c.user_2_id = u2.id 
                     WHERE c.user_1_id = :user_1_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":user_1_id", $user_1_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in get: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function create($data) {
        try {
            $query = "INSERT INTO contacts (user_1_id, user_2_id, user_2_alias) 
                     VALUES (:user_1_id, :user_2_id, :user_2_alias)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindValue(":user_1_id", $data['user_1_id']);
            $stmt->bindValue(":user_2_id", $data['user_2_id']);
            $stmt->bindValue(":user_2_alias", $data['user_2_alias'] ?? null);
            
            if ($stmt->execute()) {
                return [
                    "user_1_id" => $data['user_1_id'],
                    "user_2_id" => $data['user_2_id'],
                    "user_2_alias" => $data['user_2_alias'] ?? null
                ];
            }
            
            throw new Exception("Unable to create contact");
        } catch (PDOException $e) {
            error_log("SQL Error in create: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function update($id, $data) {
        try {
            $query = "UPDATE contacts SET 
                        user_2_alias = :user_2_alias,
                        updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindValue(":id", $id);
            $stmt->bindValue(":user_2_alias", $data['user_2_alias'] ?? null);
            
            if ($stmt->execute()) {
                return $this->get($id);
            }
            
            throw new Exception("Unable to update contact");
        } catch (PDOException $e) {
            error_log("SQL Error in update: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function delete($user_1_id, $user_2_id) {
        try {
            $query = "DELETE FROM contacts 
                     WHERE user_1_id = :user_1_id AND user_2_id = :user_2_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":user_1_id", $user_1_id);
            $stmt->bindValue(":user_2_id", $user_2_id);
            
            if ($stmt->execute()) {
                return ["message" => "Contact deleted successfully"];
            }
            
            throw new Exception("Unable to delete contact");
        } catch (PDOException $e) {
            error_log("SQL Error in delete: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
} 