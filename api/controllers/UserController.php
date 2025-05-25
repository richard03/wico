<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $user;
    
    public function __construct() {
        $this->user = new User();
    }
    
    public function getAll() {
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
        return $this->user->update($id, $data);
    }
    
    public function delete($id) {
        return $this->user->delete($id);
    }
} 