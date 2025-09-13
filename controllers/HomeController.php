<?php
require_once __DIR__ . '/../models/User.php';

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
}
?>
