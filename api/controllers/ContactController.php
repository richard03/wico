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
    
    /**
     * Get all contacts
     * GET /contacts
     * @return array
     */
    public function getAll() {
        return $this->contact->getAll();
    }

    /**
     * Get a contact by ID
     * GET /contacts/{id}
     * @param int $id
     * @return array
     */
    public function get($id) {
        return $this->contact->get($id);
    }

    /**
     * Get all contacts of a user
     * GET /contacts/user/{user_id}
     * @param int $user_id
     * @return array
     */
    public function getUserContacts($user_id, $desire = null) {
        // Verify user exists
        $user = $this->user->get($user_id);
        if (!$user) {
            throw new Exception('User not found');
        }
        return $this->contact->getUserContacts($user_id, $desire);
    }
    
    /**
     * Create a new contact
     * POST /contacts
     * @param array $data {
     *     @var int $user_1_id
     *     @var string $user_2_phone
     *     @var string $user_2_alias
     * }
     * @return array
     */
    public function create($data) {
        if (!isset($data['user_1_id']) || !isset($data['user_2_phone'])) {
            throw new Exception('Both user_1_id and user_2_phone are required');
        }
        return $this->contact->create($data);
    }
    
    /**
     * Update a contact
     * PUT /contacts/{id}
     * @param int $user_1_id
     * @param array $data {
     *     @var string $user_2_alias
     * }
     * @return array
     */
    public function update($id, $data) {
        if (isset($data['user_2_alias']) && empty($data['user_2_alias'])) {
            throw new Exception('User 2 alias cannot be empty');
        }
        return $this->contact->update($id, $data);
    }
    
    /**
     * Delete a contact
     * DELETE /contacts/{id}
     * @param int $user_1_id
     * @param string $user_2_phone
     * @return array
     */
    public function delete($user_1_id, $user_2_phone) {
        return $this->contact->delete($user_1_id, $user_2_phone);
    }
} 