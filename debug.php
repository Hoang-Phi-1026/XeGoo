<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Xegoo System</h1>";

// Test 1: Basic PHP
echo "<h2>1. PHP Working: ✓</h2>";

// Test 2: Database connection
echo "<h2>2. Testing Database Connection...</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    echo "Database config loaded ✓<br>";
    
    // Test connection
    $pdo = new PDO("mysql:host=localhost;dbname=xegoo_db;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful ✓<br>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chuyenxe");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Found {$result['count']} trips in database ✓<br>";
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
}

// Test 3: Check if trip 630 exists
echo "<h2>3. Testing Trip 630...</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM chuyenxe WHERE maChuyenXe = ?");
    $stmt->execute([630]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($trip) {
        echo "Trip 630 found ✓<br>";
        echo "Details: " . print_r($trip, true) . "<br>";
    } else {
        echo "Trip 630 NOT found ❌<br>";
    }
} catch (Exception $e) {
    echo "Query Error: " . $e->getMessage() . "<br>";
}

// Test 4: BookingController
echo "<h2>4. Testing BookingController...</h2>";
try {
    require_once __DIR__ . '/controllers/BookingController.php';
    echo "BookingController loaded ✓<br>";
    
    $controller = new BookingController();
    echo "BookingController instantiated ✓<br>";
    
} catch (Exception $e) {
    echo "BookingController Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<h2>Debug Complete</h2>";
?>
