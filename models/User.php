<?php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {
    protected $table_name = 'users';

    public function createUser($data) {
        // Generate a secure session token if not provided
        if (!isset($data['session_token'])) {
            $data['session_token'] = bin2hex(random_bytes(32));
        }
        
        // Set session expiry to 30 days from now if not provided
        if (!isset($data['session_token_expiry'])) {
            $data['session_token_expiry'] = date('Y-m-d H:i:s', strtotime('+30 days'));
        }

        return $this->create($data);
    }

    public function getUserBySessionToken($token) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE session_token = ? AND session_token_expiry > NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByGoogleId($googleId) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE google_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$googleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateLastLogin($userId) {
        $query = "UPDATE " . $this->table_name . " 
                 SET last_login = CURRENT_TIMESTAMP 
                 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$userId]);
    }

    public function invalidateSessionToken($userId) {
        $query = "UPDATE " . $this->table_name . " 
                 SET session_token = NULL, session_token_expiry = NULL 
                 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$userId]);
    }
} 