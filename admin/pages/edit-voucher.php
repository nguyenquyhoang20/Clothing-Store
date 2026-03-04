<?php
$pageTitle = "Chỉnh sửa voucher - Admin NHÓM 10";

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "Voucher không tồn tại";
    header("Location: index.php?page=voucher");
    exit();
}

$voucher_id = intval($_GET['id']);
$voucher = getVoucherById($voucher_id);

if (!$voucher || mysqli_num_rows($voucher) === 0) {
    $_SESSION['message'] = "Không tìm thấy voucher cần chỉnh sửa";
    header("Location: index.php?page=voucher");
    exit();
}

$voucher = mysqli_fetch_assoc($voucher);

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

$start_value = $voucher['start_date'] ? date('Y-m-d\TH:i', strtotime($voucher['start_date'])) : '';
$end_value = $voucher['end_date'] ? date('Y-m-d\TH:i', strtotime($voucher['end_date'])) : '';
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
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa voucher
                    </h4>
                    <a href="index.php?page=voucher" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
                <div class="card-body">
                    <form action="code.php" method="POST">
                        <input type="hidden" name="voucher_id" value="<?= $voucher['id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mã voucher <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" required value="<?= htmlspecialchars($voucher['code']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="percentage" <?= $voucher['type'] === 'percentage' ? 'selected' : ''; ?>>Phần trăm (%)</option>
                                    <option value="fixed" <?= $voucher['type'] === 'fixed' ? 'selected' : ''; ?>>Số tiền (VNĐ)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="value" class="form-control" required value="<?= rtrim(rtrim($voucher['value'], '0'), '.'); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Đơn hàng tối thiểu (VNĐ)</label>
                                <input type="number" step="1000" min="0" name="min_order" class="form-control" value="<?= $voucher['min_order'] !== null ? (int)$voucher['min_order'] : ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giảm tối đa (VNĐ)</label>
                                <input type="number" step="1000" min="0" name="max_order" class="form-control" value="<?= $voucher['max_order'] !== null ? (int)$voucher['max_order'] : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày bắt đầu</label>
                                <input type="datetime-local" name="start_date" class="form-control" value="<?= $start_value; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày kết thúc</label>
                                <input type="datetime-local" name="end_date" class="form-control" value="<?= $end_value; ?>">
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="status" name="status" <?= $voucher['status'] == 1 ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status">Hoạt động</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="update_voucher_btn" class="btn bg-gradient-primary">
                            <i class="fas fa-save me-2"></i>Lưu thay đổi
                        </button>
                        <a href="index.php?page=voucher" class="btn btn-secondary ms-2">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


