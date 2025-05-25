<?php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {
    protected $table_name = 'users';

    public function generateKey() {
        return bin2hex(random_bytes(32));
    }

    public function createUser($data) {
        // Generate a secure session token if not provided
        if (!isset($data['key'])) {
            $data['key'] = $this->generateKey();
        }
        
        // Set session expiry to 30 days from now if not provided
        if (!isset($data['session_token_expiry'])) {
            $data['session_token_expiry'] = date('Y-m-d H:i:s', strtotime('+30 days'));
        }

        return $this->create($data);
    }

    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateLastLogin($userId) {
        $query = "UPDATE " . $this->table_name . " 
                 SET last_login = CURRENT_TIMESTAMP 
                 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$userId]);
    }
} 