<?php
session_start();

// Serve static files directly without routing
$staticPatterns = [
    '/uploads/' => __DIR__ . '/public/uploads/',
    '/public/' => __DIR__ . '/public/',
];

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$baseUrlPath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';

// Check if this is a static file request
foreach ($staticPatterns as $urlPrefix => $diskPath) {
    if (strpos($requestPath, $baseUrlPath . $urlPrefix) === 0) {
        // Remove base URL path and get the relative path
        $relativePath = substr($requestPath, strlen($baseUrlPath));
        $filePath = __DIR__ . '/public' . $relativePath;
        
        // Security: Prevent directory traversal
        $realPath = realpath($filePath);
        $allowedPath = realpath(__DIR__ . '/public');
        
        if ($realPath && strpos($realPath, $allowedPath) === 0 && is_file($realPath)) {
            // Determine content type
            $contentTypes = [
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'css' => 'text/css',
                'js' => 'text/javascript',
                'svg' => 'image/svg+xml',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
            ];
            
            $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
            $contentType = $contentTypes[$ext] ?? 'application/octet-stream';
            
            header('Content-Type: ' . $contentType);
            header('Cache-Control: public, max-age=3600');
            readfile($realPath);
            exit;
        } else {
            error_log("[v0] Router - Static file not found or access denied: $filePath");
            http_response_code(404);
            exit;
        }
    }
}

// Define GET routes
$routes = [
    '/' => ['controller' => 'HomeController', 'action' => 'index'],
    '/home' => ['controller' => 'HomeController', 'action' => 'index'],
    '/search' => ['controller' => 'SearchController', 'action' => 'index'],
    '/search/cities' => ['controller' => 'SearchController', 'action' => 'cities'],
    '/booking/prepare' => ['controller' => 'BookingController', 'action' => 'prepare'], // Added route for prepare action
    '/search/trip-details/{id}' => ['controller' => 'SearchController', 'action' => 'tripDetails'],
    '/booking/{id}' => ['controller' => 'BookingController', 'action' => 'show'],
    '/booking/confirm' => ['controller' => 'BookingController', 'action' => 'confirm'],
    '/booking/success/{id}' => ['controller' => 'BookingController', 'action' => 'success'],
    
    '/group-rental' => ['controller' => 'GroupRentalController', 'action' => 'index'],
    '/group-rental/success/{id}' => ['controller' => 'GroupRentalController', 'action' => 'success'],
    
    '/login' => ['controller' => 'AuthController', 'action' => 'showLogin'],
    '/register' => ['controller' => 'AuthController', 'action' => 'showRegister'],
    '/verify-email' => ['controller' => 'AuthController', 'action' => 'showVerifyEmail'],
    '/forgot-password' => ['controller' => 'AuthController', 'action' => 'showForgotPassword'],
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
    
    '/ticket-lookup' => ['controller' => 'TicketLookupController', 'action' => 'index'],
    
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
    
    // Driver routes for schedule and report features
    '/driver/schedule' => ['controller' => 'DriverScheduleController', 'action' => 'index'],
    '/driver/report' => ['controller' => 'DriverReportController', 'action' => 'index'],
    '/driver/report/attendance/{id}' => ['controller' => 'DriverReportController', 'action' => 'attendance'],
    
    '/staff/monitoring' => ['controller' => 'StaffMonitoringController', 'action' => 'index'],
    '/staff/monitoring/{id}' => ['controller' => 'StaffMonitoringController', 'action' => 'detail'],
    '/staff/monitoring/date-range' => ['controller' => 'StaffMonitoringController', 'action' => 'getByDateRange'],
    '/staff/rental-support' => ['controller' => 'StaffRentalSupportController', 'action' => 'index'],
    '/staff/rental-support/{id}' => ['controller' => 'StaffRentalSupportController', 'action' => 'detail'],
    
    '/support' => ['controller' => 'ChatController', 'action' => 'index'],
    '/support/ai' => ['controller' => 'AIChatController', 'action' => 'index'],
    '/staff/support' => ['controller' => 'ChatController', 'action' => 'staffDashboard'],
    
    '/api/chat/sessions' => ['controller' => 'ChatController', 'action' => 'getSessions'],
    '/api/chat/messages/{sessionId}' => ['controller' => 'ChatController', 'action' => 'getMessages'],
    '/api/chat/pending-count' => ['controller' => 'ChatController', 'action' => 'getPendingCount'],
    '/api/chat/get-pending-sessions' => ['controller' => 'ChatController', 'action' => 'getPendingSessions'],
    '/api/chat/get-messages' => ['controller' => 'ChatController', 'action' => 'getMessagesApi'],
    '/api/chat/assign-staff' => ['controller' => 'ChatController', 'action' => 'assignStaff'],
    '/api/chat/send-message' => ['controller' => 'ChatController', 'action' => 'sendMessageApi'],
    
    // Promotional codes routes
    '/promotional-codes' => ['controller' => 'PromotionalCodeController', 'action' => 'index'],
    '/promotional-codes/{id}/delete' => ['controller' => 'PromotionalCodeController', 'action' => 'delete'],

    // Admin statistics route
    '/admin/statistics' => ['controller' => 'StatisticsController', 'action' => 'index'],
    
    // AJAX route for trip load factor pagination
    '/admin/trip-load-factor-ajax' => ['controller' => 'StatisticsController', 'action' => 'getTripLoadFactorAjax'],
    
    '/admin/today-ticket-sales-ajax' => ['controller' => 'StatisticsController', 'action' => 'getTodayTicketSalesAjax'],
    
    // New route for fetching ticket sales by date
    '/admin/ticket-sales-by-date-ajax' => ['controller' => 'StatisticsController', 'action' => 'getTicketSalesByDateAjax'],
    
    '/admin/revenue-by-route-ajax' => ['controller' => 'StatisticsController', 'action' => 'getRevenueByRouteAjax'],

    // Post routes
    '/post' => ['controller' => 'PostController', 'action' => 'index'],
    '/post/moderation' => ['controller' => 'PostController', 'action' => 'moderation'],

    // Notification API routes for in-app reminders
    '/api/notifications/unread' => ['controller' => 'NotificationController', 'action' => 'getUnreadNotifications'],

       // Driver notification routes
    '/api/driver/notifications/unread' => ['controller' => 'DriverNotificationController', 'action' => 'getUnreadNotifications'],
    '/api/driver/notifications/mark-read' => ['controller' => 'DriverNotificationController', 'action' => 'markAsRead'],

    // Notification API routes for popup notifications
    '/api/popup-notifications/pending' => ['controller' => 'PopupNotificationController', 'action' => 'getPendingNotifications'],

    // Admin AJAX routes for revenue filtering and driver statistics
    '/admin/filtered-revenue-ajax' => ['controller' => 'StatisticsController', 'action' => 'getFilteredRevenueAjax'],
    
    '/admin/driver-revenue-ajax' => ['controller' => 'StatisticsController', 'action' => 'getDriverRevenueAjax'],
    
    '/admin/revenue-data-ajax' => ['controller' => 'StatisticsController', 'action' => 'getFilteredRevenueAjax'],

        // AJAX route cho tỷ lệ lấp đầy chuyến xe
    '/admin/trip-load-factor-ajax' => ['controller' => 'StatisticsController', 'action' => 'getTripLoadFactorAjax'],
    '/admin/trip-status-stats-ajax' => ['controller' => 'StatisticsController', 'action' => 'getTripStatusStatsAjax'],

    // New route for detailed revenue AJAX
    '/admin/detailed-revenue-ajax' => ['controller' => 'StatisticsController', 'action' => 'getDetailedRevenueAjax'],

    // Transaction statistics AJAX route
    '/admin/transaction-stats-ajax' => ['controller' => 'StatisticsController', 'action' => 'getTransactionStatsByStatusAjax'],

    '/admin/loyalty-points-ajax' => ['controller' => 'StatisticsController', 'action' => 'getLoyaltyPointsStatsAjax'],

    
];

// Define POST routes
$postRoutes = [
    '/login' => ['controller' => 'AuthController', 'action' => 'showLogin'],
    '/register' => ['controller' => 'AuthController', 'action' => 'showRegister'],
    '/verify-email' => ['controller' => 'AuthController', 'action' => 'showVerifyEmail'],
    '/resend-verification' => ['controller' => 'AuthController', 'action' => 'resendVerificationCode'],
    '/auth/verify-email' => ['controller' => 'AuthController', 'action' => 'verifyEmail'],
    '/auth/resend-code' => ['controller' => 'AuthController', 'action' => 'resendCode'],
    '/forgot-password/send-code' => ['controller' => 'AuthController', 'action' => 'sendResetCode'],
    '/forgot-password/verify-code' => ['controller' => 'AuthController', 'action' => 'verifyResetCode'],
    '/search/api' => ['controller' => 'SearchController', 'action' => 'api'],
    '/booking/process' => ['controller' => 'BookingController', 'action' => 'process'],
    '/booking/complete' => ['controller' => 'BookingController', 'action' => 'complete'],
    
    '/group-rental/submit' => ['controller' => 'GroupRentalController', 'action' => 'submit'],
    
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
    
    '/ticket-lookup/search' => ['controller' => 'TicketLookupController', 'action' => 'search'],
    
    '/my-tickets/cancel/{id}' => ['controller' => 'MyTicketsController', 'action' => 'cancel'],
    '/my-tickets/saveRating' => ['controller' => 'MyTicketsController', 'action' => 'saveRating'],
    
    // Driver POST route for confirming departure
    '/driver/report/confirm-departure' => ['controller' => 'DriverReportController', 'action' => 'confirmDeparture'],
    '/driver/report/complete-trip' => ['controller' => 'DriverReportController', 'action' => 'completeTrip'],
    
    '/staff/monitoring/date-range' => ['controller' => 'StaffMonitoringController', 'action' => 'getByDateRange'],
    '/staff/monitoring/confirm-departure' => ['controller' => 'StaffMonitoringController', 'action' => 'confirmDeparture'],
    '/staff/monitoring/{id}' => ['controller' => 'StaffMonitoringController', 'action' => 'detail'],
    '/staff/monitoring' => ['controller' => 'StaffMonitoringController', 'action' => 'index'],
    '/staff/rental-support/update-status' => ['controller' => 'StaffRentalSupportController', 'action' => 'updateStatus'],
    
    '/api/chat/send' => ['controller' => 'ChatController', 'action' => 'sendMessage'],
    '/api/chat/create-session' => ['controller' => 'ChatController', 'action' => 'createSession'],
    '/api/chat/close-session' => ['controller' => 'ChatController', 'action' => 'closeSession'],
    '/api/chat/mark-read' => ['controller' => 'ChatController', 'action' => 'markAsRead'],
    '/api/chat/get-pending-sessions' => ['controller' => 'ChatController', 'action' => 'getPendingSessions'],
    '/api/chat/get-messages' => ['controller' => 'ChatController', 'action' => 'getMessagesApi'],
    '/api/chat/assign-staff' => ['controller' => 'ChatController', 'action' => 'assignStaff'],
    '/api/chat/send-message' => ['controller' => 'ChatController', 'action' => 'sendMessageApi'],
    
    // Promotional codes store route
    '/promotional-codes/store' => ['controller' => 'PromotionalCodeController', 'action' => 'store'],

    '/api/posts/create' => ['controller' => 'PostController', 'action' => 'create'],
    '/api/posts/add-comment' => ['controller' => 'PostController', 'action' => 'addComment'],
    '/api/posts/add-reaction' => ['controller' => 'PostController', 'action' => 'addReaction'],
    '/api/posts/approve' => ['controller' => 'PostController', 'action' => 'approve'],
    '/api/posts/reject' => ['controller' => 'PostController', 'action' => 'reject'],
    
    // AI chat POST routes
    '/api/aichat/ask' => ['controller' => 'AIChatController', 'action' => 'askAI'],
    '/api/aichat/switch-to-staff' => ['controller' => 'AIChatController', 'action' => 'switchToStaffChat'],

    // Notification API routes for in-app reminders
    '/api/notifications/mark-read' => ['controller' => 'NotificationController', 'action' => 'markAsRead'],
    '/api/notifications/mark-all-read' => ['controller' => 'NotificationController', 'action' => 'markAllAsRead'],

    // Driver notification mark-read POST route
    '/api/driver/notifications/mark-read' => ['controller' => 'DriverNotificationController', 'action' => 'markAsRead'],

    // Notification API routes for popup notifications
    '/api/popup-notifications/update-status' => ['controller' => 'PopupNotificationController', 'action' => 'updateNotificationStatus'],
    '/api/popup-notifications/mark-all-shown' => ['controller' => 'PopupNotificationController', 'action' => 'markAllAsShown'],

     // Admin statistics AJAX routes for POST requests
    '/admin/revenue-by-route-ajax' => ['controller' => 'StatisticsController', 'action' => 'getRevenueByRouteAjax'],
];

// Get current URL path and remove base directory
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$baseUrlPath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
if (strpos($path, $baseUrlPath) === 0) {
    $path = substr($path, strlen($baseUrlPath));
}

if ($path === '' || $path === '/') {
    $path = '/';
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

error_log("[v0] Router - Method: $method, Original path: " . $_SERVER['REQUEST_URI'] . ", Processed path: $path");
error_log("[v0] Router - Base URL path: $baseUrlPath");

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
