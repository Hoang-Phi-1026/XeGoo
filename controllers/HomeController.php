<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Route.php';
require_once __DIR__ . '/../models/Vehicle.php';

class HomeController {
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index() {
        // Check if user is logged in
        $isLoggedIn = isset($_SESSION['user_id']);
        $user = null;
        
        if ($isLoggedIn) {
            $userModel = new User();
            $user = $userModel->getUserById($_SESSION['user_id']);
        }
        
        // Include the home view
        require_once __DIR__ . '/../views/home/index.php';
    }
    
    public function about() {
        // Check if user is logged in
        $isLoggedIn = isset($_SESSION['user_id']);
        $user = null;
        
        if ($isLoggedIn) {
            $userModel = new User();
            $user = $userModel->getUserById($_SESSION['user_id']);
        }
        
        // Include the about view
        require_once __DIR__ . '/../views/home/about.php';
    }
    
    public function bookingGuide() {
        // Check if user is logged in
        $isLoggedIn = isset($_SESSION['user_id']);
        $user = null;
        
        if ($isLoggedIn) {
            $userModel = new User();
            $user = $userModel->getUserById($_SESSION['user_id']);
        }
        
        // Get sample routes and vehicle types for display
        $routes = Route::getAll('Đang hoạt động', null, 6);
        $vehicleTypes = Vehicle::getVehicleTypes();
        $vehicleStats = Vehicle::getStats();
        
        // Include the booking guide view
        require_once __DIR__ . '/../views/home/booking-guide.php';
    }
}
?>
