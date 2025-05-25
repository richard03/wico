<?php
require_once __DIR__ . '/../models/Desire.php';
require_once __DIR__ . '/../models/User.php';

class DesireController {
    private $desire;
    private $user;
    
    public function __construct() {
        $this->desire = new Desire();
        $this->user = new User();
    }
    
    public function getAll() {
        return $this->desire->getAll();
    }
    
    public function get($user_id) {
        // Verify user exists
        $user = $this->user->get($user_id);
        if (!$user) {
            throw new Exception('User not found');
        }
        return $this->desire->get($user_id);
    }
    
    public function create($data) {
        if (!isset($data['desire'])) {
            throw new Exception('Desire is required');
        }
        if (!isset($data['user_id'])) {
            throw new Exception('User ID is required');
        }
        
        // Verify user exists
        $user = $this->user->get($data['user_id']);
        if (!$user) {
            throw new Exception('User not found');
        }
        
        return $this->desire->create($data);
    }
    
    public function update($user_id, $data) {
        if (!isset($data['desire'])) {
            throw new Exception('Desire is required');
        }
        
        // Verify user exists
        $user = $this->user->get($user_id);
        if (!$user) {
            throw new Exception('User not found');
        }
        
        return $this->desire->update($user_id, $data);
    }
    
    public function delete($user_id) {
        // Verify user exists
        $user = $this->user->get($user_id);
        if (!$user) {
            throw new Exception('User not found');
        }
        return $this->desire->delete($user_id);
    }
} 