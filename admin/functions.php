<?php
include("config/dbconn.php");
// include("functions/audit_functions.php"); // Tạm thời comment vì chưa cần

function getAll($table)
{
    global $conn;
    $query = "SELECT * FROM $table ORDER BY id DESC";
    return mysqli_query($conn, $query);
}

function getByID($table, $id)
{
    global $conn;
    $query = "SELECT * FROM $table WHERE id='$id'";
    return mysqli_query($conn, $query);
}

function totalValue($table)
{
    global $conn;
    $query = "SELECT COUNT(*) as `number` FROM $table";
    $totalValue = mysqli_query($conn, $query);
    $totalValue = mysqli_fetch_array($totalValue);
    return $totalValue['number'];
}

function getAllUsers($page = 0)
{
    global $conn;
    $query = "SELECT `users`.*, COUNT(`order_detail`.`id`) AS `total_buy` FROM `users`
            LEFT JOIN `order_detail` ON `users`.`id` = `order_detail`.`user_id`
            GROUP BY `users`.`id`
            ORDER BY `users`.`created_at` DESC";
    return mysqli_query($conn, $query);
}

// order
function getAllOrder($type = -1)
{
    global $conn;
    $getStatus = "2,3,4,5";
    if ($type != -1) {
        $getStatus = $type . "";
    }
    // Sử dụng LEFT JOIN để lấy cả đơn hàng của khách không đăng nhập (user_id = NULL)
    // COALESCE để ưu tiên lấy thông tin từ customer_* (khách) trước, nếu không có mới lấy từ users (admin)
    $query = "SELECT `orders`.*, 
                COUNT(`order_detail`.`id`) as `quantity`,
                GROUP_CONCAT(CONCAT(`products`.`name`, ' (x', `order_detail`.`quantity`, 
                    CASE WHEN `order_detail`.`product_size` IS NOT NULL AND `order_detail`.`product_size` != '' 
                    THEN CONCAT(' - Size: ', `order_detail`.`product_size`) 
                    ELSE '' END, ')') SEPARATOR ', ') as `products_info`,
                COALESCE(`orders`.`customer_name`, `users`.`name`) as `name`,
                COALESCE(`orders`.`customer_email`, `users`.`email`) as `email`,
                COALESCE(`orders`.`customer_phone`, `users`.`phone`) as `phone`,
                COALESCE(`orders`.`customer_address`, `users`.`address`) as `address`,
                `voucher`.`code` as `voucher_code` 
                FROM `orders`
                LEFT JOIN `users` ON `orders`.`user_id` = `users`.`id`
                LEFT JOIN `voucher` ON `orders`.`voucher_id` = `voucher`.`id`
                LEFT JOIN `order_detail` ON `order_detail`.`order_id` = `orders`.`id`
                LEFT JOIN `products` ON `order_detail`.`product_id` = `products`.`id`
                WHERE `orders`.`status` IN($getStatus)
                GROUP BY `orders`.`id`
                ORDER BY `orders`.`id` DESC";
    return mysqli_query($conn, $query);
}

function getOrderDetail($order_id)
{
    global $conn;
    // Sử dụng LEFT JOIN và COALESCE để hỗ trợ cả đơn hàng của khách không đăng nhập
    $query = "SELECT `orders`.`user_id`, `orders`.`voucher_id`, `orders`.`total_amount`,
                COALESCE(`orders`.`customer_name`, `users`.`name`) as `name`,
                COALESCE(`orders`.`customer_email`, `users`.`email`) as `email`,
                COALESCE(`orders`.`customer_phone`, `users`.`phone`) as `phone`,
                COALESCE(`orders`.`customer_address`, `users`.`address`) as `address`,
                `orders`.`customer_note`,
                `products`.`name` as `name_product`, `products`.`selling_price`,
                `voucher`.`code` as `voucher_code`, `voucher`.`type` as `voucher_type`, `voucher`.`value` as `voucher_value`,
                `order_detail`.*  FROM `order_detail` 
                JOIN `orders` ON `order_detail`.`order_id` = `orders`.`id`
                LEFT JOIN `users` ON `orders`.`user_id` = `users`.`id`
                JOIN `products` ON `products`.`id` = `order_detail`.`product_id`
                LEFT JOIN `voucher` ON `orders`.`voucher_id` = `voucher`.`id`
                WHERE `order_detail`.`order_id` = '$order_id'";
    return mysqli_query($conn, $query);
}

function totalPriceGet()
{
    global $conn;
    $query = "SELECT price * quantity as total_price FROM `order_detail` WHERE `status` = 4";
    $prices = mysqli_query($conn, $query);
    $total_price = 0;
    foreach ($prices as $price) {
        $total_price += $price['total_price'];
    }
    return $total_price;
}

function redirect($url, $message)
{
    $_SESSION['message'] = $message;
    header("Location:" . $url);
    exit();
}

// Function để lấy tất cả ảnh của sản phẩm
function getProductImages($product_id) {
    global $conn;
    $query = "SELECT * FROM product_images WHERE product_id = '$product_id' ORDER BY is_main DESC, id ASC";
    return mysqli_query($conn, $query);
}

// Function để lấy ảnh chính của sản phẩm
function getMainProductImage($product_id) {
    global $conn;
    $query = "SELECT * FROM product_images WHERE product_id = '$product_id' AND is_main = 1 LIMIT 1";
    $result = mysqli_query($conn, $query);
    if(mysqli_num_rows($result) > 0) {
        return mysqli_fetch_array($result);
    }
    return false;
}

// Voucher functions
function getAllVouchers()
{
    global $conn;
    $query = "SELECT * FROM voucher ORDER BY created_at DESC";
    return mysqli_query($conn, $query);
}

function getVoucherByCode($code)
{
    global $conn;
    $query = "SELECT * FROM voucher WHERE code = '$code' AND status = 1 AND start_date <= NOW() AND end_date >= NOW()";
    return mysqli_query($conn, $query);
}

function getVoucherById($id)
{
    global $conn;
    $query = "SELECT * FROM voucher WHERE id = '$id'";
    return mysqli_query($conn, $query);
}

function calculateOrderTotal($order_id)
{
    global $conn;
    $query = "SELECT SUM(price * quantity) as total FROM order_detail WHERE order_id = '$order_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    return $row['total'] ?? 0;
}

function applyVoucherDiscount($total_amount, $voucher)
{
    if ($voucher['type'] == 'percentage') {
        $discount = ($total_amount * $voucher['value']) / 100;
    } else {
        $discount = $voucher['value'];
    }
    
    // Apply min/max order constraints
    if ($total_amount < $voucher['min_order']) {
        return 0; // No discount if order is below minimum
    }
    
    if ($voucher['max_order'] > 0 && $discount > $voucher['max_order']) {
        $discount = $voucher['max_order'];
    }
    
    return $discount;
}

// Flash sale functions
function getAllFlashSales()
{
    global $conn;
    $query = "SELECT fs.*, 
                    p.name AS product_name,
                    p.slug AS product_slug,
                    p.selling_price AS product_price,
                    p.image AS product_image
              FROM flash_sales fs
              JOIN products p ON fs.product_id = p.id
              ORDER BY fs.start_time DESC";
    return mysqli_query($conn, $query);
}

function getFlashSaleById($id)
{
    global $conn;
    $id = intval($id);
    $query = "SELECT fs.*, 
                    p.name AS product_name,
                    p.slug AS product_slug,
                    p.selling_price AS product_price,
                    p.image AS product_image
              FROM flash_sales fs
              JOIN products p ON fs.product_id = p.id
              WHERE fs.id = '$id'
              LIMIT 1";
    return mysqli_query($conn, $query);
}

function getProductsForFlashSale()
{
    global $conn;
    $query = "SELECT id, name, selling_price FROM products WHERE status = 0 ORDER BY name ASC";
    return mysqli_query($conn, $query);
}

// Order statistics
function getOrderStatusSummary()
{
    global $conn;
    $query = "SELECT status, COUNT(*) AS total FROM orders GROUP BY status";
    $result = mysqli_query($conn, $query);
    $summary = [
        '2' => 0, // Đang chuẩn bị
        '3' => 0, // Đang giao
        '4' => 0, // Hoàn thành
        '5' => 0, // Đã hủy
        '6' => 0  // Thất bại
    ];

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $status = (string)$row['status'];
            if (isset($summary[$status])) {
                $summary[$status] = (int)$row['total'];
            }
        }
    }

    return $summary;
}

function getTopSellingProducts($limit = 5)
{
    global $conn;
    $limit = intval($limit);
    if ($limit <= 0) {
        $limit = 5;
    }

    $query = "SELECT 
                p.id,
                p.name,
                p.image,
                SUM(od.quantity) AS total_quantity,
                SUM(od.quantity * od.price) AS total_revenue
              FROM order_detail od
              JOIN orders o ON od.order_id = o.id
              JOIN products p ON od.product_id = p.id
              WHERE o.status = 4
              GROUP BY p.id, p.name, p.image
              ORDER BY total_quantity DESC, total_revenue DESC
              LIMIT $limit";

    return mysqli_query($conn, $query);
}

?>
