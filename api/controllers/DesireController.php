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
    
    /**
     * Get all desires
     * GET /desires
     * @param int $user_id
     * @param string $desire
     * @return array
     */
    public function getAll($user_id = null, $desire = null) {
        return $this->desire->getAll($user_id, $desire);
    }
    
    /**
     * Get a desire by ID
     * GET /desires/{id}
     * @param int $id
     * @return array
     */
    public function get($id) {
        return $this->desire->get($id);
    }
    
    /**
     * Create a new desire
     * POST /desires
     * @param array $data {
     *     @var int $user_id
     *     @var string $desire
     *     @var string $comment
     * }
     * @return array
     */
    public function create($data) {
        if (!isset($data['user_id']) || !isset($data['desire'])) {
            throw new Exception('User ID and desire are required');
        }
        return $this->desire->create($data);
    }
    
    /**
     * Update a desire
     * PUT /desires/{id}
     * @param int $id
     * @param array $data {
     *     @var string $desire
     *     @var string $comment
     * }
     * @return array
     */
    public function update($id, $data) {
        if (!isset($data['desire'])) {
            throw new Exception('Desire is required');
        }
        return $this->desire->update($id, $data);
    }
    
    /**
     * Delete a desire
     * DELETE /desires/{id}
     * @param int $id
     * @return array
     */
    public function delete($id) {
        return $this->desire->delete($id);
    }
} 