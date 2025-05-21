<?php
require_once __DIR__ . '/../config/database.php';

class BaseModel {
    protected $conn;
    protected $table_name;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    protected function create($data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $query = "INSERT INTO " . $this->table_name . " 
                 (" . implode(', ', $fields) . ") 
                 VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute(array_values($data));
    }

    protected function read($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function update($id, $data) {
        $fields = array_keys($data);
        $set = implode(' = ?, ', $fields) . ' = ?';
        
        $query = "UPDATE " . $this->table_name . " 
                 SET " . $set . " 
                 WHERE id = ?";

        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->conn->prepare($query);
        return $stmt->execute($values);
    }

    protected function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    protected function getAll($conditions = [], $orderBy = null) {
        $query = "SELECT * FROM " . $this->table_name;
        
        if (!empty($conditions)) {
            $where = [];
            $values = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
                $values[] = $value;
            }
            $query .= " WHERE " . implode(' AND ', $where);
        }

        if ($orderBy) {
            $query .= " ORDER BY " . $orderBy;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($values ?? []);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 