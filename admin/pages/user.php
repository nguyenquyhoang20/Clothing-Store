<?php 
// ============================================
// TRANG QUẢN LÝ USER - ADMIN
// ============================================
$pageTitle = "Quản lý User - Admin NHÓM 10";

// Chỉ cho phép admin truy cập
if(!isset($_SESSION['auth_user']['role_as']) || $_SESSION['auth_user']['role_as'] != 1) {
    header("Location: index.php?page=dashboard");
    exit();
}

$users = getAllUsers(1);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-users me-2"></i>Quản lý User</h4>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Tên</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 ps-2">Số điện thoại</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 ps-2">Địa chỉ</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Vai trò</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Tổng đơn hàng</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Ngày tham gia</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($users && mysqli_num_rows($users) > 0): ?>
                                    <?php while($user = mysqli_fetch_assoc($users)): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><?= htmlspecialchars($user['name']) ?></h6>
                                                        <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($user['email']) ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">
                                                    <?= htmlspecialchars($user['phone'] ?? 'N/A') ?>
                                                </p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">
                                                    <?= htmlspecialchars($user['address'] ?? 'N/A') ?>
                                                </p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <?php if($user['role_as'] == 1): ?>
                                                    <span class="badge bg-gradient-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-gradient-info">Nhân viên</span>
                                                    <br>
                                                    <small class="text-xs">
                                                        <?php 
                                                        $is_active = $user['is_active'] ?? 1;
                                                        if($is_active == 1) {
                                                            echo '<span class="badge bg-success">Hoạt động</span>';
                                                        } elseif($is_active == 0) {
                                                            echo '<span class="badge bg-warning">Vô hiệu hóa</span>';
                                                        } elseif($is_active == 2) {
                                                            echo '<span class="badge bg-danger">Từ chối</span>';
                                                        }
                                                        ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge bg-gradient-primary"><?= $user['total_buy'] ?? 0 ?></span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    <?= isset($user['created_at']) ? date('d-m-Y', strtotime($user['created_at'])) : (isset($user['creat_at']) ? date('d-m-Y', strtotime($user['creat_at'])) : 'N/A') ?>
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <?php if($user['role_as'] == 0): ?>
                                                    <a href="code.php?delete_user=<?= $user['id'] ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Bạn có chắc muốn xóa nhân viên <?= htmlspecialchars($user['name']) ?>? Hành động này không thể hoàn tác!')">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted text-xs">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="text-muted">Chưa có user nào</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

