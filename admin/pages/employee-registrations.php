<?php 
// ============================================
// TRANG QUẢN LÝ ĐĂNG KÝ NHÂN VIÊN - ADMIN
// ============================================
$pageTitle = "Quản lý đăng ký nhân viên - Admin NHÓM 10";

// Chỉ cho phép admin truy cập
if(!isset($_SESSION['auth_user']['role_as']) || $_SESSION['auth_user']['role_as'] != 1) {
    header("Location: index.php?page=dashboard");
    exit();
}

// Lấy danh sách yêu cầu đăng ký từ bảng users
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';
$where_clause = "WHERE role_as = 0";
if($status_filter == 'pending') {
    $where_clause .= " AND is_active = 0";
} elseif($status_filter == 'approved') {
    $where_clause .= " AND is_active = 1";
} elseif($status_filter == 'rejected') {
    $where_clause .= " AND is_active = 2";
}
$query = "SELECT * FROM users $where_clause ORDER BY created_at DESC";
$registrations = mysqli_query($conn, $query);

// Đếm số lượng theo trạng thái
$count_pending = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role_as = 0 AND is_active = 0"));
$count_approved = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role_as = 0 AND is_active = 1"));
$count_rejected = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role_as = 0 AND is_active = 2"));
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-user-check me-2"></i>Quản lý đăng ký nhân viên</h4>
                    <div class="btn-group" role="group">
                        <a href="?page=employee-registrations&status=all" 
                           class="btn btn-sm <?= $status_filter == 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            Tất cả
                        </a>
                        <a href="?page=employee-registrations&status=pending" 
                           class="btn btn-sm <?= $status_filter == 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">
                            Chờ duyệt <span class="badge bg-dark"><?= $count_pending ?></span>
                        </a>
                        <a href="?page=employee-registrations&status=approved" 
                           class="btn btn-sm <?= $status_filter == 'approved' ? 'btn-success' : 'btn-outline-success' ?>">
                            Đã duyệt <span class="badge bg-dark"><?= $count_approved ?></span>
                        </a>
                        <a href="?page=employee-registrations&status=rejected" 
                           class="btn btn-sm <?= $status_filter == 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">
                            Từ chối <span class="badge bg-dark"><?= $count_rejected ?></span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Tên</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Email</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Số điện thoại</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Địa chỉ</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Ngày đăng ký</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Trạng thái</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($registrations && mysqli_num_rows($registrations) > 0): ?>
                                    <?php while($reg = mysqli_fetch_assoc($registrations)): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><?= htmlspecialchars($reg['name']) ?></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($reg['email']) ?></p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($reg['phone'] ?? 'N/A') ?></p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($reg['address'] ?? 'N/A') ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    <?= isset($reg['created_at']) ? date('d/m/Y H:i', strtotime($reg['created_at'])) : 'N/A' ?>
                                                </span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <?php 
                                                $is_active = $reg['is_active'] ?? 0;
                                                if($is_active == 0) {
                                                    echo '<span class="badge bg-warning">Chờ duyệt</span>';
                                                } elseif($is_active == 1) {
                                                    echo '<span class="badge bg-success">Đã duyệt</span>';
                                                } elseif($is_active == 2) {
                                                    echo '<span class="badge bg-danger">Từ chối</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="align-middle text-center">
                                                <?php if($is_active == 0): ?>
                                                    <a href="code.php?approve_registration=<?= $reg['id'] ?>" 
                                                       class="btn btn-sm btn-success me-1" 
                                                       onclick="return confirm('Bạn có chắc muốn duyệt yêu cầu này?')">
                                                        <i class="fas fa-check"></i> Duyệt
                                                    </a>
                                                    <a href="code.php?reject_registration=<?= $reg['id'] ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Bạn có chắc muốn từ chối yêu cầu này?')">
                                                        <i class="fas fa-times"></i> Từ chối
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted text-xs">Đã xử lý</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="text-muted">Không có yêu cầu đăng ký nào</p>
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

