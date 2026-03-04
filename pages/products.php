<?php
// ============================================
// TRANG DANH SÁCH SẢN PHẨM - CÓ BỘ LỌC NÂNG CAO
// ============================================
$additionalJS = ['./assets/js/products.js'];

// Lấy filter params
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : 0;
$per_page = 9;
$page_display = $page_num + 1;

// Load data dùng hàm mới (JOIN, prepared statements, tránh N+1)
$products = getProductsWithCategory($per_page, $page_num * $per_page, $type, $search, $sort, $min_price, $max_price);
$total_products_filtered = countProducts($type, $search, $min_price, $max_price);
$total_pages = ceil($total_products_filtered / $per_page);

// Lấy thông tin category nếu có filter
$current_category_name = "Tất cả sản phẩm";
if (!empty($type)) {
    $category_data = getBySlug("categories", $type);
    if ($category_data && mysqli_num_rows($category_data) > 0) {
        $category_info = mysqli_fetch_array($category_data);
        $current_category_name = $category_info['name'];
    }
}

// Set page title
if (!empty($search)) {
    $pageTitle = "Tìm kiếm: " . e($search) . " - NHÓM 10 Fashion Shop";
} else {
    $pageTitle = e($current_category_name) . " - NHÓM 10 Fashion Shop";
}

// Helper: Xây URL params cho filter/sort/pagination
function buildFilterUrl($overrides = []) {
    global $type, $search, $sort, $min_price, $max_price;
    $params = ['page' => 'products'];
    if (!empty($type)) $params['type'] = $type;
    if (!empty($search)) $params['search'] = $search;
    if (!empty($sort) && $sort !== 'newest') $params['sort'] = $sort;
    if ($min_price > 0) $params['min_price'] = $min_price;
    if ($max_price > 0) $params['max_price'] = $max_price;
    $params = array_merge($params, $overrides);
    return 'index.php?' . http_build_query($params);
}
?>

<style>
/* Filter & Sort Controls */
.filter-controls { background: #fff; border-radius: 10px; padding: 15px 20px; margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
.filter-controls label { font-weight: 600; font-size: 14px; color: #555; white-space: nowrap; }
.filter-controls select, .filter-controls input[type="number"] { padding: 8px 12px; border: 2px solid #e0e6ed; border-radius: 8px; font-size: 14px; transition: border-color 0.3s; background: #fafafa; }
.filter-controls select:focus, .filter-controls input:focus { outline: none; border-color: #667eea; }
.filter-controls input[type="number"] { width: 120px; }
.filter-controls .filter-btn { padding: 8px 18px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s; }
.filter-controls .filter-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102,126,234,0.4); }
.filter-controls .filter-clear { padding: 8px 14px; background: #f0f0f0; color: #666; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; }
.filter-controls .filter-clear:hover { background: #e0e0e0; }
.results-count { font-size: 14px; color: #888; margin-bottom: 10px; }
.wishlist-btn-card { position: absolute; top: 10px; left: 10px; background: rgba(255,255,255,0.9); border: none; width: 34px; height: 34px; border-radius: 50%; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center; transition: all 0.3s; z-index: 2; color: #999; }
.wishlist-btn-card:hover, .wishlist-btn-card.active { color: #e74c3c; background: white; }
</style>

<!-- products content -->
<div class="bg-main">
    <div class="container">
        <div class="box">
            <div class="breadcumb">
                <a href="index.php?page=home">Trang chủ</a>
                <span><i class='bx bxs-chevrons-right'></i></span>
                <a href="index.php?page=products">Tất cả sản phẩm</a>
                <?php if (!empty($type)): ?>
                <span><i class='bx bxs-chevrons-right'></i></span>
                <a href="#"><?= e($current_category_name) ?></a>
                <?php endif; ?>
                <?php if (!empty($search)): ?>
                <span><i class='bx bxs-chevrons-right'></i></span>
                <a href="#">Tìm kiếm: "<?= e($search) ?>"</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="box">
            <div class="row">
                <div class="col-3 filter-col" id="filter-col">
                    <div class="box filter-toggle-box">
                        <button class="btn-flat btn-hover" id="filter-close">close</button>
                    </div>
                    <div class="box">
                        <span class="filter-header">Danh mục</span>
                        <ul class="filter-list">
                            <li><a href="index.php?page=products" style="<?= empty($type) ? 'font-weight:700;color:#667eea;' : '' ?>">Tất cả</a></li>
                            <?php
                            $categories = getAllActive("categories");
                            if ($categories && mysqli_num_rows($categories) > 0) {
                                foreach ($categories as $item) {
                                    $active_style = ($type === $item['slug']) ? 'font-weight:700;color:#667eea;' : '';
                            ?>
                                    <li><a href="index.php?page=products&type=<?= e($item['slug']) ?>" style="<?= $active_style ?>"><?= e($item['name']) ?></a></li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <!-- Price Filter in Sidebar -->
                    <div class="box">
                        <span class="filter-header">Lọc theo giá</span>
                        <form method="GET" action="index.php" style="padding: 10px 0;">
                            <input type="hidden" name="page" value="products">
                            <?php if (!empty($type)): ?><input type="hidden" name="type" value="<?= e($type) ?>"><?php endif; ?>
                            <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                            <?php if (!empty($sort) && $sort !== 'newest'): ?><input type="hidden" name="sort" value="<?= e($sort) ?>"><?php endif; ?>
                            <div style="margin-bottom: 8px;">
                                <input type="number" name="min_price" placeholder="Giá từ" value="<?= $min_price > 0 ? $min_price : '' ?>" 
                                       style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px; font-size:13px;" min="0">
                            </div>
                            <div style="margin-bottom: 10px;">
                                <input type="number" name="max_price" placeholder="Giá đến" value="<?= $max_price > 0 ? $max_price : '' ?>" 
                                       style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px; font-size:13px;" min="0">
                            </div>
                            <button type="submit" style="width:100%; padding:8px; background: linear-gradient(135deg,#667eea,#764ba2); color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600;">
                                <i class='bx bx-filter'></i> Áp dụng
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-9 col-md-12">
                    <div class="box filter-toggle-box">
                        <button id="filter-toggle">Lọc</button>
                    </div>
                    
                    <!-- Sort & Results Info -->
                    <div class="filter-controls">
                        <label><i class='bx bx-sort-alt-2'></i> Sắp xếp:</label>
                        <select onchange="window.location.href=this.value">
                            <option value="<?= buildFilterUrl(['sort'=>'newest']) ?>" <?= $sort==='newest'?'selected':'' ?>>Mới nhất</option>
                            <option value="<?= buildFilterUrl(['sort'=>'price_asc']) ?>" <?= $sort==='price_asc'?'selected':'' ?>>Giá tăng dần</option>
                            <option value="<?= buildFilterUrl(['sort'=>'price_desc']) ?>" <?= $sort==='price_desc'?'selected':'' ?>>Giá giảm dần</option>
                            <option value="<?= buildFilterUrl(['sort'=>'name_asc']) ?>" <?= $sort==='name_asc'?'selected':'' ?>>Tên A-Z</option>
                        </select>
                        <span class="results-count"><?= $total_products_filtered ?> sản phẩm</span>
                        <?php if ($min_price > 0 || $max_price > 0): ?>
                        <a href="<?= buildFilterUrl(['min_price'=>'', 'max_price'=>'']) ?>" class="filter-clear">
                            <i class='bx bx-x'></i> Xóa bộ lọc giá
                        </a>
                        <?php endif; ?>
                    </div>

                    <div class="box">
                        <div class="row" id="products">
                        <?php if (empty($products)): ?>
                            <div style="text-align: center; padding: 40px; width: 100%;">
                                <i class='bx bx-search' style="font-size: 50px; color: #ddd;"></i>
                                <h3 style="color: #999;">Không tìm thấy sản phẩm</h3>
                                <p style="color: #bbb;">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                            </div>
                        <?php endif; ?>
                        <?php foreach ($products as $product) { 
                            // Dùng hàm checkProductStock() thay vì code trùng lặp
                            $cat_name_lower_prod = mb_strtolower($product['category_name'] ?? '', 'UTF-8');
                            $stock_info = checkProductStock($product, $cat_name_lower_prod);
                            $has_size_prod = $stock_info['has_size'];
                            $is_in_stock_prod = $stock_info['is_in_stock'];

                            $pricing = calculateProductPricing($product);
                            $final_price = $pricing['final_price'];
                            $has_flash_sale = $pricing['flash_sale'] !== null && $final_price < $pricing['base_price'];
                            $in_wishlist = isInWishlist($product['id']);
                        ?>
                            <div class="col-4 col-md-6 col-sm-12">
                                <div class="product-card">
                                    <div class="product-card-img" style="position: relative;">
                                        <img src="./images/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                                        <img src="./images/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                                        <!-- Wishlist button -->
                                        <a href="index.php?page=wishlist&<?= $in_wishlist ? 'remove' : 'add' ?>_wishlist=<?= $product['id'] ?>&redirect=products" 
                                           class="wishlist-btn-card <?= $in_wishlist ? 'active' : '' ?>" title="<?= $in_wishlist ? 'Xóa khỏi yêu thích' : 'Thêm yêu thích' ?>">
                                            <i class='bx <?= $in_wishlist ? 'bxs-heart' : 'bx-heart' ?>'></i>
                                        </a>
                                        <?php if ($has_flash_sale): ?>
                                        <div style="position: absolute; top: 10px; right: 10px; background: #ff6b6b; color: #fff; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 13px;">
                                            Flash Sale -<?= intval($pricing['discount_percent']) ?>%
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!$is_in_stock_prod): ?>
                                        <div style="position: absolute; bottom: 10px; left: 10px; background: #f44336; color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">
                                            HẾT HÀNG
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-card-info">
                                        <div class="product-btn">
                                            <a href="index.php?page=product-detail&slug=<?= e($product['slug']) ?>" class="btn-flat btn-hover btn-shop-now">Mua ngay</a>
                                            <?php if ($is_in_stock_prod): ?>
                                            <button class="btn-flat btn-hover btn-cart-add" 
                                                    onclick="addToCartFromHome(<?= intval($product['id']) ?>, '<?= ejs($product['name']) ?>', '<?= ejs($cat_name_lower_prod) ?>', '<?= ejs($product['slug']) ?>')">
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
                    </div>
                    <div class="box">
                        <ul class="pagination">
                            <?php 
                            for($i = 1 ; $i <= $total_pages ; $i++) { 
                                $url_params = buildFilterUrl(['page_num' => $i]);
                                if ($i == $page_display) {
                                    echo "<li><a class='active'>$i</a></li>";
                                } else {
                                    echo "<li><a href='$url_params'>$i</a></li>";
                                }
                            } 
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end products content -->

<!-- Modal chọn size khi thêm vào giỏ từ trang sản phẩm -->
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

// Hàm thêm vào giỏ từ trang sản phẩm
function addToCartFromHome(productId, productName, categoryName, productSlug) {
    currentProductId = productId;
    currentProductSlug = productSlug;
    selectedSize = null;
    
    const sizeInfo = determineSizeType(categoryName);
    
    if (sizeInfo.type === 'none' || sizeInfo.sizes.length === 0) {
        submitAddToCart(productId, '', 1);
    } else {
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
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('product_size', size);
    formData.append('quantity', quantity);
    formData.append('order', 'true');
    
    fetch('./pages/ordercode.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('success') || data.includes('Thêm vào giỏ hàng thành công')) {
            alertify.success('Đã thêm sản phẩm vào giỏ hàng!');
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

