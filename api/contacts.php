<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../utils/ApiResponse.php';

$user = new User();
$contact = new Contact();
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

if (!$token) {
    ApiResponse::error('Unauthorized', 401);
}

$currentUser = $user->getUserBySessionToken($token);
if (!$currentUser) {
    ApiResponse::error('Invalid session token', 401);
}

switch ($method) {
    case 'POST':
        // Add new contact
        if (!isset($data['user_id'])) {
            ApiResponse::error('Missing user_id field');
        }

        $result = $contact->createContact($currentUser['id'], $data['user_id']);
        if ($result) {
            ApiResponse::success(null, 'Contact added successfully');
        } else {
            ApiResponse::error('Failed to add contact or contact already exists');
        }
        break;

    case 'GET':
        // Get user's contacts
        $contacts = $contact->getUserContacts($currentUser['id']);
        ApiResponse::success($contacts);
        break;

    case 'DELETE':
        // Remove a contact
        if (!isset($_GET['user_id'])) {
            ApiResponse::error('Missing user_id parameter');
        }

        $contactUserId = (int)$_GET['user_id'];
        $result = $contact->removeContact($currentUser['id'], $contactUserId);
        if ($result) {
            ApiResponse::success(null, 'Contact removed successfully');
        } else {
            ApiResponse::error('Failed to remove contact');
        }
        break;

    default:
        ApiResponse::error('Method not allowed', 405);
} 