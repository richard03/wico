<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $user;
    
    public function __construct() {
        $this->user = new User();
    }
    
    /**
     * Get all users
     * GET /users
     * @return array
     */
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
    
    /**
     * Get a user by ID
     * GET /users/{id}
     * @param int $id
     * @return array
     */
    public function get($id) {
        return $this->user->get($id);
    }
    
    /**
     * Create a new user
     * POST /users
     * @param array $data {
     *     @var string $email
     *     @var string $phone
     *     @var string $nickname
     *     @var string $password
     *     @var string $timezone
     * }
     * @return array
     */
    public function create($data) {
        if (!isset($data['email'])) {
            throw new Exception('Email is required');
        }
        return $this->user->create($data);
    }
    
    /**
     * Update a user
     * PUT /users/{id}
     * @param int $id
     * @param array $data {
     *     @var string $nickname
     *     @var string $password
     *     @var string $timezone
     * }
     * @return array
     */
    public function update($id, $data) {
        if (isset($data['nickname']) && empty($data['nickname'])) {
            throw new Exception('Nickname cannot be empty');
        }
        return $this->user->update($id, $data);
    }
    
    /**
     * Delete a user
     * DELETE /users/{id}
     * @param int $id
     * @return array
     */
    public function delete($id) {
        return $this->user->delete($id);
    }
} 