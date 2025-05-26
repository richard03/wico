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
        if (!isset($data['user_1_id']) || !isset($data['user_2_id'])) {
            throw new Exception('Both users are required');
        }
        
        // Verify both users exist
        $user1 = $this->user->get($data['user_1_id']);
        if (!$user1) {
            throw new Exception('User 1 not found');
        }
        
        $user2 = $this->user->get($data['user_2_id']);
        if (!$user2) {
            throw new Exception('User 2 not found');
        }
        
        return $this->contact->create($data);
    }
    
    public function update($contact_id, $data) {
        if (!isset($data['user_1_id'])) {
            throw new Exception('User 1 is required');
        }
        if (!isset($data['user_2_id'])) {
            throw new Exception('User 2 is required');
        }
        
        // Verify both users exist
        $user1 = $this->user->get($data['user_1_id']);
        if (!$user1) {
            throw new Exception('User 1 not found');
        }
        
        $user2 = $this->user->get($data['user_2_id']);
        if (!$user2) {
            throw new Exception('User 2 not found');
        }
        
        return $this->contact->update($contact_id, $data);
    }
    
    public function delete($contact_id) {
        // Verify contact exists
        $contact = $this->contact->get($contact_id);
        if (!$contact) {
            throw new Exception('Contact not found');
        }
        return $this->contact->delete($contact_id);
    }
} 