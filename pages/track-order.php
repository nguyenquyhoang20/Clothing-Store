<?php
// ============================================
// TRANG TRA CỨU ĐƠN HÀNG
// ============================================
$pageTitle = "Tra cứu đơn hàng - NHÓM 10 Fashion Shop";

$order_result = null;
$order_items = [];
$error_msg = '';

if (isset($_POST['track_order'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $phone = trim($_POST['phone'] ?? '');
    
    if ($order_id <= 0 || empty($phone)) {
        $error_msg = "Vui lòng nhập đầy đủ mã đơn hàng và số điện thoại.";
    } else {
        $order_result = trackOrder($order_id, $phone);
        if ($order_result) {
            $order_items = getTrackOrderItems($order_id);
        } else {
            $error_msg = "Không tìm thấy đơn hàng. Vui lòng kiểm tra lại mã đơn và số điện thoại.";
        }
    }
}

$status_labels = [
    1 => ['text' => 'Chờ xác nhận', 'color' => '#f39c12', 'icon' => 'bx-time'],
    2 => ['text' => 'Đang chuẩn bị', 'color' => '#3498db', 'icon' => 'bx-package'],
    3 => ['text' => 'Đang giao hàng', 'color' => '#9b59b6', 'icon' => 'bx-car'],
    4 => ['text' => 'Hoàn thành', 'color' => '#27ae60', 'icon' => 'bx-check-circle'],
    5 => ['text' => 'Đã hủy', 'color' => '#e74c3c', 'icon' => 'bx-x-circle'],
    6 => ['text' => 'Thất bại', 'color' => '#95a5a6', 'icon' => 'bx-error'],
];
?>

<style>
.track-container { max-width: 700px; margin: 0 auto; }
.track-form { background: white; border-radius: 16px; padding: 30px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
.track-form h2 { text-align: center; margin-bottom: 25px; color: #333; }
.track-form h2 i { color: #667eea; }
.track-input-group { margin-bottom: 15px; }
.track-input-group label { display: block; font-weight: 600; margin-bottom: 6px; color: #555; }
.track-input-group input { width: 100%; padding: 12px 16px; border: 2px solid #e0e6ed; border-radius: 10px; font-size: 16px; transition: border-color 0.3s; }
.track-input-group input:focus { outline: none; border-color: #667eea; }
.track-btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
.track-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }
.track-error { background: #fff5f5; border: 1px solid #fecaca; color: #dc2626; padding: 12px 16px; border-radius: 10px; margin-bottom: 15px; text-align: center; }
.order-result { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); margin-top: 20px; }
.order-status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; color: white; font-weight: 600; font-size: 14px; }
.order-info { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 15px 0; }
.order-info-item { padding: 10px; background: #f8fafc; border-radius: 8px; }
.order-info-item label { font-size: 12px; color: #94a3b8; text-transform: uppercase; display: block; }
.order-info-item span { font-weight: 600; color: #1e293b; }
.order-items-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
.order-items-table th { background: #f1f5f9; padding: 10px; text-align: left; font-size: 13px; color: #64748b; text-transform: uppercase; }
.order-items-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; }
.order-items-table img { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; }
</style>

<div class="bg-main">
    <div class="container">
        <div class="box">
            <div class="breadcumb">
                <a href="index.php?page=home">Trang chủ</a>
                <span><i class='bx bxs-chevrons-right'></i></span>
                <a href="#">Tra cứu đơn hàng</a>
            </div>
        </div>
        
        <div class="box">
            <div class="track-container">
                <div class="track-form">
                    <h2><i class='bx bx-package'></i> Tra cứu đơn hàng</h2>
                    
                    <?php if (!empty($error_msg)): ?>
                        <div class="track-error">
                            <i class='bx bx-error-circle'></i> <?= e($error_msg) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="track-input-group">
                            <label><i class='bx bx-hash'></i> Mã đơn hàng</label>
                            <input type="number" name="order_id" placeholder="Nhập mã đơn hàng (VD: 18)" required min="1" value="<?= e($_POST['order_id'] ?? '') ?>">
                        </div>
                        <div class="track-input-group">
                            <label><i class='bx bx-phone'></i> Số điện thoại</label>
                            <input type="text" name="phone" placeholder="Nhập SĐT khi đặt hàng" required value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>
                        <button type="submit" name="track_order" class="track-btn">
                            <i class='bx bx-search'></i> Tra cứu
                        </button>
                    </form>
                </div>
                
                <?php if ($order_result): ?>
                <div class="order-result">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="margin: 0;">Đơn hàng #<?= intval($order_result['id']) ?></h3>
                        <?php 
                        $st = $status_labels[$order_result['status']] ?? ['text' => 'Không xác định', 'color' => '#999', 'icon' => 'bx-question-mark'];
                        ?>
                        <span class="order-status-badge" style="background: <?= $st['color'] ?>;">
                            <i class='bx <?= $st['icon'] ?>'></i> <?= $st['text'] ?>
                        </span>
                    </div>
                    
                    <div class="order-info">
                        <div class="order-info-item">
                            <label>Khách hàng</label>
                            <span><?= e($order_result['customer_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="order-info-item">
                            <label>Số điện thoại</label>
                            <span><?= e($order_result['customer_phone'] ?? 'N/A') ?></span>
                        </div>
                        <div class="order-info-item">
                            <label>Địa chỉ</label>
                            <span><?= e($order_result['customer_address'] ?? 'N/A') ?></span>
                        </div>
                        <div class="order-info-item">
                            <label>Ngày đặt</label>
                            <span><?= e($order_result['created_at'] ?? 'N/A') ?></span>
                        </div>
                        <div class="order-info-item">
                            <label>Tổng tiền</label>
                            <span style="color: #e74c3c; font-size: 18px;"><?= formatVND($order_result['total_amount']) ?></span>
                        </div>
                        <div class="order-info-item">
                            <label>Số sản phẩm</label>
                            <span><?= intval($order_result['total_items']) ?> items</span>
                        </div>
                    </div>
                    
                    <?php if (!empty($order_items)): ?>
                    <h4 style="margin-top: 20px;">Chi tiết sản phẩm</h4>
                    <table class="order-items-table">
                        <thead>
                            <tr>
                                <th>Ảnh</th>
                                <th>Sản phẩm</th>
                                <th>Size</th>
                                <th>SL</th>
                                <th>Giá</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><img src="./images/<?= e($item['image']) ?>" alt="" loading="lazy"></td>
                                <td><a href="index.php?page=product-detail&slug=<?= e($item['slug']) ?>"><?= e($item['product_name']) ?></a></td>
                                <td><?= e($item['product_size'] ?: '—') ?></td>
                                <td><?= intval($item['quantity']) ?></td>
                                <td><?= formatVND($item['price']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
