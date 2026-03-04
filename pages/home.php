<?php 
// ============================================
// TRANG CHỦ - CHỈ CHỨA CONTENT
// ============================================
$pageTitle = "Trang chủ - NHÓM 10 Fashion Shop";
$additionalJS = ['./assets/js/index.js'];

// Load data
$bestSellingProducts = getBestSelling(8);
$LatestProducts = getLatestProducts(8);
?>

<!-- hero section -->
<div class="hero">
    <div class="slider">
        <div class="container">
        <?php
            $count = 0; 
            foreach($bestSellingProducts as $product) { 
            if ($count == 3){
                break;
            }
        ?>
                <!-- slide item -->
                <div class="slide">
                    <div class="info">
                        <div class="info-content">
                            <h3 class="top-down">
                                <?= e($product['name']) ?>
                            </h3>
                            <h2 class="top-down trans-delay-0-2">
                                <?= e($product['name']) ?>
                            </h2>
                            <p class="top-down trans-delay-0-4">
                                <?= e($product['small_description']) ?>
                            </p>
                            <div class="top-down trans-delay-0-6">
                                <a href="index.php?page=product-detail&slug=<?= e($product['slug']) ?>">
                                    <button class="btn-flat btn-hover">
                                        <span>Mua ngay</span>
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="img right-left">
                        <img src="./images/<?= e($product['display_image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                    </div>
                </div>
                <!-- end slide item -->
        <?php
            $count ++;
            } 
        ?>
        </div>
        <!-- slider controller -->
        <button class="slide-controll slide-next">
            <i class='bx bxs-chevron-right'></i>
        </button>
        <button class="slide-controll slide-prev">
            <i class='bx bxs-chevron-left'></i>
        </button>
        <!-- end slider controller -->
    </div>
</div>
<!-- end hero section -->

<!-- promotion section -->
<div class="promotion">
    <div class="row">
    <?php
        $count = 0; 
        foreach($LatestProducts as $product) { 
        if ($count == 3){
            break;
        }
    ?>
        <div class="col-4 col-md-12 col-sm-12">
            <div class="promotion-box">
                <div class="text">
                    <h3><?= e($product['name']) ?></h3>
                    <a href="index.php?page=product-detail&slug=<?= e($product['slug']) ?>">
                        <button class="btn-flat btn-hover"><span>Xem chi tiết</span></button>
                    </a>
                </div>
                <img src="./images/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
            </div>
        </div>
    <?php
        $count ++;
        } 
    ?>
    </div>
</div>
<!-- end promotion section -->

<!-- product list -->
<div class="section">
    <div class="container">
        <div class="section-header">
            <h2>Những sản phẩm mới nhất</h2>
        </div>
        <div class="row" id="latest-products">
            <?php
                foreach($LatestProducts as $product) { 
            ?>
            <div class="col-3 col-md-6 col-sm-12">
                <div class="product-card">
                    <?php
                    // Dùng checkProductStock() thay vì code trùng lặp
                    $cat_name_lower = mb_strtolower($product['category_name'] ?? '', 'UTF-8');
                    $stock_info = checkProductStock($product, $cat_name_lower);
                    $is_in_stock = $stock_info['is_in_stock'];
                    $pricing = calculateProductPricing($product);
                    $final_price = $pricing['final_price'];
                    $has_flash_sale = $pricing['flash_sale'] !== null && $final_price < $pricing['base_price'];
                    ?>
                    <div class="product-card-img" style="position: relative;">
                        <img src="./images/<?= e($product['display_image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                        <img src="./images/<?= e($product['display_image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                        <?php if ($has_flash_sale): ?>
                        <div style="position: absolute; top: 10px; right: 10px; background: #ff6b6b; color: #fff; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 13px;">
                            Flash Sale -<?= intval($pricing['discount_percent']) ?>%
                        </div>
                        <?php endif; ?>
                        <?php if (!$is_in_stock): ?>
                        <div style="position: absolute; top: 10px; left: 10px; background: #f44336; color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">
                            HẾT HÀNG
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-info">
                        <div class="product-btn">
                            <a href="index.php?page=product-detail&slug=<?= e($product['slug']) ?>">
                                <button class="btn-flat btn-hover btn-shop-now">Mua ngay</button>
                            </a>
                            <?php if ($is_in_stock): ?>
                            <button class="btn-flat btn-hover btn-cart-add" 
                                    onclick="addToCartFromHome(<?= intval($product['id']) ?>, '<?= ejs($product['name']) ?>', '<?= ejs($cat_name_lower) ?>', '<?= ejs($product['slug']) ?>')">
                                <i class='bx bxs-cart-add'></i>
                            </button>
                            <?php else: ?>
                            <button class="btn-flat btn-cart-add" 
                                    style="background: #ccc; cursor: not-allowed; opacity: 0.6;" 
                                    disabled 
                                    title="Sản phẩm hiện đã hết hàng">
                                <i class='bx bxs-cart-add'></i>
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="product-card-name">
                            <?= e($product['name']) ?>
                        </div>
                        <div class="product-card-price">
                            <span><del><?= formatVND($product['original_price']) ?></del></span>
                            <?php if ($has_flash_sale): ?>
                                <span class="curr-price"><?= formatVND($final_price) ?></span>
                            <?php else: ?>
                                <span class="curr-price"><?= formatVND($product['selling_price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>  
            </div>
            <?php } ?>
        </div>
        <div class="section-footer">
            <a href="index.php?page=products" class="btn-flat btn-hover">Xem tất cả</a>
        </div>
    </div>
</div>
<!-- end product list -->

<!-- special product -->
<div class="bg-second">
    <div class="section container">
        <div class="row">
        <?php
            foreach($bestSellingProducts as $product) { 
        ?>
            <div class="col-4 col-md-4">
                <div class="sp-item-img">
                    <img src="./images/<?= $product['image'] ?>" alt="">
                </div>
            </div>
            <div class="col-7 col-md-8">
                <div class="sp-item-info">
                    <div class="sp-item-name"><?= $product['name']?></div>
                    <p class="sp-item-description">
                        <?= $product['small_description']?>
                    </p>
                    <a href="index.php?page=product-detail&slug=<?= $product['slug'] ?>">
                        <button class="btn-flat btn-hover">Xem chi tiết</button>
                    </a>
                </div>
            </div>
        <?php 
            break; 
            }
        ?>
        </div>
    </div>
</div>
<!-- end special product -->

<!-- product list -->
<div class="section">
    <div class="container">
        <div class="section-header">
            <h2>Những sản phẩm bán chạy nhất</h2>
        </div>
        <div class="row" id="best-products">
            <?php
                foreach($bestSellingProducts as $product) { 
            ?>
            <div class="col-3 col-md-6 col-sm-12">
                <div class="product-card">
                    <?php
                    // Dùng checkProductStock() thay vì code trùng lặp
                    $cat_name_lower2 = mb_strtolower($product['category_name'] ?? '', 'UTF-8');
                    $stock_info2 = checkProductStock($product, $cat_name_lower2);
                    $is_in_stock2 = $stock_info2['is_in_stock'];
                    $pricing2 = calculateProductPricing($product);
                    $final_price2 = $pricing2['final_price'];
                    $has_flash_sale2 = $pricing2['flash_sale'] !== null && $final_price2 < $pricing2['base_price'];
                    $in_wishlist2 = isInWishlist($product['id']);
                    ?>
                    <div class="product-card-img" style="position: relative;">
                        <img src="./images/<?= e($product['image'])?>" alt="<?= e($product['name']) ?>" loading="lazy">
                        <img src="./images/<?= e($product['image'])?>" alt="<?= e($product['name']) ?>" loading="lazy">
                        <?php if ($has_flash_sale2): ?>
                        <div style="position: absolute; top: 10px; right: 10px; background: #ff6b6b; color: #fff; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 13px;">
                            Flash Sale -<?= intval($pricing2['discount_percent']) ?>%
                        </div>
                        <?php endif; ?>
                        <?php if (!$is_in_stock2): ?>
                        <div style="position: absolute; top: 10px; left: 10px; background: #f44336; color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">
                            HẾT HÀNG
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-info">
                        <div class="product-btn">
                            <a href="index.php?page=product-detail&slug=<?= e($product['slug']) ?>">
                                <button class="btn-flat btn-hover btn-shop-now">Mua ngay</button>
                            </a>
                            <?php if ($is_in_stock2): ?>
                            <button class="btn-flat btn-hover btn-cart-add" 
                                    onclick="addToCartFromHome(<?= intval($product['id']) ?>, '<?= ejs($product['name']) ?>', '<?= ejs($cat_name_lower2) ?>', '<?= ejs($product['slug']) ?>')">
                                <i class='bx bxs-cart-add'></i>
                            </button>
                            <?php else: ?>
                            <button class="btn-flat btn-cart-add" 
                                    style="background: #ccc; cursor: not-allowed; opacity: 0.6;" 
                                    disabled 
                                    title="Sản phẩm hiện đã hết hàng">
                                <i class='bx bxs-cart-add'></i>
                            </button>
                            <?php endif; ?>
                            <a href="index.php?page=wishlist&<?= $in_wishlist2 ? 'remove' : 'add' ?>_wishlist=<?= $product['id'] ?>&redirect=home" 
                               class="btn-flat btn-hover btn-cart-add" style="<?= $in_wishlist2 ? 'color: #e74c3c;' : '' ?>">
                                <i class='bx <?= $in_wishlist2 ? 'bxs-heart' : 'bx-heart' ?>'></i>
                            </a>
                        </div>
                        <div class="product-card-name">
                            <?= e($product['name'])?>
                        </div>
                        <div class="product-card-price">
                            <span><del><?= formatVND($product['original_price']) ?></del></span>
                            <?php if ($has_flash_sale2): ?>
                                <span class="curr-price"><?= formatVND($final_price2) ?></span>
                            <?php else: ?>
                                <span class="curr-price"><?= formatVND($product['selling_price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="section-footer">
            <a href="index.php?page=products" class="btn-flat btn-hover">Xem tất cả</a>
        </div>
    </div>
</div>
<!-- end product list -->

<!-- Modal chọn size khi thêm vào giỏ từ trang chủ -->
<div id="sizeModal" class="size-modal" style="display: none;">
    <div class="size-modal-overlay" onclick="closeSizeModal()"></div>
    <div class="size-modal-content">
        <div class="size-modal-header">
            <h3 id="modal-product-name">Chọn size sản phẩm</h3>
            <button class="size-modal-close" onclick="closeSizeModal()">&times;</button>
        </div>
        <div class="size-modal-body">
            <div id="size-options" class="size-options">
                <!-- Size options sẽ được thêm bằng JavaScript -->
            </div>
            <div class="quantity-selector">
                <label>Số lượng:</label>
                <div class="product-quantity-wrapper">
                    <span class="product-quantity-btn" onclick="modalQuantityChange('down')">
                        <i class='bx bx-minus'></i>
                    </span>
                    <input type="number" class="product-quantity-input" id="modal-quantity" value="1" min="1" max="999">
                    <span class="product-quantity-btn" onclick="modalQuantityChange('up')">
                        <i class='bx bx-plus'></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="size-modal-footer">
            <button class="btn-flat btn-hover" onclick="closeSizeModal()" style="background: #ccc;">Hủy</button>
            <button class="btn-flat btn-hover" onclick="confirmAddToCart()">Thêm vào giỏ</button>
        </div>
    </div>
</div>

<style>
/* Modal styles */
.size-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.size-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.size-modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.size-modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.size-modal-header h3 {
    margin: 0;
    font-size: 20px;
    color: #333;
}

.size-modal-close {
    background: none;
    border: none;
    font-size: 32px;
    color: #999;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 32px;
    height: 32px;
}

.size-modal-close:hover {
    color: #333;
}

.size-modal-body {
    padding: 20px;
}

.size-options {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}

.size-option {
    padding: 12px;
    border: 2px solid #ddd;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    background: white;
}

.size-option:hover {
    border-color: #007bff;
    background: #f0f8ff;
}

.size-option.selected {
    border-color: #007bff;
    background: #007bff;
    color: white;
}

.quantity-selector {
    margin-top: 20px;
}

.quantity-selector label {
    display: block;
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
}

.size-modal-footer {
    padding: 20px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.size-modal-footer button {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
</style>

<script>
// Biến lưu trữ thông tin sản phẩm hiện tại
let currentProductId = null;
let currentProductSlug = null;
let selectedSize = null;

// Xác định loại size dựa trên tên danh mục
function determineSizeType(categoryName) {
    // Chuyển về lowercase để so sánh
    categoryName = categoryName.toLowerCase();
    
    const clothingKeywords = ['áo', 'ao', 'quần', 'quan', 'đầm', 'dam', 'váy', 'vay', 'shirt', 'pant'];
    const shoeKeywords = ['giày', 'giay', 'dép', 'dep', 'sandal', 'shoe'];
    const noSizeKeywords = ['balo', 'túi', 'tui', 'cặp', 'cap', 'ví', 'vi', 'bag', 'wallet'];
    
    for (let keyword of clothingKeywords) {
        if (categoryName.includes(keyword)) {
            return { type: 'clothing', sizes: ['S', 'M', 'L'] };
        }
    }
    
    for (let keyword of shoeKeywords) {
        if (categoryName.includes(keyword)) {
            return { type: 'shoes', sizes: ['36', '37', '38', '39', '40', '41', '42', '43', '44'] };
        }
    }
    
    for (let keyword of noSizeKeywords) {
        if (categoryName.includes(keyword)) {
            return { type: 'none', sizes: [] };
        }
    }
    
    return { type: 'none', sizes: [] };
}

// Hàm thêm vào giỏ từ trang chủ
function addToCartFromHome(productId, productName, categoryName, productSlug) {
    currentProductId = productId;
    currentProductSlug = productSlug;
    selectedSize = null;
    
    const sizeInfo = determineSizeType(categoryName);
    
    if (sizeInfo.type === 'none' || sizeInfo.sizes.length === 0) {
        // Không cần chọn size, thêm trực tiếp
        submitAddToCart(productId, '', 1);
    } else {
        // Lấy size còn hàng từ server
        fetchAvailableSizes(productId, productName);
    }
}

// Lấy size còn hàng từ server
function fetchAvailableSizes(productId, productName) {
    fetch(`get_available_sizes.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.sizes.length > 0) {
                showSizeModal(productName, data.sizes);
            } else {
                alert('Sản phẩm hiện đang hết hàng!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi kiểm tra tồn kho');
        });
}

// Hiển thị modal
function showSizeModal(productName, sizes) {
    document.getElementById('modal-product-name').textContent = productName;
    document.getElementById('modal-quantity').value = 1;
    
    const sizeOptions = document.getElementById('size-options');
    sizeOptions.innerHTML = '';
    
    sizes.forEach(sizeData => {
        const sizeBtn = document.createElement('div');
        sizeBtn.className = 'size-option';
        
        // Nếu sizes là array object {size, quantity}
        if (typeof sizeData === 'object') {
            sizeBtn.innerHTML = `${sizeData.size}<br><small style="font-size: 11px; color: #666;">(Còn ${sizeData.quantity})</small>`;
            sizeBtn.onclick = function() {
                document.querySelectorAll('.size-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
                selectedSize = sizeData.size;
            };
        } else {
            // Fallback cho string
            sizeBtn.textContent = sizeData;
            sizeBtn.onclick = function() {
                document.querySelectorAll('.size-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
                selectedSize = sizeData;
            };
        }
        
        sizeOptions.appendChild(sizeBtn);
    });
    
    document.getElementById('sizeModal').style.display = 'flex';
}

// Đóng modal
function closeSizeModal() {
    document.getElementById('sizeModal').style.display = 'none';
    selectedSize = null;
}

// Thay đổi số lượng trong modal
function modalQuantityChange(type) {
    const input = document.getElementById('modal-quantity');
    let quantity = parseInt(input.value) || 1;
    
    if (type === 'up') {
        quantity++;
        if (quantity > 999) quantity = 999;
    } else {
        quantity--;
        if (quantity < 1) quantity = 1;
    }
    
    input.value = quantity;
}

// Xác nhận thêm vào giỏ
function confirmAddToCart() {
    if (!selectedSize) {
        alertify.error('Vui lòng chọn size!');
        return;
    }
    
    const quantity = parseInt(document.getElementById('modal-quantity').value) || 1;
    submitAddToCart(currentProductId, selectedSize, quantity);
    closeSizeModal();
}

// Gửi request thêm vào giỏ
function submitAddToCart(productId, size, quantity) {
    // Tạo form data
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('product_size', size);
    formData.append('quantity', quantity);
    formData.append('order', 'true');
    
    // Gửi request
    fetch('./pages/ordercode.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Kiểm tra nếu response chứa thông báo thành công
        if (data.includes('success') || data.includes('Thêm vào giỏ hàng thành công')) {
            alertify.success('Đã thêm sản phẩm vào giỏ hàng!');
            // Reload trang để cập nhật số lượng giỏ hàng
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else if (data.includes('login')) {
            alertify.error('Vui lòng đăng nhập để thêm vào giỏ hàng!');
            setTimeout(() => {
                window.location.href = 'index.php?page=login';
            }, 1500);
        } else {
            alertify.error('Có lỗi xảy ra. Vui lòng thử lại!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alertify.error('Có lỗi xảy ra. Vui lòng thử lại!');
    });
}
</script>

