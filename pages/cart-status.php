<?php 
$pageTitle = "Trạng thái đơn hàng - NHÓM 10 Fashion Shop";
?>

<div class="bg-main">
    <div class="container">
        <div class="box">
            <div class="breadcumb">
                <a href="index.php?page=home">Trang chủ</a>
                <span><i class='bx bxs-chevrons-right'></i></span>
                <a href="#">Trạng thái đơn hàng</a>
            </div>
        </div>

        <div class="box" style="padding: 20px 40px">
            <h2>Tra cứu đơn hàng</h2>
            <p>Nhập mã đơn hàng để xem trạng thái</p>
            
            <form method="GET" action="index.php" style="margin: 20px 0;" id="trackingForm" onsubmit="return validateTrackingCode()">
                <input type="hidden" name="page" value="cart-status">
                <input type="text" name="tracking_code" id="tracking_code" placeholder="Nhập mã đơn hàng (tối thiểu 4 số)" 
                       minlength="4" pattern="[0-9]{4,}" 
                       style="padding: 10px; width: 300px; border: 2px solid #ddd; border-radius: 5px;"
                       value="<?= isset($_GET['tracking_code']) ? htmlspecialchars($_GET['tracking_code']) : '' ?>">
                <button type="submit" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Tra cứu
                </button>
                <div id="error-message" style="color: #dc3545; margin-top: 10px; display: none;"></div>
            </form>
            
            <script>
            function validateTrackingCode() {
                const input = document.getElementById('tracking_code');
                const errorDiv = document.getElementById('error-message');
                const value = input.value.trim();
                
                // Kiểm tra chỉ chứa số
                if (!/^\d+$/.test(value)) {
                    errorDiv.textContent = 'Mã đơn hàng chỉ được chứa số!';
                    errorDiv.style.display = 'block';
                    input.style.borderColor = '#dc3545';
                    return false;
                }
                
                // Kiểm tra tối thiểu 4 số
                if (value.length < 4) {
                    errorDiv.textContent = 'Vui lòng nhập tối thiểu 4 số!';
                    errorDiv.style.display = 'block';
                    input.style.borderColor = '#dc3545';
                    return false;
                }
                
                errorDiv.style.display = 'none';
                input.style.borderColor = '#ddd';
                return true;
            }
            
            // Xóa thông báo lỗi khi người dùng bắt đầu nhập
            document.getElementById('tracking_code').addEventListener('input', function() {
                const errorDiv = document.getElementById('error-message');
                if (errorDiv.style.display === 'block') {
                    errorDiv.style.display = 'none';
                    this.style.borderColor = '#ddd';
                }
            });
            </script>

            <?php
            if(isset($_GET['tracking_code'])) {
                $tracking_code = trim($_GET['tracking_code']);
                
                // Validation: chỉ chứa số và tối thiểu 4 số
                if (!preg_match('/^\d+$/', $tracking_code)) {
                    ?>
                    <div style="background: #f8d7da; border: 1px solid #dc3545; border-radius: 8px; padding: 20px; margin-top: 20px;">
                        <i class='bx bx-error-circle' style="font-size: 24px; color: #dc3545; vertical-align: middle;"></i>
                        <strong style="color: #dc3545;">Lỗi!</strong>
                        <p style="margin: 10px 0 0 0; color: #721c24;">Mã đơn hàng chỉ được chứa số!</p>
                    </div>
                    <?php
                } elseif (strlen($tracking_code) < 4) {
                    ?>
                    <div style="background: #f8d7da; border: 1px solid #dc3545; border-radius: 8px; padding: 20px; margin-top: 20px;">
                        <i class='bx bx-error-circle' style="font-size: 24px; color: #dc3545; vertical-align: middle;"></i>
                        <strong style="color: #dc3545;">Lỗi!</strong>
                        <p style="margin: 10px 0 0 0; color: #721c24;">Vui lòng nhập tối thiểu 4 số!</p>
                    </div>
                    <?php
                } else {
                    $tracking_code = mysqli_real_escape_string($conn, $tracking_code);
                    
                    // Lấy 2 số đầu từ mã tra cứu
                    $first_two_digits = substr($tracking_code, 0, 2);
                
                // Tìm đơn hàng theo ID bắt đầu bằng 2 số đầu
                global $pdo;
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id LIKE ? ORDER BY id DESC LIMIT 1");
                $search_term = $first_two_digits . '%';
                $stmt->execute([$search_term]);
                
                if($stmt->rowCount() > 0) {
                    $order = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Xác định trạng thái
                    $status_text = '';
                    $status_color = '';
                    $status_icon = '';
                    
                    switch($order['status']) {
                        case 2:
                            $status_text = 'Đang chuẩn bị';
                            $status_color = '#17a2b8';
                            $status_icon = 'bx-package';
                            break;
                        case 3:
                            $status_text = 'Đang giao';
                            $status_color = '#007bff';
                            $status_icon = 'bx-car';
                            break;
                        case 4:
                            $status_text = 'Hoàn thành';
                            $status_color = '#28a745';
                            $status_icon = 'bx-check-circle';
                            break;
                        case 5:
                            $status_text = 'Đã hủy';
                            $status_color = '#dc3545';
                            $status_icon = 'bx-x-circle';
                            break;
                        case 6:
                            $status_text = 'Thất bại';
                            $status_color = '#ffc107';
                            $status_icon = 'bx-error';
                            break;
                        default:
                            $status_text = 'Không xác định';
                            $status_color = '#6c757d';
                            $status_icon = 'bx-help-circle';
                    }
                    
                    // Lấy chi tiết sản phẩm trong đơn hàng
                    $detail_stmt = $pdo->prepare("SELECT od.*, p.name, p.image, p.selling_price 
                                    FROM order_detail od 
                                    JOIN products p ON od.product_id = p.id 
                                    WHERE od.order_id = ?");
                    $detail_stmt->execute([$order['id']]);
                    $details = $detail_stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    
                    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-top: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; margin-bottom: 20px;">
                            <div>
                                <?php 
                                // Tạo mã đơn hàng dài từ ID (giống logic tạo đơn hàng)
                                // Format: ID + 2 số ngẫu nhiên + ngày đặt hàng (ddmmyyyy)
                                $date_suffix = date('dmY', strtotime($order['created_at'])); // ddmmyyyy = 8 chữ số
                                // Dùng hash từ ID và ngày để tạo 2 số nhất quán (thay vì random)
                                $order_id_str = (string)$order['id'];
                                $hash_seed = crc32($order_id_str . $order['created_at']);
                                $random_2digits = str_pad(abs($hash_seed) % 100, 2, '0', STR_PAD_LEFT); // 2 chữ số từ hash
                                $order_code = $order['id'] . $random_2digits . $date_suffix;
                                ?>
                                <h3 style="margin: 0; color: #333;">Đơn hàng #<?= $order['id'] ?></h3>
                                <p style="color: #666; margin: 5px 0 0 0;">
                                    <strong>Mã tra cứu:</strong> <span style="font-size: 18px; color: #667eea; font-weight: bold;"><?= $order_code ?></span><br>
                                    <small style="color: #999;">(Nhập 2 số đầu: <?= substr($order_code, 0, 2) ?> để tra cứu)</small><br>
                                    <span style="color: #666;">Ngày đặt: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <div style="background: <?= $status_color ?>; color: white; padding: 12px 24px; border-radius: 25px; display: inline-flex; align-items: center; gap: 8px; font-weight: bold; font-size: 16px;">
                                    <i class='bx <?= $status_icon ?>' style="font-size: 24px;"></i>
                                    <?= $status_text ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sản phẩm -->
                        <div>
                            <h4 style="color: #333; margin-bottom: 15px;"><i class='bx bx-shopping-bag'></i> Sản phẩm trong đơn hàng</h4>
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                        <th style="padding: 12px; text-align: left;">Sản phẩm</th>
                                        <th style="padding: 12px; text-align: center;">Size</th>
                                        <th style="padding: 12px; text-align: center;">Số lượng</th>
                                        <th style="padding: 12px; text-align: right;">Đơn giá</th>
                                        <th style="padding: 12px; text-align: right;">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($details as $detail): ?>
                                    <tr style="border-bottom: 1px solid #dee2e6;">
                                        <td style="padding: 12px;">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <img src="images/<?= e($detail['image']) ?>" alt="<?= e($detail['name']) ?>" 
                                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                                <span><?= e($detail['name']) ?></span>
                                            </div>
                                        </td>
                                        <td style="padding: 12px; text-align: center;">
                                            <?= !empty($detail['product_size']) ? e($detail['product_size']) : '-' ?>
                                        </td>
                                        <td style="padding: 12px; text-align: center;"><?= intval($detail['quantity']) ?></td>
                                        <td style="padding: 12px; text-align: right;"><?= formatVND($detail['price']) ?></td>
                                        <td style="padding: 12px; text-align: right; font-weight: bold;">
                                            <?= formatVND($detail['price'] * $detail['quantity']) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background: #f8f9fa;">
                                        <td colspan="4" style="padding: 15px; text-align: right; font-weight: bold; font-size: 18px;">
                                            Tổng cộng:
                                        </td>
                                        <td style="padding: 15px; text-align: right; font-weight: bold; font-size: 18px; color: #dc3545;">
                                            <?= formatVND($order['total_amount']) ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <?php if(!empty($order['customer_note'])): ?>
                        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                            <h4 style="color: #333; margin-bottom: 10px;"><i class='bx bx-note'></i> Ghi chú</h4>
                            <p style="margin: 0;"><?= nl2br(htmlspecialchars($order['customer_note'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php
                } else {
                    ?>
                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-top: 20px;">
                        <i class='bx bx-info-circle' style="font-size: 24px; color: #856404; vertical-align: middle;"></i>
                        <strong>Không tìm thấy đơn hàng!</strong>
                        <p style="margin: 10px 0 0 0;">Vui lòng kiểm tra lại mã đơn hàng.</p>
                    </div>
                    <?php
                }
                } // End else validation
            }
            ?>
        </div>
    </div>
</div>

