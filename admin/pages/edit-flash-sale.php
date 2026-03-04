<?php
$pageTitle = "Chỉnh sửa flash sale - Admin NHÓM 10";

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "Flash sale không tồn tại";
    header("Location: index.php?page=flash-sale");
    exit();
}

$flash_sale_id = intval($_GET['id']);
$flash_sale = getFlashSaleById($flash_sale_id);

if (!$flash_sale || mysqli_num_rows($flash_sale) === 0) {
    $_SESSION['message'] = "Không tìm thấy flash sale cần chỉnh sửa";
    header("Location: index.php?page=flash-sale");
    exit();
}

$flash_sale = mysqli_fetch_assoc($flash_sale);
$products = getProductsForFlashSale();

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

$start_value = date('Y-m-d\TH:i', strtotime($flash_sale['start_time']));
$end_value = date('Y-m-d\TH:i', strtotime($flash_sale['end_time']));
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <?php if(!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Chỉnh sửa flash sale
                    </h4>
                    <a href="index.php?page=flash-sale" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
                <div class="card-body">
                    <form action="code.php" method="POST">
                        <input type="hidden" name="flash_sale_id" value="<?= $flash_sale['id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                                <select name="product_id" class="form-select" required>
                                    <option value="">-- Chọn sản phẩm --</option>
                                    <?php if($products && mysqli_num_rows($products) > 0): ?>
                                        <?php while($product = mysqli_fetch_assoc($products)): ?>
                                            <option value="<?= $product['id']; ?>" <?= $product['id'] == $flash_sale['product_id'] ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($product['name']); ?> (<?= number_format($product['selling_price'], 0, ',', '.'); ?> VNĐ)
                                            </option>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <option value="">Chưa có sản phẩm nào</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                                <select name="discount_type" class="form-select" required>
                                    <option value="percentage" <?= $flash_sale['discount_type'] === 'percentage' ? 'selected' : ''; ?>>Phần trăm (%)</option>
                                    <option value="fixed" <?= $flash_sale['discount_type'] === 'fixed' ? 'selected' : ''; ?>>Số tiền (VNĐ)</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="discount_value" class="form-control" required value="<?= rtrim(rtrim($flash_sale['discount_value'], '0'), '.'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thời gian bắt đầu <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_time" class="form-control" required value="<?= $start_value; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thời gian kết thúc <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="end_time" class="form-control" required value="<?= $end_value; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số lượng tối đa</label>
                                <input type="number" min="0" name="max_quantity" class="form-control" value="<?= $flash_sale['max_quantity'] !== null ? (int)$flash_sale['max_quantity'] : ''; ?>">
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="status" name="status" <?= $flash_sale['status'] == 1 ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status">Kích hoạt flash sale</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="update_flash_sale_btn" class="btn bg-gradient-primary">
                            <i class="fas fa-save me-2"></i>Lưu thay đổi
                        </button>
                        <a href="index.php?page=flash-sale" class="btn btn-secondary ms-2">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


