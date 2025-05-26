<?php
require_once __DIR__ . '/../../config/database.php';

class Localization {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll($language) {
        try {
            $query = "SELECT message_key, message_text 
                     FROM localization 
                     WHERE language = :language";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":language", $language);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in getAll: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
} 