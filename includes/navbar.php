<!-- mobile menu -->
<div class="mobile-menu bg-second">
    <a href="index.php?page=home" class="mb-logo">NHÓM 10</a>
    <span class="mb-menu-toggle" id="mb-menu-toggle">
        <i class='bx bx-menu'></i>
    </span>
</div>
<!-- end mobile menu -->

<!-- main header -->
<div class="header-wrapper" id="header-wrapper">
    <span class="mb-menu-toggle mb-menu-close" id="mb-menu-close">
        <i class='bx bx-x'></i>
    </span>
    
    <!-- top header -->
    <div class="bg-second">
        <div class="top-header container">
            <ul class="devided">
                <li>
                    <a href="tel:+84123456789"><i class='bx bx-phone'></i> +84 123 456 789</a>
                </li>
                <li>
                    <a href="mailto:nhom10@fashion.com"><i class='bx bx-envelope'></i> nhom10@fashion.com</a>
                </li>
            </ul>
            <ul class="devided" style="float: right;">
                <?php if (isset($_SESSION['auth']) && isset($_SESSION['auth_user'])): ?>
                    <li>
                        <a href="index.php?page=profile"><i class='bx bx-user'></i> <?= e($_SESSION['auth_user']['name']) ?></a>
                    </li>
                    <li>
                        <a href="./functions/authcode.php?logout=true"><i class='bx bx-log-out'></i> Đăng xuất</a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="index.php?page=login"><i class='bx bx-log-in'></i> Đăng nhập</a>
                    </li>
                    <li>
                        <a href="index.php?page=register"><i class='bx bx-user-plus'></i> Đăng ký</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <!-- end top header -->
    
    <!-- mid header -->
    <div class="bg-main">
        <div class="mid-header container">
            <a href="index.php?page=home" class="logo">NHÓM 10</a>
            
            <form class="search" method="get" action="index.php">
                <input type="hidden" name="page" value="products">
                <input name="search" type="text" value="<?= e($search ?? '') ?>" placeholder="Tìm kiếm sản phẩm...">
                <button type="submit" style="display:inline; border:none">
                    <i class='bx bx-search-alt'></i>
                </button>
            </form>

            <ul class="user-menu">
                <?php
                // Hide cart for admin
                if (!isset($_SESSION['auth']) || !isset($_SESSION['auth_user']['role_as']) || $_SESSION['auth_user']['role_as'] != 1) {
                    // Wishlist count
                    $wishlist_count = getWishlistCount();
                    echo '<li class="cart-icon-wrapper">';
                    echo '<a href="index.php?page=wishlist" style="position: relative; display: inline-block;" title="Yêu thích">';
                    echo '<i class="bx bx-heart"></i>';
                    if ($wishlist_count > 0) {
                        echo '<span class="cart-badge wishlist-badge">' . $wishlist_count . '</span>';
                    }
                    echo '</a>';
                    echo '</li>';

                    // Cart count
                    $cart_count = 0;
                    if (isset($_SESSION['cart'])) {
                        $cart_count = count($_SESSION['cart']);
                    }
                    
                    echo '<li class="cart-icon-wrapper">';
                    echo '<a href="index.php?page=cart" style="position: relative; display: inline-block;" title="Giỏ hàng">';
                    echo '<i class="bx bx-cart"></i>';
                    if ($cart_count > 0) {
                        echo '<span class="cart-badge">' . $cart_count . '</span>';
                    }
                    echo '</a>';
                    echo '</li>';
                }
                ?>
            </ul>
        </div>
    </div>

    <!-- menu-main -->
    <div class="bg-second">
        <div class="bottom-header container">
            <ul class="main-menu">
                <li><a href="index.php?page=home">Trang chủ</a></li>
                
                <!-- mega menu -->
                <li class="mega-dropdown">
                    <a href="index.php?page=products">Danh mục<i class='bx bxs-chevron-down'></i></a>
                    <div class="mega-content">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box">
                                    <ul>
                                        <?php
                                        $categories = getAllActive("categories");
                                        
                                        if ($categories && mysqli_num_rows($categories) > 0) {
                                            foreach ($categories as $item) {
                                        ?>
                                                <li><a href="index.php?page=products&type=<?= e($item['slug']) ?>"><?= e($item['name']) ?></a></li>
                                        <?php
                                            }
                                        } else {
                                            echo "<li>Không có danh mục</li>";
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- end mega menu -->

                <li><a href="index.php?page=track-order"><i class='bx bx-package'></i> Tra cứu đơn hàng</a></li>
            </ul>
        </div>
    </div>
    <!-- end bottom header -->
</div>
<!-- end main header -->
