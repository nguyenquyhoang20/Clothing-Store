<?php 
// ============================================
// TRANG LỊCH SỬ HOẠT ĐỘNG - ADMIN
// ============================================
$pageTitle = "Lịch sử hoạt động - Admin NHÓM 10";

// Chỉ cho phép admin truy cập
if(!isset($_SESSION['auth_user']['role_as']) || $_SESSION['auth_user']['role_as'] != 1) {
    header("Location: index.php?page=dashboard");
    exit();
}

include(__DIR__ . "/../functions/audit_functions.php");

// Lấy danh sách log (50 bản ghi đầu tiên)
$logs = getAuditLogs(50, 0);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-history me-2"></i>Lịch sử hoạt động</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Thời gian</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Người dùng</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Bảng</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Hành động</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($logs && mysqli_num_rows($logs) > 0): ?>
                                    <?php while($log = mysqli_fetch_assoc($logs)): ?>
                                        <tr>
                                            <td>
                                                <span class="text-xs font-weight-bold">
                                                    <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-xs font-weight-bold"><?= htmlspecialchars($log['admin_name'] ?? 'System') ?></span>
                                                    <span class="text-xs text-secondary">
                                                        <?php 
                                                        $role_as = $log['role_as'] ?? null;
                                                        $admin_id = $log['admin_id'] ?? null;
                                                        $admin_name = $log['admin_name'] ?? '';
                                                        
                                                        // Nếu role_as là NULL (admin_id không tồn tại trong users), kiểm tra admin_name
                                                        if ($role_as === null || $role_as === '') {
                                                            // Nếu admin_name là "Admin" hoặc chứa "admin", coi như Admin
                                                            if (stripos($admin_name, 'admin') !== false || $admin_name === 'Admin') {
                                                                $role_text = 'Admin';
                                                                $badge_class = 'bg-gradient-danger';
                                                            } else {
                                                                $role_text = 'Nhân viên';
                                                                $badge_class = 'bg-gradient-success';
                                                            }
                                                        } elseif ($role_as == 1) {
                                                            $role_text = 'Admin';
                                                            $badge_class = 'bg-gradient-danger';
                                                        } elseif ($role_as == 0) {
                                                            $role_text = 'Nhân viên ' . ($admin_id ? $admin_id : '');
                                                            $badge_class = 'bg-gradient-success';
                                                        } else {
                                                            $role_text = 'Không xác định';
                                                            $badge_class = 'bg-gradient-secondary';
                                                        }
                                                        ?>
                                                        <span class="badge <?= $badge_class ?> badge-sm"><?= htmlspecialchars($role_text) ?></span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-xs"><?= htmlspecialchars($log['table_name'] ?? 'N/A') ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                $action = $log['action'] ?? 'N/A';
                                                $badge_class = 'bg-gradient-info';
                                                if ($action === 'CREATE') $badge_class = 'bg-gradient-success';
                                                elseif ($action === 'UPDATE') $badge_class = 'bg-gradient-warning';
                                                elseif ($action === 'DELETE') $badge_class = 'bg-gradient-danger';
                                                ?>
                                                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($action) ?></span>
                                            </td>
                                            <td>
                                                <span class="text-xs">
                                                    <?= formatAuditDetails($log['action'] ?? '', $log['old_values'] ?? null, $log['new_values'] ?? null, $log['description'] ?? '') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted">Chưa có lịch sử hoạt động</p>
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

