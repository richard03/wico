<?php
require_once __DIR__ . '/../../config/database.php';

class DesireController {
    private $conn;
    private $table_name = 'desires';

    private $desire;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->createTables();
    }

    private function createTables() {
        try {
            $this->conn->exec("CREATE TABLE IF NOT EXISTS desires (
                user_key VARCHAR(255) NOT NULL PRIMARY KEY,
                desire VARCHAR(20) NOT NULL,
                comment VARCHAR(255) NULL,
                time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        } catch(PDOException $e) {
            error_log("Error creating tables: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    private function checkKey($key = null) {
        if (!$key) {
            throw new Exception('Correct KEY is required for a GET request');
        }
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE user_key = :key LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":key", $key);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['COUNT(*)'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get a desire by key
     * GET /desires/
     * @param string $key
     * @return array
     */
    public function get($key = null) {
        if (!$key) {
            throw new Exception('Correct KEY is required for a GET request');
        }
        try {
            $query = "SELECT *
                     FROM " . $this->table_name . "
                     WHERE user_key = :key
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":key", $key);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in get: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Create a new desire
     * POST /desires
     * @param array $data {
     *     @var string $desire
     *     @var string $comment
     * }
     * @return array
     */
    public function set($data = null, $key = null) {
        if (!$key) {
            throw new Exception('Correct KEY is required for a POST request');
        }
        if (!$data || !isset($data['desire'])) {
            throw new Exception('Desire value is required');
        }
        try {
            // First check if desire exists for this user
            $checkQuery = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                    WHERE user_key = :key LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindValue(":key", $key);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $recordExists = $result['count'] > 0;
            
            if ($recordExists) {
                // If exists, update it
                $query = "UPDATE " . $this->table_name . " SET 
                            desire = :desire,
                            comment = :comment,
                            time = CURRENT_TIMESTAMP
                        WHERE user_key = :key LIMIT 1";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(":key", $key);
                $stmt->bindValue(":desire", $data['desire']);
                $stmt->bindValue(":comment", $data['comment'] ?? null);
                
                if ($stmt->execute()) {
                    return $this->get($key);
                }
            } else {
                // If doesn't exist, create new
                $query = "INSERT INTO " . $this->table_name . " (user_key, desire, comment, time) 
                        VALUES (:key, :desire, :comment, CURRENT_TIMESTAMP)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(":key", $key);
                $stmt->bindValue(":desire", $data['desire']);
                $stmt->bindValue(":comment", $data['comment'] ?? null);
                
                if ($stmt->execute()) {
                    return $this->get($key);
                }
            }
            
            throw new Exception("Unable to create/update desire");
        } catch (PDOException $e) {
            error_log("SQL Error in create: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Delete a desire
     * DELETE /desires/{id}
     * @param int $id
     * @return array
     */
    public function delete($key = null) {
        if (!$key) {
            throw new Exception('Correct KEY is required for a DELETE request');
        }
        try {
            // First check if the record exists
            $checkQuery = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                    WHERE user_key = :key LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindValue(":key", $key);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                throw new Exception("Desire not found");
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE user_key = :key LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":key", $key);
            
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