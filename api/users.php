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
        if (!isset($data['google_id']) || !isset($data['email'])) {
            ApiResponse::error('Missing required fields');
        }

        $existingUser = $user->getUserByGoogleId($data['google_id']);
        if ($existingUser) {
            // Update last login
            $user->updateLastLogin($existingUser['id']);
            ApiResponse::success($existingUser, 'User logged in successfully');
        }

        $result = $user->createUser($data);
        if ($result) {
            $newUser = $user->getUserByGoogleId($data['google_id']);
            ApiResponse::success($newUser, 'User created successfully');
        } else {
            ApiResponse::error('Failed to create user');
        }
        break;

    case 'GET':
        if (!$token) {
            ApiResponse::error('Unauthorized', 401);
        }

        $currentUser = $user->getUserBySessionToken($token);
        if (!$currentUser) {
            ApiResponse::error('Invalid session token', 401);
        }

        // Get user profile
        ApiResponse::success($currentUser);
        break;

    case 'PUT':
        if (!$token) {
            ApiResponse::error('Unauthorized', 401);
        }

        $currentUser = $user->getUserBySessionToken($token);
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
        if (!$token) {
            ApiResponse::error('Unauthorized', 401);
        }

        $currentUser = $user->getUserBySessionToken($token);
        if (!$currentUser) {
            ApiResponse::error('Invalid session token', 401);
        }

        // Logout user
        $result = $user->invalidateSessionToken($currentUser['id']);
        if ($result) {
            ApiResponse::success(null, 'Logged out successfully');
        } else {
            ApiResponse::error('Failed to logout');
        }
        break;

    default:
        ApiResponse::error('Method not allowed', 405);
} 