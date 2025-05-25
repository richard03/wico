<?php
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/User.php';

class ContactController {
    private $contact;
    private $user;
    
    public function __construct() {
        $this->contact = new Contact();
        $this->user = new User();
    }
    
    public function getAll() {
        return $this->contact->getAll();
    }
    
    public function get($user_id) {
        // Verify user exists
        $user = $this->user->get($user_id);
        if (!$user) {
            throw new Exception('User not found');
        }
        return $this->contact->get($user_id);
    }
    
    public function create($data) {
        if (!isset($data['user_1']) || !isset($data['user_2'])) {
            throw new Exception('Both users are required');
        }
        
        // Verify both users exist
        $user1 = $this->user->get($data['user_1']);
        if (!$user1) {
            throw new Exception('User 1 not found');
        }
        
        $user2 = $this->user->get($data['user_2']);
        if (!$user2) {
            throw new Exception('User 2 not found');
        }
        
        return $this->contact->create($data);
    }
    
    public function update($user_id, $data) {
        if (!isset($data['user_2'])) {
            throw new Exception('User 2 is required');
        }
        
        // Verify both users exist
        $user1 = $this->user->get($user_id);
        if (!$user1) {
            throw new Exception('User 1 not found');
        }
        
        $user2 = $this->user->get($data['user_2']);
        if (!$user2) {
            throw new Exception('User 2 not found');
        }
        
        return $this->contact->update($user_id, $data);
    }
    
    public function delete($user_id) {
        // Verify user exists
        $user = $this->user->get($user_id);
        if (!$user) {
            throw new Exception('User not found');
        }
        return $this->contact->delete($user_id);
    }
} 