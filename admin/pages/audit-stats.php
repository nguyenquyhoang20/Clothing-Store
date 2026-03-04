<?php 
// ============================================
// TRANG THỐNG KÊ HOẠT ĐỘNG - ADMIN
// ============================================
$pageTitle = "Thống kê hoạt động - Admin NHÓM 10";

// Chỉ cho phép admin truy cập
if(!isset($_SESSION['auth_user']['role_as']) || $_SESSION['auth_user']['role_as'] != 1) {
    header("Location: index.php?page=dashboard");
    exit();
}

include(__DIR__ . "/../functions/audit_functions.php");

// Lấy thống kê
$total_actions = countAuditLogs();
$active_admins = getActiveAdmins();
$admin_count = $active_admins ? mysqli_num_rows($active_admins) : 0;

// Đếm hành động hôm nay
$today_query = "SELECT COUNT(*) as count FROM audit_log WHERE DATE(created_at) = CURDATE()";
$today_result = mysqli_query($conn, $today_query);
$today_row = mysqli_fetch_assoc($today_result);
$today_actions = $today_row['count'] ?? 0;

// Đếm hành động tuần này
$week_query = "SELECT COUNT(*) as count FROM audit_log WHERE WEEK(created_at) = WEEK(NOW()) AND YEAR(created_at) = YEAR(NOW())";
$week_result = mysqli_query($conn, $week_query);
$week_row = mysqli_fetch_assoc($week_result);
$this_week = $week_row['count'] ?? 0;

$stats = [
    'total_actions' => $total_actions,
    'total_users' => $admin_count,
    'today_actions' => $today_actions,
    'this_week' => $this_week
];
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow border-0" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Thống kê hoạt động</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card p-4" style="background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.08)">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stats-icon" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#0ea5e9,#0284c7);display:flex;align-items:center;justify-content:center;color:#fff">
                                        <i class="fas fa-list"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-4"><?= $stats['total_actions'] ?? 0 ?></div>
                                        <div class="text-muted small">Tổng hành động</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card p-4" style="background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.08)">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stats-icon" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;color:#fff">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-4"><?= $stats['total_users'] ?? 0 ?></div>
                                        <div class="text-muted small">Người dùng hoạt động</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card p-4" style="background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.08)">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stats-icon" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-4"><?= $stats['today_actions'] ?? 0 ?></div>
                                        <div class="text-muted small">Hôm nay</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card p-4" style="background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.08)">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stats-icon" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#ef4444,#f97316);display:flex;align-items:center;justify-content:center;color:#fff">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-4"><?= $stats['this_week'] ?? 0 ?></div>
                                        <div class="text-muted small">Tuần này</div>
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

