<?php
require_once __DIR__ . '/../../config/database.php';

class User {
    private $conn;
    private $table_name = 'users';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        try {
            $query = "SELECT id, nickname, email, created_at, updated_at, last_login, phone, gps 
                     FROM " . $this->table_name;
            
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
                     FROM " . $this->table_name . " 
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
    
    public function findByEmailOrPhone($email = null, $phone = null) {
        try {
            $query = "SELECT id, nickname, email, created_at, updated_at, last_login, phone, gps 
                     FROM users 
                     WHERE 1=1";
            $params = [];
            
            if ($email !== null) {
                $query .= " AND email = :email";
                $params[':email'] = $email;
            }
            
            if ($phone !== null) {
                // Decode the phone number from URI encoding
                $phone = urldecode($phone);
                // Remove all spaces from the phone number
                $phone = str_replace(' ', '', $phone);
                // Add + prefix if not present
                if (strpos($phone, '+') !== 0) {
                    $phone = '+' . $phone;
                }
                
                // Log the phone number for debugging
                error_log("Searching for phone number: " . $phone);
                
                // First, let's see what phone numbers we have in the database
                $debugQuery = "SELECT phone FROM users WHERE phone IS NOT NULL";
                $debugStmt = $this->conn->prepare($debugQuery);
                $debugStmt->execute();
                $allPhones = $debugStmt->fetchAll(PDO::FETCH_COLUMN);
                error_log("All phone numbers in database: " . print_r($allPhones, true));
                
                $query .= " AND REPLACE(phone, ' ', '') = :phone";
                $params[':phone'] = $phone;
            }
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Log the final query and parameters
            error_log("Final query: " . $query);
            error_log("Parameters: " . print_r($params, true));
            
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result === false) {
                return null;
            }
            return $result;
        } catch (PDOException $e) {
            error_log("SQL Error in findByEmailOrPhone: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function create($data) {
        try {
            // Generate a random 10-digit number for id if not provided
            if (!isset($data['id'])) {
                $data['id'] = $this->generateUniqueId();
            }

            $query = "INSERT INTO " . $this->table_name . " 
                     (id, nickname, email, phone, gps) 
                     VALUES (:id, :nickname, :email, :phone, :gps)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindValue(":id", $data['id']);
            $stmt->bindValue(":nickname", $data['nickname'] ?? null);
            $stmt->bindValue(":email", $data['email']);
            $stmt->bindValue(":phone", $data['phone']);
            $stmt->bindValue(":gps", $data['gps'] ?? null);
            
            if ($stmt->execute()) {
                return $this->get($data['id']);
            }
            
            throw new Exception("Unable to create user");
        } catch (PDOException $e) {
            error_log("SQL Error in create: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Generate a unique 10-digit ID for a new user
     * @return int
     */
    private function generateUniqueId() {
        $maxAttempts = 10; // Maximum number of attempts to generate unique ID
        $attempt = 0;
        
        do {
            $id = mt_rand(1000000000, 9999999999);
            
            // Check if ID exists
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":id", $id);
            $stmt->execute();
            
            $exists = $stmt->fetchColumn() > 0;
            $attempt++;
            
            if (!$exists) {
                return $id;
            }
        } while ($attempt < $maxAttempts);
        
        throw new Exception("Unable to generate unique user ID after {$maxAttempts} attempts");
    }
    
    public function update($id, $data) {
        try {
            $updateFields = [];
            $params = [":id" => $id];
            
            if (isset($data['nickname'])) {
                $updateFields[] = "nickname = :nickname";
                $params[":nickname"] = $data['nickname'];
            }
            if (isset($data['phone'])) {
                $updateFields[] = "phone = :phone";
                $params[":phone"] = $data['phone'];
            }
            if (isset($data['gps'])) {
                $updateFields[] = "gps = :gps";
                $params[":gps"] = $data['gps'];
            }
            
            if (empty($updateFields)) {
                return $this->get($id);
            }
            
            $query = "UPDATE " . $this->table_name . " 
                     SET " . implode(", ", $updateFields) . " 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
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
            // First delete all contacts where this user is either user_1_id or their phone is user_2_phone
            $user = $this->get($id);
            if (!$user) {
                throw new Exception("User not found");
            }

            $deleteContactsQuery = "DELETE FROM contacts WHERE user_1_id = :user_id OR user_2_phone = :user_phone";
            $deleteContactsStmt = $this->conn->prepare($deleteContactsQuery);
            $deleteContactsStmt->bindValue(":user_id", $id);
            $deleteContactsStmt->bindValue(":user_phone", $user['phone']);
            $deleteContactsStmt->execute();
            
            // Then delete all desires associated with this user
            $deleteDesiresQuery = "DELETE FROM desires WHERE user_id = :user_id";
            $deleteDesiresStmt = $this->conn->prepare($deleteDesiresQuery);
            $deleteDesiresStmt->bindValue(":user_id", $id);
            $deleteDesiresStmt->execute();
            
            // Finally delete the user
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            
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

    public function updateLastLogin($id) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                     SET last_login = CURRENT_TIMESTAMP 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":id", $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("SQL Error in updateLastLogin: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
} 