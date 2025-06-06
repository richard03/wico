<?php

$dev = true; // developers mode - all responses are slowed randomly

// Start output buffering
ob_start();

// Process the request
try {
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
    switch ($resource) {
        case 'desires':
            require_once __DIR__ . '/controllers/DesireController.php';
            $controller = new DesireController();
            
            switch ($method) {
                case 'GET':
                    $key = $_GET['key'] ?? null;
                    if (!$key) {
                        throw new Exception('Correct KEY is required for a GET request');
                    }
                    $desire = $_GET['desire'] ?? null;
                    echo json_encode($controller->get($key));
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
                    echo json_encode($controller->delete($key));
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

// Get the response content
$response = ob_get_clean();

// Add random delay in developer mode to observe racing conditions
if ($dev) {
    $delay = rand(0, 3); // random delay between 0 and 2 seconds
    $microdelay = rand(0, 999); // more precise random delay between 0 and 999 microseconds
    sleep($delay); // non-blocking delay
    usleep($microdelay); // blocking micro delay
}

// Send headers and response
header('Content-Type: application/json');
echo $response;