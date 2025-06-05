<?php
require_once __DIR__ . '/../../config/database.php';

class LocalizationController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->createTables();
    }

    private function createTables() {
        try {

            // Localization table
            $this->conn->exec("CREATE TABLE IF NOT EXISTS localization (
                message_key VARCHAR(255) NOT NULL PRIMARY KEY,
                language VARCHAR(5) NOT NULL,
                message_text TEXT NOT NULL
            )");
        } catch(PDOException $e) {
            error_log("Error creating tables: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Get all localizations
     * GET /localizations
     * @param string $language
     * @return array
     */
    public function getAll($language) {
        if (empty($language)) {
            throw new Exception('Language is required');
        }
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