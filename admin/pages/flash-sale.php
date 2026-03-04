<?php
// ============================================
// TRANG QUẢN LÝ FLASH SALE
// ============================================
$pageTitle = "Flash Sale - Admin NHÓM 10";

// Lấy tham số phân trang
$page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$limit = 10; // Số flash sale mỗi trang
$offset = ($page - 1) * $limit;

// Query đếm tổng số flash sale
$count_query = "SELECT COUNT(*) as total FROM flash_sales";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_flash_sales = $count_row['total'];
$total_pages = ceil($total_flash_sales / $limit);

// Query lấy flash sale với phân trang
$flash_sale_query = "SELECT fs.*, 
                        p.name AS product_name,
                        p.slug AS product_slug,
                        p.selling_price AS product_price,
                        p.image AS product_image
                  FROM flash_sales fs
                  JOIN products p ON fs.product_id = p.id
                  ORDER BY fs.created_at DESC
                  LIMIT $limit OFFSET $offset";
$flashSales = mysqli_query($conn, $flash_sale_query);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <?php 
            // Hiển thị thông báo từ session
            if (function_exists('getSessionMessage')) {
                $flash = getSessionMessage();
                if ($flash): ?>
                    <div class="alert alert-<?= $flash['type']; ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($flash['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif;
            }
            ?>

            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Danh sách flash sale (<?= $total_flash_sales ?>)</h4>
                    <a href="index.php?page=add-flash-sale" class="btn bg-gradient-primary btn-sm">
                        <i class="fas fa-bolt me-2"></i>Thêm flash sale
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Sản phẩm</th>
                                    <th>Giá gốc</th>
                                    <th>Giảm giá</th>
                                    <th>Thời gian</th>
                                    <th>Số lượng tối đa</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($flashSales && mysqli_num_rows($flashSales) > 0): ?>
                                    <?php while($sale = mysqli_fetch_assoc($flashSales)): ?>
                                        <?php
                                            $start = date('d/m/Y H:i', strtotime($sale['start_time']));
                                            $end = date('d/m/Y H:i', strtotime($sale['end_time']));
                                            $discountDisplay = $sale['discount_type'] === 'percentage'
                                                ? rtrim(rtrim(number_format($sale['discount_value'], 2, ',', '.'), '0'), ',') . '%'
                                                : number_format($sale['discount_value'], 0, ',', '.') . ' VNĐ';
                                        ?>
                                        <?php
                                            $now = time();
                                            $startTime = strtotime($sale['start_time']);
                                            $endTime = strtotime($sale['end_time']);
                                            $statusBadge = '';

                                        if ($sale['status'] != 1) {
                                                $statusBadge = "<span class='badge bg-gradient-secondary'>Đã tắt</span>";
                                            } elseif ($now < $startTime) {
                                                $statusBadge = "<span class='badge bg-gradient-info'>Chưa bắt đầu</span>";
                                            } elseif ($now > $endTime) {
                                                $statusBadge = "<span class='badge bg-gradient-dark'>Đã kết thúc</span>";
                                            } else {
                                                $statusBadge = "<span class='badge bg-gradient-success'>Hoạt động</span>";
                                            }
                                        ?>
                                        <tr>
                                            <td><?= $sale['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3" style="width: 55px; height: 55px; border-radius: 12px; overflow: hidden; background: #f4f4f4;">
                                                        <img src="../images/<?= htmlspecialchars($sale['product_image']); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($sale['product_name']); ?></strong>
                                                        <div class="text-muted small">ID: <?= $sale['product_id']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= number_format($sale['product_price'], 0, ',', '.'); ?> VNĐ</td>
                                            <td>
                                                <span class="badge bg-gradient-danger"><?= $discountDisplay; ?></span>
                                            </td>
                                            <td style="min-width:200px;">
                                                <div><strong>Bắt đầu:</strong> <?= $start; ?></div>
                                                <div><strong>Kết thúc:</strong> <?= $end; ?></div>
                                            </td>
                                            <td>
                                                <?= $sale['max_quantity'] !== null ? (int)$sale['max_quantity'] : 'Không giới hạn'; ?>
                                            </td>
                                            <td>
                                                <?= $statusBadge; ?>
                                                <div class="text-muted small mt-1">
                                                    <i class="fas fa-clock me-1"></i>Hiện tại: <?= date('d/m/Y H:i'); ?>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <a href="index.php?page=edit-flash-sale&id=<?= $sale['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="fas fa-edit me-1"></i>Sửa
                                                </a>
                                                <form action="code.php" method="POST" class="d-inline" onsubmit="return confirm('Xóa flash sale cho sản phẩm <?= htmlspecialchars($sale['product_name']); ?>?');">
                                                    <input type="hidden" name="flash_sale_id" value="<?= $sale['id']; ?>">
                                                    <button type="submit" name="delete_flash_sale_btn" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash me-1"></i>Xóa
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            Chưa có flash sale nào. <a href="index.php?page=add-flash-sale">Thêm flash sale ngay</a>.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <!-- Nút Previous -->
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=flash-sale&page_num=<?= $page - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">&laquo;</span>
                                </li>
                                <?php endif; ?>

                                <!-- Các số trang -->
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=flash-sale&page_num=1">1</a>
                                    </li>
                                    <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=flash-sale&page_num=<?= $i; ?>"><?= $i; ?></a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=flash-sale&page_num=<?= $total_pages; ?>"><?= $total_pages; ?></a>
                                    </li>
                                <?php endif; ?>

                                <!-- Nút Next -->
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=flash-sale&page_num=<?= $page + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">&raquo;</span>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
