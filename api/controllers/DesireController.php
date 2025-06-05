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
                user_id BIGINT NOT NULL PRIMARY KEY,
                user_key VARCHAR(255) NOT NULL,
                desire VARCHAR(20) NOT NULL,
                comment TEXT NULL,
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
     * Get all desires
     * GET /desires
     * @param string $key
     * @param string $desire
     * @return array
     */
    public function getAll($desire = null, $key = null, $limit = 20) {
        if (!$key) {
            throw new Exception('Missing KEY');
        }
        if (!$this->checkKey($key)) {
            throw new Exception('Invalid KEY');
        }
        try {
            $query = "SELECT *
                     FROM " . $this->table_name . " 
                     WHERE 1=1";
            
            if ($desire) {
                $query .= " AND desire = :desire";
            }
            
            $query .= " ORDER BY time DESC LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            
            if ($desire) {
                $stmt->bindValue(":desire", $desire);
            }
            $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in getAll: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    /**
     * Get a desire by ID
     * GET /desires/{user_id}
     * @param int $user_id
     * @param string $key
     * @return array
     */
    public function get($user_id = null, $key = null) {
        if (!$key) {
            throw new Exception('Correct KEY is required for a GET request');
        }
        if (!$user_id) {
            throw new Exception('User ID is required for a GET request');
        }
        try {
            $query = "SELECT *
                     FROM " . $this->table_name . "
                     WHERE user_id = :user_id
                     AND user_key = :key
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":user_id", $user_id);
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
     *     @var int $user_id
     *     @var string $desire
     *     @var string $comment
     * }
     * @return array
     */
    public function set($data = null, $key = null) {
        if (!$key) {
            throw new Exception('Correct KEY is required for a POST request');
        }
        if (!$data || !isset($data['user_id']) || !isset($data['desire'])) {
            throw new Exception('User ID and desire are required');
        }
        try {
            // First check if desire exists for this user
            $checkQuery = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                    WHERE user_id = :user_id AND user_key = :key LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindValue(":user_id", $data['user_id']);
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
                        WHERE user_id = :user_id AND user_key = :key LIMIT 1";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(":user_id", $data['user_id']);
                $stmt->bindValue(":key", $key);
                $stmt->bindValue(":desire", $data['desire']);
                $stmt->bindValue(":comment", $data['comment'] ?? null);
                
                if ($stmt->execute()) {
                    return $this->get($data['user_id'], $key);
                }
            } else {
                // If doesn't exist, create new
                $query = "INSERT INTO " . $this->table_name . " (user_id, user_key, desire, comment, time) 
                        VALUES (:user_id, :key, :desire, :comment, CURRENT_TIMESTAMP)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(":user_id", $data['user_id']);
                $stmt->bindValue(":key", $key);
                $stmt->bindValue(":desire", $data['desire']);
                $stmt->bindValue(":comment", $data['comment'] ?? null);
                
                if ($stmt->execute()) {
                    return $this->get($data['user_id'], $key);
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
    public function delete($user_id = null, $key = null) {
        if (!$key) {
            throw new Exception('Correct KEY is required for a DELETE request');
        }
        if (!$user_id) {
            throw new Exception('User ID is required for a DELETE request');
        }
        try {
            // First check if the record exists
            $checkQuery = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                    WHERE user_id = :user_id AND user_key = :key LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindValue(":user_id", $user_id);
            $checkStmt->bindValue(":key", $key);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                throw new Exception("Desire not found");
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id AND user_key = :key LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":user_id", $user_id);
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