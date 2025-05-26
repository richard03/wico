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
$id = $segments[1] ?? null;

// Handle OPTIONS request for CORS
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Route the request
try {
    switch ($resource) {
        case 'users':
            require_once __DIR__ . '/controllers/UserController.php';
            $controller = new UserController();
            
            // Check if this is a request for user contacts
            if (isset($segments[2]) && $segments[2] === 'contacts') {
                require_once __DIR__ . '/controllers/ContactController.php';
                $contactController = new ContactController();
                
                if ($method === 'GET') {
                    $desire = $_GET['desire'] ?? null;
                    echo json_encode($contactController->getUserContacts($id, $desire));
                    break;
                }
            }
            
            switch ($method) {
                case 'GET':
                    if ($id) {
                        echo json_encode($controller->get($id));
                    } else {
                        echo json_encode($controller->getAll());
                    }
                    break;
                    
                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    echo json_encode($controller->create($data));
                    break;
                    
                case 'PUT':
                    if (!$id) {
                        throw new Exception('ID is required for PUT request');
                    }
                    $data = json_decode(file_get_contents('php://input'), true);
                    echo json_encode($controller->update($id, $data));
                    break;
                    
                case 'DELETE':
                    if (!$id) {
                        throw new Exception('ID is required for DELETE request');
                    }
                    echo json_encode($controller->delete($id));
                    break;
                    
                default:
                    throw new Exception('Method not allowed');
            }
            break;
            
        case 'contacts':
            require_once __DIR__ . '/controllers/ContactController.php';
            $controller = new ContactController();
            
            switch ($method) {
                case 'GET':
                    if ($id) {
                        echo json_encode($controller->get($id));
                    } else {
                        echo json_encode($controller->getAll());
                    }
                    break;
                    
                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    echo json_encode($controller->create($data));
                    break;
                    
                case 'PUT':
                    if (!$id) {
                        throw new Exception('ID is required for PUT request');
                    }
                    $data = json_decode(file_get_contents('php://input'), true);
                    echo json_encode($controller->update($id, $data));
                    break;
                    
                case 'DELETE':
                    if (!$id) {
                        throw new Exception('User ID is required for DELETE request');
                    }
                    $contact_id = $_GET['contact'] ?? null;
                    if (!$contact_id) {
                        throw new Exception('Contact ID is required for DELETE request');
                    }
                    echo json_encode($controller->delete($id, $contact_id));
                    break;
                    
                default:
                    throw new Exception('Method not allowed');
            }
            break;

        case 'desires':
            require_once __DIR__ . '/controllers/DesireController.php';
            $controller = new DesireController();
            
            switch ($method) {
                case 'GET':
                    if ($id) {
                        echo json_encode($controller->get($id));
                    } else {
                        $desire = $_GET['desire'] ?? null;
                        echo json_encode($controller->getAll($desire));
                    }
                    break;
                    
                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    echo json_encode($controller->create($data));
                    break;
                    
                case 'PUT':
                    if (!$id) {
                        throw new Exception('ID is required for PUT request');
                    }
                    $data = json_decode(file_get_contents('php://input'), true);
                    echo json_encode($controller->update($id, $data));
                    break;
                    
                case 'DELETE':
                    if (!$id) {
                        throw new Exception('ID is required for DELETE request');
                    }
                    echo json_encode($controller->delete($id));
                    break;
                    
                default:
                    throw new Exception('Method not allowed');
            }
            break;

        case 'feelings':
            require_once __DIR__ . '/controllers/FeelingController.php';
            $controller = new FeelingController();
            
            switch ($method) {
                case 'GET':
                    if ($id) {
                        echo json_encode($controller->get($id));
                    } else {
                        echo json_encode($controller->getAll());
                    }
                    break;
                    
                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    echo json_encode($controller->create($data));
                    break;
                    
                case 'PUT':
                    if (!$id) {
                        throw new Exception('ID is required for PUT request');
                    }
                    $data = json_decode(file_get_contents('php://input'), true);
                    echo json_encode($controller->update($id, $data));
                    break;
                    
                case 'DELETE':
                    if (!$id) {
                        throw new Exception('ID is required for DELETE request');
                    }
                    echo json_encode($controller->delete($id));
                    break;
                    
                default:
                    throw new Exception('Method not allowed');
            }
            break;
            
        case 'localization':
            require_once __DIR__ . '/controllers/LocalizationController.php';
            $controller = new LocalizationController();
            
            if ($method === 'GET') {
                if (!$id) {
                    throw new Exception('Language is required');
                }
                echo json_encode($controller->getAll($id));
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