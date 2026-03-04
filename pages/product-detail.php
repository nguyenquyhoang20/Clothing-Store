<?php 
// ============================================
// TRANG CHI TIẾT SẢN PHẨM - CHỈ CONTENT
// ============================================
$pageTitle = "Chi tiết sản phẩm - NHÓM 10 Fashion Shop";
?>

<script>
// Gallery JavaScript - Define functions first
let currentIndex = 0;
let totalImages = 0;

// Initialize when page loads
function initGallery() {
    totalImages = document.querySelectorAll('.product-img-item').length;
}

// Change main image
function changeMainImage(imageSrc, index) {
    const mainImg = document.getElementById('main-product-image');
    if (mainImg) {
        mainImg.src = imageSrc;
        currentIndex = index;
        updateThumbnails();
        updateCounter();
    }
}

// Update thumbnail active state
function updateThumbnails() {
    const thumbnails = document.querySelectorAll('.product-img-item');
    thumbnails.forEach((thumb, index) => {
        if (index === currentIndex) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });
}

// Update counter
function updateCounter() {
    const counter = document.getElementById('current-image');
    if (counter) {
        counter.textContent = currentIndex + 1;
    }
}

// Next image
function nextImage() {
    if (totalImages <= 1) return;
    currentIndex = (currentIndex + 1) % totalImages;
    const thumbnails = document.querySelectorAll('.product-img-item');
    if (thumbnails[currentIndex]) {
        const img = thumbnails[currentIndex].querySelector('img');
        changeMainImage(img.src, currentIndex);
    }
}

// Previous image
function previousImage() {
    if (totalImages <= 1) return;
    currentIndex = (currentIndex - 1 + totalImages) % totalImages;
    const thumbnails = document.querySelectorAll('.product-img-item');
    if (thumbnails[currentIndex]) {
        const img = thumbnails[currentIndex].querySelector('img');
        changeMainImage(img.src, currentIndex);
    }
}

// Run initialization when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGallery);
} else {
    initGallery();
}
</script>

<style>
/* CSS cho Image Gallery */
.product-img {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.product-img img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-img:hover img {
    transform: scale(1.05);
}

/* Nút điều hướng */
.image-nav {
    position: absolute;
    top: 50%;
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 10px;
    transform: translateY(-50%);
    opacity: 1;
    transition: opacity 0.3s ease;
}

.nav-btn {
    background: rgba(0,0,0,0.7);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nav-btn:hover {
    background: rgba(0,0,0,0.9);
    transform: scale(1.1);
}

/* Thumbnail list */
.product-img-list {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    overflow-x: auto;
    padding: 10px 0;
}

.product-img-item {
    min-width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 3px solid transparent;
    transition: all 0.3s ease;
    position: relative;
}

.product-img-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-img-item:hover {
    border-color: #007bff;
    transform: scale(1.05);
}

.product-img-item.active {
    border-color: #007bff;
    box-shadow: 0 0 10px rgba(0,123,255,0.5);
}

/* Image counter */
.image-counter {
    text-align: center;
    margin-top: 10px;
    font-size: 14px;
    color: #666;
    font-weight: bold;
}

/* Responsive */
@media (max-width: 768px) {
    .product-img img {
        height: 300px;
    }
    
    .product-img-item {
        min-width: 60px;
        height: 60px;
    }
    
    .nav-btn {
        width: 35px;
        height: 35px;
        font-size: 16px;
    }
}

/* CSS cho input số lượng */
.product-quantity-input {
    width: 60px;
    text-align: center;
    border: none;
    outline: none;
    font-size: 16px;
    font-weight: bold;
}

.flash-sale-active {
    display: flex;
    align-items: baseline;
    gap: 12px;
}

.flash-sale-active .old-price {
    font-size: 18px;
    color: #888;
    text-decoration: line-through;
}

.flash-sale-active .new-price {
    font-size: 32px;
    font-weight: 700;
    color: #e74c3c;
}

.flash-sale-badge {
    background: linear-gradient(135deg, #ff6b6b 0%, #f093fb 100%);
    color: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
}

.flash-sale-countdown {
    margin-top: 10px;
    padding: 10px 16px;
    border-radius: 10px;
    background: rgba(231, 76, 60, 0.1);
    color: #c0392b;
    font-weight: 600;
    display: inline-block;
}

.flash-sale-countdown .countdown-value {
    margin-left: 6px;
    font-size: 16px;
}

.flash-sale-countdown.expired {
    background: #f1f2f6;
    color: #636e72;
}
</style>

<?php
if(isset($slug) && !empty($slug)) {
    $product = getBySlug("products", $slug);

    if(mysqli_num_rows($product) > 0) {
        $product = mysqli_fetch_array($product);
        $categoryName = getByID("categories", $product['category_id']);
        $categoryName = mysqli_fetch_array($categoryName);
        $pricing_detail = calculateProductPricing($product);
        $final_price_detail = $pricing_detail['final_price'];
        $has_flash_sale_detail = $pricing_detail['flash_sale'] !== null && $final_price_detail < $pricing_detail['base_price'];
        $flash_sale_detail = $pricing_detail['flash_sale'];
?>
<!-- product-detail content -->
<div class="bg-main">
    <div class="container">
        <div class="box">
            <div class="breadcumb">
                <a href="index.php?page=home">Trang chủ</a>
                <span><i class='bx bxs-chevrons-right'></i></span>
                <a href="index.php?page=products">Tất cả sản phẩm</a>
                <span><i class='bx bxs-chevrons-right'></i></span>
                <a href="#"><?= e($product['name']) ?></a>
            </div>
        </div>

        <div class="row product-row">
            <div class="col-5 col-md-12">
                <div class="product-img" id="product-img">
                    <?php
                    // Lấy ảnh chính từ product_images hoặc fallback về products.image
                    $main_image = getMainProductImage($product['id']);
                    if($main_image) {
                        echo '<img src="./images/' . e($main_image['image_url']) . '" alt="' . e($product['name']) . '" id="main-product-image" loading="lazy">';
                    } else {
                        echo '<img src="./images/' . e($product['image']) . '" alt="' . e($product['name']) . '" id="main-product-image" loading="lazy">';
                    }
                    ?>
                    <!-- Nút điều hướng ảnh -->
                    <div class="image-nav">
                        <button class="nav-btn prev-btn" onclick="previousImage()">‹</button>
                        <button class="nav-btn next-btn" onclick="nextImage()">›</button>
                    </div>
                </div>
                <div class="box">
                    <div class="product-img-list" id="thumbnail-list">
                        <?php
                        // Hiển thị tất cả ảnh từ product_images
                        $product_images = getProductImages($product['id']);
                        $image_count = 0;
                        if($product_images && mysqli_num_rows($product_images) > 0) {
                            while($img = mysqli_fetch_array($product_images)) {
                                $active_class = ($image_count == 0) ? 'active' : '';
                                echo '<div class="product-img-item ' . $active_class . '" onclick="changeMainImage(\'./images/' . ejs($img['image_url']) . '\', ' . $image_count . ')">';
                                echo '<img src="./images/' . e($img['image_url']) . '" alt="' . e($img['alt_text'] ?? $product['name']) . '" loading="lazy">';
                                echo '</div>';
                                $image_count++;
                            }
                        } else {
                            // Fallback: hiển thị ảnh chính từ products
                            echo '<div class="product-img-item active" onclick="changeMainImage(\'./images/' . ejs($product['image']) . '\', 0)">';
                            echo '<img src="./images/' . e($product['image']) . '" alt="' . e($product['name']) . '" loading="lazy">';
                            echo '</div>';
                            $image_count = 1;
                        }
                        ?>
                    </div>
                    <!-- Hiển thị số ảnh -->
                    <div class="image-counter">
                        <span id="current-image">1</span> / <span id="total-images"><?= $image_count ?></span>
                    </div>
                </div>
            </div>
            <div class="col-7 col-md-12">
                <div class="product-info">
                    <h1>
                        <?= e($product['name']) ?>
                    </h1>
                    <div class="product-info-detail">
                        <span class="product-info-detail-title">Danh mục:</span>
                        <a><?= e($categoryName['name']) ?></a>
                    </div>
                    <div class="product-info-detail">
                        <span class="product-info-detail-title">Còn:</span>
                        <a><?= intval($product['qty']) ?></a><span class="product-info-detail-title"> Sản phẩm</span>
                    </div>
                    <div class="product-info-detail">
                        <span class="product-info-detail-title">Đánh giá:</span>
                        <span class="rating">
                            <?= avgRate($product['id']) ?>
                            <i class='bx bxs-star'></i>
                        </span>
                    </div>
                    <h3>Đặc điểm nổi bật</h3>
                    <p class="product-description">
                        <?= nl2br(e($product['small_description'])) ?>
                    </p>
                    <?php if ($has_flash_sale_detail): ?>
                    <div class="product-info-price flash-sale-active">
                        <span class="old-price"><?= formatVND($product['selling_price']) ?></span>
                        <span class="new-price"><?= formatVND($final_price_detail) ?></span>
                        <span class="flash-sale-badge">-<?= intval($pricing_detail['discount_percent']) ?>%</span>
                    </div>
                    <?php if (!empty($flash_sale_detail['end_time'])): ?>
                    <div class="flash-sale-countdown" id="flash-sale-countdown" data-end="<?= $flash_sale_detail['end_time'] ?>">
                        Kết thúc sau: <span class="countdown-value">--:--:--</span>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="product-info-price"><?= formatVND($product['selling_price']) ?></div>
                    <?php endif; ?>
                    
                    <?php
                    // Chỉ cho phép khách hàng và user thường mua hàng, không cho admin mua
                    if (!isset($_SESSION['auth']) || $_SESSION['auth_user']['role_as'] != 1) {
                    ?>
                    <form method="post" action="./pages/ordercode.php" id="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="quantity" id="quantity" value="1">
                        <input type="hidden" name="order" value="true">
                        
                    <?php 
                    // Kiểm tra tồn kho bằng helper mới
                    $category_name_lower = mb_strtolower($categoryName['name'], 'UTF-8');
                    $stock_info = checkProductStock($product, $category_name_lower);
                    $has_size = $stock_info['has_size'];
                    $is_in_stock = $stock_info['is_in_stock'];
                    $is_out_of_stock = !$is_in_stock;
                    
                    if ($has_size): 
                        // Lấy danh sách size từ DB cho dropdown
                        $available_sizes = getAvailableSizes($product['id']);
                        $has_available_stock = !empty($available_sizes);
                    ?>
                    <!-- Chọn Size -->
                    <div class="size-selector">
                        <label for="product-size" style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">
                            <i class='bx bx-ruler'></i> Chọn Size: <span style="color: red;">*</span>
                        </label>
                        <?php if ($has_available_stock): ?>
                        <select name="product_size" id="product-size" required style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; margin-bottom: 15px;">
                            <option value="">-- Vui lòng chọn size --</option>
                            <?php
                            foreach($available_sizes as $size_info) {
                                echo '<option value="' . e($size_info['size']) . '">' . e($size_info['size']) . ' (Còn ' . intval($size_info['quantity']) . ')</option>';
                            }
                            ?>
                        </select>
                        <?php else: ?>
                        <div style="padding: 15px; background: #ffebee; border: 2px solid #f44336; border-radius: 8px; color: #c62828; text-align: center; font-weight: bold;">
                            <i class='bx bx-error-circle' style="font-size: 24px;"></i> HẾT HÀNG
                        </div>
                        <input type="hidden" name="product_size" value="">
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <!-- Không cần size, gửi giá trị rỗng -->
                    <input type="hidden" name="product_size" value="">
                    <?php endif; ?>
                        
                        <?php if (!$is_out_of_stock): ?>
                        <div class="product-quantity-wrapper">
                            <span class="product-quantity-btn" onclick="QualityChange('down')">
                                <i class='bx bx-minus'></i>
                            </span>
                            <input type="number" class="product-quantity-input" id="quantity-show" value="1" min="1" max="999" onchange="updateQuantity()">
                            <span class="product-quantity-btn" onclick="QualityChange('up')">
                                <i class='bx bx-plus'></i>
                            </span>
                        </div>
                        
                        <button type="submit" class="btn-flat btn-hover">Thêm vào giỏ hàng</button>
                        <?php else: ?>
                        <button type="button" disabled class="btn-flat" style="background: #ccc; cursor: not-allowed; opacity: 0.6;">
                            <i class='bx bx-x-circle'></i> Hết hàng
                        </button>
                        <?php endif; ?>
                    </form>
                    <div style="margin-top: 10px;">
                        <a href="index.php?page=cart" class="btn-flat btn-hover" style="display: inline-block; text-decoration: none; color: white; background: #007bff; padding: 10px 20px; border-radius: 5px;">
                            Mua ngay
                        </a>
                    </div>
                    <?php
                    } else {
                        // Hiển thị thông báo cho admin
                        echo '<div style="padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; color: #856404; text-align: center; margin-top: 10px;">';
                        echo '<i class="bx bx-info-circle" style="font-size: 20px;"></i> ';
                        echo '<strong>Tài khoản Admin không thể mua hàng</strong>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="box-header">
                Mô Tả
            </div>
            <div class="product-detail-description">
                <p>
                    <?= nl2br(e($product['description'])) ?>
                </p>
            </div>
        </div>
        <div class="box">
            <div class="box-header">
                Đánh giá
            </div>
            <div>
                <?php
                    $rates = getRate($product['id']);
                    if (mysqli_num_rows($rates) > 0){
                    foreach ($rates as $rate) {
                ?>
                    <div class="user-rate">
                        <div class="user-info">
                            <div class="user-avt">
                                <img src="./images/avatar.jpg" alt="User Avatar" loading="lazy">
                            </div>
                            <div class="user-name">
                                <span class="name"><?= e($rate['name']) ?></span>
                                <span class="rating">
                                    <?php  
                                        for($i=0 ; $i<intval($rate['rate']) ; $i++){
                                            echo "<i class='bx bxs-star'></i>";
                                        }
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="user-rate-content">
                            <?= nl2br(e($rate['comment'])) ?>
                        </div>
                    </div>
                <?php 
                    }}else{
                ?>
                    <div class="user-rate-content">
                        Chưa có lượt bình luận hoặc đánh giá nào
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<!-- end product-detail content -->

<script>
    // Định nghĩa hàm ở mức toàn cục
    function QualityChange(type) {
        const quantityInput = document.getElementById('quantity-show');
        const hiddenInput = document.getElementById('quantity');
        
        // Lấy giá trị hiện tại
        let quantity = parseInt(quantityInput.value) || 1;
        
        if (type == 'up') {
            quantity++;
            if (quantity > 999) quantity = 999;
        } else {
            quantity--;
            if (quantity < 1) quantity = 1;
        }
        
        // Cập nhật cả hai input
        quantityInput.value = quantity;
        hiddenInput.value = quantity;
    }
    
    function updateQuantity() {
        const quantityInput = document.getElementById('quantity-show');
        const hiddenInput = document.getElementById('quantity');
        
        let quantity = parseInt(quantityInput.value) || 1;
        
        if (quantity < 1) quantity = 1;
        if (quantity > 999) quantity = 999;
        
        quantityInput.value = quantity;
        hiddenInput.value = quantity;
    }
    
    // Cập nhật giá trị khi input thay đổi (khi người dùng gõ)
    function syncQuantity() {
        const quantityInput = document.getElementById('quantity-show');
        const hiddenInput = document.getElementById('quantity');
        
        // Cập nhật ngay cả khi người dùng đang gõ
        hiddenInput.value = quantityInput.value;
    }
    
    // Khởi tạo khi trang tải xong
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantity-show');
        const hiddenInput = document.getElementById('quantity');
        
        if(quantityInput && hiddenInput) {
            // Đồng bộ giá trị ban đầu
            hiddenInput.value = quantityInput.value;
            
            // Gán nhiều sự kiện để đảm bảo đồng bộ
            quantityInput.onchange = updateQuantity;
            quantityInput.oninput = syncQuantity;
            quantityInput.onkeyup = syncQuantity;
            quantityInput.onblur = updateQuantity;
        }

        const countdownEl = document.getElementById('flash-sale-countdown');
        if (countdownEl) {
            const countdownValueEl = countdownEl.querySelector('.countdown-value');
            const endTimeRaw = countdownEl.getAttribute('data-end');

            if (countdownValueEl && endTimeRaw) {
                const endTime = new Date(endTimeRaw.replace(' ', 'T'));
                if (!isNaN(endTime.getTime())) {
                    const updateCountdown = () => {
                        const now = new Date();
                        const distance = endTime - now;

                        if (distance <= 0) {
                            countdownValueEl.textContent = 'Đã kết thúc';
                            countdownEl.classList.add('expired');
                            clearInterval(countdownInterval);
                            return;
                        }

                        const hours = Math.floor(distance / (1000 * 60 * 60));
                        const minutes = Math.floor((distance / (1000 * 60)) % 60);
                        const seconds = Math.floor((distance / 1000) % 60);

                        countdownValueEl.textContent = `${hours.toString().padStart(2, '0')}h ${minutes.toString().padStart(2, '0')}m ${seconds.toString().padStart(2, '0')}s`;
                    };

                    updateCountdown();
                    const countdownInterval = setInterval(updateCountdown, 1000);
                }
            }
        }
    });
</script>

<?php
    } else {
        echo '<div class="bg-main"><div class="container"><div class="box-header" style="text-align: center;"> Không tìm thấy sản phẩm </div></div></div>';
    }
} else {
    echo '<div class="bg-main"><div class="container"><div class="box-header" style="text-align: center;"> Thiếu thông tin sản phẩm </div></div></div>';
}
?>

