<?php
session_start();

// Define routes
$routes = [
    '/' => ['controller' => 'HomeController', 'action' => 'index'],
    '/home' => ['controller' => 'HomeController', 'action' => 'index'],
    '/login' => ['controller' => 'AuthController', 'action' => 'showLogin'],
    '/register' => ['controller' => 'AuthController', 'action' => 'showRegister'],
    '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    '/dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
    '/profile' => ['controller' => 'ProfileController', 'action' => 'index'],
    '/about' => ['controller' => 'HomeController', 'action' => 'about'],
];

// Handle POST requests
$postRoutes = [
    '/login' => ['controller' => 'AuthController', 'action' => 'showLogin'],
    '/register' => ['controller' => 'AuthController', 'action' => 'showRegister'],
    '/profile/update' => ['controller' => 'ProfileController', 'action' => 'updateProfile'],
    '/profile/change-password' => ['controller' => 'ProfileController', 'action' => 'changePassword'],
    '/profile/upload-avatar' => ['controller' => 'ProfileController', 'action' => 'uploadAvatar'],
];

// Get current URL path and remove base directory
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/xegoo';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
if ($path === '' || $path === '/') {
    $path = '/';
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Debug logging
error_log("Router - Method: $method, Path: $path");

// Choose appropriate routes array
$currentRoutes = ($method === 'POST') ? $postRoutes : $routes;

// Match route
$routeFound = false;
$params = [];

foreach ($currentRoutes as $route => $handler) {
    // Convert route to regex pattern for dynamic parameters
    $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
    $pattern = '#^' . $pattern . '$#';
    
    if (preg_match($pattern, $path, $matches)) {
        $routeFound = true;
        
        // Extract parameters
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        
        error_log("Router - Route matched: $route");
        
        // Load controller and call action
        $controllerName = $handler['controller'];
        $actionName = $handler['action'];
        
        $controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';
        
        if (!file_exists($controllerFile)) {
            error_log("Router - Controller file not found: $controllerFile");
            http_response_code(500);
            echo "Internal Server Error: Controller not found";
            exit;
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerName)) {
            error_log("Router - Controller class not found: $controllerName");
            http_response_code(500);
            echo "Internal Server Error: Controller class not found";
            exit;
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $actionName)) {
            error_log("Router - Action method not found: $actionName in $controllerName");
            http_response_code(500);
            echo "Internal Server Error: Action method not found";
            exit;
        }
        
        // Call the controller action with parameters
        call_user_func_array([$controller, $actionName], $params);
        break;
    }
}

// If no route found, show 404 page
if (!$routeFound) {
    error_log("Router - No route found for: $method $path");
    http_response_code(404);
    
    // Check if 404 error page exists
    $errorFile = __DIR__ . '/views/errors/404.php';
    if (file_exists($errorFile)) {
        include $errorFile;
    } else {
        // Fallback 404 page
        echo "<!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>404 - Không tìm thấy trang</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                h1 { color: #e74c3c; }
                a { color: #3498db; text-decoration: none; }
                a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <h1>404 - Không tìm thấy trang</h1>
            <p>Trang bạn đang tìm kiếm không tồn tại.</p>
            <a href='" . BASE_URL . "'>Về trang chủ</a>
        </body>
        </html>";
    }
}
?>
