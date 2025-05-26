<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $user;
    
    public function __construct() {
        $this->user = new User();
    }
    
    public function getAll() {
        // Get query parameters from $_GET
        $email = isset($_GET['email']) ? urldecode($_GET['email']) : null;
        $phone = isset($_GET['phone']) ? urldecode($_GET['phone']) : null;
        
        if ($email !== null || $phone !== null) {
            $result = $this->user->findByEmailOrPhone($email, $phone);
            if ($result === null) {
                return [];
            }
            return $result;
        }
        
        return $this->user->getAll();
    }
    
    public function get($id) {
        return $this->user->get($id);
    }
    
    public function create($data) {
        if (!isset($data['email'])) {
            throw new Exception('Email is required');
        }
        return $this->user->create($data);
    }
    
    public function update($id, $data) {
        if (isset($data['nickname']) && empty($data['nickname'])) {
            throw new Exception('Nickname cannot be empty');
        }
        return $this->user->update($id, $data);
    }
    
    public function delete($id) {
        return $this->user->delete($id);
    }
} 