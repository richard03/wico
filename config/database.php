<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $secret = require __DIR__ . '/secret.php';
        $this->host = $secret['host'];
        $this->db_name = $secret['db_name'];
        $this->username = $secret['username'];
        $this->password = $secret['password'];
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->createTables();
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }

    private function createTables() {
        // Users table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS users (
            id BIGINT PRIMARY KEY,
            nickname VARCHAR(255) NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            phone VARCHAR(20) NULL,
            gps VARCHAR(255) NULL
        )");

        // Feelings table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS feelings (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT NOT NULL,
            feeling TEXT NOT NULL,
            time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");

        // Desires table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS desires (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT NOT NULL,
            desire TEXT NOT NULL,
            comment TEXT NULL,
            time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");

        // Contacts table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS contacts (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_1_id BIGINT NOT NULL,
            user_2_id BIGINT NOT NULL,
            user_2_alias VARCHAR(255) NULL,
            FOREIGN KEY (user_1_id) REFERENCES users(id),
            FOREIGN KEY (user_2_id) REFERENCES users(id)
        )");

        // Localization table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS localization (
            message_key VARCHAR(255) NOT NULL PRIMARY KEY,
            language VARCHAR(5) NOT NULL,
            message_text TEXT NOT NULL
        )");
    }
} 