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
    
    public function get($id) {
        return $this->contact->get($id);
    }
    
    public function getUserContacts($user_id, $desire = null) {
        // Verify user exists
        $user = $this->user->get($user_id);
        if (!$user) {
            throw new Exception('User not found');
        }
        return $this->contact->getUserContacts($user_id, $desire);
    }
    
    public function create($data) {
        if (!isset($data['user_1_id']) || !isset($data['user_2_id'])) {
            throw new Exception('Both user IDs are required');
        }
        return $this->contact->create($data);
    }
    
    public function update($id, $data) {
        if (isset($data['user_2_alias']) && empty($data['user_2_alias'])) {
            throw new Exception('User 2 alias cannot be empty');
        }
        return $this->contact->update($id, $data);
    }
    
    public function delete($user_1_id, $user_2_id) {
        return $this->contact->delete($user_1_id, $user_2_id);
    }
} 