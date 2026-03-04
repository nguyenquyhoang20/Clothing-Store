<?php
// ============================================
// TRANG QUẢN LÝ VOUCHER
// ============================================
$pageTitle = "Voucher - Admin NHÓM 10";

// Lấy tham số phân trang
$page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$limit = 10; // Số voucher mỗi trang
$offset = ($page - 1) * $limit;

// Query đếm tổng số voucher
$count_query = "SELECT COUNT(*) as total FROM voucher";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_vouchers = $count_row['total'];
$total_pages = ceil($total_vouchers / $limit);

// Query lấy voucher với phân trang
$voucher_query = "SELECT * FROM voucher ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$vouchers = mysqli_query($conn, $voucher_query);
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
                    <h4 class="mb-0">Danh sách voucher (<?= $total_vouchers ?>)</h4>
                    <a href="index.php?page=add-voucher" class="btn bg-gradient-primary btn-sm">
                        <i class="fas fa-plus me-2"></i>Thêm voucher
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Mã</th>
                                    <th>Loại</th>
                                    <th>Giá trị</th>
                                    <th>Tối thiểu đơn</th>
                                    <th>Giảm tối đa</th>
                                    <th>Số lượng</th>
                                    <th>Hiệu lực</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($vouchers && mysqli_num_rows($vouchers) > 0): ?>
                                    <?php while($voucher = mysqli_fetch_assoc($vouchers)): ?>
                                        <tr>
                                            <td><?= $voucher['id']; ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($voucher['code']); ?></strong>
                                            </td>
                                            <td>
                                                <?= $voucher['type'] === 'percentage' ? 'Phần trăm' : 'Tiền cố định'; ?>
                                            </td>
                                            <td>
                                                <?php if($voucher['type'] === 'percentage'): ?>
                                                    <?= rtrim(rtrim(number_format($voucher['value'], 2, ',', '.'), '0'), ','); ?>%
                                                <?php else: ?>
                                                    <?= number_format($voucher['value'], 0, ',', '.'); ?> VNĐ
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= number_format($voucher['min_order'] ?? 0, 0, ',', '.'); ?> VNĐ
                                            </td>
                                            <td>
                                                <?php
                                                    $maxDiscount = $voucher['max_discount'];
                                                    if ($maxDiscount === null && isset($voucher['max_order'])) {
                                                        $maxDiscount = $voucher['max_order'];
                                                    }
                                                ?>
                                                <?= $maxDiscount ? number_format($maxDiscount, 0, ',', '.') . ' VNĐ' : 'Không giới hạn'; ?>
                                            </td>
                                            <td>
                                                <?= $voucher['quantity'] === null ? 'Không giới hạn' : (int)$voucher['quantity']; ?>
                                            </td>
                                            <td style="min-width:200px;">
                                                <?php
                                                    $start = $voucher['start_date'] ? date('d/m/Y H:i', strtotime($voucher['start_date'])) : 'Không giới hạn';
                                                    $end = $voucher['end_date'] ? date('d/m/Y H:i', strtotime($voucher['end_date'])) : 'Không giới hạn';
                                                ?>
                                                <div><strong>Bắt đầu:</strong> <?= $start; ?></div>
                                                <div><strong>Kết thúc:</strong> <?= $end; ?></div>
                                            </td>
                                            <td>
                                                <?php if($voucher['status'] == 1): ?>
                                                    <span class="badge bg-gradient-success">Hoạt động</span>
                                                <?php else: ?>
                                                    <span class="badge bg-gradient-secondary">Đã tắt</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="index.php?page=edit-voucher&id=<?= $voucher['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="fas fa-edit me-1"></i>Sửa
                                                </a>
                                                <form action="code.php" method="POST" class="d-inline" onsubmit="return confirm('Xóa voucher <?= htmlspecialchars($voucher['code']); ?>?');">
                                                    <input type="hidden" name="voucher_id" value="<?= $voucher['id']; ?>">
                                                    <button type="submit" name="delete_voucher_btn" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash me-1"></i>Xóa
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            Chưa có voucher nào. <a href="index.php?page=add-voucher">Thêm voucher ngay</a>.
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
                                    <a class="page-link" href="index.php?page=voucher&page_num=<?= $page - 1; ?>" aria-label="Previous">
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
                                        <a class="page-link" href="index.php?page=voucher&page_num=1">1</a>
                                    </li>
                                    <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=voucher&page_num=<?= $i; ?>"><?= $i; ?></a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=voucher&page_num=<?= $total_pages; ?>"><?= $total_pages; ?></a>
                                    </li>
                                <?php endif; ?>

                                <!-- Nút Next -->
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=voucher&page_num=<?= $page + 1; ?>" aria-label="Next">
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
