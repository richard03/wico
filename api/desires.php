<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Desire.php';
require_once __DIR__ . '/../utils/ApiResponse.php';

$user = new User();
$desire = new Desire();
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
        // Create new desire
        if (!isset($data['desire'])) {
            ApiResponse::error('Missing desire field');
        }

        $data['user_id'] = $currentUser['id'];
        $result = $desire->createDesire($data);
        if ($result) {
            ApiResponse::success(null, 'Desire recorded successfully');
        } else {
            ApiResponse::error('Failed to record desire');
        }
        break;

    case 'GET':
        // Get user's desires
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

        if ($startDate && $endDate) {
            $desires = $desire->getDesiresByDateRange($currentUser['id'], $startDate, $endDate);
        } else {
            $desires = $desire->getUserDesires($currentUser['id'], $limit);
        }

        ApiResponse::success($desires);
        break;

    case 'DELETE':
        // Delete a desire
        if (!isset($_GET['id'])) {
            ApiResponse::error('Missing desire ID');
        }

        $desireId = (int)$_GET['id'];
        $result = $desire->delete($desireId);
        if ($result) {
            ApiResponse::success(null, 'Desire deleted successfully');
        } else {
            ApiResponse::error('Failed to delete desire');
        }
        break;

    default:
        ApiResponse::error('Method not allowed', 405);
} 