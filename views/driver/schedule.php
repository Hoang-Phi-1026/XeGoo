<?php include __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/driver-schedule.css">

<div class="driver-schedule-container">
    <div class="schedule-header">
        <h1>Lịch Trình Chạy Xe</h1>
        <p class="driver-name">Tài xế: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
    </div>

    <div class="month-navigation">
        <?php
        $prevMonth = date('Y-m', strtotime($currentMonth . '-01 -1 month'));
        $nextMonth = date('Y-m', strtotime($currentMonth . '-01 +1 month'));
        ?>
        <a href="?month=<?php echo $prevMonth; ?>&date=<?php echo $prevMonth; ?>-01" class="nav-btn">
            <i class="fas fa-chevron-left"></i> Tháng trước
        </a>
        <h2 class="current-month">Tháng <?php echo date('m/Y', strtotime($currentMonth . '-01')); ?></h2>
        <a href="?month=<?php echo $nextMonth; ?>&date=<?php echo $nextMonth; ?>-01" class="nav-btn">
            Tháng sau <i class="fas fa-chevron-right"></i>
        </a>
    </div>

    <div class="calendar-container">
        <div class="calendar-weekdays">
            <div class="weekday">CN</div>
            <div class="weekday">T2</div>
            <div class="weekday">T3</div>
            <div class="weekday">T4</div>
            <div class="weekday">T5</div>
            <div class="weekday">T6</div>
            <div class="weekday">T7</div>
        </div>
        
        <div class="calendar-days">
            <?php
            for ($i = 0; $i < $calendarData['firstDayOfWeek']; $i++) {
                echo '<div class="calendar-day empty"></div>';
            }
            
            $today = date('Y-m-d');
            for ($day = 1; $day <= $calendarData['daysInMonth']; $day++) {
                $date = sprintf('%s-%02d', $currentMonth, $day);
                $tripCount = $calendarData['scheduleLookup'][$date] ?? 0;
                $isToday = ($date === $today);
                $isSelected = ($date === $selectedDate);
                $isPast = ($date < $today);
                
                $classes = ['calendar-day'];
                if ($isToday) $classes[] = 'today';
                if ($isSelected) $classes[] = 'selected';
                if ($isPast) $classes[] = 'past';
                if ($tripCount > 0 && !$isPast) $classes[] = 'has-trips';
                
                if ($isPast) {
                    echo '<div class="' . implode(' ', $classes) . '">';
                } else {
                    echo '<a href="?month=' . $currentMonth . '&date=' . $date . '" class="' . implode(' ', $classes) . '">';
                }
                
                echo '<span class="day-number">' . $day . '</span>';
                if ($tripCount > 0 && !$isPast) {
                    echo '<span class="trip-badge">' . $tripCount . ' chuyến</span>';
                }
                
                if ($isPast) {
                    echo '</div>';
                } else {
                    echo '</a>';
                }
            }
            ?>
        </div>
    </div>

    <div class="title-schedule-today">Các chuyến xe trong ngày</div>

    <div class="day-trips-section">
        <h3>Lịch trình ngày <?php echo date('d/m/Y', strtotime($selectedDate)); ?></h3>
        
        <?php if (empty($dayTrips)): ?>
            <div class="no-trips">
                <i class="fas fa-calendar-times"></i>
                <p>Không có chuyến xe nào trong ngày này</p>
            </div>
        <?php else: ?>
            <div class="trips-list">
                <?php 
                $today = date('Y-m-d');
                $isPastDate = ($selectedDate < $today);
                
                foreach ($dayTrips as $trip): 
                    $canAttendance = !$isPastDate && $trip['trangThai'] === 'Sẵn sàng';
                ?>
                    <div class="trip-card">
                        <div class="trip-status <?php echo strtolower($trip['trangThai']); ?>">
                            <?php echo htmlspecialchars($trip['trangThai']); ?>
                        </div>
                        
                        <div class="trip-route">
                            <div class="route-point">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($trip['diemDi']); ?></span>
                            </div>
                            <div class="route-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <div class="route-point">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($trip['diemDen']); ?></span>
                            </div>
                        </div>
                        
                        <div class="trip-details">
                            <div class="detail-item">
                                <span class="label">Mã chuyến</span>
                                <span class="value"><?php echo htmlspecialchars($trip['kyHieuTuyen']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Biển số</span>
                                <span class="value"><?php echo htmlspecialchars($trip['bienSo']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Giờ khởi hành</span>
                                <span class="value"><?php echo date('H:i', strtotime($trip['thoiGianKhoiHanh'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Giờ kết thúc</span>
                                <span class="value"><?php echo date('H:i', strtotime($trip['thoiGianKetThuc'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Số ghế</span>
                                <span class="value"><?php echo $trip['soChoDaDat']; ?>/<?php echo $trip['soChoTong']; ?></span>
                            </div>
                        </div>
                        
                        <?php if ($canAttendance): ?>
                            <a href="<?php echo BASE_URL; ?>/driver/report/attendance/<?php echo $trip['maChuyenXe']; ?>" class="btn-report">
                                <i class="fas fa-clipboard-check"></i>
                                Điểm danh hành khách
                            </a>
                        <?php elseif ($isPastDate): ?>
                            <button class="btn-report disabled" disabled>
                                <i class="fas fa-lock"></i>
                                Không thể điểm danh (Đã qua)
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
