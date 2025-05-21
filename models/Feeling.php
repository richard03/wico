<?php
require_once __DIR__ . '/BaseModel.php';

class Feeling extends BaseModel {
    protected $table_name = 'feelings';

    public function createFeeling($data) {
        return $this->create($data);
    }

    public function getUserFeelings($userId, $limit = null) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE user_id = ? 
                 ORDER BY time DESC";
        
        if ($limit) {
            $query .= " LIMIT ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$userId, $limit]);
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$userId]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFeelingsByDateRange($userId, $startDate, $endDate) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE user_id = ? 
                 AND time BETWEEN ? AND ? 
                 ORDER BY time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, $startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 