<?php
require_once __DIR__ . '/../../config/database.php';

class Desire {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll($desire = null) {
        try {
            $query = "SELECT d.*, u.nickname as user_name 
                     FROM desires d 
                     JOIN users u ON d.user_id = u.id";
            
            if ($desire !== null) {
                $query .= " WHERE d.desire LIKE :desire";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($desire !== null) {
                $stmt->bindValue(":desire", "%" . $desire . "%");
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in getAll: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function get($id) {
        try {
            $query = "SELECT d.*, u.nickname as user_name 
                     FROM desires d 
                     JOIN users u ON d.user_id = u.id 
                     WHERE d.id = :id";
            
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
            // First check if desire exists for this user
            $checkQuery = "SELECT id FROM desires WHERE user_id = :user_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindValue(":user_id", $data['user_id']);
            $checkStmt->execute();
            $existingDesire = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingDesire) {
                // If exists, update it
                $query = "UPDATE desires SET 
                            desire = :desire,
                            comment = :comment,
                            time = CURRENT_TIMESTAMP
                         WHERE user_id = :user_id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(":user_id", $data['user_id']);
                $stmt->bindValue(":desire", $data['desire']);
                $stmt->bindValue(":comment", $data['comment'] ?? null);
                
                if ($stmt->execute()) {
                    return [
                        "id" => $existingDesire['id'],
                        "user_id" => $data['user_id'],
                        "desire" => $data['desire'],
                        "comment" => $data['comment'] ?? null
                    ];
                }
            } else {
                // If doesn't exist, create new
                $query = "INSERT INTO desires (user_id, desire, comment, time) 
                         VALUES (:user_id, :desire, :comment, CURRENT_TIMESTAMP)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(":user_id", $data['user_id']);
                $stmt->bindValue(":desire", $data['desire']);
                $stmt->bindValue(":comment", $data['comment'] ?? null);
                
                if ($stmt->execute()) {
                    return [
                        "id" => $this->conn->lastInsertId(),
                        "user_id" => $data['user_id'],
                        "desire" => $data['desire'],
                        "comment" => $data['comment'] ?? null
                    ];
                }
            }
            
            throw new Exception("Unable to create/update desire");
        } catch (PDOException $e) {
            error_log("SQL Error in create: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function update($id, $data) {
        try {
            $query = "UPDATE desires SET 
                        desire = :desire,
                        comment = :comment,
                        updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindValue(":id", $id);
            $stmt->bindValue(":desire", $data['desire']);
            $stmt->bindValue(":comment", $data['comment'] ?? null);
            
            if ($stmt->execute()) {
                return $this->get($id);
            }
            
            throw new Exception("Unable to update desire");
        } catch (PDOException $e) {
            error_log("SQL Error in update: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            $query = "DELETE FROM desires WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":id", $id);
            
            if ($stmt->execute()) {
                return ["message" => "Desire deleted successfully"];
            }
            
            throw new Exception("Unable to delete desire");
        } catch (PDOException $e) {
            error_log("SQL Error in delete: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
} 