<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/ApiResponse.php';

$user = new User();
$method = $_SERVER['REQUEST_METHOD'];

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($method === 'OPTIONS') {
    exit(0);
}

// Get authorization header
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// Extract token if present
$token = null;
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
}

switch ($method) {
    case 'POST':
        // Create new user
        if (!isset($data['email'])) {
            ApiResponse::error('Missing required fields');
        }

        $existingUser = $user->getUserByEmail($data['email']);
        if ($existingUser) {
            // Update last login
            $user->updateLastLogin($existingUser['id']);
            ApiResponse::success($existingUser, 'User logged in successfully');
        }

        $result = $user->createUser($data);
        if ($result) {
            $newUser = $user->getUserByEmail($data['email']);
            ApiResponse::success($newUser, 'User created successfully');
        } else {
            ApiResponse::error('Failed to create user');
        }
        break;

    case 'GET':
        if (!$id) {
            ApiResponse::error('Unauthorized', 401);
        }

        $currentUser = $user->getUserById($id);
        if (!$currentUser) {
            ApiResponse::error('Invalid session token', 401);
        }

        // Get user profile
        ApiResponse::success($currentUser);
        break;

    case 'PUT':
        if (!$id) {
            ApiResponse::error('Unauthorized', 401);
        }

        $currentUser = $user->getUserById($id);
        if (!$currentUser) {
            ApiResponse::error('Invalid session token', 401);
        }

        // Update user profile
        $result = $user->update($currentUser['id'], $data);
        if ($result) {
            $updatedUser = $user->read($currentUser['id']);
            ApiResponse::success($updatedUser, 'Profile updated successfully');
        } else {
            ApiResponse::error('Failed to update profile');
        }
        break;

    case 'DELETE':
        if (!$id) {
            ApiResponse::error('Unauthorized', 401);
        }

        $currentUser = $user->getUserById($id);
        if (!$currentUser) {
            ApiResponse::error('Invalid session token', 401);
        }
        break;

    default:
        ApiResponse::error('Method not allowed', 405);
} 