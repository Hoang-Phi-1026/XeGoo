<!-- Updated seat map to display vehicle type and default seat type, and support 3 columns: left, middle, right -->
<div class="seat-map">
    <div class="vehicle-layout">
        <?php
        $hasMiddle = isset($currentSeatLayout['middle_columns']) && $currentSeatLayout['middle_columns'] > 0;
        $totalColumns = $currentSeatLayout['left_columns'] + $currentSeatLayout['middle_columns'] + $currentSeatLayout['right_columns'];
        ?>
        <?php if ($currentSeatLayout['floors'] > 1): ?>
            <div class="floors-container">
                <?php for ($floor = 1; $floor <= $currentSeatLayout['floors']; $floor++): ?>
                    <div class="floor-section">
                        <div class="floor-title">Tầng <?php echo $floor; ?></div>
                        <?php 
                        $seatsPerFloor = $currentSeatLayout['total_seats'] / $currentSeatLayout['floors'];
                        $rowsPerFloor = ceil($seatsPerFloor / $totalColumns);
                        $startSeat = ($floor - 1) * $seatsPerFloor + 1;
                        $endSeat = $floor * $seatsPerFloor;
                        ?>
                        <?php for ($row = 1; $row <= $rowsPerFloor; $row++): ?>
                            <div class="seat-row">
                                <div class="row-number"><?php echo $row; ?></div>
                                
                                <!-- Cột trái -->
                                <?php for ($col = 1; $col <= $currentSeatLayout['left_columns']; $col++): ?>
                                    <?php 
                                    $seatNum = $startSeat + ($row - 1) * $totalColumns + $col - 1;
                                    if ($seatNum <= $endSeat && $seatNum <= $currentSeatLayout['total_seats']):
                                    ?>
                                        <button type="button" 
                                            class="seat <?php echo in_array($seatNum, $currentBookedSeats) ? 'occupied' : 'available'; ?> left-seat"
                                            data-seat="<?php echo $seatNum; ?>"
                                            <?php echo in_array($seatNum, $currentBookedSeats) ? 'disabled' : ''; ?>>
                                            <?php echo $seatNum; ?>
                                        </button>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <!-- Cột giữa -->
                                <?php if ($hasMiddle): ?>
                                    <?php for ($col = 1; $col <= $currentSeatLayout['middle_columns']; $col++): ?>
                                        <?php 
                                        $seatNum = $startSeat + ($row - 1) * $totalColumns + $currentSeatLayout['left_columns'] + $col - 1;
                                        if ($seatNum <= $endSeat && $seatNum <= $currentSeatLayout['total_seats']):
                                        ?>
                                            <button type="button" 
                                                class="seat <?php echo in_array($seatNum, $currentBookedSeats) ? 'occupied' : 'available'; ?> middle-seat"
                                                data-seat="<?php echo $seatNum; ?>"
                                                <?php echo in_array($seatNum, $currentBookedSeats) ? 'disabled' : ''; ?>>
                                                <?php echo $seatNum; ?>
                                            </button>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                <?php endif; ?>

                                <div class="aisle"></div>
                                
                                <!-- Cột phải -->
                                <?php for ($col = 1; $col <= $currentSeatLayout['right_columns']; $col++): ?>
                                    <?php 
                                    $seatNum = $startSeat + ($row - 1) * $totalColumns + $currentSeatLayout['left_columns'] + $currentSeatLayout['middle_columns'] + $col - 1;
                                    if ($seatNum <= $endSeat && $seatNum <= $currentSeatLayout['total_seats']):
                                    ?>
                                        <button type="button" 
                                            class="seat <?php echo in_array($seatNum, $currentBookedSeats) ? 'occupied' : 'available'; ?> right-seat"
                                            data-seat="<?php echo $seatNum; ?>"
                                            <?php echo in_array($seatNum, $currentBookedSeats) ? 'disabled' : ''; ?>>
                                            <?php echo $seatNum; ?>
                                        </button>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endfor; ?>
            </div>
        <?php else: ?>
            <?php for ($row = 1; $row <= $currentSeatLayout['rows_per_floor']; $row++): ?>
                <div class="seat-row">
                    <div class="row-number"><?php echo $row; ?></div>
                    
                    <!-- Cột trái -->
                    <?php for ($col = 1; $col <= $currentSeatLayout['left_columns']; $col++): ?>
                        <?php 
                        $seatNum = ($row - 1) * $totalColumns + $col;
                        if ($seatNum <= $currentSeatLayout['total_seats']):
                        ?>
                            <button type="button" 
                                class="seat <?php echo in_array($seatNum, $currentBookedSeats) ? 'occupied' : 'available'; ?> left-seat"
                                data-seat="<?php echo $seatNum; ?>"
                                <?php echo in_array($seatNum, $currentBookedSeats) ? 'disabled' : ''; ?>>
                                <?php echo $seatNum; ?>
                            </button>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Cột giữa -->
                    <?php if ($hasMiddle): ?>
                        <?php for ($col = 1; $col <= $currentSeatLayout['middle_columns']; $col++): ?>
                            <?php 
                            $seatNum = ($row - 1) * $totalColumns + $currentSeatLayout['left_columns'] + $col;
                            if ($seatNum <= $currentSeatLayout['total_seats']):
                            ?>
                                <button type="button" 
                                    class="seat <?php echo in_array($seatNum, $currentBookedSeats) ? 'occupied' : 'available'; ?> middle-seat"
                                    data-seat="<?php echo $seatNum; ?>"
                                    <?php echo in_array($seatNum, $currentBookedSeats) ? 'disabled' : ''; ?>>
                                    <?php echo $seatNum; ?>
                                </button>
                            <?php endif; ?>
                        <?php endfor; ?>
                    <?php endif; ?>
                    
                    <div class="aisle"></div>
                    
                    <!-- Cột phải -->
                    <?php for ($col = 1; $col <= $currentSeatLayout['right_columns']; $col++): ?>
                        <?php 
                        $seatNum = ($row - 1) * $totalColumns + $currentSeatLayout['left_columns'] + $currentSeatLayout['middle_columns'] + $col;
                        if ($seatNum <= $currentSeatLayout['total_seats']):
                        ?>
                            <button type="button" 
                                class="seat <?php echo in_array($seatNum, $currentBookedSeats) ? 'occupied' : 'available'; ?> right-seat"
                                data-seat="<?php echo $seatNum; ?>"
                                <?php echo in_array($seatNum, $currentBookedSeats) ? 'disabled' : ''; ?>>
                                <?php echo $seatNum; ?>
                            </button>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endfor; ?>
        <?php endif; ?>

        <!-- Display vehicle type and default seat type -->
        <div class="vehicle-info">
            <h4><?php echo htmlspecialchars($currentSeatLayout['vehicle_type']); ?></h4>
            <h5>Loại chỗ: <?php echo htmlspecialchars($currentSeatLayout['default_seat_type']); ?></h5>
        </div>
    </div>
</div>