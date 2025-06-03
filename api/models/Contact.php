<?php
require_once __DIR__ . '/../../config/database.php';

class Contact {
    private $conn;
    private $table_name = 'contacts';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Get all contacts
     * GET /contacts
     * @return array
     */
    public function getAll() {
        try {
            $query = "SELECT c.*, 
                            u1.nickname as user_1_nickname,
                            u2.nickname as user_2_nickname,
                            u2.email as user_2_email,
                            u2.phone as user_2_phone
                     FROM " . $this->table_name . " c 
                     JOIN users u1 ON c.user_1_id = u1.id 
                     JOIN users u2 ON c.user_2_phone = u2.phone";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in getAll: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Get all contacts of a user
     * GET /contacts/user/{user_id}
     * @param int $user_id
     * @param string|null $desire Optional desire filter
     * @return array
     */
    public function getUserContacts($user_id, $desire = null) {
        try {
            $query = "SELECT c.*,
                            d.desire as user_2_desire, 
                            d.comment as user_2_desire_comment,
                            d.time as user_2_desire_time,
                            u2.nickname as user_2_nickname
                     FROM " . $this->table_name . " c 
                     LEFT JOIN users u2 ON c.user_2_phone = u2.phone
                     LEFT JOIN desires d ON d.user_id = u2.id
                     WHERE c.user_1_id = :user_id";
            
            if ($desire !== null) {
                $query .= " AND d.desire = :desire";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":user_id", $user_id);
            
            if ($desire !== null) {
                $stmt->bindValue(":desire", $desire);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in getUserContacts: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Get a contact by user_1_id
     * GET /contacts/{user_1_id}
     * @param int $user_1_id
     * @return array
     */
    public function get($id) {
        try {
            $query = "SELECT c.*, 
                            u1.nickname as user_1_nickname,
                            u2.nickname as user_2_nickname,
                            u2.email as user_2_email,
                            u2.phone as user_2_phone
                     FROM " . $this->table_name . " c 
                     JOIN users u1 ON c.user_1_id = u1.id 
                     JOIN users u2 ON c.user_2_phone = u2.phone
                     WHERE c.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":id", $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in get: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Create a new contact
     * POST /contacts
     * @param array $data {
     *     @var int $user_1_id
     *     @var string $user_2_phone
     *     @var string $user_2_alias
     * }
     * @return array
     */
    public function create($data) {
        try {
            if (!isset($data['user_1_id']) || !isset($data['user_2_phone'])) {
                throw new Exception("Both user_1_id and user_2_phone are required");
            }

            // Check if contact already exists
            if ($this->contactExists($data['user_1_id'], $data['user_2_phone'])) {
                throw new Exception("Contact already exists");
            }

            $query = "INSERT INTO " . $this->table_name . " 
                     (user_1_id, user_2_phone, user_2_alias) 
                     VALUES (:user_1_id, :user_2_phone, :user_2_alias)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":user_1_id", $data['user_1_id']);
            $stmt->bindValue(":user_2_phone", $data['user_2_phone']);
            $stmt->bindValue(":user_2_alias", $data['user_2_alias'] ?? null);
            
            if ($stmt->execute()) {
                return $this->get($this->conn->lastInsertId());
            }
            
            throw new Exception("Unable to create contact");
        } catch (PDOException $e) {
            error_log("SQL Error in create: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Update a contact
     * PUT /contacts/{id}
     * @param int $id
     * @param array $data {
     *     @var string $user_2_alias
     * }
     * @return array
     */
    public function update($id, $data) {
        try {
            if (!isset($data['user_2_alias'])) {
                throw new Exception("user_2_alias is required for update");
            }

            $query = "UPDATE " . $this->table_name . " 
                     SET user_2_alias = :user_2_alias
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":id", $id);
            $stmt->bindValue(":user_2_alias", $data['user_2_alias']);
            
            if ($stmt->execute()) {
                return $this->get($id);
            }
            
            throw new Exception("Unable to update contact");
        } catch (PDOException $e) {
            error_log("SQL Error in update: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Delete a contact
     * DELETE /contacts/{id}
     * @param int $id
     * @return array
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":id", $id);
            
            if ($stmt->execute()) {
                return ["message" => "Contact deleted successfully"];
            }
            
            throw new Exception("Unable to delete contact");
        } catch (PDOException $e) {
            error_log("SQL Error in delete: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function contactExists($user1Id, $user2Phone) {
        try {
            $query = "SELECT id FROM " . $this->table_name . " 
                     WHERE user_1_id = :user_1_id AND user_2_phone = :user_2_phone";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":user_1_id", $user1Id);
            $stmt->bindValue(":user_2_phone", $user2Phone);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $e) {
            error_log("SQL Error in contactExists: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
} 