<?php
// ============================================
// HỆ THỐNG ROUTING ADMIN - FILE TEMPLATE CHÍNH
// ============================================

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập (admin hoặc nhân viên đã được duyệt)
if(!isset($_SESSION['auth']) || !isset($_SESSION['auth_user'])) {
    header("Location: login.php");
    exit();
}

// Include functions (cần include trước để có $conn)
include("functions.php");

// Kiểm tra role: chỉ cho phép admin (role_as = 1) hoặc nhân viên đã duyệt (role_as = 0 và is_active = 1)
$user_role = $_SESSION['auth_user']['role_as'];
if($user_role != 1) {
    // Nếu là nhân viên, cần kiểm tra is_active
    if($user_role == 0) {
        // Lấy thông tin user từ database để kiểm tra is_active
        $user_id = $_SESSION['auth_user']['id'];
        $check_user = "SELECT is_active FROM users WHERE id='$user_id' AND role_as=0";
        $user_result = mysqli_query($conn, $check_user);
        
        if(mysqli_num_rows($user_result) > 0) {
            $user_data = mysqli_fetch_assoc($user_result);
            if($user_data['is_active'] != 1) {
                // Nhân viên chưa được duyệt hoặc bị từ chối
                session_destroy();
                header("Location: login.php");
                exit();
            }
        } else {
            // Không tìm thấy user
            session_destroy();
            header("Location: login.php");
            exit();
        }
    } else {
        // Role không hợp lệ
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

// ============================================
// ROUTING - XÁC ĐỊNH TRANG CẦN HIỂN THỊ
// ============================================
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$page_path = "pages/{$page}.php";

// Đặt tiêu đề mặc định
$pageTitle = $user_role == 1 ? "Admin - NHÓM 10" : "Nhân viên - NHÓM 10";

// ============================================
// LOAD NỘI DUNG TRANG CON (OUTPUT BUFFERING)
// ============================================
ob_start(); // Bắt đầu bộ đệm đầu ra

if (file_exists($page_path)) {
    include $page_path;
} else {
    // Trang 404
    echo '<div class="container-fluid py-4">
            <div class="alert alert-danger text-center">
                <h2>404 - Không tìm thấy trang</h2>
                <p>Trang bạn đang tìm không tồn tại.</p>
                <a href="index.php?page=dashboard" class="btn btn-primary">Về Dashboard</a>
            </div>
          </div>';
}

$page_content = ob_get_clean(); // Lấy nội dung và xóa bộ đệm

// ============================================
// HIỂN THỊ TEMPLATE CHÍNH (HTML/CSS)
// ============================================
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $pageTitle ?></title>
    
    <!-- Fonts and icons -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
    
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <!-- CSS Files - TẬP TRUNG TẤT CẢ CSS -->
    <link id="pagestyle" href="assets/css/material-dashboard.min.css" rel="stylesheet" />
    <link id="pagestyle" href="assets/css/form.css" rel="stylesheet" />
    
    <!-- Alertify JS -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
</head>   

<body class="g-sidenav-show bg-gray-200">
    
    <!-- ============================================ -->
    <!-- SIDEBAR -->
    <!-- ============================================ -->
    <?php include("includes/sidebar.php"); ?>
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <!-- ============================================ -->
        <!-- NAVBAR -->
        <!-- ============================================ -->
        <?php include("includes/navbar.php"); ?>
        
        <!-- ============================================ -->
        <!-- NỘI DUNG TRANG CON ĐƯỢC ĐƯA VÀO ĐÂY -->
        <!-- ============================================ -->
        <?php echo $page_content; ?>
        
        <!-- ============================================ -->
        <!-- FOOTER -->
        <!-- ============================================ -->
        <footer class="footer py-4">
            <div class="container-fluid">
                <div class="row align-items-center justify-content-lg-between">
                    <div class="col-lg-6 mb-lg-0 mb-4">
                        <div class="copyright text-center text-sm text-muted text-lg-start">
                            © NHÓM 10 - Fashion Shop Admin
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </main>
    
    <!-- ============================================ -->
    <!-- JAVASCRIPT - TẬP TRUNG TẤT CẢ JS -->
    <!-- ============================================ -->
    
    <!-- Core JS Files -->
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/perfect-scrollbar.min.js"></script>
    <script src="assets/js/smooth-scrollbar.min.js"></script>
    
    <!-- Alertify JS -->
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    
    <!-- Alertify Messages -->
    <script>
    <?php if(isset($_SESSION['message'])): ?>
        alertify.set('notifier','position', 'top-right');
        alertify.success('<?= $_SESSION['message'] ?>');
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    </script>
    
</body>
</html>
