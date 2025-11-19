<?php
require_once __DIR__ . '/../models/TripSearch.php';
require_once __DIR__ . '/../config/config.php';

class SearchController {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Show search form and handle search requests
     */
    public function index() {
        $diemDi = trim($_GET['from'] ?? '');
        $diemDen = trim($_GET['to'] ?? '');
        $ngayDi = $_GET['departure_date'] ?? '';
        $ngayVe = $_GET['return_date'] ?? '';
        $soKhach = max(1, (int)($_GET['passengers'] ?? 1));
        $isRoundTrip = isset($_GET['is_round_trip']) && $_GET['is_round_trip'] === '1';
        
        // Initialize all required variables
        $errors = [];
        $searchResults = ['outbound' => [], 'return' => []];
        $hasSearched = false;
        $filters = [
            'departure_time' => $_GET['departure_time'] ?? '',
            'vehicle_type' => $_GET['vehicle_type'] ?? '',
            'min_price' => !empty($_GET['min_price']) ? (float)$_GET['min_price'] : null,
            'max_price' => !empty($_GET['max_price']) ? (float)$_GET['max_price'] : null,
            'sort_by' => $_GET['sort_by'] ?? 'time',
            'sort_order' => $_GET['sort_order'] ?? 'asc'
        ];
        
        if (empty($_GET)) {
            TripSearch::debugDatabase();
        }
        
        error_log("=== SearchController ===");
        error_log("Search params: from='$diemDi', to='$diemDen', date='$ngayDi', passengers=$soKhach");
        
        if (!empty($diemDi) || !empty($diemDen) || !empty($ngayDi)) {
            $hasSearched = true;
            
            if (empty($diemDi)) {
                $errors[] = 'Vui lòng chọn điểm đi';
            }
            if (empty($diemDen)) {
                $errors[] = 'Vui lòng chọn điểm đến';
            }
            if (empty($ngayDi)) {
                $errors[] = 'Vui lòng chọn ngày đi';
            }
            if ($diemDi === $diemDen) {
                $errors[] = 'Điểm đi và điểm đến không thể giống nhau';
            }
            if (!empty($ngayDi) && strtotime($ngayDi) < strtotime(date('Y-m-d'))) {
                $errors[] = 'Ngày đi không thể là ngày trong quá khứ';
            }
            if ($isRoundTrip) {
                if (empty($ngayVe)) {
                    $errors[] = 'Vui lòng chọn ngày về cho chuyến khứ hồi';
                } elseif (!empty($ngayDi) && strtotime($ngayVe) <= strtotime($ngayDi)) {
                    $errors[] = 'Ngày về phải sau ngày đi';
                }
            }
        }
        
        if ($hasSearched && empty($errors)) {
            try {
                error_log("Performing search...");
                $searchResults = TripSearch::searchTrips($diemDi, $diemDen, $ngayDi, $isRoundTrip ? $ngayVe : null, $soKhach);
                
                error_log("Search completed: " . count($searchResults['outbound']) . " outbound, " . count($searchResults['return']) . " return");
                
                // Save recent search after successful search
                $this->saveRecentSearch($diemDi, $diemDen, $ngayDi, $isRoundTrip, $ngayVe, $soKhach);

                $allTripsUnfiltered = $searchResults['outbound'];
                
                if (!empty(array_filter($filters))) {
                    $searchResults['outbound'] = TripSearch::filterTrips($searchResults['outbound'], $filters);
                    if ($isRoundTrip) {
                        $searchResults['return'] = TripSearch::filterTrips($searchResults['return'], $filters);
                    }
                }
                
                $searchResults['outbound'] = $this->sortTrips($searchResults['outbound'], $filters['sort_by'], $filters['sort_order']);
                if ($isRoundTrip) {
                    $searchResults['return'] = $this->sortTrips($searchResults['return'], $filters['sort_by'], $filters['sort_order']);
                }
                
            } catch (Exception $e) {
                error_log("SearchController error: " . $e->getMessage());
                $errors[] = 'Có lỗi xảy ra khi tìm kiếm. Vui lòng thử lại.';
            }
        }
        
        $availableCities = TripSearch::getAvailableCities();
        $popularRoutes = TripSearch::getPopularRoutes();
        $vehicleTypes = TripSearch::getVehicleTypes();
        $seatTypes = TripSearch::getSeatTypes();
        $priceRange = TripSearch::getPriceRange();
        
        error_log("Form data loaded: " . count($availableCities) . " cities, " . count($popularRoutes) . " popular routes");
        
        // Load search view
        include __DIR__ . '/../views/search/index.php';
    }
    
    /**
     * Sort trips by specified criteria
     */
    private function sortTrips($trips, $sortBy, $sortOrder = 'asc') {
        if (empty($trips)) return $trips;
        
        usort($trips, function($a, $b) use ($sortBy, $sortOrder) {
            $result = 0;
            
            switch ($sortBy) {
                case 'price':
                    $result = $a['giaVe'] <=> $b['giaVe'];
                    break;
                case 'duration':
                    $aDuration = strtotime('1970-01-01 ' . $a['thoiGianDiChuyen']);
                    $bDuration = strtotime('1970-01-01 ' . $b['thoiGianDiChuyen']);
                    $result = $aDuration <=> $bDuration;
                    break;
                case 'departure':
                    $result = strtotime($a['thoiGianKhoiHanh']) <=> strtotime($b['thoiGianKhoiHanh']);
                    break;
                case 'seats':
                    $result = $b['soChoTrong'] <=> $a['soChoTrong']; // More seats first
                    break;
                default: // time
                    $result = strtotime($a['thoiGianKhoiHanh']) <=> strtotime($b['thoiGianKhoiHanh']);
                    break;
            }
            
            return $sortOrder === 'desc' ? -$result : $result;
        });
        
        return $trips;
    }
    
    /**
     * Save recent search to session
     */
    private function saveRecentSearch($from, $to, $departureDate, $isRoundTrip, $returnDate = null, $passengers = 1) {
        $search = [
            'from' => $from,
            'to' => $to,
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'passengers' => $passengers,
            'is_round_trip' => $isRoundTrip,
            'search_time' => date('Y-m-d H:i:s')
        ];
        
        // Initialize recent searches array if not exists
        if (!isset($_SESSION['recent_searches'])) {
            $_SESSION['recent_searches'] = [];
        }
        
        // Check if this search already exists (same route and date)
        $exists = false;
        foreach ($_SESSION['recent_searches'] as $key => $recentSearch) {
            if ($recentSearch['from'] === $from && 
                $recentSearch['to'] === $to && 
                $recentSearch['departure_date'] === $departureDate &&
                $recentSearch['is_round_trip'] === $isRoundTrip) {
                // Remove the existing one and add updated one at the top
                unset($_SESSION['recent_searches'][$key]);
                $exists = true;
                break;
            }
        }
        
        // Add new search at the beginning
        array_unshift($_SESSION['recent_searches'], $search);
        
        // Keep only the last 10 searches
        $_SESSION['recent_searches'] = array_slice($_SESSION['recent_searches'], 0, 10);
        
        error_log("Recent search saved: " . json_encode($search));
    }
    
    /**
     * API endpoint for AJAX search
     */
    public function api() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $diemDi = trim($input['from'] ?? '');
        $diemDen = trim($input['to'] ?? '');
        $ngayDi = $input['departure_date'] ?? '';
        $ngayVe = $input['return_date'] ?? '';
        $soKhach = max(1, (int)($input['passengers'] ?? 1));
        $isRoundTrip = isset($input['is_round_trip']) && $input['is_round_trip'] === true;
        
        $errors = [];
        if (empty($diemDi)) $errors[] = 'Vui lòng chọn điểm đi';
        if (empty($diemDen)) $errors[] = 'Vui lòng chọn điểm đến';
        if (empty($ngayDi)) $errors[] = 'Vui lòng chọn ngày đi';
        if ($diemDi === $diemDen) $errors[] = 'Điểm đi và điểm đến không thể giống nhau';
        if (strtotime($ngayDi) < strtotime(date('Y-m-d'))) $errors[] = 'Ngày đi không thể là ngày trong quá khứ';
        
        if ($isRoundTrip) {
            if (empty($ngayVe)) $errors[] = 'Vui lòng chọn ngày về';
            elseif (strtotime($ngayVe) <= strtotime($ngayDi)) $errors[] = 'Ngày về phải sau ngày đi';
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['error' => implode(', ', $errors)]);
            return;
        }
        
        try {
            $searchResults = TripSearch::searchTrips($diemDi, $diemDen, $ngayDi, $isRoundTrip ? $ngayVe : null, $soKhach);
            
            // Save recent search after successful API search
            $this->saveRecentSearch($diemDi, $diemDen, $ngayDi, $isRoundTrip, $ngayVe, $soKhach);
            
            $response = [
                'success' => true,
                'data' => [
                    'outbound' => array_map([TripSearch::class, 'formatTripForDisplay'], $searchResults['outbound']),
                    'return' => array_map([TripSearch::class, 'formatTripForDisplay'], $searchResults['return']),
                    'search_params' => [
                        'from' => $diemDi,
                        'to' => $diemDen,
                        'departure_date' => $ngayDi,
                        'return_date' => $ngayVe,
                        'passengers' => $soKhach,
                        'is_round_trip' => $isRoundTrip
                    ],
                    'summary' => [
                        'outbound_count' => count($searchResults['outbound']),
                        'return_count' => count($searchResults['return']),
                        'total_combinations' => count($searchResults['outbound']) * ($isRoundTrip ? max(1, count($searchResults['return'])) : 1)
                    ]
                ]
            ];
            
            echo json_encode($response);
        } catch (Exception $e) {
            error_log("API search error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Có lỗi xảy ra khi tìm kiếm: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get cities for autocomplete
     */
    public function cities() {
        header('Content-Type: application/json');
        
        $query = $_GET['q'] ?? '';
        $cities = TripSearch::getAvailableCities();
        
        if (!empty($query)) {
            $queryLower = mb_strtolower($query, 'UTF-8');
            $cities = array_filter($cities, function($city) use ($queryLower) {
                $name = mb_strtolower($city['name'], 'UTF-8');
                return strpos($name, $queryLower) !== false;
            });
        }
        
        echo json_encode(array_values($cities));
    }
    
    /**
     * Get trip details
     */
    public function tripDetails($tripId) {
        header('Content-Type: application/json');
        
        if (!is_numeric($tripId)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID chuyến xe không hợp lệ']);
            return;
        }
        
        try {
            $points = TripSearch::getTripPoints($tripId);
            echo json_encode(['success' => true, 'points' => $points]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }


}
?>
