<?php
class Database {
    private $host = "localhost";
    private $db_name = "wico_db";
    private $username = "root";
    private $password = "";
    private $conn;

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
            id INT AUTO_INCREMENT PRIMARY KEY,
            `key` BIGINT NOT NULL,
            name VARCHAR(255) NULL,
            google_id VARCHAR(255) NOT NULL UNIQUE,
            username VARCHAR(255) NULL UNIQUE,
            profile_picture_url TEXT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            session_token VARCHAR(255) NULL UNIQUE,
            session_token_expiry TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            phone VARCHAR(20) NULL,
            gps VARCHAR(255) NULL
        )");

        // Feelings table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS feelings (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            `key` BIGINT NOT NULL,
            user_id INT NOT NULL,
            feeling TEXT NOT NULL,
            time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");

        // Desires table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS desires (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            `key` BIGINT NOT NULL,
            user_id INT NOT NULL,
            desire TEXT NOT NULL,
            comment TEXT NULL,
            time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");

        // Contacts table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS contacts (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_1 INT NOT NULL,
            user_2 INT NOT NULL,
            FOREIGN KEY (user_1) REFERENCES users(id),
            FOREIGN KEY (user_2) REFERENCES users(id)
        )");
    }
} 