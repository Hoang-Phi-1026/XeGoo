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
                            <a href="#">Hướng dẫn mua vé</a>
                        </div>
                    </div>

                    <!-- Search inputs -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Điểm đi</label>
                            <select name="from" class="form-select" required>
                                <option value="">Chọn điểm đi</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Điểm đến</label>
                            <select name="to" class="form-select" required>
                                <option value="">Chọn điểm đến</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Ngày đi</label>
                            <input type="date" name="departure_date" value="<?php echo htmlspecialchars($ngayDi); ?>" 
                                   class="form-input" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group return-date-group" id="returnDateGroup" style="<?php echo $isRoundTrip ? '' : 'display: none;'; ?>">
                            <label class="form-label">Ngày về</label>
                            <input type="date" name="return_date" value="<?php echo htmlspecialchars($ngayVe); ?>" 
                                   class="form-input" min="<?php echo $ngayDi ?: date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <!-- Recent searches -->
                    <div class="recent-searches">
                        <label class="form-label">Tìm kiếm gần đây</label>
                        <div class="recent-searches-grid">
                            <div class="recent-search-item">
                                <div class="recent-search-route">TP. Hồ Chí Minh - Đà Lạt</div>
                                <div class="recent-search-date">18/09/2025</div>
                            </div>
                            <div class="recent-search-item">
                                <div class="recent-search-route">An Giang - Ba Rịa - Vũng Tàu</div>
                                <div class="recent-search-date">11/09/2025</div>
                            </div>
                            <div class="recent-search-item">
                                <div class="recent-search-route">TP. Hồ Chí Minh - Tuy Hòa</div>
                                <div class="recent-search-date">Đã đi: 07/09/2025</div>
                            </div>
                            <div class="recent-search-item">
                                <div class="recent-search-route">Tuy Hòa - TP. Hồ Chí Minh</div>
                                <div class="recent-search-date">Đã đi: 11/09/2025</div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="passengers" value="<?php echo $soKhach; ?>">
                    <input type="hidden" name="is_round_trip" value="<?php echo $isRoundTrip ? '1' : '0'; ?>">
                    
                    <div class="text-center">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                            Tìm chuyến xe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <?php if ($hasSearched): ?>
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
                                    <span>Tất cả</span>
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="departure_time" value="early_morning" <?php echo $filters['departure_time'] === 'early_morning' ? 'checked' : ''; ?>>
                                    <span>Sáng sớm 00:00 - 06:00 (0)</span>
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="departure_time" value="morning" <?php echo $filters['departure_time'] === 'morning' ? 'checked' : ''; ?>>
                                    <span>Buổi sáng 06:00 - 12:00 (0)</span>
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="departure_time" value="afternoon" <?php echo $filters['departure_time'] === 'afternoon' ? 'checked' : ''; ?>>
                                    <span>Buổi chiều 12:00 - 18:00 (0)</span>
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="departure_time" value="evening" <?php echo $filters['departure_time'] === 'evening' ? 'checked' : ''; ?>>
                                    <span>Buổi tối 18:00 - 24:00 (<?php echo count($searchResults['outbound']); ?>)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Vehicle type filters -->
                        <div class="filter-section">
                            <h4 class="filter-section-title">Loại xe</h4>
                            <div class="filter-tags">
                                <button type="button" class="filter-tag">Ghế</button>
                                <button type="button" class="filter-tag">Giường</button>
                                <button type="button" class="filter-tag active">Limousine</button>
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
                            <button class="results-tab active" data-tab="outbound">
                                CHUYẾN ĐI - THỨ 5, 18/09
                            </button>
                            <button class="results-tab" data-tab="return">
                                CHUYẾN VỀ - THỨ 7, 20/09
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
                                                <a href="<?php echo BASE_URL; ?>/booking/<?php echo $trip['maChuyenXe']; ?>" 
                                                   class="trip-book-btn">
                                                    Chọn chuyến
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <div class="trip-actions">
                                            <a href="#" class="trip-action">
                                                <i class="fas fa-chair"></i>
                                                Chọn ghế
                                            </a>
                                            <a href="#" class="trip-action">
                                                <i class="fas fa-clock"></i>
                                                Lịch trình
                                            </a>
                                            <a href="#" class="trip-action">
                                                <i class="fas fa-route"></i>
                                                Trung chuyển
                                            </a>
                                            <a href="#" class="trip-action">
                                                <i class="fas fa-info-circle"></i>
                                                Chính sách
                                            </a>
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
                                                <a href="<?php echo BASE_URL; ?>/booking/<?php echo $trip['maChuyenXe']; ?>" 
                                                   class="trip-book-btn">
                                                    Chọn chuyến
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <div class="trip-actions">
                                            <a href="#" class="trip-action">
                                                <i class="fas fa-chair"></i>
                                                Chọn ghế
                                            </a>
                                            <a href="#" class="trip-action">
                                                <i class="fas fa-clock"></i>
                                                Lịch trình
                                            </a>
                                            <a href="#" class="trip-action">
                                                <i class="fas fa-route"></i>
                                                Trung chuyển
                                            </a>
                                            <a href="#" class="trip-action">
                                                <i class="fas fa-info-circle"></i>
                                                Chính sách
                                            </a>
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>
