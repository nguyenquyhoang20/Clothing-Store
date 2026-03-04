<?php 
// ============================================
// TRANG CHI TIẾT ĐƠN HÀNG - CHỈ CONTENT
// ============================================
$pageTitle = "Chi tiết đơn hàng - Admin NHÓM 10";

if(!isset($_GET['id'])) {
    header("Location: index.php?page=orders");
    exit();
}

$order_id = $_GET['id'];
$order_query = "SELECT o.*, 
                COALESCE(o.customer_name, u.name) as customer_name,
                COALESCE(o.customer_email, u.email) as customer_email,
                COALESCE(o.customer_phone, u.phone) as customer_phone,
                COALESCE(o.customer_address, u.address) as customer_address
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = '$order_id'";
$order_result = mysqli_query($conn, $order_query);

if(mysqli_num_rows($order_result) == 0) {
    header("Location: index.php?page=orders");
    exit();
}

$order = mysqli_fetch_array($order_result);

// Lấy chi tiết sản phẩm trong đơn hàng
$detail_query = "SELECT od.*, p.name as product_name, p.image as product_image, p.slug
                 FROM order_detail od
                 JOIN products p ON od.product_id = p.id
                 WHERE od.order_id = '$order_id'";
$detail_result = mysqli_query($conn, $detail_query);

// Xác định trạng thái
$status_text = '';
$status_class = '';
switch($order['status']) {
    case 2:
        $status_text = 'Đang chuẩn bị';
        $status_class = 'info';
        break;
    case 3:
        $status_text = 'Đang giao';
        $status_class = 'primary';
        break;
    case 4:
        $status_text = 'Hoàn thành';
        $status_class = 'success';
        break;
    case 5:
        $status_text = 'Đã hủy';
        $status_class = 'danger';
        break;
    case 6:
        $status_text = 'Thất bại';
        $status_class = 'warning';
        break;
    default:
        $status_text = 'Không xác định';
        $status_class = 'secondary';
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Chi tiết đơn hàng #<?= $order['id']; ?></h4>
                    <a href="index.php?page=orders" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Thông tin đơn hàng -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin đơn hàng</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Mã đơn hàng:</strong></td>
                                            <td>#<?= $order['id']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày đặt:</strong></td>
                                            <td><?= date('d/m/Y H:i:s', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng thái:</strong></td>
                                            <td>
                                                <span class="badge bg-<?= $status_class; ?> fs-6"><?= $status_text; ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tổng tiền:</strong></td>
                                            <td><h5 class="text-danger"><?= number_format($order['total_amount'], 0, ',', '.'); ?>đ</h5></td>
                                        </tr>
                                        <?php if(!empty($order['customer_note'])): ?>
                                        <tr>
                                            <td><strong>Ghi chú:</strong></td>
                                            <td><?= nl2br(htmlspecialchars($order['customer_note'])); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin khách hàng -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-user"></i> Thông tin khách hàng</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Họ tên:</strong></td>
                                            <td><?= htmlspecialchars($order['customer_name']); ?></td>
                                        </tr>
                                        <?php if(!empty($order['customer_email'])): ?>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td><?= htmlspecialchars($order['customer_email']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if(!empty($order['customer_phone'])): ?>
                                        <tr>
                                            <td><strong>Số điện thoại:</strong></td>
                                            <td><?= htmlspecialchars($order['customer_phone']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if(!empty($order['customer_address'])): ?>
                                        <tr>
                                            <td><strong>Địa chỉ:</strong></td>
                                            <td><?= nl2br(htmlspecialchars($order['customer_address'])); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cập nhật trạng thái -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-sync-alt"></i> Cập nhật trạng thái</h5>
                                </div>
                                <div class="card-body">
                                    <form action="code.php" method="POST" class="d-flex gap-2 align-items-center">
                                        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                        <select name="order_status" class="form-select" style="max-width: 300px;">
                                            <option value="2" <?= $order['status'] == 2 ? 'selected' : ''; ?>>Đang chuẩn bị</option>
                                            <option value="3" <?= $order['status'] == 3 ? 'selected' : ''; ?>>Đang giao</option>
                                            <option value="4" <?= $order['status'] == 4 ? 'selected' : ''; ?>>Hoàn thành</option>
                                            <option value="5" <?= $order['status'] == 5 ? 'selected' : ''; ?>>Đã hủy</option>
                                            <option value="6" <?= $order['status'] == 6 ? 'selected' : ''; ?>>Thất bại</option>
                                        </select>
                                        <button type="submit" name="update_order_status" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Cập nhật
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách sản phẩm -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-shopping-bag"></i> Sản phẩm trong đơn hàng</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Ảnh</th>
                                                    <th>Tên sản phẩm</th>
                                                    <th>Size</th>
                                                    <th>Giá</th>
                                                    <th>Số lượng</th>
                                                    <th>Thành tiền</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if(mysqli_num_rows($detail_result) > 0) {
                                                    while($detail = mysqli_fetch_array($detail_result)) {
                                                        $subtotal = $detail['price'] * $detail['quantity'];
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <img src="../images/<?= $detail['product_image']; ?>" 
                                                                 width="60" height="60" 
                                                                 style="object-fit:cover;border-radius:8px;" 
                                                                 alt="<?= $detail['product_name'];?>">
                                                        </td>
                                                        <td>
                                                            <strong><?= htmlspecialchars($detail['product_name']); ?></strong>
                                                            <br>
                                                            <a href="../index.php?page=product-detail&slug=<?= $detail['slug']; ?>" 
                                                               target="_blank" 
                                                               class="text-muted small">
                                                                <i class="fas fa-external-link-alt"></i> Xem sản phẩm
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($detail['product_size'])): ?>
                                                                <span class="badge bg-info"><?= htmlspecialchars($detail['product_size']); ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= number_format($detail['price'], 0, ',', '.'); ?>đ</td>
                                                        <td><span class="badge bg-secondary"><?= $detail['quantity']; ?></span></td>
                                                        <td><strong><?= number_format($subtotal, 0, ',', '.'); ?>đ</strong></td>
                                                    </tr>
                                                <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='6' class='text-center'>Không có sản phẩm</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-end"><strong>Tổng cộng:</strong></td>
                                                    <td><h5 class="text-danger mb-0"><?= number_format($order['total_amount'], 0, ',', '.'); ?>đ</h5></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

