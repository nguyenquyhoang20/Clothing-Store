<?php 
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
// Lấy role của user hiện tại
$user_role = isset($_SESSION['auth_user']['role_as']) ? $_SESSION['auth_user']['role_as'] : 1;
$is_admin = ($user_role == 1);
?>

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="index.php">
        <span class="ms-1 font-weight-bold text-white"><?= $is_admin ? 'NHÓM 10 DASHBOARD' : 'NHÓM 10 - NHÂN VIÊN' ?></span>
      </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main" style="height: 75vh">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link text-white <?= $current_page == "dashboard" ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=dashboard">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">dashboard</i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>
        <?php if($is_admin): ?>
        <li class="nav-item">
          <a class="nav-link text-white <?= $current_page == "user" ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=user">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">person</i>
            </div>
            <span class="nav-link-text ms-1">User manage</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?= $current_page == "employee-registrations" ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=employee-registrations">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">person_add</i>
            </div>
            <span class="nav-link-text ms-1">Duyệt đăng ký nhân viên</span>
          </a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link text-white <?= $current_page == "profile" ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=profile">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">account_circle</i>
            </div>
            <span class="nav-link-text ms-1">Hồ sơ</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?= in_array($current_page, ['orders', 'order-detail']) ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=orders">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">shopping_cart</i>
            </div>
            <span class="nav-link-text ms-1">Quản lý đơn hàng</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?= $current_page == "category" ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=category">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">table_view</i>
            </div>
            <span class="nav-link-text ms-1">All Categories</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?= $current_page == "add-category" ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=add-category">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">table_view</i>
            </div>
            <span class="nav-link-text ms-1">Add Category</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?= $current_page == "products" ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=products">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">table_view</i>
            </div>
            <span class="nav-link-text ms-1">All Products</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?= $current_page == "add-product" ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=add-product">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">table_view</i>
            </div>
            <span class="nav-link-text ms-1">Add Product</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?= $current_page == "voucher" ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=voucher">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">local_offer</i>
            </div>
            <span class="nav-link-text ms-1">Voucher</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?= $current_page == "flash-sale" ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=flash-sale">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">bolt</i>
            </div>
            <span class="nav-link-text ms-1">Flash Sale</span>
          </a>
        </li>
        <?php if($is_admin): ?>
        <li class="nav-item">
          <a class="nav-link text-white <?= $current_page == "audit-log" ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=audit-log">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">history</i>
            </div>
            <span class="nav-link-text ms-1">Lịch sử hoạt động</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?= $current_page == "audit-stats" ? 'active bg-gradient-primary' : '' ?>" href="index.php?page=audit-stats">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">bar_chart</i>
            </div>
            <span class="nav-link-text ms-1">Thống kê hoạt động</span>
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
    <div class="sidenav-footer position-absolute w-100 bottom-0">
      <div class="mx-3">
        <a class="btn bg-gradient-primary mt-4 w-100" href="logout.php" type="button">Logout</a>
      </div>
    </div>
</aside>
