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
    
    public function getAll($user_id = null, $desire = null) {
        return $this->desire->getAll($user_id, $desire);
    }
    
    public function get($id) {
        return $this->desire->get($id);
    }
    
    public function create($data) {
        if (!isset($data['user_id']) || !isset($data['desire'])) {
            throw new Exception('User ID and desire are required');
        }
        return $this->desire->create($data);
    }
    
    public function update($id, $data) {
        if (!isset($data['desire'])) {
            throw new Exception('Desire is required');
        }
        return $this->desire->update($id, $data);
    }
    
    public function delete($id) {
        return $this->desire->delete($id);
    }
} 