<?php
session_start();
include(__DIR__ . "/../config/dbcon.php");
include(__DIR__ . "/../functions/myfunctions.php");
include(__DIR__ . "/../functions/userfunctions.php");

if (isset($_POST['order'])){
    // Chặn admin thêm vào giỏ hàng
    if (isset($_SESSION['auth']) && $_SESSION['auth_user']['role_as'] == 1) {
        $_SESSION['message'] = "Tài khoản Admin không thể mua hàng!";
        header("Location: ../index.php");
        exit();
    }
    
    $product_id = $_POST['product_id'];
    $quantity   = $_POST['quantity'];
    $size       = $_POST['product_size'] ?? '';

    $product = getByID("products",$product_id);
    if(mysqli_num_rows($product) >0){
        $product = mysqli_fetch_array($product);
        $slug    = $product['slug'];
        
        // Kiểm tra số lượng tồn kho
        $available_qty = 0;
        if (!empty($size)) {
            // Nếu có size, kiểm tra số lượng của size đó
            $size_qty = getSizeQuantity($product_id, $size);
            $available_qty = $size_qty;
        } else {
            // Nếu không có size, lấy số lượng tổng
            $available_qty = $product['qty'];
        }
        
        if ($quantity != "" && $quantity > 0 && $quantity <= $available_qty){
            // Thêm vào giỏ hàng session (cho khách không đăng nhập)
            addToCart($product_id, $quantity, $size);
            $_SESSION['message']="Thêm vào giỏ hàng thành công" . (!empty($size) ? " - Size: $size" : "");
            header("Location: ../index.php?page=product-detail&slug=$slug");
        }else{
            if ($available_qty == 0) {
                $_SESSION['message']="Sản phẩm đã hết hàng" . (!empty($size) ? " (Size: $size)" : "");
            } else {
                $_SESSION['message']="Số lượng không phù hợp. Chỉ còn $available_qty sản phẩm" . (!empty($size) ? " (Size: $size)" : "");
            }
            header("Location: ../index.php?page=product-detail&slug=$slug");
        }
    }else{
        $_SESSION['message']="Đã xảy ra lỗi không đáng có";
        header("Location: ../index.php?page=products");
    }    
}else if (isset($_GET['remove_cart'])){
    $cart_key = $_GET['remove_cart'];
    removeFromCart($cart_key);
    $_SESSION['message']="Xóa sản phẩm thành công";
    header("Location: ../index.php?page=cart");
}else if (isset($_POST['update_cart'])){
    $cart_key = $_POST['cart_key'];
    $quantity   = $_POST['quantity'];
    
    // Lấy product_id và size từ cart_key (format: productId_size hoặc chỉ productId)
    $parts = explode('_', $cart_key);
    $product_id = $parts[0];
    $size = isset($parts[1]) ? $parts[1] : '';

    // Kiểm tra số lượng tồn kho
    $available_qty = 0;
    if (!empty($size)) {
        // Nếu có size, kiểm tra số lượng của size đó
        $size_qty = getSizeQuantity($product_id, $size);
        $available_qty = $size_qty;
    } else {
        // Nếu không có size, lấy số lượng tổng
        $query = "SELECT `qty` FROM `products` WHERE `id` = '$product_id'";
        $available_qty = mysqli_fetch_array(mysqli_query($conn, $query))['qty'];
    }

    // Kiểm tra số lượng còn lại trong kho
    if ($available_qty >= $quantity){
        updateCartQuantity($cart_key, $quantity);
        $_SESSION['message']="Cập nhật sản phẩm thành công";
    }else{
        $_SESSION['message']="Số lượng quá lớn. Chỉ còn $available_qty sản phẩm" . (!empty($size) ? " (Size: $size)" : "");
    }
    
    header("Location: ../index.php?page=cart");
}else if (isset($_POST['buy_product'])){
    $check = true;
    $cart_items = getCartItems();
    
    if (empty($cart_items)) {
        $_SESSION['message'] = "Giỏ hàng trống";
        header("Location: ../index.php?page=cart");
        exit();
    }
    
    // Kiểm tra số lượng trong kho
    foreach ($cart_items as $item){
        $product = $item['product'];
        $quantity = $item['quantity'];
        $size = $item['size'] ?? '';
        
        // Kiểm tra số lượng tồn kho
        $available_qty = 0;
        if (!empty($size)) {
            // Nếu có size, kiểm tra số lượng của size đó
            $available_qty = getSizeQuantity($product['id'], $size);
        } else {
            // Nếu không có size, lấy số lượng tổng
            $available_qty = $product['qty'];
        }
        
        if ($quantity > $available_qty){
            $_SESSION['message'] = "Số lượng sản phẩm: " . $product['name'] . " không đủ trong kho. Chỉ còn " . $available_qty . " sản phẩm" . (!empty($size) ? " (Size: $size)" : "");
            $check = false;
            header("Location: ../index.php?page=cart");
            break;
        }
    }

    // Nếu hợp lệ sẽ tiến hành đặt hàng
    if ($check) {
        $user_id = isset($_SESSION['auth_user']['id']) ? $_SESSION['auth_user']['id'] : null;
        $order_status = 2;
        $cart_totals = calculateCartTotals($cart_items);
        $final_total = $cart_totals['effective_total'];

        // Tạo order
        if ($user_id !== null) {
            $order_query = "INSERT INTO orders (user_id, voucher_id, status, total_amount, created_at) 
                            VALUES ('$user_id', NULL, $order_status, '$final_total', NOW())";
        } else {
            $order_query = "INSERT INTO orders (user_id, voucher_id, status, total_amount, created_at) 
                            VALUES (NULL, NULL, $order_status, '$final_total', NOW())";
        }
        mysqli_query($conn, $order_query);
        $order_id = mysqli_insert_id($conn);
        
        // Lấy thời gian tạo đơn hàng để tạo mã nhất quán
        $order_time_query = "SELECT created_at FROM orders WHERE id = '$order_id'";
        $order_time_result = mysqli_query($conn, $order_time_query);
        $order_time_data = mysqli_fetch_array($order_time_result);
        $order_created_at = $order_time_data['created_at'];
        
        // Tạo mã đơn hàng dài: ID + 2 số ngẫu nhiên + ngày đặt hàng (ddmmyyyy) = 10 chữ số
        // Ví dụ: ID=18, ngày 15/11/2025 -> 18 + 26 + 15112025 = 182615112025
        $date_suffix = date('dmY', strtotime($order_created_at)); // ddmmyyyy = 8 chữ số
        $random_2digits = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT); // 2 chữ số ngẫu nhiên
        $order_code = $order_id . $random_2digits . $date_suffix;
        
        $total_amount = 0;
        
        // Tạo order details và cập nhật số lượng
        foreach ($cart_items as $item){
            $product = $item['product'];
            $quantity = $item['quantity'];
            $size = $item['size'] ?? '';
            $pricing = calculateProductPricing($product);
            $price = $pricing['final_price'];
            
            // Tạo order detail
            if ($user_id !== null) {
                $order_detail_query = "INSERT INTO order_detail (product_id, order_id, user_id, quantity, price, product_size, status, created_at) 
                                      VALUES ('{$product['id']}', '$order_id', '$user_id', '$quantity', '$price', '$size', $order_status, NOW())";
            } else {
                $order_detail_query = "INSERT INTO order_detail (product_id, order_id, user_id, quantity, price, product_size, status, created_at) 
                                      VALUES ('{$product['id']}', '$order_id', NULL, '$quantity', '$price', '$size', $order_status, NOW())";
            }
            mysqli_query($conn, $order_detail_query);
            
            // Cập nhật số lượng trong kho theo size
            if (!empty($size)) {
                decreaseSizeQuantity($product['id'], $size, $quantity);
            } else {
                $new_qty = $product['qty'] - $quantity;
                $update_qty_query = "UPDATE products SET qty = '$new_qty' WHERE id = '{$product['id']}'";
                mysqli_query($conn, $update_qty_query);
            }
            
            $total_amount += $price * $quantity;
        }
        
        // Đảm bảo tổng tiền chính xác
        $update_total_query = "UPDATE orders SET total_amount = '$total_amount' WHERE id = '$order_id'";
        mysqli_query($conn, $update_total_query);
        
        // Xóa giỏ hàng và voucher đã áp dụng (nếu có)
        clearCart();
        unset($_SESSION['checkout_voucher']);
        
        // Hiển thị mã đơn hàng dài cho user
        $_SESSION['message']="Đặt hàng thành công! Mã đơn hàng: $order_code (Tra cứu bằng 2 số đầu: " . substr($order_code, 0, 2) . ")";
        header("Location: ../index.php?page=cart-status");
    }

}else if(isset($_POST['place_order'])){
    // Xử lý checkout với thông tin khách hàng
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    
    // Ghép địa chỉ từ các trường riêng biệt
    $street = mysqli_real_escape_string($conn, $_POST['street'] ?? '');
    $ward = mysqli_real_escape_string($conn, $_POST['ward'] ?? '');
    $district = mysqli_real_escape_string($conn, $_POST['district'] ?? '');
    $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
    $customer_address = $street . ", " . $ward . ", " . $district . ", " . $city;
    
    $customer_email = mysqli_real_escape_string($conn, $_POST['customer_email'] ?? '');
    $order_note = mysqli_real_escape_string($conn, $_POST['order_note'] ?? '');
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? 'COD');
    
    // Ghép ghi chú đơn hàng với thông tin phương thức thanh toán
    if ($payment_method === 'Bank Transfer') {
        $full_note = "Phương thức thanh toán: Chuyển khoản ngân hàng (MB Bank - 0000955063080 - Nguyen Tran Tuan Phat)";
    } else {
        $full_note = "Phương thức thanh toán: Thanh toán khi nhận hàng (COD)";
    }
    
    if (!empty($order_note)) {
        $full_note .= " | Ghi chú: " . $order_note;
    }
    
    $cart_items = getCartItems();
    
    if (empty($cart_items)) {
        $_SESSION['message'] = "Giỏ hàng trống";
        header("Location: ../index.php?page=cart");
        exit();
    }
    
    // Kiểm tra số lượng trong kho
    $check = true;
    foreach ($cart_items as $item){
        $product = $item['product'];
        $quantity = $item['quantity'];
        $size = $item['size'] ?? '';
        
        // Kiểm tra số lượng tồn kho
        $available_qty = 0;
        if (!empty($size)) {
            $available_qty = getSizeQuantity($product['id'], $size);
        } else {
            $available_qty = $product['qty'];
        }
        
        if ($quantity > $available_qty){
            $_SESSION['message'] = "Số lượng sản phẩm: " . $product['name'] . " không đủ trong kho. Chỉ còn " . $available_qty . " sản phẩm" . (!empty($size) ? " (Size: $size)" : "");
            $check = false;
            header("Location: ../index.php?page=cart");
            break;
        }
    }
    
    if ($check) {
        $user_id = isset($_SESSION['auth_user']['id']) ? $_SESSION['auth_user']['id'] : null;
        $order_status = 2;
        $cart_totals = calculateCartTotals($cart_items);
        
        $voucher_code_input = isset($_POST['voucher_code']) ? trim($_POST['voucher_code']) : '';
        $voucher_data = null;
        $voucher_discount = 0;
        $voucher_id = null;
        
        if (!empty($voucher_code_input)) {
            $voucher_data = getVoucherDataByCode($voucher_code_input);
            if ($voucher_data) {
                $maybe_discount = applyVoucherDiscount($cart_totals['effective_total'], $voucher_data);
                if ($maybe_discount > 0) {
                    $voucher_discount = $maybe_discount;
                    $voucher_id = $voucher_data['id'];
                    $full_note .= " | Voucher áp dụng: " . $voucher_data['code'];
                } else {
                    $voucher_data = null;
                    unset($_SESSION['checkout_voucher']);
                }
            } else {
                unset($_SESSION['checkout_voucher']);
            }
        } else {
            unset($_SESSION['checkout_voucher']);
        }
        
        $final_total = max($cart_totals['effective_total'] - $voucher_discount, 0);
        $final_total = round($final_total, 2);
        
        $voucher_id_value = "NULL";
        if ($voucher_id !== null) {
            $voucher_id_value = "'" . mysqli_real_escape_string($conn, (string)$voucher_id) . "'";
        }
        
        // Tạo order với thông tin khách hàng
        if ($user_id !== null) {
            $order_query = "INSERT INTO orders (user_id, voucher_id, status, total_amount, customer_name, customer_phone, customer_address, customer_email, customer_note, created_at) 
                            VALUES ('$user_id', $voucher_id_value, $order_status, '$final_total', '$customer_name', '$customer_phone', '$customer_address', '$customer_email', '$full_note', NOW())";
        } else {
            $order_query = "INSERT INTO orders (user_id, voucher_id, status, total_amount, customer_name, customer_phone, customer_address, customer_email, customer_note, created_at) 
                            VALUES (NULL, $voucher_id_value, $order_status, '$final_total', '$customer_name', '$customer_phone', '$customer_address', '$customer_email', '$full_note', NOW())";
        }
        mysqli_query($conn, $order_query);
        $order_id = mysqli_insert_id($conn);
        
        // Lấy thời gian tạo đơn hàng để tạo mã nhất quán
        $order_time_query = "SELECT created_at FROM orders WHERE id = '$order_id'";
        $order_time_result = mysqli_query($conn, $order_time_query);
        $order_time_data = mysqli_fetch_array($order_time_result);
        $order_created_at = $order_time_data['created_at'];
        
        // Tạo mã đơn hàng dài: ID + 2 số ngẫu nhiên + ngày đặt hàng (ddmmyyyy) = 10 chữ số
        // Ví dụ: ID=18, ngày 15/11/2025 -> 18 + 26 + 15112025 = 182615112025
        $date_suffix = date('dmY', strtotime($order_created_at)); // ddmmyyyy = 8 chữ số
        $random_2digits = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT); // 2 chữ số ngẫu nhiên
        $order_code = $order_id . $random_2digits . $date_suffix;
        
        $total_amount = 0;
        
        // Tạo order details và cập nhật số lượng
        foreach ($cart_items as $item){
            $product = $item['product'];
            $quantity = $item['quantity'];
            $size = $item['size'] ?? '';
            $pricing = calculateProductPricing($product);
            $price = $pricing['final_price'];
            
            if ($user_id !== null) {
                $order_detail_query = "INSERT INTO order_detail (product_id, order_id, user_id, quantity, price, product_size, status, created_at) 
                                      VALUES ('{$product['id']}', '$order_id', '$user_id', '$quantity', '$price', '$size', $order_status, NOW())";
            } else {
                $order_detail_query = "INSERT INTO order_detail (product_id, order_id, user_id, quantity, price, product_size, status, created_at) 
                                      VALUES ('{$product['id']}', '$order_id', NULL, '$quantity', '$price', '$size', $order_status, NOW())";
            }
            mysqli_query($conn, $order_detail_query);
            
            if (!empty($size)) {
                decreaseSizeQuantity($product['id'], $size, $quantity);
            } else {
                $new_qty = $product['qty'] - $quantity;
                $update_qty_query = "UPDATE products SET qty = '$new_qty' WHERE id = '{$product['id']}'";
                mysqli_query($conn, $update_qty_query);
            }
            
            $total_amount += $price * $quantity;
        }
        
        $total_amount = round($total_amount, 2);
        $update_total_query = "UPDATE orders SET total_amount = '$total_amount' WHERE id = '$order_id'";
        mysqli_query($conn, $update_total_query);
        
        if ($voucher_id !== null && $voucher_data && isset($voucher_data['voucher_quantity']) && $voucher_data['voucher_quantity'] !== null) {
            mysqli_query($conn, "UPDATE voucher SET quantity = CASE WHEN quantity > 0 THEN quantity - 1 ELSE 0 END WHERE id = '$voucher_id'");
        }
        
        clearCart();
        unset($_SESSION['checkout_voucher']);
        
        if ($payment_method === 'Bank Transfer') {
            $_SESSION['order_id'] = $order_id;
            $_SESSION['total_amount'] = $total_amount;
            header("Location: ../index.php?page=payment&order_id=$order_id");
        } else {
            // Hiển thị mã đơn hàng dài cho user
            $_SESSION['message']="✅ Đặt hàng thành công! Mã đơn hàng: $order_code (Tra cứu bằng 2 số đầu: " . substr($order_code, 0, 2) . "). Chúng tôi sẽ liên hệ với bạn sớm nhất!";
            header("Location: ../index.php?page=cart-status");
        }
    }
    
}else if(isset($_GET['cancel_order'])){
    $order_id = $_GET['cancel_order'];
    
    // Kiểm tra quyền hủy đơn hàng (chỉ user đã đăng nhập hoặc khách có thể hủy)
    $check_order_query = "SELECT * FROM orders WHERE id = '$order_id'";
    $check_order_result = mysqli_query($conn, $check_order_query);
    
    if (mysqli_num_rows($check_order_result) > 0) {
        $order_data = mysqli_fetch_array($check_order_result);
        
        // Chỉ cho phép hủy đơn hàng ở trạng thái "Đang chuẩn bị hàng" (status = 2)
        if ($order_data['status'] == 2) {
            // Cập nhật trạng thái đơn hàng thành "Đã hủy" (status = 5)
            $cancel_order_query = "UPDATE orders SET status = 5 WHERE id = '$order_id'";
            mysqli_query($conn, $cancel_order_query);
            
            // Cập nhật trạng thái order_detail thành "Đã hủy" (status = 5)
            $cancel_detail_query = "UPDATE order_detail SET status = 5 WHERE order_id = '$order_id'";
            mysqli_query($conn, $cancel_detail_query);
            
            // Hoàn trả số lượng sản phẩm về kho
            $get_order_details = "SELECT product_id, quantity FROM order_detail WHERE order_id = '$order_id'";
            $order_details_result = mysqli_query($conn, $get_order_details);
            
            while ($detail = mysqli_fetch_array($order_details_result)) {
                $product_id = $detail['product_id'];
                $quantity = $detail['quantity'];
                
                // Cộng lại số lượng vào kho
                $restore_qty_query = "UPDATE products SET qty = qty + '$quantity' WHERE id = '$product_id'";
                mysqli_query($conn, $restore_qty_query);
            }
            
            $_SESSION['message'] = "Đơn hàng đã được hủy thành công!";
        } else {
            $_SESSION['message'] = "Không thể hủy đơn hàng này. Chỉ có thể hủy đơn hàng đang chuẩn bị.";
        }
    } else {
        $_SESSION['message'] = "Không tìm thấy đơn hàng!";
    }
    
    header("Location: ../index.php?page=cart-status");
    
}else if(isset($_POST['rate'])){
    $user_id    = $_SESSION['auth_user']['id'];
    $id         = $_POST['id'];
    $rate       = $_POST['rating'];
    $comment    = $_POST['comment'];

    $rate = intval($rate);
    $id = intval($id);
    $user_id = intval($user_id);
    $comment = mysqli_real_escape_string($conn, $comment);
    $query =    "UPDATE `order_detail` SET `rate` = '$rate', `comment` = '$comment'
                WHERE `id` = '$id' AND `user_id` = '$user_id' AND `status` = '4'";
    mysqli_query($conn, $query);

    $_SESSION['message']="Đánh giá sản phẩm thành công";
    header("Location: ../index.php?page=cart-status");
}

?>

