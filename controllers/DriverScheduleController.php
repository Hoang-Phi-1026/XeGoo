<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DriverSchedule.php';

class DriverScheduleController {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in and is a driver
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này.';
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
    
    /**
     * Show driver schedule calendar view
     */
    public function index() {
        try {
            $driverId = $_SESSION['user_id'];
            $currentMonth = $_GET['month'] ?? date('Y-m');
            $selectedDate = $_GET['date'] ?? date('Y-m-d');
            
            // Get schedule for the month
            $monthSchedule = DriverSchedule::getMonthSchedule($driverId, $currentMonth);
            
            // Get trips for selected date
            $dayTrips = DriverSchedule::getDayTrips($driverId, $selectedDate);
            
            // Get calendar data
            $calendarData = $this->generateCalendarData($currentMonth, $monthSchedule);
            
            $viewData = compact('currentMonth', 'selectedDate', 'monthSchedule', 'dayTrips', 'calendarData', 'driverId');
            extract($viewData);
            
            include __DIR__ . '/../views/driver/schedule.php';
            
        } catch (Exception $e) {
            error_log("DriverScheduleController index error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải lịch trình.';
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
    
    /**
     * Generate calendar data for display
     */
    private function generateCalendarData($month, $scheduleData) {
        $year = (int)substr($month, 0, 4);
        $monthNum = (int)substr($month, 5, 2);
        
        $firstDay = mktime(0, 0, 0, $monthNum, 1, $year);
        $daysInMonth = date('t', $firstDay);
        $dayOfWeek = date('w', $firstDay);
        
        // Create schedule lookup
        $scheduleLookup = [];
        foreach ($scheduleData as $trip) {
            $date = date('Y-m-d', strtotime($trip['ngayKhoiHanh']));
            if (!isset($scheduleLookup[$date])) {
                $scheduleLookup[$date] = 0;
            }
            $scheduleLookup[$date]++;
        }
        
        return [
            'year' => $year,
            'month' => $monthNum,
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $dayOfWeek,
            'scheduleLookup' => $scheduleLookup
        ];
    }
}
?>
