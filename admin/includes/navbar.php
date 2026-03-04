<?php 
$page= substr($_SERVER['SCRIPT_NAME'], strripos($_SERVER['SCRIPT_NAME'],"/")+1);
 ?>
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;"><p><b>Dashboard</b></p></a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">
              <?php 
                if( $page== "index.php")
                {
                  echo " ";
                }
                else if($page== "category.php")
                {
                  echo "All Category";
                }
                else if($page== "add-category.php")
                {
                  echo "Add Category";
                }
                else if($page== "products.php")
                {
                  echo "All Product";
                }
                else if($page== "add-product.php")
                {
                  echo "Add Product";
                }
                else if($page== "user.php")
                {
                  echo "User manage";
                }
                else if($page== "order.php")
                {
                  echo "Order manage";
                }
                else if($page== "order-detail.php")
                {
                  echo "Order manage / order-detail ";
                }
                else if($page== "audit-log.php")
                {
                  echo "Lịch sử hoạt động";
                }
                else if($page== "audit-stats.php")
                {
                  echo "Thống kê hoạt động";
                }
                else if($page== "voucher.php")
                {
                  echo "Quản lý voucher";
                }
                else if($page== "flash-sale.php")
                {
                  echo "Quản lý flash sale";
                }
                else if($page== "add-flash-sale.php")
                {
                  echo "Thêm flash sale";
                }
                else if($page== "edit-flash-sale.php")
                {
                  echo "Chỉnh sửa flash sale";
                }
              ?>
            </li>
          </ol>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
          </div>
        </div>
      </div>
</nav>