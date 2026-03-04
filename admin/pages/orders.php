<?php 
// ============================================
// TRANG ĐỚN HÀNG - CHỈ CONTENT
// ============================================
$pageTitle = "Đơn hàng - Admin NHÓM 10";
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header">
                    <h4 class="mb-3">Order</h4>
                    
                    <!-- Bộ lọc trạng thái -->
                    <div class="btn-group" role="group">
                        <a href="index.php?page=orders" class="btn btn-sm <?= !isset($_GET['status']) ? 'btn-primary' : 'btn-outline-primary' ?>">
                            Tất cả
                        </a>
                        <a href="index.php?page=orders&status=2" class="btn btn-sm <?= (isset($_GET['status']) && $_GET['status'] == 2) ? 'btn-info' : 'btn-outline-info' ?>">
                            Đang chuẩn bị
                        </a>
                        <a href="index.php?page=orders&status=3" class="btn btn-sm <?= (isset($_GET['status']) && $_GET['status'] == 3) ? 'btn-primary' : 'btn-outline-primary' ?>">
                            Đang giao
                        </a>
                        <a href="index.php?page=orders&status=4" class="btn btn-sm <?= (isset($_GET['status']) && $_GET['status'] == 4) ? 'btn-success' : 'btn-outline-success' ?>">
                            Hoàn thành
                        </a>
                        <a href="index.php?page=orders&status=5" class="btn btn-sm <?= (isset($_GET['status']) && $_GET['status'] == 5) ? 'btn-danger' : 'btn-outline-danger' ?>">
                            Đã hủy
                        </a>
                        <a href="index.php?page=orders&status=6" class="btn btn-sm <?= (isset($_GET['status']) && $_GET['status'] == 6) ? 'btn-warning' : 'btn-outline-warning' ?>">
                            Thất bại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Mã ĐH</th>
                                    <th>Khách hàng</th>
                                    <th>Sản phẩm</th>
                                    <th>SĐT</th>
                                    <th>Địa chỉ</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    // Lấy trạng thái từ URL
                                    $status_filter = isset($_GET['status']) ? $_GET['status'] : -1;
                                    $orders = getAllOrder($status_filter);

                                    if(mysqli_num_rows($orders) > 0)
                                    {
                                        foreach($orders as $item)
                                        {
                                            // Xác định trạng thái
                                            $status_text = '';
                                            $status_class = '';
                                            $next_status = null;
                                            $next_status_text = '';
                                            
                                            switch($item['status']) {
                                                case 2:
                                                    $status_text = 'Đang chuẩn bị';
                                                    $status_class = 'bg-info';
                                                    $next_status = 3;
                                                    $next_status_text = 'Đang giao';
                                                    break;
                                                case 3:
                                                    $status_text = 'Đang giao';
                                                    $status_class = 'bg-primary';
                                                    $next_status = 4;
                                                    $next_status_text = 'Hoàn thành';
                                                    break;
                                                case 4:
                                                    $status_text = 'Hoàn thành';
                                                    $status_class = 'bg-success';
                                                    break;
                                                case 5:
                                                    $status_text = 'Đã hủy';
                                                    $status_class = 'bg-danger';
                                                    break;
                                                case 6:
                                                    $status_text = 'Thất bại';
                                                    $status_class = 'bg-warning';
                                                    break;
                                                default:
                                                    $status_text = 'Không xác định';
                                                    $status_class = 'bg-secondary';
                                            }
                                        ?>
                                            <tr>
                                                <td><strong>#<?= $item['id'];?></strong></td>
                                                <td>
                                                    <?= htmlspecialchars($item['name'] ?? 'Khách'); ?>
                                                    <?php if(!empty($item['email'])): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($item['email']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                        $productsInfo = $item['products_info'] ?? '';
                                                        $productsSummary = 'N/A';
                                                        if (!empty($productsInfo)) {
                                                            $productsList = array_filter(array_map('trim', explode(',', $productsInfo)));
                                                            if (!empty($productsList)) {
                                                                $firstProduct = $productsList[0];
                                                                $remainingCount = count($productsList) - 1;
                                                                if ($remainingCount > 0) {
                                                                    $productsSummary = $firstProduct . ' + ' . $remainingCount . ' sản phẩm khác';
                                                                } else {
                                                                    $productsSummary = $firstProduct;
                                                                }
                                                                if (strlen($productsSummary) > 80) {
                                                                    $productsSummary = substr($productsSummary, 0, 77) . '...';
                                                                }
                                                            }
                                                        }
                                                    ?>
                                                    <div class="text-muted order-products-cell">
                                                        <?= htmlspecialchars($productsSummary); ?>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($item['phone'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <small><?= htmlspecialchars(substr($item['address'] ?? 'N/A', 0, 50)); ?>
                                                    <?= strlen($item['address'] ?? '') > 50 ? '...' : ''; ?></small>
                                                </td>
                                                <td><strong><?= number_format($item['total_amount'], 0, ',', '.'); ?>đ</strong></td>
                                                <td>
                                                    <span class="badge <?= $status_class; ?>"><?= $status_text; ?></span>
                                                </td>
                                                <td><?= date('d/m/Y H:i', strtotime($item['created_at'])); ?></td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="index.php?page=order-detail&id=<?= $item['id'];?>" class="btn bg-gradient-info btn-sm">
                                                            <i class="fas fa-eye"></i> Xem
                                                        </a>
                                                        
                                                        <?php if($next_status !== null): ?>
                                                        <form action="code.php" method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận chuyển trạng thái đơn hàng này?');">
                                                            <input type="hidden" name="order_id" value="<?= $item['id']; ?>">
                                                            <input type="hidden" name="order_status" value="<?= $next_status; ?>">
                                                            <button type="submit" name="update_order_status" class="btn bg-gradient-<?= $next_status == 4 ? 'success' : 'primary' ?> btn-sm">
                                                                <i class="fas fa-arrow-right"></i> <?= $next_status_text; ?>
                                                            </button>
                                                        </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($item['status'] == 2): ?>
                                                        <form action="code.php" method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận hủy đơn hàng này?');">
                                                            <input type="hidden" name="order_id" value="<?= $item['id']; ?>">
                                                            <input type="hidden" name="order_status" value="5">
                                                            <button type="submit" name="update_order_status" class="btn bg-gradient-danger btn-sm">
                                                                <i class="fas fa-times"></i> Hủy
                                                            </button>
                                                        </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($item['status'] == 3): ?>
                                                        <form action="code.php" method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận đơn hàng giao THẤT BẠI? Sản phẩm sẽ được hoàn về kho.');">
                                                            <input type="hidden" name="order_id" value="<?= $item['id']; ?>">
                                                            <input type="hidden" name="order_status" value="6">
                                                            <button type="submit" name="update_order_status" class="btn bg-gradient-warning btn-sm">
                                                                <i class="fas fa-exclamation-triangle"></i> Thất bại
                                                            </button>
                                                        </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>                     
                                            </tr>
                                        <?php
                                        }
                                    }
                                    else
                                    {
                                        echo "<tr><td colspan='9' class='text-center'>Không có đơn hàng nào</td></tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-group .btn {
    margin-right: 5px;
    margin-bottom: 5px;
}

.d-flex.gap-2 {
    display: flex !important;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.table td {
    vertical-align: middle;
}

.btn-sm {
    white-space: nowrap;
}
</style>

<style>
.order-products-cell {
    max-width: 260px;
    display: block;
    white-space: normal;
    word-break: break-word;
    overflow-wrap: break-word;
}
</style>

