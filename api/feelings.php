<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Feeling.php';
require_once __DIR__ . '/../utils/ApiResponse.php';

$user = new User();
$feeling = new Feeling();
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
        // Create new feeling
        if (!isset($data['feeling'])) {
            ApiResponse::error('Missing feeling field');
        }

        $data['user_id'] = $currentUser['id'];
        $result = $feeling->createFeeling($data);
        if ($result) {
            ApiResponse::success(null, 'Feeling recorded successfully');
        } else {
            ApiResponse::error('Failed to record feeling');
        }
        break;

    case 'GET':
        // Get user's feelings
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

        if ($startDate && $endDate) {
            $feelings = $feeling->getFeelingsByDateRange($currentUser['id'], $startDate, $endDate);
        } else {
            $feelings = $feeling->getUserFeelings($currentUser['id'], $limit);
        }

        ApiResponse::success($feelings);
        break;

    case 'DELETE':
        // Delete a feeling
        if (!isset($_GET['id'])) {
            ApiResponse::error('Missing feeling ID');
        }

        $feelingId = (int)$_GET['id'];
        $result = $feeling->delete($feelingId);
        if ($result) {
            ApiResponse::success(null, 'Feeling deleted successfully');
        } else {
            ApiResponse::error('Failed to delete feeling');
        }
        break;

    default:
        ApiResponse::error('Method not allowed', 405);
} 