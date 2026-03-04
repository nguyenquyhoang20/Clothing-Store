<?php
// ============================================
// HỆ THỐNG ROUTING - FILE TEMPLATE CHÍNH
// ============================================

// Luôn khởi động session đầu tiên
session_start();

// Include các functions cần thiết
include("./functions/userfunctions.php");
include("./functions/myfunctions.php");
include("./functions/currency.php");

// Xử lý các tham số từ URL
$search = isset($_GET["search"]) ? $_GET["search"] : "";
$page_num = isset($_GET["page_num"]) ? $_GET["page_num"] : 1;
$type = isset($_GET["type"]) ? $_GET["type"] : "";
$slug = isset($_GET["slug"]) ? $_GET["slug"] : "";

$page_num = $page_num - 1;

// ============================================
// ROUTING - XÁC ĐỊNH TRANG CẦN HIỂN THỊ
// ============================================
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$page_path = "pages/{$page}.php";

// Đặt tiêu đề mặc định
$pageTitle = "NHÓM 10 - Fashion Shop";
$additionalCSS = [];
$additionalJS = [];

// ============================================
// LOAD NỘI DUNG TRANG CON (OUTPUT BUFFERING)
// ============================================
ob_start(); // Bắt đầu bộ đệm đầu ra

if (file_exists($page_path)) {
    include $page_path;
} else {
    // Trang 404
    echo '<div class="bg-main">
            <div class="container" style="padding: 100px 0; text-align: center;">
                <h1 style="font-size: 120px; color: #ddd; margin: 0;">404</h1>
                <h3 style="margin: 20px 0;">Oops! Không tìm thấy trang</h3>
                <p style="color: #666; margin: 20px 0;">Trang bạn đang tìm không tồn tại.</p>
                <a href="index.php" class="btn-flat btn-hover">Về trang chủ</a>
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
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Alertify JS -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
    
    <!-- ============================================ -->
    <!-- APP CSS - TẬP TRUNG TẤT CẢ CSS Ở ĐÂY -->
    <!-- ============================================ -->
    <link rel="stylesheet" href="./assets/css/grid.css">
    <link rel="stylesheet" href="./assets/css/app.css">
    <link rel="stylesheet" href="./assets/css/reponsive.css">
    
    <?php if(!empty($additionalCSS)): ?>
        <!-- CSS riêng cho từng trang -->
        <?php foreach($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <!-- ============================================ -->
    <!-- NAVBAR - MENU NAVIGATION -->
    <!-- ============================================ -->
    <?php include("./includes/navbar.php"); ?>

    <!-- ============================================ -->
    <!-- NỘI DUNG TRANG CON ĐƯỢC ĐƯA VÀO ĐÂY -->
    <!-- ============================================ -->
    <?php echo $page_content; ?>

    <!-- ============================================ -->
    <!-- FOOTER -->
    <!-- ============================================ -->
    <footer class="bg-second">
        <div class="container">
            <div class="row" style="padding: 30px 0;">
                <div class="col-3 col-md-6 col-sm-12">
                    <div class="contact">
                        <h3 class="contact-header">NHÓM 10</h3>
                        <p style="color: #999; font-size: 14px; line-height: 1.8; margin-bottom: 15px;">
                            Cửa hàng thời trang NHÓM 10 - Phong cách trẻ trung, hiện đại. Chất lượng cam kết, giá cả hợp lý.
                        </p>
                        <ul class="contact-socials">
                            <li><a href="#"><i class='bx bxl-facebook-circle'></i></a></li>
                            <li><a href="#"><i class='bx bxl-instagram-alt'></i></a></li>
                            <li><a href="#"><i class='bx bxl-youtube'></i></a></li>
                            <li><a href="#"><i class='bx bxl-tiktok'></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-3 col-md-6 col-sm-12">
                    <div class="contact">
                        <h3 class="contact-header">Liên kết nhanh</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 8px;"><a href="index.php?page=home" style="color: #999; text-decoration: none; transition: color 0.3s;"><i class='bx bx-chevron-right'></i> Trang chủ</a></li>
                            <li style="margin-bottom: 8px;"><a href="index.php?page=products" style="color: #999; text-decoration: none;"><i class='bx bx-chevron-right'></i> Sản phẩm</a></li>
                            <li style="margin-bottom: 8px;"><a href="index.php?page=track-order" style="color: #999; text-decoration: none;"><i class='bx bx-chevron-right'></i> Tra cứu đơn hàng</a></li>
                            <li style="margin-bottom: 8px;"><a href="index.php?page=wishlist" style="color: #999; text-decoration: none;"><i class='bx bx-chevron-right'></i> Yêu thích</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-3 col-md-6 col-sm-12">
                    <div class="contact">
                        <h3 class="contact-header">Hỗ trợ khách hàng</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 8px; color: #999;"><i class='bx bx-chevron-right'></i> Chính sách đổi trả</li>
                            <li style="margin-bottom: 8px; color: #999;"><i class='bx bx-chevron-right'></i> Hướng dẫn mua hàng</li>
                            <li style="margin-bottom: 8px; color: #999;"><i class='bx bx-chevron-right'></i> Chính sách bảo mật</li>
                            <li style="margin-bottom: 8px; color: #999;"><i class='bx bx-chevron-right'></i> Điều khoản sử dụng</li>
                        </ul>
                    </div>
                </div>
                <div class="col-3 col-md-6 col-sm-12">
                    <div class="contact">
                        <h3 class="contact-header">Liên hệ</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 10px; color: #999;"><i class='bx bx-map' style="color: #667eea;"></i> 123 Đường ABC, Q.1, TP.HCM</li>
                            <li style="margin-bottom: 10px; color: #999;"><i class='bx bx-phone' style="color: #667eea;"></i> +84 123 456 789</li>
                            <li style="margin-bottom: 10px; color: #999;"><i class='bx bx-envelope' style="color: #667eea;"></i> nhom10@fashion.com</li>
                            <li style="margin-bottom: 10px; color: #999;"><i class='bx bx-time' style="color: #667eea;"></i> 8:00 - 21:00 (T2 - CN)</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div style="border-top: 1px solid #333; padding: 15px 0; text-align: center;">
                <p style="color: #666; font-size: 13px; margin: 0;">© 2025 NHÓM 10 Fashion Shop. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- ============================================ -->
    <!-- JAVASCRIPT - TẬP TRUNG TẤT CẢ JS Ở ĐÂY -->
    <!-- ============================================ -->
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    
    <!-- Alertify Messages -->
    <script>
    <?php if(isset($_SESSION['message'])): ?>
        alertify.set('notifier','position', 'top-right');
        alertify.success('<?= ejs($_SESSION['message']) ?>');
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    </script>

    <!-- App JS -->
    <script src="./assets/js/app.js"></script>
    
    <?php if(!empty($additionalJS)): ?>
        <!-- JS riêng cho từng trang -->
        <?php foreach($additionalJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
