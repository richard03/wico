<?php
require_once __DIR__ . '/BaseModel.php';

class Contact extends BaseModel {
    protected $table_name = 'contacts';

    public function createContact($user1Id, $user2Id) {
        // Check if contact already exists
        if ($this->contactExists($user1Id, $user2Id)) {
            return false;
        }

        $data = [
            'user_1' => $user1Id,
            'user_2' => $user2Id
        ];

        return $this->create($data);
    }

    public function contactExists($user1Id, $user2Id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE (user_1 = ? AND user_2 = ?) 
                 OR (user_1 = ? AND user_2 = ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user1Id, $user2Id, $user2Id, $user1Id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function getUserContacts($userId) {
        $query = "SELECT u.* FROM users u 
                 INNER JOIN " . $this->table_name . " c 
                 ON (c.user_1 = ? AND c.user_2 = u.id) 
                 OR (c.user_2 = ? AND c.user_1 = u.id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeContact($user1Id, $user2Id) {
        $query = "DELETE FROM " . $this->table_name . " 
                 WHERE (user_1 = ? AND user_2 = ?) 
                 OR (user_1 = ? AND user_2 = ?)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user1Id, $user2Id, $user2Id, $user1Id]);
    }
} 