<?php 
// ============================================
// TRANG GIỎ HÀNG - CHỈ CONTENT
// ============================================
$pageTitle = "Giỏ hàng - NHÓM 10 Fashion Shop";

// Không cần kiểm tra đăng nhập - cho phép khách hàng xem giỏ hàng
// Chặn admin truy cập giỏ hàng
if (isset($_SESSION['auth']) && $_SESSION['auth_user']['role_as'] == 1) {
    $_SESSION['message'] = "Tài khoản Admin không thể truy cập giỏ hàng!";
    header("Location: index.php?page=home");
    exit();
}
?>

<style>
    th,td{
        padding: 5px;
        text-align: center;
    }
    .input-number{
        width: 100%;
        font-size: 20px;
        outline: none;
        border: none;
    }
    .btn-buy{
        border: none;
        outline: none;
        font-size: 17px;
        cursor: pointer;
        padding: 12px 24px;
        border-radius: 25px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: bold;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .btn-buy:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .btn-buy:active {
        transform: translateY(0px) scale(0.95);
    }
    
    /* CSS cho nút + và - - THIẾT KẾ ĐẸP HƠN */
    .quantity-btn {
        width: 35px;
        height: 35px;
        border: none;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: white;
        transition: all 0.3s ease;
        border-radius: 50%;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        font-weight: bold;
    }
    
    .quantity-btn:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .quantity-btn:active {
        transform: translateY(0px) scale(0.95);
        box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
    }
    
    .minus-btn {
        margin-right: 8px;
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
    }
    
    .minus-btn:hover {
        background: linear-gradient(135deg, #ee5a24 0%, #ff6b6b 100%);
        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
    }
    
    .plus-btn {
        margin-left: 8px;
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
    }
    
    .plus-btn:hover {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
    }
    
    /* Cải thiện input số lượng - THIẾT KẾ ĐẸP HƠN */
    .input-number {
        width: 70px;
        height: 35px;
        text-align: center;
        border: 2px solid #e0e6ed;
        border-radius: 20px;
        padding: 8px 12px;
        font-size: 16px;
        font-weight: bold;
        margin: 0 8px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        color: #2c3e50;
        transition: all 0.3s ease;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .input-number:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2), inset 0 2px 4px rgba(0,0,0,0.1);
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    }
    
    /* Container cho quantity controls */
    .quantity-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: 1px solid #dee2e6;
    }
</style>

<!-- product-detail content -->
<div class="bg-main">
    <div class="container">
        <div class="box">
            <div class="breadcumb">
                <a href="index.php?page=home">Trang chủ</a>
                <span><i class='bx bxs-chevrons-right'></i></span>
                <a href="#">Giỏ hàng của tôi</a>
            </div>
        </div>

        <div class="box" style="padding: 0 40px">
            <div class="product-info">
            <?php
                // Lấy sản phẩm từ giỏ hàng session (cho khách không đăng nhập)
                $cart_items = getCartItems();
                $cart_totals = calculateCartTotals($cart_items);
                
                if (empty($cart_items)){
            ?>
                <p style="font-size: 20px; text-align: center;">
                  Giỏ hàng của bạn trống. Mua ngay <a style="color: blue; text-decoration: underline" href="index.php?page=products">Tại đây</a>  
                </p>
            <?php } else { ?>
                <table width="100%" border="1" cellspacing="0">
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                        <th>Tổng</th>
                        <th>Xóa</th>
                        <th>Cập nhập</th>
                    </tr>
                    <?php foreach ($cart_items as $item){ 
                        $product = $item['product'];
                        $quantity = $item['quantity'];
                        $size = $item['size'] ?? '';
                        $cartKey = $item['cart_key'];
                        $cartId = md5($cartKey);
                        $base_price = isset($product['selling_price']) ? floatval($product['selling_price']) : 0;
                        $effective_price = isset($product['effective_price']) ? floatval($product['effective_price']) : $base_price;
                        $has_flash_sale = $effective_price < $base_price;
                    ?>
                    <tr>
                        <td style="text-align: left;">
                            <a href="index.php?page=product-detail&slug=<?= e($product['slug'])?>">
                                <?= e($product['name'])?>
                            </a>
                            <?php if (!empty($size)): ?>
                                <div style="font-size: 14px; color: #666; margin-top: 5px;">
                                    <i class='bx bx-ruler'></i> Size: <strong><?= e($size) ?></strong>
                                </div>
                            <?php endif; ?>
                        </td>
                        <form action="./pages/ordercode.php" method="post">
                            <td width=140>
                                <input type="hidden" name="update_cart" value="true">
                                <input type="hidden" name="cart_key" value="<?= e($cartKey) ?>">
                                <input type="hidden" class="product-price" value="<?= $effective_price ?>">
                                
                                <!-- Container đẹp cho quantity controls -->
                                <div class="quantity-controls" data-cart-key="<?= e($cartKey) ?>" data-cart-id="<?= e($cartId) ?>">
                                    <!-- Nút giảm số lượng -->
                                    <button type="button" class="quantity-btn minus-btn" onclick="changeQuantity(this, -1)" title="Giảm số lượng">
                                        <i class='bx bx-minus'></i>
                                    </button>
                                    
                                    <!-- Input số lượng -->
                                    <input type="number" name="quantity" value="<?= intval($quantity) ?>" class="input-number qty-input" id="qty-<?= e($cartId) ?>" min="1" max="999">
                                    
                                    <!-- Nút tăng số lượng -->
                                    <button type="button" class="quantity-btn plus-btn" onclick="changeQuantity(this, 1)" title="Tăng số lượng">
                                        <i class='bx bx-plus'></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <span>
                                    <?php if ($has_flash_sale): ?>
                                        <del style="color: #999; font-size: 14px;"><?= formatVND($base_price) ?></del><br>
                                        <strong><?= formatVND($effective_price) ?></strong>
                                    <?php else: ?>
                                        <?= formatVND($effective_price) ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <span class="total-price">
                                    <?= formatVND($effective_price * $quantity) ?>
                                </span>
                            </td>
                            <td>
                                <a class="btn-buy" style="font-size: 15px; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3); text-decoration: none;" href="./pages/ordercode.php?remove_cart=<?= urlencode($item['cart_key'])?>">
                                    <i class='bx bx-trash'></i> Xóa
                                </a>
                            </td>
                            <td>
                                <button class="btn-buy" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);">
                                    <i class='bx bx-refresh'></i> Cập nhập
                                </button>
                            </td>
                        </form>
                    </tr>
                    <?php
                        } 
                    ?>
                </table>
                <div style="text-align: right; margin-top: 20px;">
                    <div style="margin-bottom: 10px;">
                        <span style="font-size: 16px;">Tạm tính:</span>
                        <strong style="margin-left: 10px;"><?= formatVND($cart_totals['base_total']) ?></strong>
                    </div>
                    <?php if ($cart_totals['flash_discount'] > 0): ?>
                    <div style="margin-bottom: 10px; font-size: 16px; color: #d63031;">
                        Flash sale: -<?= formatVND($cart_totals['flash_discount']) ?>
                    </div>
                    <?php endif; ?>
                    <div style="margin-bottom: 15px; font-size: 18px; font-weight: bold;">
                        Tổng tiền: <span style="color: #e74c3c; font-size: 20px;"><?= formatVND($cart_totals['effective_total']) ?></span>
                    </div>
                    <a href="index.php?page=checkout" class="btn-buy" style="display: inline-block; text-decoration: none; padding: 15px 30px; font-size: 18px;">
                        <i class='bx bx-credit-card'></i> Thanh toán
                    </a>
                </div>
            <?php } ?>
            <a href="index.php?page=cart-status">
                <h4>Xem trạng thái đơn hàng</h4>
            </a>
            <br>
            <br>
            </div>
        </div>
    </div>
</div>
<!-- end product-detail content -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    // Hàm thay đổi số lượng khi click nút + và -
    function changeQuantity(triggerEl, change) {
        const controls = triggerEl.closest('.quantity-controls');
        if (!controls) return;
        const input = controls.querySelector('.qty-input');
        if (!input) return;

        let currentValue = parseInt(input.value) || 1;
        let newValue = currentValue + change;
        
        // Giới hạn số lượng từ 1 đến 999
        if (newValue < 1) newValue = 1;
        if (newValue > 999) newValue = 999;
        
        // Cập nhật giá trị input
        input.value = newValue;
        
        // Cập nhật tổng tiền
        updateTotalPrice(input);
    }
    
    // Hàm cập nhật tổng tiền
    function updateTotalPrice(inputEl) {
        const input = inputEl instanceof HTMLElement ? inputEl : document.getElementById('qty-' + inputEl);
        if (!input) return;
        const quantity = parseInt(input.value, 10) || 1;
        
        // Tìm row chứa sản phẩm này
        const row = input.closest('tr');
        const priceElement = row.querySelector('.product-price');
        const totalElement = row.querySelector('.total-price');
        
        if (priceElement && totalElement) {
            const price = parseInt(priceElement.value);
            const total = price * quantity;
            
            // Định dạng lại tổng giá theo định dạng Việt Nam
            const formattedPrice = total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " VNĐ";
            totalElement.innerHTML = formattedPrice;
        }
    }
    
    $(document).ready(function () {
        // Xử lý khi thay đổi input số lượng
        $('.input-number').on('change', function (e) {
            if (e.target.value == 0 || e.target.value < 1){
                e.target.value = 1;
            }
            if (e.target.value > 999){
                e.target.value = 999;
            }
            
            updateTotalPrice(e.target);
        });
        
        // Xử lý khi gõ vào input
        $('.input-number').on('input', function (e) {
            updateTotalPrice(e.target);
        });
    });
</script>
