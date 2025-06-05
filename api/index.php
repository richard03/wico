<?php
header('Content-Type: application/json');

// Get the request URI and remove the base path
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/api/';
$path = substr($request_uri, strpos($request_uri, $base_path) + strlen($base_path));

// Split the path into segments and remove query string
$path = strtok($path, '?');
$segments = explode('/', trim($path, '/'));

// Get the HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Basic routing
$resource = $segments[0] ?? '';

// Handle OPTIONS request for CORS
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Route the request
try {
    switch ($resource) {

        case 'desires':
            require_once __DIR__ . '/controllers/DesireController.php';
            $controller = new DesireController();

            // Routing
            $user_id = $segments[1] ?? null;

            switch ($method) {
                case 'GET':
                    $key = $_GET['key'] ?? null;
                    if (!$key) {
                        throw new Exception('Correct KEY is required for a GET request');
                    }
                    $user_id = $_GET['user_id'] ?? null;
                    $desire = $_GET['desire'] ?? null;
                    
                    if ($user_id) {
                        // get a desire by user_id
                        echo json_encode($controller->get($user_id, $key));
                    } else {
                        // get all records by desire
                        $limit = $_GET['limit'] ?? null;
                        if ($limit) {
                            echo json_encode($controller->getAll($desire, $key, (int)$limit));
                        } else {
                            echo json_encode($controller->getAll($desire, $key));
                        }
                    }
                    break;
                    
                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    $key = $data['key'] ?? null;
                    if (!$key) {
                        throw new Exception('Correct KEY is required for a POST request');
                    }
                    echo json_encode($controller->set($data, $key));
                    break;
                    
                case 'DELETE':
                    $key = $_GET['key'] ?? null;
                    if (!$key) {
                        throw new Exception('Correct KEY is required for a DELETE request');
                    }
                    $user_id = $_GET['user_id'] ?? null;
                    if (!$user_id) {
                        throw new Exception('User ID is required for a DELETE request');
                    }
                    echo json_encode($controller->delete($user_id, $key));
                    break;
                    
                default:
                    throw new Exception('Method not allowed');
            }
            break;

        case 'localization':
            require_once __DIR__ . '/controllers/LocalizationController.php';
            $controller = new LocalizationController();
            
            if ($method === 'GET') {
                $language = $segments[1] ?? null;
                if (!$language) {
                    throw new Exception('Language is required');
                }
                echo json_encode($controller->getAll($language));
            } else {
                throw new Exception('Method not allowed');
            }
            break;
            
        default:
            throw new Exception('Resource not found');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}