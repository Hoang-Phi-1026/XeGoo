<div class="seat-map">
    <div class="vehicle-info-compact">
        <span class="vehicle-type"><?php echo htmlspecialchars($currentSeatLayout['vehicle_type']); ?></span>
        <span class="seat-type">Loại chỗ: <?php echo htmlspecialchars($currentSeatLayout['default_seat_type']); ?></span>
    </div>
    <div class="vehicle-layout">
        <?php
        $floors      = $currentSeatLayout['floors'] ?? 1;
        $total_seats = $currentSeatLayout['total_seats'] ?? 40;
        $cols_left   = $currentSeatLayout['left_columns'] ?? 2;
        $cols_right  = $currentSeatLayout['right_columns'] ?? 2;
        $total_cols  = $cols_left + $cols_right;

        $seats_per_floor = ceil($total_seats / $floors);
        $seatNumbers = [];
        for ($floor = 0; $floor < $floors; $floor++) {
            $seatNumbers[$floor] = [];
            $start = $floor * $seats_per_floor + 1;
            $end   = min(($floor + 1) * $seats_per_floor, $total_seats);
            for ($i = $start; $i <= $end; $i++) {
                $seatNumbers[$floor][] = $i;
            }
        }
        $rows_per_floor = [];
        foreach ($seatNumbers as $floor => $seats) {
            $rows_per_floor[$floor] = ceil(count($seats) / $total_cols);
        }
        ?>
        <?php if ($floors > 1): ?>
        <div class="floors-container" style="display: flex; gap: 60px; align-items: flex-start; justify-content: center;">
            <?php foreach ($seatNumbers as $floor => $seats): ?>
                <div class="floor-section">
                    <div class="floor-title"><?php echo $floor == 0 ? 'Tầng dưới' : 'Tầng trên'; ?></div>
                    <div class="floor-content">
                        <div class="seats-grid" style="margin:0 auto;">
                            <?php
                            $rows = $rows_per_floor[$floor];
                            for ($row = 0; $row < $rows; $row++):
                            ?>
                            <div class="seat-row" style="display:flex; justify-content:center; gap:40px;">
                                <!-- Cột trái -->
                                <div style="display:flex; gap:12px;">
                                <?php for ($col = 0; $col < $cols_left; $col++):
                                    $seatIdx = $row * $total_cols + $col;
                                    $num = $seats[$seatIdx] ?? null;
                                    if ($num): ?>
                                    <button type="button"
                                        class="seat <?php echo in_array($num, $currentBookedSeats) ? 'occupied' : 'available'; ?>"
                                        data-seat="<?php echo $num; ?>"
                                        <?php echo in_array($num, $currentBookedSeats) ? 'disabled' : ''; ?>>
                                        <span class="seat-number">C<?php echo $num; ?></span>
                                    </button>
                                <?php endif; endfor; ?>
                                </div>
                                <!-- Cột phải -->
                                <div style="display:flex; gap:12px;">
                                <?php for ($col = 0; $col < $cols_right; $col++):
                                    $seatIdx = $row * $total_cols + $cols_left + $col;
                                    $num = $seats[$seatIdx] ?? null;
                                    if ($num): ?>
                                    <button type="button"
                                        class="seat <?php echo in_array($num, $currentBookedSeats) ? 'occupied' : 'available'; ?>"
                                        data-seat="<?php echo $num; ?>"
                                        <?php echo in_array($num, $currentBookedSeats) ? 'disabled' : ''; ?>>
                                        <span class="seat-number">A<?php echo $num; ?></span>
                                    </button>
                                <?php endif; endfor; ?>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <!-- Xe 1 tầng -->
        <div class="single-floor">
            <div class="seats-grid">
            <?php
                $rows = ceil($total_seats / $total_cols);
                for ($row = 0; $row < $rows; $row++):
            ?>
                <div class="seat-row" style="display:flex; justify-content:center; gap:50px;">
                    <div style="display:flex; gap:20px;">
                    <?php for ($col = 0; $col < $cols_left; $col++):
                        $seatIdx = $row * $total_cols + $col;
                        $num = ($seatIdx+1) <= $total_seats ? ($seatIdx+1) : null;
                        if ($num): ?>
                        <button type="button"
                            class="seat <?php echo in_array($num, $currentBookedSeats) ? 'occupied' : 'available'; ?>"
                            data-seat="<?php echo $num; ?>"
                            <?php echo in_array($num, $currentBookedSeats) ? 'disabled' : ''; ?>>
                            <span class="seat-number">C<?php echo $num; ?></span>
                        </button>
                    <?php endif; endfor; ?>
                    </div>
                    <div style="display:flex; gap:20px;">
                    <?php for ($col = 0; $col < $cols_right; $col++):
                        $seatIdx = $row * $total_cols + $cols_left + $col;
                        $num = ($seatIdx+1) <= $total_seats ? ($seatIdx+1) : null;
                        if ($num): ?>
                        <button type="button"
                            class="seat <?php echo in_array($num, $currentBookedSeats) ? 'occupied' : 'available'; ?>"
                            data-seat="<?php echo $num; ?>"
                            <?php echo in_array($num, $currentBookedSeats) ? 'disabled' : ''; ?>>
                            <span class="seat-number">A<?php echo $num; ?></span>
                        </button>
                    <?php endif; endfor; ?>
                    </div>
                </div>
            <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="seat-legend">
        <div class="legend-item"><div class="legend-seat available"></div><span>Trống</span></div>
        <div class="legend-item"><div class="legend-seat selected"></div><span>Đã chọn</span></div>
        <div class="legend-item"><div class="legend-seat holding"></div><span>Đang giữ</span></div>
        <div class="legend-item"><div class="legend-seat occupied"></div><span>Đã đặt</span></div>
    </div>
    <div class="selection-note"><i class="fas fa-info-circle"></i> Vui lòng chọn ít nhất 1 chỗ ngồi</div>
</div>