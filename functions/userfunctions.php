<?php
include(__DIR__ . "/../config/dbcon.php");

/**
 * Kiểm tra bảng flash_sales có tồn tại hay không (cache kết quả)
 */
function flashSalesTableExists()
{
    static $exists = null;
    global $conn;

    if ($exists !== null) {
        return $exists;
    }

    $check = mysqli_query($conn, "SHOW TABLES LIKE 'flash_sales'");
    $exists = $check && mysqli_num_rows($check) > 0;
    return $exists;
}

/**
 * Kiểm tra bảng voucher có tồn tại hay không (cache kết quả)
 */
function voucherTableExists()
{
    static $exists = null;
    global $conn;

    if ($exists !== null) {
        return $exists;
    }

    $check = mysqli_query($conn, "SHOW TABLES LIKE 'voucher'");
    $exists = $check && mysqli_num_rows($check) > 0;
    return $exists;
}

/**
 * Lấy flash sale đang hoạt động của một sản phẩm (nếu có)
 */
function getActiveFlashSale($product_id)
{
    global $pdo;

    if (!flashSalesTableExists()) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM flash_sales 
              WHERE product_id = ? 
                AND status = 1
                AND (start_time IS NULL OR start_time <= NOW())
                AND (end_time IS NULL OR end_time >= NOW())
              ORDER BY end_time ASC
              LIMIT 1");
    $stmt->execute([intval($product_id)]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Tính toán giá bán hiện tại của sản phẩm có xét flash sale
 */
function calculateProductPricing($product)
{
    $base_price = isset($product['selling_price']) ? floatval($product['selling_price']) : 0;
    $final_price = $base_price;
    $discount_amount = 0;
    $discount_percent = 0;
    $flash_sale = null;

    if (isset($product['id'])) {
        $flash_sale = getActiveFlashSale($product['id']);
    }

    if ($flash_sale) {
        $discount_type = $flash_sale['discount_type'] ?? 'fixed';
        $discount_value = floatval($flash_sale['discount_value'] ?? 0);

        if ($discount_type === 'percentage') {
            if ($discount_value > 100) {
                $discount_value = 100;
            }
            $discount_amount = $base_price * ($discount_value / 100);
            $discount_percent = $discount_value;
        } else {
            $discount_amount = $discount_value;
            if ($base_price > 0) {
                $discount_percent = round(($discount_amount / $base_price) * 100);
            }
        }

        if ($discount_amount > $base_price) {
            $discount_amount = $base_price;
        }

        $final_price = max($base_price - $discount_amount, 0);
    }

    return [
        'base_price' => $base_price,
        'final_price' => $final_price,
        'discount_amount' => $discount_amount,
        'discount_percent' => $discount_percent,
        'flash_sale' => $flash_sale
    ];
}

/**
 * Chuẩn hoá dữ liệu voucher để sử dụng nhất quán
 */
function normalizeVoucherRow($voucher_row)
{
    if (!$voucher_row) {
        return null;
    }

    $type = $voucher_row['discount_type'] ?? $voucher_row['type'] ?? 'fixed';
    $value = $voucher_row['discount_value'] ?? $voucher_row['value'] ?? 0;
    $min_order = $voucher_row['min_order'] ?? 0;
    $max_discount = $voucher_row['max_discount'] ?? $voucher_row['max_order'] ?? 0;
    $quantity = $voucher_row['quantity'] ?? null;
    $used_count = $voucher_row['used_count'] ?? null;

    return array_merge($voucher_row, [
        'voucher_type' => $type,
        'voucher_value' => floatval($value),
        'voucher_min_order' => floatval($min_order),
        'voucher_max_discount' => floatval($max_discount),
        'voucher_quantity' => $quantity !== null ? intval($quantity) : null,
        'voucher_used_count' => $used_count !== null ? intval($used_count) : null,
    ]);
}

/**
 * Lấy dữ liệu voucher (đã chuẩn hoá) theo code
 */
function getVoucherDataByCode($code)
{
    $result = getVoucherByCode($code);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        return normalizeVoucherRow($row);
    }
    return null;
}

function getAllActive($table)
{
    global $conn;
    $allowed = ['products', 'categories', 'voucher', 'flash_sales'];
    if (!in_array($table, $allowed)) return mysqli_query($conn, "SELECT 1 WHERE 0");
    $query= "SELECT * FROM `$table` WHERE status='0'";
    return mysqli_query($conn, $query);
}
function getIDActive($table,$id)
{
    global $conn;
    $allowed = ['products', 'categories', 'voucher', 'flash_sales'];
    if (!in_array($table, $allowed)) return mysqli_query($conn, "SELECT 1 WHERE 0");
    $id = intval($id);
    $query= "SELECT * FROM `$table` WHERE id='$id' AND status='0'";
    return mysqli_query($conn, $query);
}
// getByID function đã được định nghĩa trong myfunctions.php
// getAll function đã được định nghĩa trong myfunctions.php
function getBySlug($table,$slug)
{
    global $pdo, $conn;
    $allowed = ['products', 'categories', 'voucher'];
    if (!in_array($table, $allowed)) return mysqli_query($conn, "SELECT 1 WHERE 0");
    
    if ($table === 'products') {
        $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE slug=? AND deleted_at IS NULL");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE slug=?");
    }
    $stmt->execute([$slug]);
    // Trả về mysqli result để giữ tương thích ngược
    $slug_esc = mysqli_real_escape_string($conn, $slug);
    if ($table === 'products') {
        return mysqli_query($conn, "SELECT * FROM `$table` WHERE slug='$slug_esc' AND deleted_at IS NULL");
    }
    return mysqli_query($conn, "SELECT * FROM `$table` WHERE slug='$slug_esc'");
}
// totalValue function đã được định nghĩa trong myfunctions.php
function getBestSelling($numberGet){
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, p.image as display_image, c.name as category_name, c.slug as category_slug
                FROM `products` p 
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.`status` = 0 AND p.`deleted_at` IS NULL
                ORDER BY p.`id` DESC
                LIMIT ?");
    $stmt->execute([intval($numberGet)]);
    return $stmt->fetchAll();
}
function getLatestProducts($numberGet,$page = 0,$type = "",$search=""){
    global $pdo;
    $offset = intval($numberGet) * intval($page);
    $params = [];

    $sql = "SELECT p.*, p.image as display_image, c.name as category_name, c.slug as category_slug
            FROM `products` p 
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.`status` = 0 AND p.`deleted_at` IS NULL";

    if (!empty($search)) {
        $sql .= " AND p.`name` LIKE ?";
        $params[] = '%' . $search . '%';
    }

    if (!empty($type)) {
        $sql .= " AND c.`slug` = ?";
        $params[] = $type;
    }

    $sql .= " ORDER BY p.`id` DESC LIMIT ? OFFSET ?";
    $params[] = intval($numberGet);
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
function getBlogs($page, $keyWold){
    global $conn;
    $page_extra = 10 * $page;
    $query =    "SELECT * FROM `blog` 
                WHERE `title` LIKE '%$keyWold%'
                ORDER BY `id` DESC
                LIMIT 10 OFFSET $page_extra";
    return mysqli_query($conn, $query);
}

// ============================================
// CÁC HÀM ĐÃ ĐƯỢC HOÀN THIỆN
// ============================================

// order
function checkOrder($id_product){
    global $pdo;
    if (!isset($_SESSION['auth_user']['id'])) {
        return 0;
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM order_detail 
              WHERE user_id = ? AND product_id = ? AND status = 4");
    $stmt->execute([intval($_SESSION['auth_user']['id']), intval($id_product)]);
    $row = $stmt->fetch();
    return $row ? $row['count'] : 0;
}

function getMyOrders(){
    global $conn;
    if (!isset($_SESSION['auth_user']['id'])) {
        return false;
    }
    $user_id = $_SESSION['auth_user']['id'];
    
    $query = "SELECT o.*, COUNT(od.id) as quantity 
              FROM orders o
              LEFT JOIN order_detail od ON o.id = od.order_id
              WHERE o.user_id = '$user_id'
              GROUP BY o.id
              ORDER BY o.created_at DESC";
    return mysqli_query($conn, $query);
}

function getMyOrderVote($id){
    global $conn;
    if (!isset($_SESSION['auth_user']['id'])) {
        return false;
    }
    $user_id = $_SESSION['auth_user']['id'];
    
    $query = "SELECT od.*, p.name, p.slug, p.image, p.small_description, p.description, p.selling_price 
              FROM order_detail od
              JOIN products p ON od.product_id = p.id
              WHERE od.id = '$id' AND od.user_id = '$user_id' AND od.status = 4";
    return mysqli_query($conn, $query);
}

function getOrderWasBuy(){
    global $conn;
    if (!isset($_SESSION['auth_user']['id'])) {
        // Trả về query rỗng thay vì false
        return mysqli_query($conn, "SELECT 1 WHERE 0");
    }
    $user_id = $_SESSION['auth_user']['id'];
    
    $query = "SELECT od.*, p.name, p.slug, p.selling_price 
              FROM order_detail od
              JOIN products p ON od.product_id = p.id
              WHERE od.user_id = '$user_id'
              ORDER BY od.created_at DESC";
    return mysqli_query($conn, $query);
}

function getRate($product_id){
    global $conn;
    $query = "SELECT od.rate, od.comment, u.name 
              FROM order_detail od
              LEFT JOIN users u ON od.user_id = u.id
              WHERE od.product_id = '$product_id' AND od.rate > 0 AND od.status = 4
              ORDER BY od.created_at DESC";
    $result = mysqli_query($conn, $query);
    return $result ? $result : mysqli_query($conn, "SELECT 1 WHERE 0");
}

function avgRate($product_id)
{
    global $conn;
    $query = "SELECT AVG(rate) as avg_rate, COUNT(*) as total_reviews 
              FROM order_detail 
              WHERE product_id = '$product_id' AND rate > 0 AND status = 4";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        if ($row['total_reviews'] > 0 && $row['avg_rate'] > 0) {
            return number_format($row['avg_rate'], 1) . "/5 (" . $row['total_reviews'] . " đánh giá)";
        }
    }
    return "Chưa có đánh giá";
}

// redirect function đã được định nghĩa trong myfunctions.php

// Functions for guest users (không cần đăng nhập)
function addToCart($product_id, $quantity = 1, $size = '') {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    // Tạo key unique cho sản phẩm + size
    $cart_key = $product_id . (!empty($size) ? '_' . $size : '');
    
    if (isset($_SESSION['cart'][$cart_key])) {
        // Kiểm tra cấu trúc cũ (chỉ là số) hay mới (array)
        if (is_array($_SESSION['cart'][$cart_key])) {
            // Cấu trúc mới
            $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
        } else {
            // Cấu trúc cũ, chuyển sang cấu trúc mới
            $old_quantity = $_SESSION['cart'][$cart_key];
            $_SESSION['cart'][$cart_key] = [
                'product_id' => $product_id,
                'quantity' => $old_quantity + $quantity,
                'size' => $size
            ];
        }
    } else {
        $_SESSION['cart'][$cart_key] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'size' => $size
        ];
    }
}

function removeFromCart($cart_key) {
    if (isset($_SESSION['cart'][$cart_key])) {
        unset($_SESSION['cart'][$cart_key]);
    }
}

function updateCartQuantity($cart_key, $quantity) {
    if (isset($_SESSION['cart'][$cart_key])) {
        if ($quantity <= 0) {
            removeFromCart($cart_key);
        } else {
            // Cập nhật số lượng cho cấu trúc mới (array)
            if (is_array($_SESSION['cart'][$cart_key])) {
                $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
            } else {
                // Cấu trúc cũ (chỉ là số)
                $_SESSION['cart'][$cart_key] = $quantity;
            }
        }
    }
}

function getCartItems() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return array();
    }
    
    $cart_items = array();
    foreach ($_SESSION['cart'] as $cart_key => $item) {
        // Xử lý cả cấu trúc cũ (chỉ có số) và mới (array)
        if (is_array($item)) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $size = $item['size'] ?? '';
        } else {
            // Cấu trúc cũ: cart_key = product_id, item = quantity
            $product_id = $cart_key;
            $quantity = $item;
            $size = '';
        }
        
        $product = getByID("products", $product_id);
        if (mysqli_num_rows($product) > 0) {
            $product_data = mysqli_fetch_array($product);
            $pricing = calculateProductPricing($product_data);
            $product_data['effective_price'] = $pricing['final_price'];
            $product_data['flash_sale_discount_amount'] = $pricing['discount_amount'];
            $product_data['flash_sale_discount_percent'] = $pricing['discount_percent'];
            $product_data['flash_sale_data'] = $pricing['flash_sale'];
            $cart_items[] = array(
                'cart_key' => $cart_key,
                'product' => $product_data,
                'quantity' => $quantity,
                'size' => $size
            );
        }
    }
    return $cart_items;
}

function getCartTotal() {
    $summary = calculateCartTotals();
    return $summary['effective_total'];
}

function clearCart() {
    unset($_SESSION['cart']);
}

function createGuestOrder($customer_info, $cart_items, $voucher_code = null) {
    global $conn;
    
    $user_id = 0; // Guest user
    $voucher_id = null;
    $cart_totals = calculateCartTotals($cart_items);
    $total_amount = $cart_totals['effective_total'];
    
    if ($voucher_code) {
        $voucher_data = getVoucherDataByCode($voucher_code);
        if ($voucher_data) {
            $discount = applyVoucherDiscount($total_amount, $voucher_data);
            if ($discount > 0) {
                $voucher_id = $voucher_data['id'];
                $total_amount = max($total_amount - $discount, 0);
            }
        }
    }
    
    $voucher_id_sql = $voucher_id ? "'" . mysqli_real_escape_string($conn, (string)$voucher_id) . "'" : "NULL";
    $order_query = "INSERT INTO orders (user_id, voucher_id, status, total_amount, created_at) 
                    VALUES ('$user_id', $voucher_id_sql, 2, '$total_amount', NOW())";
    mysqli_query($conn, $order_query);
    $order_id = mysqli_insert_id($conn);
    
    return $order_id;
}

function getVoucherByCode($code) {
    global $conn, $pdo;
    if (!voucherTableExists()) {
        return mysqli_query($conn, "SELECT 1 WHERE 0");
    }

    $stmt = $pdo->prepare("SELECT * FROM voucher 
              WHERE code = ? 
                AND status = 1 
                AND (start_date IS NULL OR start_date <= NOW()) 
                AND (end_date IS NULL OR end_date >= NOW())
              LIMIT 1");
    $stmt->execute([$code]);
    // Trả về mysqli result cho tương thích ngược
    $code_esc = mysqli_real_escape_string($conn, $code);
    return mysqli_query($conn, "SELECT * FROM voucher WHERE code = '$code_esc' AND status = 1 AND (start_date IS NULL OR start_date <= NOW()) AND (end_date IS NULL OR end_date >= NOW()) LIMIT 1");
}

function applyVoucherDiscount($total_amount, $voucher) {
    if (!$voucher) {
        return 0;
    }
    
    if (!isset($voucher['voucher_type'])) {
        $voucher = normalizeVoucherRow($voucher);
    }

    // Kiểm tra giới hạn số lượng (nếu có)
    if (isset($voucher['voucher_quantity']) && $voucher['voucher_quantity'] !== null && $voucher['voucher_quantity'] <= 0) {
        return 0;
    }

    // Kiểm tra điều kiện đơn hàng tối thiểu
    if ($total_amount < ($voucher['voucher_min_order'] ?? 0)) {
        return 0;
    }

    $discount = 0;
    if ($voucher['voucher_type'] === 'percentage') {
        $discount = ($total_amount * $voucher['voucher_value']) / 100;
    } else {
        $discount = $voucher['voucher_value'];
    }

    // Apply min/max order constraints
    if (isset($voucher['voucher_max_discount']) && $voucher['voucher_max_discount'] > 0 && $discount > $voucher['voucher_max_discount']) {
        $discount = $voucher['voucher_max_discount'];
    }
    
    return $discount;
}

/**
 * Tính tổng tiền giỏ hàng (tổng gốc, tổng sau flash sale, giảm giá)
 */
function calculateCartTotals($cart_items = null)
{
    if ($cart_items === null) {
        $cart_items = getCartItems();
    }

    $base_total = 0;
    $effective_total = 0;
    $flash_discount = 0;
    $total_quantity = 0;

    foreach ($cart_items as $item) {
        $quantity = intval($item['quantity']);
        $base_price = isset($item['product']['selling_price']) ? floatval($item['product']['selling_price']) : 0;
        $effective_price = isset($item['product']['effective_price']) ? floatval($item['product']['effective_price']) : $base_price;

        $base_total += $base_price * $quantity;
        $effective_total += $effective_price * $quantity;
        $flash_discount += ($base_price - $effective_price) * $quantity;
        $total_quantity += $quantity;
    }

    return [
        'base_total' => $base_total,
        'effective_total' => $effective_total,
        'flash_discount' => $flash_discount,
        'total_quantity' => $total_quantity,
    ];
}

// Functions for product images
function getProductImages($product_id) {
    global $conn;
    $pid = intval($product_id);
    $query = "SELECT * FROM product_images WHERE product_id = '$pid' ORDER BY is_main DESC, id ASC";
    return mysqli_query($conn, $query);
}

function getMainProductImage($product_id) {
    global $conn;
    $pid = intval($product_id);
    $query = "SELECT * FROM product_images WHERE product_id = '$pid' AND is_main = 1 LIMIT 1";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_array($result);
    }
    return false;
}

// Functions for product sizes
function getProductSizes($product_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT size, quantity FROM product_sizes WHERE product_id = ? ORDER BY size ASC");
    $stmt->execute([intval($product_id)]);
    $sizes = [];
    while($row = $stmt->fetch()) {
        $sizes[$row['size']] = $row['quantity'];
    }
    return $sizes;
}

function getAvailableSizes($product_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT size, quantity FROM product_sizes WHERE product_id = ? AND quantity > 0 ORDER BY size ASC");
    $stmt->execute([intval($product_id)]);
    return $stmt->fetchAll();
}

function getSizeQuantity($product_id, $size) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT quantity FROM product_sizes WHERE product_id = ? AND size = ? LIMIT 1");
    $stmt->execute([intval($product_id), $size]);
    $row = $stmt->fetch();
    return $row ? $row['quantity'] : 0;
}

function updateSizeQuantity($product_id, $size, $quantity) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE product_sizes SET quantity = ? WHERE product_id = ? AND size = ?");
    return $stmt->execute([intval($quantity), intval($product_id), $size]);
}

function decreaseSizeQuantity($product_id, $size, $amount) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE product_sizes SET quantity = quantity - ? WHERE product_id = ? AND size = ? AND quantity >= ?");
    return $stmt->execute([intval($amount), intval($product_id), $size, intval($amount)]);
}

function increaseSizeQuantity($product_id, $size, $amount) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE product_sizes SET quantity = quantity + ? WHERE product_id = ? AND size = ?");
    return $stmt->execute([intval($amount), intval($product_id), $size]);
}

// ============================================
// WISHLIST FUNCTIONS (Session-based)
// ============================================

function addToWishlist($product_id) {
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
    $product_id = intval($product_id);
    if (!in_array($product_id, $_SESSION['wishlist'])) {
        $_SESSION['wishlist'][] = $product_id;
    }
}

function removeFromWishlist($product_id) {
    if (isset($_SESSION['wishlist'])) {
        $key = array_search(intval($product_id), $_SESSION['wishlist']);
        if ($key !== false) {
            unset($_SESSION['wishlist'][$key]);
            $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
        }
    }
}

function isInWishlist($product_id) {
    return isset($_SESSION['wishlist']) && in_array(intval($product_id), $_SESSION['wishlist']);
}

function getWishlistItems() {
    if (!isset($_SESSION['wishlist']) || empty($_SESSION['wishlist'])) {
        return [];
    }
    global $pdo;
    $ids = array_map('intval', $_SESSION['wishlist']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id IN ($placeholders) AND p.deleted_at IS NULL");
    $stmt->execute($ids);
    return $stmt->fetchAll();
}

function getWishlistCount() {
    return isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
}

?>