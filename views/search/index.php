<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="min-h-screen bg-gray-50">
    <!-- Updated search form to use unified CSS classes and structure -->
    <div class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-6">
            <div class="search-form-container">
                <form method="GET" action="<?php echo BASE_URL; ?>/search" class="search-form">
                    <!-- Trip type selection -->
                    <div class="trip-type-selector">
                        <label class="trip-type-option">
                            <input type="radio" name="trip_type" value="one_way" <?php echo !$isRoundTrip ? 'checked' : ''; ?>>
                            <span>Một chiều</span>
                        </label>
                        <label class="trip-type-option">
                            <input type="radio" name="trip_type" value="round_trip" <?php echo $isRoundTrip ? 'checked' : ''; ?>>
                            <span>Khứ hồi</span>
                        </label>
                        <div class="trip-type-guide">
                            <a href="<?php echo BASE_URL; ?>/booking-guide" class="text-orange-500 text-sm hover:underline">Hướng dẫn mua vé</a>
                        </div>
                    </div>

                    <!-- Search inputs -->
                    <div class="form-grid" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label class="form-label">Điểm đi</label>
                            <select name="from" class="form-select" required>
                                <option value="">Chọn điểm đi</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label class="form-label">Điểm đến</label>
                            <select name="to" class="form-select" required>
                                <option value="">Chọn điểm đến</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="flex: 0 0 150px;">
                            <label class="form-label">Ngày đi</label>
                            <input type="date" name="departure_date" value="<?php echo htmlspecialchars($ngayDi); ?>" 
                                   class="form-input" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group return-date-group" id="returnDateGroup" style="flex: 0 0 150px; <?php echo $isRoundTrip ? '' : 'display: none;'; ?>">
                            <label class="form-label">Ngày về</label>
                            <input type="date" name="return_date" value="<?php echo htmlspecialchars($ngayVe); ?>" 
                                   class="form-input" min="<?php echo $ngayDi ?: date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <!-- Search button on separate row -->
                    <div class="text-center" style="margin-top: 20px;">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                            Tìm chuyến xe
                        </button>
                    </div>

                    <input type="hidden" name="passengers" value="<?php echo $soKhach; ?>">
                    <input type="hidden" name="is_round_trip" value="<?php echo $isRoundTrip ? '1' : '0'; ?>">
                    
                </form>
                
            </div>
            
            <!-- Recent searches moved outside main search form -->
            <?php
            // Get recent searches from session (max 4 items)
            $recentSearches = $_SESSION['recent_searches'] ?? [];
            $recentSearches = array_slice($recentSearches, 0, 4); // Limit to 4 items
            ?>
            
            <?php if (!empty($recentSearches)): ?>
            <div class="recent-searches" style="margin-top: 20px;">
                <label class="form-label">Tìm kiếm gần đây</label>
                <div class="recent-searches-grid">
                    <?php foreach ($recentSearches as $search): ?>
                        <div class="recent-search-item" onclick="applyRecentSearch('<?php echo htmlspecialchars($search['from']); ?>', '<?php echo htmlspecialchars($search['to']); ?>', '<?php echo $search['departure_date']; ?>', '<?php echo $search['is_round_trip'] ? 'true' : 'false'; ?>', '<?php echo $search['return_date'] ?? ''; ?>')">
                            <div class="recent-search-route">
                                <?php echo htmlspecialchars($search['from']); ?> - <?php echo htmlspecialchars($search['to']); ?>
                            </div>
                            <div class="recent-search-date">
                                <?php 
                                $searchDate = new DateTime($search['departure_date']);
                                $today = new DateTime();
                                $yesterday = new DateTime('-1 day');
                                
                                if ($searchDate->format('Y-m-d') === $today->format('Y-m-d')) {
                                    echo 'Hôm nay';
                                } elseif ($searchDate->format('Y-m-d') === $yesterday->format('Y-m-d')) {
                                    echo 'Hôm qua';
                                } elseif ($searchDate < $today) {
                                    echo 'Đã đi: ' . $searchDate->format('d/m/Y');
                                } else {
                                    echo $searchDate->format('d/m/Y');
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <?php if ($hasSearched): ?>
            <?php
            // Process ALL search results (before filtering) for dynamic filtering options
            $allTrips = $searchResults['all'] ?? $searchResults['outbound']; // Use 'all' if available, fallback to 'outbound'
            
            $departureTimesCount = []; // Array to count trips by specific departure time
            $vehicleTypes = [];
            
            // Analyze ALL trips (before filtering) for filter options
            foreach ($allTrips as $trip) {
                // Count trips by specific departure time (HH:MM format)
                $departureTime = date('H:i', strtotime($trip['thoiGianKhoiHanh']));
                if (!isset($departureTimesCount[$departureTime])) {
                    $departureTimesCount[$departureTime] = 0;
                }
                $departureTimesCount[$departureTime]++;
                
                // Count vehicle types
                $vehicleType = $trip['tenLoaiPhuongTien'] ?? 'Không xác định';
                if (!isset($vehicleTypes[$vehicleType])) {
                    $vehicleTypes[$vehicleType] = 0;
                }
                $vehicleTypes[$vehicleType]++;
            }
            
            // Sort departure times chronologically
            ksort($departureTimesCount);
            ?>
            <!-- Updated results layout to use unified CSS classes -->
            <div class="search-results-container">
                <!-- Filter sidebar -->
                <div class="filter-sidebar">
                    <div class="filter-header">
                        <h3 class="filter-title">BỘ LỌC TÌM KIẾM</h3>
                        <a href="#" class="filter-clear">
                            <i class="fas fa-trash-alt"></i>
                            Bỏ lọc
                        </a>
                    </div>
                    
                    <form method="GET" action="<?php echo BASE_URL; ?>/search" id="filterForm">
                        <!-- Preserve search parameters -->
                        <input type="hidden" name="from" value="<?php echo htmlspecialchars($diemDi); ?>">
                        <input type="hidden" name="to" value="<?php echo htmlspecialchars($diemDen); ?>">
                        <input type="hidden" name="departure_date" value="<?php echo htmlspecialchars($ngayDi); ?>">
                        <?php if ($isRoundTrip): ?>
                            <input type="hidden" name="is_round_trip" value="1">
                            <input type="hidden" name="return_date" value="<?php echo htmlspecialchars($ngayVe); ?>">
                        <?php endif; ?>
                        <input type="hidden" name="passengers" value="<?php echo $soKhach; ?>">
                        
                        <!-- Time filters -->
                        <div class="filter-section">
                            <h4 class="filter-section-title">Giờ đi</h4>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="radio" name="departure_time" value="" <?php echo empty($filters['departure_time']) ? 'checked' : ''; ?>>
                                    <span>Tất cả (<?php echo count($allTrips); ?>)</span>
                                </label>
                                <?php foreach ($departureTimesCount as $time => $count): ?>
                                    <label class="filter-option">
                                        <input type="radio" name="departure_time" value="<?php echo $time; ?>" <?php echo ($filters['departure_time'] ?? '') === $time ? 'checked' : ''; ?>>
                                        <span><?php echo $time; ?> (<?php echo $count; ?> chuyến)</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Vehicle type filters -->
                        <div class="filter-section">
                            <h4 class="filter-section-title">Loại xe</h4>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="radio" name="vehicle_type" value="" <?php echo empty($filters['vehicle_type']) ? 'checked' : ''; ?>>
                                    <span>Tất cả (<?php echo count($allTrips); ?>)</span>
                                </label>
                                <?php foreach ($vehicleTypes as $type => $count): ?>
                                    <label class="filter-option">
                                        <input type="radio" name="vehicle_type" value="<?php echo htmlspecialchars($type); ?>" <?php echo ($filters['vehicle_type'] ?? '') === $type ? 'checked' : ''; ?>>
                                        <span><?php echo htmlspecialchars($type); ?> (<?php echo $count; ?>)</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Results area -->
                <div class="results-area">
                    <div class="results-header">
                        <h2 class="results-title">
                            <?php echo htmlspecialchars($diemDi); ?> → <?php echo htmlspecialchars($diemDen); ?> (<?php echo count($searchResults['outbound']); ?>)
                        </h2>
                        
                        <?php if ($isRoundTrip): ?>
                        <div class="results-tabs">
                            <!-- Add data attributes for tab switching -->
                            <button class="results-tab active" data-tab="outbound" onclick="handleTabSwitch(this)">
                                CHUYẾN ĐI
                            </button>
                            <button class="results-tab" data-tab="return" onclick="handleTabSwitch(this)">
                                CHUYẾN VỀ
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Wrap trip cards in tab content containers -->
                    <!-- Outbound trips -->
                    <div class="tab-content" data-tab="outbound">
                        <div class="trip-cards">
                            <?php if (empty($searchResults['outbound'])): ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <h4 class="empty-state-title">Không tìm thấy chuyến xe</h4>
                                    <p class="empty-state-description">Không có chuyến xe nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($searchResults['outbound'] as $index => $trip): ?>
                                    <div class="trip-card">
                                        <div class="trip-main">
                                            <div class="trip-route">
                                                <div class="trip-time">
                                                    <div class="trip-time-value">
                                                        <?php echo date('H:i', strtotime($trip['thoiGianKhoiHanh'])); ?>
                                                    </div>
                                                    <div class="trip-location">
                                                        <?php echo htmlspecialchars($trip['diemDi']); ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="trip-duration">
                                                    <div><?php echo $trip['thoiGianDiChuyen'] ?? '8 giờ'; ?></div>
                                                    <div class="trip-duration-line"></div>
                                                    <div class="text-xs">(Asian/Ho Chi Minh)</div>
                                                </div>
                                                
                                                <div class="trip-time">
                                                    <div class="trip-time-value">
                                                        <?php echo date('H:i', strtotime($trip['thoiGianKetThuc'])); ?>
                                                    </div>
                                                    <div class="trip-location">
                                                        <?php echo htmlspecialchars($trip['diemDen']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="trip-info">
                                                <div class="trip-vehicle">
                                                    <i class="fas fa-couch"></i>
                                                    <?php echo htmlspecialchars($trip['tenLoaiPhuongTien'] ?? 'Limousine'); ?>
                                                </div>
                                                <div class="trip-seats">
                                                    <?php echo $trip['soChoTrong']; ?> chỗ trống
                                                </div>
                                            </div>
                                            
                                            <div class="trip-price-section">
                                                <div class="trip-price">
                                                    <?php echo number_format($trip['giaVe'], 0, ',', '.'); ?>đ
                                                </div>
                                                <!-- Updated booking button for round trip handling -->
                                                <?php if ($isRoundTrip): ?>
                                                    <button class="trip-book-btn" onclick="selectOutboundTrip(<?php echo $trip['maChuyenXe']; ?>, '<?php echo htmlspecialchars($trip['kyHieuTuyen']); ?>', '<?php echo date('d/m/Y H:i', strtotime($trip['thoiGianKhoiHanh'])); ?>', <?php echo $trip['giaVe']; ?>)">
                                                        Chọn chuyến
                                                    </button>
                                                <?php else: ?>
                                                    <a href="<?php echo BASE_URL; ?>/booking/<?php echo $trip['maChuyenXe']; ?>" 
                                                       class="trip-book-btn">
                                                        Chọn chuyến
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Add return trips tab content -->
                    <?php if ($isRoundTrip): ?>
                    <div class="tab-content" data-tab="return" style="display: none;">
                        <div class="trip-cards">
                            <?php if (empty($searchResults['return'])): ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <h4 class="empty-state-title">Không tìm thấy chuyến về</h4>
                                    <p class="empty-state-description">Không có chuyến xe nào phù hợp cho chuyến về.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($searchResults['return'] as $index => $trip): ?>
                                    <div class="trip-card">
                                        <div class="trip-main">
                                            <div class="trip-route">
                                                <div class="trip-time">
                                                    <div class="trip-time-value">
                                                        <?php echo date('H:i', strtotime($trip['thoiGianKhoiHanh'])); ?>
                                                    </div>
                                                    <div class="trip-location">
                                                        <?php echo htmlspecialchars($trip['diemDi']); ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="trip-duration">
                                                    <div><?php echo $trip['thoiGianDiChuyen'] ?? '8 giờ'; ?></div>
                                                    <div class="trip-duration-line"></div>
                                                    <div class="text-xs">(Asian/Ho Chi Minh)</div>
                                                </div>
                                                
                                                <div class="trip-time">
                                                    <div class="trip-time-value">
                                                        <?php echo date('H:i', strtotime($trip['thoiGianKetThuc'])); ?>
                                                    </div>
                                                    <div class="trip-location">
                                                        <?php echo htmlspecialchars($trip['diemDen']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="trip-info">
                                                <div class="trip-vehicle">
                                                    <i class="fas fa-couch"></i>
                                                    <?php echo htmlspecialchars($trip['tenLoaiPhuongTien'] ?? 'Limousine'); ?>
                                                </div>
                                                <div class="trip-seats">
                                                    <?php echo $trip['soChoTrong']; ?> chỗ trống
                                                </div>
                                            </div>
                                            
                                            <div class="trip-price-section">
                                                <div class="trip-price">
                                                    <?php echo number_format($trip['giaVe'], 0, ',', '.'); ?>đ
                                                </div>
                                                <!-- Updated return trip booking button -->
                                                <button class="trip-book-btn" onclick="selectReturnTrip(<?php echo $trip['maChuyenXe']; ?>, '<?php echo htmlspecialchars($trip['kyHieuTuyen']); ?>', '<?php echo date('d/m/Y H:i', strtotime($trip['thoiGianKhoiHanh'])); ?>', <?php echo $trip['giaVe']; ?>)">
                                                    Chọn chuyến
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h2 class="empty-state-title">Tìm kiếm chuyến xe</h2>
                <p class="empty-state-description">Nhập thông tin tìm kiếm để xem các chuyến xe có sẵn.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.recent-search-item:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.recent-search-item {
    cursor: pointer;
    transition: all 0.2s ease;
}

.selected-trip-info {
    background: #f0f9ff;
    border: 1px solid #0ea5e9;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

.trip-selection-message {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #dbeafe;
    color: #1e40af;
    padding: 1rem 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    max-width: 400px;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
</style>

<script>
// Auto-submit filter form when radio buttons change
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        const radioButtons = filterForm.querySelectorAll('input[type="radio"]');
        
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }
});

// Function to apply recent search
function applyRecentSearch(from, to, departureDate, isRoundTrip, returnDate) {
    const searchForm = document.querySelector('.search-form');
    if (!searchForm) return;
    
    // Set form values
    const fromSelect = searchForm.querySelector('select[name="from"]');
    const toSelect = searchForm.querySelector('select[name="to"]');
    const departureDateInput = searchForm.querySelector('input[name="departure_date"]');
    const returnDateInput = searchForm.querySelector('input[name="return_date"]');
    const tripTypeInputs = searchForm.querySelectorAll('input[name="trip_type"]');
    const isRoundTripInput = searchForm.querySelector('input[name="is_round_trip"]');
    const returnDateGroup = searchForm.querySelector('.return-date-group');
    
    // Set trip type
    tripTypeInputs.forEach(input => {
        if ((isRoundTrip === 'true' && input.value === 'round_trip') || 
            (isRoundTrip === 'false' && input.value === 'one_way')) {
            input.checked = true;
        }
    });
    
    // Set return date visibility
    if (returnDateGroup) {
        if (isRoundTrip === 'true') {
            returnDateGroup.style.display = 'block';
            if (returnDateInput) returnDateInput.required = true;
            if (isRoundTripInput) isRoundTripInput.value = '1';
        } else {
            returnDateGroup.style.display = 'none';
            if (returnDateInput) returnDateInput.required = false;
            if (isRoundTripInput) isRoundTripInput.value = '0';
        }
    }
    
    // Set form values
    if (fromSelect) fromSelect.value = from;
    if (toSelect) toSelect.value = to;
    if (departureDateInput) departureDateInput.value = departureDate;
    if (returnDateInput && returnDate) returnDateInput.value = returnDate;
    
    // Submit form
    searchForm.submit();
}

// Add click cursor for recent search items
document.querySelectorAll('.recent-search-item').forEach(item => {
    item.style.cursor = 'pointer';
});

let selectedOutboundTrip = null;
let selectedReturnTrip = null;

// Function to select outbound trip for round trip
function selectOutboundTrip(tripId, route, time, price) {
    selectedOutboundTrip = {
        id: tripId,
        route: route,
        time: time,
        price: price
    };
    
    // Show success message
    showTripSelectionMessage('Đã chọn chuyến đi: ' + route + ' lúc ' + time, 'success');
    
    // Switch to return tab
    const returnTab = document.querySelector('.results-tab[data-tab="return"]');
    if (returnTab) {
        returnTab.click();
    }
    
    // Show instruction for return trip
    setTimeout(() => {
        showTripSelectionMessage('Vui lòng chọn chuyến về để hoàn tất đặt vé khứ hồi', 'info');
    }, 1000);
}

// Function to select return trip for round trip
function selectReturnTrip(tripId, route, time, price) {
    if (!selectedOutboundTrip) {
        showTripSelectionMessage('Vui lòng chọn chuyến đi trước', 'error');
        return;
    }
    
    selectedReturnTrip = {
        id: tripId,
        route: route,
        time: time,
        price: price
    };
    
    // Show confirmation and redirect to booking
    showTripSelectionMessage('Đã chọn chuyến về. Đang chuyển đến trang đặt vé...', 'success');
    
    setTimeout(() => {
        // Redirect to booking page with both trips
        const bookingUrl = `<?php echo BASE_URL; ?>/booking/${selectedOutboundTrip.id}?return_trip=${selectedReturnTrip.id}&is_round_trip=1`;
        window.location.href = bookingUrl;
    }, 1500);
}

// Function to show trip selection messages
function showTripSelectionMessage(message, type = 'info') {
    // Remove existing message
    const existingMessage = document.querySelector('.trip-selection-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = 'trip-selection-message';
    
    let bgColor, textColor, icon;
    switch(type) {
        case 'success':
            bgColor = '#dcfce7';
            textColor = '#166534';
            icon = 'fas fa-check-circle';
            break;
        case 'error':
            bgColor = '#fee2e2';
            textColor = '#dc2626';
            icon = 'fas fa-exclamation-circle';
            break;
        default: // info
            bgColor = '#dbeafe';
            textColor = '#1e40af';
            icon = 'fas fa-info-circle';
    }
    
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${bgColor};
        color: ${textColor};
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-width: 400px;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    `;
    
    messageDiv.innerHTML = `<i class="${icon}"></i> ${message}`;
    
    document.body.appendChild(messageDiv);
    
    // Auto-hide after 3 seconds (except for info messages which stay longer)
    const hideDelay = type === 'info' ? 5000 : 3000;
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, hideDelay);
}

function handleTabSwitch(clickedTab) {
    const allTabs = document.querySelectorAll(".results-tab");
    const tabContent = document.querySelectorAll(".tab-content");

    // Remove active class from all tabs
    allTabs.forEach((tab) => tab.classList.remove("active"));

    // Add active class to clicked tab
    clickedTab.classList.add("active");

    // Get tab type (outbound or return)
    const tabType = clickedTab.dataset.tab || (clickedTab.textContent.includes("CHUYẾN ĐI") ? "outbound" : "return");

    // Show/hide appropriate content
    tabContent.forEach((content) => {
        if (content.dataset.tab === tabType) {
            content.style.display = "block";
        } else {
            content.style.display = "none";
        }
    });

    // Show selected trip info when switching tabs
    if (tabType === 'return' && selectedOutboundTrip) {
        showSelectedTripInfo();
    }

    // Update URL without page reload
    const url = new URL(window.location);
    url.searchParams.set("tab", tabType);
    window.history.replaceState({}, "", url);
}

// Function to show selected outbound trip info
function showSelectedTripInfo() {
    let infoDiv = document.querySelector('.selected-trip-info');
    if (!infoDiv) {
        infoDiv = document.createElement('div');
        infoDiv.className = 'selected-trip-info';
        infoDiv.style.cssText = `
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        `;
        
        const returnTabContent = document.querySelector('.tab-content[data-tab="return"]');
        if (returnTabContent) {
            returnTabContent.insertBefore(infoDiv, returnTabContent.firstChild);
        }
    }
    
    if (selectedOutboundTrip) {
        infoDiv.innerHTML = `
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-check-circle text-green-600"></i>
                <strong>Chuyến đi đã chọn:</strong>
            </div>
            <div class="text-sm">
                <span class="font-medium">${selectedOutboundTrip.route}</span> - 
                <span>${selectedOutboundTrip.time}</span> - 
                <span class="font-semibold text-orange-600">${new Intl.NumberFormat('vi-VN').format(selectedOutboundTrip.price)}đ</span>
            </div>
        `;
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
