<?php
require_once __DIR__ . '/../../config/database.php';

class Desire {
    private $conn;
    private $table_name = 'desires';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll($user_id = null, $desire = null) {
        try {
            $query = "SELECT d.*, u.nickname as user_name 
                     FROM " . $this->table_name . " d 
                     JOIN users u ON d.user_id = u.id";
            
            $conditions = [];
            $params = [];
            
            if ($user_id !== null) {
                $conditions[] = "d.user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            
            if ($desire !== null) {
                $conditions[] = "d.desire LIKE :desire";
                $params[':desire'] = "%" . $desire . "%";
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $query .= " ORDER BY d.time DESC";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
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
                     FROM " . $this->table_name . " d 
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
            $checkQuery = "SELECT id FROM " . $this->table_name . " WHERE user_id = :user_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindValue(":user_id", $data['user_id']);
            $checkStmt->execute();
            $existingDesire = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingDesire) {
                // If exists, update it
                $query = "UPDATE " . $this->table_name . " SET 
                            desire = :desire,
                            comment = :comment,
                            time = CURRENT_TIMESTAMP
                         WHERE user_id = :user_id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(":user_id", $data['user_id']);
                $stmt->bindValue(":desire", $data['desire']);
                $stmt->bindValue(":comment", $data['comment'] ?? null);
                
                if ($stmt->execute()) {
                    return $this->get($existingDesire['id']);
                }
            } else {
                // If doesn't exist, create new
                $query = "INSERT INTO " . $this->table_name . " (user_id, desire, comment, time) 
                         VALUES (:user_id, :desire, :comment, CURRENT_TIMESTAMP)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(":user_id", $data['user_id']);
                $stmt->bindValue(":desire", $data['desire']);
                $stmt->bindValue(":comment", $data['comment'] ?? null);
                
                if ($stmt->execute()) {
                    return $this->get($this->conn->lastInsertId());
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
            $query = "UPDATE " . $this->table_name . " SET 
                        desire = :desire,
                        comment = :comment,
                        time = CURRENT_TIMESTAMP
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
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            
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

    public function getUserDesires($userId, $limit = null) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                     WHERE user_id = :user_id 
                     ORDER BY time DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":user_id", $userId);
            if ($limit) {
                $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in getUserDesires: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function getDesiresByDateRange($userId, $startDate, $endDate) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                     WHERE user_id = :user_id 
                     AND time BETWEEN :start_date AND :end_date 
                     ORDER BY time DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":user_id", $userId);
            $stmt->bindValue(":start_date", $startDate);
            $stmt->bindValue(":end_date", $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in getDesiresByDateRange: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
} 