<?php
if (!isset($outboundTrip) || !isset($returnTrip)) {
    return;
}
?>

<div class="bg-card border border-border rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4 flex items-center">
        <i class="fas fa-exchange-alt text-primary mr-2"></i>
        Tóm tắt chuyến khứ hồi
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Outbound Trip -->
        <div class="border-r border-border pr-6">
            <h4 class="font-medium text-primary mb-3 flex items-center">
                <i class="fas fa-arrow-right mr-2"></i>
                Chuyến đi
            </h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Tuyến:</span>
                    <span class="font-medium"><?php echo htmlspecialchars($outboundTrip['kyHieuTuyen']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Ngày:</span>
                    <span class="font-medium"><?php echo date('d/m/Y', strtotime($outboundTrip['ngayKhoiHanh'])); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Giờ:</span>
                    <span class="font-medium">
                        <?php echo date('H:i', strtotime($outboundTrip['thoiGianKhoiHanh'])); ?> - 
                        <?php echo date('H:i', strtotime($outboundTrip['thoiGianKetThuc'])); ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Xe:</span>
                    <span class="font-medium"><?php echo htmlspecialchars($outboundTrip['bienSo']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Giá:</span>
                    <span class="font-semibold text-primary"><?php echo number_format($outboundTrip['giaVe'], 0, ',', '.'); ?> VNĐ</span>
                </div>
            </div>
        </div>
        
        <!-- Return Trip -->
        <div class="pl-6">
            <h4 class="font-medium text-secondary mb-3 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Chuyến về
            </h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Tuyến:</span>
                    <span class="font-medium"><?php echo htmlspecialchars($returnTrip['kyHieuTuyen']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Ngày:</span>
                    <span class="font-medium"><?php echo date('d/m/Y', strtotime($returnTrip['ngayKhoiHanh'])); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Giờ:</span>
                    <span class="font-medium">
                        <?php echo date('H:i', strtotime($returnTrip['thoiGianKhoiHanh'])); ?> - 
                        <?php echo date('H:i', strtotime($returnTrip['thoiGianKetThuc'])); ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Xe:</span>
                    <span class="font-medium"><?php echo htmlspecialchars($returnTrip['bienSo']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Giá:</span>
                    <span class="font-semibold text-secondary"><?php echo number_format($returnTrip['giaVe'], 0, ',', '.'); ?> VNĐ</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Summary -->
    <div class="border-t border-border pt-4 mt-4">
        <div class="flex justify-between items-center">
            <div class="text-sm text-muted-foreground">
                Tổng cộng cho <?php echo $passengers ?? 1; ?> khách
            </div>
            <div class="text-xl font-bold text-foreground">
                <?php echo number_format(($outboundTrip['giaVe'] + $returnTrip['giaVe']) * ($passengers ?? 1), 0, ',', '.'); ?> VNĐ
            </div>
        </div>
        <div class="text-xs text-muted-foreground mt-1">
            Tiết kiệm: <?php echo number_format((($outboundTrip['giaVe'] + $returnTrip['giaVe']) * 0.05) * ($passengers ?? 1), 0, ',', '.'); ?> VNĐ so với mua lẻ
        </div>
    </div>
</div>
