<?php
session_start();

// Define GET routes
$routes = [
    '/' => ['controller' => 'HomeController', 'action' => 'index'],
    '/home' => ['controller' => 'HomeController', 'action' => 'index'],
    '/search' => ['controller' => 'SearchController', 'action' => 'index'],
    '/search/cities' => ['controller' => 'SearchController', 'action' => 'cities'],
    '/search/trip-details/{id}' => ['controller' => 'SearchController', 'action' => 'tripDetails'],
    '/booking/{id}' => ['controller' => 'BookingController', 'action' => 'show'],
    '/booking/confirm' => ['controller' => 'BookingController', 'action' => 'confirm'],
    '/booking/success/{id}' => ['controller' => 'BookingController', 'action' => 'success'],
    '/login' => ['controller' => 'AuthController', 'action' => 'showLogin'],
    '/register' => ['controller' => 'AuthController', 'action' => 'showRegister'],
    '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    '/dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
    '/admin' => ['controller' => 'AdminController', 'action' => 'index'],
    '/profile' => ['controller' => 'ProfileController', 'action' => 'index'],
    '/loyalty' => ['controller' => 'LoyaltyController', 'action' => 'index'],
    '/about' => ['controller' => 'HomeController', 'action' => 'about'],
    '/booking-guide' => ['controller' => 'HomeController', 'action' => 'bookingGuide'],
    
    '/my-tickets' => ['controller' => 'MyTicketsController', 'action' => 'index'],
    '/my-tickets/history' => ['controller' => 'MyTicketsController', 'action' => 'history'],
    '/my-tickets/detail/{id}' => ['controller' => 'MyTicketsController', 'action' => 'detail'],
    
    // Vehicle routes
    '/vehicles' => ['controller' => 'VehicleController', 'action' => 'index'],
    '/vehicles/list' => ['controller' => 'VehicleController', 'action' => 'list'],
    '/vehicles/create' => ['controller' => 'VehicleController', 'action' => 'create'],
    '/vehicles/reports' => ['controller' => 'VehicleController', 'action' => 'reports'],
    '/vehicles/maintenance' => ['controller' => 'VehicleController', 'action' => 'maintenance'],
    '/vehicles/{id}' => ['controller' => 'VehicleController', 'action' => 'show'],
    '/vehicles/{id}/edit' => ['controller' => 'VehicleController', 'action' => 'edit'],
    '/vehicles/{id}/delete' => ['controller' => 'VehicleController', 'action' => 'delete'],
    
    // Route management routes
    '/routes' => ['controller' => 'RouteController', 'action' => 'index'],
    '/routes/list' => ['controller' => 'RouteController', 'action' => 'list'],
    '/routes/create' => ['controller' => 'RouteController', 'action' => 'create'],
    '/routes/map' => ['controller' => 'RouteController', 'action' => 'map'],
    '/routes/reports' => ['controller' => 'RouteController', 'action' => 'reports'],
    '/routes/{id}' => ['controller' => 'RouteController', 'action' => 'show'],
    '/routes/{id}/edit' => ['controller' => 'RouteController', 'action' => 'edit'],
    '/routes/{id}/delete' => ['controller' => 'RouteController', 'action' => 'delete'],
    
    // Schedule routes
    '/schedules' => ['controller' => 'ScheduleController', 'action' => 'index'],
    '/schedules/list' => ['controller' => 'ScheduleController', 'action' => 'list'],
    '/schedules/create' => ['controller' => 'ScheduleController', 'action' => 'create'],
    '/schedules/calendar' => ['controller' => 'ScheduleController', 'action' => 'calendar'],
    '/schedules/generate-trips' => ['controller' => 'ScheduleController', 'action' => 'generateTrips'],
    '/schedules/{id}' => ['controller' => 'ScheduleController', 'action' => 'show'],
    '/schedules/{id}/edit' => ['controller' => 'ScheduleController', 'action' => 'edit'],
    '/schedules/{id}/delete' => ['controller' => 'ScheduleController', 'action' => 'delete'],
    
    // Trip routes
    '/trips' => ['controller' => 'TripController', 'action' => 'index'],
    '/trips/list' => ['controller' => 'TripController', 'action' => 'list'],
    '/trips/tracking' => ['controller' => 'TripController', 'action' => 'tracking'],
    '/trips/export' => ['controller' => 'TripController', 'action' => 'export'],
    '/trips/statistics' => ['controller' => 'TripController', 'action' => 'statistics'],
    '/trips/{id}' => ['controller' => 'TripController', 'action' => 'show'],
    '/trips/{id}/delete' => ['controller' => 'TripController', 'action' => 'delete'],
    
    // User routes
    '/users' => ['controller' => 'UserController', 'action' => 'index'],
    '/users/list' => ['controller' => 'UserController', 'action' => 'list'],
    '/users/create' => ['controller' => 'UserController', 'action' => 'create'],
    '/users/roles' => ['controller' => 'UserController', 'action' => 'roles'],
    '/users/export' => ['controller' => 'UserController', 'action' => 'export'],
    '/users/show/{id}' => ['controller' => 'UserController', 'action' => 'show'],
    '/users/edit/{id}' => ['controller' => 'UserController', 'action' => 'edit'],
    '/users/delete/{id}' => ['controller' => 'UserController', 'action' => 'delete'],
    '/users/restore/{id}' => ['controller' => 'UserController', 'action' => 'restore'],
    
    // Price routes
    '/prices' => ['controller' => 'PriceController', 'action' => 'index'],
    '/prices/list' => ['controller' => 'PriceController', 'action' => 'list'],
    '/prices/create' => ['controller' => 'PriceController', 'action' => 'create'],
    '/prices/calculator' => ['controller' => 'PriceController', 'action' => 'calculator'],
    '/prices/export' => ['controller' => 'PriceController', 'action' => 'export'],
    '/prices/search' => ['controller' => 'PriceController', 'action' => 'search'],
    '/prices/{id}' => ['controller' => 'PriceController', 'action' => 'show'],
    '/prices/{id}/edit' => ['controller' => 'PriceController', 'action' => 'edit'],
    '/prices/{id}/delete' => ['controller' => 'PriceController', 'action' => 'delete'],
    
    // Payment routes
    '/payment' => ['controller' => 'PaymentController', 'action' => 'show'],
    '/payment/cancel' => ['controller' => 'PaymentController', 'action' => 'cancel'],
    '/payment/release-seats' => ['controller' => 'PaymentController', 'action' => 'releaseSeats'],
    
    '/payment/momo/return' => ['controller' => 'MoMoController', 'action' => 'handleReturn'],
    '/payment/vnpay/return' => ['controller' => 'VNPayController', 'action' => 'handleReturn'],
    
    // Payment gateway callback routes
    '/payment/momo/callback' => ['controller' => 'MoMoController', 'action' => 'callback'],
    '/payment/vnpay/callback' => ['controller' => 'VNPayController', 'action' => 'callback'],
    
    // API routes for promotions and loyalty
    '/api/promotions/active' => ['controller' => 'PromotionController', 'action' => 'getActive'],
    '/api/loyalty/points' => ['controller' => 'LoyaltyController', 'action' => 'getPoints'],
];

// Define POST routes
$postRoutes = [
    '/login' => ['controller' => 'AuthController', 'action' => 'showLogin'],
    '/register' => ['controller' => 'AuthController', 'action' => 'showRegister'],
    '/search/api' => ['controller' => 'SearchController', 'action' => 'api'],
    '/booking/process' => ['controller' => 'BookingController', 'action' => 'process'],
    '/booking/complete' => ['controller' => 'BookingController', 'action' => 'complete'],
    '/profile/update' => ['controller' => 'ProfileController', 'action' => 'updateProfile'],
    '/profile/change-password' => ['controller' => 'ProfileController', 'action' => 'changePassword'],
    '/profile/upload-avatar' => ['controller' => 'ProfileController', 'action' => 'uploadAvatar'],
    
    // Vehicle POST routes
    '/vehicles/store' => ['controller' => 'VehicleController', 'action' => 'store'],
    '/vehicles/{id}/update' => ['controller' => 'VehicleController', 'action' => 'update'],
    
    // Route POST routes
    '/routes/store' => ['controller' => 'RouteController', 'action' => 'store'],
    '/routes/{id}/update' => ['controller' => 'RouteController', 'action' => 'update'],
    
    // Schedule POST routes
    '/schedules/store' => ['controller' => 'ScheduleController', 'action' => 'store'],
    '/schedules/{id}/update' => ['controller' => 'ScheduleController', 'action' => 'update'],
    '/schedules/process-generate-trips' => ['controller' => 'ScheduleController', 'action' => 'processGenerateTrips'],
    '/schedules/validate-trips' => ['controller' => 'ScheduleController', 'action' => 'validateTrips'],
    
    // Trip POST routes
    '/trips/{id}/update-status' => ['controller' => 'TripController', 'action' => 'updateStatus'],
    
    // User POST routes
    '/users/store' => ['controller' => 'UserController', 'action' => 'store'],
    '/users/update/{id}' => ['controller' => 'UserController', 'action' => 'update'],
    
    // Price POST routes
    '/prices/store' => ['controller' => 'PriceController', 'action' => 'store'],
    '/prices/{id}/update' => ['controller' => 'PriceController', 'action' => 'update'],
    
    // Payment POST routes
    '/payment/process' => ['controller' => 'PaymentController', 'action' => 'process'],
    '/payment/cancel' => ['controller' => 'PaymentController', 'action' => 'cancel'],
    '/payment/apply-promotion' => ['controller' => 'PaymentController', 'action' => 'applyPromotion'],
    '/payment/use-points' => ['controller' => 'PaymentController', 'action' => 'usePoints'],
    '/payment/remove-promotion' => ['controller' => 'PaymentController', 'action' => 'removePromotion'],
    '/payment/remove-points' => ['controller' => 'PaymentController', 'action' => 'removePoints'],
    '/payment/release-seats' => ['controller' => 'PaymentController', 'action' => 'releaseSeats'],
    '/payment/heartbeat' => ['controller' => 'PaymentController', 'action' => 'heartbeat'],
    
    // Seat management POST routes
    '/api/seats/hold' => ['controller' => 'SeatController', 'action' => 'holdSeats'],
    '/api/seats/release' => ['controller' => 'SeatController', 'action' => 'releaseSeats'],
    
    // Promotion and loyalty POST routes
    '/api/promotions/apply' => ['controller' => 'PromotionController', 'action' => 'apply'],
    '/api/loyalty/use' => ['controller' => 'LoyaltyController', 'action' => 'usePoints'],
    
    // Payment gateway POST routes
    '/payment/momo/create' => ['controller' => 'MoMoController', 'action' => 'createPayment'],
    '/payment/momo/notify' => ['controller' => 'MoMoController', 'action' => 'handleNotify'],
    '/payment/vnpay/create' => ['controller' => 'VNPayController', 'action' => 'createPayment'],
    
    '/my-tickets/cancel/{id}' => ['controller' => 'MyTicketsController', 'action' => 'cancel'],
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

error_log("[v0] Router - Method: $method, Original path: " . $_SERVER['REQUEST_URI'] . ", Processed path: $path");
error_log("[v0] Router - Base path: $basePath");

// Choose appropriate routes array
$currentRoutes = ($method === 'POST') ? $postRoutes : $routes;

if ($path === '/payment' || strpos($path, '/booking') !== false || strpos($path, '/payment') !== false) {
    error_log("[v0] Router - Payment/Booking route detected");
    error_log("[v0] Router - Available routes for $method: " . json_encode(array_keys($currentRoutes)));
    error_log("[v0] Router - Session final_booking_data exists: " . (isset($_SESSION['final_booking_data']) ? 'yes' : 'no'));
    error_log("[v0] Router - Session held_seats exists: " . (isset($_SESSION['held_seats']) ? 'yes' : 'no'));
}

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
        
        error_log("[v0] Router - Route matched: $route");
        
        // Load controller and call action
        $controllerName = $handler['controller'];
        $actionName = $handler['action'];
        
        error_log("[v0] Router - Controller: $controllerName, Action: $actionName");
        error_log("[v0] Router - Parameters: " . json_encode($params));
        
        $controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';
        
        if (!file_exists($controllerFile)) {
            error_log("[v0] Router - Controller file not found: $controllerFile");
            http_response_code(500);
            echo "Internal Server Error: Controller not found";
            exit;
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerName)) {
            error_log("[v0] Router - Controller class not found: $controllerName");
            http_response_code(500);
            echo "Internal Server Error: Controller class not found";
            exit;
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $actionName)) {
            error_log("[v0] Router - Action method not found: $actionName in $controllerName");
            http_response_code(500);
            echo "Internal Server Error: Action method not found";
            exit;
        }
        
        error_log("[v0] Router - About to call $controllerName::$actionName");
        
        // Call the controller action with parameters
        if (!empty($params)) {
            // Extract parameter values in order they appear in the route
            $paramValues = array_values($params);
            call_user_func_array([$controller, $actionName], $paramValues);
        } else {
            $controller->$actionName();
        }
        
        error_log("[v0] Router - Controller action completed successfully");
        break;
    }
}

// If no route found, show 404 page
if (!$routeFound) {
    error_log("[v0] Router - No route found for: $method $path");
    http_response_code(404);
    
    $errorFile = __DIR__ . '/error/404.php';
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
