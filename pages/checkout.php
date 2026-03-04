<?php 
$pageTitle = "Thanh toán - NHÓM 10 Fashion Shop";

if (isset($_SESSION['auth']) && $_SESSION['auth_user']['role_as'] == 1) {
    $_SESSION['message'] = "Tài khoản Admin không thể mua hàng!";
    header("Location: index.php");
    exit();
}
?>

<style>
    .checkout-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .checkout-form {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #333;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .btn-submit {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }
    
    .order-summary {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    .order-summary-row {
        display: flex;
        justify-content: space-between;
        margin: 8px 0;
        font-size: 16px;
    }

    .order-summary-row.total {
        font-weight: bold;
        font-size: 18px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e0e0e0;
    }

    .order-summary-row.discount {
        color: #d63031;
    }

    .voucher-section {
        margin-bottom: 20px;
    }

    .voucher-input-wrapper {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .voucher-input-wrapper input {
        flex: 1;
        border-radius: 8px;
        border: 2px solid #ddd;
        padding: 10px 12px;
        font-size: 16px;
    }

    .voucher-input-wrapper button {
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
        color: #fff;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .voucher-input-wrapper button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 154, 158, 0.35);
    }

    .voucher-remove-btn {
        background: #fff;
        color: #d63031;
        border: 2px solid #d63031;
    }

    .voucher-remove-btn:hover {
        background: #d63031;
        color: #fff;
        box-shadow: 0 4px 12px rgba(214, 48, 49, 0.35);
    }

    .voucher-feedback {
        margin-top: 8px;
        font-size: 14px;
    }

    .voucher-feedback.success {
        color: #2ecc71;
    }

    .voucher-feedback.error {
        color: #e74c3c;
    }
</style>

<div class="bg-main">
    <div class="container">
        <div class="box">
            <div class="breadcumb">
                <a href="index.php?page=home">Trang chủ</a>
                <span><i class='bx bxs-chevrons-right'></i></span>
                <a href="index.php?page=cart">Giỏ hàng</a>
                <span><i class='bx bxs-chevrons-right'></i></span>
                <a href="#">Thanh toán</a>
            </div>
        </div>

        <div class="checkout-container">
            <?php
            $cart_items = getCartItems();
            if (empty($cart_items)) {
                echo '<p style="text-align: center;">Giỏ hàng trống. <a href="index.php?page=products">Mua sắm ngay</a></p>';
            } else {
                $cart_totals = calculateCartTotals($cart_items);
                $voucher_discount = 0;
                $voucher_code_prefill = "";
                $voucher_row = null;

                if (isset($_SESSION['checkout_voucher']['code'])) {
                    $voucher_row = getVoucherDataByCode($_SESSION['checkout_voucher']['code']);
                    if ($voucher_row) {
                        $calculated_discount = applyVoucherDiscount($cart_totals['effective_total'], $voucher_row);
                        if ($calculated_discount > 0) {
                            $voucher_discount = $calculated_discount;
                            $voucher_code_prefill = $voucher_row['code'];
                        } else {
                            unset($_SESSION['checkout_voucher']);
                        }
                    } else {
                        unset($_SESSION['checkout_voucher']);
                    }
                }

                $final_total = max($cart_totals['effective_total'] - $voucher_discount, 0);
            ?>
            
            <div class="order-summary">
                <h3>Đơn hàng của bạn</h3>
                <?php foreach ($cart_items as $item) {
                    $product = $item['product'];
                    $quantity = $item['quantity'];
                    $base_price = isset($product['selling_price']) ? floatval($product['selling_price']) : 0;
                    $effective_price = isset($product['effective_price']) ? floatval($product['effective_price']) : $base_price;
                    $subtotal = $effective_price * $quantity;
                    $has_flash_sale = $effective_price < $base_price;
                ?>
                <div style="margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span><?= e($product['name']) ?> x <?= intval($quantity) ?></span>
                        <span><?= formatVND($subtotal) ?></span>
                    </div>
                    <?php if ($has_flash_sale): ?>
                        <div style="font-size: 13px; color: #ff7675; display: flex; justify-content: space-between;">
                            <span>Flash sale giảm</span>
                            <span>-<?= formatVND(($base_price - $effective_price) * $quantity) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php } ?>

                <div class="order-summary-row">
                    <span>Tạm tính</span>
                    <span id="summary-base-total"><?= formatVND($cart_totals['base_total']) ?></span>
                </div>
                <div class="order-summary-row discount" id="summary-flash-row" style="<?= $cart_totals['flash_discount'] > 0 ? '' : 'display:none;' ?>">
                    <span>Flash sale</span>
                    <span id="summary-flash-discount">-<?= formatVND($cart_totals['flash_discount']) ?></span>
                </div>
                <div class="order-summary-row discount" id="summary-voucher-row" style="<?= $voucher_discount > 0 ? '' : 'display:none;' ?>">
                    <span>Voucher</span>
                    <span id="summary-voucher-discount">-<?= formatVND($voucher_discount) ?></span>
                </div>
                <div class="order-summary-row total">
                    <span>Tổng thanh toán</span>
                    <span id="summary-final-total"><?= formatVND($final_total) ?></span>
                </div>
            </div>

            <div class="checkout-form">
                <h2>Thông tin giao hàng</h2>
                <form action="./pages/ordercode.php" method="POST" id="checkout-form">
                    <input type="hidden" name="place_order" value="true">
                    <input type="hidden" name="voucher_code" id="voucher_code_input" value="<?= htmlspecialchars($voucher_code_prefill) ?>">
                    
                    <div class="form-group">
                        <label>Họ và tên *</label>
                        <input type="text" name="customer_name" required 
                               value="<?= isset($_SESSION['auth_user']['name']) ? e($_SESSION['auth_user']['name']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="customer_email" required
                               value="<?= isset($_SESSION['auth_user']['email']) ? e($_SESSION['auth_user']['email']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Số điện thoại *</label>
                        <input type="tel" name="customer_phone" id="customer_phone" required
                               value="<?= isset($_SESSION['auth_user']['phone']) ? e($_SESSION['auth_user']['phone']) : '' ?>">
                    </div>

                    <div class="form-group voucher-section">
                        <label>Mã giảm giá</label>
                        <div class="voucher-input-wrapper">
                            <input type="text" id="voucher-code" placeholder="Nhập mã voucher" value="<?= htmlspecialchars($voucher_code_prefill) ?>">
                            <button type="button" id="apply-voucher-btn">Áp dụng</button>
                            <button type="button" id="remove-voucher-btn" class="voucher-remove-btn" style="<?= $voucher_discount > 0 ? '' : 'display:none;' ?>">Hủy mã</button>
                        </div>
                        <div id="voucher-feedback" class="voucher-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Số nhà, tên đường *</label>
                        <input type="text" name="street" required placeholder="VD: 123 Nguyễn Huệ">
                    </div>
                    
                    <div class="form-group">
                        <label>Phường/Xã *</label>
                        <input type="text" name="ward" required placeholder="VD: Phường Bến Nghé">
                    </div>
                    
                    <div class="form-group">
                        <label>Quận/Huyện *</label>
                        <input type="text" name="district" required placeholder="VD: Quận 1">
                    </div>
                    
                    <div class="form-group">
                        <label>Thành phố/Tỉnh *</label>
                        <input type="text" name="city" required placeholder="VD: TP. Hồ Chí Minh">
                    </div>
                    
                    <div class="form-group">
                        <label>Ghi chú đơn hàng</label>
                        <textarea name="order_note" rows="3" placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Phương thức thanh toán *</label>
                        <select name="payment_method" id="payment_method" required>
                            <option value="COD">Thanh toán khi nhận hàng (COD)</option>
                            <option value="Bank Transfer">Chuyển khoản ngân hàng</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="payment-note" style="display: none;">
                        <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;">
                            <strong><i class='bx bx-info-circle'></i> Lưu ý:</strong>
                            <p style="margin: 10px 0 0 0; color: #856404;">
                                Sau khi đặt hàng, bạn sẽ được chuyển đến trang thanh toán với QR code và thông tin chuyển khoản chi tiết.
                            </p>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">Đặt hàng</button>
                </form>
            </div>
            
            <?php } ?>
        </div>
    </div>
</div>

<script>
    const phoneInput = document.getElementById('customer_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });
    }
    
    const paymentSelect = document.getElementById('payment_method');
    if (paymentSelect) {
        paymentSelect.addEventListener('change', function() {
            const paymentNote = document.getElementById('payment-note');
            
            if (paymentNote) {
                if (this.value === 'Bank Transfer') {
                    paymentNote.style.display = 'block';
                } else {
                    paymentNote.style.display = 'none';
                }
            }
        });
    }

    const voucherInput = document.getElementById('voucher-code');
    const voucherHiddenInput = document.getElementById('voucher_code_input');
    const applyVoucherBtn = document.getElementById('apply-voucher-btn');
    const removeVoucherBtn = document.getElementById('remove-voucher-btn');
    const voucherFeedback = document.getElementById('voucher-feedback');
    const baseTotalEl = document.getElementById('summary-base-total');
    const flashRow = document.getElementById('summary-flash-row');
    const flashDiscountEl = document.getElementById('summary-flash-discount');
    const voucherRow = document.getElementById('summary-voucher-row');
    const voucherDiscountEl = document.getElementById('summary-voucher-discount');
    const finalTotalEl = document.getElementById('summary-final-total');

    function setVoucherFeedback(message, isSuccess) {
        if (!voucherFeedback) return;
        voucherFeedback.textContent = message || '';
        voucherFeedback.classList.remove('success', 'error');
        if (message) {
            voucherFeedback.classList.add(isSuccess ? 'success' : 'error');
        }
    }

    function updateSummary(formatted) {
        if (!formatted) return;

        if (baseTotalEl && typeof formatted.base_total === 'string') {
            baseTotalEl.textContent = formatted.base_total;
        }

        if (flashRow && flashDiscountEl && typeof formatted.flash_discount === 'string') {
            if (formatted.flash_discount === '0 VNĐ') {
                flashRow.style.display = 'none';
                flashDiscountEl.textContent = '';
            } else {
                flashRow.style.display = 'flex';
                flashDiscountEl.textContent = '-' + formatted.flash_discount;
            }
        }

        if (voucherRow && voucherDiscountEl && typeof formatted.voucher_discount === 'string') {
            if (formatted.voucher_discount === '0 VNĐ') {
                voucherRow.style.display = 'none';
                voucherDiscountEl.textContent = '';
            } else {
                voucherRow.style.display = 'flex';
                voucherDiscountEl.textContent = '-' + formatted.voucher_discount;
            }
        }

        if (finalTotalEl && typeof formatted.final_total === 'string') {
            finalTotalEl.textContent = formatted.final_total;
        }
    }

    function handleVoucherResponse(data) {
        if (!data) {
            setVoucherFeedback('Không thể áp dụng voucher. Vui lòng thử lại sau.', false);
            return;
        }

        if (data.formatted) {
            updateSummary(data.formatted);
        }

        if (data.success) {
            setVoucherFeedback(data.message, true);
            if (voucherHiddenInput) {
                voucherHiddenInput.value = data.voucher ? data.voucher.code : '';
            }
            if (voucherInput && data.voucher) {
                voucherInput.value = data.voucher.code;
            }
            if (removeVoucherBtn && data.voucher) {
                removeVoucherBtn.style.display = 'inline-block';
            }
        } else {
            setVoucherFeedback(data.message, false);
            if (voucherHiddenInput) {
                voucherHiddenInput.value = '';
            }
            if (removeVoucherBtn) {
                removeVoucherBtn.style.display = 'none';
            }
        }
    }

    if (applyVoucherBtn) {
        applyVoucherBtn.addEventListener('click', function() {
            if (!voucherInput) return;
            const code = voucherInput.value.trim();
            if (!code) {
                setVoucherFeedback('Vui lòng nhập mã voucher.', false);
                return;
            }

            const formData = new FormData();
            formData.append('voucher_code', code);

            fetch('pages/apply_voucher.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => handleVoucherResponse(data))
            .catch(() => {
                setVoucherFeedback('Có lỗi xảy ra khi áp dụng voucher.', false);
            });
        });
    }

    if (removeVoucherBtn) {
        removeVoucherBtn.addEventListener('click', function() {
            const formData = new FormData();
            formData.append('action', 'remove');

            fetch('pages/apply_voucher.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (voucherHiddenInput) {
                    voucherHiddenInput.value = '';
                }
                if (voucherInput) {
                    voucherInput.value = '';
                }
                removeVoucherBtn.style.display = 'none';
                handleVoucherResponse(data);
            })
            .catch(() => {
                setVoucherFeedback('Có lỗi xảy ra khi hủy voucher.', false);
            });
        });
    }
</script>

